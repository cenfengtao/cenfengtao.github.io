<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

require_once __DIR__ . '/../../../ThinkPHP/Library/Org/Util/JSDDK.class.php';

class ArticleController extends BaseController
{
    public function index()
    {
        $this->display();
    }

    public function ajaxIndex()
    {
        $cateId = $_GET['cate_id'] ? $_GET['cate_id'] : 0;
        //去除视频分类 id=10 去除专家视频分类 id=13
        $cateList = M('article_cate')->order('sort desc')->where(array('id' => array(array('NEQ', 10), array('NEQ', 13), 'AND')))->select();
        if ($cateId == 0) {
            $articleList = M('Article')->field('id,image,title,cate_id,create_time,token')
                ->where(array('status' => 2, 'cate_id' => array(array('NEQ', 10), array('NEQ', 13), 'AND')))->order('create_time desc,sort desc')->limit(0, 10)->select();
            foreach ($articleList as $k => $v) {
                $articleList[$k]['cate_title'] = D('ArticleCate')->getTitleById($v['cate_id']);
                $articleList[$k]['time'] = date("Y-m-d", $v['create_time']);
                $articleList[$k]['count'] = D('Comment')->getCountByArtId($v['id']);
                $articleList[$k]['collect'] = D('Collect')->getPageByType($v['id'], 5);
            }
        } else {
            $articleList = M('Article')->field('id,image,title,cate_id,create_time,token')
                ->where(array('status' => 2, 'cate_id' => $cateId))->order('create_time desc,sort desc')->limit(0, 10)->select();
            foreach ($articleList as $k => $v) {
                $articleList[$k]['cate_title'] = D('ArticleCate')->getTitleById($v['cate_id']);
                $articleList[$k]['time'] = date("Y-m-d", $v['create_time']);
                $articleList[$k]['count'] = D('Comment')->getCountByArtId($v['id']);
                $articleList[$k]['collect'] = D('Collect')->getPageByType($v['id'], 5);
            }
        }
        return show(1, '', ['cateList' => $cateList, 'articleList' => $articleList]);
    }

    public function videoList()
    {
        $this->title = "优培视频";
        $articleList = M('Article')->field('id,image,title,cate_id,create_time,token')
            ->where(array('status' => 2, 'cate_id' => array('EQ', 10)))->order('create_time desc,sort desc')->limit(0, 10)->select();
        foreach ($articleList as $k => $v) {
            $articleList[$k]['cate_title'] = D('ArticleCate')->getTitleById($v['cate_id']);
            $articleList[$k]['time'] = date("Y-m-d", $v['create_time']);
            $articleList[$k]['count'] = D('Comment')->getCountByArtId($v['id']);
            $articleList[$k]['collect'] = D('Collect')->getPageByType($v['id'], 5);
        }
        $this->assign('articleList', $articleList);
        $this->display();
    }


    public function professionVideoList()
    {
        $this->title = "专家微课";
        $articleList = M('Article')->field('id,image,title,cate_id,create_time,token')
            ->where(array('status' => 2, 'cate_id' => array('EQ', 13)))->order('create_time desc,sort desc')->limit(0, 10)->select();
        foreach ($articleList as $k => $v) {
            $articleList[$k]['cate_title'] = "专家";
            $articleList[$k]['time'] = date("Y-m-d", $v['create_time']);
            $articleList[$k]['count'] = D('Comment')->getCountByArtId($v['id']);
            $articleList[$k]['collect'] = D('Collect')->getPageByType($v['id'], 5);
        }
        $this->assign('articleList', $articleList);
        $this->display();
    }


