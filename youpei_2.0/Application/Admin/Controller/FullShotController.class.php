<?php
/**
 * 机构全景
 */
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;

class FullShotController extends CommonController
{
    public function getList()
    {
        $orgId = M('organization')->where(['token' => $this->token])->getField('id');
        $classes = D('FullShotClass')->getList(['org_id' => $orgId]);
        $list = [];
        foreach ($classes as $k => $v) {
            $list[$k] = $v;
            $list[$k]['child'] = D('FullShot')->getList(['org_id' => $orgId, 'class_id' => $v['id']]);
        }
        $this->assign('list', $list)->assign('classList', $classes);
        $this->display();
    }

    //添加分类
    public function addFullShotClass()
    {
        if (!$_POST['classTitle']) {
            return show(0, '分类名称不能为空');
        }
        $orgId = M('organization')->where(['token' => $this->token])->getField('id');
        $data = [
            'org_id' => $orgId,
            'class_title' => $_POST['classTitle'],
            'sort' => $_POST['sort'],
        ];
        if ($_POST['id']) {
            $data['id'] = $_POST['id'];
            return $this->saveClass($data);
        }
        try {
            $id = D('FullShotClass')->insert($data);
            if ($id) {
                return show(1, '添加成功');
            } else {
                return show(0, '添加失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //删除分类
    public function deleteClass()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $result = D('FullShotClass')->delete($_POST['id']);
            if ($result) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function getClass()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        $class = D('FullShotClass')->find($_GET['id']);
        return show(1, '获取成功', $class);
    }

    public function addFullShot()
    {
        if ($_POST) {
            if (!$_POST['title']) {
                return show(0, '投票标题不能为空');
            }
            if (!$_POST['class_id']) {
                return show(0, '请选择分类');
            }
            if (!$_POST['image']) {
                unset($_POST['image']);
            }
            try {
                $data = $_POST;
                if ($_POST['id']) {
                    return $this->save($data);
                }
                $data['create_time'] = time();
                $org_id = M('organization')->where(['token' => $this->token])->getField('id');
                $data['org_id'] = $org_id;
                $id = D('FullShot')->insert($data);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $this->display();
        }
    }

    public function editFullShot()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        try {
            $shot = D('FullShot')->find($_GET['id']);
            $classes = D('FullShotClass')->getList(['org_id' => $shot['org_id']]);
            $this->assign('shot', $shot)->assign('classList', $classes);
            $this->display();
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function save($data)
    {
        try {
            $id = D('FullShot')->updateById($data['id'], $data);
            if ($id === false) {
                return show(0, '修改失败');
            }
            return show(1, '修改成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function saveClass($data)
    {
        try {
            $id = D('FullShotClass')->updateById($data['id'], $data);
            if ($id === false) {
                return show(0, '修改失败');
            }
            return show(1, '修改成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function deleteShot()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $result = D('FullShot')->delete($_POST['id']);
            if ($result) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }
}