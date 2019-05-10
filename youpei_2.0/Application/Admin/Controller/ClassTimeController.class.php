<?php
/**
 *课程表管理
 */
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;
use Think\Page;
use Org\Util\ExcelToArray;

class ClassTimeController extends CommonController
{
    public function timeList()
    {
        $correctList = M('class_time')->where(['token' => $this->token, 'status' => 1])->distinct(true)->field('user_id')->select();
        $classList = [];
        foreach ($correctList as $v) {
            $mobile = M('user')->where(['id' => $v['user_id']])->getField('mobile');
            $count = M('class_time')->where(['token' => $this->token, 'status' => 1, 'user_id' => $v['user_id'], 'mobile' => $mobile])->count();
            $name = M('class_time')->where(['token' => $this->token, 'status' => 1, 'user_id' => $v['user_id'], 'mobile' => $mobile])->find();
            if ($count > 0) {
                $classList[] = [
                    'username' => $name['username'],
                    'mobile' => $mobile,
                    'count' => $count . '条',
                    'user_id' => $v['user_id']
                ];
            }

        }
        //录入错误列表
        $faultCount = M('class_time')->where(['token' => $this->token, 'status' => 2])->count();
        $fault = [];
        if ($faultCount > 0) {
            $fault = ['username' => '匹配不到对应手机号的用户', 'count' => $faultCount . '条', 'user_id' => 0];
        }
        //请假通知
        $leaveCount = M('class_time')->where(['token' => $this->token, 'leave' => ['EXP', 'is not null']])->count();
        $leave = [];
        if ($leaveCount > 0) {
            $leave = ['username' => '请假通知', 'count' => $leaveCount . '条', 'user_id' => 0];
        }
        $this->assign('classList', $classList)->assign('fault', $fault)->assign('leave', $leave);
        $this->display();
    }

    public function addTime()
    {
        //接收前台文件
        $tmp_file = $_FILES['excel_file']['tmp_name'];
        $file_types = explode(".", $_FILES ['excel_file'] ['name']);
        $file_type = $file_types[count($file_types) - 1];
        if (strtolower($file_type) != "xlsx" && strtolower($file_type) != "xls") {
            $this->error('不是Excel文件，重新上传');
        }
        /*设置上传路径*/
        $savePath = "./Upload/" . date("Ymd") . '/';
        if (!file_exists($savePath)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($savePath, 0777, true);
        }
        /*以时间来命名上传的文件*/
        $str = date('Ymdhis');
        $file_name = $str . "." . $file_type;
        /*是否上传成功*/
        if (!copy($tmp_file, $savePath . $file_name)) {
            $this->error('上传失败');
        }
        $ExcelToArray = new ExcelToArray();//实例化
        $res = $ExcelToArray->read($savePath . $file_name, $file_type);//传参,判断office2007还是office2003
        foreach ($res as $k => $v) //循环excel表
        {
            $status = 1;
            $errmsg = '';
            //判断手机号是否对应
            $userId = M('user')->where(['mobile' => $v['6']])->getField('id');
            if (empty($userId)) {
                $userId = 0;
                $status = 2;
                $errmsg .= '第' . $k . '行找不到对应手机号的用户，请检查手机号码';
            }
            //判断是否已插入
            $isInsert = M('class_time')->where(['token' => $this->token, 'class_title' => $v[5], 'user_id' => $userId,
                'start_day_time' => strtotime($v[0]), 'start_hour_time' => $v[1]])->getField('id');
            if ($isInsert) {
                continue;
            }
            $insertData = [
                'start_day_time' => strtotime($v[0]),
                'start_hour_time' => $v[1],
                'end_hour_time' => $v[2],
                'teacher' => $v[3],
                'user_id' => $userId,
                'class_title' => $v[4],
                'remark' => $v[12],
                'username' => $v[7],
                'classroom' => $v[8],
                'classroom_people' => $v[9],
                'classroom_max_people' => $v[10],
                'type' => $v[11],
                'class_hour' => $v[5],
                'token' => $this->token,
                'status' => $status,
                'errmsg' => $errmsg,
                'mobile' => $v[6],
            ];
            M('class_time')->add($insertData);
        }
        $this->success('添加成功', '/admin.php/ClassTime/timeList');
    }

