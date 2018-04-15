<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

class DiscoveryController extends BaseController
{
    public function demo()
    {
        $this->display();
    }

    public function ajaxIndex()
    {
        //轮播广告
        $bannerPosters = M('poster')->where(['location' => 2])->select();
        foreach ($bannerPosters as $k => $v) {
            if ($v['type'] == 1) {
                $bannerPosters[$k]['image'] = M('article')->where(['id' => $v['type_id']])->getField('image');
                $bannerPosters[$k]['url'] = "/index.php/Article/getArticle?art_id=" . $v['type_id'];
            } else if ($v['type'] == 2) {
                $bannerPosters[$k]['image'] = M('product')->where(['id' => $v['type_id']])->getField('pic_url');
                $bannerPosters[$k]['url'] = "/index.php/Product/productDetail?pro_id=" . $v['type_id'];
            } else {
                $bannerPosters[$k]['image'] = $v['image'];
                $bannerPosters[$k]['url'] = $v['type_id'];
            }
        }
        $discoverInfoList = S('DISCOVER_INFO_LIST');
        if (empty($discoverInfoList)) {
            $discoverInfoList = _getDiscoverInfo();
        }
        //最新内容（前三条）
        $discoverInfoList = array_slice($discoverInfoList, 0, 3);
        $newest = [];
        foreach ($discoverInfoList as $k => $v) { //type:1-文章 2-视频 3-普通商品&课程 4-团购课程 5-砍价 6-团购商品
            if ($v['type'] == 1) {
                $article = M('article')->field('content', true)->where(['id' => $v['id']])->find();
                $start = mb_strpos($article['title'], '】', 0, 'utf-8');
                $firstTitle = mb_substr($article['title'], 0, $start + 1, 'utf-8');
                $secondTitle = mb_substr($article['title'], $start + 1, null, 'utf-8');
                $secondTitle = mb_strlen($secondTitle, 'utf-8') > 12 ? mb_substr($secondTitle, 0, 18, 'utf-8') : $secondTitle;
                $newest[$k] = [
                    'comment_count' => D('Comment')->getCountByArtId($article['id']),
                    'collect_count' => D('Collect')->getPageByType($article['id'], 5),
                    'article' => $article,
                    'firstTitle' => $firstTitle,
                    'secondTitle' => $secondTitle,
                    'class_title' => M('article_cate')->where(['id' => $article['cate_id']])->getField('title'),
                    'type' => 1
                ];
            } else if ($v['type'] == 2) {
                $video = M('article')->field('id,image')->where(['id' => $v['id']])->find();
                $newest[$k] = [
                    'video' => $video,
                    'type' => 2
                ];
            } else if ($v['type'] == 3) {
                $class = M('product')->field('content,desc', true)->where(['id' => $v['id']])->find();
                $tag = explode(' ', $class['tag']);
                $newest[$k]['tagA'] = $tag[0] ?: '';
                $newest[$k]['tagB'] = $tag[1] ?: '';
                $newest[$k]['tagC'] = $tag[2] ?: '';
                $price = json_decode($class['price'], true);
                $newest[$k]['original_price'] = reset($price)['original_price'];
                $newest[$k]['now_price'] = reset($price)['now_price'];
                $newest[$k]['logo'] = M('organization')->where(array('token' => $class['token']))->getField('picture');
                $newest[$k]['class'] = $class;
                $newest[$k]['type'] = 3;
                $newest[$k]['class']['title'] = mb_strlen($newest[$k]['class']['title'], 'utf-8') > 10 ? mb_substr($newest[$k]['class']['title'], 0, 20, 'utf-8') : $newest[$k]['class']['title'];
            } else if ($v['type'] == 4) {
                $group = M('group_product')->field('description', true)->where(['id' => $v['id']])->find();
                $userCount = M('order')->where(array('group_id' => $group['id'],
                    'status' => array(array('eq', 1), array('eq', 4), 'OR')))->sum('amount');
                $tag = explode(' ', $group['tag']);
                $newest[$k] = [
                    'tagA' => $tag[0] ?: '',
                    'tagB' => $tag[1] ?: '',
                    'title' => mb_strlen($group['title'], 'utf-8') > 10 ? mb_substr($group['title'], 0, 10, 'utf-8') : $group['title'],
                    'userCount' => $userCount ?: 0,
                    'group' => $group,
                    'type' => 4,
                    'logo' => M('organization')->where(array('token' => $group['token']))->getField('picture'),
                ];
            } else if ($v['type'] == 5) {
                $info = [];
                foreach ($v['info'] as $val) {
                    $bargain = M('bargain')->where(['id' => $val['id']])->find();
                    $product = M('product')->field('pic_url,title,price,tag')->where(['id' => $bargain['type_id']])->find();
                    $bargainTag = explode(' ', $product['tag']);
                    $prices = json_decode($product['price'], true);
                    $price = $prices[$bargain['key']]['now_price'];
                    $info[] = [
                        'pro_id' => $bargain['type_id'],
                        'key' => $bargain['type'],
                        'tagA' => $bargainTag[0] ?: '',
                        'tagB' => $bargainTag[1] ?: '',
                        'tagC' => $bargainTag[2] ?: '',
                        'image' => $product['pic_url'],
                        'title' => mb_strlen($product['title'], 'utf-8') > 10 ? mb_substr($product['title'], 0, 10, 'utf-8') : $product['title'],
                        'price' => $price
                    ];
                }
                $newest[$k] = [
                    'type' => 5,
                    'info' => $info,
                ];
            } else if ($v['type'] == 6) {
                $info = [];
                foreach ($v['info'] as $key => $val) {
                    $group = M('group_product')->field('description', true)->where(['id' => $val['id']])->find();
                    $userCount = M('order')->where(array('group_id' => $group['id'],
                        'status' => array(array('eq', 1), array('eq', 4), 'OR')))->sum('amount');
                    $tag = explode(' ', $group['tag']);
                    $info[$key] = $group;
                    $info[$key]['tagA'] = $tag[0] ?: '';
                    $info[$key]['title'] = mb_strlen($group['title'], 'utf-8') > 10 ? mb_substr($group['title'], 0, 10, 'utf-8') : $group['title'];
                    $info[$key]['userCount'] = $userCount ?: 0;
                }
                $newest[$k] = [
                    'type' => 6,
                    'info' => $info,
                ];
            }
        }
        $data = [
            'bannerPosters' => $bannerPosters,
            'newest' => $newest,
        ];
        return show(1, '获取成功', $data);
    }