    public function getArticle()
    {
        if (!$_GET['art_id'] || !is_numeric($_GET['art_id'])) {
            $this->error('文章不存在');
        }
        $article = D('Article')->find($_GET['art_id']);
        //增加浏览记录
        $this->addFootprint(2, $article['id']);
        $article['fingerCount'] = D('FingerRecord')->getCountByArtId($_GET['art_id']);
        $article['author'] = D('Organization')->getOrgnameByToken($article['token']);
        $article['isFinger'] = D('FingerRecord')->isFingerByArtId($this->user['id'], $_GET['art_id']);
        $article['comment_time'] = M('comment')->where(['article_id' => $_GET['art_id'], 'status' => 1])->order('create_time desc')->getField('create_time');
        $comments = D('Comment')->getCommentsByArtId(array("father_id" => 0, "article_id" => $_GET['art_id'], 'status' => 1));
        foreach ($comments as $k => $v) {
            $comments[$k]['username'] = D('User')->getNameById($v['user_id']);
            $comments[$k]['headimgurl'] = D('User')->getHeadById($v['user_id']);
            $comments[$k]['finger_count'] = D('FingerRecord')->getCountByCommentId($v['id']);
            $comments[$k]['is_finger'] = D('FingerRecord')->isFingerByCommentId($v['user_id'], $v['id']);
            $comments[$k]['child'] = D('Comment')->getCommentsByArtId(array('type_id' => $v['id'], 'status' => 1, 'article_id' => $v['article_id']));
            foreach ($comments[$k]['child'] as $ke => $va) {
                //回复
                if ($va['is_gm'] == 1) {
                    $comments[$k]['child'][$ke]['headImg'] = D('user')->getHeadById($va['user_id']);
                } elseif ($va['is_gm'] == 2) {
                    //待机构管理员完善之后需修改
                    //客服头像
                    $picture = M('organization')->field('picture')->where(array('token' => $this->token))->find();
                    $comments[$k]['child'][$ke]['headImg'] = $picture['picture'];
                }
                //被回复
                $userId = M('Comment')->where(array('id' => $va['father_id']))->find();
                if ($userId['is_gm'] == 2) {
                    $pictures = M('organization')->field('picture')->where(array('token' => $userId['token']))->find();
                    $comments[$k]['child'][$ke]['headImgs'] = $pictures['picture'];
                } else if ($userId['is_gm'] == 1) {
                    $comments[$k]['child'][$ke]['headImgs'] = D('user')->getHeadById($userId['user_id']);
                }
            }
        }
        $readRecord = D('ReadRecord')->getRecordByUserId($this->user['id'], 1, $_GET['art_id']);
        if (!$readRecord || empty($readRecord)) {
            $recordData = [
                'user_id' => $this->user['id'],
                'create_time' => time(),
                'type' => 1,
                'type_id' => $_GET['art_id'],
                'from_user_id' => $_GET['share_user_id'] ?: '',
            ];
            D('ReadRecord')->insert($recordData);
            //添加该文章的阅读数
            M('Article')->where("id={$article['id']}")->setInc('read_count');
            $config = M('config')->where("token='{$this->token}'")->find();
            $integral = $this->add_user_integral($this->user['id'], $config['task_read_integral'], $config['max_integral']);
            //判断是否完成每日任务
            $isReadByTask = D('IntegralRecord')->isReadByTask($this->user['id']);
            $todayReadCount = D('ReadRecord')->getTodayCount($this->user['id']);
            if (empty($isReadByTask) && $todayReadCount >= 3) {
                $integralDataByTask = [
                    'user_id' => $this->user['id'],
                    'create_time' => time(),
                    'status' => 1,
                    'type' => 2,
                    'integral_type' => 6,
                    'desc' => '每日阅读任务',
                    'integral' => $integral['integral'],
                    'token' => $this->token,
                ];
                D('IntegralRecord')->insert($integralDataByTask);
                M('User')->where("id={$this->user['id']}")->setInc('integral', $integral['integral']);
                //发送模板信息
                $this->sendTemplate($integral['integral'], $this->user['id'], $this->user['open_id'], '每日阅读任务');
            }
        }
        //获取ticket
        $wxuser = get_wxuser($this->token);
        $jssdk = new \JSSDK($wxuser['appid'], $wxuser['appsecret']);
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('article', $article)->assign('comments', $comments)->assign('signPackage', $signPackage);
        //分享内容图片和链接地址
        if (strpos($article['image'], 'http') === false) {
            $shareImg = 'http://' . $_SERVER["HTTP_HOST"] . $article['image'];
        } else {
            $shareImg = $article['image'];
        }
        $shareUrl = 'http://' . $_SERVER["HTTP_HOST"] . U('Article/getArticle', array('share_user_id' =>
                $this->user['id'], 'art_id' => $_GET['art_id'], 'token' => $this->token));
        //是否收藏
        $isCollect = D('Collect')->isCollectById($this->user['id'], $_GET['art_id'], 5);
        $this->assign('share_img', $shareImg)->assign('share_url', $shareUrl)->assign('isCollect', $isCollect);
        $this->display();
    }

