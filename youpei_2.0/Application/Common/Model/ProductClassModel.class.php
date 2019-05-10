<?php
namespace Common\Model;

use Think\Model;

class ProductClassModel extends Model
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('ProductClass');
    }

    public function getList($where = array())
    {
        $list = $this->_db->where($where)->select();
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

    public function getProductClass()
    {
        $list = $this->_db->order('sort asc')->select();
        return $list;
    }

    public function getTitleById($classId)
    {
        $title = $this->_db->where("id={$classId}")->getField('title');
        return $title;
    }
}