<?php
namespace Common\Model;

class DrawRecordModel extends BaseModel
{
    public function getDrawrecord($userid)
    {
        $sql = "select a.*,b.explain from __PREFIX__draw_record a
        left join __PREFIX__lottery_prize b on a.prizeid = b.id
        where a.userid=" . $userid . " order by create_time desc";
        $rs = $this->pageQuery($sql);
        return $rs;
    }

    public function getallDrawrecord()
    {
        $sql = "select * from __PREFIX__draw_record where userid is not null order by id desc limit 20";
        return $this->query($sql);
    }

    public function prizesIssued($id)
    {
        $rd = array('status' => -1);
        $data["status"] = 1;
        $data['sending_time'] = NOW_TIME;
        $rs = $this->where("id=" . $id)->save($data);
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    public function cancelSend($id)
    {
        $rd = array('status' => -1);
        $data["status"] = 0;
        $data['sending_time'] = 0;
        $rs = $this->where("id=" . $id)->save($data);
        if (false !== $rs) {
            $rd['status'] = 1;
        }
        return $rd;
    }

    public function updateById($id, $data)
    {
        if (!isset($id) || !is_numeric($id)) {
            throw_exception('ID不合法');
        }
        if (!isset($data) || !is_array($data)) {
            throw_exception('更新数据不合法');
        }
        return M('draw_record')->where('id=' . $id)->save($data);
    }
}