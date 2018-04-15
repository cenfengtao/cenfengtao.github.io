<?php
/**
 *订单管理
 */
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;
use Think\Page;

class OrderController extends CommonController
{
    public function orderList()
    {
        /*
     * 分页操作逻辑
     * */
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 10;
        if ($this->isSuper) {
            $list = D('Order')->getListForOrder(array(), $page, $pageSize);
            $count = D('Order')->getCount();
        } else {
            $list = D('Order')->getListForOrder(array('token' => $this->token), $page, $pageSize);
            $count = D('Order')->getCount(array('token' => $this->token));
        }

        $res = new Page($count, $pageSize);
        $page = $res->show();
        foreach ($list as $k => $v) {
            $list[$k]['username'] = D('User')->getNameById($v['user_id']);
            if (!empty($v['product_id'])) {
                $list[$k]['product_title'] = D('Product')->getTitleById($v['product_id']);
            }
            if (!empty($v['parenting_id'])) {
                $list[$k]['product_title'] = M('parenting')->where("id={$v['parenting_id']}")->getField('title');
            }
            if (!empty($v['activity_id'])) {
                $list[$k]['product_title'] = M('organization_activity')->where("id={$v['activity_id']}")->getField('title');
            }
            if (!empty($v['group_record_id'])) {
                $groupId = M('group_record')->where("id={$v['group_record_id']}")->getField('group_id');
                $list[$k]['product_title'] = M('group_product')->where("id={$groupId}")->getField('title');
            }
        }
        $this->assign('orderList', $list)->assign('page', $page);
        $this->display();
    }

    public function appeal()
    {
        /*
         * 分页操作逻辑
         * */
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 10;
        if ($this->isSuper) {
            $rejectList = D('Appeal')->getListForAppeal(array(), $page, $pageSize);
            $count = D('Appeal')->getCount();
        } else {
            $rejectList = D('Appeal')->getListForAppeal(array('token' => $this->token), $page, $pageSize);
            $count = D('Appeal')->getCount();
        }
        $res = new Page($count, $pageSize);
        $page = $res->show();
        foreach ($rejectList as $k => $v) {
            $rejectList[$k]['username'] = M('User')->where("id='{$v['user_id']}'")->getField('username');
            $proId = D('Order')->find($v['order_id']);
            if ($proId['product_id'] || !empty($proId['product_id'])) {
                $rejectList[$k]['title'] = M('Product')->where("id='{$proId['product_id']}'")->getField('title');
                $type = M('Product')->where("id='{$proId['product_id']}'")->getField('type');
                if ($type == 1) {
                    $rejectList[$k]['cate_name'] = '课程';
                } else if ($type == 2) {
                    $rejectList[$k]['cate_name'] = '商品';
                }
            } else if ($proId['parenting_id'] || !empty($proId['parenting_id'])) {
                $rejectList[$k]['title'] = M('Parenting')->where("id='{$proId['parenting_id']}'")->getField('title');
                $rejectList[$k]['cate_name'] = '亲子';
            } else if ($proId['activity_id'] || !empty($proId['activity_id'])) {
                $rejectList[$k]['title'] = M('Organization_activity')->where("id='{$proId['activity_id']}'")->getField('title');
                $rejectList[$k]['cate_name'] = '机构活动';
            } else if ($proId['group_record_id'] || !empty($proId['group_record_id'])) {
                $groupId = M('Group_record')->where("id='{$proId['group_record_id']}'")->find();
                $rejectList[$k]['title'] = M('Group_product')->where("id='{$groupId['group_id']}'")->getField('title');
                $rejectList[$k]['cate_name'] = '团购';
            }
        }
        $this->assign('list', $rejectList)->assign('page', $page);
        $this->display();
    }

    //修改申诉结果
    public function accompLish()
    {
        if ($_POST) {
            if (!$_POST['id'] || empty($_POST['id'])) {
                return show(0, 'ID参数错误');
            }
            if (!$_POST['status']) {
                return show(0, '参数错误');
            }
            $id = D('Appeal')->updateById($_POST['id'], ['status' => $_POST['status'], 'gm_reply' => $_POST['reply']]);
            if ($id !== false) {
                return show(1, '修改成功');
            } else {
                return show(0, '修改失败');
            }
        }
    }

