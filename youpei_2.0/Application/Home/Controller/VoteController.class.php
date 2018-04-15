<?php
/**
 * 投票
 */
namespace Home\Controller;

use Think\Controller;
use Think\Exception;
use Think\Upload;

class VoteController extends BaseController
{
    //榜单列表
    public function userBillboardList()
    {
        $this->display();
    }

    //投票活动列表
    public function voteActivityList()
    {
        $this->display();
    }

    public function ajaxVoteActivityList()
    {
        $list = M('vote')->where(array('check_status' => 2))->order('work_start_time desc')
            ->field('id,work_start_time,vote_end_time,status,title,image,tag')->select();
        $over = [];//已结束活动
        $proceed = [];//进行中活动
        foreach ($list as $k => $v) {
            //status 1-投稿中 2-截止投稿 3-投票中 4-截止投票 5-发布结果 6-结束
            if ($v['status'] == 4 || $v['status'] == 5 || $v['status'] == 6 || $v['vote_end_time'] < time()) {
                $over[$k] = $v;
                $over[$k]['work_start_time'] = date('Y/m/d', $v['work_start_time']);
                $over[$k]['vote_end_time'] = date('Y/m/d', $v['vote_end_time']);
                //票数
                $over[$k]['userVoteCount'] = M('contribution_record')->where(array('vote_id' => $v['id'], 'status' => 2))->sum('vote_count');
                if (is_null($over[$k]['userVoteCount'])) {
                    $over[$k]['userVoteCount'] = 0;
                }
                //作品数
                $userWorks[$k] = M('contribution_record')->where(array('vote_id' => $v['id'], 'status' => 2))->field('id')->select();
                $over[$k]['userWorksCount'] = count($userWorks[$k]);
                $tag = explode(' ', $v['tag']);
                $over[$k]['tagA'] = $tag[0] ?: '';
                $over[$k]['tagB'] = $tag[1] ?: '';
                $over[$k]['tagC'] = $tag[2] ?: '';
            } else {
                $proceed[$k] = $v;
                $proceed[$k]['work_start_time'] = date('Y/m/d', $v['work_start_time']);
                $proceed[$k]['vote_end_time'] = date('Y/m/d', $v['vote_end_time']);
                //票数
                $proceed[$k]['userVoteCount'] = M('contribution_record')->where(array('vote_id' => $v['id'], 'status' => 2))->sum('vote_count');
                if (is_null($proceed[$k]['userVoteCount'])) {
                    $proceed[$k]['userVoteCount'] = 0;
                }
                //作品数
                $userWorks[$k] = M('contribution_record')->where(array('vote_id' => $v['id'], 'status' => 2))->field('id')->select();
                $proceed[$k]['userWorksCount'] = count($userWorks[$k]);
                $tag = explode(' ', $v['tag']);
                $proceed[$k]['tagA'] = $tag[0] ?: '';
                $proceed[$k]['tagB'] = $tag[1] ?: '';
                $proceed[$k]['tagC'] = $tag[2] ?: '';
            }
        }
        $overCount = count($over);
        $proceedCount = count($proceed);
        //重新排序数组
        $over = array_merge($over);
        $proceed = array_merge($proceed);
        //访问人数
        $voteVisitSum = M('vote')->where('check_status=2')->sum('visit_sum');
        return show(1, '', ['over' => $over, 'overCount' => $overCount, 'proceed' => $proceed,
            'proceedCount' => $proceedCount, 'voteVisitSum' => $voteVisitSum]);
    }

    //作品列表
    public function voteList()
    {
        $this->display();
    }

