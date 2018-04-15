<?php
/**
 *商品管理
 */
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;
use Think\Page;

class ProductController extends CommonController
{
    //上架列表
    public function proUpList()
    {
        /*
         * 分页操作逻辑
         * */
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 10;
        try {
            if ($this->isSuper) {
                $list = D('Product')->getListForAdmin(array('status' => 1), $page, $pageSize);
                $count = D('Product')->getCount(array('status' => 1));
            } else {
                $list = D('Product')->getListForAdmin(array('status' => 1, 'token' => $this->token), $page, $pageSize);
                $count = D('Product')->getCount(array('status' => 1, 'token' => $this->token));
            }
            foreach ($list as $k => $v) {
                $list[$k]['org_name'] = D('Organization')->getOrgNameById($v['org_id']);
            }
            $res = new Page($count, $pageSize);
            $pages = $res->show();
            $this->assign('upList', $list)->assign('page', $pages);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    //下架列表
    public function proDownList()
    {
        if ($this->isSuper) {
            $downList = M('Product')->where(array('status' => 2))->field("desc,cost", true)->select();
            $groupDownList = M('group_product')->where(array('is_show' => 2))->field("desc,cost", true)->select();
        } else {
            $downList = D('Product')->getDownList($this->token);
            $groupDownList = M('group_product')->where(array('is_show' => 2, 'token' => $this->token))->field("description,cost", true)->select();
        }
        foreach ($downList as $k => $v) {
            $downList[$k]['org_name'] = D('Organization')->getOrgnameByToken($v['token']);
            $price = json_decode($v['price'], true);
            $downList[$k]['now_price'] = $price[1]['now_price'];
            $downList[$k]['count'] = $price[1]['count'];
        }
        foreach ($groupDownList as $k => $v) {
            $groupDownList[$k]['org_name'] = D('Organization')->getOrgnameByToken($v['token']);
            $groupDownList[$k]['amount'] = M('order')->where(array('group_id' => array('eq', $v['id'],
                'status' => array(array('eq', 1), array('eq', 4), 'or'))))->sum('amount');
            if ($groupDownList[$k]['amount'] || $groupDownList[$k]['amount'] > 0) {
                $groupDownList[$k]['count'] = $v['max_people'] - $groupDownList[$k]['amount'];
            } else {
                $groupDownList[$k]['count'] = 0;
            }
            $groupDownList[$k]['now_price'] = $groupDownList[$k]['price'];
        }
        $this->assign('downList', $downList)->assign('groupDownList', $groupDownList);
        $this->display();
    }

    //抢购列表
    public function rushList()
    {
        $list = M('bargain')->where(array('type' => 2))->select();
        foreach ($list as $k => $val) {
            $list[$k]['title'] = M('product')->where(['id' => $val['type_id']])->getField('title');
            $list[$k]['org_name'] = M('organization')->where(['token' => $val['token']])->getField('org_name');
        }
        $this->assign('list', $list);
        $this->display();
    }

    //精选列表
    public function hotList()
    {
        $list = D('Product')->getHotList($this->token);
        foreach ($list as $k => $val) {
            $list[$k]['org_name'] = D('Organization')->getOrgNameById($val['org_id']);
            $price = json_decode($val['price'], true);
            $list[$k]['now_price'] = $price[1]['now_price'];
            $list[$k]['count'] = $price[1]['count'];
            $list[$k]['class_title'] = M('product_class')->where(['id' => $val['class_id']])->getField('title');
        }
        $this->assign('list', $list);
        $this->display();
    }

    //下架
    public function soldOut()
    {
        if (!$_POST['id'] || !is_numeric($_POST['id'])) {
            return show(0, 'ID参数错误');
        }
        try {
            $id = D('Product')->soldOutById($_POST['id']);
            $bargains = M('bargain')->where(['type' => 1, 'type_id' => $_POST['id']])->select();
            foreach ($bargains as $k => $v) {
                $data = ['end_time' => time()];
                M('bargain')->where('id=' . $v['id'])->save($data);
            }
            $saleIntegrals = M('bargain')->where(['type' => 4, 'type_id' => $_POST['id']])->select();
            foreach ($saleIntegrals as $k => $v) {
                D('Bargain')->updateById($v['id'], ['extra' => 2]);
            }
            if ($id) {
                _getDiscoverInfo();
                _getIndexInfo();
                return show(1, '下架成功');
            } else {
                return show(0, '下架失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //上架
    public function putAway()
    {
        if (!$_POST['id'] || !is_numeric($_POST['id'])) {
            return show(0, 'ID参数错误');
        }
        try {
            $id = D('Product')->putAwayById($_POST['id']);
            if ($id) {
                return show(1, '上架成功');
            } else {
                return show(0, '上架失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //上架团购
    public function groupPutAway()
    {
        if (!$_POST['id'] || !is_numeric($_POST['id'])) {
            return show(0, 'ID参数错误');
        }
        try {
            $id = D('GroupProduct')->updateById($_POST['id'], ['is_show' => 1]);
            if ($id) {
                return show(1, '上架成功');
            } else {
                return show(0, '上架失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function addProduct()
    {
        if ($_POST) {
            if (!$_POST['title'] || empty($_POST['title'])) {
                return show(0, '课程名称不能为空');
            }
            if (empty($_POST['pic_url'])) {
                unset($_POST['pic_url']);
            }
            if (empty($_POST['class_time'])) {
                return show(0, '上课时间不能为空');
            }
            if (empty($_POST['price'])) {
                return show(0, '价钱不能为空');
            }
            if ($_POST['is_book'] == 1) {
                $_POST['book_time'] = '';
            }
            $_POST['type'] = 1;
            $bookTime = [];
            if ($_POST['is_book'] == 2) {
                //编译成json格式存进数据库
                foreach ($_POST['book_time'] as $k => $v) {
                    $bookTime[$k]['book_time_day'] = $v[0];
                    $bookTime[$k]['book_start_hour'] = $v[1];
                    $bookTime[$k]['book_end_hour'] = $v[2];
                    if (empty($bookTime[$k]['book_time_day']) || empty($bookTime[$k]['book_start_hour']) || empty($bookTime[$k]['book_end_hour'])) {
                        return show(0, '预约上课时间设置有误，请重新核实');
                    }
                }
                $bookTime = json_encode($bookTime);
                $_POST['book_time'] = $bookTime;
            }
            $classTime = [];
            //编译成json格式存进数据库
            foreach ($_POST['class_time'] as $key => $val) {
                $classTime[$key]['class_time_day'] = $val[0];
                $classTime[$key]['class_start_hour'] = $val[1];
                $classTime[$key]['class_end_hour'] = $val[2];
                if (empty($classTime[$key]['class_time_day']) || empty($classTime[$key]['class_start_hour']) || empty($classTime[$key]['class_end_hour'])) {
                    return show(0, '上课时间设置有误，请重新核实');
                }
            }
            $classTime = json_encode($classTime);
            $price = [];
            //编译成json格式存进数据库
            foreach ($_POST['price'] as $key => $val) {
                $price[$key]['class_normal'] = $val[0];
                $price[$key]['original_price'] = $val[1];
                $price[$key]['now_price'] = $val[2];
                if (is_null($val[3])) {
                    return show(0, '库存不能为空，请重新核实');
                }
                $price[$key]['count'] = $val[3];
                $price[$key]['status'] = $val[4];
                /*if ($val[3] == 0) {//当库存为0时结束砍价活动
                    $id = M('bargain')->where(array('type' => array(array('eq', 1), array('eq', 2), 'or'), 'type_id' => $_POST['id'], 'key' => $key))->field('id')->select();
                    if ($id) {
                        foreach ($id as $k => $v) {
                            D('Bargain')->updateById($v['id'], ['end_time' => time()]);
                        }
                    }
                }*/
                if (empty($price[$key]['class_normal']) || empty($price[$key]['original_price']) || empty($price[$key]['now_price'])) {
                    return show(0, '价钱设置有误，请重新核实');
                }
            }
            $price = json_encode($price);
            $_POST['class_time'] = $classTime;
            $_POST['book_time'] = $bookTime;
            $_POST['price'] = $price;
            if ($_POST['id'] && !empty($_POST['id'])) {
                return $this->save($_POST);
            }
            try {
                $_POST['token'] = $this->token;
                $_POST['create_time'] = time();
                $_POST['org_id'] = M('organization')->where(array("token" => $this->token))->getField('id');
                $id = D('Product')->insert($_POST);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $classList = D('ProductClass')->getList(['type' => 1]);
            $orgList = D('Organization')->getOrgList();
            $this->assign('org_list', $orgList)->assign('class_list', $classList);
            $this->display();
        }
    }

    //添加商品
    public function addRealProduct()
    {

        if ($_POST) {
            $_POST['type'] = 2;
            if (!$_POST['title'] || empty($_POST['title'])) {
                return show(0, '商品名称不能为空');
            }
            if (empty($_POST['pic_url'])) {
                unset($_POST['pic_url']);
            }
            $price = [];
            //编译成json格式存进数据库
            foreach ($_POST['price'] as $key => $val) {
                $price[$key]['class_normal'] = $val[0];
                $price[$key]['original_price'] = $val[1];
                $price[$key]['now_price'] = $val[2];
                if (is_null($val[3])) {
                    return show(0, '库存不能为空，请重新核实');
                }
                $price[$key]['count'] = $val[3];
                $price[$key]['status'] = $val[4];
                /*if ($val[3] == 0) {//当库存为0时结束砍价活动
                    $id = M('bargain')->where(array('type' => array(array('eq' => 1), array('eq', 2), 'or'), 'type_id' => $_POST['id'], 'key' => $key))->getField('id');
                    if ($id) {
                        D('Bargain')->updateById($id, ['end_time' => time()]);
                    }
                }*/
                if (empty($price[$key]['class_normal']) || empty($price[$key]['original_price']) || empty($price[$key]['now_price'])) {
                    return show(0, '价钱设置有误，请重新核实');
                }
            }
            $price = json_encode($price);
            $_POST['price'] = $price;
            if ($_POST['id'] && !empty($_POST['id'])) {
                return $this->save($_POST);
            }
            try {
                $_POST['token'] = $this->token;
                $_POST['create_time'] = time();
                $_POST['org_id'] = M('organization')->where(array("token" => $this->token))->getField('id');
                $id = D('Product')->insert($_POST);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $classList = D('ProductClass')->getList(['type' => 2]);
            $orgList = D('Organization')->getOrgList();
            $this->assign('org_list', $orgList)->assign('class_list', $classList);
            $this->display();
        }
    }

    public function editProduct()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            $this->error('ID参数错误');
        }
        try {
            $product = D('Product')->find($_GET['id']);
            $classList = D('ProductClass')->getList(['type' => 1]);
            $bookTime = json_decode($product['book_time'], true);
            $classTime = json_decode($product['class_time'], true);
            $price = json_decode($product['price'], true);
            $this->assign('product', $product)->assign('book_time', $bookTime)
                ->assign('class_list', $classList)->assign('book_time_count', count($bookTime))
                ->assign('class_time_count', count($classTime))->assign('class_time', $classTime)
                ->assign('price', $price)->assign('price_count', count($price));
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function editRealProduct()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            $this->error('ID参数错误');
        }
        try {
            $product = D('Product')->find($_GET['id']);
            $classList = D('ProductClass')->getList(['type' => 2]);
            $price = json_decode($product['price'], true);
            $this->assign('product', $product)->assign('class_list', $classList)
                ->assign('price', $price)->assign('price_count', count($price));
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function save($data)
    {
        $data['check_status'] = 1;
        try {
            $id = D('Product')->updateById($data['id'], $data);
            if ($id === false) {
                return show(0, '修改失败');
            }
            return show(1, '修改成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //添加抢购商品
    public function addRushProduct()
    {
        if ($_POST) {
            if (!$_POST['id'] || !is_numeric($_POST['id'])) {
                return show(0, '抢购课程不能为空');
            }
            if (!$_POST['start_time'] || empty($_POST['start_time'])) {
                return show(0, '开始时间不能为空');
            }
            if (!$_POST['end_time'] || empty($_POST['end_time'])) {
                return show(0, '结束时间不能为空');
            }
            if (!$_POST['key']) {
                return show(0, '请选择课程规格');
            }
            if (!$_POST['rush_price'] || empty($_POST['rush_price']) || floatval($_POST['rush_price']) == 0) {
                return show(0, '抢购价不能为空或0');
            }
            $type_id = M('Bargain')->where(array('type' => 2, 'type_id' => $_POST['id'], 'key' => $_POST['key']))->getField('id');
            if ($type_id) {
                return show(0, '不能重复添加');
            }
            $insertData = [
                'start_time' => strtotime($_POST['start_time']),
                'end_time' => strtotime($_POST['end_time']) + 3600 * 24 - 1,//当天的23点59分59秒
                'price' => $_POST['rush_price'],
                'key' => $_POST['key'],
                'type' => 2,
                'type_id' => $_POST['id'],
                'token' => M('product')->where(['id' => $_POST['id']])->getField('token'),
            ];
            try {
                $id = D('Bargain')->insert($insertData);
                if ($id) {
                    return show(1, '添加成功');
                }
                return show(0, '添加失败');
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $upList = M('Product')->where(array('status' => 1, 'type' => 1, 'check_status' => 2))->field('desc,cost', true)->select();
            $this->assign('upList', $upList);
            $this->display();
        }
    }

    //修改抢购商品
    public function editRushProduct()
    {
        if ($_POST) {
            if (!$_POST['id']) {
                return show(0, '参数错误');
            }
            if (!$_POST['start_time'] || empty($_POST['start_time'])) {
                return show(0, '开始时间不能为空');
            }
            if (!$_POST['end_time'] || empty($_POST['end_time'])) {
                return show(0, '结束时间不能为空');
            }
            if (!$_POST['rush_price'] || empty($_POST['rush_price']) || floatval($_POST['rush_price']) == 0) {
                return show(0, '抢购价不能为空或0');
            }
            $updateData = [
                'start_time' => strtotime($_POST['start_time']),
                'end_time' => strtotime($_POST['end_time']) + 3600 * 24 - 1,//当天的23点59分59秒
                'price' => $_POST['rush_price'],
            ];
            $id = D('Bargain')->updateById($_POST['id'], $updateData);
            if ($id === false) {
                return show(0, '修改失败');
            } else {
                return show(1, '修改成功');
            }
        } else {
            if (!$_GET['id'] || !is_numeric($_GET['id'])) {
                return show(0, 'ID参数错误');
            }
            $rush = D('Bargain')->find($_GET['id']);
            $price = M('product')->where(['id' => $rush['type_id']])->getField('price');
            $title = M('product')->where(['id' => $rush['type_id']])->getField('title');
            $price = json_decode($price, true);
            $classNormal = $price[$rush['key']];
            $this->assign('rush', $rush)->assign('classNormal', $classNormal)->assign('title', $title);
            $this->display();
        }
    }

    //取消抢购
    public function cancelRush()
    {
        if (!$_POST['id']) {
            return show(0, 'ID不能为空');
        }
        $id = D('Bargain')->updateById($_POST['id'], ['end_time' => time()]);
        if ($id) {
            return show(1, '取消成功');
        } else {
            return show(0, '取消失败');
        }
    }

    //添加精选商品
    public function addHotProduct()
    {
        if ($_POST) {
            if (!$_POST['id'] || !is_numeric($_POST['id'])) {
                return show(0, '精选商品不能为空');
            }
            //精选商品数
            $classId = M('product')->where(['id' => $_POST['id']])->getField('class_id');
            $count = M('Product')->where(array('is_hot' => 2, 'type' => 2, 'class_id' => $classId))->count();
            if ($count > 4) {
                return show(0, '每个分类的精选商品不能超过4个');
            }
            $id = D('Product')->updateById($_POST['id'], array('is_hot' => 2));
            if ($id) {
                return show(1, '添加成功');
            } else {
                return show(0, '添加失败');
            }
        } else {
            $upList = M('Product')->where(array('status' => 1, 'token' => $this->token, 'type' => 2, 'is_hot' => 1))->select();
            $this->assign('upList', $upList);
            $this->display();
        }
    }

    //取消精选
    public function cancelHot()
    {
        if (!$_POST['id'] || !is_numeric($_POST['id'])) {
            return show(0, 'ID参数错误');
        }
        $id = D('Product')->updateById($_POST['id'], array('is_hot' => 1));
        if ($id) {
            return show(1, '取消成功');
        } else {
            return show(0, '取消失败');
        }
    }

    //团购列表
    public function groupProductList()
    {
        if ($this->isSuper) {
            $groupList = M('group_product')->order('create_time desc')->where(array('type' => [['eq', 1], ['eq', 2], 'or']))
                ->field('class_time,description,cost', true)->select();
        } else {
            $groupList = M('group_product')->where(array('type' => [['eq', 1], ['eq', 2], 'or'], 'token' => $this->token))
                ->order('create_time desc')->field('class_time,description,cost', true)->select();
        }
        foreach ($groupList as $k => $v) {
            $groupList[$k]['class_name'] = M('product_class')->where("id={$v['class_id']}")->getField('title');
            $groupList[$k]['org_name'] = D('Organization')->getOrgnameByToken($v['token']);
        }
        $this->assign('groupList', $groupList);
        $this->display();
    }

    //添加团购课程
    public function addGroupClass()
    {
        if ($_POST) {
            if (!$_POST['title'] || !$_POST['title']) {
                return show(0, '标题不能为空');
            }
            if (!$_POST['class_id'] || empty($_POST['class_id'])) {
                return show(0, '请选择分类');
            }
            if (!$_POST['original_price'] || $_POST['original_price'] <= 0) {
                return show(0, '原价不能少于0');
            }
            if (!$_POST['price'] || $_POST['price'] <= 0) {
                return show(0, '团购价不能少于0');
            }
            if (!$_POST['start_time']) {
                return show(0, '团购开始时间不能为空');
            } else {
                $_POST['start_time'] = strtotime($_POST['start_time']);
            }
            if (!$_POST['end_time']) {
                return show(0, '团购结束时间不能为空');
            } else {
                $_POST['end_time'] = strtotime($_POST['end_time']) + 86399;
            }
            if (!$_POST['unit'] || empty($_POST['unit'])) {
                return show(0, '单位不能为空');
            }
            if (!$_POST['class_time'] || empty($_POST['class_time'])) {
                return show(0, '上课时间不能为空');
            }
            if (!empty($_POST['reward_money'] && $_POST['reward_money'] < 0)) {
                return show(0, '奖励金不能少于0');
            }
            $insertData = $_POST;
            $classTime = [];
            //编译成json格式存进数据库
            foreach ($_POST['class_time'] as $k => $v) {
                $classTime[$k]['class_time_day'] = $v[0];
                $classTime[$k]['class_start_hour'] = $v[1];
                $classTime[$k]['class_end_hour'] = $v[2];
                if (empty($classTime[$k]['class_time_day']) || empty($classTime[$k]['class_start_hour']) || empty($classTime[$k]['class_end_hour'])) {
                    return show(0, '上课时间设置有误，请重新核实');
                }
            }
            $classTime = json_encode($classTime);
            $insertData['class_time'] = $classTime;
            $insertData['token'] = $this->token;
            $insertData['create_time'] = time();
            $insertData['type'] = 1;
            try {
                $id = D('GroupProduct')->insert($insertData);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $classList = M('ProductClass')->where(['type' => 1])->select();
            $this->assign('class_list', $classList);
            $this->display();
        }
    }

    //添加团购商品
    public function addGroupProduct()
    {
        if ($_POST) {
            if (!$_POST['title'] || !$_POST['title']) {
                return show(0, '标题不能为空');
            }
            if (!$_POST['class_id'] || empty($_POST['class_id'])) {
                return show(0, '请选择分类');
            }
            if (!$_POST['original_price'] || $_POST['original_price'] <= 0) {
                return show(0, '原价不能少于0');
            }
            if (!$_POST['price'] || $_POST['price'] <= 0) {
                return show(0, '团购价不能少于0');
            }
            if (!$_POST['start_time']) {
                return show(0, '团购开始时间不能为空');
            } else {
                $_POST['start_time'] = strtotime($_POST['start_time']);
            }
            if (!$_POST['end_time']) {
                return show(0, '团购结束时间不能为空');
            } else {
                $_POST['end_time'] = strtotime($_POST['end_time']) + 86399;
            }
            if (!$_POST['unit'] || empty($_POST['unit'])) {
                return show(0, '单位不能为空');
            }
            if (!empty($_POST['reward_money'] && $_POST['reward_money'] < 0)) {
                return show(0, '奖励金不能少于0');
            }
            $insertData = $_POST;
            $insertData['token'] = $this->token;
            $insertData['create_time'] = time();
            $insertData['type'] = 2;
            try {
                $id = D('GroupProduct')->insert($insertData);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $classList = M('ProductClass')->where(['type' => 2])->select();
            $this->assign('class_list', $classList);
            $this->display();
        }
    }

    //修改团购课程
    public function editGroupProduct()
    {
        if ($_POST) {
            if (!$_POST['id'] || !$_POST['id']) {
                return show(0, 'ID参数不能为空');
            }
            if (!$_POST['title'] || !$_POST['title']) {
                return show(0, '标题不能为空');
            }
            if (!$_POST['class_id'] || empty($_POST['class_id'])) {
                return show(0, '请选择分类');
            }
            if (!$_POST['original_price'] || $_POST['original_price'] <= 0) {
                return show(0, '原价不能少于0');
            }
            if (!$_POST['price'] || $_POST['price'] <= 0) {
                return show(0, '团购价不能少于0');
            }
            if (!$_POST['start_time']) {
                return show(0, '团购开始时间不能为空');
            } else {
                $_POST['start_time'] = strtotime($_POST['start_time']);
            }
            if (!$_POST['end_time']) {
                return show(0, '团购结束时间不能为空');
            } else {
                $_POST['end_time'] = strtotime($_POST['end_time']) + 86399;
            }
            if (!$_POST['unit'] || empty($_POST['unit'])) {
                return show(0, '单位不能为空');
            }
            if ($_POST['type'] == 1) {
                if (!$_POST['class_time'] || empty($_POST['class_time'])) {
                    return show(0, '上课时间不能为空');
                }
            }
            if (!$_POST['image'] || empty($_POST['image'])) {
                unset($_POST['image']);
            }
            $updateData = $_POST;
            $classTime = [];
            //编译成json格式存进数据库
            foreach ($_POST['class_time'] as $k => $v) {
                $classTime[$k]['class_time_day'] = $v[0];
                $classTime[$k]['class_start_hour'] = $v[1];
                $classTime[$k]['class_end_hour'] = $v[2];
                if (empty($classTime[$k]['class_time_day']) || empty($classTime[$k]['class_start_hour']) || empty($classTime[$k]['class_end_hour'])) {
                    return show(0, '上课时间设置有误，请重新核实');
                }
            }
            $classTime = json_encode($classTime);
            $updateData['class_time'] = $classTime;
            $updateData['check_status'] = 1;
            if ($_POST['status'] == 6 && $_POST['end_time'] > time()) {
                $updateData['status'] = 1;
            }
            $id = D('GroupProduct')->updateById($_POST['id'], $updateData);
            if ($id !== false) {
                return show(1, '更新成功');
            } else {
                return show(0, '更新失败');
            }
        } else {
            if (!$_GET['id'] || !is_numeric($_GET['id'])) {
                $this->error('ID参数错误');
            }
            $product = D('GroupProduct')->find($_GET['id']);
            $classTime = json_decode($product['class_time'], true);
            $classList = M('product_class')->where(array('type' => 2))->select();
            $this->assign('product', $product)->assign('class_time', $classTime)
                ->assign('class_time_count', count($classTime))->assign('class_list', $classList);
            $this->display();
        }
    }

    public function editGroupClass()
    {
        if ($_POST) {
            if (!$_POST['id'] || !$_POST['id']) {
                return show(0, 'ID参数不能为空');
            }
            if (!$_POST['title'] || !$_POST['title']) {
                return show(0, '标题不能为空');
            }
            if (!$_POST['class_id'] || empty($_POST['class_id'])) {
                return show(0, '请选择分类');
            }
            if (!$_POST['original_price'] || $_POST['original_price'] <= 0) {
                return show(0, '原价不能少于0');
            }
            if (!$_POST['price'] || $_POST['price'] <= 0) {
                return show(0, '团购价不能少于0');
            }
            if (!$_POST['start_time']) {
                return show(0, '团购开始时间不能为空');
            } else {
                $_POST['start_time'] = strtotime($_POST['start_time']);
            }
            if (!$_POST['end_time']) {
                return show(0, '团购结束时间不能为空');
            } else {
                $_POST['end_time'] = strtotime($_POST['end_time']) + 86399;
            }
            if (!$_POST['unit'] || empty($_POST['unit'])) {
                return show(0, '单位不能为空');
            }
            if (!$_POST['image'] || empty($_POST['image'])) {
                unset($_POST['image']);
            }
            $_POST['check_status'] = 1;
            if ($_POST['status'] == 6 && $_POST['end_time'] > time()) {
                $_POST['status'] = 1;
            }
            $id = D('GroupProduct')->updateById($_POST['id'], $_POST);
            if ($id !== false) {
                return show(1, '更新成功');
            } else {
                return show(0, '更新失败');
            }
        } else {
            if (!$_GET['id'] || !is_numeric($_GET['id'])) {
                $this->error('ID参数错误');
            }
            $product = D('GroupProduct')->find($_GET['id']);
            $classList = M('product_class')->where(array('type' => 1))->select();
            $this->assign('product', $product)->assign('class_list', $classList);
            $this->display();
        }
    }

    //下架团购
    public function deleteGroupProduct()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, 'ID参数错误');
        }
        $image = M('GroupProduct')->where("id={$_POST['id']}")->getField('image');
        try {
            //删除图片
//            $thumbImage = str_replace('.', '_thumb.', $image);
//            unlink(dirname(__FILE__) . '/../../..' . $image);
//            unlink(dirname(__FILE__) . '/../../..' . $thumbImage);
            $id = D('GroupProduct')->updateById($_POST['id'], array('is_show' => 2));
            if ($id) {
                return show(1, '下架成功');
            } else {
                return show(0, '下架失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //热门列表
    public function fireList()
    {
        $list = M('Product')->where(array('is_fire' => 2, 'token' => $this->token))->select();
        foreach ($list as $k => $val) {
            $list[$k]['org_name'] = D('Organization')->getOrgNameById($val['org_id']);
            $price = json_decode($val['price'], true);
            $list[$k]['now_price'] = $price[1]['now_price'];
            $list[$k]['count'] = $price[1]['count'];
        }
        $this->assign('list', $list);
        $this->display();
    }

    //添加热门课程
    public function addFireProduct()
    {
        if ($_POST) {
            if (!$_POST['id']) {
                return show(0, 'ID参数错误');
            }
            try {
                $data = [
                    'is_fire' => 2
                ];
                $id = D('Product')->updateById($_POST['id'], $data);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $list = M('Product')->where(array('type' => 1, 'status' => 1, 'is_hot' => 1, 'is_fire' => 1))->select();
            $this->assign('list', $list);
            $this->display();
        }
    }

    //取消热门课程
    public function cancelFire()
    {
        if (!$_POST['id']) {
            return show(0, '课程ID错误');
        }
        $data = [
            'is_fire' => 1
        ];
        $id = D('Product')->updateById($_POST['id'], $data);
        if ($id) {
            return show(1, '取消成功');
        } else {
            return show(0, '取消失败');
        }
    }

    //活动列表
    public function activityList()
    {
        $list = M('Product')->where(array('is_activity' => 2, 'token' => $this->token))->select();
        foreach ($list as $k => $val) {
            $list[$k]['org_name'] = D('Organization')->getOrgNameById($val['org_id']);
            $price = json_decode($val['price'], true);
            $list[$k]['now_price'] = $price[1]['now_price'];
            $list[$k]['count'] = $price[1]['count'];
        }
        $this->assign('list', $list);
        $this->display();
    }

    //添加活动商品
    public function addActivityProduct()
    {
        if ($_POST) {
            if (!$_POST['id']) {
                return show(0, 'ID参数错误');
            }
            try {
                $data = [
                    'is_activity' => 2
                ];
                $id = D('Product')->updateById($_POST['id'], $data);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $list = M('Product')->order('create_time desc')->where(array('status' => 1, 'is_activity' => 1, 'token' => $this->token))->select();
            $this->assign('list', $list);
            $this->display();
        }
    }

    //取消活动商品
    public function cancelActivity()
    {
        if (!$_POST['id']) {
            return show(0, '商品ID错误');
        }
        $data = [
            'is_activity' => 1
        ];
        $id = D('Product')->updateById($_POST['id'], $data);
        if ($id) {
            return show(1, '取消成功');
        } else {
            return show(0, '取消失败');
        }
    }

    //砍价列表
    public function bargainList()
    {
        if ($this->isSuper) {
            $list = M('bargain')->where(array('type' => 1))->select();
        } else {
            $list = M('bargain')->where(array('type' => 1, 'token' => $this->token))->select();
        }
        foreach ($list as $k => $val) {
            $list[$k]['title'] = M('product')->where(['id' => $val['type_id']])->getField('title');
            $list[$k]['org_name'] = M('organization')->where(['token' => $val['token']])->getField('org_name');
        }
        $this->assign('list', $list);
        $this->display();
    }

    //添加砍价商品
    public function addBargainProduct()
    {
        if ($_POST) {
            if (!$_POST['id'] || !is_numeric($_POST['id'])) {
                return show(0, '商品不能为空');
            }
            if (!$_POST['start_time'] || empty($_POST['start_time'])) {
                return show(0, '开始时间不能为空');
            }
            if (!$_POST['end_time'] || empty($_POST['end_time'])) {
                return show(0, '结束时间不能为空');
            }
            if (!$_POST['price'] || empty($_POST['price']) || floatval($_POST['price']) == 0) {
                return show(0, '砍价不能为空');
            }
            if ($_POST['price'] >= $_POST['lastPrice']) {
                return show(0, '砍价不能比原价高或者一样');
            }
            if (!$_POST['key']) {
                return show(0, '课程规格不能为空');
            }
            $type_id = M('Bargain')->where(array('type' => 1, 'type_id' => $_POST['id'], 'key' => $_POST['key']))->find();
            if ($type_id == false) {
                $data = [
                    'type' => 1,
                    'type_id' => $_POST['id'],
                    'start_time' => strtotime($_POST['start_time']),
                    'end_time' => strtotime($_POST['end_time']) + 3600 * 24 - 1,
                    'price' => $_POST['price'],
                    'key' => $_POST['key'],
                    'token' => $this->token,
                    'extra' => $_POST['type']
                ];
                if ($data['start_time'] >= $data['end_time']) {
                    return show(0, '开始时间不能大于结束时间');
                }
                $id = D('Bargain')->insert($data);
                if ($id) {
                    _getDiscoverInfo();
                    _getIndexInfo();
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } else {
                return show(0, '不能重复添加');
            }
        } else {
            if ($this->isSuper) {
                $list = D('Product')->getList(array('status' => 1, 'check_status' => 2));
            } else {
                $list = D('Product')->getList(array('status' => 1, 'check_status' => 2, 'token' => $this->token));
            }
            $this->assign('list', $list);
            $this->display();
        }
    }

    public function editBargainProduct()
    {
        if ($_POST) {
            if (!$_POST['id'] || empty($_POST['id'])) {
                return show(0, 'ID不能为空');
            }
            if (!$_POST['start_time'] || empty($_POST['start_time'])) {
                return show(0, '开始时间不能为空');
            }
            if (!$_POST['end_time'] || empty($_POST['end_time'])) {
                return show(0, '结束时间不能为空');
            }
            if (!$_POST['price'] || empty($_POST['price']) || floatval($_POST['price']) == 0) {
                return show(0, '砍价不能为空');
            }
            if ($_POST['price'] >= $_POST['lastPrice']) {
                return show(0, '砍价不能比原价高或者一样');
            }
            $data = [
                'start_time' => strtotime($_POST['start_time']),
                'end_time' => strtotime($_POST['end_time']) + 3600 * 24 - 1,
                'price' => $_POST['price']
            ];
            if ($data['start_time'] >= $data['end_time']) {
                return show(0, '开始时间不能大于结束时间');
            }
            $id = D('Bargain')->updateById($_POST['id'], $data);
            if ($id) {
                _getDiscoverInfo();
                _getIndexInfo();
                return show(1, '修改成功');
            } else {
                return show(0, '修改失败');
            }
        } else {
            if (!$_GET['id'] || !is_numeric($_GET['id'])) {
                return show(0, 'ID参数错误');
            }
            $bargain = D('Bargain')->find($_GET['id']);
            if (!$bargain) {
                return show(0, '所选的记录不存在，请刷新后再选择');
            }
            $price = M('product')->where(['id' => $bargain['type_id']])->getField('price');
            $title = M('product')->where(['id' => $bargain['type_id']])->getField('title');
            $price = json_decode($price, true);
            $classNormal = $price[$bargain['key']];
            $this->assign('bargain', $bargain)->assign('classNormal', $classNormal)->assign('title', $title);
            $this->display();
        }
    }

    public function cancelBargain()
    {
        if (!$_POST['id']) {
            return show(0, 'ID不能为空');
        }
        $id = D('Bargain')->updateById($_POST['id'], ['end_time' => time()]);
        if ($id) {
            _getDiscoverInfo();
            _getIndexInfo();
            return show(1, '取消成功');
        } else {
            return show(0, '取消失败');
        }
    }

    //搜索分页
    public function search()
    {
        if (!$_GET['title'] || empty($_GET['title'])) {
            $pageList = '';
            $page = '';
            $this->assign('upList', $pageList)->assign('page', $page);
        }
        try {
            if ($this->isSuper) {
                $list = M('Product')->where("status=1")->order('create_time desc')->select();
            } else {
                $list = M('Product')->where(array('status' => 1, 'token' => $this->token))->order('create_time desc')->select();
            }
            foreach ($list as $k => $v) {
                $list[$k]['org_name'] = D('Organization')->getOrgNameById($v['org_id']);
                $list[$k]['new_title'] = M('Product')->where(array('id' => $v['id'], 'title' => array('like', '%' . $_GET['title'] . '%')))->getField('title');
                if (!$list[$k]['new_title'] || empty($list[$k]['new_title'])) {
                    unset($list[$k]);
                }
            }
            /*
               * 分页操作逻辑
               * */
            $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
            $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 10;
            $offset = ($page - 1) * $pageSize;
            $pageList = array_slice($list, $offset, $pageSize);
            $count = count($list);
            $res = new Page($count, $pageSize);
            $page = $res->show();
            $this->assign('upList', $pageList)->assign('page', $page);
            $this->display();
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //获取课程的价钱
    public function getClassNormal()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        try {
            $classNormal = M('Product')->where(['id' => $_GET['id']])->getField('price');
            $classNormal = json_decode($classNormal, true);
            return show(1, '获取成功', ['class_normal' => $classNormal]);
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //积分专区
    public function integralSaleList()
    {
        $list = M('bargain')->where(array('type' => 4, 'extra' => 1))->select();
        foreach ($list as $k => $val) {
            $list[$k]['title'] = M('product')->where(['id' => $val['type_id']])->getField('title');
            $list[$k]['org_name'] = M('organization')->where(['token' => $val['token']])->getField('org_name');
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function addIntegralProduct()
    {
        if ($_POST) {
            if (!$_POST['id'] || !is_numeric($_POST['id'])) {
                return show(0, '请选择商品');
            }
            if (!$_POST['key']) {
                return show(0, '请选择商品规格');
            }
            if (!$_POST['integral'] || $_POST['integral'] <= 0) {
                return show(0, '积分不能为空或0');
            }
            $type_id = M('Bargain')->where(array('type' => 4, 'type_id' => $_POST['id'], 'key' => $_POST['key']))->getField('id');
            if ($type_id) {
                return show(0, '不能重复添加');
            }
            $insertData = [
                'price' => $_POST['integral'],
                'key' => $_POST['key'],
                'type' => 4,
                'type_id' => $_POST['id'],
                'token' => M('product')->where(['id' => $_POST['id']])->getField('token'),
                'extra' => 1 //正常显示
            ];
            try {
                $id = D('Bargain')->insert($insertData);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $upList = M('Product')->where(array('status' => 1, 'type' => 2, 'check_status' => 2))->field('desc,cost', true)->select();
            $this->assign('upList', $upList);
            $this->display();
        }
    }

    public function editIntegralProduct()
    {
        if ($_POST) {
            if (!$_POST['id']) {
                return show(0, '参数错误');
            }
            if (!$_POST['integral'] || $_POST['integral'] <= 0) {
                return show(0, '积分不能为空或0');
            }
            $updateData = [
                'price' => $_POST['integral'],
            ];
            $id = D('Bargain')->updateById($_POST['id'], $updateData);
            if ($id === false) {
                return show(0, '修改失败');
            } else {
                return show(1, '修改成功');
            }
        } else {
            if (!$_GET['id'] || !is_numeric($_GET['id'])) {
                return show(0, '参数错误');
            }
            $sale = D('Bargain')->find($_GET['id']);
            $price = M('product')->where(['id' => $sale['type_id']])->getField('price');
            $title = M('product')->where(['id' => $sale['type_id']])->getField('title');
            $price = json_decode($price, true);
            $classNormal = $price[$sale['key']];
            $this->assign('sale', $sale)->assign('classNormal', $classNormal)->assign('title', $title);
            $this->display();
        }
    }

    public function cancelIntegralProduct()
    {
        if (!$_POST['id']) {
            return show(0, 'ID不能为空');
        }
        $id = D('Bargain')->updateById($_POST['id'], ['extra' => 2]);
        if ($id) {
            _getDiscoverInfo();
            _getIndexInfo();
            return show(1, '取消成功');
        } else {
            return show(0, '取消失败');
        }
    }
}