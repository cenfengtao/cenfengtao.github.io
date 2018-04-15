<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

class BoutiqueController extends BaseController
{
    public function index()
    {
        $this->title = "精品商品";
        try {
            $boutiqueList = M('Product')->where(array('type' => 2, 'status' => 1, 'check_status' => 2))->limit(0, 10)->select();
            foreach ($boutiqueList as $key => $val) {
                $prices = json_decode($val['price'], true);
                $boutiqueList[$key]['price'] = $prices[key($prices)];
            }
            $this->assign('boutiqueList', $boutiqueList);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function ajaxIndex()
    {
        try {
            $class = M('product_class')->order('sort desc')->where(array('type' => 2))->select();
            if ($_GET['class_id']) {
                $list = D('Product')->getBoutiqueList(array('type' => 2, 'status' => 1, 'check_status' => 2, 'class_id' => $_GET['class_id']));
                foreach ($list as $key => $val) {
                    $prices = json_decode($val['price'], true);
                    $list[$key]['price'] = $prices[key($prices)];
                }
            } else {
                $list = D('Product')->getBoutiqueList(array('type' => 2, 'status' => 1, 'check_status' => 2));
                foreach ($list as $key => $val) {
                    $prices = json_decode($val['price'], true);
                    $list[$key]['price'] = $prices[key($prices)];
                }
            }
            return show(1, '', ['list' => $list, 'class' => $class]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function loadingProduct()
    {
        $npage = (int)I("npage");
        if (I("class_id")) {
            $list = D('Product')->getBoutiqueList(array('type' => 2, 'status' => 1, 'check_status' => 2, 'class_id' => I("class_id")), $npage);
        } else {
            $list = D('Product')->getBoutiqueList(array('type' => 2, 'status' => 1, 'check_status' => 2), $npage);
        }
        if (!$list || empty($list)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '没有数据'));
        }
        foreach ($list as $key => $val) {
            $prices = json_decode($val['price'], true);
            $list[$key]['price'] = $prices[key($prices)];
        }
        $this->ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $list));
    }

    public function demo()
    {
        $this->display();
    }
}