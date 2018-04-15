<?php
/**
 * Created by PhpStorm.
 * User: 新港西余文乐
 * Date: 2017/2/13
 * Time: 17:02
 */

namespace Common\Model;

use Think\Model;

class UserModel extends BaseModel
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('User');
    }

    public function getList($field = "*")
    {
        $list = $this->_db->field($field)->order('create_time desc')->select();
        return $list;
    }

    public function find($id)
    {
        $user = $this->_db->where("id={$id}")->find();
        return $user;
    }

    public function insert($data)
    {
        if (!$data || !is_array($data)) {
            throw_exception('添加数据不合法');
        }
        return $this->_db->add($data);
    }

    public function updateById($id, $data)
    {
        if (!isset($id) || !is_numeric($id)) {
            throw_exception('ID不合法');
        }
        if (!isset($data) || !is_array($data)) {
            throw_exception('更新数据不合法');
        }
        return $this->_db->where('id=' . $id)->save($data);
    }

    public function delete($id)
    {
        $id = $this->_db->where("id={$id}")->delete();
        return $id;
    }

    public function getNameById($id)
    {
        $name = $this->_db->where("id={$id}")->getField('username');
        return $name;
    }

    public function getIntegralById($id)
    {
        $integral = $this->_db->where("id={$id}")->getField('integral');
        return $integral;
    }

    public function getChildCount($id)
    {
        $count = $this->_db->where("up_user_id={$id}")->count();
        return $count;
    }

    public function getChildList($user_id)
    {
        $list = $this->_db->where('up_user_id=' . $user_id)->select();
        return $list;
    }

    public function getUserByOpenid($openid)
    {
        $user = $this->_db->where("open_id='{$openid}'")->find();
        return $user;
    }

    public function getHeadById($user_id)
    {
        $head = $this->_db->where("id={$user_id}")->getField('headimgurl');
        return $head;
    }

    public function queryByLotteryinfo($userid, $token, $activityid)
    {
        $isLottery = $this->userlimit($userid, $token, $activityid);
        if (!$isLottery) {
            M('User')->where("id={$userid}")->setInc('lottery_count');
            $re = M('temporary_draw')->where(array('userid' => $userid, 'token' => $token, 'activityid' => $activityid))->find();
            if (!$re) {
                // $row = M('user')->where(array('id' => $userid))->setInc('lottery_count');
                $data['userid'] = $userid;
                $data['token'] = $token;
                $data['activityid'] = $activityid;
                M('temporary_draw')->add($data);
//                 return 1;
            }
            return 1;
        }
        return 0;
    }

    public function getLotteryinfo($userid)
    {
        $Lotteryinfo = $this->_db->where("id={$userid}")->field('lottery_count')->find();
        return $Lotteryinfo;
    }

    public function getLotteryintegral($user)
    {
        return $this->_db->where(array('id' => $user['id']))->getField('integral');
    }

    public function confirmexchange($user, $num)
    {
        $rd = array('status' => -1);
        $integral_exchange = M('config')->where("id=1")->find();
        $integral = $num * $integral_exchange['integral_exchange'];
        $user = $this->_db->where("id='{$user['id']}'")->find();
        if ($user['integral'] < $integral) {
            $rd['msg'] = '积分不足,无法兑换';
            return $rd;
        }

        if ($num < 1) {
            $rd['msg'] = '兑换次数必须大于1次';
            return $rd;
        }
        // if ($num > 5) {
        //     $num = 5;
        // }
        M()->startTrans();
        $where['id'] = array('eq', $user['id']);
        if (M('user')->where($where)->setDec('integral', $integral)) {
            if (M('user')->where($where)->setInc('lottery_count', $num)) {
                $addSign['user_id'] = $user['id'];
                $addSign['integral'] = $integral;
                $addSign['create_time'] = time();
                $addSign['status'] = 1;
                $addSign['type'] = 1;
                $addSign['integral_type'] = 9;
                $addSign['token'] = $user['token'];
                $addSign['desc'] = '积分兑换抽奖次数';
                $add = M('integral_record')->add($addSign);
                if (false !== $add) {
                    M()->commit();
                    $rd['status'] = 1;
                }
            }
        } else {
            M()->rollback();
            $rd['msg'] = '兑换失败!';
            return $rd;
        }
        return $rd;
    }


    public function getLotteryResult($userid, $token, $activityid)
    {
        $rd = array('status' => -1);
        $where = array(
            'id' => $userid['id']
        );
        $user = M('user')->where($where)->find();
        if ($user['lottery_count'] <= 0) {
            $rd['msg'] = '点击下方抽奖说明获取更多抽奖机会';
            return $rd;
        }
        if (!empty($token) && $activityid > 0) {
            if (!$this->isStop($token, $activityid)) {
                $rd['msg'] = '该活动已结束';
                return $rd;
            }
            if (!$this->lotteryCount($token, $activityid)) {
                $rd['msg'] = '活动到期啦';
                return $rd;
            }

            if ($this->userlimit($userid['id'], $token, $activityid)) {
                $rd['msg'] = '今天已经抽过啦！';
                return $rd;
            }
            $limit = M('temporaryactivity')->where(array('id' => $activityid, 'token' => $token, 'status' => 1))->find();
            if (time() < $limit['start_time']) {
                $rd['msg'] = '亲，来早啦!本次的活动未开始，开始时间:' . date('Y-m-d', $limit['start_time']) . '至' . date('Y-m-d', $limit['end_time']);
                return $rd;
            }
            if (time() > $limit['end_time']) {
                $rd['msg'] = '活动已结束';
                return $rd;
            }
            $date = strtotime(date("Y-m-d 00:00:00")); //时间戳
            $w['activityid'] = $activityid;
            $w['token'] = $token;
            $w['status'] = 1;
            $w['create_time'] = array('gt', $date);
            $re = M('draw_record')->where($w)->count();
            if ($re != 0) {
                if ($re >= $limit['limit']) {
                    $rd['msg'] = '抽奖人数过多，请稍后再抽!';
                    return $rd;
                }
            }
            M('user')->where($where)->setInc('lottery_count', 1);
        }

        $addMoney = $this->countWeight($token, $activityid);
        if (empty($addMoney['yes']['title'])) {
            $rd['msg'] = '未设置奖品';
            return $rd;
        }
        //合并抽奖结果数组
        $data['no'] = array();
        foreach ($addMoney['no'] as $value) {
            $data['no'][] = array(
                'prizeid' => $value['id'],
                'title' => $value['title'],
                'pic' => $value['pic'],
            );
        }
        $data['yes'] = array(
            'prizeid' => $addMoney['yes']['id'],
            'title' => $addMoney['yes']['title'],
            'pic' => $addMoney['yes']['pic'],
        );

        //发放奖励
        if ($addMoney['yes']['type'] == 'red' && $addMoney['yes']['amount'] > 0) {
            //获取可发放红包余额
            $currentMoney = $this->getCurrentMoney();
            if ($currentMoney <= 0) {
                $rd['msg'] = '红包已经被抢光了！下一波红包请留意服务号消息！好友助力，红包抢不停！';
                return $rd;
            } else {
                M('user')->where($where)->setInc('money', $addMoney['yes']['amount']);
                $result = $this->lottery_red_envelope($user, $user['open_id'], $addMoney['yes']['amount']);
                if ($result['status'] == -1) {
                    M('user')->where($where)->setDec('money', $addMoney['yes']['amount']);
                    $rd['msg'] = '红包已经被抢光了！下一波红包请留意服务号消息！好友助力，红包抢不停！';
                    return $rd;
                }
                M('user')->where($where)->setDec('lottery_count', 1);
                M('lottery_prize')->where(array('type' => 'red', 'amount' => $addMoney['yes']['amount'], 'token' => $token, 'activityid' => $activityid, 'id' => $addMoney['yes']['id']))->setDec('count');
                //增加抽奖记录
                $datarecord = array(
                    'userid' => $userid['id'],
                    'token' => $token,
                    'username' => $user['username'],
                    'prize' => $addMoney['yes']['title'],
                    'create_time' => NOW_TIME,
                    'code' => substr(md5(uniqid() . $user['open_id']), 0, 8),
                    'prizeid' => $addMoney['yes']['id'],
                    'status' => 1,
                    'typeid' => 3,
                    'is_addr' => 0,
                    'activityid' => $activityid,
                    'sending_time' => NOW_TIME
                );
                $pid = M('draw_record')->add($datarecord);
                $data['pid'] = $pid;
                $rd['status'] = 1;
                $rd['msg'] = $data;
                return $rd;
            }
        } else if ($addMoney['yes']['type'] == 'integral' && $addMoney['yes']['amount'] > 0) {
            $config = M('config')->where("token='{$token}'")->find();
            $integral = $this->add_user_integral($userid['id'], $addMoney['yes']['amount'], $config['max_integral']);
            if ($integral['integral'] <= 0) {
                if (!empty($token) && $activityid > 0) {
                    M('user')->where($where)->setDec('lottery_count', 1);
                }
                $rd['msg'] = '今天获得积分超出上限！';
                return $rd;
            }
            M('user')->where($where)->setDec('lottery_count', 1);
            M('lottery_prize')->where(array('type' => 'integral', 'amount' => $addMoney['yes']['amount'], 'token' => $token, 'activityid' => $activityid, 'id' => $addMoney['yes']['id']))->setDec('count');
            M('user')->where(array('id' => $userid['id']))->setInc('integral', $addMoney['yes']['amount']);
            $re = array();
            $re['user_id'] = $userid['id'];
            $re['token'] = $token;
            $re['integral'] = $addMoney['yes']['amount'];
            $re['status'] = 1;
            $re['type'] = 2;
            $re['integral_type'] = 4;
            $re['desc'] = '抽奖送积分';
            $re["create_time"] = time();
            M('integral_record')->add($re);
            //增加抽奖记录
            $datarecord = array(
                'userid' => $userid['id'],
                'token' => $token,
                'username' => $user['username'],
                'prize' => $addMoney['yes']['title'],
                'create_time' => NOW_TIME,
                'code' => substr(md5(uniqid() . $user['open_id']), 0, 8),
                'prizeid' => $addMoney['yes']['id'],
                'status' => 1,
                'typeid' => 1,
                'is_addr' => 0,
                'activityid' => $activityid,
                'sending_time' => NOW_TIME
            );
            $pid = M('draw_record')->add($datarecord);
            //发送积分信息
            $first = '【优培圈】温馨提醒您的积分有变动';
            $keyword1 = '+' . $addMoney['yes']['amount'] . '分';
            $keyword2 = '抽奖送积分';
            $keyword3 = date("Y-m-d H:i:s", time());
            $keyword4 = $user['integral'] + $addMoney['yes']['amount'] . '分';
            $remark = '注册优培圈平台即可获取积分并兑换微信红包，邀请更多好友扫码支持即有机会直接抽取现金红包';
            $url = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/MemberIntegral/integrallist/token/" . $token;
            $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__KEYWORD4__', '__REMARK__');
            $infoFormat = array($user['open_id'], $url, $first, $keyword1, $keyword2, $keyword3, $keyword4, $remark);
            $wxuser = get_wxuser($token);
            execute_public_template('INTEGRAL_CHANGE', $templeFormat, $infoFormat, $wxuser);
            $data['pid'] = $pid;
            $rd['status'] = 1;
            $rd['msg'] = $data;
            return $rd;
        } else if ($addMoney['yes']['type'] == 'coupon' && $addMoney['yes']['amount'] > 0) {
            M('user')->where($where)->setDec('lottery_count', 1);
            M('lottery_prize')->where(array('type' => 'coupon', 'amount' => $addMoney['yes']['amount'], 'token' => $token, 'activityid' => $activityid, 'id' => $addMoney['yes']['id']))->setDec('count');
            $deadline = strtotime('+3 months', time());
            //转为当天0点时间戳
            $deadline = strtotime(date("Y-m-d", $deadline));
            $couponData = array(
                'user_id' => $user['id'],
                'token' => $token,
                'type' => 1,
                'title' => $addMoney['yes']['amount'],
                'create_time' => time(),
                'deadline' => $deadline,
                'status' => 0,
                'sncode' => $user['id'] . '_' . substr(md5(time()), 0, 8),
            );
            $couponId = M('coupon')->add($couponData);
            $couponLogData = array(
                'user_id' => $user['id'],
                'token' => $token,
                'coupon' => $addMoney['yes']['amount'],
                'operate' => '1',
                'type' => '6',
                'create_time' => time(),
                'coupon_id' => $couponId,
            );
            M('coupon_log')->add($couponLogData);
            //增加抽奖记录
            $datarecord = array(
                'userid' => $userid['id'],
                'token' => $token,
                'username' => $user['username'],
                'prize' => $addMoney['yes']['title'],
                'create_time' => NOW_TIME,
                'code' => substr(md5(uniqid() . $user['open_id']), 0, 8),
                'prizeid' => $addMoney['yes']['id'],
                'status' => 1,
                'typeid' => 4,
                'is_addr' => 0,
                'activityid' => $activityid,
                'sending_time' => NOW_TIME
            );
            $pid = M('draw_record')->add($datarecord);
            $data['pid'] = $pid;

            $rd['status'] = 1;
            $rd['msg'] = $data;
            return $rd;
        } else if ($addMoney['yes']['type'] == 'prize') {
            M('user')->where($where)->setDec('lottery_count', 1);
            M('lottery_prize')->where(array('type' => 'prize', 'title' => $addMoney['yes']['title'], 'token' => $token, 'activityid' => $activityid, 'id' => $addMoney['yes']['id']))->setDec('count');
            //增加抽奖记录
            $datarecord = array(
                'userid' => $userid['id'],
                'token' => $token,
                'username' => $user['username'],
                'prize' => $addMoney['yes']['title'],
                'create_time' => NOW_TIME,
                'code' => substr(md5(uniqid() . $user['open_id']), 0, 8),
                'prizeid' => $addMoney['yes']['id'],
                'status' => 1,
                'typeid' => 2,
                'is_addr' => 2,
                'activityid' => $activityid,
                'sending_time' => 0
            );
            $pid = M('draw_record')->add($datarecord);
            $data['pid'] = $pid;

            $rd['status'] = 2;
            $rd['msg'] = $data;
            return $rd;
        }
        return $rd;
    }

    public function getCurrentMoney()
    {
        $where['status'] = 1;
        $result = M('wxpayout_limit')->where($where)->order('sort desc,create_time desc')->getField('current_money');
        $result = empty($result) ? 0 : $result;
        return $result;
    }

    public function isStop($token, $activityid)
    {
        return M('temporaryactivity')->where(array('id' => $activityid, 'token' => $token, 'status' => 1))->find();
    }

    public function userlimit($userid, $token, $activityid)
    {
        $date = strtotime(date("Y-m-d 00:00:00")); //时间戳
        $where['userid'] = array('eq', $userid);
        $where['status'] = array('eq', 1);
        $where['token'] = array('eq', $token);
        $where['activityid'] = array('eq', $activityid);
        $where['create_time'] = array('gt', $date);
        $re = M('draw_record')->where($where)->find();
        return $re;
    }

    public function lotteryCount($token, $activityid)
    {

        $re = M('temporaryactivity')->where(array('token' => $token, 'id' => $activityid))->find();
        if (time() > $re['end_time']) {
            return false;
        }
        return true;
    }

    public function countWeight($token, $activityid)
    {
        $prize_arr = $this->probability($token, $activityid);
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['weight'];
        }

        $rid = $this->get_rand($arr); //根据概率获取奖项id

        $re = M('lottery_prize')->where(array('id' => $rid))->find();
        $res['yes'] = array(
            'id' => $re['id'],
            'pic' => $re['pic'],
            'title' => $re['title'],//中奖项
            'amount' => $re['amount'],
            'type' => $re['type'],
        );
        foreach ($prize_arr as $key => $val) {

            if ($val['id'] == $re['id']) {

                unset($prize_arr[$key]); //将中奖项从数组中剔除，剩下未中奖项
            }
        }

        shuffle($prize_arr); //打乱数组顺序
        for ($i = 0; $i < count($prize_arr); $i++) {

            $pr[] = array(
                'id' => $prize_arr[$i]['id'],
                'pic' => $prize_arr[$i]['pic'],
                'title' => $prize_arr[$i]['title'],
                'amount' => $prize_arr[$i]['amount'],
                'type' => $prize_arr[$i]['type']
            );
        }
        $res['no'] = $pr;
        return $res;
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
        unset($proArr);
        return $result;
    }

    public function probability($token, $activityid)
    {
        $list = M('lottery_prize')->where(array('token' => $token, 'activityid' => $activityid))->limit(6)->select();
        foreach ($list as $k => $v) {
            if ($v['count'] <= 0) {
                $exceptWeight = $v['weight'];

                M('lottery_prize')->where(array('id' => $v['id'], 'token' => $token, 'activityid' => $activityid))->save(array('weight' => 0));
                // M('lottery_prize')->where(array('type' => 'integral', 'amount' => '10', 'token' => $token, 'activityid' => $activityid))->setInc('weight', $exceptWeight);
                // $rs = M('lottery_prize')->where(array('token' => $token, 'activityid' => $activityid))->field("max(weight) weight,id")->find();
                $sql = " SELECT `id` FROM `yp_lottery_prize` WHERE `token` = '$token' AND `activityid` = $activityid AND count = (SELECT MIN(count) weight FROM `yp_lottery_prize` WHERE  `token` = '$token' AND `count` > 0 AND `activityid` = $activityid LIMIT 1 ) LIMIT 1";
                $rs = M()->query($sql);
                if ($rs) {
                    M('lottery_prize')->where(array('id' => $rs[0]['id'], 'token' => $token, 'activityid' => $activityid))->setInc('weight', $exceptWeight);
                }
            }
        }

        $newList = M('lottery_prize')->where(array('token' => $token, 'activityid' => $activityid))->limit(6)->select();
        return $newList;
    }

    ////抽红包
    public function lottery_red_envelope($user, $openId, $money)
    {
        $rd = array('status' => -1);
        // if ($money < 1) {
        //     $rd['msg'] = '兑换金额必须大于1元';
        //     return $rd;
        // }

        $where['status'] = 1;
        $limit = M('wxpayout_limit')->where($where)->order('sort desc,create_time desc')->find();

        if (!$limit) {
            $rd['msg'] = '系统未开启发放红包';
            return $rd;
        }

        if (time() < $limit['start_time']) {
            $rd['status'] = 2;
            $rd['msg'] = '亲，来早啦!本次的红包兑换未开始，开始时间:' . date('Y-m-d', $limit['start_time']) . '至' . date('Y-m-d', $limit['end_time']);
            return $rd;
        }

        if (time() > $limit['end_time']) {
            $rd['msg'] = '系统发放红包已结束';
            return $rd;
        }
        try {
            M()->startTrans();
            $status = M('wxpayout_limit')->where(array('id' => $limit['id'], 'current_money' => array('egt', $money)))->setDec('current_money', $money);
            if (!$status) {
                throw new Exception("兑换失败!系统发放红包已结束!");
            }
            $where['id'] = $user['id'];
            $where['money'] = array('egt', $money);
            if (M('user')->where($where)->setDec('money', $money)) {
                //发送红包
                $WXPayTools = new \Common\Common\WXPayTools();
                $return = $WXPayTools::redPacket($openId, $money);
                if ($return['code'] == 1) {
                    //add 兑换记录
                    $now_money = M('user')->where(array('id' => $user['id']))->getField('money');
                    $data = array(
                        'user_id' => $user['id'],
                        'integral' => 0,
                        'money' => $money,
                        'create_time' => NOW_TIME,
                        'type' => 2,
                        'money_before' => $now_money + $money,
                        'money_after' => $now_money,
                    );
                    M('redpacket')->add($data);
                    M()->commit();
                    return $rd['status'] = 1;
                } else if ($return['code'] == 4) {
                    //add 兑换记录
                    $now_money = M('user')->where(array('id' => $user['id']))->getField('money');
                    $data = array(
                        'user_id' => $user['id'],
                        'integral' => 0,
                        'money' => $money,
                        'create_time' => NOW_TIME,
                        'type' => 2,
                        'money_before' => $now_money + $money,
                        'money_after' => $now_money,
                        'status' => 0
                    );
                    M('redpacket')->add($data);
                    M()->commit();
                    return $rd['status'] = 1;
                } else {
                    throw new Exception("兑换失败!");
                }
            } else {
                throw new Exception("兑换失败!当前可用余额不足!");
            }
        } catch (Exception $e) {
            M()->rollback();
            return array('status' => -1, 'msg' => $e->getMessage());
        }
    }

    public function add_user_integral($userid, $integral, $max_integral)
    {
        // 今天已经获得的分数
        $date = strtotime(date("Y-m-d 00:00:00")); //时间戳
        $fetched = M('integral_record')->where(array(
            'user_id' => $userid,
            'type' => 2,
            'create_time' => array('egt', $date)
        ))->sum('integral');
        if ($integral > $max_integral) {
            $integral = $max_integral;
        }
        if ($max_integral == 0) {
            $data['integral'] = $integral;
        } else {
            $data['integral'] = min($integral, $max_integral - $fetched);
        }

        if ($data['integral'] <= 0) {
            $data['integral'] = 0;
        }
        return $data;
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

    public function getUpUser($userId)
    {
        $user = $this->find($userId);
        if ($user['up_user_id'] && !empty($user['up_user_id'])) {
            $upUser = $this->find($user['up_user_id']);
        }
        return $upUser ?: false;
    }

    public function getChildren($userId, $field = '*')
    {
        $children = $this->_db->field($field)->where("up_user_id={$userId}")->select();
        return $children;
    }


    public function isTodayInvite($userId)
    {
        $dayTime = strtotime(date("Y-m-d"));
        $result = $this->_db->where(array("up_user_id" => $userId, 'create_time' => array('EGT', $dayTime)))->find();
        return $result ? true : false;
    }

    //后台用户列表
    public function getListForAdmin($where, $page, $pageSize = 10, $field = '*')
    {
        $offset = ($page - 1) * $pageSize;
        $list = $this->_db->where($where)->field($field)->order('create_time desc')->limit($offset, $pageSize)->select();
        return $list;
    }

    public function getCount($where = array())
    {
        return $this->_db->where($where)->count();
    }

    public function getAllUser()
    {
        $sql = "SELECT `id`,`username`,`headimgurl`,`mobile`,`integral`,`money`,`last_login_time`,`last_ip` FROM `yp_user` WHERE `username` IS NOT NULL AND `username` <> '' ORDER BY create_time desc";
        $res = M()->query($sql);
        return $res;
    }

    public function getAllUserByToken($token)
    {
        $sql = "SELECT `id`,`username`,`headimgurl`,`mobile`,`integral`,`money`,`last_login_time`,`last_ip` FROM `yp_user` WHERE `token` = '$token' AND `username` IS NOT NULL AND `username` <> '' ORDER BY create_time desc";
        $res = M()->query($sql);
        return $res;
    }

    public function getAllUsername($id, $name)
    {
        $sql = "SELECT `username` FROM `yp_user` WHERE `username` LIKE '%$name%' AND `id` = '$id' AND `username` IS NOT NULL AND `username` <> '' ORDER BY create_time desc";
        $res = M()->query($sql);
        return $res;
    }
}