    public function index()
    {
        $this->title = "发现";
        try {
            $current = (int)I('current', 2);
            $this->assign('current', $current);
            //导航分类列表
            $cateList = D('ArticleCate')->getList();
            $this->assign('cateList', $cateList);
            //精选育儿文章
            $helpChildList = D('Article')->getArticleByClass('育儿', 'id,image,title');
            $this->assign('helpChildList', $helpChildList);
            //精选福利文章
            $parentList = D('Article')->getArticleByClass('福利', 'id,image,title');
            $this->assign('parentList', $parentList);
            //热销商品
            $saleProduct = D('Product')->getSaleList(2, 6, 'id,pic_url,title,f_title,price');
            foreach ($saleProduct as $k => $v) {
                $prices = json_decode($v['price'], true);
                $price = $prices[key($prices)];
                $saleProduct[$k]['price'] = $price;
            }
            $this->assign('saleProduct', $saleProduct);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function loadingProduct()
    {
        $npage = (int)I("npage");
        $list = D('Product')->getListByPage($npage, 2);
        foreach ($list as $k => $v) {
            $prices = json_decode($v['price'], true);
            $price = $prices[key($prices)];
            $list[$k]['price'] = $price;
        }
        if (!$list || empty($list)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '没有数据'));
        }
        $this->ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $list));
    }


    public function loadingIndex()
    {
        if (!$_GET['page']) {
            return show(0, '没有更多了');
        }
        $discoverInfoList = S('DISCOVER_INFO_LIST');
        if (empty($discoverInfoList)) {
            $discoverInfoList = _getDiscoverInfo();
        }
        //最新内容（前三条）
        $discoverInfoList = array_slice($discoverInfoList, $_GET['page'], 6);
        $newest = [];
        foreach ($discoverInfoList as $k => $v) { //type:1-文章 2-视频 3-普通商品&课程 4-团购课程 5-砍价 6-团购商品
            if ($v['type'] == 1) {
                $article = M('article')->field('content', true)->where(['id' => $v['id']])->find();
                $start = mb_strpos($article['title'], '】', 0, 'utf-8');
                $firstTitle = mb_substr($article['title'], 0, $start + 1, 'utf-8');
                $secondTitle = mb_substr($article['title'], $start + 1, null, 'utf-8');
                $secondTitle = mb_strlen($secondTitle, 'utf-8') > 12 ? mb_substr($secondTitle, 0, 18, 'utf-8') : $secondTitle;
                $newest[$k] = [
                    'comment_count' => D('Comment')->getCountByArtId($article['id']),
                    'collect_count' => D('Collect')->getPageByType($article['id'], 5),
                    'article' => $article,
                    'firstTitle' => $firstTitle,
                    'secondTitle' => $secondTitle,
                    'class_title' => M('article_cate')->where(['id' => $article['cate_id']])->getField('title'),
                    'type' => 1
                ];
            } else if ($v['type'] == 2) {
                $video = M('article')->field('id,image')->where(['id' => $v['id']])->find();
                $newest[$k] = [
                    'video' => $video,
                    'type' => 2
                ];
            } else if ($v['type'] == 3) {
                $class = M('product')->field('content,desc', true)->where(['id' => $v['id']])->find();
                $tag = explode(' ', $class['tag']);
                $newest[$k]['tagA'] = $tag[0] ?: '';
                $newest[$k]['tagB'] = $tag[1] ?: '';
                $newest[$k]['tagC'] = $tag[2] ?: '';
                $price = json_decode($class['price'], true);
                $newest[$k]['original_price'] = reset($price)['original_price'];
                $newest[$k]['now_price'] = reset($price)['now_price'];
                $newest[$k]['logo'] = M('organization')->where(array('token' => $class['token']))->getField('picture');
                $newest[$k]['class'] = $class;
                $newest[$k]['type'] = 3;
                $newest[$k]['class']['title'] = mb_strlen($newest[$k]['class']['title'], 'utf-8') > 10 ? mb_substr($newest[$k]['class']['title'], 0, 20, 'utf-8') : $newest[$k]['class']['title'];
            } else if ($v['type'] == 4) {
                $group = M('group_product')->field('description', true)->where(['id' => $v['id']])->find();
                $userCount = M('order')->where(array('group_id' => $group['id'],
                    'status' => array(array('eq', 1), array('eq', 4), 'OR')))->sum('amount');
                $tag = explode(' ', $group['tag']);
                $newest[$k] = [
                    'tagA' => $tag[0] ?: '',
                    'tagB' => $tag[1] ?: '',
                    'title' => mb_strlen($group['title'], 'utf-8') > 10 ? mb_substr($group['title'], 0, 10, 'utf-8') : $group['title'],
                    'userCount' => $userCount ?: 0,
                    'group' => $group,
                    'type' => 4,
                    'logo' => M('organization')->where(array('token' => $group['token']))->getField('picture'),
                ];
            } else if ($v['type'] == 5) {
                $info = [];
                foreach ($v['info'] as $val) {
                    $bargain = M('bargain')->where(['id' => $val['id']])->find();
                    $product = M('product')->field('pic_url,title,price,tag')->where(['id' => $bargain['type_id']])->find();
                    $bargainTag = explode(' ', $product['tag']);
                    $prices = json_decode($product['price'], true);
                    $price = $prices[$bargain['key']]['now_price'];
                    $info[] = [
                        'id' => $bargain['id'],
                        'pro_id' => $bargain['type_id'],
                        'key' => $bargain['type'],
                        'tagA' => $bargainTag[0] ?: '',
                        'tagB' => $bargainTag[1] ?: '',
                        'tagC' => $bargainTag[2] ?: '',
                        'image' => $product['pic_url'],
                        'title' => mb_strlen($product['title'], 'utf-8') > 10 ? mb_substr($product['title'], 0, 10, 'utf-8') : $product['title'],
                        'price' => $price
                    ];
                }
                $newest[$k] = [
                    'type' => 5,
                    'info' => $info,
                ];
            } else if ($v['type'] == 6) {
                $info = [];
                foreach ($v['info'] as $key => $val) {
                    $group = M('group_product')->field('description', true)->where(['id' => $val['id']])->find();
                    $userCount = M('order')->where(array('group_id' => $group['id'],
                        'status' => array(array('eq', 1), array('eq', 4), 'OR')))->sum('amount');
                    $tag = explode(' ', $group['tag']);
                    $info[$key] = $group;
                    $info[$key]['tagA'] = $tag[0] ?: '';
                    $info[$key]['title'] = mb_strlen($group['title'], 'utf-8') > 10 ? mb_substr($group['title'], 0, 10, 'utf-8') : $group['title'];
                    $info[$key]['userCount'] = $userCount ?: 0;
                }
                $newest[$k] = [
                    'type' => 6,
                    'info' => $info,
                ];
            }
        }


        return show(1, '获取成功', ['newest' => $newest]);
    }

}