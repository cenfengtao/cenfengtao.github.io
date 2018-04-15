<?php
/**
 *权限管理
 */
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;

class AccessController extends CommonController
{
    public function accessList()
    {
        $roleId = $_GET['roleId'] ? $_GET['roleId'] : D('AdminRole')->getSuperUserId();
        $list = D('Access')->getListFatherId(array('father_id' => 0));
        foreach ($list as $key => $val) {
            $list[$key]['child'] = D('Access')->getListFatherId(array('father_id' => $val['id']));
            $adminAuth[$key] = M('access_auth')->where(['type' => 1, 'type_id' => $roleId, 'acc_id' => $val['id']])->find();
            if($adminAuth[$key]){
                $list[$key]['adminAuth'] = 2;
            }else{
                $list[$key]['adminAuth'] = 1;
            }
            foreach ($list[$key]['child'] as $k => $v) {
                $fatherName = D('Access')->getAccNameById($v['father_id']);
                $fatherName = $fatherName ? $fatherName : '';
                $isAccess = D('AccessAuth')->isAccess($v['id'], $roleId);
                $list[$key]['child'][$k]['isAccess'] = $isAccess ? '已授权' : '未授权';
                $list[$key]['child'][$k]['fatherName'] = $fatherName;
                $adminAuth[$k] = M('access_auth')->where(['type' => 1, 'type_id' => $roleId, 'acc_id' => $v['id']])->find();
                if($adminAuth[$k]){
                    $list[$key]['child'][$k]['adminAuth'] = 2;
                }else{
                    $list[$key]['child'][$k]['adminAuth'] = 1;
                }
            }
            $fatherName = D('Access')->getAccNameById($val['father_id']);
            $fatherName = $fatherName ? $fatherName : '';
            $isAccess = D('AccessAuth')->isAccess($val['id'], $roleId);
            $list[$key]['isAccess'] = $isAccess ? '已授权' : '未授权';
            $list[$key]['fatherName'] = $fatherName;
        }
        $roleList = D('AdminRole')->getRoleList();
        $this->assign('list', $list)->assign('roleList', $roleList)->assign('nowRoleId', $roleId);
        $this->display();
    }

    public function addAccess()
    {
        if ($_POST) {
            if (!isset($_POST['father_id'])) {
                return show(0, '附属菜单不能为空');
            }
            if (!$_POST['acc_name'] || empty($_POST['acc_name'])) {
                return show(0, '权限名称不能为空');
            }
            if (!$_POST['url'] || empty($_POST['url'])) {
                return show(0, '地址不能为空');
            }
            if (!isset($_POST['type'])) {
                return show(0, '类型不能为空');
            }
            $insertData = [
                'father_id' => $_POST['father_id'],
                'acc_name' => $_POST['acc_name'],
                'url' => $_POST['url'],
                'type' => $_POST['type'],
                'description' => $_POST['description'],
            ];
            if ($_POST['id']) {
                $insertData['id'] = $_POST['id'];
                return $this->save($insertData);
            }
            try {
                $id = D('Access')->insert($insertData);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $accMenuList = D('Access')->getMenuList();
            $this->assign('accMenuList', $accMenuList);
            $this->display();
        }
    }

    public function editAccess()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            return show(0, '参数错误');
        }
        try {
            $access = D('Access')->find($_GET['id']);
            $accMenuList = D('Access')->getMenuList();
            $this->assign('access', $access)->assign('accMenuList', $accMenuList);
            $this->display();
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function deleteAcc()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $result = D('Access')->deleteAccById($_POST['id']);
            if ($result) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function save($data)
    {
        $accId = $data['id'];
        unset($data['id']);
        try {
            $id = D('Access')->updateAccById($accId, $data);
            if ($id === false) {
                return show(0, '修改失败');
            }
            return show(1, '修改成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function access()
    {
        $roleId = $_POST['roleId'];
        $accIds = $_POST['accIds'];
        if (!$accIds || !is_array($accIds)) {
            return show(0, '参数错误');
        }
        if (!$roleId || !is_numeric($roleId)) {
            return show(0, '参数错误');
        }
        try {
            D('AccessAuth')->deleteByRoleId($roleId);
            foreach ($accIds as $val) {
                D('AccessAuth')->addAuth($val, $roleId);
            }
            return show(1, '授权成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }
}