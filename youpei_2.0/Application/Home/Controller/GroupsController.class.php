<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

class GroupsController extends BaseController
{
    public function index()
    {
        $this->title = "团购列表";
        $this->display();
    }

    public function ajaxIndex()
    {
        try {
            //先把过期的团购修改状态
            M('group_product')->where(array('status' => array('eq', 1), 'end_time' => array('ELT', time())))->save(array('status' => 6));
            //课程分类
            $classifyCurriculum = M('product_class')->where(array('type' => 1))->order('sort desc')->select();
            //商品分类
            $classifyProduct = M('product_class')->where(array('type' => 2))->order('sort desc')->select();
            //课程团购
            $groupCurriculum = M('group_product')->where(array('status' => [array('eq', 1), array('eq', 2), 'or'], 'check_status' => 2, 'type' => 1, 'is_show' => 1))->limit(10)->select();
            if ($groupCurriculum) {
                foreach ($groupCurriculum as $k => $v) {
                    $groupCurriculumCount[$k] = M('order')->where(array('group_id' => array('eq', $v['id']),
                        'status' => [array('eq', 1), array('eq', 4), 'or']))->sum('amount');
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
            }
            //商品团购
            $groupProduct = M('group_product')->where(array('status' => [array('eq', 1), array('eq', 2), 'or'], 'check_status' => 2, 'type' => 2, 'is_show' => 1))->limit(10)->select();
            if ($groupProduct) {
                foreach ($groupProduct as $key => $val) {
                    $groupProductCount[$key] = M('order')->where(array('group_id' => array('eq', $val['id']),
                        'status' => [array('eq', 1), array('eq', 4), 'or']))->sum('amount');
                    if (!$groupProductCount[$key]) {
                        $groupProduct[$key]['groupCount'] = 0;
                    } else {
                        $groupProduct[$key]['groupCount'] = $groupProductCount[$key];
                    }
                    $groupProduct[$key]['logo'] = M('organization')->where(array('token' => $val['token']))->getField('picture');
                    $groupProductTag = explode(' ', $val['tag']);
                    $groupProduct[$key]['tagA'] = $groupProductTag[0] ?: '';
                    $groupProduct[$key]['tagB'] = $groupProductTag[1] ?: '';
                }
            }
            return show(1, '', ['classifyCurriculum' => $classifyCurriculum, 'classifyProduct' => $classifyProduct,
                'groupCurriculum' => $groupCurriculum, 'groupProduct' => $groupProduct]);
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    //根据分类调用团购商品
    public function getGroupByClass()
    {
        $groupProduct = [];
        if ($_GET['class_id']) {
            $groupProduct = M('group_product')->where(array('status' => [array('eq', 1), array('eq', 2), 'or'],
                'check_status' => 2, 'class_id' => $_GET['class_id'], 'is_show' => 1))->limit(10)->select();
        }
        if ($_GET['type']) {
            $groupProduct = M('group_product')->where(array('status' => [array('eq', 1), array('eq', 2), 'or'],
                'check_status' => 2, 'type' => $_GET['type'], 'is_show' => 1))->limit(10)->select();
        }
        if ($groupProduct) {
            foreach ($groupProduct as $k => $v) {
                $groupProductCount[$k] = M('order')->where(array('group_id' => array('eq', $v['id']),
                    'status' => [array('eq', 1), array('eq', 4), 'or']))->sum('amount');
                if (!$groupProductCount[$k]) {
                    $groupProduct[$k]['groupCount'] = 0;
                } else {
                    $groupProduct[$k]['groupCount'] = $groupProductCount[$k];
                }
                $groupProduct[$k]['logo'] = M('organization')->where(array('token' => $v['token']))->getField('picture');
                $groupProductTag = explode(' ', $v['tag']);
                $groupProduct[$k]['tagA'] = $groupProductTag[0] ?: '';
                $groupProduct[$k]['tagB'] = $groupProductTag[1] ?: '';
                if ($v['type'] == 2) {
                    $groupProduct[$k]['tagC'] = $groupProductTag[2] ?: '';
                }
            }
        }
        return show(1, '', ['groupProduct' => $groupProduct]);
    }


    public function loadingGroup()
    {
        if (!$_GET['page']) {
            return show(0, '分页数据不能为空');
        }
        $groupProduct = [];
        if ($_GET['class_id']) {
            $groupProduct = M('group_product')->where(array('status' => [array('eq', 1), array('eq', 2), 'or'],
                'check_status' => 2, 'class_id' => $_GET['class_id']))->limit($_GET['page'], 6)->select();
        }
        if ($_GET['type']) {
            $groupProduct = M('group_product')->where(array('status' => [array('eq', 1), array('eq', 2), 'or'],
                'check_status' => 2, 'type' => $_GET['type']))->limit($_GET['page'], 6)->select();
        }
        if ($groupProduct) {
            foreach ($groupProduct as $k => $v) {
                $groupProductCount[$k] = M('order')->where(array('group_id' => array('eq', $v['id']),
                    'status' => [array('eq', 1), array('eq', 4), 'or']))->sum('amount');
                if (!$groupProductCount[$k]) {
                    $groupProduct[$k]['groupCount'] = 0;
                } else {
                    $groupProduct[$k]['groupCount'] = $groupProductCount[$k];
                }
                $groupProduct[$k]['logo'] = M('organization')->where(array('token' => $v['token']))->getField('picture');
                $groupProductTag = explode(' ', $v['tag']);
                $groupProduct[$k]['tagA'] = $groupProductTag[0] ?: '';
                $groupProduct[$k]['tagB'] = $groupProductTag[1] ?: '';
                if ($v['type'] == 2) {
                    $groupProduct[$k]['tagC'] = $groupProductTag[2] ?: '';
                }
            }
        }
        return show(1, '', ['groupProduct' => $groupProduct]);
    }


    public function getGroup()
    {
        if ($_POST) {
            if (!$_POST['group_id'] || empty($_POST['group_id'])) {
                return show(0, 'ID不能为空');
            }
            if (!$_POST['content'] || empty($_POST['content'])) {
                return show(0, '咨询内容不能为空');
            }
            $data = array(
                'group_id' => $_POST['group_id'],
                'user_id' => $this->user['id'],
                'create_time' => time(),
                'type' => 1,
                'status' => 1,
                'content' => $_POST['content'],
                'token' => $this->token,
            );
            $id = D('ProductComment')->insert($data);
            $find = D('ProductComment')->find($id);
            $headImg = M('user')->where("id={$find['user_id']}")->field('headimgurl')->find();
            $find['headImg'] = $headImg['headimgurl'];
            if (!$find || empty($find)) {
                $this->ajaxReturn(array('status' => 0, 'msg' => '咨询失败'));
            }
            $this->ajaxReturn(array('status' => 1, 'msg' => '咨询成功', 'data' => $find));

        } else {
            $this->title = "团购详细";
            if (!$_GET['id'] || !is_numeric($_GET['id'])) {
                $this->error('ID参数错误');
            }
            if (!$_GET['shareUserId']) {
                $_GET['shareUserId'] = 0;
            }
            try {
                $group = D('GroupProduct')->find($_GET['id']);
                //增加浏览记录
                $this->addFootprint(3, $group['id']);
                $tags = explode(' ', $group['tag']);
                $group['tagA'] = $tags[0] ?: '';
                $group['tagB'] = $tags[1] ?: '';
                $group['tagC'] = $tags[2] ?: '';
                //评论列表
                $commentList = D('ProductComment')->getCommentByFatherGroupId(0, 0, $_GET['id']);
                foreach ($commentList as $k => $v) {
                    $commentList[$k]['child'] = M('ProductComment')->where(array('type_id' => $v['id'], 'group_id' => $_GET['id'], 'status' => 1))->select();
                    $commentList[$k]['headImg'] = D('user')->getHeadById($v['user_id']);
                    foreach ($commentList[$k]['child'] as $ke => $va) {
                        //回复
                        if ($va['is_gm'] == 2) {
                            //待机构管理员完善之后需修改
                            //客服头像
                            $picture = M('organization')->field('picture')->where(array('token' => $this->token))->find();
                            $commentList[$k]['child'][$ke]['headImg'] = $picture['picture'];
                        } elseif ($va['is_gm'] == 1) {
                            $commentList[$k]['child'][$ke]['headImg'] = D('user')->getHeadById($va['user_id']);
                        }
                        //被回复
                        $userId = M('ProductComment')->where(array('id' => $commentList[$k]['child'][$ke]['father_id']))->find();
                        if ($userId['is_gm'] == 2) {
                            $pictures = M('organization')->field('picture')->where(array('token' => $userId['token']))->find();
                            $commentList[$k]['child'][$ke]['headImgs'] = $pictures['picture'];
                        } else if ($userId['is_gm'] == 1) {
                            $commentList[$k]['child'][$ke]['headImgs'] = D('user')->getHeadById($userId['user_id']);
                        }
                    }
                }
                //购买商品的所有用户头像
                $userId = D('Order')->getGroupByGroupRecordId($_GET['id']);
                $headImg = [];
                foreach ($userId as $k => $v) {
                    $headImg[$k]['headImg'] = D('User')->getHeadById($v['user_id']);
                    $headImg[$k]['nickname'] = M('user')->where(['id' => $v['user_id']])->getField('username');
                }
                $userCount = M('order')->where(array('group_id' => $_GET['id'],
                    'status' => array(array('eq', 1), array('eq', 4), 'OR')))->sum('amount');
                $organization = D('Organization')->findByToken($group['token']);
                $this->assign('group', $group)->assign('commentList', $commentList)->assign('shareUserId', $_GET['shareUserId']);
                $this->assign('headImg', $headImg)->assign('userCount', $userCount)->assign('organization', $organization);
                $this->display();
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }

    public function confirmationInfo()
    {
        if (!$_GET['groupId']) {
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
        if (!$_GET['groupId']) {
            return show(0, '参数错误');
        }
        if (!$_GET['amount'] || $_GET['amount'] < 1) {
            return show(0, '数量参数错误');
        }
        $group = M('group_product')->where(['id' => $_GET['groupId']])->field('description,cost', true)->find();
        $org = D('Organization')->findByToken($group['token']);
        $price = $group['price'];
        //代金券
        $couponByUser = M('coupon')->where(['status' => 1, 'user_id' => $this->user['id'], 'coupon_type' => 2])->select();
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
            $couponList[$key] = [
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
        return show(1, '获取成功', ['couponList' => $couponList, 'totalPrice' => $totalPrice, 'nowIntegral' => $nowIntegral,
            'title' => $group['title'], 'image' => $group['image'], 'unit' => $group['unit'], 'type' => $group['type']]);
    }


    //发起组团
    public function addGroup()
    {
        $this->title = "愿望单";
        //筛选出有可团购的机构列表
        $tokenList = M('group_product')->distinct(true)->field('token')->where(array('status' => 1, 'check_status' => 2))->select();
        $orgList = [];
        foreach ($tokenList as $k => $v) {
            $orgList[$k] = M('organization')->where(array('token' => $v['token']))->field('token,org_name')->find();
        }
        $this->assign('orgList', $orgList);
        $this->display();
    }

    //根据机构id显示团购列表
    public function getGroupsByOrgId()
    {
        if (!$_POST['token'] || empty($_POST['token'])) {
            return show(0, '请选择机构');
        }
        $groups = D('GroupProduct')->getGroupsByToken($_POST['token'], 'id,title');
        return show(1, '获取成功', $groups);
    }

    public function getGroupById()
    {
        if (!$_POST['id'] || empty($_POST['id'])) {
            return show(0, 'ID参数错误');
        }
        $product = D('GroupProduct')->find($_POST['id']);
        $product['class_time'] = json_decode($product['class_time'], true);
        $product['group_time'] = [
            'start_time' => date('Y/m/d', time()),
            'end_time' => date('Y/m/d', strtotime('+2 week', time()))
        ];
        return show(1, '获取成功', $product);
    }

    //发起团购
    public function launchGroup()
    {
        if (!$_POST['group_id'] || !is_numeric($_POST['group_id'])) {
            return show(0, '参数错误');
        }
        //判断是否有相同的团购
        $isGroup = D('GroupRecord')->isSameGroup($_POST['group_id']);
        if ($isGroup && !empty($isGroup)) {
            return show(0, '已经有该课程的团购');
        }
        $insertData = [
            'user_id' => $this->user['id'],
            'create_time' => time(),
            'group_id' => $_POST['group_id'],
            'end_time' => strtotime('+2 week', time()),
            'status' => 1,
        ];
        $id = D('GroupRecord')->insert($insertData);
        if ($id) {
            return show(1, '发起成功');
        } else {
            return show(0, '发起失败');
        }
    }

    //我的团
    public function myGroups()
    {
        $this->title = "我的团";
        $groupIds = M('order')->field('id,group_id')->where(array('status' => array(array('eq', 1), array('eq', 4), 'OR'),
            'user_id' => $this->user['id'], 'group_id' => array('neq', 0)))->order('create_time desc')->select();
        $groupList = [];
        foreach ($groupIds as $k => $v) {
            $group = D('GroupProduct')->find($v['group_id']);
            $orderCount = M('order')->where(['status' => array(['eq', 1], ['eq', 4], 'or'), 'group_id' =>
                $v['group_id']])->sum('amount');
            $groupList[$k] = [
                'order_id' => $v['id'],
                'title' => $group['title'],
                'status' => $group['status'],
                'image' => $group['image'],
                'original_price' => $group['original_price'],
                'price' => $group['price'],
                'id' => $group['id'],
                'type' => $group['type'],
                'end_time' => $group['end_time'],
                'min_people' => $group['min_people'],
                'max_people' => $group['max_people'],
                'orderCount' => $orderCount,
                'tags' => explode(' ', $group['tag']),
                'unit' => $group['unit'],
            ];
        }
        $this->assign('groupList', $groupList);
        $this->display();
    }

    //用户之间留言对话
    public function comment()
    {
        if ($_POST) {
            if (!$_POST['group_id'] || empty($_POST['group_id'])) {
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
                'group_id' => $_POST['group_id'],
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

    //添加愿望单
    public function addHope()
    {
        if (!$_POST['class_time'] || empty($_POST['class_time'])) {
            return show(0, '上课时间错误');
        }
        if (!$_POST['content'] || empty($_POST['content'])) {
            return show(0, '请写出你的愿望');
        }
        $classTime = [];
        //编译成json格式存进数据库
        foreach ($_POST['class_time'] as $k => $v) {
            $classTime[$k]['class_time_day'] = $v[0];
            $classTime[$k]['class_start_hour'] = $v[1];
            $classTime[$k]['class_end_hour'] = $v[2];
            if (empty($classTime[$k]['class_time_day']) || empty($classTime[$k]['class_start_hour']) || empty($classTime[$k]['class_end_hour'])) {
                return show(0, '上课时间设置有误，请重新核实');
            }
        }
        $classTime = json_encode($classTime);
        $insertData = [
            'create_time' => time(),
            'user_id' => $this->user['id'],
            'type' => 1,
            'class_time' => $classTime,
            'content' => $_POST['content'],
            'mobile' => '',
        ];
        try {
            $id = D('Hope')->insert($insertData);
            if ($id) {
                return show(1, '你的愿望已提交');
            } else {
                return show(0, '提交失败');
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    public function checkOrderInformation()
    {
        if (!$_GET['groupId'] || !is_numeric($_GET['groupId'])) {
            $this->error('获取不到该团购信息');
        }
        try {
            $group = D('GroupProduct')->find($_GET['groupId']);
            //判断可购买数量
            $nowCount = M('order')->where(['group_id' => $_GET['groupId'], 'status' => array(['eq', '0'], ['eq', '1'], ['eq', '4'], 'or')])->sum('amount');
            $integral = M('user')->where('id=' . $this->user['id'])->getField('integral');
            $this->assign('group', $group)->assign('nowCount', $group['max_people'] - $nowCount)->assign('integral', $integral);
            $this->display();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function createGroupPoster()
    {
        if (!$_GET['id']) {
            return show(0, '参数错误');
        }
        $qrcodeUrl = $this->getQrcodeUrl($_GET['id'], $this->user['id']);
        $qrcodeFilename = uniqid(time()) . '.png';
        $qrcodePic = $this->createQrcode($qrcodeUrl, $qrcodeFilename);
        $productImage = M('group_product')->where(['id' => $_GET['id']])->getField('image');
        $productTitle = M('group_product')->where(['id' => $_GET['id']])->getField('title');
        $contributionUsername = M('contribution_record')->where(['user_id' => $this->user['id'], 'vote_id' => $_GET['vote_id']])->getField('username');
        $background = imagecreatetruecolor(900, 1600); // 背景图片
        $color = imagecolorallocate($background, 255, 255, 255); // 为真彩色画布创建白色背景，再设置为透明
        imagefill($background, 0, 0, $color);
        //判断商品图片类型
        $productImagePathInfo = pathinfo($productImage);
        switch (strtolower($productImagePathInfo['extension'])) {
            case 'jpg' :
            case 'jpeg' :
                $gdImage = imagecreatefromjpeg('.' . $productImage);
                break;
            case 'png' :
                $gdImage = imagecreatefrompng('.' . $productImage);
                break;
            default :
                $voteImage = file_get_contents('.' . $productImage);
                $gdImage = imagecreatefromstring('.' . $voteImage);
        }
        $gdQrcodePic = imagecreatefrompng($qrcodePic);
        //作品图片位置
        imagecopyresized($background, $gdImage, 40, 40, 0, 0, 820, 990, imagesx($gdImage), imagesy($gdImage));
        //二维码地址
        imagecopyresized($background, $gdQrcodePic, 603, 1165, 0, 0, 250, 260, imagesx($gdQrcodePic), imagesx($gdQrcodePic));
        //商品名
        imagettftext($background, 30, 0, 30, 1190, imagecolorallocate($background, 70, 130, 180), "Font/msyh.ttc", $productTitle);
        //作者
        imagettftext($background, 30, 0, 260, 1275, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", $contributionUsername);
        imagettftext($background, 28, 0, 30, 1380, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", $this->user['username'] . '邀请你参加团购活动！');
        imagettftext($background, 28, 0, 30, 1450, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", '请识别右侧二维码享受优惠吧！');
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

    function getQrcodeUrl($id, $shareUserId)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create";
        $body = array(
            'action_name' => 'QR_LIMIT_STR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_str' => "isGroup_groupId={$id}_shareUserId={$shareUserId}",
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
}