<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends BaseController
{
    public function index()
    {
        $this->display();
    }

    public function play()
    {
        $this->display();
    }

    public function ajaxIndex()
    {
        //轮播图
        $picture = D('Picture')->getList();
        //首页分类
        $homeClass = D('Homeclassify')->getHomec();
        //限时抢购
        $typeIds = M('Bargain')->distinct(true)->field('type_id')->where(array('type' => 2, 'start_time' =>
            array('elt', time()), 'end_time' => array('gt', time())))->limit(4)->select();
        $rushList = [];
        if (!empty($typeIds)) {
            foreach ($typeIds as $key => $val) {
                $rushList[$key] = M('Bargain')->where(array('type' => 2, 'type_id' => $val['type_id'],
                    'start_time' => array('elt', time()), 'end_time' => array('gt', time())))->find();
            }
        }
        if (empty($rushList)) {// 如果没有抢购的话就显示普通课程
            $limitBuy = D('Product')->getSaleList(1, 3, "id,title,f_title,pic_url,type,price,status,tag,is_hot,city,area,address");
            foreach ($limitBuy as $k => $v) {
                $price = json_decode($v['price'], true);
                $limitBuy[$k]['original_price'] = reset($price)['original_price'];
                $limitBuy[$k]['now_price'] = reset($price)['now_price'];
                $limitBuy[$k]['thumb_image'] = str_replace('.', '_thumb.', $v['pic_url']);
                $tags = explode(' ', $v['tag']);
                $limitBuy[$k]['tagA'] = $tags[0] ?: '';
                $limitBuy[$k]['tagB'] = $tags[1] ?: '';
                $limitBuy[$k]['tagC'] = $tags[2] ?: '';
                $limitBuy[$k]['title'] = mb_substr($limitBuy[$k]['title'], 0, 14);
                $limitBuy[$k]['key'] = 0;
            }
        } else {
            $limitBuy = [];
            foreach ($rushList as $key => $v) {
                $limitBuy[$key] = M('Product')->field("id,title,f_title,pic_url,type,
                price,status,tag,is_hot,city,area,address")->where(['id' => $v['type_id']])->find();
                $limitBuy[$key]['key'] = $v['key'];
                $limitBuy[$key]['now_price'] = $v['price'];
                $limitBuy[$key]['end_time'] = $v['end_time'];
            }
            foreach ($limitBuy as $k => $v) {
                $price = json_decode($v['price'], true);
                $limitBuy[$k]['original_price'] = $price[$v['key']]['now_price'];
                $limitBuy[$k]['thumb_image'] = str_replace('.', '_thumb.', $v['pic_url']);
                $tags = explode(' ', $v['tag']);
                $limitBuy[$k]['tagA'] = $tags[0] ?: '';
                $limitBuy[$k]['tagB'] = $tags[1] ?: '';
                $limitBuy[$k]['tagC'] = $tags[2] ?: '';
                $limitBuy[$k]['title'] = mb_substr($limitBuy[$k]['title'], 0, 10);
            }
        }
        //精选商品分类
        $siftsClass = M('product_class')->where(array('type' => 2))->order('sort asc')->limit(0, 3)->select();
        foreach ($siftsClass as $l => $i) {
            $siftsClass[$l]['siftsProduct'] = M('product')->field("id,title,f_title,pic_url,type,price,status,tag,is_hot")
                ->where(array("type" => 2, 'is_hot' => 2, 'check_status' => 2, 'class_id' => $i['id'], 'status' => 1))->order('sort desc')->limit(4)->select();
            //精选商品
            foreach ($siftsClass[$l]['siftsProduct'] as $k => $v) {
                $tags = explode(' ', $v['tag']);
                $siftsClass[$l]['siftsProduct'][$k]['tagA'] = $tags[0] ?: '';
                $siftsClass[$l]['siftsProduct'][$k]['tagB'] = $tags[1] ?: '';
                $siftsClass[$l]['siftsProduct'][$k]['tagC'] = $tags[2] ?: '';
                $price = json_decode($v['price'], true);
                $siftsClass[$l]['siftsProduct'][$k]['original_price'] = reset($price)['original_price'];
                $siftsClass[$l]['siftsProduct'][$k]['now_price'] = reset($price)['now_price'];
                $siftsClass[$l]['siftsProduct'][$k]['thumb_image'] = str_replace('.', '_thumb.', $v['pic_url']);
            }
        }
//        平台消息
        $message = M('UserMessage')->where(array('user_id' => $this->user['id'], 'is_read' => 1))->count();
        return show(1, '', ['picture' => $picture, 'homeClass' => $homeClass, 'limitBuy' => $limitBuy,
            'siftsClass' => $siftsClass, 'message' => $message]);
    }

    public function getGroupProduct()
    {
        if (!isset($_GET['page'])) {
            return show(0, '分页数据不能为空');
        }
        $indexInfoList = S('INDEX_INFO_LIST');
        if (empty($indexInfoList)) {
            $indexInfoList = _getIndexInfo();
        }
        $indexInfoList = array_slice($indexInfoList, $_GET['page'], 2);
        return show(1, '', $indexInfoList);
    }


    public function search()
    {
        $tags = M('config')->where("token='{$this->token}'")->getField('search_tag');
        $searchTags = explode(' ', $tags);
        $searchHistory = D('SearchRecord')->getHistory($this->user['id']);
        $this->assign('searchTags', $searchTags)->assign('searchHistory', $searchHistory);
        $this->display();
    }

    public function searchResult()
    {
        if (!$_GET['type'] || empty($_GET['type'])) {  //type 1:文章 2:课程 3:商品 4:机构
            $this->error('搜索类型错误');
        };
        if (!$_GET['word'] || empty($_GET['word'])) {
            $this->error('搜索内容不能为空');
        }
        //添加到搜索记录表
        $isRecord = D('SearchRecord')->isRecordByWord($this->user['id'], $_GET['word']);
        if ($isRecord) {
            D('SearchRecord')->updateById($isRecord, array("create_time" => time()));
        } else {
            $recordData = [
                "user_id" => $this->user['id'],
                'word' => $_GET['word'],
                'create_time' => time(),
                'type' => $_GET['type'],
            ];
            D('SearchRecord')->insert($recordData);
            //判断记录有否超过7条，删除最后一条
            D('SearchRecord')->deleteLastRecord($this->user['id']);
        }
        switch ($_GET['type']) {
            case 1:
                $result = M('article')->order('create_time desc')->where(array("title" => array('like', "%{$_GET['word']}%"), 'status' => 2))->field('content', true)->limit(0, 10)->select();
                foreach ($result as $k => $v) {
                    $result[$k]['search_type'] = 1;
                    $result[$k]['time'] = date('Y年m月d日', $v['create_time']);
                }
                break;
            case 2:
                $result = M('product')->order('start_time desc')->where(array('title' => array('like', "%{$_GET['word']}%"), 'type' => 1, 'status' => 1, 'check_status' => 2))->limit(0, 10)->select();
                foreach ($result as $k => $v) {
                    $result[$k]['search_type'] = 2;
                    $result[$k]['time'] = date('Y年m月d日', $v['create_time']);
                    $price = json_decode($v['price'], true);
                    $result[$k]['original_price'] = reset($price)['original_price'];
                    $result[$k]['now_price'] = reset($price)['now_price'];
                }
                break;
            case 3:
                $result = M('product')->order('start_time desc')->where(array('title' => array('like', "%{$_GET['word']}%"), 'type' => 2, 'status' => 1, 'check_status' => 2))->limit(0, 10)->select();
                foreach ($result as $k => $v) {
                    $result[$k]['search_type'] = 3;
                    $result[$k]['time'] = date('Y年m月d日', $v['create_time']);
                    $price = json_decode($v['price'], true);
                    $result[$k]['original_price'] = reset($price)['original_price'];
                    $result[$k]['now_price'] = reset($price)['now_price'];
                }
                break;
            case 4:
                $result = M('organization')->order('create_time desc')->where(array('org_name' => array('like', "%{$_GET['word']}%")))->limit(0, 10)->select();
                foreach ($result as $k => $v) {
                    $result[$k]['search_type'] = 4;
                    $result[$k]['time'] = date('Y年m月d日', $v['create_time']);
                }
                break;
            default:
                $result = false;
        }
        $this->assign('result', $result)->assign('type', $_GET['type']);
        $this->display();
    }

    public function clearHistory()
    {
        D('SearchRecord')->deleteByUserId($this->user['id']);
        $this->ajaxReturn(array('status' => 1, 'msg' => '清除成功'));
    }

    public function loadingSearch()
    {
        $npage = (int)I('npage');
        switch ($_POST['type']) {
            case 1;
                $result = M('article')->order('create_time desc')->where(array("title" => array('like', "%{$_POST['word']}%"), 'status' => 2))->limit($npage, 6)->select();
                foreach ($result as $k => $v) {
                    $result[$k]['search_type'] = 1;
                    $result[$k]['time'] = date('Y年m月d日', $v['create_time']);
                }
                break;
            case 2;
                $result = M('product')->order('start_time desc')->where(array('title' => array('like', "%{$_POST['word']}%"), 'type' => 1, 'status' => 1, 'check_status' => 2))->limit($npage, 6)->select();
                foreach ($result as $k => $v) {
                    $result[$k]['search_type'] = 2;
                    $result[$k]['time'] = date('Y年m月d日', $v['start_time']);
                }
                break;
            case 3;
                $result = M('product')->order('start_time desc')->where(array('title' => array('like', "%{$_POST['word']}%"), 'type' => 2, 'status' => 1, 'check_status' => 2))->limit($npage, 6)->select();
                foreach ($result as $k => $v) {
                    $result[$k]['search_type'] = 3;
                    $result[$k]['time'] = date('Y年m月d日', $v['start_time']);
                }
                break;
            case 4;
                $result = M('organization')->order('create_time desc')->where(array('org_name' => array('like', "%{$_POST['word']}%")))->limit($npage, 6)->select();
                foreach ($result as $k => $v) {
                    $result[$k]['search_type'] = 4;
                    $result[$k]['time'] = date('Y年m月d日', $v['create_time']);
                }
                break;
        }
        if (!isset($result) || empty($result)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '没有数据'));
        }
        $this->ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $result));
    }
}