    //点赞
    public function finger()
    {
        $status = $_GET['status'];
        if ($status == 1) { //评论点赞
            $isFinger = D('FingerRecord')->isFingerByCommentId($this->user['id'], $_GET['id']);
            if ($isFinger) { //取消点赞
                $id = D('FingerRecord')->deleteByCommentId($this->user['id'], $_GET['id']);
                if ($id) {
                    $fingerCount = D('FingerRecord')->getCountByCommentId($_GET['id']);
                    $this->ajaxReturn(array('status' => '2', 'finger' => $fingerCount));
                }
            } else { //添加点赞
                $fingerData = [
                    'user_id' => $this->user['id'],
                    'type' => 2,
                    'type_id' => $_GET['id'],
                    'create_time' => time(),
                ];
                $id = D('FingerRecord')->insert($fingerData);
                if ($id) {
                    $fingerCount = D('FingerRecord')->getCountByCommentId($_GET['id']);
                    $this->ajaxReturn(array('status' => '1', 'finger' => $fingerCount));
                }
            }
        }
        if ($status == 2) { //文章点赞
            $isFinger = D('FingerRecord')->isFingerByArtId($this->user['id'], $_GET['id']);
            if (!$isFinger) {
                $fingerData = [
                    'user_id' => $this->user['id'],
                    'type' => 1,
                    'type_id' => $_GET['id'],
                    'create_time' => time(),
                ];
                $id = D('FingerRecord')->insert($fingerData);
                if ($id) {
                    $fingerCount = D('FingerRecord')->getCountByArtId($_GET['id']);
                    $this->ajaxReturn(array('status' => '1', 'finger' => $fingerCount));
                }
            }
        }
    }

