<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

class OrdersController extends BaseController
{
    public function allOrders()
    {
        $this->title = "全部订单";
        $list = M('order')->where(['status' => ['neq', 5], 'user_id' => $this->user['id']])->order('create_time desc')->select();
        foreach ($list as $k => $v) {
            if ($v['product_id'] && !empty($v['product_id'])) {
                //判断有否评价
                $isComment = M('product_comment')->where(['type' => 2, 'user_id' => $this->user['id'],
                    'product_id' => $v['product_id'], 'order_id' => $v['id']])->getField('id');
                if (!empty($isComment)) {
                    $list[$k]['isComment'] = 1;
                } else {
                    $list[$k]['isComment'] = 2;
                }
                $list[$k]['pro_title'] = D('Product')->getTitleById($v['product_id']);
                $list[$k]['pro_pic'] = D('Product')->getPicById($v['product_id']);
                $prices = M('product')->where("id={$v['product_id']}")->getField('price');
                $prices = json_decode($prices, true);
                $price = $prices[$v['key']];
                $list[$k]['pro_title'] .= ' ' . $price['class_normal'];
                //判断是否有砍价
                if (!empty($v['bargain_id'])) {
                    //判断砍价活动是否还有效
                    $isBargain = M('bargain')->where(['id' => $v['bargain_id'], 'start_time' => ['lt', time()],
                        'end_time' => ['gt', time()], 'key' => $v['key']])->find();
                    if ($isBargain) {
                        $list[$k]['price'] = $price['now_price'] - $v['bargain_price'];
                    } else {
                        //判断有否抢购价
                        $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                            'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                        $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                    }
                } else {
                    //判断有否抢购价
                    $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                        'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                    $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                }
            } elseif ($v['parenting_id'] && !empty($v['parenting_id'])) {
                //判断有否评价
                $isComment = M('product_comment')->where(['type' => 2, 'user_id' => $this->user['id'],
                    'parenting_id' => $v['parenting_id'], 'order_id' => $v['id']])->getField('id');
                if (!empty($isComment)) {
                    $list[$k]['isComment'] = 1;
                } else {
                    $list[$k]['isComment'] = 2;
                }
                $list[$k]['pro_title'] = M('parenting')->where("id={$v['parenting_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('parenting')->where("id={$v['parenting_id']}")->getField('image');
                $list[$k]['price'] = M('parenting')->where("id={$v['parenting_id']}")->getField('price');
            } else if ($v['activity_id'] && !empty($v['activity_id'])) {
                //判断有否评价
                $isComment = M('product_comment')->where(['type' => 2, 'user_id' => $this->user['id'],
                    'activity_id' => $v['activity_id'], 'order_id' => $v['id']])->getField('id');
                if (!empty($isComment)) {
                    $list[$k]['isComment'] = 1;
                } else {
                    $list[$k]['isComment'] = 2;
                }
                $list[$k]['pro_title'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('image');
                $list[$k]['price'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('price');
            } else if ($v['group_id'] && !empty($v['group_id'])) {
                //判断有否评价
                $isComment = M('product_comment')->where(['type' => 2, 'user_id' => $this->user['id'],
                    'group_id' => $v['group_id'], 'order_id' => $v['id']])->getField('id');
                if (!empty($isComment)) {
                    $list[$k]['isComment'] = 1;
                } else {
                    $list[$k]['isComment'] = 2;
                }
                $group = D('GroupProduct')->find($v['group_id']);
                $list[$k]['pro_title'] = $group['title'];
                $list[$k]['pro_pic'] = $group['image'];
                $list[$k]['price'] = $group['price'];
            }
            $list[$k]['is_appeal'] = D('Appeal')->isAppealByOrderId($v['id'], $this->user['id']);
            $list[$k]['is_comment'] = D('ProductComment')->isCommentByOrderId($this->user['id'], $v['id']);
        }
        $this->assign("umark", "allOrders")->assign('list', $list);
        $this->display();
    }

    public function queryPayByPage()
    {
        $this->title = "待付款";
        $list = D('Order')->getListByUserId($this->user['id'], '0');
        foreach ($list as $k => $v) {
            if ($v['product_id'] && !empty($v['product_id'])) {
                $list[$k]['pro_title'] = D('Product')->getTitleById($v['product_id']);
                $list[$k]['pro_pic'] = D('Product')->getPicById($v['product_id']);
                $prices = M('product')->where("id={$v['product_id']}")->getField('price');
                $prices = json_decode($prices, true);
                $price = $prices[$v['key']];
                $list[$k]['pro_title'] .= ' ' . $price['class_normal'];
                //判断是否有砍价
                if (!empty($v['bargain_id'])) {
                    //判断砍价活动是否还有效
                    $isBargain = M('bargain')->where(['id' => $v['bargain_id'], 'start_time' => ['lt', time()],
                        'end_time' => ['gt', time()], 'key' => $v['key']])->find();
                    if ($isBargain) {
                        $list[$k]['price'] = $price['now_price'] - $v['bargain_price'];
                    } else {
                        //判断有否抢购价
                        $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                            'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                        $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                    }
                } else {
                    //判断有否抢购价
                    $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                        'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                    $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                }
            } elseif ($v['parenting_id'] && !empty($v['parenting_id'])) {
                $list[$k]['pro_title'] = M('parenting')->where("id={$v['parenting_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('parenting')->where("id={$v['parenting_id']}")->getField('image');
                $list[$k]['price'] = M('parenting')->where("id={$v['parenting_id']}")->getField('price');
            } else if ($v['activity_id'] && !empty($v['activity_id'])) {
                $list[$k]['pro_title'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('image');
                $list[$k]['price'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('price');
            } else if ($v['group_id'] && !empty($v['group_id'])) {
                $group = D('GroupProduct')->find($v['group_id']);
                $list[$k]['pro_title'] = $group['title'];
                $list[$k]['pro_pic'] = $group['image'];
                $list[$k]['price'] = $group['price'];
            }
        }
        $this->assign("umark", "queryPayByPage")->assign('list', $list);
        $this->display('Orders/pay_list');
    }

    public function queryDeliveryByPage()
    {
        $this->title = "待收货";
        $list = D('Order')->getListByUserId($this->user['id'], '1');
        foreach ($list as $k => $v) {
            if ($v['product_id'] && !empty($v['product_id'])) {
                $list[$k]['pro_title'] = D('Product')->getTitleById($v['product_id']);
                $list[$k]['pro_pic'] = D('Product')->getPicById($v['product_id']);
                $prices = M('product')->where("id={$v['product_id']}")->getField('price');
                $prices = json_decode($prices, true);
                $price = $prices[$v['key']];
                $list[$k]['pro_title'] .= ' ' . $price['class_normal'];
                //判断是否有砍价
                if (!empty($v['bargain_id'])) {
                    //判断砍价活动是否还有效
                    $isBargain = M('bargain')->where(['id' => $v['bargain_id'], 'start_time' => ['lt', time()],
                        'end_time' => ['gt', time()], 'key' => $v['key']])->find();
                    if ($isBargain) {
                        $list[$k]['price'] = $price['now_price'] - $v['bargain_price'];
                    } else {
                        //判断有否抢购价
                        $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                            'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                        $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                    }
                } else {
                    //判断有否抢购价
                    $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                        'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                    $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                }
            } elseif ($v['parenting_id'] && !empty($v['parenting_id'])) {
                $list[$k]['pro_title'] = M('parenting')->where("id={$v['parenting_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('parenting')->where("id={$v['parenting_id']}")->getField('image');
                $list[$k]['price'] = M('parenting')->where("id={$v['parenting_id']}")->getField('price');
            } else if ($v['activity_id'] && !empty($v['activity_id'])) {
                $list[$k]['pro_title'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('image');
                $list[$k]['price'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('price');
            } else if ($v['group_id'] && !empty($v['group_id'])) {
                $group = D('GroupProduct')->find($v['group_id']);
                $list[$k]['pro_title'] = $group['title'];
                $list[$k]['pro_pic'] = $group['image'];
                $list[$k]['price'] = $group['price'];
            }
            $list[$k]['is_appeal'] = D('Appeal')->isAppealByOrderId($v['id'], $this->user['id']);
            $list[$k]['is_comment'] = D('ProductComment')->isCommentByOrderId($this->user['id'], $v['id']);
        }
        $this->assign("umark", "queryDeliveryByPage")->assign('list', $list);
        $this->display('Orders/list_delivery');
    }

    //待评价
    public function needCommentList()
    {
        $this->title = "待评价";
        $list = D('Order')->getListByUserId($this->user['id'], '4');
        foreach ($list as $k => $v) {
            if ($v['product_id'] && !empty($v['product_id'])) {
                //判断有否评价
                $isComment = M('product_comment')->where(['type' => 2, 'user_id' => $this->user['id'],
                    'product_id' => $v['product_id'], 'order_id' => $v['id']])->getField('id');
                if (!empty($isComment)) {
                    unset($list[$k]);
                    continue;
                }
                $list[$k]['pro_title'] = D('Product')->getTitleById($v['product_id']);
                $list[$k]['pro_pic'] = D('Product')->getPicById($v['product_id']);
                $prices = M('product')->where("id={$v['product_id']}")->getField('price');
                $prices = json_decode($prices, true);
                $price = $prices[$v['key']];
                $list[$k]['pro_title'] .= ' ' . $price['class_normal'];
                //判断是否有砍价
                if (!empty($v['bargain_id'])) {
                    //判断砍价活动是否还有效
                    $isBargain = M('bargain')->where(['id' => $v['bargain_id'], 'start_time' => ['lt', time()],
                        'end_time' => ['gt', time()], 'key' => $v['key']])->find();
                    if ($isBargain) {
                        $list[$k]['price'] = $price['now_price'] - $v['bargain_price'];
                    } else {
                        //判断有否抢购价
                        $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                            'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                        $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                    }
                } else {
                    //判断有否抢购价
                    $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                        'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                    $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                }
            } elseif ($v['parenting_id'] && !empty($v['parenting_id'])) {
                //判断有否评价
                $isComment = M('product_comment')->where(['type' => 2, 'user_id' => $this->user['id'],
                    'parenting_id' => $v['parenting_id'], 'order_id' => $v['id']])->getField('id');
                if (!empty($isComment)) {
                    unset($list[$k]);
                    continue;
                }
                $list[$k]['pro_title'] = M('parenting')->where("id={$v['parenting_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('parenting')->where("id={$v['parenting_id']}")->getField('image');
                $list[$k]['price'] = M('parenting')->where("id={$v['parenting_id']}")->getField('price');
            } else if ($v['activity_id'] && !empty($v['activity_id'])) {
                //判断有否评价
                $isComment = M('product_comment')->where(['type' => 2, 'user_id' => $this->user['id'],
                    'activity_id' => $v['activity_id'], 'order_id' => $v['id']])->getField('id');
                if (!empty($isComment)) {
                    unset($list[$k]);
                    continue;
                }
                $list[$k]['pro_title'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('image');
                $list[$k]['price'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('price');
            } else if ($v['group_id'] && !empty($v['group_id'])) {
                //判断有否评价
                $isComment = M('product_comment')->where(['type' => 2, 'user_id' => $this->user['id'],
                    'group_id' => $v['group_id'], 'order_id' => $v['id']])->getField('id');
                if (!empty($isComment)) {
                    unset($list[$k]);
                    continue;
                }
                $group = D('GroupProduct')->find($v['group_id']);
                $list[$k]['pro_title'] = $group['title'];
                $list[$k]['pro_pic'] = $group['image'];
                $list[$k]['price'] = $group['price'];
            }
            $list[$k]['is_appeal'] = D('Appeal')->isAppealByOrderId($v['id'], $this->user['id']);
            $list[$k]['is_comment'] = D('ProductComment')->isCommentByOrderId($this->user['id'], $v['id']);
        }
        $this->assign("umark", "needCommentList")->assign('list', $list);
        $this->display();
    }

    public function queryCancelOrders()
    {
        $this->title = "退换／售后";
        $list = M('order')->where(array('user_id' => $this->user['id'], 'status' => array(array('eq', '4'), array('eq', '6'), 'or')))->select();
        foreach ($list as $k => $v) {
            //判断有否申述中
            $isAppeal = M('appeal')->where(['user_id' => $this->user['id'], 'order_id' => $v['id']])->find();
            if (empty($isAppeal)) {
                unset($list[$k]);
                continue;
            }
            $list[$k]['appeal_status'] = $isAppeal['status'];
            $list[$k]['appeal_content'] = $isAppeal['content'];
            if ($v['product_id'] && !empty($v['product_id'])) {
                $list[$k]['pro_title'] = D('Product')->getTitleById($v['product_id']);
                $list[$k]['pro_pic'] = D('Product')->getPicById($v['product_id']);
                $prices = M('product')->where("id={$v['product_id']}")->getField('price');
                $prices = json_decode($prices, true);
                $price = $prices[$v['key']];
                $list[$k]['pro_title'] .= ' ' . $price['class_normal'];
                //判断是否有砍价
                if (!empty($v['bargain_id'])) {
                    //判断砍价活动是否还有效
                    $isBargain = M('bargain')->where(['id' => $v['bargain_id'], 'start_time' => ['lt', time()],
                        'end_time' => ['gt', time()], 'key' => $v['key']])->find();
                    if ($isBargain) {
                        $list[$k]['price'] = $price['now_price'] - $v['bargain_price'];
                    } else {
                        //判断有否抢购价
                        $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                            'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                        $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                    }
                } else {
                    //判断有否抢购价
                    $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $v['product_id'], 'key' => $v['key'],
                        'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                    $list[$k]['price'] = $rushPrice ?: $price['now_price'];
                }
            } elseif ($v['parenting_id'] && !empty($v['parenting_id'])) {
                $list[$k]['pro_title'] = M('parenting')->where("id={$v['parenting_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('parenting')->where("id={$v['parenting_id']}")->getField('image');
                $list[$k]['price'] = M('parenting')->where("id={$v['parenting_id']}")->getField('price');
            } else if ($v['activity_id'] && !empty($v['activity_id'])) {
                $list[$k]['pro_title'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('title');
                $list[$k]['pro_pic'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('image');
                $list[$k]['price'] = M('OrganizationActivity')->where("id={$v['activity_id']}")->getField('price');
            } else if ($v['group_id'] && !empty($v['group_id'])) {
                $group = D('GroupProduct')->find($v['group_id']);
                $list[$k]['pro_title'] = $group['title'];
                $list[$k]['pro_pic'] = $group['image'];
                $list[$k]['price'] = $group['price'];
            }
        }
        $this->assign("umark", "queryCancelOrders")->assign('list', $list);
        $this->display('Orders/list_cancel');
    }

    //重新支付
    public function payAgain()
    {
        $this->title = '重新支付';
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            return show(0, 'ID参数错误');
        }
        $order = D('Order')->find($_GET['id']);
        //判断商品价钱是否发生改变
        if (!empty($order['product_id'])) {
            $product = D('Product')->find($order['product_id']);
            $prices = json_decode($product['price'], true);
            $price = $prices[$order['key']];
            //判断是否有砍价
            if (!empty($order['bargain_id'])) {
                //判断砍价活动是否还有效
                $isBargain = M('bargain')->where(['id' => $order['bargain_id'], 'start_time' => ['lt', time()],
                    'end_time' => ['gt', time()], 'key' => $order['key']])->find();
                if ($isBargain) {
                    $product['price'] = $price['now_price'];
                    $type = 'bargain';
                } else {
                    //判断有否抢购价
                    $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $order['product_id'], 'key' => $order['key'],
                        'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                    $product['price'] = $rushPrice ?: $price['now_price'];
                    $type = 'product';
                }
            } else {
                //判断有否抢购价
                $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $order['product_id'], 'key' => $order['key'],
                    'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                $product['price'] = $rushPrice ?: $price['now_price'];
                $type = 'product';
            }
        } else if (!empty($order['parenting_id'])) {
            $product = D('Parenting')->find($order['parenting_id']);
            $type = 'parenting';
        } else if (!empty($order['activity_id'])) {
            $product = D('OrganizationActivity')->find($order['activity_id']);
            $type = 'activity';
        } else if (!empty($order['group_id'])) {
            $product = D('GroupProduct')->find($order['group_id']);
            $type = 'group';
        } else {
            return show(0, '找不到该商品');
        }
        $nowTotalPrice = $product['price'] * $order['amount'];
        if ($nowTotalPrice != $order['total_price']) {
            //修改优惠券状态为未使用
            if (!empty($order['coupon_id'])) {
                D('Coupon')->updateById($order['coupon_id'], array('status' => 1));
            }
            //修改代金券状态为未使用
            if (!empty($order['cash_coupon_id'])) {
                $cashCouponIds = explode(',', $order['cash_coupon_id']);
                foreach ($cashCouponIds as $v) {
                    D('Coupon')->updateById($v, array('status' => 1));
                }
            }
            D('Order')->updateById($order['id'], array('status' => 2));
            return show(0, '该订单已失效，请重新下单');
        }
        //判断支付时间是否超过9分钟
        $record = D('PayRecord')->getRecordByOrderId($order['id']);
        if ($record && $record['create_time'] + 540 < time() && in_array($record['status'], [0, 2])) {
            D('PayRecord')->updateById($record['id'], array('create_time' => time(), 'out_trade_no' => rand(1000, 9999) . '-' . date("YmdHis") . '-' . $this->user['id']));
        }
        return show(1, '订单正确', array('pro_type' => $type, 'order_id' => $order['id']));
    }

    public function addComment()
    {
        if ($_POST) {
            if (!$_POST['order_id'] || empty($_POST['order_id'])) {
                return show(0, '订单ID参数错误');
            }
            if (!$_POST['org_star'] || !$_POST['env_star'] || !$_POST['quality_star']) {
                return show(0, '请对评分项进行评分');
            }
            $order = D('Order')->find($_POST['order_id']);
            if (!empty($order['product_id'])) {
                $type = 'product_id';
                $typeId = $order['product_id'];
            } else if (!empty($order['parenting_id'])) {
                $type = 'parenting_id';
                $typeId = $order['parenting_id'];
            } else if (!empty($order['activity_id'])) {
                $type = 'activity_id';
                $typeId = $order['activity_id'];
            } else if (!empty($order['group_id'])) {
                $type = 'group_id';
                $typeId = $order['group_id'];
            } else {
                $type = 'undefined';
                $typeId = 0;
            }
            $insertData = array(
                'order_id' => $_POST['order_id'],
                'user_id' => $this->user['id'],
                'create_time' => time(),
                'status' => 1,
                'type' => 2,
                'content' => $_POST['content'],
                'org_star' => $_POST['org_star'],
                'env_star' => $_POST['env_star'],
                'quality_star' => $_POST['quality_star'],
                'token' => $this->token,
            );
            $insertData[$type] = $typeId;
            $id = D('ProductComment')->insert($insertData);
            if (!$id || empty($id)) {
                return show(0, '评论失败');
            } else {
                return show(1, '评论成功');
            }
        } else {
            $this->title = "评价页面";
            if (empty($_GET['id'])) {
                $this->error('参数错误');
            }
            $this->assign('id', $_GET['id']);
            $this->display();
        }
    }

    //添加订单
    public function addOrder()
    {
        if (!$_POST['pro_id'] || !is_numeric($_POST['pro_id'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '商品ID不正确'));
        }
        if (!$_POST['amount'] || !is_numeric($_POST['amount']) || $_POST['amount'] < 1) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '商品数量不正确'));
        }
        if (!$_POST['key']) {
            $this->ajaxReturn(['status' => 0, 'msg' => '商品规格不正确']);
        }
        $product = D('Product')->find($_POST['pro_id']);
        if ($product['is_mail'] == 2 && empty($_POST['address'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '地址不能为空'));
        }
        $prices = json_decode($product['price'], true);
        $price = $prices[$_POST['key']]['now_price'];
        $count = $prices[$_POST['key']]['count'];
        if ($count < $_POST['amount']) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '当前库存为' . $count . '，请重新选择'));
        }
        //判断有否抢购价
        $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $_POST['pro_id'], 'key' => $_POST['key'],
            'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
        if ($rushPrice) {
            $price = $rushPrice;
        }
        $totalPrice = $price * $_POST['amount'];
        //使用积分
        $integral = $_POST['integral'] ?: 0;
        $nowIntegral = M('user')->where("id={$this->user['id']}")->getField('integral');
        if ($nowIntegral < $integral) {
            return show(0, '积分参数错误');
        }
        $res = M('user')->where("id={$this->user['id']}")->setDec('integral', $integral);
        $couponPrice = 0;
        //优惠券 (修改优惠券状态为使用中)
        if (!empty($_POST['coupon_id'])) {
            $offerId = M('coupon')->where(['id' => $_POST['coupon_id']])->getField('offer_id');
            $offer = D('CouponOffer')->find($offerId);
            if ($offer['full'] > $totalPrice) {
                return show(0, '优惠券参数错误');
            }
            M('coupon')->where("id={$_POST['coupon_id']}")->save(['status' => 2]);
            $couponPrice = $offer['subtract'];
        }
        //代金券 (修改代金券状态为使用中)
        $cashCouponPrice = 0;
        if (!empty($_POST['cash_coupon_ids'])) {
            foreach ($_POST['cash_coupon_ids'] as $val) {
                $offerId = M('coupon')->where(['id' => $val])->getField('offer_id');
                $fee = M('coupon_offer')->where("id={$offerId}")->getField('fee');
                $cashCouponPrice += $fee;
                M('coupon')->where("id={$val}")->save(['status' => 2]);
            }
        }
        if ($cashCouponPrice > ($totalPrice - $couponPrice)) {
            $cashCouponPrice = $totalPrice - $couponPrice;
        }
        //实际支付价钱
        $realPrice = $totalPrice - $couponPrice - $cashCouponPrice - $integral * 0.01;
        $realPrice = $realPrice > 0 ? $realPrice : 0;
        $orderData = [
            'user_id' => $this->user['id'],
            'product_id' => $product['id'],
            'create_time' => time(),
            'amount' => $_POST['amount'],
            'total_price' => $totalPrice,
            'coupon_price' => $couponPrice,
            'real_price' => $realPrice,
            'coupon_id' => $_POST['coupon_id'] ?: '',
            'cash_coupon_id' => $_POST['cash_coupon_ids'] ? implode(',', $_POST['cash_coupon_ids']) : '',
            'cash_coupon_price' => $cashCouponPrice,
            'status' => 0,
            'code' => $this->user['id'] . substr(time(), 4, 11),
            'token' => $product['token'],
            'key' => $_POST['key'],
            'integral' => $_POST['integral']
        ];
        $id = D('Order')->insert($orderData);
        if ($id) {
            $newPrices = [];
            //扣除库存
            foreach ($prices as $key => $val) {
                $newPrices[$key] = $val;
                if ($key == $_POST['key']) {
                    $newPrices[$key]['count'] = $val['count'] - $_POST['amount'];
                }
            }
            $newPrices = json_encode($newPrices);
            D('Product')->updateById($_POST['pro_id'], ['price' => $newPrices]);
            $data['userid'] = $this->user['id'];
            $data['orderid'] = $id;
            $data['name'] = $_POST['name'];
            $data['mobile'] = $_POST['mobile'];
            $data['message'] = $_POST['message'];
            $data['address'] = $_POST['address'] ?: '';
            $rs = M('order_info')->add($data);
            if ($res && $integral > 0) {
                $data = [
                    'user_id' => $this->user['id'],
                    'token' => $this->token,
                    'integral' => $integral,
                    'create_time' => time(),
                    'status' => 1,
                    'type' => 1,
                    'integral_type' => 10,
                    'order_id' => $id,
                    'desc' => '支付抵扣积分'
                ];
                M('integral_record')->add($data);
            }
            if (false !== $rs) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '订单创建成功', 'orderId' => $id));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单创建失败'));
        }
    }

    //助力折扣专区
    public function addSaleOrder()
    {
        if (!$_POST['pro_id']) {
            return show(0, '商品参数错误');
        }
        if (!$_POST['sale_id']) {
            return show(0, '折扣参数错误');
        }
        if (!$_POST['name']) {
            return show(0, '请填写联系人');
        }
        if (!$_POST['mobile']) {
            return show(0, '请填写手机号码');
        }
        if (!$_POST['address']) {
            return show(0, '请填写地址信息');
        }
        //判断是否有资格购买该折扣商品
        $sale = D('QrcodeSale')->find($_POST['sale_id']);
        $sceneId = M('qrcode')->where(['id' => $sale['qrcode_id']])->getField('scene_id');
        $templateTimeStr = $sale['template_number'] == 1 ? 'template_a_times' : ($sale['template_number']
        == 2 ? 'template_b_times' : 'template_c_times');
        $scanQuantity = M('scan_reply')->where(['scene_id' => $sceneId])->getField($templateTimeStr);
        //查询扫码次数
        $scanCount = M('qrcode_record')->where(['scene_id' => $sceneId, 'share_user_id' => $this->user['id']])->count();
        if ($scanCount < $scanQuantity) {
            return show(0, '你的助力次数不足哦');
        }
        $isOrder = M('order')->where(['qrcode_id' => $sale['qrcode_id'], 'template_number' => $sale['template_number'],
            'user_id' => $this->user['id'], 'status' => array(['eq', 0], ['eq', 1], ['eq', 4], 'or')])->getField('id');
        if ($isOrder) {
            return show(0, '你已经购买过了');
        }
        //判断库存
        if ($sale['count'] <= 0) {
            return show(0, '该商品库存已清空');
        }
        $productToken = M('product')->where(['id' => $_POST['pro_id']])->getField('token');
        $orderData = [
            'user_id' => $this->user['id'],
            'product_id' => $_POST['pro_id'],
            'create_time' => time(),
            'amount' => 1,
            'total_price' => $sale['price'],
            'coupon_price' => 0,
            'real_price' => $sale['price'],
            'status' => 0,
            'token' => $productToken,
            'code' => $this->user['id'] . substr(time(), 4, 11),
            'key' => $sale['key'],
            'qrcode_id' => $sale['qrcode_id'],
            'template_number' => $sale['template_number'],
        ];
        $id = D('Order')->insert($orderData);
        if ($id) {
            M('qrcode_sale')->where(['id' => $_POST['sale_id']])->setDec('count', 1);
            $infoData = [
                'userid' => $this->user['id'],
                'orderid' => $id,
                'name' => $_POST['name'],
                'mobile' => $_POST['mobile'],
                'message' => $_POST['message'],
                'address' => $_POST['address'] ?: '',
            ];
            M('order_info')->add($infoData);
            return show(1, '订单创建成功', ['orderId' => $id]);
        } else {
            return show(0, '订单创建失败');
        }
    }

    //积分专区
    public function addIntegralOrder()
    {
        if (!$_POST['bargainId']) {
            return show(0, '商品参数错误');
        }
        if (!$_POST['name']) {
            return show(0, '请填写联系人');
        }
        if (!$_POST['mobile']) {
            return show(0, '请填写手机号码');
        }
        if (!$_POST['address']) {
            return show(0, '请填写地址信息');
        }
        $bargain = D('Bargain')->find($_POST['bargainId']);
        //判断积分是否足够
        if ($this->user['integral'] < intval($bargain['price'])) {
            return show(0, '你的积分不足哦');
        }
        $prices = M('product')->where(['id' => $bargain['type_id']])->getField('price');
        $prices = json_decode($prices, true);
        $originalPrice = $prices[$bargain['key']]['now_price'];
        $orderData = [
            'user_id' => $this->user['id'],
            'product_id' => $bargain['type_id'],
            'create_time' => time(),
            'amount' => 1,
            'total_price' => $originalPrice,
            'coupon_price' => 0,
            'real_price' => 0,
            'status' => 0,
            'token' => $bargain['token'],
            'code' => $this->user['id'] . substr(time(), 4, 11),
            'key' => $bargain['key'],
            'bargain_id' => $bargain['id'],
            'integral' => intval($bargain['price']),
        ];
        $id = D('Order')->insert($orderData);
        if ($id) {
            $infoData = [
                'userid' => $this->user['id'],
                'orderid' => $id,
                'name' => $_POST['name'],
                'mobile' => $_POST['mobile'],
                'message' => $_POST['message'],
                'address' => $_POST['address'] ?: '',
            ];
            M('order_info')->add($infoData);
            return show(1, '订单创建成功', ['orderId' => $id]);
        } else {
            return show(0, '订单创建失败');
        }
    }

    //取消订单
    public function cancelOrder()
    {
        if (!$_POST['order_id'] || !is_numeric($_POST['order_id'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单ID参数错误'));
        }
        try {
            $order = D("Order")->find($_POST['order_id']);
            if ($order['status'] != 0) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '该订单不能取消'));
            }
            //修改优惠券状态为未使用
            if (!empty($order['coupon_id'])) {
                D('Coupon')->updateById($order['coupon_id'], array('status' => 1));
            }
            //修改代金券状态为未使用
            if (!empty($order['cash_coupon_id'])) {
                $cashCouponIds = explode(',', $order['cash_coupon_id']);
                foreach ($cashCouponIds as $v) {
                    D('Coupon')->updateById($v, array('status' => 1));
                }
            }
            //返还抵扣积分并删除积分记录
            if ($order['integral'] > 0) {
                M('user')->where('id=' . $order['user_id'])->setInc('integral', $order['integral']);
                M('integral_record')->where('order_id=' . $order['id'])->delete();
            }
            //返还库存
            if (!empty($order['product_id'])) {
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
            } elseif (!empty($order['parenting_id'])) {
                M('parenting')->where('id=' . $order['parenting_id'])->setInc('count', $order['amount']);
            } elseif (!empty($order['activity_id'])) {
                M('organization_activity')->where('id=' . $order['activity_id'])->setInc(';
                ', $order['amount']);
            }
            //修改订单状态
            $id = D('Order')->updateById($order['id'], array('status' => '2', 'end_time' => time()));
            if ($id) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '取消成功'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => '取消失败'));
            }
        } catch (Exception $e) {
            $this->ajaxReturn(array('status' => 0, 'msg' => $e->getMessage()));
        }
    }

    //删除订单
    public function deleteOrder()
    {
        if (!$_POST['order_id'] || !is_numeric($_POST['order_id'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单ID参数错误'));
        }
        try {
            $order = D("Order")->find($_POST['order_id']);
            if ($order['status'] == 0) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '请先取消订单再删除'));
            }
            $id = D('Order')->updateById($order['id'], array('status' => 5, 'end_time' => time()));
            if ($id) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '删除成功'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => '删除失败'));
            }
        } catch (Exception $e) {
            $this->ajaxReturn(array('status' => 0, 'msg' => $e->getMessage()));
        }
    }

    //添加机构活动订单
    public function addActivityOrder()
    {
        if (!$_POST['id'] || !is_numeric($_POST['id'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => 'ID参数错误'));
        }
        if (!$_POST['amount'] || $_POST['amount'] <= 0) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '商品数量不正确'));
        }
        $activity = D('OrganizationActivity')->find($_POST['id']);
        if ($activity['max_people'] < $_POST['amount']) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '当前库存为' . $activity['max_people'] . '，请重新选择'));
        }
        $totalPrice = $activity['price'] * $_POST['amount'];
        //使用积分
        $integral = $_POST['integral'] ?: 0;
        $nowIntegral = M('user')->where("id={$this->user['id']}")->getField('integral');
        if ($nowIntegral < $integral) {
            return show(0, '积分参数错误');
        }
        $id = M('user')->where("id={$this->user['id']}")->setDec('integral', $integral);
        $couponPrice = 0;
        //优惠券 (修改优惠券状态为使用中)
        if (!empty($_POST['coupon_id'])) {
            $offerId = M('coupon')->where(['id' => $_POST['coupon_id']])->getField('offer_id');
            $offer = D('CouponOffer')->find($offerId);
            if ($offer['full'] > $totalPrice) {
                return show(0, '优惠券参数错误');
            }
            M('coupon')->where("id={$_POST['coupon_id']}")->save(['status' => 2]);
            $couponPrice = $offer['subtract'];
        }
        //代金券 (修改代金券状态为使用中)
        $cashCouponPrice = 0;
        if (!empty($_POST['cash_coupon_ids'])) {
            foreach ($_POST['cash_coupon_ids'] as $val) {
                $offerId = M('coupon')->where(['id' => $val])->getField('offer_id');
                $fee = M('coupon_offer')->where("id={$offerId}")->getField('fee');
                $cashCouponPrice += $fee;
                M('coupon')->where("id={$val}")->save(['status' => 2]);
            }
        }
        //实际支付价钱
        $realPrice = $totalPrice - $couponPrice - $cashCouponPrice - $integral / 100;
        $realPrice = $realPrice > 0 ? $realPrice : 0;
        $orderData = [
            'user_id' => $this->user['id'],
            'create_time' => time(),
            'amount' => $_POST['amount'],
            'total_price' => $totalPrice,
            'real_price' => $realPrice,
            'activity_id' => $activity['id'],
            'token' => $this->token,
            'coupon_price' => $couponPrice,
            'coupon_id' => $_POST['coupon_id'] ?: '',
            'cash_coupon_id' => $_POST['cash_coupon_ids'] ? implode(',', $_POST['cash_coupon_ids']) : '',
            'cash_coupon_price' => $cashCouponPrice,
            'status' => 0,
            'order_number' => $this->user['id'] . substr(time(), 4, 11),
            'integral' => $integral
        ];
        $orderId = D('Order')->insert($orderData);
        if ($orderId) {
            //扣除库存
            M('organization_activity')->where('id=' . $activity['id'])->setDec('max_people', $_POST['amount']);
            $data['userid'] = $this->user['id'];
            $data['orderid'] = $orderId;
            $data['name'] = $_POST['name'];
            $data['mobile'] = $_POST['mobile'];
            $data['message'] = $_POST['message'] ?: '';
            $data['address'] = $_POST['address'] ?: '';
            $rs = M('order_info')->add($data);
            if ($id && $integral > 0) {
                $data = [
                    'user_id' => $this->user['id'],
                    'token' => $this->token,
                    'integral' => $integral,
                    'create_time' => time(),
                    'status' => 1,
                    'type' => 1,
                    'integral_type' => 10,
                    'order_id' => $orderId,
                    'desc' => '购买机构活动抵扣积分'
                ];
                M('integral_record')->add($data);
            }
            if (false !== $rs) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '订单创建成功', 'orderId' => $orderId));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单创建失败'));
        }
    }

