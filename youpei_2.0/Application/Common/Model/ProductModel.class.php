<?php
/**
 * Created by PhpStorm.
 * User: 新港西余文乐
 * Date: 2017/2/13
 * Time: 17:02
 */

namespace Common\Model;

use Think\Model;

class ProductModel extends Model
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('product');
    }

    //上架产品列表
    public function getUpList($token, $limit = 0)
    {
        if ($limit == 0) {
            $list = $this->_db->where(array('status' => 1, 'token' => $token))->field("desc,cost", true)->select();
        } else {
            $list = $this->_db->where(array('status' => 1, 'token' => $token))->field('desc,cost', true)->limit($limit)->select();
        }
        return $list;
    }

    //下架产品列表
    public function getDownList($token)
    {
        $list = $this->_db->where(array('status' => 2, 'token' => $token))->field("desc,cost", true)->select();
        return $list;
    }
    
    public function getRushList($limit = 'all', $field = '*')
    {
        if ($limit == 'all') {
            $list = $this->_db->field($field)->where(array('status' => 1, 'type' => 1,
                'check_status' => 2, 'start_time' => array('ELT', time()), 'end_time' => array('GT', time()),
            ))->select();
        } else {
            $list = $this->_db->field($field)->where(array('status' => 1, 'type' => 1,
                'check_status' => 2, 'start_time' => array('ELT', time()), 'end_time' => array('GT', time()),
            ))->limit($limit)->select();
        }
        return $list;
    }


    //精选列表
    public function getHotList($token, $limit = 'all')
    {
        if ($limit == 'all') {
            $list = $this->_db->where(array('token' => $token, 'is_hot' => 2, 'status' => 1))->select();
        } else {
            $list = $this->_db->where(array('token' => $token, 'is_hot' => 2, 'status' => 1))->limit($limit)->select();
        }
        return $list;
    }

    public function find($id)
    {
        $article = $this->_db->where("id={$id}")->find();
        return $article;
    }

    public function insert($data)
    {
        if (!$data || !is_array($data)) {
            throw_exception('添加数据不合法');
        }
        return $this->_db->add($data);
    }

    public function updateById($id, $data)
    {
        if (!isset($id) || !is_numeric($id)) {
            throw_exception('ID不合法');
        }
        if (!isset($data) || !is_array($data)) {
            throw_exception('更新数据不合法');
        }
        return $this->_db->where('id=' . $id)->save($data);
    }

    public function delete($id)
    {
        $id = $this->_db->where("id={$id}")->delete();
        return $id;
    }

    //下架产品
    public function soldOutById($id)
    {
        $id = $this->_db->where("id={$id}")->save(array('status' => 2));
        return $id;
    }

    //上架商品
    public function putAwayById($id)
    {
        $id = $this->_db->where("id={$id}")->save(array('status' => 1));
        return $id;
    }

    public function getTitleById($id)
    {
        $title = $this->_db->where("id={$id}")->getField('title');
        return $title;
    }

    public function getPicById($id)
    {
        $pic = $this->_db->where("id={$id}")->getField('pic_url');
        return $pic;
    }

    public function getList($where = [], $field = '*')
    {
        $list = $this->_db->where($where)->field($field)->order('id desc')->select();
        return $list;
    }

    public function getProductList($npage, $class_id)
    {
        $where['class_id'] = array('eq', $class_id, 'type' => 1, 'is_hot' => 1);
        $data = M('product')->where($where)->order('create_time desc')->limit($npage, 3)->select();
        return $data;
    }

    public function getListByPage($npage, $type)
    {
        $data = $this->_db->order('create_time desc')->where(array('type' => $type, 'status' => 1))->limit($npage, 6)->select();
        return $data;
    }
    
    public function getBoutiqueList($where = array(),$page)
    {
        if(!$page){
            $list = $this->_db->order('create_time desc')->where($where)->field('id,title,f_title,price,pic_url,rush_price,tag')->limit(0,10)->select();
        }else{
            $list = $this->_db->order('create_time desc')->where($where)->field('id,title,f_title,price,pic_url,rush_price,tag')->limit($page,6)->select();
        }
        return $list;
    }


    public function getSaleList($type, $limit = 0, $field = '*')
    {
        if ($limit == 0) {
            $list = $this->_db->field($field)->order('create_time desc')->where(array('type' => $type, 'status' => 1, 'check_status' => 2))->select();
        } else {
            $list = $this->_db->field($field)->order('create_time desc')->where(array('type' => $type, 'status' => 1, 'check_status' => 2))->limit($limit)->select();
        }
        return $list;
    }

    public function getCollectFind($proId, $type)
    {
        $first = $this->_db->field('id,title,pic_url')->where(array('id' => $proId, 'type' => $type))->find();
        return $first ? $first : false;
    }

    //判断有否抢购价
    public function isRushingById($proId)
    {
        $price = M('bargain')->where(['type' => 2, 'type_id' => $proId, 'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
        if ($price) {
            //todo
        }
    }

    public function getClassByToken($token, $page = 0, $field = '*')
    {
        $resultList = $this->_db->field($field)->where(array('token' => $token, 'status' => 1, 'type' => 1))
            ->order('create_time desc')->limit($page, 3)->select();
        return $resultList;
    }

    public function getListForAdmin($where, $page, $pageSize = 10)
    {
        $offset = ($page - 1) * $pageSize;
        $list = $this->_db->where($where)->field("desc,cost", true)->limit($offset, $pageSize)->order('create_time desc')->select();
        return $list;
    }

    public function getCount($where = array())
    {
        return $this->_db->where($where)->count();
    }

    //获取第一个规格的价钱
    public function getFirstKeyPrice($proId)
    {
        //判断有否抢购价
        $productPrice = M('product')->where(['id' => $proId])->getField('price');
        $price = json_decode($productPrice, true);
        $key = key($price);
        $rushPrice = M('bargain')->where(['type' => 2, 'key' => $key, 'type_id' => $proId, 'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
        if ($rushPrice) {
            $data = [
                'original_price' => reset($price)['now_price'],
                'now_price' => $rushPrice
            ];
        } else {
            $data = [
                'original_price' => reset($price)['original_price'],
                'now_price' => reset($price)['now_price']
            ];
        }
        return $data;
    }
}