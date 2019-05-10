<?php
/**
 * 平台消息表
 */

namespace Common\Model;

use Think\Model;

class TerraceModel extends Model
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('terrace');
    }

    public function getList($where = array())
    {
        $list = $this->_db->where($where)->select();
        return $list;
    }

    public function delete($id)
    {
        $id = $this->_db->where("id={$id}")->delete();
        return $id;
    }

    public function insert($data)
    {
        if (!$data || !is_array($data)) {
            throw_exception('添加数据不合法');
        }
        return $this->_db->add($data);
    }

    public function find($id)
    {
        $result = $this->_db->where("id={$id}")->find();
        return $result;
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
    
}