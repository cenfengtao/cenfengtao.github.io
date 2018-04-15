<?php
/**
 * 投票
 */
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;

class VoteController extends CommonController
{
    public function getList()
    {
        if ($this->isSuper) {
            $list = D('Vote')->getList();
        } else {
            $org_id = M('organization')->where(['token' => $this->token])->getField('id');
            $list = D('Vote')->getList(['type' => 1, 'type_id' => $org_id]);
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function addVote()
    {
        if ($_POST) {
            if (!$_POST['title']) {
                return show(0, '投票标题不能为空');
            }
            if (!$_POST['sponsor']) {
                return show(0, '主办单位不能为空');
            }
            if (!$_POST['work_start_time']) {
                return show(0, '开始投稿时间不能为空');
            } else {
                $_POST['work_start_time'] = strtotime($_POST['work_start_time']);
            }
            if (!$_POST['work_end_time']) {
                return show(0, '截稿时间不能为空');
            } else {
                $_POST['work_end_time'] = strtotime($_POST['work_end_time']) + 86399;
            }
            if (!$_POST['vote_end_time']) {
                return show(0, '结束投票时间不能为空');
            } else {
                $_POST['vote_end_time'] = strtotime($_POST['vote_end_time']) + 86399;
            }
            if (!$_POST['image']) {
                unset($_POST['image']);
            }
            $_POST['type'] = 1;
            try {
                $org_id = M('organization')->where(['token' => $this->token])->getField('id');
                $_POST['type_id'] = $org_id;
                if ($_POST['id']) {
                    return $this->save(array_merge($_POST, ['check_status' => 1]));
                }
                $id = D('Vote')->insert($_POST);
                if ($id) {
                    return show(1, '添加成功，请等待审核');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            $orgList = D('Organization')->getOrgList('org_name,id');
            $this->assign('orgList', $orgList);
            $this->display();
        }
    }

    public function editVote()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        try {
            $vote = D('Vote')->find($_GET['id']);
            $orgList = D('Organization')->getOrgList('org_name,id');
            $this->assign('vote', $vote)->assign('orgList', $orgList);
            $this->display();
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function save($data)
    {
        try {
            $id = D('Vote')->updateById($data['id'], $data);
            if ($id === false) {
                return show(0, '修改失败');
            }
            return show(1, '修改成功');
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $result = D('Vote')->delete($_POST['id']);
            if ($result) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //获取投稿列表
    public function getContribution()
    {
        if (!$_GET['id']) {
            return show(0, '投票ID参数错误');
        }
        $where = ['vote_id' => $_GET['id']];
        $list = D('ContributionRecord')->getList($where);
        $vote = D('Vote')->find($_GET['id']);
        foreach ($list as $k => $v) {
            if ($vote['mode'] == 2) {
                if ($v['vote_count'] >= $vote['vote_poll']) {
                    $list[$k]['ratio_count'] = $vote['vote_grade'];
                } else {//保留2位小数不四舍五入
                    $list[$k]['ratio_count'] = substr(sprintf("%.2f", $v['vote_count'] * ($vote['vote_grade'] / $vote['vote_poll'])), 0, -1);
                }
            }
            $list[$k]['nickname'] = D('User')->getNameById($v['user_id']);
        }
        $this->assign('list', $list)->assign('vote_id', $_GET['id'])->assign('mode', $vote['mode']);
        $this->display();
    }

    //修改投票结果
    public function submitContributionStatus()
    {
        if (!$_POST['id']) {
            return show(0, '参数错误');
        }
        if (!$_POST['status']) {
            return show(0, '结果参数错误');
        }
        $data = [
            'status' => $_POST['status'],
            'lose_reason' => $_POST['lose_reason']
        ];
        try {
            $id = D('ContributionRecord')->updateById($_POST['id'], $data);
            if ($id === false) {
                return show(0, '修改失败');
            } else {
                return show(1, '修改成功');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //发布投稿结果
    public function showContributionResult()
    {
        if (!$_GET['contribution_id']) {
            return show(0, '投票ID不能为空');
        }
        $contribution = D('ContributionRecord')->find($_GET['contribution_id']);
        $voteTitle = M('Vote')->where(['id' => $_GET['vote_id']])->getField('title');
        $nickname = M('user')->where(['id' => $contribution['user_id']])->getField('username');
        $userOpenid = M('user')->where(['id' => $contribution['user_id']])->getField('open_id');
        if ($contribution['status'] == 3) { //落选
            $first = $nickname . '，不要气馁，下次你一定可以的';
            $remark = '点击再次投稿吧';
            $url = "http://{$_SERVER['HTTP_HOST']}/index.php/Vote/contribution?vote_id=" . $_GET['vote_id'] . "&token=" . $this->token;
        } else if ($contribution['status'] == 2) { // 入围
            $first = $nickname . '，恭喜你，你的作品入围了，赶快去拉票吧';
            $remark = '点击查看入围作品';
            $url = "http://{$_SERVER['HTTP_HOST']}/index.php/Vote/voteDetail?id=" . $_GET['contribution_id'] . "&vote_id=" . $_GET['vote_id'] . "&token=" . $this->token;
        } else {
            return show(0, '作品参数错误');
        }
        $keyword1 = $voteTitle;
        $keyword2 = $contribution['status'] == 2 ? '入围' : "落选原因：" . $contribution['lose_reason'];
        $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__REMARK__');
        $infoFormat = array($userOpenid, $url, $first, $keyword1, $keyword2, $remark);
        $wxuser = get_wxuser("g232238gc959");
        $result = execute_public_template('CONTRIBUTION', $templeFormat, $infoFormat, $wxuser);
        if ($result['errcode'] == 0) {
            D('ContributionRecord')->updateById($contribution['id'], ['template_status' => 2]);
        } else {
            D('ContributionRecord')->updateById($contribution['id'], ['template_status' => 3]);
            return show(0, '发送失败');
        }
        return show(1, '发送成功');
    }

    public function showContribution()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        $contribution = M('contribution_record')->where(['id' => $_GET['id']])->field('path,type')->find();
        if ($contribution['type'] == 1) {
            $path = json_decode($contribution['path'], true);
        } else {
            $path = $contribution['path'];
        }
        if (!$contribution) {
            return show(0, '获取不了该作品');
        } else {
            return show(1, '获取成功', ['path' => $path, 'type' => $contribution['type']]);
        }
    }

    public function getVoteList()
    {
        if (!$_GET['vote_id']) {
            return show(0, 'ID不能为空');
        }
        $contributionList = D('ContributionRecord')->getList(['vote_id' => $_GET['vote_id']]);
        $userList = [];
        foreach ($contributionList as $k => $v) {
            $userList[$k] = M('vote_record')->where(array('type_id' => $v['id'], 'is_expend' => 1))->select();
            foreach ($userList[$k] as $l => $i) {
                $userList[$k][$l]['voteUserName'] = D('User')->getNameById($i['user_id']);
                $userList[$k][$l]['workUserName'] = D('User')->getNameById($v['user_id']);
            }
        }
        $this->assign('list', $userList);
        $this->display();
    }


    public function getProductList()
    {
        if (!$_GET['vote_id']) {
            return show(0, 'ID不能为空');
        }
        $list = M('bargain')->where(array('type' => 3, 'vote_id' => $_GET['vote_id']))->select();
        foreach ($list as $k => $v) {
            $product[$k] = D('Product')->find($v['type_id']);
            $list[$k]['title'] = $product[$k]['title'];
            $list[$k]['count'] = $product[$k]['count'];
            $key[$k] = json_decode($product[$k]['price'], true);
            foreach ($key[$k] as $z => $y) {
                if ($z == $v['key']) {
                    $list[$k]['keyName'] = $y['class_normal'];
                }
            }
            $list[$k]['orgName'] = D('Organization')->getOrgnameByToken($v['token']);
        }
        $vote = D('Vote')->find($_GET['vote_id']);
        //status 1-投稿中 2-截止投稿 3-投票中 4-截止投票 5-发布结果 6-结束
        if ($vote['status'] == 4 || $vote['status'] == 5 || $vote['status'] == 6 || $vote['vote_end_time'] < time()) {
            //$voteStatus 1-活动结束 2-活动中
            $voteStatus = 1;
        } else {
            $voteStatus = 2;
        }
        $this->assign('list', $list)->assign('voteStatus', $voteStatus);
        $this->display();
    }

    public function addVoteProduct()
    {
        if ($_POST) {
            if (!$_POST['type_id']) {
                return show(0, 'ID不能为空');
            }
            $_POST['type'] = 3;
            $_POST['token'] = $this->token;
            try {
                if ($_POST['id']) {
                    return $this->edit($_POST);
                }
                $id = D('Bargain')->insert($_POST);
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            if (!$_GET['vote_id']) {
                return show(0, 'ID不能为空');
            }
            $list = M('product')->where(array('check_status' => 2, 'status' => 1, 'token' => $this->token))->select();
            $this->assign('list', $list)->assign('voteId', $_GET['vote_id']);
            $this->display();
        }
    }

    public function cancelVoteProduct()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $result = D('Bargain')->delete($_POST['id']);
            if ($result) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }


    public function prizeList()
    {
        if (!$_GET['vote_id']) {
            return show(0, 'ID参数错误');
        }
        $list = M('vote_prize')->where(['vote_id' => $_GET['vote_id']])->order('prize_level asc')->select();
        $count = count($list);
        $userList = M('contribution_record')->where(array('vote_id' => $_GET['vote_id'], 'vote_count' => array('gt', 0)))
            ->order('vote_count desc')->limit($count)->select();
        foreach ($list as $k => $v) {
            if ($v['type'] == 1) {
                $list[$k]['type_name'] = '优惠券';
            } elseif ($v['type'] == 2) {
                $list[$k]['type_name'] = '代金券';
            } elseif ($v['type'] == 3) {
                $list[$k]['type_name'] = '积分';
            } elseif ($v['type'] == 4) {
                $list[$k]['type_name'] = '实物';
            }
            if ($v['prize_level'] == 1) {
                $list[$k]['prize_name'] = '一等奖';
            } elseif ($v['prize_level'] == 2) {
                $list[$k]['prize_name'] = '二等奖';
            } elseif ($v['prize_level'] == 3) {
                $list[$k]['prize_name'] = '三等奖';
            } elseif ($v['prize_level'] == 4) {
                $list[$k]['prize_name'] = '安慰奖';
            }
            $list[$k]['work_title'] = $userList[$k]['title'];
            $list[$k]['username'] = $userList[$k]['username'];
            $list[$k]['user_id'] = $userList[$k]['user_id'];
        }
        $this->assign('vote_id', $_GET['vote_id'])->assign('list', $list);
        $this->display();
    }


    public function addVotePrize()
    {
        if ($_POST) {
            if (!$_POST['type']) {
                return show(0, '类型不能为空');
            }
            if ($_POST['prize_count'] < 1) {
                return show(0, '奖品数量不能小于1');
            }
            if (!$_POST['prize_level']) {
                return show(0, '奖品档次不能为空');
            }
            try {
                if (strtotime($_POST['start_time']) == strtotime($_POST['end_time'])) {
                    $end_time = strtotime($_POST['end_time']) + 86399;
                } else {
                    $end_time = strtotime($_POST['end_time']);
                }
                $prize_name = '';
                if ($_POST['prize_level'] == 1) {
                    $prize_name = '一等奖';
                } elseif ($_POST['prize'] == 2) {
                    $prize_name = '二等奖';
                } elseif ($_POST['prize'] == 3) {
                    $prize_name = '三等奖';
                } elseif ($_POST['prize'] == 4) {
                    $prize_name = '安慰奖';
                }
                $voteTitle = M('Vote')->where(['id' => $_POST['vote_id']])->getField('title');
                $org_id = M('organization')->where(array('token' => $this->token))->getField('id');
                if ($org_id == 1) {
                    $type = 2;
                } else {
                    $type = 1;
                }
                $data = [];
                if ($_POST['type'] == 1) {//优惠券
                    $data = [
                        'title' => $_POST['title'],
                        'vote_id' => $_POST['vote_id'],
                        'type' => $_POST['type'],
                        'prize_level' => $_POST['prize_level'],
                        'full' => $_POST['full'],
                        'subtract' => $_POST['subtract'],
                        'start_time' => strtotime($_POST['start_time']),
                        'end_time' => $end_time,
                        'create_time' => time(),
                        'image' => $_POST['image'],
                        'desc' => $_POST['desc'],
                    ];
                    //添加优惠券
                    $CouponOfferData = [
                        'start_time' => strtotime($_POST['start_time']),
                        'end_time' => $end_time,
                        'type' => $type,
                        'type_id' => $org_id,
                        'full' => $_POST['full'],
                        'subtract' => $_POST['subtract'],
                        'coupon_type' => 1,
                        'title' => '满' . $_POST['full'] . '减' . $_POST['subtract'],
                        'count' => $_POST['prize_count'],
                        'desc' => $voteTitle . $prize_name,
                        'source' => 1,
                        'source_id' => $_POST['vote_id'],
                        'extra' => $_POST['prize_level'],
                    ];
                    $offerId = D('CouponOffer')->insert($CouponOfferData);
                    if (!$offerId) {
                        return show(0, '添加失败');
                    }
                }
                if ($_POST['type'] == 2) {//代金券
                    $data = [
                        'title' => $_POST['title'],
                        'vote_id' => $_POST['vote_id'],
                        'type' => $_POST['type'],
                        'prize_level' => $_POST['prize_level'],
                        'fee' => $_POST['fee'],
                        'start_time' => strtotime($_POST['start_time']),
                        'end_time' => $end_time,
                        'create_time' => time(),
                        'image' => $_POST['image'],
                        'desc' => $_POST['desc'],
                    ];
                    //添加代金券
                    $CouponOfferData = [
                        'start_time' => strtotime($_POST['start_time']),
                        'end_time' => $end_time,
                        'type' => $type,
                        'type_id' => $org_id,
                        'fee' => $_POST['fee'],
                        'coupon_type' => 2,
                        'title' => $_POST['fee'] . '元代金券',
                        'count' => $_POST['prize_count'],
                        'desc' => $voteTitle . $prize_name,
                        'source' => 1,
                        'source_id' => $_POST['vote_id'],
                        'extra' => $_POST['prize_level'],
                    ];
                    $offerId = D('CouponOffer')->insert($CouponOfferData);
                    if (!$offerId) {
                        return show(0, '添加失败');
                    }
                }
                if ($_POST['type'] == 3) {//积分
                    $data = [
                        'title' => $_POST['title'],
                        'vote_id' => $_POST['vote_id'],
                        'type' => $_POST['type'],
                        'prize_level' => $_POST['prize_level'],
                        'integral' => $_POST['integral'],
                        'create_time' => time(),
                        'image' => $_POST['image'],
                        'desc' => $_POST['desc'],
                    ];
                }
                if ($_POST['type'] == 4) {//实物
                    $data = [
                        'title' => $_POST['title'],
                        'vote_id' => $_POST['vote_id'],
                        'type' => $_POST['type'],
                        'prize_level' => $_POST['prize_level'],
                        'create_time' => time(),
                        'image' => $_POST['image'],
                        'desc' => $_POST['desc'],
                        'status' => 1
                    ];
                }
                $id = '';
                for ($i = 0; $i < $_POST['prize_count']; $i++) {
                    $id = D('VotePrize')->insert($data);
                }
                if ($id) {
                    return show(1, '添加成功');
                } else {
                    return show(0, '添加失败');
                }
            } catch (Exception $e) {
                return show(0, $e->getMessage());
            }
        } else {
            if (!$_GET['vote_id']) {
                return show(0, 'ID不能为空');
            }
            $prizeLevelData = [
                1 => '一等奖',
                2 => '二等奖',
                3 => '三等奖',
                4 => '安慰奖'
            ];
            $prizeLevelList = M('vote_prize')->where(array('vote_id' => $_GET['vote_id']))->distinct(true)->field('prize_level')->select();
            foreach ($prizeLevelList as $k => $v) {
                foreach ($prizeLevelData as $l => $i) {
                    if ($v['prize_level'] == $l) {
                        unset($prizeLevelData[$l]);
                    }
                }
            }
            $orgList = D('Organization')->getOrgList();
            $this->assign('voteId', $_GET['vote_id'])->assign('orgList', $orgList)->assign('prizeLevelData', $prizeLevelData);
            $this->display();
        }
    }

    public function editVotePrize()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        try {
            $votePrize = D('VotePrize')->find($_GET['id']);
            $prizeLevelData = [
                1 => '一等奖',
                2 => '二等奖',
                3 => '三等奖',
                4 => '安慰奖'
            ];
            $prizeLevelList = M('vote_prize')->where(array('vote_id' => $votePrize['vote_id']))->distinct(true)->field('prize_level')->select();
            foreach ($prizeLevelList as $k => $v) {
                foreach ($prizeLevelData as $l => $i) {
                    if ($v['prize_level'] == $l) {
                        unset($prizeLevelData[$l]);
                    }
                    if ($votePrize['prize_level'] == $l) {
                        $votePrize['prize_name'] = $i;
                    }
                }
            }
            $prizeCount = M('vote_prize')->where(array('vote_id' => $votePrize['vote_id'], 'type' => $votePrize['type'], 'prize_level' => $votePrize['prize_level']))->count();
            $this->assign('votePrize', $votePrize)->assign('prizeLevelData', $prizeLevelData)->assign('prizeCount', $prizeCount);
            $this->display();
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }


    public function editPrize()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        if (!$_POST['type']) {
            return show(0, '类型不能为空');
        }
        if ($_POST['prize_count'] < 1) {
            return show(0, '奖品数量不能小于1');
        }
        if (!$_POST['prize_level']) {
            return show(0, '奖品档次不能为空');
        }
        try {
            if (strtotime($_POST['start_time']) == strtotime($_POST['end_time'])) {
                $end_time = strtotime($_POST['end_time']) + 86399;
            } else {
                $end_time = strtotime($_POST['end_time']);
            }
            $prize_name = '';
            if ($_POST['prize_level'] == 1) {
                $prize_name = '一等奖';
            } elseif ($_POST['prize_level'] == 2) {
                $prize_name = '二等奖';
            } elseif ($_POST['prize_level'] == 3) {
                $prize_name = '三等奖';
            } elseif ($_POST['prize_level'] == 4) {
                $prize_name = '安慰奖';
            }
            $voteTitle = M('Vote')->where(['id' => $_POST['vote_id']])->getField('title');
            $org_id = M('organization')->where(array('token' => $this->token))->getField('id');
            if ($org_id == 1) {
                $type = 2;
            } else {
                $type = 1;
            }
            $prize = D('VotePrize')->find($_POST['id']);
            if ($prize['type'] == 1 || $prize['type'] == 2) {
                $offerId = M('coupon_offer')->where(array('source' => 1, 'source_id' => $prize['vote_id'], 'extra' => $prize['prize_level']))->getField('id');
                if ($offerId) {
                    if ($_POST['type'] == 1) {
                        //改成优惠券
                        $CouponOfferData = [
                            'start_time' => strtotime($_POST['start_time']),
                            'end_time' => $end_time,
                            'type' => $type,
                            'type_id' => $org_id,
                            'full' => $_POST['full'],
                            'subtract' => $_POST['subtract'],
                            'fee' => 0,
                            'coupon_type' => 1,
                            'title' => '满' . $_POST['full'] . '减' . $_POST['subtract'],
                            'count' => $_POST['prize_count'],
                            'desc' => $voteTitle . $prize_name,
                            'source' => 1,
                            'source_id' => $_POST['vote_id'],
                            'extra' => $_POST['prize_level'],
                        ];
                        $offerNowId = D('CouponOffer')->updateById($offerId, $CouponOfferData);
                        if ($offerNowId === false) {
                            return show(0, '修改优惠券失败');
                        }
                    } elseif ($_POST['type'] == 2) {
                        //改成代金券
                        $CouponOfferData = [
                            'start_time' => strtotime($_POST['start_time']),
                            'end_time' => $end_time,
                            'type' => $type,
                            'type_id' => $org_id,
                            'full' => 0,
                            'subtract' => 0,
                            'fee' => $_POST['fee'],
                            'coupon_type' => 2,
                            'title' => $_POST['fee'] . '元代金券',
                            'desc' => $voteTitle . $prize_name,
                            'source' => 1,
                            'source_id' => $_POST['vote_id'],
                            'extra' => $_POST['prize_level'],
                        ];
                        $offerNowId = D('CouponOffer')->updateById($offerId, $CouponOfferData);
                        if ($offerNowId === false) {
                            return show(0, '修改代金券失败');
                        }
                    } else {
                        $offerNowId = D('CouponOffer')->delete($offerId);
                        if (!$offerNowId) {
                            return show(0, '删除优惠券失败');
                        }
                    }
                }
            } else {
                if ($_POST['type'] == 1) {
                    //添加优惠券
                    $CouponOfferData = [
                        'start_time' => strtotime($_POST['start_time']),
                        'end_time' => $end_time,
                        'type' => $type,
                        'type_id' => $org_id,
                        'full' => $_POST['full'],
                        'subtract' => $_POST['subtract'],
                        'coupon_type' => 1,
                        'title' => '满' . $_POST['full'] . '减' . $_POST['subtract'],
                        'count' => $_POST['prize_count'],
                        'desc' => $voteTitle . $prize_name,
                        'source' => 1,
                        'source_id' => $_POST['vote_id'],
                        'extra' => $_POST['prize_level'],
                    ];
                    $offerId = D('CouponOffer')->insert($CouponOfferData);
                    if (!$offerId) {
                        return show(0, '添加优惠券失败');
                    }
                } elseif ($_POST['type'] == 2) {
                    //添加代金券
                    $CouponOfferData = [
                        'start_time' => strtotime($_POST['start_time']),
                        'end_time' => $end_time,
                        'type' => $type,
                        'type_id' => $org_id,
                        'fee' => $_POST['fee'],
                        'coupon_type' => 2,
                        'title' => $_POST['fee'] . '元代金券',
                        'count' => $_POST['prize_count'],
                        'desc' => $voteTitle . $prize_name,
                        'source' => 1,
                        'source_id' => $_POST['vote_id'],
                        'extra' => $_POST['prize_level'],
                    ];
                    $offerId = D('CouponOffer')->insert($CouponOfferData);
                    if (!$offerId) {
                        return show(0, '添加代金券失败');
                    }
                }
            }
            $data = [
                'title' => $_POST['title'],
                'vote_id' => $_POST['vote_id'],
                'type' => $_POST['type'],
                'prize_level' => $_POST['prize_level'],
                'full' => $_POST['full'],
                'subtract' => $_POST['subtract'],
                'fee' => $_POST['fee'],
                'integral' => $_POST['integral'],
                'start_time' => strtotime($_POST['start_time']),
                'end_time' => $end_time,
                'create_time' => time(),
                'image' => $_POST['image'],
                'desc' => $_POST['desc'],
            ];
            if (!$_POST['image']) {
                unset($data['image']);
            }
            if ($_POST['type'] == 1) {
                $data['fee'] = 0;
                $data['integral'] = 0;
                $data['status'] = 2;
            }
            if ($_POST['type'] == 2) {
                $data['full'] = 0;
                $data['subtract'] = 0;
                $data['integral'] = 0;
                $data['status'] = 2;
            }
            if ($_POST['type'] == 3) {
                $data['full'] = 0;
                $data['subtract'] = 0;
                $data['fee'] = 0;
                $data['status'] = 2;
            }
            if ($_POST['type'] == 4) {
                $data['full'] = 0;
                $data['subtract'] = 0;
                $data['fee'] = 0;
                $data['integral'] = 0;
                $data['status'] = 1;
            }
            $result = 0;
            $votePrizeIds = M('vote_prize')->where(array('type' => $prize['type'], 'prize_level' => $prize['prize_level'],
                'vote_id' => $prize['vote_id']))->field('id')->select();
            for ($i = 0; $i < $_POST['prize_count']; $i++) {
                $result = D('VotePrize')->updateById($votePrizeIds[$i]['id'], $data);
            }
            if ($result === false) {
                return show(0, '修改失败');
            } else {
                return show(1, '修改成功');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }


    public function deletePrize()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, '参数错误');
        }
        try {
            $prize = D('VotePrize')->find($_POST['id']);
            if ($prize['type'] == 1 || $prize['type'] == 2) {
                $prizeCount = M('vote_prize')->where(array('prize_level' => $prize['prize_level']))->count();
                if ($prizeCount > 1) {
                    $offerId = M('coupon_offer')->where(array('source' => 1, 'source_id' => $prize['vote_id'], 'extra' => $prize['prize_level']))->getField('id');
                    M('coupon_offer')->where(array('id' => $offerId))->setDec('count');
                    $result = D('VotePrize')->delete($_POST['id']);
                } else {
                    M('coupon_offer')->where(array('source' => 1, 'source_id' => $prize['vote_id'], 'extra' => $prize['prize_level']))->delete();
                    $result = D('VotePrize')->delete($_POST['id']);
                }
            } else {
                $result = D('VotePrize')->delete($_POST['id']);
            }
            if ($result) {
                return show(1, '删除成功');
            } else {
                return show(0, '删除失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }


    //发布投票奖品结果
    public function showPrizeResult()
    {
        if (!$_GET['id'] || !$_GET['user_id']) {
            return show(0, 'ID不能为空');
        }
        $votePrize = D('VotePrize')->find($_GET['id']);
        $voteTitle = M('Vote')->where(['id' => $votePrize['vote_id']])->getField('title');
        if ($votePrize['prize_level'] == 1) {
            $votePrize['prizeName'] = '一等奖';
        } elseif ($votePrize['prize_level'] == 2) {
            $votePrize['prizeName'] = '二等奖';
        } elseif ($votePrize['prize_level'] == 3) {
            $votePrize['prizeName'] = '三等奖';
        } elseif ($votePrize['prize_level'] == 4) {
            $votePrize['prizeName'] = '安慰奖';
        }
        if ($votePrize['type'] == 1) {//优惠券
            $offerId = M('coupon_offer')->where(array('source' => 1, 'source_id' => $votePrize['vote_id'],
                'extra' => $votePrize['prize_level'], 'count' => array('gt', 0)))->getField('id');
            if ($offerId) {
                //发放优惠券
                $CouponData = [
                    'create_time' => time(),
                    'offer_id' => $offerId,
                    'status' => 1,
                    'user_id' => $_GET['user_id'],
                    'coupon_type' => 1,
                ];
                $couponId = D('Coupon')->insert($CouponData);
                if ($couponId) {
                    $coupon = D('Coupon')->find($couponId);
                    M('coupon_offer')->where(array('id' => $coupon['offer_id']))->setDec('count');
                    $couponRecordData = [
                        'create_time' => time(),
                        'operate' => 2,
                        'user_id' => $_GET['user_id'],
                        'coupon_id' => $couponId,
                        'type' => 3,
                        'type_id' => $votePrize['vote_id'],
                    ];
                    D('CouponRecord')->insert($couponRecordData);
                    $votePrizeRecordData = [
                        'prize_id' => $votePrize['id'],
                        'user_id' => $_GET['user_id'],
                        'type' => $votePrize['type'],
                        'type_id' => $offerId,
                        'create_time' => time(),
                    ];
                    $votePrizeRecordId = D('VotePrizeRecord')->insert($votePrizeRecordData);
                    if (!$votePrizeRecordId) {
                        return show(0, '发送通知失败');
                    }
                } else {
                    return show(0, '发送优惠卷失败');
                }
            } else {
                return show(0, '优惠券数量不足');
            }
        } elseif ($votePrize['type'] == 2) {//代金券
            $offerId = M('coupon_offer')->where(array('source' => 1, 'source_id' => $votePrize['vote_id'],
                'extra' => $votePrize['prize_level'], 'count' => array('gt', 0)))->getField('id');
            if ($offerId) {
                //发放代金券
                $CouponData = [
                    'create_time' => time(),
                    'offer_id' => $offerId,
                    'status' => 1,
                    'user_id' => $_GET['user_id'],
                    'coupon_type' => 2,
                ];
                $couponId = D('Coupon')->insert($CouponData);
                if ($couponId) {
                    $coupon = D('Coupon')->find($couponId);
                    M('coupon_offer')->where(array('id' => $coupon['offer_id']))->setDec('count');
                    $couponRecordData = [
                        'create_time' => time(),
                        'operate' => 2,
                        'user_id' => $_GET['user_id'],
                        'coupon_id' => $couponId,
                        'type' => 3,
                        'type_id' => $votePrize['vote_id'],
                    ];
                    D('CouponRecord')->insert($couponRecordData);
                    $votePrizeRecordData = [
                        'prize_id' => $votePrize['id'],
                        'user_id' => $_GET['user_id'],
                        'type' => $votePrize['type'],
                        'type_id' => $offerId,
                        'create_time' => time(),
                    ];
                    $votePrizeRecordId = D('VotePrizeRecord')->insert($votePrizeRecordData);
                    if (!$votePrizeRecordId) {
                        return show(0, '发送通知失败');
                    }
                } else {
                    return show(0, '发放代金券失败');
                }
            } else {
                return show(0, '代金券数量不足');
            }
        } elseif ($votePrize['type'] == 3) {//积分
            //添加积分
            $userId = M('user')->where(array('id' => $_GET['user_id']))->setInc('integral', $votePrize['integral']);
            if ($userId) {
                $integralRecordData = [
                    'user_id' => $_GET['user_id'],
                    'token' => $this->token,
                    'integral' => $votePrize['integral'],
                    'create_time' => time(),
                    'status' => 1,
                    'type' => 2,
                    'integral_type' => 11,
                    'desc' => $voteTitle . '投票活动赠送'
                ];
                $integralRecordId = D('IntegralRecord')->insert($integralRecordData);
                if ($integralRecordId) {
                    $votePrizeRecordData = [
                        'prize_id' => $votePrize['id'],
                        'user_id' => $_GET['user_id'],
                        'type' => $votePrize['type'],
                        'type_id' => $integralRecordId,
                        'create_time' => time(),
                    ];
                    $votePrizeRecordId = D('VotePrizeRecord')->insert($votePrizeRecordData);
                    if (!$votePrizeRecordId) {
                        return show(0, '发送通知失败');
                    }
                } else {
                    return show(0, '积分记录添加失败');
                }
            } else {
                return show(0, '发放积分失败');
            }
        } elseif ($votePrize['type'] == 4) {//实物
            $votePrizeRecordData = [
                'prize_id' => $votePrize['id'],
                'user_id' => $_GET['user_id'],
                'type' => $votePrize['type'],
                'create_time' => time(),
                'status' => $votePrize['status']
            ];
            $votePrizeRecordId = D('VotePrizeRecord')->insert($votePrizeRecordData);
            if (!$votePrizeRecordId) {
                return show(0, '发送通知失败');
            }
        }
        //发送模板通知
        $nickname = M('user')->where(['id' => $_GET['user_id']])->getField('username');
        $userOpenid = M('user')->where(['id' => $_GET['user_id']])->getField('open_id');
        //当类型为实物并且需要填写收货信息时修改跳转地址和提示信息
        if ($votePrize['type'] == 4 && $votePrize['status'] == 1) {
            $first = $nickname . '，恭喜你，你参加的作品获得了' . $votePrize['prizeName'] . '，赶快去领奖吧';
            $remark = '点击领奖';
            $url = "http://{$_SERVER['HTTP_HOST']}/index.php/Company/votePrizeIndex?prize_id=" . $votePrize['id'] . "&token=" . $this->token;
        } else {
            $first = $nickname . '，恭喜你，你参加的作品获得了' . $votePrize['prizeName'] . '，快去查看自己的奖品吧';
            $remark = '点击查看';
            $url = "http://{$_SERVER['HTTP_HOST']}/index.php/Vote/userBillboard.html?vote_id=" . $votePrize['vote_id'] . "&token=" . $this->token;
        }
        $keyword1 = $voteTitle;
        $keyword2 = $votePrize['prizeName'];
        $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__REMARK__');
        $infoFormat = array($userOpenid, $url, $first, $keyword1, $keyword2, $remark);
        $wxuser = get_wxuser("g232238gc959");
        $result = execute_public_template('CONTRIBUTION', $templeFormat, $infoFormat, $wxuser);
        if ($result['errcode'] == 0) {
            D('VotePrize')->updateById($_GET['id'], ['template_status' => 2]);
        } else {
            D('VotePrize')->updateById($_GET['id'], ['template_status' => 3]);
            return show(0, '发送失败');
        }
        return show(1, '发送成功');
    }


}