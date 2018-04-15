<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

require_once __DIR__ . '/../../../ThinkPHP/Library/Org/Util/JSDDK.class.php';

class OrganizationController extends BaseController
{
    public function index()
    {
        $this->title = "机构列表";
        $orgList = D('Organization')->getOrgList('id,org_name,org_star,env_star,quality_star,picture,is_show');
        $this->assign('orgList', $orgList);
        $this->display();
    }

    public function getOrganizationIntroduce()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            $this->error('ID参数错误');
        }
        $organization = D('Organization')->find($_GET['id']);
        $this->assign('organization', $organization);
        $this->display();
    }

    public function demo()
    {
        $this->display();
    }

    public function demoList()
    {
        $this->display();
    }

    public function association()
    {
        $this->display();
    }

    public function activityList()
    {
        $this->display();
    }

    public function home()
    {
        $this->title = "优培圈";
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            $this->error('ID参数错误');
        }
        $organization = M('Organization')->field('description', true)->where("id={$_GET['id']}")->find();
        //判断是否显示
        $this->assign('isShow', $organization['is_show']);
        //热门课程
        $classList = D('Product')->getClassByToken($organization['token'], 0, 'id,pic_url,org_id,title,rush_price,price,class_id');
        foreach ($classList as $k => $v) {
            $classList[$k]['class_title'] = D('ProductClass')->getTitleById($v['class_id']);
            $classList[$k]['price'] = D('Product')->getFirstKeyPrice($v['id']);
        }
        //文章福利
        $articleList = D('Article')->getArticleByToken($organization['token'], 'id,title,image,cate_id,create_time', 3);
        foreach ($articleList as $k => $v) {
            $articleList[$k]['cate_title'] = D('ArticleCate')->getTitleById($v['cate_id']);
            $articleList[$k]['time'] = date("Y-m-d", $v['create_time']);
            $articleList[$k]['comment_count'] = D('Comment')->getCountByArtId($v['id']);
            $articleList[$k]['collect'] = D('Collect')->getPageByType($v['id'], 5);
        }
        //机构活动
        $activityList = D('OrganizationActivity')->getNowActivityList($organization['token'], 0, 'id,create_time,
        max_people,start_time,end_time,activity_time,title,image,price,tag');
        //轮播信息
        $bannerList = D('OrganizationBanner')->getListByToken($organization['token']);
        $banners = [];
        foreach ($bannerList as $k => $v) {
            if ($v['type'] == 1) { //文章
                $articleImage = M('Article')->where("id={$v['type_id']}")->getField('image');
                $banners[$k] = [
                    'id' => $v['id'],
                    'type' => $v['type'],
                    'type_id' => $v['type_id'],
                    'image' => $articleImage,
                ];
            }
            if ($v['type'] == 2) { //课程
                $productImage = M('Product')->where("id={$v['type_id']}")->getField('pic_url');
                $banners[$k] = [
                    'id' => $v['id'],
                    'type' => $v['type'],
                    'type_id' => $v['type_id'],
                    'image' => $productImage,
                ];
            }
        }
        $this->assign('classList', $classList)->assign('articleList', $articleList)->assign('banners', $banners)
            ->assign('organization', $organization)->assign('activityList', $activityList);
        //获取关注信息
        $followed = M('Collect')->where(array('user_id' => $this->user['id'], 'type' => 1, 'type_id' => $_GET['id']))->find();
        if ($followed) {
            $isFollowed = 1;
        } else {
            $isFollowed = 0;
        }
        //文章推荐
        $newList = M('Article')->where(array('token' => 'g232238gc959', 'status' => 2))->order('create_time desc')->limit(3)->select();
        foreach ($newList as $k => $v) {
            $newList[$k]['org_pic'] = M('organization')->where("token='g232238gc959'")->getField('picture');
            $newList[$k]['title_name'] = mb_substr($v['title'], 0, 12, 'utf-8');
        }
        //机构活动推荐
        $actList = D('OrganizationActivity')->getNowActivityList($organization['token'], 0, 'id,create_time,
        max_people,start_time,end_time,activity_time,title,image,price,tag');
        foreach ($actList as $k => $v) {
            $actList[$k]['org_pic'] = M('organization')->where(['token' => $this->token])->getField('picture');
            $actList[$k]['title_name'] = mb_substr($v['title'], 0, 12, 'utf-8');
        }
        //可领取的优惠券
        $couponWhere = [
            'start_time' => ['lt', time()],
            'end_time' => ['gt', time()],
            'type' => 1,
            'type_id' => $organization['id'],
            'coupon_type' => 1,
            'count' => ['gt', 0],
            'source' => ['EXP', 'is null'],
        ];
        $couponOfferList = M('coupon_offer')->where($couponWhere)->select();
        $this->assign("isFollowed", $isFollowed)->assign("newList", $newList)->assign("actList", $actList)
            ->assign('offerList', $couponOfferList);
        $this->display();
    }

    public function activityDetail()
    {
        $this->title = '活动详细';
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            $this->error('ID参数错误');
        }
        $activity = D('OrganizationActivity')->find($_GET['id']);
        $organization = D('Organization')->findByToken($activity['token']);
        $tags = explode(' ', $activity['tag']);
        $activity['tagA'] = $tags[0] ?: '';
        $activity['tagB'] = $tags[1] ?: '';
        $activity['tagC'] = $tags[2] ?: '';
        //目前报名人数
        $nowCount = M('order')->where(array('status' => array(array('eq', 1), array('eq', 4), 'or'),
            'activity_id' => $activity['id']))->count();
        //咨询列表
        $activityComment = D('ProductComment')->getCommentByFatherActivityId(0, 0, $_GET['id']);
        foreach ($activityComment as $k => $v) {
            $activityComment[$k]['child'] = D('ProductComment')->getCommentByFatherActivityId($v['id'],
                $v['user_id'], $v['activity_id']);
            $activityComment[$k]['headImg'] = D('user')->getHeadById($v['user_id']);
        }
        $headImg = [];
        $userId = D('Order')->getGroupByActivityId($_GET['id']);
        foreach ($userId as $k => $v) {
            $headImg[$k]['headImg'] = D('User')->getHeadById($v['user_id']);
        }
        //是否收藏
        $isCollect = D('Collect')->isCollectById($this->user['id'], $_GET['id'], 6);
        $this->assign('activity', $activity)->assign('organization', $organization)
            ->assign('nowCount', $nowCount)->assign('activityComment', $activityComment)->assign('headImg', $headImg)
            ->assign('isCollect', $isCollect);
        $this->display();
    }

    //添加咨询
    public function addComment()
    {
        if (!$_POST['activity_id'] || !is_numeric($_POST['activity_id'])) {
            $this->error('活动ID参数错误');
        }
        if (!$_POST['content'] || empty($_POST['content'])) {
            $this->error('评论内容不能为空');
        }
        $token = M('OrganizationActivity')->where("id={$_POST['activity_id']}")->getField('token');
        $insertData = [
            'user_id' => $this->user['id'],
            'create_time' => time(),
            'type' => 1,
            'status' => 1,
            'content' => $_POST['content'],
            'token' => $token,
            'activity_id' => $_POST['activity_id'],
        ];
        $id = D('ProductComment')->insert($insertData);
        if ($id) {
            return show(1, '咨询成功');
        } else {
            return show(0, '咨询失败');
        }
    }

    //选择购买参数
    public function chooseOrderInfo()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            $this->error('ID参数不能为空');
        }
        $activity = D('OrganizationActivity')->find($_GET['id']);
        //还可下单人数
        $nowCount = M('order')->where(array('status' => array(array('eq', 1), array('eq', 4), 'or'),
            'activity_id' => $activity['id']))->count();
        $maxPeople = $activity['max_people'] - $nowCount;
        $this->assign('activity', $activity)->assign('maxPeople', $maxPeople);
        $this->display();
    }

    public function confirmationInfo()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        if (!$_GET['amount'] || $_GET['amount'] < 1) {
            return show(0, '数量参数错误');
        }
        if (!$_GET['name']) {
            return show(0, '联系人不能为空');
        }
        if (!$_GET['mobile']) {
            return show(0, '联系电话不能为空');
        }
        $this->display();
    }


    public function ajaxConfirmationInfo()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        if (!$_GET['amount'] || $_GET['amount'] < 1) {
            return show(0, '数量参数错误');
        }
        $activity = M('organization_activity')->where(['id' => $_GET['id']])->field('description,cost', true)->find();
        if ($_GET['amount'] > $activity['max_people']) {
            return show(0, '数量超出库存范围，请返回上一页刷新之后再选择');
        }
        $org = D('Organization')->findByToken($activity['token']);
        $price = $activity['price'];
        $couponByUser = M('coupon')->where(['status' => 1, 'user_id' => $this->user['id']])->select();
        $couponList = [];
        $totalPrice = $price * $_GET['amount'];
        foreach ($couponByUser as $key => $val) {
            //判断有否过期
            $offer = M('coupon_offer')->where(['id' => $val['offer_id']])->find();
            if ($offer['end_time'] <= time()) {
                M('coupon')->where(['id' => $val['id']])->save(['status' => 4]);
                continue;
            }
            //判断该商品是否可用
            if ($offer['type'] == 1 && $offer['type_id'] != $org['id']) {
                continue;
            }
            //判断商品总额，是否可用
            if ($offer['coupon_type'] == 1 && $offer['full'] > $totalPrice) {
                continue;
            }
            $couponList[$key] = [
                'full' => $offer['full'],
                'subtract' => $offer['subtract'],
                'start_time' => date("Y.m.d", $offer['start_time']),
                'end_time' => date("Y.m.d", $offer['end_time']),
                'fee' => $offer['fee'],
                'coupon_type' => $offer['coupon_type'],
                'id' => $val['id']
            ];
        }
        //优惠券排序
        $flag = [];
        foreach ($couponList as $v) {
            $flag[] = $v['coupon_type'];
        }
        array_multisort($flag, SORT_DESC, $couponList);
        //现有积分
        $nowIntegral = M('user')->where("id={$this->user['id']}")->getField('integral');
        return show(1, '获取成功', ['couponList' => $couponList, 'totalPrice' => $totalPrice, 'nowIntegral' => $nowIntegral
            , 'title' => $activity['title'], 'image' => $activity['image']]);
    }


    public function checkOrderInformation()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            $this->error('获取不到该活动信息');
        }
        try {
            $product = D('OrganizationActivity')->find($_GET['id']);
            $integral = M('user')->where('id=' . $this->user['id'])->getField('integral');
            $this->assign('product', $product)->assign('integral', $integral);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }


    public function loadingOrganization()
    {
        if (!$_POST['id'] || !is_numeric($_POST['id'])) {
            $this->error('ID参数错误');
        }
        $organization = D('Organization')->find($_POST['id']);
        $page = (int)I('page');
        if ($_POST['cate_id'] == 1) {
            //热门课程
            $list = D('Product')->getClassByToken($organization['token'], $page,'id,pic_url,org_id,title,rush_price,price,class_id');
            if (!$list || empty($list)) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '没有数据'));
            }
            foreach ($list as $k => $v) {
                $list[$k]['class_title'] = D('ProductClass')->getTitleById($v['class_id']);
                $list[$k]['province'] = $organization['province'];
                $list[$k]['city'] = $organization['city'];
                $list[$k]['area'] = $organization['area'];
                $list[$k]['address'] = $organization['address'];
                //判断抢购价
                $list[$k]['price'] = D('Product')->getFirstKeyPrice($v['id']);
            }

            $this->ajaxReturn(array('status' => 2, 'msg' => '获取到热门课程数据', 'data' => $list));
        } else if ($_POST['cate_id'] == 2) {
            //文章福利
            $list = D('Article')->getArticleByTokens($organization['token'], $page);
            if (!$list || empty($list)) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '没有数据'));
            }
            foreach ($list as $k => $v) {
                $list[$k]['cate_title'] = D('ArticleCate')->getTitleById($v['cate_id']);
                $list[$k]['time'] = date("Y-m-d", $v['create_time']);
                $list[$k]['comment_count'] = D('Comment')->getCountByArtId($v['id']);
                $list[$k]['collect'] = D('Collect')->getPageByType($v['id'], 5);
            }
            $this->ajaxReturn(array('status' => 3, 'msg' => '获取到文章福利数据', 'data' => $list));
        } else if ($_POST['cate_id'] == 3) {
            //机构活动
            $list = D('OrganizationActivity')->getNowActivityList($organization['token'], $page);
            if (!$list || empty($list)) {
                $this->ajaxReturn(array('status' => 1, 'msg' => '没有数据'));
            }
            foreach ($list as $k => $v) {
                $list[$k]['time'] = date('Y-m-d', $v['activity_time']);
            }

            $this->ajaxReturn(array('status' => 4, 'msg' => '获取到机构活动数据', 'data' => $list));
        }
    }

    public function followed()
    {
        if ($_POST) {
            if (!$_POST['id'] || empty($_POST['id'])) {
                $this->ajaxReturn(array('status' => 0, 'message' => 'ID参数错误'));
            }
            $isFollowed = M('Collect')->where(array('user_id' => $this->user['id'], 'type' => 1, 'type_id' => $_POST['id']))->find();
            if ($isFollowed) {
                $del = D('Collect')->delete($isFollowed['id']);
                if ($del) {
                    $this->ajaxReturn(array('status' => 2, 'message' => '取消关注成功'));
                } else {
                    $this->ajaxReturn(array('status' => 0, 'message' => '取消关注失败'));
                }
            } else {
                $data = [
                    'user_id' => $this->user['id'],
                    'create_time' => time(),
                    'type' => 1,
                    'type_id' => $_POST['id']
                ];
                $res = D('Collect')->insert($data);
                if ($res) {
                    $this->ajaxReturn(array('status' => 1, 'message' => '关注成功'));
                }
                $this->ajaxReturn(array('status' => 0, 'message' => '关注失败'));
            }
        }
    }

    //机构全景
    public function fullShot()
    {
        $this->display();
    }

    public function ajaxFullShot()
    {
        if (!$_GET['id']) {
            return show(0, 'ID不能为空');
        }
        $class = M('full_shot_class')->where('org_id=' . $_GET['id'])->order('sort asc')->select();
        if (!$class) {
            return show(0, '此机构暂无添加任何图片');
        }
        if (!$_GET['class_id']) {
            $classContent = M('full_shot')->where(array('org_id' => $_GET['id'], 'class_id' => $class[0]['id']))->order('sort asc')->select();
        } else {
            $classContent = M('full_shot')->where(array('org_id' => $_GET['id'], 'class_id' => $_GET['class_id']))->order('sort asc')->select();
        }
        if (!$classContent) {
            return show(0, '暂无该分类相关图片');
        }
        return show(1, '', ['class' => $class, 'classContent' => $classContent]);
    }

    public function getOfferList()
    {
        if (!$_GET['id']) {
            return show(0, '机构参数错误');
        }
        //可领取的优惠券
        $couponWhere = [
            'start_time' => ['lt', time()],
            'end_time' => ['gt', time()],
            'type' => 1,
            'type_id' => $_GET['id'],
            'coupon_type' => 1,
            'count' => ['gt', 0],
            'source' => ['EXP', 'is null'],
        ];
        $couponOfferList = M('coupon_offer')->where($couponWhere)->select();
        foreach ($couponOfferList as $key => $val) {
            //判断用户是否领取过优惠券
            $isGetCoupon = M('coupon')->where(['offer_id' => $val['id'], 'user_id' => $this->user['id']])->getField('id');
            $couponOfferList[$key]['is_get'] = $isGetCoupon ? 1 : 2;
            $couponOfferList[$key]['start_time'] = date("Y.m.d", $val['start_time']);
            $couponOfferList[$key]['end_time'] = date("Y.m.d", $val['end_time']);
        }
        if (empty($couponOfferList)) {
            return show(0, '没有课程券');
        }
        return show(1, '获取成功', ['offerList' => $couponOfferList]);
    }

    //领取优惠券
    public function getCoupon()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        //判断有否已领取
        $where = [
            'offer_id' => $_GET['id'],
            'user_id' => $this->user['id']
        ];
        $isGetCoupon = M('coupon')->where($where)->getField('id');
        if (!empty($isGetCoupon)) {
            return show(0, '你已经领取过了喔');
        }
        //判断库存
        $couponCount = M('coupon_offer')->where(['id' => $_GET['id'], 'start_time' => ['lt', time()], 'end_time' => ['gt', time()]])->getField('count');
        if (!$couponCount || $couponCount < 1) {
            return show(0, '该优惠券已被抢光了，下次再来吧');
        }
        //添加优惠券
        $couponData = [
            'create_time' => time(),
            'offer_id' => $_GET['id'],
            'status' => 1,
            'user_id' => $this->user['id'],
            'coupon_type' => 1,
        ];
        $couponId = D('Coupon')->insert($couponData);
        if ($couponId) {
            M('coupon_offer')->where(['id' => $_GET['id']])->setDec('count');
            //添加优惠券记录
            $recordData = [
                'create_time' => time(),
                'operate' => 2,
                'user_id' => $this->user['id'],
                'coupon_id' => $couponId,
                'type' => 1
            ];
            D('CouponRecord')->insert($recordData);
            return show(1, '领取成功，赶快去购买吧');
        } else {
            return show(0, '领取失败');
        }
    }


    public function ajaxIndex()
    {
        $list = M('organization')->field('id,org_name,org_star,env_star,quality_star,picture,is_show,cover_image')->order('org_star,env_star,quality_star desc')->select();
        $count = [];
        foreach ($list as $k => $v) {
            $count[$k] = $v['org_star'] + $v['env_star'] + $v['quality_star'];
        }
        //根据值倒叙排序
        arsort($count);
        $i = 0;
        $data = [];
        //根据键值赋到新的数组里
        foreach ($count as $k => $v) {
            $data[$i] = $list[$k];
            $i++;
        }
        $ranking = [];
        //再以按总分倒叙排序的数组赋值
        foreach ($data as $k => $v) {
            $ranking[$k] = $v['org_star'] + $v['env_star'] + $v['quality_star'];
        }
        //给当前排序排名
        $info = $ranking;
        //根据键值倒叙排序
        rsort($info);
        $c = [];
        foreach ($ranking as $v) {
            $b = array_search($v, $info);
            $c[] = $b + 1;
        }
        foreach ($c as $j => $l) {
            $data[$j]['ranking'] = $l;
        }
        return show(1, '', ['data' => $data]);
    }

    public function ajaxHome()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            return show(0, 'ID参数错误');
        }
        $organization = M('Organization')->field('description', true)->where("id={$_GET['id']}")->find();
        //获取关注信息
        $followed = M('Collect')->where(array('user_id' => $this->user['id'], 'type' => 1, 'type_id' => $_GET['id']))->find();
        //1-已关注 0-未关注
        if ($followed) {
            $isFollowed = 1;
        } else {
            $isFollowed = 0;
        }
        //轮播信息
        $bannerList = D('OrganizationBanner')->getListByToken($organization['token']);
        $banners = [];
        foreach ($bannerList as $k => $v) {
            if ($v['type'] == 1) { //文章
                $articleImage = M('Article')->where("id={$v['type_id']}")->getField('image');
                $banners[$k] = [
                    'id' => $v['id'],
                    'type' => $v['type'],
                    'type_id' => $v['type_id'],
                    'image' => $articleImage,
                ];
            }
            if ($v['type'] == 2) { //课程
                $productImage = M('Product')->where("id={$v['type_id']}")->getField('pic_url');
                $banners[$k] = [
                    'id' => $v['id'],
                    'type' => $v['type'],
                    'type_id' => $v['type_id'],
                    'image' => $productImage,
                ];
            }
        }
        //可领取的优惠券
        $couponWhere = [
            'start_time' => ['lt', time()],
            'end_time' => ['gt', time()],
            'type' => 1,
            'type_id' => $organization['id'],
            'coupon_type' => 1,
            'count' => ['gt', 0],
            'source' => ['EXP', 'is null'],
        ];
        $couponOfferList = M('coupon_offer')->where($couponWhere)->select();
        return show(1, '', ['isFollowed' => $isFollowed, 'banners' => $banners, 'organization' => $organization,
            'offerList' => $couponOfferList]);
    }

    public function getHomeInfo()
    {
        if (!$_GET['id'] || !is_numeric($_GET['id'])) {
            return show(0, 'ID参数错误');
        }
        if (!isset($_GET['page'])) {
            return show(0, 'ID参数错误');
        }
        $data = [];
        $organization = M('Organization')->field('description', true)->where("id={$_GET['id']}")->find();
        //团购课程
        $groupCurriculum = M('group_product')->where(array('type' => 1, 'token' => $organization['token'],
            'is_show' => 1, 'check_status' => 2, 'status' => [array('eq', 1), array('eq', 2), 'or'],
            'start_time' => array('lt', time()), 'end_time' => array('gt', time())))->field('description,cost', true)
            ->order('create_time desc')->limit($_GET['page'] * 2, 2)->select();
        if ($groupCurriculum) {
            foreach ($groupCurriculum as $k => $v) {
                $groupCurriculumCount[$k] = M('order')->where(array('group_id' => array('eq', $v['id'],
                    'status' => array(array('eq', 1), array('eq', 4), 'or'))))->sum('amount');
                if (!$groupCurriculumCount[$k]) {
                    $groupCurriculum[$k]['groupCount'] = 0;
                } else {
                    $groupCurriculum[$k]['groupCount'] = $groupCurriculumCount[$k];
                }
                $groupCurriculum[$k]['logo'] = M('organization')->where(array('token' => $v['token']))->getField('picture');
                $groupCurriculumTag = explode(' ', $v['tag']);
                $groupCurriculum[$k]['tagA'] = $groupCurriculumTag[0] ?: '';
                $groupCurriculum[$k]['tagB'] = $groupCurriculumTag[1] ?: '';
                $groupCurriculum[$k]['tagC'] = $groupCurriculumTag[2] ?: '';
            }
            $data['groupCurriculum'] = $groupCurriculum;
        }
        //课程
        $products = M('product')->where(['token' => $organization['token'], 'check_status' => 2, 'status' => 1])->order('create_time desc')
            ->field('id,pic_url,org_id,title,rush_price,price,class_id,tag')->limit($_GET['page'], 1)->select();
        if ($products) {
            $product = $products[0];
            $tag = explode(' ', $product['tag']);
            $product['tagA'] = $tag[0] ?: '';
            $product['tagB'] = $tag[1] ?: '';
            $product['tagC'] = $tag[2] ?: '';
            $price = json_decode($product['price'], true);
            $product['original_price'] = reset($price)['original_price'];
            $product['now_price'] = reset($price)['now_price'];
            $product['logo'] = M('organization')->where(array('token' => $product['token']))->getField('picture');
            $data['product'] = $product;
        }
        //团购商品
        $groupProducts = M('group_product')->where(array('type' => 2, 'is_show' => 1, 'check_status' => 2,
            'start_time' => array('lt', time()), 'end_time' => array('gt', time()), 'token' => $organization['token'],
            'status' => [array('eq', 1), array('eq', 2), 'or']))->field('description,cost', true)
            ->order('create_time desc')->limit($_GET['page'] * 2, 2)->select();
        if ($groupProducts) {
            $groupProductsCount = count($groupProducts);
            if ($groupProductsCount > 1) {
                foreach ($groupProducts as $k => $v) {
                    $groupProductCount[$k] = M('order')->where(array('group_id' => array('eq', $v['id']),
                        'status' => [array('eq', 1), array('eq', 4), 'or']))->sum('amount');
                    if (!$groupProductCount[$k]) {
                        $groupProducts[$k]['groupCount'] = 0;
                    } else {
                        $groupProducts[$k]['groupCount'] = $groupProductCount[$k];
                    }
                    $groupProducts[$k]['logo'] = M('organization')->where(array('token' => $v['token']))->getField('picture');
                    $groupProductTag = explode(' ', $v['tag']);
                    $groupProducts[$k]['tagA'] = $groupProductTag[0] ?: '';
                    $groupProducts[$k]['tagB'] = $groupProductTag[1] ?: '';
                }
            } elseif ($groupProductsCount == 1 && $_GET['page'] > 0) {//如果最后一组只有一个时，调取前一组的最后一个
                $groupProduct = M('group_product')->where(array('type' => 2, 'is_show' => 1, 'check_status' => 2,
                    'start_time' => array('lt', time()), 'end_time' => array('gt', time()),
                    'status' => [array('eq', 1), array('eq', 2), 'or']))->field('description,cost', true)
                    ->order('create_time desc')->limit($_GET['page'] * 2 - 1, 1)->select();
                $groupProducts[1] = $groupProduct[0];
                foreach ($groupProducts as $q => $w) {
                    $groupProductCount[$q] = M('order')->where(array('group_id' => array('eq', $w['id']),
                        'status' => [array('eq', 1), array('eq', 4), 'or']))->sum('amount');
                    if (!$groupProductCount[$q]) {
                        $groupProducts[$q]['groupCount'] = 0;
                    } else {
                        $groupProducts[$q]['groupCount'] = $groupProductCount[$q];
                    }
                    $groupProducts[$q]['logo'] = M('organization')->where(array('token' => $w['token']))->getField('picture');
                    $groupProductTag = explode(' ', $w['tag']);
                    $groupProducts[$q]['tagA'] = $groupProductTag[0] ?: '';
                    $groupProducts[$q]['tagB'] = $groupProductTag[1] ?: '';
                }
            }
            $data['groupProducts'] = $groupProducts;
        }
        //文章
        $articles = M('article')->where(array('status' => 2, 'class_id' => ['neq', 13], 'token' => $organization['token']))
            ->order('create_time desc')->field('content', true)->limit($_GET['page'], 1)->select();
        if ($articles) {
            $article = $articles[0];
            $article['count'] = D('Comment')->getCountByArtId($article['id']);
            $article['collect'] = D('Collect')->getPageByType($article['id'], 5);
            $prefix = mb_strpos($article['title'], '】', 0, 'utf-8');
            $article['prefix'] = mb_substr($article['title'], 0, $prefix + 1, 'utf-8');
            $article['title'] = mb_substr($article['title'], $prefix + 1, null, 'utf-8');
            $article['class_title'] = M('article_cate')->where(['id' => $article['cate_id']])->getField('title');
            $data['article'] = $article;
        }
        //视频
        $video = M('article')->where(array('status' => 2, 'class_id' => ['eq', 13], 'token' => $organization['token']))
            ->order('create_time desc')->field('content', true)->limit($_GET['page'], 1)->select();
        if ($video) {
            $data['video'] = $video[0];
        }
        return show(1, '', $data);
    }

    public function ajaxActivityList()
    {
        if (!$_GET['id']) {
            return show(0, 'ID参数错误');
        }
        if (!isset($_GET['page'])) {
            return show(0, 'ID参数错误');
        }
        $parenting = M('parenting')->where(['org_id' => $_GET['id'], 'status' => 1, 'check_status' => 2])
            ->field('content,cost', true)->order('create_time desc')->limit($_GET['page'], 5)->select();
        $vote = M('vote')->where(['type' => 1, 'type_id' => $_GET['id'], 'check_status' => 2])
            ->field('description', true)->order('vote_end_time desc')->limit($_GET['page'], 5)->select();
        $parentingTime = [];
        $voteTime = [];
        foreach ($parenting as $k => $v) {
            $parentingTime[$k]['time'] = $v['create_time'];
            $tags = explode(' ', $v['tag']);
            $parenting[$k]['tagA'] = $tags[0] ?: '';
            $parenting[$k]['tagB'] = $tags[1] ?: '';
            $parenting[$k]['tagC'] = $tags[2] ?: '';
            $org[$k] = M('organization')->where(array('id' => $v['org_id']))->find();
            $parenting[$k]['address'] = $org[$k]['city'] . $org[$k]['area'] . $org[$k]['address'];
            $parenting[$k]['time'] = date('Y-m-d', $v['create_time']);
            $parenting[$k]['logo'] = $org[$k]['picture'];
        }
        foreach ($vote as $k => $v) {
            $voteTime[$k]['time'] = $v['work_start_time'];
            $vote[$k]['start_time'] = $v['work_start_time'];
            $vote[$k]['work_start_time'] = date('Y/m/d', $v['work_start_time']);
            $vote[$k]['end_time'] = date('Y/m/d', $v['vote_end_time']);
            //票数
            $vote[$k]['userVoteCount'] = M('contribution_record')->where(array('vote_id' => $v['id'], 'status' => 2))->sum('vote_count');
            if (is_null($vote[$k]['userVoteCount'])) {
                $vote[$k]['userVoteCount'] = 0;
            }
            //作品数
            $userWorks[$k] = M('contribution_record')->where(array('vote_id' => $v['id'], 'status' => 2))->field('id')->select();
            $vote[$k]['userWorksCount'] = count($userWorks[$k]);
            //1-已结束 2-进行中
            if ($v['status'] == 4 || $v['status'] == 5 || $v['status'] == 6 || $v['vote_end_time'] < time()) {
                $vote[$k]['vote_status'] = 1;
            } else {
                $vote[$k]['vote_status'] = 2;
            }
        }
        $newTime = array_merge($parentingTime, $voteTime);
        arsort($newTime);
        $newTime = array_merge($newTime);
        $list = [];
        //1-活动 2-投票
        foreach ($newTime as $k => $v) {
            foreach ($parenting as $n => $m) {
                if ($v['time'] == $m['create_time']) {
                    $list[$k] = $m;
                    $list[$k]['activity_type'] = 1;
                }
            }
            foreach ($vote as $n => $m) {
                if ($v['time'] == $m['start_time']) {
                    $list[$k] = $m;
                    $list[$k]['activity_type'] = 2;
                }
            }
        }
        $list = array_merge($list);
        return show(1, '', ['list' => $list]);
    }

    public function ajaxAssociation()
    {
        if (!$_GET['id']) {
            return show(0, 'ID参数错误');
        }
        if (!isset($_GET['page'])) {
            return show(0, 'ID参数错误');
        }
        $token = M('Organization')->where("id={$_GET['id']}")->getField('token');
        $list = M('association')->where(['token' => $token, 'status' => 1])->order('create_time desc')->limit($_GET['page'], 6)->select();
        return show(1, '', ['list' => $list]);
    }


}