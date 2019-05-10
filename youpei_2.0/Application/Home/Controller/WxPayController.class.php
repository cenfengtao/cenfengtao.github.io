<?php
namespace Home\Controller;

use Common\Controller\Wechat;
use Think\Controller;
use Think\Exception;

require_once __DIR__ . '/../../../ThinkPHP/Library/Vendor/ChuanglanSmsHelper/ChuanglanSmsApi.php';

class WxPayController extends BaseController
{
    public function jsapiIndex()
    {
        require_once VENDOR_PATH . 'WxPay/example/jsapi.php';
    }

    public function notify()
    {
        require_once VENDOR_PATH . 'WxPay/example/notify.php';
    }

    public function jsapiByActivity()
    {
        require_once VENDOR_PATH . 'WxPay/example/jsapiByActivity.php';
    }

    public function jsapiByGroup()
    {
        require_once VENDOR_PATH . 'WxPay/example/jsapiByGroup.php';
    }

    public function jsapiByParenting()
    {
        require_once VENDOR_PATH . 'WxPay/example/jsapiByParenting.php';
    }

    public function jsapiByBargain()
    {
        require_once VENDOR_PATH . 'WxPay/example/jsapiByBargain.php';
    }

    public function jsapiByIntegral()
    {
        require_once VENDOR_PATH . 'WxPay/example/jsapiByIntegral.php';
    }
    
