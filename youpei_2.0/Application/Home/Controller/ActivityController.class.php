<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

class ActivityController extends BaseController
{
    public function index()
    {
        $this->display();
    }
    public function bargain()
    {
        $this->display();
    }

    public function indexAjax()
    {
        $products = M('Product')->where(array('status' => 1, 'is_activity' => 2, 'type' => 2, 'check_status' => 2))->order('create_time desc')->field('desc,cost', true)->select();
        $courses = M('Product')->where(array('status' => 1, 'is_activity' => 2, 'type' => 1, 'check_status' => 2))->order('create_time desc')->field('desc,cost', true)->select();
        $articles = M('Article')->where(array('show_liability' => 1, 'status' => 2, 'is_activity' => 2))->order('create_time desc')->field('content', true)->select();
        foreach ($articles as $k => $v) {
            $articles[$k]['count'] = D('Comment')->getCountByArtId($v['id']);
            $articles[$k]['collect'] = D('Collect')->getPageByType($v['id'], 5);
        }
        foreach ($courses as $k => $v) {
            if (strlen($courses[$k]['title']) > 14) {
                $courses[$k]['titles'] = mb_substr($courses[$k]['title'], 0, 12, 'utf-8') . '...';
            } else {
                $courses[$k]['titles'] = $courses[$k]['title'];
            }
            $org[$k] = M('organization')->where(array('id' => $v['org_id']))->find();
            $courses[$k]['add'] = $org[$k]['city'] . $org[$k]['area'] . $org[$k]['address'];
            $tags = explode(' ', $v['tag']);
            $courses[$k]['tagA'] = $tags[0] ?: '';
            $courses[$k]['tagB'] = $tags[1] ?: '';
            $courses[$k]['tagC'] = $tags[2] ?: '';
        }
        foreach ($products as $k => $v) {
            if (strlen($products[$k]['title']) > 28) {
                $products[$k]['titles'] = mb_substr($products[$k]['title'], 0, 20, 'utf-8') . '...';
            } else {
                $products[$k]['titles'] = $products[$k]['title'];
            }
        }
        foreach ($articles as $k => $v) {
            $articles[$k]['create_time'] = date("Y-m-d", $v['create_time']);
            $articles[$k]['cate_name'] = M('article_cate')->where(['id' => $v['cate_id']])->getField('title');
            if (strlen($articles[$k]['title']) > 28) {
                $articles[$k]['titles'] = mb_substr($articles[$k]['title'], 0, 20, 'utf-8') . '...';
            } else {
                $articles[$k]['titles'] = $articles[$k]['title'];
            }
        }
        $data = [
            'products' => $products,
            'courses' => $courses,
            'articles' => $articles
        ];
        return show(1, '', $data);
    }

    public function ajaxBargain()
    {
        $bargainList = M('bargain')->where(['type' => 1, 'start_time' => ['lt', time()], 'end_time' => ['gt', time()]])->select();
        $articles = M('Article')->where(array('show_liability' => 1, 'status' => 2, 'is_activity' => 2))->order('create_time desc')->field('content', true)->select();
        $productList = [];
        foreach ($bargainList as $k => $v) {
            $productList[$k] = M('product')->field('desc,cost', true)->where("id={$v['type_id']}")->find();
            $productList[$k]['key'] = $v['key'];
        }
        $courses = [];
        $products = [];
        foreach ($productList as $k => $v) {
            if ($v['type'] == 1) {
                $price = json_decode($v['price'],true);
                $v['price'] = $price[$v['key']];
                $courses[] = $v;
            }
            if ($v['type'] == 2) {
                $price = json_decode($v['price'],true);
                $v['price'] = $price[$v['key']];
                $products[] = $v;
            }
        }
        foreach ($articles as $k => $v) {
            $articles[$k]['count'] = D('Comment')->getCountByArtId($v['id']);
            $articles[$k]['collect'] = D('Collect')->getPageByType($v['id'], 5);
        }
        foreach ($courses as $k => $v) {
            if (strlen($courses[$k]['title']) > 14) {
                $courses[$k]['titles'] = mb_substr($courses[$k]['title'], 0, 12, 'utf-8') . '...';
            } else {
                $courses[$k]['titles'] = $courses[$k]['title'];
            }
            $org[$k] = M('organization')->where(array('id' => $v['org_id']))->find();
            $courses[$k]['add'] = $org[$k]['city'] . $org[$k]['area'] . $org[$k]['address'];
            $tags = explode(' ', $v['tag']);
            $courses[$k]['tagA'] = $tags[0] ?: '';
            $courses[$k]['tagB'] = $tags[1] ?: '';
            $courses[$k]['tagC'] = $tags[2] ?: '';
        }
        foreach ($products as $k => $v) {
            if (strlen($products[$k]['title']) > 28) {
                $products[$k]['titles'] = mb_substr($products[$k]['title'], 0, 20, 'utf-8') . '...';
            } else {
                $products[$k]['titles'] = $products[$k]['title'];
            }
        }
        foreach ($articles as $k => $v) {
            $articles[$k]['create_time'] = date("Y-m-d", $v['create_time']);
            $articles[$k]['cate_name'] = M('article_cate')->where(['id' => $v['cate_id']])->getField('title');
            if (strlen($articles[$k]['title']) > 28) {
                $articles[$k]['titles'] = mb_substr($articles[$k]['title'], 0, 20, 'utf-8') . '...';
            } else {
                $articles[$k]['titles'] = $articles[$k]['title'];
            }
        }
        $data = [
            'products' => $products,
            'courses' => $courses,
            'articles' => $articles
        ];
        return show(1, '', $data);
    }
}

?>