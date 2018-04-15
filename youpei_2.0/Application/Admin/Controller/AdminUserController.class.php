<?php
/**
 *管理员管理
 */
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;

class AdminUserController extends CommonController
{
    //自己修改资料
    public function editAdminUser()
    {
        if ($_POST) {
            if (!$_POST['mobile'] || empty($_POST['mobile'])) {
                return show(0, '手机不能为空');
            }
            if (!preg_match('/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/', $_POST['mobile'])) {
                return show(0, '手机格式不正确');
            }
            if (!$_POST['passwordA'] || !$_POST['passwordA']) {
                return show(0, '密码不能为空');
            }
            if ($_POST['passwordA'] != $_POST['passwordB']) {
                return show(0, '两次密码不一致');
            }
            try {
                $md5Password = getMd5Password($_POST['passwordA']);
                $result = D('AdminUser')->updateAdminUserById($this->user['id'],
                    array('password' => $md5Password, 'mobile' => $_POST['mobile']));
                if (!$result) {
                    return show(0, '修改失败');
                } else {
                    return show(1, '修改成功');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $adminUser = $this->getAdminUser();
            $this->assign('mobile', $adminUser['mobile']);
            $this->display();
        }
    }

    //添加管理员
    public function addAdminUser()
    {
        if ($_POST) {
            if (!$_POST['username'] || empty($_POST['username'])) {
                return show(0, '用户名不能为空');
            }
            if (!$_POST['passwordA'] || empty($_POST['passwordA'])) {
                return show(0, '密码不能为空');
            }
            if (!preg_match('/^(?=.*\d)((?=.*[a-z])|(?=.*[A-Z]))[a-zA-Z\d]{8,20}$/', $_POST['passwordA'])) {
                return show(0, '密码格式不正确');
            }
            if ($_POST['passwordA'] != $_POST['passwordB']) {
                return show(0, '两次密码不一致');
            }
            if (!$_POST['mobile']) {
                return show(0, '联系手机不能为空');
            }
            if (!empty($_POST['mobile']) && (!preg_match('/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/', $_POST['mobile']))) {
                return show(0, '手机格式不正确');
            }
            if (!empty($_POST['mobile'])) {
                $isRepeatMobile = D('AdminUser')->isRepeatMobile($_POST['mobile']);
                if ($isRepeatMobile) {
                    return show(0, '该手机已存在');
                }
            }
            if ($_POST['role_id'] == 1 || $_POST['role_id'] == 3) {
                $_POST['org_id'] = 1;
            }else if(!$_POST['org_id'] || empty($_POST['org_id'])){
                return show(0,'机构不能为空');
            }
            $token = M('organization')->where(array('id' => $_POST['org_id']))->getField('token');
            $wxuserId = M('wxuser')->where(array('token' => $token))->getField('id');
            $_POST['wxuser_id'] = $wxuserId;
            $insertData = $_POST;
            $insertData['password'] = getMd5Password($_POST['passwordA']);
            try {
                $id = D('AdminUser')->insert($insertData);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $roleList = D('AdminRole')->getRoleList();
            $orgList = D('Organization')->getOrgList();
            $this->assign('roleList', $roleList)->assign('orgList', $orgList);
            $this->display();
        }
    }

    public function userList()
    {
        $list = D('AdminUser')->getList();
        foreach ($list as $key => $val) {
            $orgName = D('Organization')->getOrgNameById($val['org_id']);
            $roleTitle = D('AdminRole')->getTitleById($val['role_id']);
            $list[$key]['role_title'] = $roleTitle;
            $list[$key]['org_name'] = $orgName;
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function delete()
    {
        if (!$_POST['id'] || !is_numeric($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $result = D('AdminUser')->deleteUserById($_POST['id']);
            if ($result) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //超级管理员修改资料
    public function editBySuperUser()
    {
        if ($_POST) {
            if (!$_POST['id'] || !is_numeric($_POST['id'])) {
                return show(0, '参数错误');
            }
            if (!$_POST['username'] || empty($_POST['username'])) {
                return show(0, '用户名不能为空');
            }
            if (!empty($_POST['passwordA']) && !preg_match('/^(?=.*\d)((?=.*[a-z])|(?=.*[A-Z]))[a-zA-Z\d]{8,20}$/', $_POST['passwordA'])) {
                return show(0, '密码格式不正确');
            }
            if ($_POST['passwordA'] != $_POST['passwordB']) {
                return show(0, '两次密码不一致');
            }
            if (!preg_match('/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/', $_POST['mobile'])) {
                return show(0, '手机格式不正确');
            }
            if ($_POST['role_id'] == 1) {
                $_POST['org_id'] = 1;
            }
            $token = M('organization')->where(array('id' => $_POST['org_id']))->getField('token');
            $wxuserId = M('wxuser')->where(array('token' => $token))->getField('id');
            $_POST['wxuser_id'] = $wxuserId;
            $updateData = $_POST;
            if (!empty($_POST['passwordA'])) {
                $updateData['password'] = getMd5Password($_POST['passwordA']);
            }
            return $this->save($updateData);
        } else {
            if (!$_GET['id'] || !is_numeric($_GET['id'])) {
                return show(0, '参数错误');
            }
            try {
                $user = D('AdminUser')->find($_GET['id']);
                $roleList = D('AdminRole')->getRoleList();
                $orgList = D('Organization')->getOrgList();
                $this->assign('user', $user)->assign('roleList', $roleList)->assign('orgList', $orgList);
                $this->display();
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        }
        return false;
    }

    public function save($data)
    {
        $userId = $data['id'];
        unset($data['id']);
        try {
            $id = D('AdminUser')->updateUserById($userId, $data);
            if ($id === false) {
                return show(0, '修改失败');
            }
            return show(1, '修改成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }
}