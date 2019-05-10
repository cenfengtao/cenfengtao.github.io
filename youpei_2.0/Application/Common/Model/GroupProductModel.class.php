<?php
/**
 * Created by PhpStorm.
 * User: 新港西余文乐
 * Date: 2017/2/13
 * Time: 17:02
 */

namespace Common\Model;

use Think\Model;

class GroupProductModel extends Model
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('group_product');
    }

    public function getList($token, $limit = 'all')
    {
        if ($limit == 'all') {
            $list = $this->_db->where(array('token' => $token, 'status' => 1))->order('create_time desc')->select();
        } else {
            $list = $this->_db->where(array('token' => $token, 'status' => 1))->order('create_time desc')->limit($limit)->select();
        }
        return $list;
    }

    public function find($id)
    {
        $result = $this->_db->where("id={$id}")->find();
        return $result;
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

    public function getGroupsByToken($token, $field = '*')
    {
        $groups = $this->_db->where(array('token' => $token, 'status' => 1,'check_status'=>2))->field($field)->select();
        return $groups;
    }
}