    public function check()
    {
        if (!$_GET['id'] || empty($_GET['id'])) {
            return show(0, 'ID参数错误');
        }
        $single = D('Appeal')->find($_GET['id']);
        $single['username'] = M('User')->where("id='{$single['user_id']}'")->getField('username');
        $proId = D('Order')->find($single['order_id']);
        if ($proId['product_id'] || !empty($proId['product_id'])) {
            $single['title'] = M('Product')->where("id='{$proId['product_id']}'")->getField('title');
            $type = M('Product')->where("id='{$proId['product_id']}'")->getField('type');
            if ($type == 1) {
                $single['cate_name'] = '课程';
            } else if ($type == 2) {
                $single['cate_name'] = '商品';
            }
        } else if ($proId['parenting_id'] || !empty($proId['parenting_id'])) {
            $single['title'] = M('Parenting')->where("id='{$proId['parenting_id']}'")->getField('title');
            $single['cate_name'] = '亲子';
        } else if ($proId['activity_id'] || !empty($proId['activity_id'])) {
            $single['title'] = M('Organization_activity')->where("id='{$proId['activity_id']}'")->getField('title');
            $single['cate_name'] = '机构活动';
        } else if ($proId['group_record_id'] || !empty($proId['group_record_id'])) {
            $groupId = M('Group_record')->where("id='{$proId['group_record_id']}'")->find();
            $single['title'] = M('Group_product')->where("id='{$groupId['group_id']}'")->getField('title');
            $single['cate_name'] = '团购';
        }
        $this->assign('single', $single);
        $this->display();
    }

    public function checkOrder()
    {
        if (!$_GET['id'] || empty($_GET['id'])) {
            return show(0, 'ID参数错误');
        }
        $order = D('Order')->find($_GET['id']);
        $order['username'] = M('User')->where("id='{$order['user_id']}'")->getField('username');
        if ($order['product_id'] || !empty($order['product_id'])) {
            $order['title'] = M('Product')->where("id='{$order['product_id']}'")->getField('title');
            $type = M('Product')->where("id='{$order['product_id']}'")->getField('type');
            if ($type == 1) {
                $order['cate_name'] = '课程';
            } else if ($type == 2) {
                $order['cate_name'] = '商品';
            }
        } else if ($order['parenting_id'] || !empty($order['parenting_id'])) {
            $order['title'] = M('Parenting')->where("id='{$order['parenting_id']}'")->getField('title');
            $order['cate_name'] = '亲子';
        } else if ($order['activity_id'] || !empty($order['activity_id'])) {
            $order['title'] = M('Organization_activity')->where("id='{$order['activity_id']}'")->getField('title');
            $order['cate_name'] = '机构活动';
        } else if ($order['group_record_id'] || !empty($order['group_record_id'])) {
            $groupId = M('Group_record')->where("id='{$order['group_record_id']}'")->find();
            $order['title'] = M('Group_product')->where("id='{$groupId['group_id']}'")->getField('title');
            $order['cate_name'] = '团购';
        }
        $message = M('Order_info')->where("orderid={$_GET['id']}")->find();
        $order['name'] = $message['name'];
        $order['mobile'] = $message['mobile'];
        $order['message'] = $message['message'];
        $order['address'] = $message['address'];
        //添加阅读状态
        if ($order['is_read'] == 1) {
            $data = [
                'is_read' => 2
            ];
            D('Order')->updateById($_GET['id'], $data);
        }
        $this->assign('order', $order);
        $this->display();
    }

    public function updateExpress()
    {
        if (!$_POST['orderId']) {
            return show(0, '订单参数不能为空');
        }
        if (!$_POST['express']) {
            return show(0, '快递公司不能为空');
        }
        if (!$_POST['express_number']) {
            return show(0, '快递单号不能为空');
        }
        try {
            $id = D('Order')->updateById($_POST['orderId'], ['express' => $_POST['express'], 'express_number' => $_POST['express_number']]);
            if ($id === false) {
                return show(0, '填写失败');
            }
            return show(1, '填写成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function getNowExpress()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        $order = M('order')->field('express,express_number')->where("id={$_GET['id']}")->find();
        return show(1, '', ['express' => $order['express'], 'express_number' => $order['express_number']]);
    }
}