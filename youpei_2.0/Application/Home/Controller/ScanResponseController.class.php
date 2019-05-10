<?php
namespace Home\Controller;

use Common\Controller\Wechat;
use Think\Controller;

/**
 *扫码关注异步处理
 **/
class ScanResponseController extends Controller
{
    public function createQrcode()
    {
//        $_POST = [
//            'user_id'=>1069,
//            'scene_id'=>1111,
//            'open_id'=>'oSFV6w-VMN-xdb7eM0pyIq608E8E',
////            'image'=>'/Upload/20171115095652.jpg',
//            'image'=>'/Public/images/box2.jpg',
//        ];
        $url = $this->getQrcodeByVipId($_POST['user_id'], $_POST['scene_id']);
        $name = uniqid(time()) . '.png';
        $filename = './Upload/' . $name;
        $logo = "http://" . $_SERVER['HTTP_HOST'] . $_POST['image'];
        $qrFilename = $this->qrcodeByPoster($url, $filename, true, $logo, 9, 'L', 2, true);
        $file_info = array(
            'filename' => "/{$qrFilename}", // 表单提交的文件(这里我指定的是该项目的根目录)
        );
        $wxuser = get_wxuser("g232238gc959");
        $access_token = get_weixin_access_token($wxuser, false);
        $media_url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$access_token}&type=image";
        $real_path = "{$_SERVER['DOCUMENT_ROOT']}{$file_info['filename']}";
        $postData = array("media" => "@{$real_path}", 'form-data' => $file_info);
        $result = self::http_url($media_url, $postData);
        $res = json_decode($result, true);
        $this->sendMsgByGm($res['media_id'], $_POST['open_id'], 'image');
        //调用删除接口
        $delete_url = "https://api.weixin.qq.com/cgi-bin/material/del_material?access_token={$access_token}";
        $postDelData = array('media_id' => $res['media_id']);
        $postDelData = json_encode($postDelData, true);
        //删除二维码和图片
        unlink($qrFilename);
        unlink($filename);
        $this->http_url($delete_url, $postDelData);
        echo 'finish';
    }

    function bargainForFriend()
    {
        if (!$_POST['open_id']) {
            return false;
        }
        if (!$_POST['pro_id']) {
            return false;
        }
        if (!$_POST['share_user_id']) {
            return false;
        }
        $userId = M('user')->where(["open_id" => $_POST['open_id']])->getField('id');
        if ($_POST['share_user_id'] == $userId) {
            return $this->sendMsgByGm("不能给自己扫码哦", $_POST['open_id']);
        }
        if (!$_POST['key']) {
            return false;
        }
        $bargain = D('Bargain')->isBargain($type_id = $_POST['pro_id'], $_POST['key']);
        $where['create_time'] = array(array('LT', $bargain['end_time']), array('GT', $bargain['start_time']), 'and');
        $isBargain = M('BargainRecord')->where($where)->where(array('share_user_id' => $_POST['share_user_id'],
            'bargain_id' => $bargain['id'], 'user_id' => $userId))->find();
        if ($isBargain) {
            return $this->sendMsgByGm("你已经帮他砍过价了呢!", $_POST['open_id']);
        }
        //未付款
        $isOrder = M('order')->where(array('user_id' => $_POST['share_user_id'], 'product_id' => $_POST['pro_id'],
            'bargain_id' => $bargain['id'], 'status' => array('neq', 0),
            'create_time' => array(array('GT', $bargain['start_time']), array('LT', $bargain['end_time']), 'and')))->getField('id');
        if (!$isOrder) {
            //砍价随机数 精确到小数点后2位
            $priceUpper = $bargain['price'] * 0.01;
            $priceLower = $bargain['price'] * 0.03;
            $price = substr($this->randFloat($priceUpper, $priceLower), 0, 4);
            $priceSum = M('BargainRecord')->where($where)->where(array('share_user_id' => $_POST['share_user_id'], 'bargain_id' => $bargain['id']))->sum('price');
            if ($priceSum >= $bargain['price']) {
                return $this->sendMsgByGm("人家已经完成砍价了呢:)", $_POST['open_id']);
            } elseif (($priceSum + $price) > $bargain['price']) {
                $price = $bargain['price'] - $priceSum;
            }
            $insertData = [
                'create_time' => time(),
                'bargain_id' => $bargain['id'],
                'user_id' => $userId,
                'share_user_id' => $_POST['share_user_id'],
                'price' => $price
            ];
            D('BargainRecord')->insert($insertData);
            $this->sendMsgByGm("你已经成功帮你的好友砍了" . $price . '元', $_POST['open_id']);
            $userPeople = D('BargainRecord')->bargainHelpByUser($_POST['share_user_id'], $bargain['id'], $bargain['start_time'], $bargain['end_time']);
            $userCount = count($userPeople);
            if ($userCount % 3 == 0) {
                $productToken = M('product')->where(array('id' => $_POST['pro_id']))->getField('token');
                $productTitle = M('product')->where(array('id' => $_POST['pro_id']))->getField('title');
                $prices = D('BargainRecord')->bargainHelpByPrice($_POST['share_user_id'], $bargain['id'], $bargain['start_time'], $bargain['end_time']);
                $user = D('User')->find($_POST['share_user_id']);
                //发送通知模板
                $first = '【优培圈】温馨提醒您的砍价进度';
                $keyword1 = $productTitle;
                $keyword2 = '已有' . $userCount . '个人帮您砍了' . $prices . '元了';
                $keyword3 = date("Y-m-d H:i:s", time());
                $remark = '请点击“详情”购买商品';
                $url = "http://{$_SERVER['HTTP_HOST']}/index.php/Product/bargain.html?pro_id=" . $_POST['pro_id'] . "&key=" . $_POST['key'] . "&token=" . $productToken;
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
                    'type_id' => $_POST['pro_id'],
                    'create_time' => time(),
                    'errmsg' => $result['errmsg'],
                    'errcode' => $result['errcode'],
                    'status' => $status,
                ];
                D('TemplateRecord')->insert($data);
            }
        } else {
            return $this->sendMsgByGm("你的好友已提交砍价订单了", $_POST['open_id']);
        }
    }

    function voteForFriend()
    {
        if (!$_POST['open_id']) {
            return false;
        }
        if (!$_POST['share_user_id']) {
            return false;
        }
        if (!$_POST['voteId']) {
            return false;
        }
        $thisUser = D('User')->getUserByOpenid($_POST['open_id']);
        if ($_POST['share_user_id'] == $thisUser['id']) {
            return $this->sendMsgByGm('不能给自己投票哦！', $_POST['open_id']);
        }
        //判断投票活动是否还有效
        $isVote = M('vote')->where(array('id' => $_POST['voteId'], 'work_start_time' => array('ELT', time()),
            'vote_end_time' => array('EGT', time()), 'check_status' => 2))->getField('id');
        if (empty($isVote)) {
            return $this->sendMsgByGm('该投票活动已失效', $_POST['open_id']);
        }
        //判断作品是否正常
        $isContribution = M('contribution_record')->where(['user_id' => $_POST['share_user_id'], 'status' => 2, 'vote_id' => $_POST['voteId']])->getField('id');
        if (empty($isContribution)) {
            return false;
        }
        //判断每日免费票数是否有效
        $userId = M('user')->where(["open_id" => $_POST['open_id']])->getField('id');
        $todayTime = strtotime(date("Y-m-d", time()));
        $todayQuota = M('vote_record')->where(['type' => 3, 'create_time' => ['egt', $todayTime], 'user_id' =>
            $userId])->sum('count');
        $todayQuota = 2 - $todayQuota >= 0 ? 2 - $todayQuota : 0;
        if ($todayQuota <= 0) {
            return $this->sendMsgByGm('你每天免费的票数不足了哦，赶快去获得更多票数吧', $_POST['open_id']);
        }
        //扣除每日票数
        $insertData = [
            'create_time' => time(),
            'user_id' => $userId,
            'type' => 3,
            'type_id' => $_POST['id'],
            'count' => $todayQuota,
            'is_expend' => 1,
        ];
        D('VoteRecord')->insert($insertData);
        M('contribution_record')->where(['id' => $isContribution])->setInc('vote_count', $todayQuota);
        $shareUsername = M('user')->where(['id' => $_POST['share_user_id']])->getField('username');
        return $this->sendMsgByGm('你已经为你的朋友' . $shareUsername . '的作品投了' . $todayQuota . '票', $_POST['open_id']);
    }

    function sendMsgByGm($content, $open_id, $type = 'text')
    {
        $wxuser = get_wxuser("g232238gc959");
        $access_token = get_weixin_access_token($wxuser, false);
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;
        $data = array(
            "touser" => $open_id,
            "msgtype" => $type,
            $type => array(
                $type == 'text' ? 'content' : 'media_id' => $content,
            ),
        );
        return json_decode(http_post($url, json_encode($data, JSON_UNESCAPED_UNICODE)), true);
    }

    function getQrcodeByVipId($userId, $sceneId)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create";
        $body = array(
            'action_name' => 'QR_LIMIT_STR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_str' => "shareId" . $userId . '_sceneId' . $sceneId,
                )
            )
        );
        $body = json_encode($body);
        //生成结果返回
        $result = post_weixin_curl(get_wxuser("g232238gc959"), $url, $body);
        return $result['url'];
    }

    function qrcodeByPoster($data, $filename, $picPath = false, $logo = false, $size = '4', $level = 'L', $padding = 2, $saveandprint = false)
    {
        import("Vendor.phpqrcode.phpqrcode");//引入工具包
        // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
        $path = $picPath ? $picPath : SITE_PATH . "\\Public\\qrcode"; //图片输出路径
        mkdir($path);

        //在二维码上面添加LOGO
        if (empty($logo) || $logo === false) { //不包含LOGO
            if ($filename == false) {
                \QRcode::png($data, false, $level, $size, $padding, $saveandprint); //直接输出到浏览器，不含LOGO
            } else {
                // $filename=$path.'/'.$filename; //合成路径
                \QRcode::png($data, $filename, $level, $size, $padding, $saveandprint); //直接输出到浏览器，不含LOGO
            }
        } else { //包含LOGO
            if ($filename == false) {
                //$filename=tempnam('','').'.png';//生成临时文件
                die('参数错误');
            } else {
                //生成二维码,保存到文件
                // $filename = $path . '\\' . $filename; //合成路径
            }
            \QRcode::png($data, $filename, $level, $size, $padding);
            $QR = imagecreatefromstring(file_get_contents($filename));
            $logo = imagecreatefromstring(file_get_contents($logo));

            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            imagecopyresampled($logo, $QR, 415, 1130, 0, 0, 265, 265, $QR_width, $QR_height);
//            imagecopyresampled($logo, $QR, 565, 1060, 0, 0, 0, 0, $QR_width, $QR_height);
            if ($saveandprint === true) {
//                $qrFilename = 'Qrcode/images/' . uniqid(time()) . '.png';
                $qrFilename = 'Upload/' . uniqid(time()) . '.png';
                imagepng($logo, $qrFilename);
                /*header("Content-type: image/png");  //      下面2句用来调试到页面上
                imagepng($logo);*/
                return $qrFilename;
            }
        }
        return $filename;
    }

    function http_url($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            @curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //异步跳转
    function request_by_curl($remote_server, $post_string = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    //随机数
    function randFloat($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public function unlinkImage()
    {
        unlink('.' . $_POST['image']);
    }
}

?>