    //评论
    public function comment()
    {
        if (!$_POST['article_id'] || !is_numeric($_POST['article_id'])) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '文章ID参数错误'));
        }
        if (!$_POST['content'] || empty(trim($_POST['content']))) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '评论内容不能为空'));
        }
        try {
            $insertData = [
                'user_id' => $this->user['id'],
                'article_id' => $_POST['article_id'],
                'content' => $_POST['content'],
                'status' => 1,
                'create_time' => time(),
                'token' => $this->token,
            ];
            $id = D('Comment')->insert($insertData);
            if ($id) {
                //判断是否已赠送每日任务积分
                $isCommentByTask = D('IntegralRecord')->isCommentByTask($this->user['id']);
                $config = M('config')->where("token='{$this->token}'")->find();
                $integral = $this->add_user_integral($this->user['id'], $config['task_comment_integral'], $config['max_integral']);
                if (empty($isCommentByTask)) {
                    $integralData = [
                        'user_id' => $this->user['id'],
                        'integral' => $integral['integral'],
                        'create_time' => time(),
                        'status' => 1,
                        'type' => 2,
                        'integral_type' => 7,
                        'desc' => '每日评论任务',
                        'token' => $this->token,
                    ];
                    D("IntegralRecord")->insert($integralData);
                    M('User')->where("id={$this->user['id']}")->setInc('integral', $integral['integral']);
                    //发送模板消息
                    $this->sendTemplate($integral['integral'], $this->user['id'], $this->user['open_id'], '每日评论任务');
                }
                $comment = D('Comment')->find($id);
                $comment['username'] = D('User')->getNameById($comment['user_id']);
                $comment['headimgurl'] = D('User')->getHeadById($comment['user_id']);
                $comment['finger_count'] = D('FingerRecord')->getCountByCommentId($comment['id']);
                $comment['create_time'] = date("m-d H:i", $comment['create_time']);
                $data[] = $comment;
                $this->ajaxReturn(array('status' => 1, 'msg' => '评论成功', 'data' => $data));
            } else {
                $this->ajaxReturn(array('status' => 0, 'msg' => '评论失败'));
            }
        } catch (Exception $e) {
            $this->ajaxReturn(array('status' => 0, 'msg' => $e->getMessage()));
        }
    }

    //子评论
    public function childComment()
    {
        if ($_POST) {
            if (!$_POST['article_id'] || empty($_POST['article_id'])) {
                $this->ajaxReturn(array('status' => 0, 'msg' => 'ID参数错误'));
            }
            if (!$_POST['father_id'] || empty($_POST['father_id'])) {
                $this->ajaxReturn(array('status' => 0, 'msg' => 'FATHER_ID参数错误'));
            }
            if (!$_POST['content'] || empty($_POST['content'])) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '咨询内容不能为空'));
            }
            $data = [
                'user_id' => $this->user['id'],
                'father_id' => $_POST['father_id'],
                'article_id' => $_POST['article_id'],
                'content' => $_POST['content'],
                'token' => $this->token,
                'type_id' => $_POST['type_id'],
                'status' => 1,
                'create_time' => time()
            ];
            $id = D('Comment')->insert($data);
            //评论人头像
            $reply = D('Comment')->find($id);
            $headImg = M('user')->where("id={$reply['user_id']}")->field('headimgurl')->find();
            $reply['headImg'] = $headImg['headimgurl'];
            //被评论人头像
            $replys = D('Comment')->find($_POST['father_id']);
            $headImgs = M('user')->where("id={$replys['user_id']}")->field('headimgurl')->find();
            $reply['headImgs'] = $headImgs['headimgurl'];
            if (!$reply || empty($reply)) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '评论失败'));
            }
            $this->ajaxReturn(array('status' => 1, 'msg' => '评论成功', 'data' => $reply));
        }
    }

    public function sendTemplate($integral, $userId, $userOpenid, $type)
    {
        if ($integral > 0) {
            //发送积分信息
            $nowIntegral = D('User')->getIntegralById($userId);
            $first = '【优培圈】温馨提醒您的积分有变动';
            $keyword1 = '+' . $integral . '分';
            $keyword2 = $type;
            $keyword3 = date("Y-m-d H:i:s", time());
            $keyword4 = $nowIntegral . '分';
            $remark = '请点击“详情”查看具体内容';
            $url = "http://{$_SERVER['HTTP_HOST']}/index.php/MemberIntegral/integrallist?token=" . $this->token;
            $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__KEYWORD4__', '__REMARK__');
            $infoFormat = array($userOpenid, $url, $first, $keyword1, $keyword2, $keyword3, $keyword4, $remark);
            $wxuser = get_wxuser($this->token);
            $executeResult = execute_public_template('INTEGRAL_CHANGE', $templeFormat, $infoFormat, $wxuser);
            return $executeResult;
        }
    }

    //分享记录
    public function shareRecord()
    {
        $insertData = [
            'create_time' => time(),
            'type' => 1,
            'type_id' => $_POST['art_id'],
            'desc' => '分享文章',
            'user_id' => $this->user['id'],
        ];
        D('ShareRecord')->insert($insertData);
        //判断今天是否已添加积分
        $isShare = D('IntegralRecord')->isShareArticle($this->user['id']);
        if (!$isShare) {
            $config = M('config')->where("token='{$this->token}'")->find();
            $integral = $this->add_user_integral($this->user['id'], $config['task_share_integral'], $config['max_integral']);
            //添加积分记录
            $recordData = [
                'user_id' => $this->user['id'],
                'integral' => $integral['integral'],
                'create_time' => time(),
                'status' => 1,
                'type' => 2,
                'integral_type' => 5,
                'desc' => '每日转发任务赠送积分',
                'token' => $this->token,
            ];
            D('IntegralRecord')->insert($recordData);
            D('User')->updateById($this->user['id'], array('integral' => $this->user['integral'] + $integral['integral']));

            $this->sendTemplate($integral['integral'], $this->user['id'], $this->user['open_id'], '每日转发任务');
            return show(1, '分享成功，完成每日转发任务');
        }
        return show(0, '分享成功');
    }

    //滑动加载
    public function loadingProduct()
    {
        $npage = (int)I("npage");
        $cateId = $_POST['cate_id'] ? $_POST['cate_id'] : 0;
        if ($cateId == 0) {
            $list = D('Article')->getListByPage($npage);
        } else {
            $list = D('Article')->getListByPage($npage, $cateId);
        }
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date('Y-m-d', $v['create_time']);
            $list[$k]['cate_title'] = D('ArticleCate')->getTitleById($v['cate_id']);
            $list[$k]['count'] = D('Comment')->getCountByArtId($v['id']);
            $list[$k]['collect'] = D('Collect')->getPageByType($v['id'], 5);
        }
        if (!$list || empty($list)) {
            $this->ajaxReturn(array('status' => 0, 'msg' => '没有数据'));
        }
        $this->ajaxReturn(array('status' => 1, 'msg' => '获取成功', 'data' => $list));
    }
}