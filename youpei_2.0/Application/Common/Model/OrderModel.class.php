<?php
/**
 * Created by PhpStorm.
 * User: 新港西余文乐
 * Date: 2017/2/13
 * Time: 17:02
 */

namespace Common\Model;

use Think\Model;

class OrderModel extends Model
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('Order');
    }

    public function getList()
    {
        $list = $this->_db->order('create_time desc')->select();
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

    /**
     * $type 0未付款 1已付款 2已取消 3支付失败 4交易完成 5用户删除
     */
    public function getListByUserId($userId, $type = 'all')
    {
        if ($type == 'all') {
            $list = $this->_db->where(array('user_id' => $userId, array('status' => array('NEQ', '5'))))->order('create_time desc')->select();
        } else {
            $list = $this->_db->where(array('user_id' => $userId, 'status' => $type))->order('create_time desc')->select();
        }
        return $list;
    }

    public function getPayCount($userId, $status)
    {
        $count = $this->_db->where(array('user_id' => $userId, 'status' => $status))->count();
        return $count;
    }

    public function getListById($id)
    {
        $list = $this->_db->where("id={$id}")->select();
        return $list;
    }


    public function getGroupById($productId)
    {
        $data['status'] = array(array('eq', 1), array('eq', 4), 'OR');
        $data['product_id'] = $productId;
        $list = $this->_db->distinct(true)->field(array('user_id'))->where($data)->select();
        return $list;
    }

    public function getGroupByParId($productId)
    {
        $data['status'] = array(array('eq', 1), array('eq', 4), 'OR');
        $data['product_id'] = $productId;
        $list = $this->_db->distinct(true)->field(array('user_id'))->where($data)->select();
        return $list;
    }

    public function getGroupByActivityId($activityId)
    {
        $data['status'] = array(array('eq', 1), array('eq', 4), 'OR');
        $data['activity_id'] = $activityId;
        $list = $this->_db->distinct(true)->field(array('user_id'))->where($data)->select();
        return $list;
    }

    public function getGroupByGroupRecordId($groupId)
    {
        $data['status'] = array(array('eq', 1), array('eq', 4), 'OR');
        $data['group_id'] = $groupId;
        $list = $this->_db->distinct(true)->field(array('user_id'))->where($data)->select();
        return $list;
    }

    public function getListForOrder($where, $page, $pageSize = 10, $field = '*')
    {
        $offset = ($page - 1) * $pageSize;
        $list = $this->_db->where($where)->field($field)->limit($offset, $pageSize)->order('id desc')->select();
        return $list;
    }

    public function getCount($where = array())
    {
        return $this->_db->where($where)->order('id desc')->count();
    }

}