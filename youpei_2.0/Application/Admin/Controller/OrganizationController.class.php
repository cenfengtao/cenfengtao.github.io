<?php
/**
 *机构管理
 */
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;
use Think\Page;

class OrganizationController extends CommonController
{
    public function orgList()
    {
        $list = D('Organization')->getOrgList();
        $this->assign('orgList', $list);
        $this->display();
    }

    public function addOrganization()
    {
        if ($_POST) {
            if (!$_POST['org_name'] || empty($_POST['org_name'])) {
                return show(0, '机构名称不能为空');
            }
            if (empty($_POST['tel'])) {
                return show(0, '联系电话不能为空');
            }
            if (!$_POST['picture'] || empty($_POST['picture'])) {
                unset($_POST['picture']);
            }
            if (!$_POST['qr_code'] || empty($_POST['qr_code'])) {
                unset($_POST['qr_code']);
            }
            if (!$_POST['cover_image'] || empty($_POST['cover_image'])) {
                unset($_POST['cover_image']);
            }
            if ($_POST['id'] && !empty($_POST['id'])) {
                return $this->save($_POST);
            }
            try {
                $_POST['create_time'] = time();
                $id = D('Organization')->insert($_POST);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $list = M('wxuser')->field('wxname,token')->select();
            foreach ($list as $k => $v) {
                $isToken = D('Organization')->isTokenById($v['token']);
                if ($isToken) {
                    unset($list[$k]);
                }
            }
            $this->assign('orgList', $list);
            $this->display();
        }
    }

    public function save($data)
    {
        try {
            $id = D('Organization')->updateById($data['id'], $data);
            if ($id === false) {
                return show(0, '修改失败');
            }
            return show(1, '修改成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $result = D('Organization')->delete($_POST['id']);
            if ($result) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //超级管理员修改
    public function editOrganization()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            $this->error('ID参数错误');
        }
        try {
            $organization = D('Organization')->find($_GET['id']);
            $list = M('wxuser')->field('wxname,token')->select();
            foreach ($list as $k => $v) {
                if ($organization['token'] == $v['token']) {
                    $organization['wxname'] = $v['wxname'];
                }
                $isToken = D('Organization')->isTokenById($v['token']);
                if ($isToken) {
                    unset($list[$k]);
                }
            }
            $data = array([
                'wxname' => $organization['wxname'],
                'token' => $organization['token']
            ]);
            $merge = array_merge($list, $data);
            $this->assign('organization', $organization)->assign('list', $merge);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    //机构自己修改
    public function editOrganizationByToken()
    {
        try {
            $organization = D('Organization')->findByToken($this->token);
            $this->assign('organization', $organization);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    //机构文章列表
    public function getArticleList()
    {
        /*
       * 分页操作逻辑
       * */
        $page = $_REQUEST['p'] ? $_REQUEST['p'] : 1;
        $pageSize = $_REQUEST['pageSize'] ? $_REQUEST['pageSize'] : 10;
        $list = D('Article')->getListForAdmin(array('token' => $this->token), $page, $pageSize, 'title,id,create_time,image,cate_id,read_count,sort');
        $count = D('Article')->getCount();
        $res = new Page($count, $pageSize);
        $page = $res->show();
        $this->assign('artList', $list)->assign('page', $page);
        $this->display();
    }

    //轮播文章列表
    public function bannerList()
    {
        if ($this->isSuper) {
            $bannerList = M('organization_banner')->select();
        } else {
            $bannerList = M('organization_banner')->where(array('token' => $this->token))->select();
        }
        $list = [];
        foreach ($bannerList as $k => $v) {
            if ($v['type'] == 1) { //文章
                $article = D('Article')->find($v['type_id']);
                $list[$k] = [
                    'id' => $v['id'],
                    'create_time' => $v['create_time'],
                    'title' => $article['title'],
                    'type' => $v['type'],
                    'image' => $article['image'],
                    'orgname' => M('organization')->where(array('token' => $v['token']))->getField('org_name')
                ];
            }
            if ($v['type'] == 2) { //课程
                $product = D('Product')->find($v['type_id']);
                $list[$k] = [
                    'id' => $v['id'],
                    'create_time' => $v['create_time'],
                    'title' => $product['title'],
                    'type' => $v['type'],
                    'image' => $product['pic_url'],
                    'orgname' => M('organization')->where(array('token' => $v['token']))->getField('org_name')
                ];
            }
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function addBanner()
    {
        if ($_POST) {
            if (!$_POST['type'] || !is_numeric($_POST['type'])) {
                return show(0, '类型不能为空');
            }
            if (($_POST['type'] == 1 && !$_POST['art_id']) || ($_POST['type'] == 2 && !$_POST['pro_id'])) {
                return show(0, '类型ID不能为空');
            }
            $insertData = [
                'token' => $this->token,
                'create_time' => time(),
                'type' => $_POST['type'],
                'type_id' => $_POST['type'] == 1 ? $_POST['art_id'] : $_POST['pro_id'],
            ];
            $id = D('OrganizationBanner')->insert($insertData);
            if ($id) {
                return show(1, '添加成功');
            } else {
                return show(0, '添加失败');
            }
        } else {
            $articleList = D('Article')->getArticleByToken($this->token, 'id,title');
            $productList = M('Product')->where(array('status' => 1, 'token' => $this->token))->field('title,id')->select();
            $this->assign('articleList', $articleList)->assign('productList', $productList);
            $this->display();
        }
    }

    public function editBanner()
    {
        if ($_POST) {
            if (!$_POST['id'] || !is_numeric($_POST['id'])) {
                return show(0, 'ID参数错误');
            }
            if (!$_POST['type'] || empty($_POST['type'])) {
                return show(0, '类型不能为空');
            }
            if (($_POST['type'] == 1 && !$_POST['art_id']) || ($_POST['type'] == 2 && !$_POST['pro_id'])) {
                return show(0, '类型ID不能为空');
            }
            $id = D('OrganizationBanner')->updateById($_POST['id'], array('type' => $_POST['type'], 'type_id' => $_POST['type'] == 1 ? $_POST['art_id'] : $_POST['pro_id']));
            if ($id !== false) {
                return show(1, '修改成功');
            } else {
                return show(0, '修改失败');
            }
        } else {
            if (!$_GET['id'] || !is_numeric($_GET['id'])) {
                return show(0, 'ID参数错误');
            }
            $banner = D('OrganizationBanner')->find($_GET['id']);
            $articleList = D('Article')->getArticleByToken($this->token, 'id,title');
            $productList = M('Product')->where(array('status' => 1, 'token' => $this->token))->field('title,id')->select();
            $this->assign('articleList', $articleList)->assign('productList', $productList)->assign('banner', $banner);
            $this->display();
        }
    }

    public function deleteBanner()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $result = D('OrganizationBanner')->delete($_POST['id']);
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