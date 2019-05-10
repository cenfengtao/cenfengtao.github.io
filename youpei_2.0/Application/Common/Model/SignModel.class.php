<?php
namespace Common\Model;
use Think\Model;
class SignModel extends Model
{
    private $_db = '';

    public  function __construct()
    {
        $this->_db = M('sign');
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

    public function getListUserId($userId)
    {
        $list = $this->_db->order('create_time desc')->limit(0,1)->where(array('user_id'=>$userId))->select();
        return $list;
    }

    public function getStatusUserId($userId,$start)
    {
        $list = $this->_db->where(array('user_id'=>$userId,'create_time'=>array('GT',$start)))->find();
        return $list ?1:0;
    }

    public function addinsertSign($userId,$count)
    {
        $data = array();
        $data['user_id'] = $userId;
        $data['count'] = $count;
        $data['create_time'] = time();
        $rs = $this->_db->add($data);
        return $rs;
    }
}