    //添加单个课程表
    public function addSingleTime()
    {
        if ($_POST) {
            if (!preg_match('/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/', $_POST['mobile'])) {
                return show(0, '请输入正确的手机号码');
            }
            if (!$_POST['start_hour_time']) {
                return show(0, '上课时间不能为空');
            }
            if (!$_POST['end_hour_time']) {
                return show(0, '结束时间不能为空');
            }
            $status = 1;
            $errmsg = '';
            //判断手机号是否对应
            $userId = M('user')->where(['mobile' => $_POST['mobile']])->getField('id');
            if (empty($userId)) {
                $userId = 0;
                $status = 2;
                $errmsg .= '找不到对应手机号的用户，请检查手机号码';
            }
            $updateData = [
                'start_day_time' => strtotime($_POST['start_day_time']),
                'start_hour_time' => $_POST['start_hour_time'],
                'end_hour_time' => $_POST['end_hour_time'],
                'user_id' => $userId,
                'class_title' => $_POST['class_title'],
                'teacher' => $_POST['teacher'],
                'remark' => $_POST['remark'],
                'username' => $_POST['username'],
                'classroom' => $_POST['classroom'],
                'classroom_people' => $_POST['classroom_people'],
                'classroom_max_people' => $_POST['classroom_max_people'],
                'type' => $_POST['type'],
                'class_hour' => $_POST['class_hour'],
                'status' => $status,
                'errmsg' => $errmsg,
                'mobile' => $_POST['mobile'],
                'token' => $this->token,
            ];
            $id = D('ClassTime')->insert($updateData);
            if ($id === false) {
                return show(0, '添加失败');
            } else {
                return show(1, '添加成功');
            }
        } else {
            $this->display();
        }
    }

    //查看单个学生的课程表
    public function checkTime()
    {
        //今天凌晨时间
        $todayTime = strtotime(date("Y-m-d", time()));
        if (!$_GET['userId']) {
            $this->error('参数错误');
        }
        $user = D('User')->find($_GET['userId']);
        $timeList = M('class_time')->where(['status' => 1, 'token' => $this->token, 'user_id' => $_GET['userId'],
            'mobile' => $user['mobile']])->select();
        foreach ($timeList as $k => $v) {
            //1-已请假 2-未请假
            if ($v['leave']) {
                $timeList[$k]['leave_status'] = 1;
            } else {
                $timeList[$k]['leave_status'] = 2;
            }
        }
        $user = M('user')->where(['id' => $_GET['userId']])->field('username, mobile')->find();
        $this->assign('timeList', $timeList)->assign('user', $user);
        $this->display();
    }

    //查看错误的课程表列表
    public function checkFaultTime()
    {
        //今天凌晨时间
        $timeList = M('class_time')->where(['status' => 2, 'token' => $this->token])->select();
        $this->assign('timeList', $timeList);
        $this->display();
    }


    //查看课程请假通知
    public function checkLeave()
    {
        $timeList = M('class_time')->where(['token' => $this->token, 'leave' => ['EXP', 'is not null']])->select();
        $this->assign('timeList', $timeList);
        $this->display();
    }

    //阅读请假通知
    public function updateLeave()
    {
        if (!$_POST['id']) {
            return show(0, 'ID参数错误');
        }
        $data = ['is_read' => 2];
        $id = D('ClassTime')->updateById($_POST['id'], $data);
        if ($id) {
            return show(1, '成功阅读');
        } else {
            return show(0, '阅读失败');
        }
    }

    //修改单个课程表
    public function editTime()
    {
        if ($_POST) {
            if (!$_POST['id']) {
                return show(0, '参数错误');
            }
            if (!preg_match('/^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/', $_POST['mobile'])) {
                return show(0, '请输入正确的手机号码');
            }
            if (!$_POST['start_hour_time']) {
                return show(0, '上课时间不能为空');
            }
            if (!$_POST['end_hour_time']) {
                return show(0, '结束时间不能为空');
            }
            $status = 1;
            $errmsg = '';
            //判断手机号是否对应
            $userId = M('user')->where(['mobile' => $_POST['mobile']])->getField('id');
            if (empty($userId)) {
                $userId = 0;
                $status = 2;
                $errmsg .= '找不到对应手机号的用户，请检查手机号码';
            }
            $updateData = [
                'start_day_time' => strtotime($_POST['start_day_time']),
                'start_hour_time' => $_POST['start_hour_time'],
                'end_hour_time' => $_POST['end_hour_time'],
                'user_id' => $userId,
                'class_title' => $_POST['class_title'],
                'teacher' => $_POST['teacher'],
                'remark' => $_POST['remark'],
                'username' => $_POST['username'],
                'classroom' => $_POST['classroom'],
                'classroom_people' => $_POST['classroom_people'],
                'classroom_max_people' => $_POST['classroom_max_people'],
                'type' => $_POST['type'],
                'class_hour' => $_POST['class_hour'],
                'status' => $status,
                'errmsg' => $errmsg,
                'mobile' => $_POST['mobile']
            ];
            $id = D('ClassTime')->updateById($_POST['id'], $updateData);
            if ($id === false) {
                return show(0, '修改失败');
            } else {
                return show(1, '修改成功');
            }
        } else {
            if (!$_GET['id']) {
                $this->error('参数错误');
            }
            $classTime = D('ClassTime')->find($_GET['id']);
            $this->assign('classTime', $classTime);
            $this->display();
        }
    }

    public function deleteTime()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $result = D('ClassTime')->delete($_POST['id']);
            if ($result) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function deleteTimeByUser()
    {
        if (!$_POST['user_id']) {
            return show(0, '参数错误');
        }
        try {
            $result = M('class_time')->where(['user_id' => $_POST['user_id'], 'token' => $this->token])->delete();
            if ($result === false) {
                return show(0, '删除失败');
            } else {
                return show(1, '删除成功');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }
}