    //查询订单状态
    public function check()
    {
        require_once VENDOR_PATH . 'WxPay/example/notify.php';
        if (!$_POST['out_trade_no'] || empty($_POST['out_trade_no'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单号错误'));
        }
        $record = D('PayRecord')->getRecordByOutNo($_POST['out_trade_no']);
        if (!$record) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '找不到该记录'));
        }
        $notify = new \PayNotifyCallBack();
        $result = $notify->Queryorder($_POST['out_trade_no']);
        if ($result) {
            //未支付，判断是否超时，关闭订单
            if ($result['trade_state'] == 'NOTPAY') {
                if ($record['create_time'] + 540 < time()) {
                    $closeResult = $notify->Closeorder($_POST['out_trade_no']);
                    if (!$closeResult) {
                        $this->ajaxReturn(array('status' => 0, 'msg' => '订单关闭失败'));
                    }
                    //修改订单状态并释放冻结优惠券和代金券，返还积分
                    if (!empty($record['order_id'])) {
                        D('Order')->updateById($record['order_id'], array('status' => 2, 'end_time' => time()));
                        D('PayRecord')->updateById($record['id'], array('status' => 2, 'end_time' => time()));
                        $order = D('Order')->find($record['order_id']);
                        if (!empty($order['coupon_id'])) {
                            D('Coupon')->updateById($order['coupon_id'], array('status' => 1));
                        }
                        if (!empty($order['cash_coupon_id'])) {
                            $cashCouponIds = explode(',', $order['cash_coupon_id']);
                            foreach ($cashCouponIds as $v) {
                                D('Coupon')->updateById($v, array('status' => 1));
                            }
                        }
                        if ($order['integral'] > 0) {
                            M('user')->where('id=' . $order['user_id'])->setInc('integral', $order['integral']);
                            M('integral_record')->where('order_id=' . $order['id'])->delete();
                        }
                        //返还库存
                        if(!empty($order['product_id'])){
                            $product = D('Product')->find($order['product_id']);
                            $newPrices = [];
                            $prices = json_decode($product['price'], true);
                            //扣除库存
                            foreach ($prices as $key => $val) {
                                $newPrices[$key] = $val;
                                if ($key == $order['key']) {
                                    $newPrices[$key]['count'] = $val['count'] + $order['amount'];
                                }
                            }
                            $newPrices = json_encode($newPrices);
                            D('Product')->updateById($product['id'], ['price' => $newPrices]);
                        }elseif (!empty($order['parenting_id'])){
                            M('parenting')->where('id='.$order['parenting_id'])->setInc('count',$order['amount']);
                        }elseif (!empty($order['activity_id'])){
                            M('organization_activity')->where('id='.$order['activity_id'])->setInc('max_people',$order['amount']);
                        }
                    }
                    $this->ajaxReturn(array('status' => 0, 'msg' => '订单已关闭,请重新交易'));
                } else {
                    $this->ajaxReturn(array('status' => 2, 'msg' => '订单未支付'));
                }
            }
            //判断订单支付状态
            if ($result['trade_state'] == 'SUCCESS' && array_key_exists("trade_state", $result)) {
                D('PayRecord')->updateById($record['id'], array('transaction_id' => $result['transaction_id'], 'status' => 1, 'end_time' => time()));
                //如果是收款 增加收入
                if ($record['type'] == 2) {
                    D('User')->updateById($this->user['id'], array('money' => $this->user['money'] + $record['fee']));
                }
                if (!empty($record['order_id'])) {
                    $order = D('Order')->find($record['order_id']);
                    D('Order')->updateById($order['id'], array('status' => 1, 'end_time' => time()));
                    //如有使用优惠券，把状态改回已使用并添加优惠券记录
                    if (!empty($order['coupon_id'])) {
                        D('Coupon')->updateById($order['coupon_id'], array('status' => 1));
                        $couponRecordData = array(
                            'user_id' => $this->user['id'],
                            'create_time' => time(),
                            'operate' => 1,
                            'coupon_id' => $order['coupon_id'],
                            'type' => 2,
                            'type_id' => $order['id']
                        );
                        D('CouponRecord')->insert($couponRecordData);
                    }
                    //如有使用代金券，把状态改回已使用并添加代金券记录
                    if (!empty($order['cash_coupon_id'])) {
                        $cashCouponIds = explode(',', $order['cash_coupon_id']);
                        foreach ($cashCouponIds as $val) {
                            D('Coupon')->updateById($val, array('status' => 1));
                            $couponRecordData = array(
                                'user' => $this->user['id'],
                                'create_time' => time(),
                                'coupon_id' => $val,
                                'operate' => 1,
                                'type' => 2,
                                'type_id' => $order['id']
                            );
                            D('CouponRecord')->insert($couponRecordData);
                        }
                    }
                    //如果有使用积分，把积分记录删除并返还积分
                    if ($order['integral'] > 0) {
                        M('user')->where('id=' . $order['user_id'])->setInc('integral', $order['integral']);
                        M('integral_record')->where('order_id=' . $order['id'])->delete();
                    }
                    //增加投票次数
                    $this->addVoteCount($order['total_price'], $order['id']);
                    //向管理员发送消息通知
                    $this->sendMsgToOrg($record['order_id']);
                    //发送模板通知
                    $this->sendTemplateByFinish($record['order_id']);
                }
                $this->ajaxReturn(array('status' => 1, 'msg' => '交易成功'));
            } else {
                $errMsg = $result['err_code_des'] ? $result['err_code_des'] : '返回状态码为空';
                D('PayRecord')->updateById($record['id'], array('status' => 3, 'end_time' => time(), 'err_msg' => $errMsg));
                if (!empty($record['order_id'])) {
                    D('Order')->updateById($record['order_id'], array('status' => 3, 'end_time' => time()));
                }
                if (!empty($order['coupon_id'])) {
                    D('Coupon')->updateById($order['coupon_id'], array('status' => 0));
                }
                if (!empty($order['cash_coupon_id'])) {
                    $cashCouponIds = explode(',', $order['cash_coupon_id']);
                    foreach ($cashCouponIds as $v) {
                        D('Coupon')->updateById($v, array('status' => 0));
                    }
                }
                $this->ajaxReturn(array('status' => 0, 'msg' => '交易失败,请重新交易'));
            }
        } else {
            D('PayRecord')->updateById($record['id'], array('status' => 3, 'end_time' => time(), 'err_msg' => '找不到该订单'));
            if (!empty($record['order_id'])) {
                D('Order')->updateById($record['order_id'], array('status' => 3, 'end_time' => time()));
            }
            if (!empty($order['coupon_id'])) {
                D('Coupon')->updateById($order['coupon_id'], array('status' => 1));
            }
            if (!empty($order['cash_coupon_id'])) {
                $cashCouponIds = explode(',', $order['cash_coupon_id']);
                foreach ($cashCouponIds as $v) {
                    D('Coupon')->updateById($v, array('status' => 1));
                }
            }
            $this->ajaxReturn(array('status' => 0, 'msg' => '支付失败,请重新下单'));
        }
    }

    //直接用优惠抵购全部金额支付或免费
    public function finishByCoupon()
    {
        if (!$_POST['orderId'] || !is_numeric($_POST['orderId'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单ID错误'));
        }
        //获取订单
        $order = D('Order')->find($_POST['orderId']);
        if (!$order) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '找不到该订单'));
        }
        if ($order['status'] != 0) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单状态已改变'));
        }
        //支付成功，修改订单状态
        D('Order')->UpdateById($order['id'], array('status' => 1, 'end_time' => time()));
        //如有使用优惠券，把状态改回已使用并添加优惠券记录
        if (!empty($order['coupon_id'])) {
            D('Coupon')->updateById($order['coupon_id'], array('status' => 3));
            $recordData = [
                'user_id' => $this->user['id'],
                'create_time' => time(),
                'coupon_id' => $order['coupon_id'],
                'operate' => 1,
                'type' => 2,
                'type_id' => $order['id'],
            ];
            D('CouponRecord')->insert($recordData);
        }
        //如有使用代金券，把状态改回已使用并添加代金券记录
        if (!empty($order['cash_coupon_id'])) {
            $cashCouponIds = explode(',', $order['cash_coupon_id']);
            foreach ($cashCouponIds as $v) {
                D('Coupon')->updateById($v, array('status' => 3));
                $recordData = [
                    'user_id' => $this->user['id'],
                    'create_time' => time(),
                    'coupon_id' => $v,
                    'operate' => 1,
                    'type' => 2,
                    'type_id' => $order['id']
                ];
                D('CouponRecord')->insert($recordData);
            }
        }
        $this->addVoteCount($order['total_price'], $order['id']);
        $this->sendMsgToOrg($order['id']);
        $this->sendTemplateByFinish($order['id']);
        $this->ajaxReturn(array('status' => 1, 'msg' => '支付成功'));
    }

    public function finishByIntegral()
    {
        if (!$_POST['orderId'] || !is_numeric($_POST['orderId'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单ID错误'));
        }
        //获取订单
        $order = D('Order')->find($_POST['orderId']);
        if (!$order) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '找不到该订单'));
        }
        if ($order['status'] != 0) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单状态已改变'));
        }
        //判断积分是否足够
        if ($order['integral'] > $this->user['integral']) {
            D('Order')->updateById($order['id'], array('status' => 3 , 'end_time' => time()));
            $this->ajaxReturn(array('status' => 0, 'msg' => '你的积分不足'));
        }
        M('user')->where(['id' => $order['user_id']])->setDec('integral', $order['integral']);
        //支付成功，修改订单状态
        D('Order')->UpdateById($order['id'], array('status' => 1, 'end_time' => time()));
        $this->addVoteCount($order['total_price'], $order['id']);
        $this->sendMsgToOrg($order['id']);
        $this->sendTemplateByFinish($order['id']);
        $this->ajaxReturn(array('status' => 1, 'msg' => '支付成功'));
    }

    //向机构管理员发送信息通知
    public function sendMsgToOrg($orderId)
    {
        $order = D('Order')->find($orderId);
        if (!empty($order['product_id'])) {
            $proTitle = M('product')->where("id={$order['product_id']}")->getField('title');
        } else if (!empty($order['parenting_id'])) {
            $proTitle = M('parenting')->where("id={$order['parenting_id']}")->getField('title');
        } else if (!empty($order['activity_id'])) {
            $proTitle = M('organization_activity')->where("id={$order['activity_id']}")->getField('title');
        } else if (!empty($order['group_record_id'])) {
            $groupId = M('group_record')->where("id={$order['group_record_id']}")->getField('group_id');
            $proTitle = M('group_product')->where("id={$groupId}")->getField('title');
        } else {
            return false;
        }
        $orgId = M('organization')->where(array('token' => $order['token']))->getField('id');
        $orgMobile = M('admin_user')->where(array('org_id' => $orgId, 'mobile' => array(array('exp', 'is not null'), array('neq', 0), 'and')))->getField('mobile');
        $sms = new \ChuanglanSmsApi();
        $msg = '用户 {$var} 已购买了商品 {$var}，付款金额{$var}元，请及时跟进！';
        $params = "{$orgMobile},{$this->user['username']},{$proTitle},{$order['total_price']}";
        $result = $sms->sendVariableSMS($msg, $params);
        $recordData = [
            'create_time' => time(),
            'content' => "用户 {$this->user['username']} 已购买了商品 {$proTitle}，付款金额{$order['total_price']}元，请及时跟进！",
            'type' => 1,
            'type_id' => $orderId,
            'mobile' => $orgMobile
        ];
        if (!is_null(json_decode($result))) {
            $output = json_decode($result, true);
            if (isset($output['code']) && $output['code'] == '0') {
                $recordData['status'] = 2;
                $recordData['err_code'] = $output['code'];
                return D('SmsRecord')->insert($recordData);
            } else {
                $recordData['status'] = 3;
                $recordData['err_code'] = $output['code'];
                $recordData['err_msg'] = $output['errorMsg'];
                return D('SmsRecord')->insert($recordData);
            }
        } else {
            $recordData['status'] = 3;
            $recordData['err_code'] = 'undefined';
            $recordData['err_msg'] = '发送失败';
            return D('SmsRecord')->insert($recordData);
        }
    }

    public function sendTemplateByFinish($orderId)
    {
        $order = D('Order')->find($orderId);
        if ($order['status'] != 1) {
            return false;
        }
        if (!empty($order['product_id'])) {
            $product = M('product')->where("id={$order['product_id']}")->field('desc cost', true)->find();
            //如果是课程就直接完成订单
            if ($product['type'] == 1) {
                M('order')->where('id=' . $orderId)->save(['status' => 4]);
            }
            $prices = M('product')->where("id={$order['product_id']}")->getField('price');
            $prices = json_decode($prices, true);
            $classNormal = $prices[$order['key']]['class_normal'];
            $proTitle = $product['title'] . " $classNormal";
        } else if (!empty($order['parenting_id'])) {
            $proTitle = M('parenting')->where("id={$order['parenting_id']}")->getField('title');
        } else if (!empty($order['activity_id'])) {
            $proTitle = M('organization_activity')->where("id={$order['activity_id']}")->getField('title');
        } else if (!empty($order['group_id'])) {
            $proTitle = M('group_product')->where("id={$order['group_id']}")->getField('title');
        } else {
            return false;
        }
        $orgTel = M('organization')->where(array("token" => $order['token']))->getField('tel');
        $first = '恭喜你购买成功！';
        $keyword1 = $order['code'];
        $keyword2 = $proTitle;
        $keyword3 = date("Y-m-d H:i:s", $order['create_time']);
        $remark = '';
        $url = "http://{$_SERVER['HTTP_HOST']}/index.php/Orders/queryDeliveryByPage?token=" . $this->token;
        $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__REMARK__');
        $infoFormat = array($this->user['open_id'], $url, $first, $keyword1, $keyword2, $keyword3, $remark);
        $wxuser = get_wxuser($this->token);
        execute_public_template('ADD_ORDER', $templeFormat, $infoFormat, $wxuser);
    }

    public function addVoteCount($totalPrice, $orderId)
    {
        $order = D('Order')->find($orderId);
        if ($order['product_id']) {
            $counts = $totalPrice / 2;  // *元代表一票
            $counts = floor($counts); //取整
            $product = M('bargain')->where(array('type' => 3, 'type_id' => $order['product_id'], 'key' => $order['key']))->find();
            if ($product) {
                $vote = M('vote')->where(array('id' => $product['vote_id']))->find();
                if ($vote['status'] == 4 || $vote['status'] == 5 || $vote['status'] == 6 || $vote['vote_end_time'] < time()) {
                    //活动结束
                    $count = $counts;
                } else {
                    //活动中
                    $count = $order['amount'] * $product['price'] + $counts;
                }
            }
        } else {
            $count = $totalPrice / 2;  // *元代表一票
            $count = floor($count); //取整
        }
        $data = [
            'create_time' => time(),
            'end_time' => strtotime(date("Y-m-d", time())) + (86400 * 3),
            'user_id' => $this->user['id'],
            'type' => 1,
            'type_id' => $orderId,
            'count' => $count,
            'remain_count' => $count,
        ];
        try {
            $id = D('VoteQuota')->insert($data);
            if ($id) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }
}