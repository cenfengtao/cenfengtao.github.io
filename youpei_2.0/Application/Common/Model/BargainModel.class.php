<?php
/**
 * 砍价
 * Date: 2017/9/05
 */

namespace Common\Model;

use Think\Model;

class BargainModel extends Model
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('bargain');
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

    //当前产品砍价活动是否在活动期间
    public function isBargain($proId, $key)
    {
        $result = $this->_db->where(array('type' => 1, 'type_id' => $proId, 'key' => $key,
            'start_time' => array('LT', time()), 'end_time' => array('GT', time())))->find();
        return $result;
    }
}