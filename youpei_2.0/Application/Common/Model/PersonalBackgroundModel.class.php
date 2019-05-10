<?php
namespace Common\Model;

class PersonalBackgroundModel extends BaseModel
{

    public function queryPicture()
    {
        $sql = "select * from __PREFIX__personal_background LIMIT 1";
        return $this->queryRow($sql);
    }

    public function insertbackground($token)
    {
        $rd = array('status' => -1);
        $data = array();
        $data["image"] = I("adFile");
        $data["is_show"] = (int)I("is_show");
        $data["create_time"] = time();
        $data["token"] = $token;
        if ($this->checkEmpty($data)) {
            $rs = $this->add($data);
            if (false !== $rs) {
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    //修改
    public function editbackground()
    {
        $rd = array('status' => -1);
        $id = (int)I("id", 0);
        $data["image"] = I("adFile");
        $data["is_show"] = (int)I("is_show");
        $data["create_time"] = time();
        if ($this->checkEmpty($data)) {
            $rs = $this->where("id=" . $id)->save($data);
            if (false !== $rs) {
                $rd['status'] = 1;
            }
        }
        return $rd;
    }

    /**
     * 删除
     */
    public function del()
    {
        $rd = array('status' => -1);
        $rs = $this->delete((int)I('id'));
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    public function exchangered($money, $userId)
    {
        $rd = array('status' => -1);
        $integral = $money * 100;
        if ($integral > 200) {
            $rd['msg'] = '超出兑换金额';
            return $rd;
        }

        $sql = "select id,integral,open_id,token from __PREFIX__user where id=" . $userId;
        $user = $this->queryRow($sql);
        if ($user ['integral'] < $integral) {
            $rd['msg'] = '积分不足,无法兑换';
            return $rd;
        }
        $date = strtotime(date("Y-m-d 00:00:00")); //时间戳
        $where['create_time'] = array('gt', $date);
        $where['user_id'] = array('eq', $userId);
        $where['integral_type'] = array('eq', '2');

        //每天限兑换2元
        $checkIntegral = M('integral_record')->where(array('create_time' => array('egt', $date),
            'user_id' => $userId, 'integral_type' => 2))->sum('integral');
        if ($checkIntegral + $integral > 200) {
            $rd['msg'] = '每天兑换金额不能超过2元';
            return $rd;
        }
        //每天限领一次
        // $sql = "SELECT * FROM `__PREFIX__integral_record` WHERE `create_time` > ".$date." AND `user_id` = '11' AND `integral_type` = '2'";
        // $res = $this->queryRow($sql);
        // if ($res) {
        //      $rd['msg'] = '红包每天限领一次';
        //     return $rd;
        // }
        if ($money < 1) {
            $rd['msg'] = '金额必须大于1元';
            return $rd;
        }
        if ($money > 5) {
            $money = 5;
        }

        //发送红包给你用
        $rs = self::_sendRedPacket($user, $money);
        if ($rs == 1) {
            M('user')->where(array('id' => $user['id']))->setDec('integral', $integral);
            $addSign ['user_id'] = $userId;
            $addSign ['integral'] = $integral;
            $addSign ['create_time'] = time();
            $addSign ['status'] = 1;
            $addSign ['type'] = 1;
            $addSign ['integral_type'] = 2;
            $addSign ['token'] = "g232238gc959";
            $addSign ['desc'] = '红包快速兑换';
            $add = M('integral_record')->add($addSign);
            if (false !== $add) {
                $rd['status'] = 1;
            }
            //发送通知模板
            $nowIntegral = D('User')->getIntegralById($userId);
            $first = '【优培圈】温馨提醒您的积分有变动';
            $keyword1 = '-' . $integral . '分';
            $keyword2 = '兑换红包';
            $keyword3 = date("Y-m-d H:i:s", time());
            $keyword4 = $nowIntegral . '分';
            $remark = '请点击“详情”查看具体内容';
            $url = "http://{$_SERVER['HTTP_HOST']}/index.php/MemberIntegral/integrallist?token=" . $user['token'];
            $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__KEYWORD4__', '__REMARK__');
            $infoFormat = array($user['open_id'], $url, $first, $keyword1, $keyword2, $keyword3, $keyword4, $remark);
            $wxuser = get_wxuser($user['token']);
            execute_public_template('INTEGRAL_CHANGE', $templeFormat, $infoFormat, $wxuser);
        } else {
            $rd = $rs;
        }
        return $rd;
    }

    public function _sendRedPacket($user, $money)
    {
        $rd = array('status' => -1);
        if ($money < 1) {
            $rd['msg'] = '兑换金额必须大于1元';
            return $rd;
        }
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
                throw new \Exception("兑换失败!系统发放红包已结束!");
            }
            //发送红包
            $WXPayTools = new \Common\Common\WXPayTools();
            $return = $WXPayTools::redPacket($user['open_id'], $money);
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
                throw new \Exception("兑换失败!");
            }
        } catch (\Exception $e) {
            M()->rollback();
            return array('status' => 0, 'msg' => $e->getMessage());
        }
    }
}