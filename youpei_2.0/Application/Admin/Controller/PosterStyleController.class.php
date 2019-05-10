<?php
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;

class PosterStyleController extends CommonController
{
    public function index()
    {
        $list = D('PosterStyle')->getList();
        foreach ($list as $k => $v) {
            if ($v['type'] == 1) {
                $list[$k]['org_name'] = '优培教育';
            } else {
                $list[$k]['org_name'] = M('organization')->where(array('id' => $v['type_id']))->getField('org_name');
            }
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function addPoster()
    {
        if ($_POST) {
            if (!$_POST['title']) {
                return show(0, '标题不能为空');
            }
            if (!$_POST['image']) {
                return show(0, '图片不能为空');
            }
            if (!$_POST['image_count']) {
                return show(0, '图片数量不能为空');
            }
            if (!$_POST['text_count']) {
                return show(0, '文字数量不能为空');
            }
            if ($_POST['type'] == 2) {
                if (!$_POST['type_id']) {
                    return show(0, '机构不能为空');
                }
            } elseif ($_POST['type'] == 1) {
                $_POST['type_id'] = '';
            }
            $image_position = [];
            $text_position = [];
            for ($i = 0; $i < $_POST['image_count']; $i++) {
                $image_position[$i + 1][$_POST['image_position_name' . $i]] = $_POST['image_position' . $i];
            }
            for ($i = 0; $i < $_POST['text_count']; $i++) {
                $text_position[$i + 1][$_POST['text_position_name' . $i]] = $_POST['text_position' . $i];
            }
            $data = [
                'title' => $_POST['title'],
                'create_time' => time(),
                'image' => $_POST['image'],
                'image_count' => $_POST['image_count'],
                'text_count' => $_POST['text_count'],
                'type' => $_POST['type'],
                'image_position' => json_encode($image_position),
                'text_position' => json_encode($text_position),
                'type_id' => $_POST['type_id'],
                'color' => $_POST['color']
            ];
            $id = D('PosterStyle')->insert($data);
            if ($id) {
                return show(1, '添加成功');
            } else {
                return show(0, '添加失败');
            }
        } else {
            $orgList = M('organization')->where(array('token' => array('NEQ', 'g232238gc959')))->select();
            $type = [1, 2];
            $this->assign('orgList', $orgList)->assign('type', $type);
            $this->display();
        }
    }

    public function editPoster()
    {
        if ($_POST) {
            if (!$_POST['id']) {
                return show(0, 'ID不能为空');
            }
            if (!$_POST['title']) {
                return show(0, '标题不能为空');
            }
            if (!$_POST['image_count']) {
                return show(0, '图片数量不能为空');
            }
            if (!$_POST['text_count']) {
                return show(0, '文字数量不能为空');
            }
            if ($_POST['type'] == 2) {
                if (!$_POST['type_id']) {
                    return show(0, '机构不能为空');
                }
            } elseif ($_POST['type'] == 1) {
                $_POST['type_id'] = '';
            }
            $image_position = [];
            $text_position = [];
            for ($i = 0; $i < $_POST['image_count']; $i++) {
                $image_position[$i + 1][$_POST['image_position_name' . $i]] = $_POST['image_position' . $i];
            }
            for ($i = 0; $i < $_POST['text_count']; $i++) {
                $text_position[$i + 1][$_POST['text_position_name' . $i]] = $_POST['text_position' . $i];
            }
            $data = [
                'title' => $_POST['title'],
                'image' => $_POST['image'],
                'image_count' => $_POST['image_count'],
                'text_count' => $_POST['text_count'],
                'type' => $_POST['type'],
                'image_position' => json_encode($image_position),
                'text_position' => json_encode($text_position),
                'type_id' => $_POST['type_id'],
                'color' => $_POST['color']
            ];
            if (!$_POST['image'] || empty($_POST['image'])) {
                unset($data['image']);
            }
            $id = D('PosterStyle')->updateById($_POST['id'], $data);
            if ($id !== false) {
                return show(1, '更新成功');
            } else {
                return show(0, '更新失败');
            }
        } else {
            if (!$_GET['id']) {
                return show(0, 'ID参数错误');
            } else {
                $style = D('PosterStyle')->find($_GET['id']);
                $image_position = json_decode($style['image_position'], true);
                $text_position = json_decode($style['text_position'], true);
                $orgList = M('organization')->where(array('token' => array('NEQ', 'g232238gc959')))->select();
                $this->assign('style', $style)->assign('orgList', $orgList)->assign('image_position', $image_position)
                    ->assign('text_position', $text_position);
                $this->display();
            }
        }
    }

    public function delete()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, 'ID参数错误');
        }
        $image = M('GroupProduct')->where("id={$_POST['id']}")->getField('image');
        try {
            //删除图片
            /*$thumbImage = str_replace('.', '_thumb.', $image);
            unlink(dirname(__FILE__) . '/../../..' . $image);
            unlink(dirname(__FILE__) . '/../../..' . $thumbImage);*/
            $id = D('PosterStyle')->delete($_POST['id']);
            if ($id) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }


}