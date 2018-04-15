<?php
namespace Home\Controller;

use Think\Controller;

require_once __DIR__ . '/../../../ThinkPHP/Library/Org/Util/JSDDK.class.php';

class LotteryController extends BaseController
{
    public function demo(){
       $this->title = "抽奖";
        if (I('token', 0) && I('activityid', 0) > 0) {
            $re = D('User')->queryByLotteryinfo($this->user['id'], I('token'), I('activityid'));
            $this->assign('lotteryCount', $re);
        } else {
            $Lotteryinfo = D('User')->getLotteryinfo($this->user['id']);
            $this->assign('lotteryCount', $Lotteryinfo['lottery_count']);
        }
        //获取轮播中奖者
        $re = D('DrawRecord')->getallDrawrecord();
        foreach ($re as $key => $value) {
            $re[$key]['username'] = substr_cut($value['username']);
        }
        shuffle($re);
        $this->assign('re', $re);
        //设置分享信息
        $wxuser = get_wxuser($this->token);
        $jssdk = new \JSSDK($wxuser['appid'], $wxuser['appsecret']);
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);
        //分享内容图片和链接地址
        $shareImg = "http://www.youpei-exc.com/uploads/customer/g232238gc959/3/9/6/5886c9404c766.jpg";
        $shareUrl = 'http://' . $_SERVER["HTTP_HOST"] . U('Lottery/index', array('share_user_id' => $this->user['id'], 'token' => $this->token));
        $this->assign('share_img', $shareImg)->assign('share_url', $shareUrl);
        $config = D('Config')->find(1);
        $this->assign('config', $config);
        $this->assign('token', I('token', 0));
        $this->assign('activityid', I('activityid', 0));
        $this->display();
    }
    public function index()
    {
        $this->title = "抽奖";
        if (I('token', 0) && I('activityid', 0) > 0) {
            $re = D('User')->queryByLotteryinfo($this->user['id'], I('token'), I('activityid'));
            $this->assign('lotteryCount', $re);
        } else {
            $Lotteryinfo = D('User')->getLotteryinfo($this->user['id']);
            $this->assign('lotteryCount', $Lotteryinfo['lottery_count']);
        }
        //获取轮播中奖者
        $re = D('DrawRecord')->getallDrawrecord();
        foreach ($re as $key => $value) {
            $re[$key]['username'] = substr_cut($value['username']);
        }
        shuffle($re);
        $this->assign('re', $re);
        //设置分享信息
        $wxuser = get_wxuser($this->token);
        $jssdk = new \JSSDK($wxuser['appid'], $wxuser['appsecret']);
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);
        //分享内容图片和链接地址
        $shareImg = "http://www.youpei-exc.com/uploads/customer/g232238gc959/3/9/6/5886c9404c766.jpg";
        $shareUrl = 'http://' . $_SERVER["HTTP_HOST"] . U('Lottery/index', array('share_user_id' => $this->user['id'], 'token' => $this->token));
        $this->assign('share_img', $shareImg)->assign('share_url', $shareUrl);
        $config = D('Config')->find(1);
        $this->assign('config', $config);
        $this->assign('token', I('token', 0));
        $this->assign('activityid', I('activityid', 0));
        $this->display();
    }


    public function lottery()
    {
        if ($_GET) {
            $token = I('token', 0);
            $activityid = I('activityid', 0);
        }
        $LotteryResult = D('User')->getLotteryResult($this->user, $token, $activityid);
        $this->ajaxReturn($LotteryResult);
    }


    public function exchange()
    {
        $re = D('User')->getLotteryintegral($this->user);
        $rs = D('Config')->find(1);
        $this->assign('integral', $re);
        $this->assign('integral_exchange', $rs['integral_exchange']);
        $this->display();
    }

    public function confirmexchange()
    {
        $num = (int)I('num');
        $re = D('User')->confirmexchange($this->user, $num);
        $this->ajaxReturn($re);
    }


    public function queryByAreasList()
    {
        $m = D('Areas');
        $list = $m->queryByAreasList(I('parentId'));
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        $this->ajaxReturn($rs);
    }

    public function userinfo()
    {

        //获取地区信息
        $userid = $this->user['id'];
        $re = D('Areas')->queryByAreasList(0);
        $user = D('UserAddress')->get($this->user['id']);
        $user['userid'] = $userid;
        $this->assign('user', $user);
        $this->assign('prizeid', I('prizeid', 0));
        $this->assign('token', I('token', 0));
        $this->assign('pid', I('pid', 0));
        $this->assign('areaList', $re);
        $this->display();
    }

    public function confirmsave()
    {
        $m = D('UserAddress');
        $rs = $m->edit();
        if ($rs['status'] == 1) {
            $m = D('PrizeAddress');
            $rs = $m->editPrizeAddress();
        }
        $this->ajaxReturn($rs);
    }

    public function userAddress()
    {
        $this->title = "地址管理";
        $m = D('UserAddress');
        $List = $m->queryByList($this->user['id']);
        $this->assign('List', $List);
        $this->display();
    }

    public function toEdit()
    {
        $this->title = "地址修改";
        //获取地区信息
        $userid = $this->user['id'];
        $re = D('Areas')->queryByAreasList(0);
        $user = D('UserAddress')->get($userid);
        $user['userid'] = $userid;
        $this->assign('user', $user);
        $this->assign('areaList', $re);
        $this->display();
    }

    /**
     * 删除操作
     */
    public function del()
    {
        $m = D('UserAddress');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    public function lottery_record()
    {
        $this->title = "抽奖记录";
        $page = D('DrawRecord')->getDrawrecord($this->user['id']);
        $pager = new \Think\Pages($page['total'], $page['pageSize']);
        $pager->setConfig('first', '<img src="/Public/images/page02.jpg" alt="">');
        $pager->setConfig('prev', '<img src="/Public/images/page03.jpg" alt="">');
        $pager->setConfig('next', '<img src="/Public/images/page05.jpg" alt="">');
        $pager->setConfig('last', '<img src="/Public/images/page06.jpg" alt="">');
        $pager->setConfig('theme', '%FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display();
    }

    public function see()
    {
        $m = D('PrizeAddress');
        $re = $m->getPrizeinfo((int)I('pid', 0));
        $this->ajaxReturn($re);
    }

    public function rule()
    {
        // $this->title = "抽奖说明";
        // $config = M('Config')->where("token='{$this->token}'")->find();
        // $this->assign('lotteryrule', $config['lotteryrule']);
        // $this->display();
        $this->title = "抽奖说明";
        if (I('token', 0) && I('activityid', 0) > 0) {
            $m = D('Temporaryactivity');
            $re = $m->getrule(I('token', 0), I('activityid', 0));
        } else {
            $m = D('Config');
            $re = $m->getrule(I('token', 0));
        }

        $this->assign('lotteryrule', htmlspecialchars_decode($re['lotteryrule']));
        $this->display();
    }

    //分享记录
    public function shareRecord()
    {
        $insertData = [
            'create_time' => time(),
            'type' => 3,
            'type_id' => '',
            'desc' => '分享抽奖活动',
            'user_id' => $this->user['id'],
        ];
        D('ShareRecord')->insert($insertData);
        return show(0, '分享成功');
    }

    //生成海报
    public function createPoster()
    {
        //根据token获取当前活动的海报信息
        $qrcode = D('Qrcode')->getActionQrcode($_GET['token']);
        //没有当前活动
        if (!$qrcode) {
            $this->assign('status', 0);
        }
        $scanReply = D('ScanReply')->findByToken($_GET['token'], $qrcode['scene_id']);
        //当前活动没设置海报
        if (!$scanReply['image'] || empty($scanReply['image'])) {
            $this->assign('status', 1);
        }
        //生成海报
        $url = $_SERVER['HTTP_HOST'] . U('ScanResponse/createQrcode', array('token' => $this->token));
        $this->request_by_curl($url, array('user_id' => $this->user['id'],
            'scene_id' => $qrcode['scene_id'], 'image' => $scanReply['image'], 'open_id' => $this->user['open_id']));
        $this->sendMsgByGm('海报生成中，请稍后..', $this->user['open_id']);
        $this->assign('status', 2);
        $this->display();
    }

    //查看助力数
    public function checkScanCount()
    {
        //根据token获取当前活动的海报信息
        $qrcode = D('Qrcode')->getActionQrcode($_GET['token']);
        //没有当前活动
        if (!$qrcode) {
            $this->assign('status', 0);
        }
        $count = M('qrcode_record')->where(array('scene_id' => $qrcode['scene_id'], 'share_user_id' => $this->user['id']))->count();
        $this->sendMsgByGm("当前活动助力次数为" . $count . "次", $this->user['open_id']);
        $this->assign('status', 1);
        $this->display();
    }

    function sendMsgByGm($content, $open_id, $type = 'text')
    {
        $wxuser = get_wxuser($_GET['token']);
        $access_token = get_weixin_access_token($wxuser, false);
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;
        $data = array(
            "touser" => $open_id,
            "msgtype" => $type,
            $type => array(
                $type == 'text' ? 'content' : 'media_id' => $content,
            ),
        );
        return json_decode(http_post($url, json_encode($data, JSON_UNESCAPED_UNICODE)), true);
    }

    //异步跳转
    function request_by_curl($remote_server, $post_string = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}