    //添加团购订单
    public function addGroupOrder()
    {
        if (!$_POST['groupId'] || !is_numeric($_POST['groupId'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => 'ID参数错误'));
        }
        if (!$_POST['amount'] || $_POST['amount'] <= 0) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '购买人数不正确'));
        }
        $group = D('GroupProduct')->find($_POST['groupId']);
        //判断是否还有库存
        $nowCount = M('order')->where(['group_id' => $_POST['groupId'], 'status' => array(['eq', '0'], ['eq', '1'], ['eq', '4'])])->sum('amount');
        $canCount = $group['max_people'] - $nowCount;
        if ($canCount - $_POST['amount'] < 0) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '库存已不足'));
        }
        $totalPrice = $group['price'] * $_POST['amount'];
        //使用积分
        $integral = $_POST['integral'] ?: 0;
        $nowIntegral = M('user')->where("id={$this->user['id']}")->getField('integral');
        if ($nowIntegral < $integral) {
            return show(0, '积分参数错误');
        }
        M('user')->where("id={$this->user['id']}")->setDec('integral', $integral);
        //代金券 (修改代金券状态为使用中)
        $cashCouponPrice = 0;
        if (!empty($_POST['cash_coupon_ids'])) {
            foreach ($_POST['cash_coupon_ids'] as $val) {
                $offerId = M('coupon')->where(['id' => $val])->getField('offer_id');
                $fee = M('coupon_offer')->where("id={$offerId}")->getField('fee');
                $cashCouponPrice += $fee;
                M('coupon')->where("id={$val}")->save(['status' => 2]);
            }
        }
        //实际支付价钱
        $realPrice = $totalPrice - $cashCouponPrice - $integral / 100;
        $realPrice = $realPrice > 0 ? $realPrice : 0;
        $orderData = [
            'user_id' => $this->user['id'],
            'create_time' => time(),
            'amount' => $_POST['amount'],
            'total_price' => $totalPrice,
            'real_price' => $realPrice,
            'group_id' => $_POST['groupId'],
            'token' => $this->token,
            'status' => 0,
            'cash_coupon_id' => $_POST['cash_coupon_ids'] ? implode(',', $_POST['cash_coupon_ids']) : '',
            'cash_coupon_price' => $cashCouponPrice,
            'share_user_id' => $_POST['shareUserId'],
        ];
        $orderId = D('Order')->insert($orderData);
        if ($orderId) {
            $data['userid'] = $this->user['id'];
            $data['orderid'] = $orderId;
            $data['name'] = $_POST['name'];
            $data['mobile'] = $_POST['mobile'];
            $data['message'] = $_POST['message'] ?: '';
            $data['address'] = $_POST['address'] ?: '';
            $rs = M('order_info')->add($data);
            if ($integral > 0) {
                $data = [
                    'user_id' => $this->user['id'],
                    'token' => $this->token,
                    'integral' => $integral,
                    'create_time' => time(),
                    'status' => 1,
                    'type' => 1,
                    'integral_type' => 10,
                    'order_id' => $orderId,
                    'desc' => '购买团购抵扣积分'
                ];
                M('integral_record')->add($data);
            }
            if (false !== $rs) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '订单创建成功', 'orderId' => $orderId));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单创建失败'));
        }
    }

    //添加亲子活动订单
    public function addParentingOrder()
    {
        if (!$_POST['par_id'] || !is_numeric($_POST['par_id'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => 'ID参数错误'));
        }
        if (!$_POST['amount'] || $_POST['amount'] <= 0) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '购买数量不正确'));
        }
        $Parenting = D('Parenting')->find($_POST['par_id']);
        if ($Parenting['count'] < $_POST['amount']) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '当前库存为' . $Parenting['count'] . '，请重新选择'));
        }
        $totalPrice = $Parenting['price'] * $_POST['amount'];
        //使用积分
        $integral = $_POST['integral'] ?: 0;
        $nowIntegral = M('user')->where("id={$this->user['id']}")->getField('integral');
        if ($nowIntegral < $integral) {
            return show(0, '积分参数错误');
        }
        $id = M('user')->where("id={$this->user['id']}")->setDec('integral', $integral);
        $couponPrice = 0;
        //优惠券 (修改优惠券状态为使用中)
        if (!empty($_POST['coupon_id'])) {
            $offerId = M('coupon')->where(['id' => $_POST['coupon_id']])->getField('offer_id');
            $offer = D('CouponOffer')->find($offerId);
            if ($offer['full'] > $totalPrice) {
                return show(0, '优惠券参数错误');
            }
            M('coupon')->where("id={$_POST['coupon_id']}")->save(['status' => 2]);
            $couponPrice = $offer['subtract'];
        }
        //代金券 (修改代金券状态为使用中)
        $cashCouponPrice = 0;
        if (!empty($_POST['cash_coupon_ids'])) {
            foreach ($_POST['cash_coupon_ids'] as $val) {
                $offerId = M('coupon')->where(['id' => $val])->getField('offer_id');
                $fee = M('coupon_offer')->where("id={$offerId}")->getField('fee');
                $cashCouponPrice += $fee;
                M('coupon')->where("id={$val}")->save(['status' => 2]);
            }
        }
        //实际支付价钱
        $realPrice = $totalPrice - $couponPrice - $cashCouponPrice - $integral / 100;
        $realPrice = $realPrice > 0 ? $realPrice : 0;
        $orderData = [
            'user_id' => $this->user['id'],
            'parenting_id' => $Parenting['id'],
            'create_time' => time(),
            'amount' => $_POST['amount'],
            'total_price' => $totalPrice,
            'coupon_price' => $couponPrice,
            'real_price' => $realPrice,
            'coupon_id' => $_POST['coupon_id'] ?: '',
            'cash_coupon_id' => $_POST['cash_coupon_ids'] ? implode(',', $_POST['cash_coupon_ids']) : '',
            'cash_coupon_price' => $cashCouponPrice,
            'status' => 0,
            'code' => $this->user['id'] . substr(time(), 4, 11),
            'token' => $Parenting['token'],
            'integral' => $integral
        ];
        $orderId = D('Order')->insert($orderData);
        if ($orderId) {
            //扣除库存
            M('parenting')->where('id=' . $Parenting['id'])->setDec('count', $_POST['amount']);
            $data['userid'] = $this->user['id'];
            $data['orderid'] = $orderId;
            $data['name'] = $_POST['name'];
            $data['mobile'] = $_POST['mobile'];
            $data['message'] = $_POST['message'] ?: '';
            $data['address'] = $_POST['address'] ?: '';
            $rs = M('order_info')->add($data);
            if ($id && $integral > 0) {
                $data = [
                    'user_id' => $this->user['id'],
                    'token' => $this->token,
                    'integral' => $integral,
                    'create_time' => time(),
                    'status' => 1,
                    'type' => 1,
                    'integral_type' => 10,
                    'order_id' => $orderId,
                    'desc' => '购买亲子活动抵扣积分'
                ];
                M('integral_record')->add($data);
            }
            if (false !== $rs) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '订单创建成功', 'orderId' => $orderId));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单创建失败'));
        }
    }

    //添加砍价订单
    public function addBargainOrder()
    {
        if (!$_POST['pro_id'] || !is_numeric($_POST['pro_id'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '商品ID不正确'));
        }
        if (!$_POST['amount'] || !is_numeric($_POST['amount']) || $_POST['amount'] < 1) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '商品数量不正确'));
        }
        if (!$_POST['key']) {
            $this->ajaxReturn(['status' => 0, 'msg' => '商品规格不正确']);
        }
        $product = D('Product')->find($_POST['pro_id']);
        if ($product['is_mail'] == 2 && empty($_POST['address'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '地址不能为空'));
        }
        //判断砍价
        $bargain = M('Bargain')->where(array('type' => 1, 'type_id' => $_POST['pro_id'], 'key' => $_POST['key'],
            'start_time' => array('LT', time()), 'end_time' => array('GT', time())))->find();
        //付款，未付款，交易完成，退款中，已退款,付款之后不能再使用砍价额度
        $isBargainByOrder = M('order')->where(array('user_id' => $this->user['id'], 'product_id' => $product['id'],
            'bargain_id' => $bargain['id'], 'status' => array(array('EQ', 0), array('EQ', 1), array('EQ', 4), array('EQ', 6), array('EQ', 7), 'or'),
            'create_time' => array(array('GT', $bargain['start_time']), array('LT', $bargain['end_time']), 'and')))->find();
        if ($isBargainByOrder) {
            $this->ajaxReturn(['status' => 0, 'msg' => '你已经参加过此商品的砍价了']);
        }
        $bargainPrice = M('BargainRecord')->where(array('bargain_id' => $bargain['id'],
            'create_time' => array(array('LT', $bargain['end_time']), array('GT', $bargain['start_time']), 'and'),
            'share_user_id' => $this->user['id']))->sum('price');
        $originalPrice = json_decode($product['price'], true);
        $count = $originalPrice[$_POST['key']]['count'];
        if ($count < $_POST['amount']) {
            $this->ajaxReturn(['status' => 0, 'msg' => '当前库存为' . $count . '，请重新选择']);
        }
        $originalPrice = $originalPrice[$_POST['key']]['now_price'];
        $totalPrice = $originalPrice * $_POST['amount'];
        $couponPrice = 0;
        //优惠券 (修改优惠券状态为使用中)
        //todo
        //代金券 (修改代金券状态为使用中)
        $cashCouponPrice = 0;
        //todo
        if ($cashCouponPrice > ($totalPrice - $couponPrice)) {
            $cashCouponPrice = $totalPrice - $couponPrice;
        }
        //实际支付价钱
        $realPrice = $totalPrice - $couponPrice - $cashCouponPrice - $bargainPrice;
        $realPrice = $realPrice > 0 ? $realPrice : 0;
        $orderData = [
            'user_id' => $this->user['id'],
            'product_id' => $product['id'],
            'create_time' => time(),
            'amount' => $_POST['amount'],
            'total_price' => $totalPrice,
            'coupon_price' => $couponPrice,
            'real_price' => $realPrice,
            'coupon_id' => $_POST['coupon_id'] ?: '',
            'cash_coupon_id' => $_POST['cash_coupon_ids'] ? implode(',', $_POST['cash_coupon_ids']) : '',
            'cash_coupon_price' => $cashCouponPrice,
            'bargain_id' => $bargain['id'],
            'bargain_price' => $bargainPrice ?: 0,
            'status' => 0,
            'order_number' => $this->user['id'] . substr(time(), 4, 11),
            'token' => $product['token'],
            'key' => $_POST['key'],
        ];
        $id = D('Order')->insert($orderData);
        if ($id) {
            $newPrices = [];
            //扣除库存
            foreach ($originalPrice as $key => $val) {
                $newPrices[$key] = $val;
                if ($key == $_POST['key']) {
                    $newPrices[$key]['count'] = $val['count'] - $_POST['amount'];
                }
            }
            $newPrices = json_encode($newPrices);
            D('Product')->updateById($_POST['pro_id'], ['price' => $newPrices]);
            $data['userid'] = $this->user['id'];
            $data['orderid'] = $id;
            $data['name'] = $_POST['name'];
            $data['mobile'] = $_POST['mobile'];
            $data['message'] = $_POST['message'];
            $data['address'] = $_POST['address'] ?: '';
            $rs = M('order_info')->add($data);
            if (false !== $rs) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '订单创建成功', 'orderId' => $id));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'msg' => '订单创建失败'));
        }
    }

    //订单申诉
    public function rejectOrder()
    {
        if (!$_GET['orderId'] || empty($_GET['orderId'])) {
            return show(0, 'ID参数错误');
        }
        $find = D('Order')->find($_GET['orderId']);
        if ($find['product_id'] && !empty($find['product_id'])) {
            $product = D('Product')->find($find['product_id']);
            $prices = json_decode($product['price'], true);
            $price = $prices[$find['key']];
            $find['pro_title'] = $product['title'];
            $find['pro_pic'] = $product['pic_url'];
            //判断是否有砍价
            if (!empty($order['bargain_id'])) {
                //判断砍价活动是否还有效
                $isBargain = M('bargain')->where(['id' => $order['bargain_id'], 'start_time' => ['lt', time()],
                    'end_time' => ['gt', time()], 'key' => $order['key']])->find();
                if ($isBargain) {
                    $find['price'] = $price['now_price'];
                } else {
                    //判断有否抢购价
                    $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $order['product_id'], 'key' => $order['key'],
                        'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                    $find['price'] = $rushPrice ?: $price['now_price'];
                }
            } else {
                //判断有否抢购价
                $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $order['product_id'], 'key' => $order['key'],
                    'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                $find['price'] = $rushPrice ?: $price['now_price'];
            }
        } elseif ($find['parenting_id'] && !empty($find['parenting_id'])) {
            $find['pro_title'] = M('parenting')->where("id={$find['parenting_id']}")->getField('title');
            $find['pro_pic'] = M('parenting')->where("id={$find['parenting_id']}")->getField('image');
            $find['price'] = M('parenting')->where("id={$find['parenting_id']}")->getField('price');
        } else if ($find['activity_id'] && !empty($find['activity_id'])) {
            $find['pro_title'] = M('OrganizationActivity')->where("id={$find['activity_id']}")->getField('title');
            $find['pro_pic'] = M('OrganizationActivity')->where("id={$find['activity_id']}")->getField('image');
            $find['price'] = M('OrganizationActivity')->where("id={$find['activity_id']}")->getField('price');
        } else if ($find['group_record_id'] && !empty($find['group_record_id'])) {
            $groupRecord = D('GroupRecord')->find($find['group_record_id']);
            $group = D('GroupProduct')->find($groupRecord['group_id']);
            $find['pro_title'] = $group['title'];
            $find['pro_pic'] = $group['image'];
            $find['price'] = $group['price'];
        }
        $this->assign('id', $_GET['orderId'])->assign('find', $find);
        $this->display();
    }

    public function uploadPic()
    {
        $config = array(
            'maxSize' => 1048576, //上传的文件大小限制 (0-不做限制)
            'exts' => array('jpg', 'png', 'gif', 'jpeg'), //允许上传的文件后缀
            'rootPath' => './Upload/', //保存根路径
            'driver' => 'LOCAL', // 文件上传驱动
            'subName' => array('date', 'Y-m'),
            'savePath' => I('dir', 'uploads') . "/"
        );
        $dirs = explode(",", C("YP_UPLOAD_DIR"));
        if (!in_array(I('dir', 'uploads'), $dirs)) {
            echo '非法文件目录！';
            return false;
        }

        $upload = new \Think\Upload($config);
        $rs = $upload->upload($_FILES);
        $Filedata = key($_FILES);
        if (!$rs) {
            $this->error($upload->getError());
        } else {
            $images = new \Think\Image();
            $images->open('./Upload/' . $rs[$Filedata]['savepath'] . $rs[$Filedata]['savename']);
            $newsavename = str_replace('.', '_thumb.', $rs[$Filedata]['savename']);
//            $vv = $images->thumb(I('width', 300), I('height', 300))->save('./Upload/' . $rs[$Filedata]['savepath'] . $newsavename);
            if (C('YP_M_IMG_SUFFIX') != '') {
                $msuffix = C('YP_M_IMG_SUFFIX');
                $mnewsavename = str_replace('.', $msuffix . '.', $rs[$Filedata]['savename']);
                $mnewsavename_thmb = str_replace('.', "_thumb" . $msuffix . '.', $rs[$Filedata]['savename']);
                $images->open('./Upload/' . $rs[$Filedata]['savepath'] . $rs[$Filedata]['savename']);
//                $images->thumb(I('width', 700), I('height', 700))->save('./Upload/' . $rs[$Filedata]['savepath'] . $mnewsavename);
//                $images->thumb(I('width', 250), I('height', 250))->save('./Upload/' . $rs[$Filedata]['savepath'] . $mnewsavename_thmb);
            }

        }
        $rs[$Filedata]['savepath'] = "Upload/" . $rs[$Filedata]['savepath'];
        $rs[$Filedata]['savethumbname'] = $rs[$Filedata]['savename'];
        $rs['status'] = 1;
        $this->ajaxReturn($rs, 'JSON');
    }

    public function addAppela()
    {
        if ($_POST) {
            if (!$_POST['content'] || empty($_POST['content'])) {
                return show(0, '内容不能为空');
            }
            if (!$_POST['order_id']) {
                return show(0, '参数错误');
            }
            //判断有否正在申述中
            $isAppeal = M('appeal')->where(['user_id' => $this->user['id'], 'order_id' => $_POST['order_id'], 'status' => 1])->getField('id');
            if ($isAppeal) {
                return show(0, '你已提交过申述，请耐心等到结果');
            }
            $token = M('Order')->where("id={$_POST['order_id']}")->getField('token');
            $insertData = $_POST;
            $insertData['token'] = $token;
            $insertData['create_time'] = time();
            $insertData['order_id'] = $_POST['order_id'];
            $insertData['user_id'] = $this->user['id'];
            $insertData['status'] = 1;
            try {
                $id = D('Appeal')->insert($insertData);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        }
    }

    //查看地址
    public function checkExpress()
    {
        $this->display();
    }

    public function ajaxCheckExpress()
    {
        if (!$_GET['id']) {
            $this->error('参数错误');
        }
        $order = M('order')->where("id={$_GET['id']}")->find();
        if (!empty($order['product_id'])) {
            $image = M('product')->where(['id' => $order['product_id']])->getField('pic_url');
        } else if (!empty($order['parenting_id'])) {
            $image = M('parenting')->where(['id' => $order['parenting_id']])->getField('image');
        } else if (!empty($order['activity_id'])) {
            $image = M('organization_activity')->where(['id' => $order['activity_id']])->getField('image');
        } else if (!empty($order['group_id'])) {
            $image = M('group_product')->where(['id' => $order['group_id']])->getField('image');
        } else {
            $image = '';
        }
        if (!empty($order['express_number'])) {
            $requestUrl = "http://wuliu.market.alicloudapi.com/kdi?no=" . $order['express_number'];
            $result = ali_api_request('GET', $requestUrl);
            $result = json_decode($result, true);
            if ($result['status'] != 0) {
                return show(0, '查不到物流信息');
            }
            if ($result['result']['deliverystatus'] == '3') { //已签收，修改订单状态为已完成
                D('Order')->where("id={$_GET['id']}")->updateById($_GET['id'], ['status' => 4]);
            }
            //获取商品图片
            $resultData = [
                'express' => $order['express'],
                'express_number' => $order['express_number'],
                'list' => $result['result']['list'],
                'status' => $result['result']['deliverystatus'], //1在途中 2 派件中 3 已签收 4 派送失败
                'image' => $image
            ];
            return show(2, '获取成功', $resultData);
        } else {
            return show(1, '没有物流信息', ['image' => $image]);
        }
    }

    //取消申诉
    public function cancelAppeal()
    {
        if (!$_POST['id']) {
            return show(0, '参数错误');
        }
        try {
            $id = M('appeal')->where(['user_id' => $this->user['id'], 'order_id' => $_POST['id']])->save(['status' => 4]);
            if ($id !== false) {
                return show(1, '取消成功');
            } else {
                return show(0, '取消失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }
}