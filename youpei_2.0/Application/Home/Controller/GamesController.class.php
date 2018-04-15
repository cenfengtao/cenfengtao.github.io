<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

require_once __DIR__ . '/../../../ThinkPHP/Library/Org/Util/JSDDK.class.php';

class GamesController extends BaseController
{
    public function seekGame()
    {
        $this->title = "寻宝活动";
        $this->display();
    }

    public function startGame()
    {
        $this->display();
    }

    public function ajaxSeekGame()
    {
        if (!$_GET['id']) {
            return show(0, '游戏ID不能为空');
        }
        $games = M('games')->where(array('id' => $_GET['id'], 'start_time' => array('ELT', time()), 'end_time' => array('EGT', time())))->find();
        if (!$games) {
            return show(0, '并不在活动期间内哦');
        }
        $isGet = M('games_record')->where(array('user_id' => $this->user['id'], 'type' => 1, 'create_time' =>
            array(array('EGT', $games['start_time']), array('ELT', $games['end_time']), 'and')))->select();
        if (count($isGet) >= 2) {
            return show(0, '你已经参加过了呢,无法再打开宝箱,请到个人中心-我的活动-双节寻宝中查看之前的奖品吧。');
        } else if (count($isGet) == 1) {
            $isShare = M('share_record')->where(['type' => 4, 'type_id' => $_GET['id'], 'user_id' => $this->user['id']])->getField('id');
            if (!$isShare) {
                return show(0, '点击右上角分享活动可以额外获得一次抽宝箱机会');
            }
        }
        //先把库存为0的奖品既概率设为0
        M('games_prize')->where(['count' => ['elt', 0]])->save(['probability' => 0]);
        $data = M('games_prize')->where(array('type' => 1, 'type_id' => $games['id']))->select();
        $arr = [];
        foreach ($data as $key => $val) {
            $arr[$key] = $val['probability'];
        }
        $rid = $this->get_rand($arr); //根据概率获取奖项id
        $res['yes'] = $data[$rid]; //中奖项
        $res['yes']['code'] = substr(md5(uniqid() . $this->user['open_id']), 0, 8);
        unset($data[$rid]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($data); //打乱数组顺序
        for ($i = 0; $i < count($data); $i++) {
            $res['no'][$i] = $data[$i]['title'];
        }
        //判断奖品类型
        if ($res['yes']['status'] == 1) { //积分
            M('user')->where(['id' => $this->user['id']])->setInc('integral', $res['yes']['amount']);
            $integralData = [
                'user_id' => $this->user['id'],
                'token' => $this->token,
                'integral' => $res['yes']['amount'],
                'create_time' => time(),
                'status' => 1,
                'type' => 2,
                'integral_type' => 10,
                'desc' => '寻宝游戏奖励积分',
            ];
            D('IntegralRecord')->insert($integralData);
            //发送积分信息
            $first = '【优培圈】温馨提醒您的积分有变动';
            $keyword1 = '+' . $res['yes']['amount'] . '分';
            $keyword2 = '寻宝游戏奖励积分';
            $keyword3 = date("Y-m-d H:i:s", time());
            $keyword4 = $this->user['integral'] + $res['yes']['amount'] . '分';
            $remark = '注册优培圈平台即可获取积分并兑换微信红包，邀请更多好友扫码支持即有机会直接抽取现金红包';
            $url = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/MemberIntegral/integrallist/token/" . $this->token;
            $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__KEYWORD4__', '__REMARK__');
            $infoFormat = array($this->user['open_id'], $url, $first, $keyword1, $keyword2, $keyword3, $keyword4, $remark);
            $wxuser = get_wxuser($this->token);
            execute_public_template('INTEGRAL_CHANGE', $templeFormat, $infoFormat, $wxuser);
        } else if ($res['yes']['status'] == 2) { //红包
            $data = array(
                'user_id' => $this->user['id'],
                'integral' => 0,
                'money' => $res['yes']['amount'],
                'create_time' => time(),
                'type' => 3,
                'status' => 0
            );
            $redId = M('redpacket')->add($data);
            $currentMoney = M('wxpayout_limit')->getField('current_money');
            if ($res['yes']['amount'] > $currentMoney) {
                return show(0, '红包剩余数量不足，请联系客服');
            }
            $WXPayTools = new \Common\Common\WXPayTools();
            $result = $WXPayTools::redPacket($this->user['open_id'], $res['yes']['amount']);
            if (!($result['code'] == 1 || $result['code'] == 4)) {
                return show(0, '兑换失败，请联系客服');
            } else {
                M('redpacket')->where(['id' => $redId])->save(['status' => 1]);
                M('wxpayout_limit')->setDec('current_money', $res['yes']['amount']);
            }
        }
        $isExchange = 2;
        $exchangeTime = time();
        if ($res['yes']['status'] == 4 || $res['yes']['status'] == 5) {
            $isExchange = 1;
            $exchangeTime = 0;
        }
        //扣除库存
        M('games_prize')->where(['id' => $res['yes']['id']])->setDec('count', 1);
        $userPrize = [
            'user_id' => $this->user['id'],
            'create_time' => time(),
            'type' => 1,
            'type_id' => $_GET['id'],
            'code' => $res['yes']['code'],
            'status' => $res['yes']['status'],
            'title' => $res['yes']['title'],
            'is_exchange' => $isExchange,
            'exchange_time' => $exchangeTime,
            'prize_id' => $res['yes']['id'],
        ];
        $id = M('games_record')->add($userPrize);
        $res['yes']['is_address'] = $res['yes']['status'] == 4 ?: false;
        $res['yes']['record_id'] = $id;
        if (count($isGet) == 1) {
            $res['firstPrize'] = $isGet[0];
            return show(2, '恭喜你中奖了', $res);
        } else {
            return show(1, '恭喜你中奖了', $res);
        }
    }

    public function ajaxIsGet()
    {
        if (!$_GET['id']) {
            return show(0, '游戏ID不能为空');
        }
        $games = M('games')->where(array('id' => $_GET['id'], 'start_time' => array('ELT', time()), 'end_time' => array('EGT', time())))->find();
        if (!$games) {
            return show(0, '并不在活动期间内哦');
        }
        $count = M('games_record')->where(array('user_id' => $this->user['id'], 'type' => 1, 'create_time' =>
            array(array('EGT', $games['start_time']), array('ELT', $games['end_time']), 'and')))->count();
        if ($count == 2) {
            return show(2, '你已经参加过了呢', $count);
        } else if ($count == 1) {
            return show(3, '你已经参加过此活动了，点击右上角把活动转发给朋友可额外获得一次开宝箱机会哦');
        } else {
            return show(1, '获取成功');
        }
    }

    public function share()
    {
        if (!$_GET['id']) {
            return show(0, '游戏ID不能为空');
        }
        $games = M('games')->where(array('id' => $_GET['id'], 'start_time' => array('ELT', time()), 'end_time' => array('EGT', time())))->find();
        if (!$games) {
            return show(0, '找不到该游戏');
        }
        //获取ticket
        $wxuser = get_wxuser($this->token);
        $jssdk = new \JSSDK($wxuser['appid'], $wxuser['appsecret']);
        $signPackage = $jssdk->GetSignPackage();
        //分享内容图片和链接地址
        if (strpos($games['image'], 'http') === false) {
            $shareImg = 'http://' . $_SERVER["HTTP_HOST"] . $games['image'];
        } else {
            $shareImg = $games['image'];
        }
        $shareUrl = 'http://' . $_SERVER["HTTP_HOST"] . '/index.php/Games/startGame.html?id=' . $_GET['id'] . '&token=' . $this->token;
        $games['f_title'] = '上优培圈，玩游戏买课程还能拿奖品，还不快来~';
        $games['signPackage'] = $signPackage;
        $games['shareImg'] = $shareImg;
        $games['shareUrl'] = $shareUrl;
        //判断是否还可以抽奖
        $records = M('games_record')->where(['user_id' => $this->user['id'], 'type_id' => $_GET['id']])->field('prize_id,code')->select();
        $prizes = [];
        foreach ($records as $key => $val) {
            $prizes[$key]['title'] = M('games_prize')->where(['id' => $val['prize_id']])->getField('title');
            $prizes[$key]['code'] = $val['code'];
        }
        if (count($records) == 0) {
            return show(1, '获取成功', ['games' => $games]);
        } else if (count($records) == 1) {
            //判断有否转发
            $isShare = M('share_record')->where(['type' => 4, 'type_id' => $_GET['id'],
                'user_id' => $this->user['id']])->getField('id');
            return show(2, '获取成功', ['games' => $games, 'firstPrize' => $prizes[0], 'isShare' => $isShare]);
        } else {
            return show(3, '获取成功', ['games' => $games, 'firstPrize' => $prizes[0], 'secondPrize' => $prizes[1]]);
        }
    }

    function get_rand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    public function prize()
    {
        $this->title = "游戏奖品";
        $list = M('games_record')->where(array('user_id' => $this->user['id']))->limit(0, 17)->order('create_time desc')->select();
        foreach ($list as $k => $v) {
            $list[$k]['desc'] = M('games_prize')->where('id=' . $v['prize_id'])->getField('desc');
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function loadingPrize()
    {
        $page = (int)I("page");
        $list = D('GamesRecord')->getListByPage($page, $this->user['id']);
        foreach ($list as $k => $v) {
            $list[$k]['desc'] = M('games_prize')->where('id=' . $v['prize_id'])->getField('desc');
            $list[$k]['time'] = date('Y-m-d', $v['create_time']);
            if ($v['address_id']) {
                $list[$k]['username'] = M('contact_address')->where('id=' . $v['address_id'])->getField('username');
                $list[$k]['mobile'] = M('contact_address')->where('id=' . $v['address_id'])->getField('mobile');
                $list[$k]['address'] = M('contact_address')->where('id=' . $v['address_id'])->getField('address');
            }
        }
        if (!$list || empty($list)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '没有数据'));
        }
        $this->ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $list));
    }

    //分享给朋友
    public function shareForFriend()
    {
        if (!$_POST['id']) {
            return show(0, 'ID参数错误');
        }
        $isShare = M('share_record')->where(['type' => 4, 'type_id' => $_POST['id'], 'user_id' => $this->user['id']])->getField('id');
        $insertData = [
            'create_time' => time(),
            'type' => 4,
            'type_id' => $_POST['id'],
            'desc' => '分享游戏',
            'user_id' => $this->user['id'],
        ];
        D('ShareRecord')->insert($insertData);
        if ($isShare) {
            return show(0, '已经分享过了');
        } else {
            return show(1, '你可获得额外一次开宝箱机会');
        }
    }

    //更新收货地址
    public function updateAddress()
    {
        if (!$_POST['recordId']) {
            return show(0, 'ID参数错误');
        }
        if (!$_POST['username']) {
            return show(0, '请输入联系人名字');
        }
        if (!$_POST['mobile'] || !preg_match("/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/", $_POST['mobile'])) {
            return show(0, '请输入正确联系手机号码');
        }
        if (!$_POST['address']) {
            return show(0, '请输入收货地址');
        }
        $data = [
            'username' => $_POST['username'],
            'mobile' => $_POST['mobile'],
            'address' => $_POST['address']
        ];
        $id = D('GamesRecord')->updateById($_POST['recordId'], $data);
        if ($id === false) {
            return show(0, '提交失败');
        } else {
            return show(1, '提交成功，请等待查收');
        }
    }
}