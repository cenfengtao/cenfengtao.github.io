<?php
/*亲子活动管理*/
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;

class ParentingController extends CommonController
{
    //上架列表
    public function index()
    {
        if ($this->isSuper) {
            $List = D('Parenting')->getList(array('status' => 1));
        } else {
            $List = D('Parenting')->getList(array('token' => $this->token, 'status' => 1));
        }
        foreach ($List as $k => $v) {
            $List[$k]['org_name'] = D('Organization')->getOrgNameById($v['org_id']);
        }
        $this->assign('List', $List);
        $this->display();
    }

    public function addParenting()
    {
        if ($_POST) {
            if (!$_POST['title'] || empty($_POST['title'])) {
                return show(0, '商品名称不能为空');
            }
            if (!$_POST['org_id'] || empty($_POST['org_id'])) {
                return show(0, '机构不能为空');
            }
            if (!$_POST['count'] || $_POST['count'] < 0) {
                return show(0, '库存不能少于0');
            }
            if (!$_POST['original_price'] || $_POST['original_price'] < 0) {
                return show(0, '原价不能少于0');
            }
            if (!$_POST['price'] || $_POST['price'] < 0) {
                return show(0, '现价不能少于0');
            }
            if (empty($_POST['image'])) {
                unset($_POST['image']);
            }
            $_POST['token'] = $this->token;
            $_POST['create_time'] = time();
            if ($_POST['id'] && !empty($_POST['id'])) {
                return $this->save($_POST);
            }
            try {
                $id = D('Parenting')->insert($_POST);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $classList = D('ParentingCate')->getList();
            $orgList = D('Organization')->getOrgList();
            $this->assign('org_list', $orgList)->assign('class_list', $classList);
            $this->display();
        }
    }

    //下架列表
    public function parDownList()
    {
        if ($this->isSuper) {
            $downList = D('Parenting')->getDownList();
        } else {
            $downList = M('Parenting')->where(array('token' => $this->token, 'status' => 2))->select();
        }
        foreach ($downList as $k => $v) {
            $downList[$k]['org_name'] = D('Organization')->getOrgNameById($v['org_id']);
        }
        $this->assign('downList', $downList);
        $this->display();
    }


    //下架
    public function soldOut()
    {
        if (!$_POST['id'] || !is_numeric($_POST['id'])) {
            return show(0, 'ID参数错误');
        }
        try {
            $id = D('Parenting')->soldOutById($_POST['id']);
            if ($id) {
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
            $id = D('Parenting')->putAwayById($_POST['id']);
            if ($id) {
                return show(1, '上架成功');
            } else {
                return show(0, '上架失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function editParenting()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            $this->error('ID参数错误');
        }
        try {
            $parenting = D('Parenting')->find($_GET['id']);
            $orgList = D('Organization')->getOrgList();
            $cate_list = D('ParentingCate')->getList();
            $this->assign('org_list', $orgList)->assign('parenting', $parenting)->assign('cate_list', $cate_list);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function save($data)
    {
        try {
            $id = D('Parenting')->updateById($data['id'], $data);
            if ($id === false) {
                return show(0, '修改失败');
            }
            return show(1, '修改成功待审核');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }


    public function groupParentingList()
    {
        if ($this->isSuper) {
            $groupList = M('group_product')->order('create_time desc')->where(array('type' => 3))
                ->field('class_time,description,cost', true)->select();
        } else {
            $groupList = M('group_product')->where(array('type' => 3, 'token' => $this->token))->order('create_time desc')
                ->field('class_time,description,cost', true)->select();
        }
        foreach ($groupList as $k => $v) {
            $groupList[$k]['class_name'] = M('parenting_cate')->where("id={$v['class_id']}")->getField('cate_title');
            $groupList[$k]['org_name'] = D('Organization')->getOrgnameByToken($v['token']);
        }
        $this->assign('groupList', $groupList);
        $this->display();
    }


    //添加团购亲子活动
    public function addGroupParenting()
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
            $insertData = $_POST;
            $insertData['token'] = $this->token;
            $insertData['create_time'] = time();
            $insertData['type'] = 3;
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
            $classList = M('parenting_cate')->select();
            $this->assign('class_list', $classList);
            $this->display();
        }
    }


    public function editGroupParenting()
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
            $classList = M('parenting_cate')->select();
            $this->assign('product', $product)->assign('class_list', $classList);
            $this->display();
        }
    }
    
}