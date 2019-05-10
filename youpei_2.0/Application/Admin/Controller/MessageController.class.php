<?php
/**
 *机构消息模板管理
 */
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;
use Think\Page;

class MessageController extends CommonController
{
    public function index()
    {
        $list = D('Message')->getList(array('token' => $this->token));
        foreach ($list as $k => $v) {
            $status = D('MessageRecord')->createTime(1, $v['id'], $this->token);
            $list[$k]['status'] = $status[0]['status'];
            $list[$k]['create_time'] = $status[0]['create_time'];
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function addMessage()
    {
        if ($_POST) {
            if (!$_POST['title'] || empty($_POST['title'])) {
                return show(0, '标题不能为空');
            }
            if (!$_POST['name'] || empty($_POST['name'])) {
                return show(0, '名称不能为空');
            }
            if (!$_POST['number'] || empty($_POST['number'])) {
                return show(0, '编号不能为空');
            }
            if (!$_POST['jump_url'] || empty($_POST['jump_url'])) {
                return show(0, '链接不能为空');
            }
            $_POST['create_time'] = time();
            $_POST['token'] = $this->token;
            if ($_POST['id'] && !empty($_POST['id'])) {
                return $this->save($_POST);
            }
            try {
                $id = D('Message')->insert($_POST);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }

        } else {
            $this->display();
        }
    }

    public function editMessage()
    {
        if (!$_GET['id'] || empty($_GET['id'])) {
            return show(0, 'ID参数错误');
        }
        try {
            $list = D('Message')->find($_GET['id']);
            $this->assign('list', $list);
            $this->display();
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function save($data)
    {
        try {
            $id = D('Message')->updateById($data['id'], $data);
            if ($id === false) {
                return show(0, '修改失败');
            }
            return show(1, '修改成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function sendMessage()
    {
        if ($_POST) {
            if (!$_POST['id'] || empty($_POST['id'])) {
                $this->ajaxReturn(array('status' => 0, 'message' => 'ID参数错误'));
            }
            $data = D('Message')->find($_POST['id']);
            //只发送当天登录的用户
            $start_time = strtotime(date('Y-m-d 0:0:0', time()));
            $users = M('User')->where(array('last_login_time' => array(array('GT', $start_time), array('LT', time()), 'AND')))->field('open_id,id')->select();
            if ($data) {
                $token = $data['token'];
                $first = $data['title'];//标题
                $keyword1 = $data['name'];//通知名称
                $keyword2 = $data['number'];//通知编号
                $keyword3 = $data['desc'];//通知摘要
                $remark = '详情点击查看';
                $url = $data['jump_url'];
                $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__REMARK__');
                $loseCount = 0;
                //每隔5个延迟一秒发送
                $sendCount = 0;
                foreach ($users as $k => $v) {
                    $sendCount += 1;
                    if ($sendCount % 5 == 0) {
                        sleep(1);
                    }
                    //当天是否发送过
                    $userId[$k] = M('message_record')->where(array('create_time' => array(array('GT', $start_time), array('LT', time()), 'AND'),
                        'user_id' => $v['id']))->getField('id');
                    //1-未发送 2-已发送
                    if ($userId[$k]) {
                        $isSend[$k] = 2;
                    } else {
                        $isSend[$k] = 1;
                    }
                    if ($isSend[$k] == 1) {
                        $openId = $users[$k]['open_id'];
                        $infoFormat = array($openId, $url, $first, $keyword1, $keyword2, $keyword3, $remark);
                        $wxuser = get_wxuser($token);
                        $re = execute_public_template('INFORM', $templeFormat, $infoFormat, $wxuser);
                        if ($re['errmsg'] == 'ok') {
                            $arr = [
                                'token' => $token,
                                'create_time' => time(),
                                'type' => 1,
                                'type_id' => $data['id'],
                                'status' => 2,
                                'user_id' => $users[$k]['id'],
                                'errmsg' => $re['errmsg']
                            ];
                            D('MessageRecord')->insert($arr);
                        } else {
                            $arr = [
                                'token' => $token,
                                'create_time' => time(),
                                'type' => 1,
                                'type_id' => $data['id'],
                                'status' => 1,
                                'user_id' => $users[$k]['id'],
                                'errmsg' => $re['errmsg']
                            ];
                            D('MessageRecord')->insert($arr);
                            $loseCount += 1;
                        }
                    }
                }
                if ($loseCount == 0) {
                    $this->ajaxReturn(array('status' => 1, 'message' => '发送消息成功'));
                } else {
                    $this->ajaxReturn(array('status' => 0, 'message' => $loseCount . '条信息发送失败'));
                }
            } else {
                $this->ajaxReturn(['status' => 0, 'message' => '找不到模板消息']);
            }
        }
    }

    public function delete()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, 'ID参数错误');
        }
        try {
            $id = D('Message')->delete($_POST['id']);
            if ($id) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

}