    public function ajaxVoteList()
    {
        if (!$_GET['vote_id']) {
            return show(0, 'ID不能为空');
        }
        $vote = D('Vote')->find($_GET['vote_id']);
        if (!$vote) {
            return show(0, '没有此活动，请返回活动页面刷新下吧');
        }
        if ($vote['check_status'] == 1) {
            return show(0, '活动正在审核中，请耐心等待');
        }
        //status 1-投稿中 2-截止投稿 3-投票中 4-截止投票 5-发布结果 6-结束
        if ($vote['work_start_time'] > time()) {
            //$voteStatus 1-活动结束 2-活动中 3-活动未开始
            $voteStatus = 3;
        } elseif ($vote['status'] == 4 || $vote['status'] == 5 || $vote['status'] == 6 || $vote['vote_end_time'] < time()) {
            $voteStatus = 1;
        } else {
            $voteStatus = 2;
        }
        //1-截止投稿 2-投稿中
        if ($vote['work_start_time'] <= time() && $vote['work_end_time'] >= time()) {
            $isContribute = 2;
        } else {
            $isContribute = 1;
        }
        //主办方
        $sponsor = M('organization')->where('id=' . $vote['sponsor'])->field('id,picture')->find();
        //协办方
        if ($vote['organizer'] > 0) {
            $organizer = M('organization')->where('id=' . $vote['organizer'])->field('id,picture')->find();
        } else {
            $organizer = [];
        }
        //每次增加一次浏览记录
        M('vote')->where('id=' . $_GET['vote_id'])->setInc('visit_sum');
        //是否有获得每日票数
        $isGet = D('VoteRecord')->everydayCount($this->user['id']);
        if ($isGet == 1) {
            $countData = [
                'create_time' => time(),
                'user_id' => $this->user['id'],
                'type' => 1,
                'type_id' => $_GET['share_user_id'],
                'count' => 2,
                'is_expend' => 2
            ];
            D('VoteRecord')->insert($countData);
        }
        //status 1-审核中 2-已入围 3-落选
        $joinUser = M('contribution_record')->where(array('vote_id' => $_GET['vote_id'], 'status' => 2))
            ->order('vote_count desc')->field('id,type,path,username,title,vote_count,number')->limit(6)->select();
        if ($joinUser) {
            if ($vote['upload_type'] == 1) {
                foreach ($joinUser as $k => $v) {
                    $image[$k] = json_decode($v['path'], true);
                    $joinUser[$k]['path'] = $image[$k][0];
                }
            } else {
                foreach ($joinUser as $k => $v) {
                    $joinUser[$k]['path'] = substr($v['path'], 0, strlen($v['path']) - 3) . 'jpg';
                }
            }
        }
        //投票总数
        $voteSum = M('contribution_record')->where(array('vote_id' => $_GET['vote_id'], 'status' => 2))->sum('vote_count');
        if (!$voteSum) {
            $voteSum = 0;
        }
        //参加人数
        $joinSum = M('contribution_record')->where(array('vote_id' => $_GET['vote_id'], 'status' => 2))
            ->order('vote_count desc')->field('id,type,path,username,title,vote_count')->count();
        //浏览数
        $visitSum = $vote['visit_sum'];
        //是否已参加活动 1-未参加 2-已参加
        $isJoin = M('contribution_record')->where(['user_id' => $this->user['id'], 'vote_id' => $_GET['vote_id']])->getField('id');
        if ($isJoin) {
            $join = 2;
        } else {
            $join = 1;
        }
        //是否第一次浏览此活动
        $readId = M('read_record')->where(array('user_id' => $this->user['id'], 'type' => 3, 'type_id' => $_GET['vote_id']))->getField('id');
        // 1-已浏览 2-未浏览
        if ($readId) {
            $isRead = 1;
        } else {
            $isRead = 2;
        }
        $data = [
            'joinUser' => $joinUser,
            'joinSum' => $joinSum,
            'voteSum' => $voteSum,
            'visitSum' => $visitSum,
            'voteStatus' => $voteStatus,
            'upload_type' => $vote['upload_type'],
            'isContribute' => $isContribute,
            'background' => $vote['image'],
            'start_time' => date('Y-m-d', $vote['work_start_time']),
            'desc' => $vote['description'],
            'title' => $vote['title'],
            'join' => $join,
            'isRead' => $isRead,
            'sponsor' => $sponsor,
            'organizer' => $organizer,
        ];
        return show(1, '', $data);
    }


    public function loadVoteList()
    {
        if (!$_GET['page']) {
            return show(0, '参数错误');
        }
        if (!$_GET['vote_id']) {
            return show(0, 'ID不能为空');
        }
        $vote = D('Vote')->find($_GET['vote_id']);
        $joinUser = M('contribution_record')->where(array('vote_id' => $_GET['vote_id'], 'status' => 2))
            ->order('vote_count desc')->field('id,type,path,username,title,vote_count,number')->limit($_GET['page'], 6)->select();
        if ($joinUser) {
            if ($vote['upload_type'] == 1) {
                foreach ($joinUser as $k => $v) {
                    $image[$k] = json_decode($v['path'], true);
                    $joinUser[$k]['path'] = $image[$k][0];
                }
            } else {
                foreach ($joinUser as $k => $v) {
                    $joinUser[$k]['path'] = substr($v['path'], 0, strlen($v['path']) - 4) . '.jpg';
                }
            }
        }
        return show(1, '', ['joinUser' => $joinUser]);
    }


    public function read()
    {
        if (!$_GET['vote_id']) {
            return show(0, '活动ID参数错误');
        }
        $readId = M('read_record')->where(array('user_id' => $this->user['id'], 'type' => 3, 'type_id' => $_GET['vote_id']))->getField('id');
        if (!$readId) {
            $data = [
                'create_time' => time(),
                'user_id' => $this->user['id'],
                'type' => 3,
                'type_id' => $_GET['vote_id']
            ];
            $id = D('ReadRecord')->insert($data);
            if ($id) {
                return show(1, '已阅读');
            } else {
                return show(1, '阅读失败');
            }
        } else {
            return show(1, '已阅读');
        }
    }


    public function voteDetail()
    {
        $this->display();
    }

