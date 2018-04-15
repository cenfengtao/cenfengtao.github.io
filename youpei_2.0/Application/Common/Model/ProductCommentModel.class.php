<?php
namespace Common\Model;

class ProductCommentModel extends BaseModel
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('product_comment');
    }

    public function getList($token)
    {
        $list = $this->_db->where("token='{$token}'")->order('id desc')->select();
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

    public function getListByProId($productId)
    {
        $list = $this->_db->where("product_id={$productId}")->select();
        return $list;
    }

    public function getListByUserId($userId, $orderId)
    {
        $list = $this->_db->where(array("user_id" => $userId, "order_id" => $orderId))->select();
        return $list;
    }

    public function getCommentByFatherId($fatherId, $userId, $productId)
    {
        if (!isset($userId) || empty($userId)) {
            $list = $this->_db->where(array("father_id" => $fatherId, "product_id" => $productId))->order('create_time desc')->select();
        } else {
            $list = $this->_db->where(array("father_id" => $fatherId, "user_id" => $userId, "product_id" => $productId))->order('create_time desc')->select();
        }
        return $list;
    }

    public function getCommentByFatherParId($fatherId, $userId, $productId)
    {
        if (!isset($userId) || empty($userId)) {
            $list = $this->_db->where(array("father_id" => $fatherId, "parenting_id" => $productId))->select();
        } else {
            $list = $this->_db->where(array("father_id" => $fatherId, "user_id" => $userId, "parenting_id" => $productId))->select();
        }
        return $list;
    }

    public function getCommentByFatherActivityId($fatherId, $userId, $activityId)
    {
        if (!isset($userId) || empty($userId)) {
            $list = $this->_db->where(array("father_id" => $fatherId, "activity_id" => $activityId))->select();
        } else {
            $list = $this->_db->where(array("father_id" => $fatherId, "user_id" => $userId, "activity_id" => $activityId))->select();
        }
        return $list;
    }

    public function getCommentByFatherGroupId($fatherId, $userId, $groupId)
    {
        if (!isset($userId) || empty($userId)) {
            $list = $this->_db->where(array("father_id" => $fatherId, "group_id" => $groupId))->select();
        } else {
            $list = $this->_db->where(array("father_id" => $fatherId, "user_id" => $userId, "group_id" => $groupId))->select();
        }
        return $list;
    }

    public function isCommentByOrderId($userId, $orderId)
    {
        $result = $this->_db->where(array('user_id' => $userId, 'order_id' => $orderId))->find();
        return $result ?: false;
    }

    public function getCountByProId($proId)
    {
        $count = $this->_db->where("product_id={$proId}")->count();
        return $count;
    }

    public function getListForPro($where, $page, $pageSize = 10, $field = '*')
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