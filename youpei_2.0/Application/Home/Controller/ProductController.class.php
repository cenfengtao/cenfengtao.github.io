<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

require_once __DIR__ . '/../../../ThinkPHP/Library/Org/Util/JSDDK.class.php';
require_once __DIR__ . '/../../../ThinkPHP/Library/Vendor/ChuanglanSmsHelper/ChuanglanSmsApi.php';

class ProductController extends BaseController
{
    public function videoList()
    {
        $this->display();
    }

    public function appoint()
    {
        $this->display();
    }

    public function confirm()
    {
        $this->display();
    }

    public function getClass()
    {
        $this->display();
    }

    public function courseList()
    {
        $this->display();
    }

    public function productDetails()
    {
        if ($_POST) {
            if (!$_POST['product_id'] || empty($_POST['product_id'])) {
                return show(0, '商品参数错误');
            }
            if (!$_POST['content'] || empty($_POST['content'])) {
                return show(0, '咨询内容不能为空');
            }
            $insertData = array(
                'product_id' => $_POST['product_id'],
                'user_id' => $this->user['id'],
                'create_time' => time(),
                'type' => 1,
                'status' => 1,
                'content' => $_POST['content'],
                'token' => $this->token,
            );
            $id = D('ProductComment')->insert($insertData);
            $list = D('ProductComment')->find($id);
            $headImg = M('user')->where("id={$list['user_id']}")->field('headimgurl')->find();
            $list['headImg'] = $headImg['headimgurl'];
            if (!$list || empty($list)) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '咨询失败'));
            }
            $this->ajaxReturn(array('status' => 1, 'msg' => '咨询成功', 'data' => $list));
        } else {
            $this->title = "课程名称";
            if (!$_GET['pro_id'] || !is_numeric($_GET['pro_id'])) {
                $this->error('获取不到该商品信息');
            }
            try {
                $product = D('Product')->find($_GET['pro_id']);
                //增加浏览记录
                $this->addFootprint(1, $product['id']);
                //评论列表
                $productComment = D('ProductComment')->getCommentByFatherId(0, 0, $_GET['pro_id']);
                foreach ($productComment as $k => $v) {
                    $productComment[$k]['child'] = M('ProductComment')->where(array('type_id' => $v['id'], 'product_id' => $v['product_id'], 'status' => 1))->select();
                    $productComment[$k]['headImg'] = D('user')->getHeadById($v['user_id']);
                    foreach ($productComment[$k]['child'] as $ke => $va) {
                        //回复
                        if ($va['is_gm'] == 2) {
                            //待机构管理员完善之后需修改
                            //客服头像
                            $picture = M('organization')->field('picture')->where(array('token' => $this->token))->find();
                            $productComment[$k]['child'][$ke]['headImg'] = $picture['picture'];
                        } else if ($va['is_gm'] == 1) {
                            $productComment[$k]['child'][$ke]['headImg'] = D('user')->getHeadById($va['user_id']);
                        }
                        //被回复
                        $userId = M('ProductComment')->where(array('id' => $productComment[$k]['child'][$ke]['father_id']))->find();
                        if ($userId['is_gm'] == 2) {
                            $pictures = M('organization')->field('picture')->where(array('token' => $userId['token']))->find();
                            $productComment[$k]['child'][$ke]['headImgs'] = $pictures['picture'];
                        } else if ($userId['is_gm'] == 1) {
                            $productComment[$k]['child'][$ke]['headImgs'] = D('user')->getHeadById($userId['user_id']);
                        }
                    }
                }
                //标签
                $tags = explode(' ', $product['tag']);
                //机构
                $organization = D('Organization')->find($product['org_id']);
                //获取ticket
                $wxuser = get_wxuser($this->token);
                $jssdk = new \JSSDK($wxuser['appid'], $wxuser['appsecret']);
                $signPackage = $jssdk->GetSignPackage();
                //分享内容图片和链接地址
                if (strpos($product['pic_url'], 'http') === false) {
                    $shareImg = 'http://' . $_SERVER["HTTP_HOST"] . $product['pic_url'];
                } else {
                    $shareImg = $product['pic_url'];
                }
                $shareUrl = 'http://' . $_SERVER["HTTP_HOST"] . U('Product/productDetails', array('share_user_id' =>
                        $this->user['id'], 'pro_id' => $_GET['pro_id'], 'token' => $this->token));
                //购买商品的所有用户头像
                $productId = $_GET['pro_id'];
                $userId = D('Order')->getGroupById($productId);
                foreach ($userId as $k => $v) {
                    $headImg[$k]['headImg'] = D('User')->getHeadById($v['user_id']);
                }
                $userCount = sizeof($userId);
                //是否收藏
                $isCollect = D('Collect')->isCollectById($this->user['id'], $_GET['pro_id'], $product['type'] == 1 ? 2 : 3);
                $price = json_decode($product['price'], true);
                if ($_GET['key']) {
                    $firstKey = $_GET['key'];
                } else {
                    $firstKey = key($price);
                }
                $product['price'] = [];
                foreach ($price as $k => $v) {
                    if ($v['status'] == 2) {
                        continue;
                    }
                    //判断有否抢购价
                    $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $_GET['pro_id'], 'key' => $k,
                        'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                    if ($rushPrice) {
                        $product['price'][$k] = [
                            'class_normal' => $v['class_normal'],
                            'original_price' => $v['now_price'],
                            'now_price' => $rushPrice,
                            'count' => $v['count'],
                        ];
                    } else {
                        $product['price'][$k] = $v;
                    }
                }
                $this->assign('signPackage', $signPackage)->assign('share_img', $shareImg)->assign('share_url', $shareUrl);
                $this->assign('headImg', $headImg)->assign('userCount', $userCount)->assign('isCollect', $isCollect)
                    ->assign('type', $product['type'])->assign('productComment', $productComment)->assign('tags', $tags)
                    ->assign('organization', $organization)->assign('product', $product)->assign('firstKey', $firstKey);
                $this->display();
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }

    public function saleProductDetail()
    {
        if (!$_GET['pro_id'] || !is_numeric($_GET['pro_id'])) {
            $this->error('获取不到该商品信息');
        }
        if (!$_GET['sale_id']) {
            $this->error('参数错误，请联系客服');
        }
        //判断是否有资格购买该折扣商品
        $sale = D('QrcodeSale')->find($_GET['sale_id']);
        $sceneId = M('qrcode')->where(['id' => $_GET['qrcodeId']])->getField('scene_id');
        $templateTimeStr = $sale['template_number'] == 1 ? 'template_a_times' : ($sale['template_number']
        == 2 ? 'template_b_times' : 'template_c_times');
        $scanQuantity = M('scan_reply')->where(['scene_id' => $sceneId])->getField($templateTimeStr);
        //查询扫码次数
        $scanCount = M('qrcode_record')->where(['scene_id' => $sceneId, 'share_user_id' => $this->user['id']])->count();
        if ($scanCount < $scanQuantity) {
            $this->error('你的助力次数不足哦');
        }
        $isOrder = M('order')->where(['qrcode_id' => $_GET['qrcodeId'], 'template_number' => $_GET['sort'],
            'user_id' => $this->user['id'], 'status' => array(['eq', 0], ['eq', 1], ['eq', 4], 'or')])->getField('id');
        if ($isOrder) {
            $this->error('你已经购买过了');
        }
        $product = D('Product')->find($_GET['pro_id']);
        //标签
        $tags = explode(' ', $product['tag']);
        $prices = json_decode($product['price'], true);
        $price = $prices[$sale['key']];
        $this->assign('sale', $sale)->assign('price', $price)->assign('product', $product)->assign('tags', $tags);
        $this->display();
    }

    public function integralProductDetail()
    {
        if (!$_GET['bargain_id'] || !is_numeric($_GET['bargain_id'])) {
            $this->error('获取不到该商品信息');
        }
        $bargain = D('Bargain')->find($_GET['bargain_id']);
        $product = D('Product')->find($bargain['type_id']);
        //标签
        $tags = explode(' ', $product['tag']);
        $prices = json_decode($product['price'], true);
        $price = $prices[$bargain['key']];
        $bargain['price'] = intval($bargain['price']);
        //判断是否有足够积分  1-足够 2-不足
        if ($bargain['price'] < $this->user['integral']) {
            $canOrder = 1;
        } else {
            $canOrder = 2;
        }
        $this->assign('bargain', $bargain)->assign('price', $price)->assign('product', $product)->assign('tags', $tags)
            ->assign('canOrder', $canOrder);
        $this->display();
    }

    public function checkOrderInformation()
    {
        if (!$_GET['pro_id'] || !is_numeric($_GET['pro_id'])) {
            $this->error('获取不到该商品信息');
        }
        try {
            $product = D('Product')->find($_GET['pro_id']);
            $price = json_decode($product['price'], true);
            if ($_GET['key']) {
                $firstKey = $_GET['key'];
            } else {
                $firstKey = key($price);
            }
            $product['price'] = [];
            foreach ($price as $k => $v) {
                $product['price'][$k] = $v;
                //判断有否抢购价
                $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $_GET['pro_id'], 'key' => $k,
                    'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
                if ($rushPrice) {
                    $product['price'][$k]['original_price'] = $v['now_price'];
                    $product['price'][$k]['now_price'] = $rushPrice;
                }
            }
            $integral = M('user')->where('id=' . $this->user['id'])->getField('integral');
            $this->assign('product', $product)->assign('firstKey', $firstKey)->assign('price', $price)->assign('integral', $integral);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function saleCheckOrderInformation()
    {
        if (!$_GET['pro_id'] || !$_GET['sale_id']) {
            $this->error('参数错误');
        }
        try {
            //判断是否还有库存
            $sale = D('QrcodeSale')->find($_GET['sale_id']);
            if ($sale['count'] <= 0) {
                $this->error('库存已不足了哦');
            }
            $product = D('Product')->find($_GET['pro_id']);
            $prices = json_decode($product['price'], true);
            $classNormal = $prices[$sale['key']]['class_normal'];
            $this->assign('product', $product)->assign('sale', $sale)->assign('classNormal', $classNormal);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function integralCheckOrderInformation()
    {
        if (!$_GET['bargain_id']) {
            $this->error('参数错误');
        }
        try {
            $bargain = D('Bargain')->find($_GET['bargain_id']);
            $product = D('Product')->find($bargain['type_id']);
            $prices = json_decode($product['price'], true);
            $classNormal = $prices[$bargain['key']]['class_normal'];
            $bargain['price'] = intval($bargain['price']);
            $this->assign('product', $product)->assign('bargain', $bargain)->assign('classNormal', $classNormal);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function confirmationInfo()
    {
        if (!$_GET['pro_id']) {
            return show(0, '参数错误');
        }
        if (!$_GET['amount'] || $_GET['amount'] < 1) {
            return show(0, '数量参数错误');
        }
        if (!$_GET['key']) {
            return show(0, '规格参数错误');
        }
        if (!$_GET['name']) {
            return show(0, '联系人不能为空');
        }
        if (!$_GET['mobile']) {
            return show(0, '联系电话不能为空');
        }
        if (!$_GET['amount']) {
            return show(0, '数量不能为空');
        }
        $this->display();
    }

    public function ajaxConfirmationInfo()
    {
        if (!$_GET['pro_id']) {
            return show(0, '参数错误');
        }
        if (!$_GET['amount'] || $_GET['amount'] < 1) {
            return show(0, '数量参数错误');
        }
        if (!$_GET['key']) {
            return show(0, '规格参数错误');
        }
        $product = M('product')->where(['id' => $_GET['pro_id']])->field('desc,cost', true)->find();
        $prices = json_decode($product['price'], true);
        $price = $prices[$_GET['key']]['now_price'];
        $count = $prices[$_GET['key']]['count'];
        if ($count < $_GET['amount']) {
            return show(0, '数量超出库存范围，请返回上一页刷新之后再选择');
        }
        $couponByUser = M('coupon')->where(['status' => 1, 'user_id' => $this->user['id']])->select();
        $couponList = [];
        //判断有否抢购价
        $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $_GET['pro_id'], 'key' => $_GET['key'],
            'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
        if ($rushPrice && !empty($rushPrice)) {
            $price = $rushPrice;
        }
        $totalPrice = $price * $_GET['amount'];
        foreach ($couponByUser as $key => $val) {
            //判断有否过期
            $offer = M('coupon_offer')->where(['id' => $val['offer_id']])->find();
            if ($offer['end_time'] <= time()) {
                M('coupon')->where(['id' => $val['id']])->save(['status' => 4]);
                continue;
            }
            //判断该商品是否可用
            if ($offer['type'] == 1 && $offer['type_id'] != $product['org_id']) {
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
        //判断积分是否超过总价
        if ($nowIntegral / 100 > $totalPrice) $nowIntegral = ceil($totalPrice) * 100;
        //type 1-课程 2-普通商品
        return show(1, '获取成功', ['couponList' => $couponList, 'totalPrice' => $totalPrice, 'nowIntegral' => $nowIntegral,
            'classNormal' => $prices[$_GET['key']]['class_normal'], 'title' => $product['title'], 'image' => $product['pic_url'], 'type' => $product['type']]);
    }

    //分享记录
    public function shareRecord()
    {
        $insertData = [
            'create_time' => time(),
            'type' => 2,
            'type_id' => $_POST['pro_id'],
            'desc' => '分享商品',
            'user_id' => $this->user['id'],
        ];
        D('ShareRecord')->insert($insertData);
        return show(0, '分享成功');
    }

    //用户之间留言对话
    public function comment()
    {
        if ($_POST) {
            if (!$_POST['product_id'] || empty($_POST['product_id'])) {
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
                'product_id' => $_POST['product_id'],
                'content' => $_POST['content'],
                'token' => $this->token,
                'type_id' => $_POST['type_id'],
                'status' => 1,
                'type' => 1,
                'create_time' => time()
            ];
            $id = D('ProductComment')->insert($data);
            //评论人头像
            $reply = D('ProductComment')->find($id);
            $headImg = M('user')->where("id={$reply['user_id']}")->field('headimgurl')->find();
            $reply['headImg'] = $headImg['headimgurl'];
            //被评论人头像
            $replys = D('ProductComment')->find($_POST['father_id']);
            $headImgs = M('user')->where("id={$replys['user_id']}")->field('headimgurl')->find();
            $reply['headImgs'] = $headImgs['headimgurl'];
            if (!$reply || empty($reply)) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '评论失败'));
            }
            $this->ajaxReturn(array('status' => 1, 'msg' => '评论成功', 'data' => $reply));
        }
    }

    public function getClassTable()
    {
        $this->display();
    }

    public function getClassTableAjax()
    {
        if (!$_POST['pro_id']) {
            return show(0, '课程ID参数错误');
        }
        $product = D('Product')->find($_POST['pro_id']);
        $classTime = json_decode($product['class_time'], true);
        $bookTime = json_decode($product['book_time'], true);
        if (!$_POST['start_date']) {
            $differDays = date('w', time());
            $startTime = time() - 86400 * $differDays;
        } else {
            $startTime = $_POST['start_date'];
        }
        $weekArray = [
            ['day' => '周日', 'year' => date("Y", $startTime), "month" => date("m", $startTime), 'date' => date("d", $startTime)],
            ['day' => '周一', 'year' => date("Y", $startTime + 86400), "month" => date("m", $startTime + 86400), 'date' => date("d", $startTime + 86400)],
            ['day' => '周二', 'year' => date("Y", $startTime + 86400 * 2), "month" => date("m", $startTime + 86400 * 2), 'date' => date("d", $startTime + 86400 * 2)],
            ['day' => '周三', 'year' => date("Y", $startTime + 86400 * 3), "month" => date("m", $startTime + 86400 * 3), 'date' => date("d", $startTime + 86400 * 3)],
            ['day' => '周四', 'year' => date("Y", $startTime + 86400 * 4), "month" => date("m", $startTime + 86400 * 4), 'date' => date("d", $startTime + 86400 * 4)],
            ['day' => '周五', 'year' => date("Y", $startTime + 86400 * 5), "month" => date("m", $startTime + 86400 * 5), 'date' => date("d", $startTime + 86400 * 5)],
            ['day' => '周六', 'year' => date("Y", $startTime + 86400 * 6), "month" => date("m", $startTime + 86400 * 6), 'date' => date("d", $startTime + 86400 * 6)],
        ];
        foreach ($weekArray as $key => $val) {
            foreach ($classTime as $k => $v) {
                if ($val['day'] == $v['class_time_day']) {
                    $weekArray[$key]['class_time'][] = $v;
                }
            }
            foreach ($bookTime as $ke => $va) {
                if ($val['day'] == $va['book_time_day']) {
                    $weekArray[$key]['book_time'][] = $va;
                }
            }
        }
        $header = [
            'start_date' => date("m-d", $startTime),
            'end_date' => date("m-d", strtotime("+6 day", $startTime)),
        ];
        $data = [
            'header' => $header,
            'weekArray' => $weekArray
        ];
        return show(1, '', $data);
    }

    public function getBookTimeByDay()
    {
        if (!$_POST['pro_id']) {
            return show(0, '商品ID不能为空');
        }
        if (!$_POST['time']) {
            return show(0, '日期参数有误');
        }
        $bookTime = M('product')->where("id={$_POST['pro_id']}")->getField('book_time');
        $weekArray = ["周日", "周一", "周二", "周三", "周四", "周五", "周六"];
        $day = $weekArray[date('w', $_POST['time'])];
        $bookTime = json_decode($bookTime, true);
        $canBookTime = [];
        foreach ($bookTime as $key => $val) {
            if ($val['book_time_day'] == $day) {
                $canBookTime[] = [
                    'start_time' => $val['book_start_hour'],
                    'end_time' => $val['book_end_hour'],
                ];
            }
        }
        $canBook['book_time'] = $canBookTime;
        if (empty($canBookTime)) {
            return show(0, '无可预约时间');
        }
        return show(1, '', $canBook);
    }

    public function getBookReadRecord()
    {
        if (!$_POST['pro_id']) {
            return show(0, '商品ID不能为空');
        }
        if (!$_POST['name']) {
            return show(0, '姓名不能为空');
        }
        if (!$_POST['tel']) {
            return show(0, '手机号不能为空');
        }
        $token = M('product')->where(array('id' => $_POST['pro_id']))->getField('token');
        $data = [
            'create_time' => time(),
            'type' => 1,
            'type_id' => $_POST['pro_id'],
            'user_id' => $this->user['id'],
            'name' => $_POST['name'],
            'mobile' => $_POST['tel'],
            'status' => 1,
            'token' => $token,
            'start_time' => strtotime($_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['date'] . ' ' . $_POST['start_time']),
            'end_time' => strtotime($_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['date'] . ' ' . $_POST['end_time']),
            'is_read' => 1
        ];
        //判断是否有预约记录
        $isBook = M('BookRecord')->where(array('type' => 1, 'type_id' => $_POST['pro_id'], 'user_id' => $this->user['id']))->find();
        if ($isBook) {
            if ($isBook['status'] != 4) {
                if ($isBook['count'] < 3) {
                    $data['count'] = $isBook['count'] + 1;
                    $id = D('BookRecord')->updateById($isBook['id'], $data);
                    if ($id) {
                        $this->sendMsgToOrgBook($isBook['id']);
                        return show(1, '为您更新了预约');
                    } else {
                        return show(0, '您之前预约过此课程了呢');
                    }
                } else {
                    return show(0, '您预约修改的次数太多，请联系管理员');
                }
            } else {
                return show(0, '预约失败');
            }
        } else {
            //没预约就提交数据
            $recordId = D('BookRecord')->insert($data);
            if ($recordId) {
                $sms = $this->sendMsgToOrg($recordId);
                if ($sms) {
                    return show(1, '预约成功');
                }
            } else {
                return show(0, '预约失败');
            }
        }
    }

    //向机构管理员发送信息通知
    public function sendMsgToOrg($bookId)
    {
        $bookClass = D('BookRecord')->find($bookId);
        $proTitle = M('product')->where(array('id' => $bookClass['type_id']))->getField('title');
        $orgId = M('organization')->where(array('token' => $bookClass['token']))->getField('id');
        $orgName = M('organization')->where(array('token' => $bookClass['token']))->getField('org_name');
        $orgMobile = M('admin_user')->where(array('org_id' => $orgId, 'mobile' => array(array('exp', 'is not null'), array('neq', 0), 'and')))->getField('mobile');
        $sms = new \ChuanglanSmsApi();
        $msg = '{$var} 您好，您在优培圈上架的课程 {$var} 有家长预约上课了，请及时登录后台跟进！';
        $params = "{$orgMobile},{$orgName},{$proTitle}";
        $result = $sms->sendVariableSMS($msg, $params);
        $recordData = [
            'create_time' => time(),
            'content' => "{$orgName}您好，您在优培圈上架的课程 {$proTitle} 有家长预约上课了，请及时登录后台跟进！",
            'type' => 3,
            'type_id' => $bookClass['id'],
            'mobile' => $orgMobile
        ];
        if (!is_null(json_decode($result))) {
            $output = json_decode($result, true);
            if (isset($output['code']) && $output['code'] == '0') {
                $recordData['status'] = 2;
                $recordData['err_code'] = $output['code'];
                return D('SmsRecord')->insert($recordData);
            } else {
                $recordData['status'] = 3;
                $recordData['err_code'] = $output['code'];
                $recordData['err_msg'] = $output['errorMsg'];
                return D('SmsRecord')->insert($recordData);
            }
        } else {
            $recordData['status'] = 3;
            $recordData['err_code'] = 'undefined';
            $recordData['err_msg'] = '发送失败';
            return D('SmsRecord')->insert($recordData);
        }
    }

    //向机构管理员发送信息通知
    public function sendMsgToOrgBook($bookId)
    {
        $bookClass = D('BookRecord')->find($bookId);
        $proTitle = M('product')->where(array('id' => $bookClass['type_id']))->getField('title');
        $orgId = M('organization')->where(array('token' => $bookClass['token']))->getField('id');
        $orgName = M('organization')->where(array('token' => $bookClass['token']))->getField('org_name');
        $orgMobile = M('admin_user')->where(array('org_id' => $orgId, 'mobile' => array(array('exp', 'is not null'), array('neq', 0), 'and')))->getField('mobile');
        $sms = new \ChuanglanSmsApi();
        $msg = '{$var} 您好，您的课程 {$var} 有家长更改了预约时间，请及时登录后台跟进！';
        $params = "{$orgMobile},{$orgName},{$proTitle}";
        $result = $sms->sendVariableSMS($msg, $params);
        $recordData = [
            'create_time' => time(),
            'content' => "{$orgName}您好，您的课程 {$proTitle} 有家长更改了预约时间，请及时登录后台跟进！",
            'type' => 3,
            'type_id' => $bookClass['id'],
            'mobile' => $orgMobile
        ];
        if (!is_null(json_decode($result))) {
            $output = json_decode($result, true);
            if (isset($output['code']) && $output['code'] == '0') {
                $recordData['status'] = 2;
                $recordData['err_code'] = $output['code'];
                return D('SmsRecord')->insert($recordData);
            } else {
                $recordData['status'] = 3;
                $recordData['err_code'] = $output['code'];
                $recordData['err_msg'] = $output['errorMsg'];
                return D('SmsRecord')->insert($recordData);
            }
        } else {
            $recordData['status'] = 3;
            $recordData['err_code'] = 'undefined';
            $recordData['err_msg'] = '发送失败';
            return D('SmsRecord')->insert($recordData);
        }
    }

    //砍价商品
    public function bargain()
    {
        $this->display();
    }

    public function bargainList()
    {
        $this->display();
    }

    public function ajaxBargain()
    {
        if (!$_GET['pro_id'] || empty($_GET['pro_id'])) {
            return show(0, 'ID不能为空');
        }
        if (!$_GET['key']) {
            return show(0, '规格不能为空');
        }
        $bargain = M('bargain')->where(array('type' => 1, 'type_id' => $_GET['pro_id'], 'key' => $_GET['key'],
            'start_time' => array('LT', time()), 'end_time' => array('GT', time())))->find();
        if (!$bargain) {
            return show(0, '此产品未参加砍价活动');
        }
        //添加浏览记录
        $this->addFootprint(4, $bargain['id']);
        if ($_GET['share_user_id'] == 0 || $_GET['share_user_id'] == $this->user['id']) {
            $isMe = 2;  //我自己
            $share_user_id = $this->user['id'];
            //砍价帮助人数和金额
            $bargainHelpUser = D('BargainRecord')->bargainHelpByUser($this->user['id'], $bargain['id'], $bargain['start_time'], $bargain['end_time']);
            $bargainPeople = count($bargainHelpUser);
            $bargainPrice = D('BargainRecord')->bargainHelpByPrice($this->user['id'], $bargain['id'], $bargain['start_time'], $bargain['end_time']);
            //付款，未付款，交易完成，退款中，已退款,付款之后不能再使用砍价额度
            $order = M('order')->where(array('user_id' => $this->user['id'], 'product_id' => $_GET['pro_id'], 'key' => $_GET['key'],
                'bargain_id' => $bargain['id'], 'status' => array(array('EQ', 0), array('EQ', 1), array('EQ', 4), array('EQ', 6), array('EQ', 7), 'or'),
                'create_time' => array(array('GT', $bargain['start_time']), array('LT', $bargain['end_time']), 'and')))->find();
            if (!$order) {
                $isBuy = -1;
            } else {
                $isBuy = $order['status'];
            }
        } else {
            $isBuy = -1;
            $isMe = 1;
            $share_user_id = $_GET['share_user_id'];
            //砍价帮助人数和金额
            $bargainHelpUser = D('BargainRecord')->bargainHelpByUser($_GET['share_user_id'], $bargain['id'], $bargain['start_time'], $bargain['end_time']);
            $bargainPeople = count($bargainHelpUser);
            $bargainPrice = D('BargainRecord')->bargainHelpByPrice($_GET['share_user_id'], $bargain['id'], $bargain['start_time'], $bargain['end_time']);
        }
        $userImage = [];
        $userPrice = [];
        foreach ($bargainHelpUser as $k => $v) {
            $userImage[$k] = D('User')->getHeadById($v['user_id']);
            $userPrice[$k] = $v['price'];
        }
        $product = M('product')->where(array('id' => $_GET['pro_id']))->find();
        $prices = json_decode($product['price'], true);
        $price = $prices[$_GET['key']]['now_price'];
        $product['now_price'] = $price;
        $product['class_normal'] = $prices[$_GET['key']]['class_normal'];
        //获取ticket
        $wxuser = get_wxuser($this->token);
        $jssdk = new \JSSDK($wxuser['appid'], $wxuser['appsecret']);
        $signPackage = $jssdk->GetSignPackage();
        //分享内容图片和链接地址
        if (strpos($product['pic_url'], 'http') === false) {
            $shareImg = 'http://' . $_SERVER["HTTP_HOST"] . $product['pic_url'];
        } else {
            $shareImg = $product['pic_url'];
        }
        $userName = D('User')->getNameById($this->user['id']);
        $product['f_title'] = $userName . '邀请你来帮Ta减价!!';
        /*$shareUrl = 'http://' . $_SERVER["HTTP_HOST"] . U('Product/bargain', array('share_user_id' =>
                $this->user['id'], 'pro_id' => $_GET['pro_id'], 'token' => $this->token));*/
        $shareUrl = 'http://' . $_SERVER["HTTP_HOST"] . '/index.php/Product/bargain?share_user_id=' . $share_user_id .
            '&pro_id=' . $_GET['pro_id'] . '&token=' . $this->token . '&key=' . $_GET['key'];
        $data = [
            'signPackage' => $signPackage,
            'shareImg' => $shareImg,
            'shareUrl' => $shareUrl,
            'product' => $product,
            'bargainPeople' => $bargainPeople,
            'bargainPrice' => $bargainPrice,
            'userImage' => $userImage,
            'userPrice' => $userPrice,
            'isBuy' => $isBuy,
            'isMe' => $isMe,
            'totalBargainPrice' => $bargain['price'],
        ];
        return show(1, '', $data);
    }

    //砍他一刀
    public function bargainHelp()
    {
        if (!$_GET['pro_id'] || empty($_GET['pro_id'])) {
            return show(0, 'ID不能为空');
        }
        if (!$_GET['share_user_id'] || empty($_GET['share_user_id'])) {
            return show(0, 'ID不能为空');
        }
        if ($_GET['share_user_id'] == $this->user['id']) {
            return show(0, '不能给自己砍价哦~');
        }
        if (!$_GET['key']) {
            return show(0, '品类参数错误');
        }
        $bargain = D('Bargain')->isBargain($type_id = $_GET['pro_id'], $_GET['key']);
        $where['create_time'] = array(array('LT', $bargain['end_time']), array('GT', $bargain['start_time']), 'and');
        $isBargain = M('BargainRecord')->where($where)->where(array('share_user_id' => $_GET['share_user_id'],
            'bargain_id' => $bargain['id'], 'user_id' => $this->user['id']))->find();
        if ($isBargain) {
            return show(2, '你已经帮他砍过价了呢!');
        }
        //砍价随机数 精确到小数点后2位
        $priceUpper = $bargain['price'] * 0.01;
        $priceLower = $bargain['price'] * 0.03;
        $price = substr($this->randFloat($priceUpper, $priceLower), 0, 4);
        $priceSum = M('BargainRecord')->where($where)->where(array('share_user_id' => $_GET['share_user_id'], 'bargain_id' => $bargain['id']))->sum('price');
        if ($priceSum >= $bargain['price']) {
            return show(3, '人家已经完成砍价了呢:)');
        } elseif (($priceSum + $price) > $bargain['price']) {
            $price = $bargain['price'] - $priceSum;
        }
        $insertData = [
            'create_time' => time(),
            'bargain_id' => $bargain['id'],
            'user_id' => $this->user['id'],
            'share_user_id' => $_GET['share_user_id'],
            'price' => $price
        ];
        $id = D('BargainRecord')->insert($insertData);
        if ($id) {
            //未付款
            $isOrder = M('order')->where(array('user_id' => $_GET['share_user_id'], 'product_id' => $_GET['pro_id'],
                'bargain_id' => $bargain['id'], 'status' => array('neq', 0),
                'create_time' => array(array('GT', $bargain['start_time']), array('LT', $bargain['end_time']), 'and')))->getField('id');
            if (!$isOrder) {
                $userPeople = D('BargainRecord')->bargainHelpByUser($_GET['share_user_id'], $bargain['id'], $bargain['start_time'], $bargain['end_time']);
                $userCount = count($userPeople);
                if ($userCount % 3 == 0) {
                    $productToken = M('product')->where(array('id' => $_GET['pro_id']))->getField('token');
                    $productTitle = M('product')->where(array('id' => $_GET['pro_id']))->getField('title');
                    $prices = D('BargainRecord')->bargainHelpByPrice($_GET['share_user_id'], $bargain['id'], $bargain['start_time'], $bargain['end_time']);
                    $user = D('User')->find($_GET['share_user_id']);
                    //发送通知模板
                    $first = '【优培圈】温馨提醒您的砍价进度';
                    $keyword1 = $productTitle;
                    $keyword2 = '已有' . $userCount . '个人帮您砍了' . $prices . '元了';
                    $keyword3 = date("Y-m-d H:i:s", time());
                    $remark = '请点击“详情”购买商品';
                    $url = "http://{$_SERVER['HTTP_HOST']}/index.php/Product/bargain.html?pro_id=" . $_GET['pro_id'] . "&key=" . $_GET['key'] . "&token=" . $productToken;
                    $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__KEYWORD4__', '__REMARK__');
                    $infoFormat = array($user['open_id'], $url, $first, $keyword1, $keyword2, $keyword3, $remark);
                    $wxuser = get_wxuser("g232238gc959");
                    $result = execute_public_template('BARGAIN_SCHEDULE', $templeFormat, $infoFormat, $wxuser);
                    if (isset($result['errcode']) && $result['errcode'] == 0) {
                        $status = 2;
                    } else {
                        $status = 1;
                    }
                    $data = [
                        'user_id' => $user['id'],
                        'type' => 1,
                        'type_id' => $_GET['pro_id'],
                        'create_time' => time(),
                        'errmsg' => $result['errmsg'],
                        'errcode' => $result['errcode'],
                        'status' => $status,
                    ];
                    $id = D('TemplateRecord')->insert($data);
                }
            }
            return show(1, '砍价成功', $price);
        } else {
            return show(0, '砍价失败');
        }
    }

    //随机数
    function randFloat($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    //分享砍价记录
    public function bargainRecord()
    {
        $bargain = M('Bargain')->where(array('type' => 1, 'type_id' => $_POST['pro_id'],
            'start_time' => ['lt', time()], 'end_time' => ['gt', time()]))->find();
        $isShare = M('BargainRecord')->where(array('user_id' => $this->user['id'], 'bargain_id' => $bargain['id'], 'share_user_id' => array('EXP', 'IS NULL')))->select();
        if ($isShare) {
            return show(1, '分享成功,请赶快跟朋友说一声吧');
        } else {
            $bargain = M('Bargain')->where(array('type' => 1, 'type_id' => $_POST['pro_id']))->find();
            $insertData = [
                'create_time' => time(),
                'bargain_id' => $bargain['id'],
                'user_id' => $this->user['id'],
            ];
            D('BargainRecord')->insert($insertData);
            return show(1, '分享成功，请赶快跟朋友说一声吧：）');
        }
    }

    public function bargainCheckOrderInfo()
    {
        $this->title = "商品详细";
        if (!$_GET['pro_id'] || !is_numeric($_GET['pro_id'])) {
            $this->error('获取不到该商品信息');
        }
        if (!$_GET['key']) {
            return show(0, '规格参数错误');
        }
        try {
            $product = D('Product')->find($_GET['pro_id']);
            //判断砍价
            $bargain = M('bargain')->where(['type' => 1, 'type_id' => $_GET['pro_id'], 'key' => $_GET['key'],
                'start_time' => ['lt', time()], 'end_time' => ['gt', time()]])->find();
            $productPrice = json_decode($product['price'], true);
            $originalPrice = $productPrice[$bargain['key']]['original_price'];
            //付款，未付款，交易完成，退款中，已退款,付款之后不能再使用砍价额度
            $isBargainByOrder = M('order')->where(array('user_id' => $this->user['id'], 'product_id' => $product['id'],
                'bargain_id' => $bargain['id'], 'status' => array(array('EQ', 0), array('EQ', 1), array('EQ', 4), array('EQ', 6), array('EQ', 7), 'or'),
                'create_time' => array(array('GT', $bargain['start_time']), array('LT', $bargain['end_time']), 'and')))->find();
            if ($isBargainByOrder) {
                $price = 0;
            } else {
                $price = M('BargainRecord')->where(array('bargain_id' => $bargain['id'],
                    'create_time' => array(array('LT', $bargain['end_time']), array('GT', $bargain['start_time']), 'and'),
                    'share_user_id' => $this->user['id']))->sum('price');
            }
            $product['bargain_price'] = $originalPrice - $price;
            $this->assign('product', $product)->assign('originalPrice', $originalPrice);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function bargainCheckOrderInformation()
    {
        if (!$_GET['pro_id'] || !is_numeric($_GET['pro_id'])) {
            $this->error('获取不到该商品信息');
        }
        if (!$_GET['key']) {
            return show(0, '规格参数错误');
        }
        try {
            $product = D('Product')->find($_GET['pro_id']);
            //判断砍价
            $bargain = M('bargain')->where(['type' => 1, 'type_id' => $_GET['pro_id'], 'key' => $_GET['key'],
                'start_time' => ['lt', time()], 'end_time' => ['gt', time()]])->find();
            $productPrice = json_decode($product['price'], true);
            //付款，未付款，交易完成，退款中，已退款,付款之后不能再使用砍价额度
            $isBargainByOrder = M('order')->where(array('user_id' => $this->user['id'], 'product_id' => $product['id'],
                'bargain_id' => $bargain['id'], 'status' => array(array('EQ', 0), array('EQ', 1), array('EQ', 4), array('EQ', 6), array('EQ', 7), 'or'),
                'create_time' => array(array('GT', $bargain['start_time']), array('LT', $bargain['end_time']), 'and')))->find();
            if ($isBargainByOrder) {
                $price = 0;
            } else {
                $price = M('BargainRecord')->where(array('bargain_id' => $bargain['id'],
                    'create_time' => array(array('LT', $bargain['end_time']), array('GT', $bargain['start_time']), 'and'),
                    'share_user_id' => $this->user['id']))->sum('price');
            }
            $product['price'] = $productPrice[$bargain['key']];
            if ($product['price']['count'] < 1) {
                $product['bargain_price'] = 0;
                $product['amount'] = 0;
                $product['price']['now_price'] = 0;
            } else {
                $product['amount'] = 1;
                $product['bargain_price'] = $product['price']['now_price'] - $price;
            }
            $integral = M('user')->where('id=' . $this->user['id'])->getField('integral');
            $this->assign('product', $product)->assign('integral', $integral);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function bargainConfirmationInfo()
    {
        if (!$_GET['pro_id']) {
            return show(0, '参数错误');
        }
        if (!$_GET['amount'] || $_GET['amount'] < 1) {
            return show(0, '数量参数错误');
        }
        if (!$_GET['key']) {
            return show(0, '规格参数错误');
        }
        if (!$_GET['name']) {
            return show(0, '联系人不能为空');
        }
        if (!$_GET['mobile']) {
            return show(0, '联系电话不能为空');
        }
        $this->display();
    }


    public function ajaxBargainConfirmationInfo()
    {
        if (!$_GET['pro_id']) {
            return show(0, '参数错误');
        } else {
            $id = $_GET['pro_id'];
        }
        if (!$_GET['amount'] || $_GET['amount'] < 1) {
            return show(0, '数量参数错误');
        } else {
            $amount = $_GET['amount'];
        }
        if (!$_GET['key']) {
            return show(0, '规格参数错误');
        } else {
            $key = $_GET['key'];
        }
        $product = D('Product')->find($id);
        $couponByUser = M('coupon')->where(['status' => 1, 'user_id' => $this->user['id']])->select();
        $couponList = [];
        //判断砍价
        $bargain = M('Bargain')->where(array('type' => 1, 'type_id' => $id, 'key' => $key,
            'start_time' => array('LT', time()), 'end_time' => array('GT', time())))->find();
        //付款，未付款，交易完成，退款中，已退款,付款之后不能再使用砍价额度
        $isBargainByOrder = M('order')->where(array('user_id' => $this->user['id'], 'product_id' => $product['id'],
            'bargain_id' => $bargain['id'], 'status' => array(array('EQ', 0), array('EQ', 1), array('EQ', 4), array('EQ', 6), array('EQ', 7), 'or'),
            'create_time' => array(array('GT', $bargain['start_time']), array('LT', $bargain['end_time']), 'and')))->find();
        if ($isBargainByOrder) {
            $price = 0;
        } else {
            $price = M('BargainRecord')->where(array('bargain_id' => $bargain['id'],
                'create_time' => array(array('LT', $bargain['end_time']), array('GT', $bargain['start_time']), 'and'),
                'share_user_id' => $this->user['id']))->sum('price');
        }
        //显示可用的代金券和优惠券
        $prices = json_decode($product['price'], true);
        $count = $prices[$key]['count'];
        if ($count < $amount) {
            return show(0, '数量超出库存范围，请返回上一页刷新之后再选择');
        }
        $originalPrice = $prices[$key]['original_price'];
        $totalPrice = $originalPrice * $amount - $price;
        foreach ($couponByUser as $key => $val) {
            //判断有否过期
            $offer = M('coupon_offer')->where(['id' => $val['offer_id']])->find();
            if ($offer['end_time'] <= time()) {
                M('coupon')->where(['id' => $val['id']])->save(['status' => 4]);
                continue;
            }
            //判断该商品是否可用
            if ($offer['type'] == 1 && $offer['type_id'] != $product['org_id']) {
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
        //判断积分是否超过总价
        if ($nowIntegral / 100 > $totalPrice) $nowIntegral = ceil($totalPrice) * 100;
        //type 1-课程 2-普通商品
        return show(1, '获取成功', ['couponList' => $couponList, 'totalPrice' => $totalPrice, 'nowIntegral' => $nowIntegral,
            'classNormal' => $prices[$_GET['key']]['class_normal'], 'title' => $product['title'], 'image' => $product['pic_url'], 'type' => $product['type']]);
    }


    //获取规格价钱
    public function getNormalPrice()
    {
        if (!$_GET['pro_id']) {
            return show(0, 'ID参数错误');
        }
        if (!$_GET['key']) {
            return show(0, '规格参数错误');
        }
        $product = M('product')->where(['id' => $_GET['pro_id']])->field('price,type')->find();
        $price = json_decode($product['price'], true);
        $normal = $price[$_GET['key']];
        //判断有否抢购价
        $rushPrice = M('bargain')->where(['type' => 2, 'type_id' => $_GET['pro_id'], 'key' => $_GET['key'],
            'start_time' => ['elt', time()], 'end_time' => ['gt', time()]])->getField('price');
        if ($rushPrice) {
            $normal['original_price'] = $normal['now_price'];
            $normal['now_price'] = $rushPrice;
        }
        $normal['type'] = (int)$product['type'];
        //判断是否砍价
        $isBargain = D('Bargain')->isBargain($_GET['pro_id'], $_GET['key']);
        if (!$normal) {
            return show(0, '获取失败');
        } else {
            return show(1, '获取成功', ['normal' => $normal, 'isBargain' => $isBargain]);
        }
    }

    public function createBargainPoster()
    {
        if (!$_GET['pro_id']) {
            return show(0, '参数错误');
        }
        if (!$_GET['key']) {
            return show(0, '参数错误');
        }
        $qrcodeUrl = $this->getQrcodeBargainUrl($_GET['pro_id'], $_GET['key'], $this->user['id']);
        $qrcodeFilename = uniqid(time()) . '.png';
        $qrcodePic = $this->createQrcode($qrcodeUrl, $qrcodeFilename);
        $productPic = M('product')->where(['id' => $_GET['pro_id']])->getField('pic_url');
        $headPic = $this->getwechathead($this->user['headimgurl']);
        $prices = M('product')->where(['id' => $_GET['pro_id']])->getField('price');
        $prices = json_decode($prices, true);
        $nowPrice = $prices[$_GET['key']]['now_price'];
        $classNormal = $prices[$_GET['key']]['class_normal'];
        $bargainPrice = M('bargain')->where(['type' => 1, 'type_id' => $_GET['pro_id'], 'key' => $_GET['key'],
            'start_time' => ['lt', time()], 'end_time' => ['gt', time()]])->getField('price');
        $lastPrice = $nowPrice - $bargainPrice;
        if (empty($bargainPrice)) {
            return show(0, '该砍价活动已过期');
        }
        $productTitle = M('product')->where(['id' => $_GET['pro_id']])->getField('title');
        $background = imagecreatetruecolor(900, 1600); // 背景图片
        $color = imagecolorallocate($background, 255, 255, 255); // 为真彩色画布创建白色背景，再设置为透明
        imagefill($background, 0, 0, $color);
        //判断商品图片类型
        $productPicPathInfo = pathinfo($productPic);
        switch (strtolower($productPicPathInfo['extension'])) {
            case 'jpg' :
            case 'jpeg' :
                $gdProductPic = imagecreatefromjpeg('.' . $productPic);
                break;
            case 'png' :
                $gdProductPic = imagecreatefrompng('.' . $productPic);
                break;
            default :
                $productPic = file_get_contents('.' . $productPic);
                $gdProductPic = imagecreatefromstring('.' . $productPic);
        }
        $gdQrcodePic = imagecreatefrompng($qrcodePic);
        $gdHeadPic = imagecreatefromjpeg($headPic);
        //商品图片位置
        imagecopyresized($background, $gdProductPic, 0, 0, 0, 0, 900, 1050, imagesx($gdProductPic), imagesy($gdProductPic));
        //头像位置
        imagecopyresized($background, $gdHeadPic, 30, 1250, 0, 0, 150, 150, imagesx($gdHeadPic), imagesx($gdHeadPic));
        //二维码地址
        imagecopyresized($background, $gdQrcodePic, 620, 1250, 0, 0, 250, 250, imagesx($gdQrcodePic), imagesx($gdQrcodePic));
        //价钱
        imagettftext($background, 30, 0, 30, 1130, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", $productTitle . ' ' . $classNormal);
        imagettftext($background, 30, 0, 500, 1200, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", $lastPrice . '元');
        imagettftext($background, 30, 0, 700, 1200, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", $nowPrice . '元');
        imagettftext($background, 30, 0, 700, 1180, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", "_______");
        //提示
        imagettftext($background, 30, 0, 220, 1300, imagecolorallocate($background, 0, 139, 139), "Font/msyh.ttc", $this->user['username']);
        imagettftext($background, 30, 0, 240, 1380, imagecolorallocate($background, 0, 139, 139), "Font/msyh.ttc", '亲，长按识别二维码');
        imagettftext($background, 30, 0, 240, 1430, imagecolorallocate($background, 0, 139, 139), "Font/msyh.ttc", "帮我砍价哈~");
        $posterDir = "Upload/" . date("Ymd", time()) . '/';
        if (!file_exists($posterDir)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($posterDir, 0777, true);
        }
        $posterFilename = "Upload/" . date('Ymd', time()) . "/" . uniqid(time()) . '.png';
        imagepng($background, $posterFilename);
        //删除头像和二维码文件
        unlink($qrcodePic);
        unlink($headPic);
        return show(1, '', '/' . $posterFilename);
    }

    function getQrcodeBargainUrl($proId, $key, $shareUserId)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create";
        $body = array(
            'action_name' => 'QR_LIMIT_STR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_str' => "isBargain_proId={$proId}_key={$key}_shareUserId={$shareUserId}",
                )
            )
        );
        $body = json_encode($body);
        //生成结果返回
        $result = post_weixin_curl(get_wxuser("g232238gc959"), $url, $body);
        return $result['url'];
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

    function getwechathead($url)
    {
        $new_file = "Upload/" . date('Ymd', time()) . "/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0777, true);
        }
        $filename = $new_file . time() . '.jpg';
        $head = $this->httpGet($url);
        file_put_contents('./' . $filename, $head);
        return "./" . $filename;
    }

    public function ajaxAppoint()
    {
        if (!$_GET['pro_id']) {
            return show(0, 'ID不能为空');
        }
        try {
            $product = M('product')->where('id=' . $_GET['pro_id'])->field('title,province,city,area,address')->find();
            return show(1, '', ['product' => $product]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function uploadAppoint()
    {
        if (!$_POST['pro_id']) {
            return show(0, 'ID不能为空');
        }
        if (!$_POST['name']) {
            return show(0, '姓名不能为空');
        }
        if (!$_POST['age']) {
            return show(0, '年龄不能为空');
        }
        if (!$_POST['mobile']) {
            return show(0, '手机号不能为空');
        }
        if (strtotime($_POST['book_time']) < time()) {
            return show(0, '预约时间必须大于今天，最好提前2-3日');
        }
        //判断是否有预约记录
        $isBook = M('book_record')->where(['type' => 1, 'type_id' => $_POST['pro_id'], 'user_id' => $this->user['id']])->find();
        if ($isBook) {
            $data = [
                'book_time' => strtotime($_POST['book_time']),
                'name' => $_POST['name'],
                'mobile' => $_POST['mobile'],
                'age' => $_POST['age'],
                'is_read' => 1
            ];
            if ($isBook['status'] != 4) {
                if ($isBook['count'] < 3) {
                    $data['count'] = $isBook['count'] + 1;
                    $id = D('BookRecord')->updateById($isBook['id'], $data);
                    if ($id) {
                        $this->sendMsgToOrgBook($isBook['id']);
                        return show(1, '为您更新了预约');
                    } else {
                        return show(0, '您之前预约过此课程了呢');
                    }
                } else {
                    return show(0, '您预约修改的次数太多，请联系管理员');
                }
            } else {
                return show(0, '您之前已经预约过了呢');
            }
        } else {
            $token = M('product')->where(array('id' => $_POST['pro_id']))->getField('token');
            $data = [
                'create_time' => time(),
                'type' => 1,
                'type_id' => $_POST['pro_id'],
                'name' => $_POST['name'],
                'mobile' => $_POST['mobile'],
                'age' => $_POST['age'],
                'user_id' => $this->user['id'],
                'status' => 1,
                'token' => $token,
                'book_time' => strtotime($_POST['book_time']),
                'is_read' => 1
            ];
            //没预约就提交数据
            $recordId = D('BookRecord')->insert($data);
            if ($recordId) {
                $sms = $this->sendMsgToOrg($recordId);
                if ($sms) {
                    return show(1, '预约成功');
                }
            } else {
                return show(0, '预约失败');
            }
        }
    }

    //课程列表
    public function ajaxCourseList()
    {
        if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id'])) {
            return show(0, 'ID不存在');
        }
        if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
            return show(0, '参数错误');
        }
        if ($_GET['class_id'] == 0) {
            $product = M('product')->where(['type' => 1, 'check_status' => 2, 'status' => 1])->order('sort desc')
                ->field("id,title,f_title,pic_url,type,price,status,tag,token")->limit($_GET['page'], 8)->select();
        } else {
            $product = M('product')->where(['class_id' => $_GET['class_id'], 'check_status' => 2, 'status' => 1])
                ->field("id,title,f_title,pic_url,type,price,status,tag,token")->order('sort desc')->limit($_GET['page'], 8)->select();
        }
        foreach ($product as $k => $v) {
            $tag = explode(' ', $product[$k]['tag']);
            $product[$k]['tagA'] = $tag[0] ?: '';
            $product[$k]['tagB'] = $tag[1] ?: '';
            $product[$k]['tagC'] = $tag[2] ?: '';
            $price = json_decode($product[$k]['price'], true);
            $product[$k]['original_price'] = reset($price)['original_price'];
            $product[$k]['now_price'] = reset($price)['now_price'];
            $product[$k]['logo'] = M('organization')->where(array('token' => $product[$k]['token']))->getField('picture');
        }
        if ($_GET['page'] == 0) {
            $class = M('product_class')->where(['type' => 1])->field('id,title')->order('sort desc')->select();
            return show(1, '', ['product' => $product, 'class' => $class]);
        } else {
            return show(1, '', ['product' => $product]);
        }
    }

}