    public function ajaxVoteDetail()
    {
        try {
            if (!$_GET['id']) {
                return show(0, 'ID不能为空');
            }
            $work = M('contribution_record')->where(['id' => $_GET['id'], 'status' => 2])->field('id,path,username,title,vote_id,vote_count,user_id,number')->find();
            if (!$work) {
                return show(0, '没有此作品，请返回活动页面刷新下吧');
            }
            $vote = M('Vote')->where(['id' => $work['vote_id']])->find();
            if ($vote['check_status'] == 1) {
                return show(0, '活动正在审核中，请耐心等候通过吧');
            }
            //1-截止投稿 2-投稿中
            if ($vote['work_start_time'] <= time() && $vote['work_end_time'] >= time()) {
                $isContribute = 2;
            } else {
                $isContribute = 1;
            }
            //status 1-投稿中 2-截止投稿 3-投票中 4-截止投票 5-发布结果 6-结束
            if ($vote['status'] == 4 || $vote['status'] == 5 || $vote['status'] == 6 || $vote['vote_end_time'] < time()) {
                //$voteStatus 1-活动结束 2-活动中
                $voteStatus = 1;
            } else {
                $voteStatus = 2;
            }
            //是否已参加活动 1-未参加 2-已参加
            $isJoin = M('contribution_record')->where(['user_id' => $this->user['id'], 'vote_id' => $work['vote_id']])->getField('id');
            if ($isJoin) {
                $join = 2;
            } else {
                $join = 1;
            }
            //判断是否自己的作品  1-是 2-否
            $isMyContribution = $work['user_id'] == $this->user['id'] ? 1 : 2;
            //每次增加一次浏览记录
            M('vote')->where('id=' . $vote['id'])->setInc('visit_sum');
            //是否有获得每日票数
            $isGet = D('VoteRecord')->everydayCount($this->user['id']);
            if ($isGet == 1) {
                $countData = [
                    'create_time' => time(),
                    'user_id' => $this->user['id'],
                    'type' => 1,
                    'type_id' => $_GET['share_user_id'],
                    'count' => 2,
                    'is_expend' => 2,
                ];
                D('VoteRecord')->insert($countData);
            }
            /*//获取可投票数
            $quotaWhere = [
                'create_time' => ['elt', time()],
                'end_time' => ['gt', time()],
                'user_id' => $this->user['id'],
            ];
            $count = M('vote_quota')->where($quotaWhere)->sum('remain_count');
            //判断每日投票数
            $todayTime = strtotime(date("Y-m-d", time()));
            $todayQuota = M('vote_record')->where(['type' => 3, 'create_time' => ['egt', $todayTime], 'user_id' =>
                $this->user['id']])->sum('count');
            $todayQuota = 2 - $todayQuota >= 0 ? 2 - $todayQuota : 0;
            $count = $count + $todayQuota;*/
            //更多作品
            $contributions = D('ContributionRecord')->random($vote['id'], $work['id']);
            if ($vote['upload_type'] == 1) {
                $image = json_decode($work['path'], true);
                $work['path'] = $image;
                foreach ($contributions as $k => $v) {
                    $images = json_decode($v['path'], true);
                    $contributions[$k]['path'] = $images[0];
                }
            } else {
                $work['video'] = $work['path'];
                $image = substr($work['path'], 0, strlen($work['path']) - 3);
                $work['path'] = $image . 'jpg';
                foreach ($contributions as $k => $v) {
                    $images = substr($v['path'], 0, strlen($v['path']) - 3);
                    $contributions[$k]['path'] = $images . 'jpg';
                }
            }
            $data = [
                'work' => $work,
                'more' => $contributions,
                'upload_type' => $vote['upload_type'],
                'isContribute' => $isContribute,
                'join' => $join,
                'voteStatus' => $voteStatus,
                'desc' => $vote['description'],
                'isMyContribution' => $isMyContribution,
                'integral' => $this->user['integral'],
                'start_time' => date('Y.m.d', $vote['work_start_time']),
                'end_time' => date('Y.m.d', $vote['vote_end_time'])
//                'userCount' => $count
            ];
            return show(1, '', $data);
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function contribution()
    {
        $this->display();
    }

    //投稿
    public function ajaxContribution()
    {
        if (!$_POST['vote_id']) {
            return show(0, '投票ID参数错误');
        }
        //判断是否已投稿
        $isContribution = M('contribution_record')->where(['vote_id' => $_POST['vote_id'], 'user_id' => $this->user['id']])->getField('id');
        $vote = D('Vote')->find($_POST['vote_id']);
        if (!$isContribution) {
            if (!$_POST['title']) {
                return show(0, '请填写你的作品名称');
            }
            if (!$_POST['username']) {
                return show(0, '参赛人不能为空');
            }
            if (!$_POST['mobile']) {
                return show(0, '联系手机不能为空');
            }
            if ($vote['upload_type'] == 1) {
                if (!$_POST['path'][0]) {
                    return show(0, '第一张作品不能为空');
                }
                $path = json_encode($_POST['path']);
            } else {
                if (!$_POST['path']) {
                    return show(0, '作品不能为空');
                }
                $path = $_POST['path'];
            }
            $number = M('contribution_record')->where(['vote_id' => $_POST['vote_id']])->field('max(number)')->select();
            $data = [
                'create_time' => time(),
                'user_id' => $this->user['id'],
                'type' => $vote['upload_type'],
                'vote_id' => $vote['id'],
                'path' => $path,
                'title' => $_POST['title'],
                'username' => $_POST['username'],
                'mobile' => $_POST['mobile'],
                'number' => (int)$number[0]['max(number)'] + 1,
            ];
            $id = D('ContributionRecord')->insert($data);
            if ($id) {
                if ($_POST['share_user_id'] && $_POST['is_share']) {
                    M('contribution_record')->where(array('user_id' => $_POST['share_user_id'], 'vote_id' => $vote['id']))->setInc('vote_count', 9);
                    $insertData = [
                        'create_time' => time(),
                        'user_id' => $_POST['share_user_id'],
                        'type' => 5,
                        'type_id' => $this->user['id'],
                        'count' => 9,
                        'is_expend' => 2,
                    ];
                    D('VoteRecord')->insert($insertData);
                    $username = D('User')->getNameById($_POST['share_user_id']);
                    return show(1, '恭喜您投稿成功，并给你的好友' . $username . '助力9票，投稿成功后需活动方审核通过后才能在相关页面显示，可在“我的作品”页查看审核状态。！');
                }
                return show(1, '恭喜您投稿成功，投稿成功后需活动方审核通过后才能在相关页面显示，可在“我的作品”页查看审核状态。！');
            } else {
                return show(0, '投稿失败，请稍候再试');
            }
        } else {
            $image = M('contribution_record')->where(['vote_id' => $_POST['vote_id'], 'user_id' => $this->user['id']])->getField('path');
            if ($vote['upload_type'] == 1) {
                $oldPath = json_decode($image, true);
                foreach ($oldPath as $k => $v) {
                    //删除图片
                    if (!empty($_POST['path'][$k])) {
                        unlink(dirname(__FILE__) . '/../../..' . $v);
                        $oldPath[$k] = $_POST['path'][$k];
                    }
                }
                $path = json_encode($oldPath);
            } else {
                //删除视频
                unlink(dirname(__FILE__) . '/../../..' . $image);
                $path = $_POST['path'];
            }
            $data = [
                'path' => $path,
                'create_time' => time(),
                'title' => $_POST['title'],
                'username' => $_POST['username'],
                'mobile' => $_POST['mobile'],
                'status' => 1,
                'template_status' => 1,
            ];
            $id = D('ContributionRecord')->updateById($isContribution, $data);
            if ($id === false) {
                return show(0, '重新投稿失败');
            }
            return show(1, '恭喜您重新投稿成功，投稿成功后需活动方审核通过后才能在相关页面显示，可在“我的作品”页查看审核状态！');
        }
    }


    //投票
    public function toVote()
    {
        if (!$_POST['id']) {
            return show(0, '参数错误');
        }
        if (!is_numeric($_POST['count']) || $_POST['count'] < 0) {
            return show(0, '投票数不正确');
        }
        try {
            $userWork = M('contribution_record')->where(array('id' => $_POST['id'], 'status' => 2))->field('vote_id,user_id')->find();
            if ($userWork['user_id'] == $this->user['id']) {
                return show(0, '不能给自己投票哦！');
            }
            $isVote = M('vote')->where(array('id' => $userWork['vote_id'], 'work_start_time' => array('ELT', time()),
                'vote_end_time' => array('EGT', time()), 'check_status' => 2))->getField('id');
            if (!$isVote) {
                return show(0, '活动已过期，请去正在进行的活动投票吧');
            }
            $voteCount = $_POST['count'];
            //获取可投票数
            $quotaWhere = [
                'user_id' => $this->user['id'],
                'remain_count' => ['gt', 0],
            ];
            $count = M('vote_quota')->where($quotaWhere)->sum('remain_count');
            //判断每日投票数
            $todayTime = strtotime(date("Y-m-d", time()));
            $todayQuota = M('vote_record')->where(['type' => 3, 'create_time' => ['egt', $todayTime], 'user_id' =>
                $this->user['id']])->sum('count');
            $todayQuota = 2 - $todayQuota >= 0 ? 2 - $todayQuota : 0;
            $count = $count + $todayQuota;
            if ($_POST['count'] > $count) {
                return show(0, '你的票数不足哦！每天登录平台可获得2张免费票，在平台购物可获大量票数哦！');
            }
            //先扣除每日投票数
            if ($todayQuota > 0) {
                $insertCount = $voteCount - $todayQuota <= 0 ? $voteCount : $todayQuota;
                $insertData = [
                    'create_time' => time(),
                    'user_id' => $this->user['id'],
                    'type' => 3,
                    'type_id' => $_POST['id'],
                    'count' => $insertCount,
                    'is_expend' => 1,
                ];
                D('VoteRecord')->insert($insertData);
                M('contribution_record')->where(['id' => $_POST['id']])->setInc('vote_count', $insertCount);
            }
            if ($voteCount - $todayQuota <= 0) {
                return show(1, '投票成功');
            } else {
                //超过每日免费票数
                $voteCount -= $todayQuota;
                $voteQuatos = M('vote_quota')->where($quotaWhere)->order('create_time asc')->select();
                foreach ($voteQuatos as $key => $val) {
                    $insertCount = $voteCount - $val['remain_count'] <= 0 ? $voteCount : $val['remain_count'];
                    $voteCount -= $insertCount;
                    $insertData = [
                        'create_time' => time(),
                        'user_id' => $this->user['id'],
                        'type' => 4,
                        'type_id' => $_POST['id'],
                        'count' => $insertCount,
                        'is_expend' => 1,
                    ];
                    if ($val['type'] == 2) {
                        $insertData['type'] = 5;
                    }
                    D('VoteRecord')->insert($insertData);
                    M('vote_quota')->where(['id' => $val['id']])->setDec('remain_count', $insertCount);
                    M('contribution_record')->where(['id' => $_POST['id']])->setInc('vote_count', $insertCount);
                    if ($voteCount <= 0) {
                        return show(1, '投票成功');
                    }
                }
                return show(1, '投票成功');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //查看票数
    public function lookVoteCount()
    {
        //获取可投票数
        $quotaWhere = [
            'user_id' => $this->user['id'],
            'remain_count' => ['gt', 0],
        ];
        $count = M('vote_quota')->where($quotaWhere)->sum('remain_count');
        //判断每日投票数
        $todayTime = strtotime(date("Y-m-d", time()));
        $todayQuota = M('vote_record')->where(['type' => 3, 'create_time' => ['egt', $todayTime], 'user_id' =>
            $this->user['id']])->sum('count');
        $todayQuota = 2 - $todayQuota >= 0 ? 2 - $todayQuota : 0;
        $count = $count + $todayQuota;
        return show(1, '获取成功', ['count' => $count]);
    }

    //查看用户作品分享
    public function userContribution()
    {
        $this->display();
    }

    //作品详情
    public function voteContribution()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        $contribution = D('ContributionRecord')->find($_GET['id']);
        $vote = D('Vote')->find($contribution['vote_id']);
        if ($vote['upload_type'] == 1) {
            $path = json_decode($contribution['path'], true);
            $image = $path[0];
        } else {
            $image = substr($contribution['path'], 0, strlen($contribution['path']) - 3) . 'jpg';
        }
        $username = D('User')->getNameById($this->user['id']);
        //设置默认分享信息
        $shareData = [
            'share_title' => '【优培·投票】' . '我是' . $contribution['username'] . ',正在参加' . $vote['title'],
            'share_desc' => "你的好友" . $username . "邀请你帮Ta投票",
            'share_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/index.php/Vote/voteDetail.html?id=' . $_GET['id'] .
                '&vote_id=' . $contribution['vote_id'] . '&share_user_id=' . $this->user['id'] . '&token=' . $this->token,
            'share_img' => 'http://' . $_SERVER['HTTP_HOST'] . $image,
        ];
        return show(1, '获取成功', $shareData);
    }

    //投票作品列表
    public function voteListContribution()
    {
        if (!$_GET['vote_id']) {
            return show(0, '参数错误');
        }
        $vote = D('Vote')->find($_GET['vote_id']);
        //设置默认分享信息
        $shareData = [
            'share_title' => '【优培·投票】' . $vote['title'],
            'share_desc' => "快来参与投票吧！",
            'share_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/index.php/Vote/voteList?vote_id=' . $_GET['vote_id'] .
                '&share_user_id=' . $this->user['id'] . '&token=' . $this->token,
            'share_img' => 'http://' . $_SERVER['HTTP_HOST'] . $vote['image'],
        ];
        return show(1, '获取成功', $shareData);
    }

    //投票列表
    public function voteActivitContribution()
    {
        if ($_GET) {
            if ($_GET['vote_id']) {
                $vote = D('Vote')->find($_GET['vote_id']);
                $shareData['title'] = $vote['title'];
            }
        }
        //设置默认分享信息
        $shareData['share_img'] = 'http://' . $_SERVER['HTTP_HOST'] . '/Public/images/logo_m.png';
        $shareData['username'] = $this->user['username'];
        return show(1, '获取成功', $shareData);
    }

    public function uploadType()
    {
        if (!$_GET['vote_id']) {
            return show(0, 'ID不能为空');
        }
        //1-图片 2-视频
        $vote = M('vote')->where(array('id' => $_GET['vote_id']))->find();
        if (!$vote) {
            return show(0, '该活动不存在');
        }
        //1-截止投稿 2-投稿中 3-投稿未开始
        if ($vote['work_start_time'] > time()) {
            $isContribute = 3;
        } elseif ($vote['work_start_time'] <= time() && $vote['work_end_time'] >= time()) {
            $isContribute = 2;
        } else {
            $isContribute = 1;
        }
        $userUpload = D('ContributionRecord')->uploadStatus($_GET['vote_id'], $this->user['id']);
        //1-未投稿 2-已投稿
        if (!$userUpload) {
            $userUpload['upload_status'] = 1;
        } else {
            $userUpload['upload_status'] = 2;
        }
        if ($vote['upload_type'] == 1) {
            $image = json_decode($userUpload['path'], true);
            $userUpload['path'] = $image;
        } else {
            $userUpload['video'] = $userUpload['path'];
            $image = substr($userUpload['path'], 0, strlen($userUpload['path']) - 3);
            $userUpload['path'] = $image . 'jpg';
        }
        $age_bracket = [
            [
                'id' => 1,
                'title' => '3-4岁'
            ],
            [
                'id' => 2,
                'title' => '5-6岁'
            ],
            [
                'id' => 3,
                'title' => '7岁以上'
            ],
        ];
        $data = [
            'type' => $vote['upload_type'],
            'userUpload' => $userUpload,
            'age_bracket' => $age_bracket,
            'isContribute' => $isContribute
        ];
        return show(1, '获取类型成功', $data);
    }

    //我的作品
    public function userWorks()
    {
        $this->display();
    }

    public function ajaxUserWorks()
    {
        if (!$_GET['vote_id']) {
            return show(0, 'ID不能为空');
        }
        //status 1-审核中 2-入选 3-落选
        $work = M('contribution_record')->where(array('user_id' => $this->user['id'], 'vote_id' => $_GET['vote_id']))->
        field('id,create_time,path,title,vote_count,vote_id,status,number')->find();
        $vote = M('vote')->where(array('id' => $_GET['vote_id']))->field('id,upload_type,status,work_start_time,work_end_time,vote_end_time')->find();
        //1-截止投稿 2-投稿中 3-未开始
        if ($vote['work_start_time'] > time()) {
            $isContribute = 3;
        } elseif ($vote['work_start_time'] <= time() && $vote['work_end_time'] >= time()) {
            $isContribute = 2;
        } else {
            $isContribute = 1;
        }
        if (!$work) {
//            不为空
            $work['is_null'] = 0;
        } else {
            $work['is_null'] = 1;
        }
        if ($vote['upload_type'] == 1) {
            $image = json_decode($work['path'], true);
            $work['path'] = $image;
        } else {
            $work['video'] = $work['path'];
            $image = substr($work['path'], 0, strlen($work['path']) - 3);
            $work['path'] = $image . 'jpg';
        }
        $work['time'] = date('Y-m-d', $work['create_time']);
        $work['start_time'] = date('Y-m-d', $vote['work_start_time']);
        $work['isContribute'] = $isContribute;
        $work['upload_type'] = $vote['upload_type'];
        //status 1-投稿中 2-截止投稿 3-投票中 4-截止投票 5-发布结果 6-结束
        if ($vote['status'] == 4 || $vote['status'] == 5 || $vote['status'] == 6 || $vote['vote_end_time'] < time()) {
            //voteStatus 1-活动结束 2-活动中
            $work['voteStatus'] = 1;
        } else {
            $work['voteStatus'] = 2;
        }
        return show(1, '', $work);
    }

    public function userVote()
    {
        $this->display();
    }

    //我的投票
    public function ajaxUserVote()
    {
        if (!$_GET['vote_id']) {
            return show(0, 'ID不能为空');
        }
        $worksList = M('contribution_record')->where(array('vote_id' => $_GET['vote_id'], 'status' => 2))
            ->field('id,path,title,username,vote_id,vote_count,create_time,number')->select();
        $vote = M('vote')->where(array('id' => $_GET['vote_id'], 'check_status' => 2))
            ->field('id,upload_type,status,vote_end_time')->find();
        $workList = [];
        $list = [];
        if ($worksList) {
            foreach ($worksList as $l => $i) {
                $list[$l] = M('vote_record')->where(array('type' => array(array('EQ', 3), array('EQ', 4), 'or'),
                    'user_id' => $this->user['id'], 'type_id' => $i['id']))->distinct(true)->field('type_id')->select();
                if ($list[$l]) {
                    $workList[$l] = $i;
                    $workList[$l]['time'] = date('Y-m-d', $i['create_time']);
                    if ($vote['upload_type'] == 1) {
                        $image[$l] = json_decode($i['path'], true);
                        $workList[$l]['path'] = $image[$l][0];
                    } else {
                        $workList[$l]['path'] = substr($i['path'], 0, strlen($l['path']) - 3) . 'jpg';
                    }
                }
            }
        }
        //status 1-投稿中 2-截止投稿 3-投票中 4-截止投票 5-发布结果 6-结束
        if ($vote['status'] == 4 || $vote['status'] == 5 || $vote['status'] == 6 || $vote['vote_end_time'] < time()) {
            //voteStatus 1-活动结束 2-活动中
            $voteStatus = 1;
        } else {
            $voteStatus = 2;
        }
        //1-图片 2-视频
        $upload_type = $vote['upload_type'];
        if (!$workList) {
            $is_null = 0;
        } else {
            $is_null = 1;
        }
        //重新排序数组
        $workList = array_merge($workList);
        return show(1, '', ['workList' => $workList, 'is_null' => $is_null, 'voteStatus' => $voteStatus,
            'upload_type' => $upload_type, 'integral' => $this->user['integral']]);
    }

    public function userBillboard()
    {
        $this->display();
    }

    public function ajaxUserBillboard()
    {
        if (!$_GET['vote_id']) {
            return show(0, 'ID不能为空');
        }
        $vote = D('Vote')->find($_GET['vote_id']);
        //status 1-投稿中 2-截止投稿 3-投票中 4-截止投票 5-发布结果 6-结束
        if ($vote['status'] == 4 || $vote['status'] == 5 || $vote['status'] == 6 || $vote['vote_end_time'] < time()) {
            //voteStatus 1-活动结束 2-活动中
            $voteStatus = 1;
        } else {
            $voteStatus = 2;
        }
        //投票数倒叙排序
        $list = M('contribution_record')->where(array('vote_id' => $vote['id'], 'status' => 2))
            ->order('vote_count desc ,create_time asc')->field('id,path,username,user_id,title,vote_count,vote_id')->select();
        //1-截止投稿 2-投稿中 3-未开始
        if ($vote['work_start_time'] > time()) {
            $isContribute = 3;
        } elseif ($vote['work_start_time'] <= time() && $vote['work_end_time'] >= time()) {
            $isContribute = 2;
        } else {
            $isContribute = 1;
        }
        if (!$list) {
            $isNull = 0;
        } else {
            $isNull = 1;
        }
        $count = [];
        foreach ($list as $key => $val) {
            if ($vote['upload_type'] == 1) {
                $path[$key] = json_decode($val['path'], true);
                $list[$key]['image'] = $path[$key][0];
            } else {
                $list[$key]['image'] = substr($val['path'], 0, strlen($val['path']) - 3) . 'jpg';
            }
            $count[$key] = $val['vote_count'];
        }
        //给当前排序排名
        $arr1 = $count;
        rsort($arr1);
        $c = [];
        foreach ($count as $v) {
            $b = array_search($v, $arr1);
            $c[] = $b + 1;
        }
        foreach ($c as $j => $l) {
            $list[$j]['ranking'] = $l;
        }
        //奖品
        //type 1-优惠券 2-代金券 3-积分 4-实物
        //prize_level 1-一等奖 2-二等奖 3-三等奖 4-安慰奖
        $prize = M('vote_prize')->where(['vote_id' => $_GET['vote_id']])->order('prize_level asc')->select();
        $data = [
            'endTime' => $vote['vote_end_time'],
            'voteStatus' => $voteStatus,
            'list' => $list,
            'isContribute' => $isContribute,
            'start_time' => date('Y-m-d', $vote['work_start_time']),
            'is_null' => $isNull,
            'title' => $vote['title'],
            'prize' => $prize
        ];
        return show(1, '', $data);
    }

    public function upload()
    {
        $this->display();
    }

    public function uploadPic()
    {
        $config = array(
            'maxSize' => 5242880, //上传的文件大小限制 (0-不做限制)
            'exts' => array('jpg', 'png', 'gif', 'jpeg', 'mp4'), //允许上传的文件后缀
            'rootPath' => './Upload/', //保存根路径
            'driver' => 'LOCAL', // 文件上传驱动
            'subName' => array('date', 'Y-m-d'),
            'savePath' => I('dir', 'uploads') . "/"
        );
        $dirs = explode(",", C("YP_UPLOAD_DIR"));
        if (!file_exists($dirs)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($dirs, 0777, true);
        }
        $upload = new \Think\Upload($config);
        $rs = $upload->upload($_FILES);
        $Filedata = key($_FILES);
        $type = explode("/", $_FILES['file']['type']);
        if (!$rs) {
            $this->ajaxReturn(['status' => 0, 'message' => $upload->getError()], 'JSON');
        } else {
            $uploadType = I('upload_type', 'uploads');
            if ($uploadType == 1 && $type[0] != 'image') {
                $this->ajaxReturn(['status' => 0, 'message' => '请上传图片'], 'JSON');
            } elseif ($uploadType == 2 && $type[0] != 'video') {
                $this->ajaxReturn(['status' => 0, 'message' => '请上传视频'], 'JSON');
            }
            $images = new \Think\Image();
            $images->open('./Upload/' . $rs[$Filedata]['savepath'] . $rs[$Filedata]['savename']);
            $newsavename = str_replace('.', '_thumb.', $rs[$Filedata]['savename']);
            $vv = $images->thumb(I('width', 300), I('height', 300))->save('./Upload/' . $rs[$Filedata]['savepath'] . $newsavename);
            if (C('YP_M_IMG_SUFFIX') != '') {
                $msuffix = C('YP_M_IMG_SUFFIX');
                $mnewsavename = str_replace('.', $msuffix . '.', $rs[$Filedata]['savename']);
                $mnewsavename_thmb = str_replace('.', "_thumb" . $msuffix . '.', $rs[$Filedata]['savename']);
                $images->open('./Upload/' . $rs[$Filedata]['savepath'] . $rs[$Filedata]['savename']);
                $images->thumb(I('width', 700), I('height', 700))->save('./Upload/' . $rs[$Filedata]['savepath'] . $mnewsavename);
                $images->thumb(I('width', 250), I('height', 250))->save('./Upload/' . $rs[$Filedata]['savepath'] . $mnewsavename_thmb);
            }
            $data[$Filedata]['savepath'] = "Upload/" . $rs[$Filedata]['savepath'];
//            $data[$Filedata]['savethumbname'] = $newsavename;注释代码：缩略图
            $data[$Filedata]['savethumbname'] = $rs[$Filedata]['savename'];
            $data[$Filedata]['savename'] = $rs[$Filedata]['savename'];
            $data['status'] = 1;
            $this->ajaxReturn($data, 'JSON');
        }
    }


    public function uploadVideo()
    {
        $config = array(
            'maxSize' => 12582912, //上传的文件大小限制 (0-不做限制)
            'exts' => array('jpg', 'png', 'gif', 'jpeg', 'mp4'), //允许上传的文件后缀
            'rootPath' => './Upload/', //保存根路径
            'driver' => 'LOCAL', // 文件上传驱动
            'subName' => array('date', 'Y-m-d'),
            'savePath' => I('dir', 'uploads') . "/"
        );
        $dirs = explode(",", C("YP_UPLOAD_DIR"));
        if (!file_exists($dirs)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($dirs, 0777, true);
        }
        $upload = new \Think\Upload($config);
        $rs = $upload->upload($_FILES);
        $Filedata = key($_FILES);
        $type = explode("/", $_FILES['file']['type']);
        if (!$rs) {
            $this->ajaxReturn(['status' => 0, 'message' => $upload->getError()], 'JSON');
        } else {
            $uploadType = I('upload_type', 'uploads');
            if ($uploadType == 1 && $type[0] != 'image') {
                $this->ajaxReturn(['status' => 0, 'message' => '请上传图片'], 'JSON');
            } elseif ($uploadType == 2 && $type[0] != 'video') {
                $this->ajaxReturn(['status' => 0, 'message' => '请上传视频'], 'JSON');
            }
            //截取视频第一帧做缩略图
            $name = explode('.', $rs[$Filedata]['savename']);
            $from = "http://" . $_SERVER['HTTP_HOST'] . "/Upload/" . $rs[$Filedata]['savepath'] . $rs[$Filedata]['savename'];
            $imageName = "./Upload/" . $rs[$Filedata]['savepath'] . $name[0] . '.jpg';
            $str = "ffmpeg -i " . $from . " -y -f mjpeg -ss 3 -t 1 -s 768x1024 " . $imageName;
            system($str);
            $data[$Filedata]['savepath'] = "Upload/" . $rs[$Filedata]['savepath'];
            $data[$Filedata]['savevideo'] = $rs[$Filedata]['savename'];
            $savethumbname = substr($rs[$Filedata]['savename'], 0, strlen($rs[$Filedata]['savename']) - 3);
            $data[$Filedata]['savethumbname'] = $savethumbname . 'jpg';
            $data['status'] = 1;
            $this->ajaxReturn($data, 'JSON');
        }
    }


    protected function __checkOpenId()
    {
        $openid = $_SESSION['openid'];
        if (!$openid) {
            echo "暂无openID";
        }
        return $openid;
    }

    function createQrcode($url, $filename)
    {
        import("Vendor.phpqrcode.phpqrcode");//引入工具包
        $dir = "Upload/qrcode/";
        if (!file_exists($dir)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($dir, 0777, true);
        }
        $filename = "./" . $dir . $filename;
        \QRcode::png($url, $filename, 'L', '4', 2);
        return $filename;
    }

    function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        //下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    public function createVotePoster()
    {
        if (!$_GET['vote_id']) {
            return show(0, '参数错误');
        }
        $qrcodeUrl = $this->getQrcodeVoteUrl($_GET['vote_id'], $this->user['id']);
        $qrcodeFilename = uniqid(time()) . '.png';
        $qrcodePic = $this->createQrcode($qrcodeUrl, $qrcodeFilename);
        $userWork = M('contribution_record')->where(['user_id' => $this->user['id'], 'vote_id' => $_GET['vote_id']])->field('path,type')->find();
        if ($userWork['type'] == 1) {
            $voteImages = json_decode($userWork['path']);
            $image = $voteImages[0];
        } else {
            $image = substr($userWork['path'], 0, strlen($userWork['path']) - 3) . 'jpg';
        }
        $contributionTitle = M('contribution_record')->where(['user_id' => $this->user['id'], 'vote_id' => $_GET['vote_id']])->getField('title');
        $contributionUsername = M('contribution_record')->where(['user_id' => $this->user['id'], 'vote_id' => $_GET['vote_id']])->getField('username');
        $voteTitle = M('vote')->where(['id' => $_GET['vote_id']])->getField('title');
        $background = imagecreatetruecolor(900, 1600); // 背景图片
        $color = imagecolorallocate($background, 255, 255, 255); // 为真彩色画布创建白色背景，再设置为透明
        imagefill($background, 0, 0, $color);
        //判断商品图片类型
        $voteImagePathInfo = pathinfo($image);
        switch (strtolower($voteImagePathInfo['extension'])) {
            case 'jpg' :
            case 'jpeg' :
                $gdImage = imagecreatefromjpeg('.' . $image);
                break;
            case 'png' :
                $gdImage = imagecreatefrompng('.' . $image);
                break;
            default :
                $voteImage = file_get_contents('.' . $image);
                $gdImage = imagecreatefromstring('.' . $voteImage);
        }
        $gdQrcodePic = imagecreatefrompng($qrcodePic);
        //背景图片
        $posterBackground = imagecreatefrompng("./Public/images/voteBackground.png");
        imagecopyresized($background, $posterBackground, 0, 0, 0, 0, 900, 1600, imagesx($posterBackground), imagesy($posterBackground));
        //作品图片位置
        imagecopyresized($background, $gdImage, 40, 40, 0, 0, 820, 990, imagesx($gdImage), imagesy($gdImage));
        //二维码地址
        imagecopyresized($background, $gdQrcodePic, 603, 1165, 0, 0, 250, 260, imagesx($gdQrcodePic), imagesx($gdQrcodePic));
        //作品名
        imagettftext($background, 30, 0, 260, 1190, imagecolorallocate($background, 70, 130, 180), "Font/msyh.ttc", $contributionTitle);
        //作者
        imagettftext($background, 30, 0, 260, 1275, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", $contributionUsername);
        imagettftext($background, 20, 0, 30, 1380, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", $this->user['username'] . '正在参加' . $voteTitle . '！');
        imagettftext($background, 20, 0, 30, 1470, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", '请识别右侧二维码投票给TA吧！（每天都可以投）');
        $posterDir = "Upload/" . date("Ymd", time()) . '/';
        if (!file_exists($posterDir)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($posterDir, 0777, true);
        }
        $posterFilename = "Upload/" . date('Ymd', time()) . "/" . uniqid(time()) . '.png';
        imagepng($background, $posterFilename);
        //删除二维码图片
        unlink($qrcodePic);
        return show(1, '', '/' . $posterFilename);
    }

    function getQrcodeVoteUrl($voteId, $shareUserId)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create";
        $body = array(
            'action_name' => 'QR_LIMIT_STR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_str' => "isVote_voteId={$voteId}_shareUserId={$shareUserId}",
                )
            )
        );
        $body = json_encode($body);
        //生成结果返回
        $result = post_weixin_curl(get_wxuser("g232238gc959"), $url, $body);
        return $result['url'];
    }


    public function votePrize()
    {
        $list = M('vote_prize_record')->order('id desc')->limit(30)->select();
        $userList = [];
        foreach ($list as $k => $v) {
            $prizeList[$k] = M('vote_prize')->where(array('id' => $v['prize_id']))->find();
            $vote[$k] = M('vote')->where(array('id' => $prizeList[$k]['vote_id']))->find();
            $username[$k] = D('User')->getNameById($v['user_id']);
            $userList[$k]['title'] = $vote[$k]['title'];
            $userList[$k]['username'] = $username[$k];
            $userList[$k]['prize'] = $prizeList[$k]['desc'];
            $userList[$k]['prizeLevel'] = $prizeList[$k]['title'];
        }
        return show(1, '', $userList);
    }

    //兑换票数
    public function exchange()
    {
        if (!is_numeric($_POST['count']) || empty($_POST['count'])) {
            return show(0, '请输入要兑换的积分数');
        }
        if ($_POST['count'] > $this->user['integral']) {
            return show(0, '积分不足，请重新输入');
        }
        $userId = M('user')->where(['id' => $this->user['id']])->setDec('integral', $_POST['count']);
        if ($userId) {
            $integralData = [
                'user_id' => $this->user['id'],
                'token' => $this->token,
                'integral' => $_POST['count'],
                'create_time' => time(),
                'status' => 1,
                'type' => 1,
                'integral_type' => 13,
                'desc' => '积分兑换票数'
            ];
            D('IntegralRecord')->insert($integralData);
        }
        $data = [
            'create_time' => time(),
            'user_id' => $this->user['id'],
            'type' => 2,
            'count' => $_POST['count'],
            'remain_count' => $_POST['count'],
        ];
        $id = D('VoteQuota')->insert($data);
        if ($userId && $id) {
            return show(1, '兑换成功');
        } else {
            return show(0, '兑换失败，请刷新页面再试');
        }
    }


}