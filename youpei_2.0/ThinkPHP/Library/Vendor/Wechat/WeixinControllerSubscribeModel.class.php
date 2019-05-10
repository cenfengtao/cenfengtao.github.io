<?php
/**
 * 关注
 * @author jxiao
 */
import("Vendor.Wechat.WeixinControllerModel");
use Common\Controller\Wechat;

class WeixinControllerSubscribeModel extends WeixinControllerModel
{

    public function _reply()
    {
        $weixin = new Wechat($this->token);
        $data = $this->data;
        $open_id = substr(json_encode($this->data['FromUserName']), 1, strlen(json_encode($this->data['FromUserName'])) - 2);
        M('user')->where(array('open_id' => $open_id))->save(array('attention' => 1));
        //判断是否扫码关注
        if (!empty($data['EventKey'])) {
            $key = $this->data['EventKey'];
            $user = D('User')->getUserByOpenid($data['FromUserName']);
            if (!$user) {
                $userData = [
                    'open_id' => $data['FromUserName'],
                    'create_time' => time(),
                    'source' => $key,
                    'token' => $this->token,
                ];
                $userId = D('User')->insert($userData);
            } else {
                $userId = $user['id'];
            }
            //判断是否砍价扫码
            if (strpos($key, 'isBargain') !== false) {
                $keyArr = explode('_', $key);
                $proId = substr($keyArr[2], 6);
                $proKey = substr($keyArr[3], 4);
                $shareUserId = substr($keyArr[4], 12);
                if (!empty($shareUserId) && empty($user['up_user_id'])) {
                    D('User')->updateById($userId, ['up_user_id' => $shareUserId]);
                }
                if ($user['id'] == $shareUserId) {
                    $weixin->response('不能扫自己的二维码哦！分享给家长朋友扫一扫吧！');
                }
                $this->request_by_curl("http://{$_SERVER['HTTP_HOST']}/index.php/ScanResponse/bargainForFriend?token=" .
                    $this->token, ['open_id' => $this->data['FromUserName'], 'pro_id' => $proId, 'key' => $proKey, 'share_user_id' => $shareUserId]);
                $weixin->response("<a href='http://{$_SERVER['HTTP_HOST']}/index.php?token=g232238gc959'>欢迎关注优培圈，更多精彩活动请点击</a>");
            }
            //判断是否投票
            if (strpos($key, 'isVote') !== false) {
                $keyArr = explode('_', $key);
                $voteId = substr($keyArr[2], 7);
                $shareUserId = substr($keyArr[3], 12);
                if (!empty($shareUserId) && empty($user['up_user_id'])) {
                    D('User')->updateById($userId, ['up_user_id' => $shareUserId]);
                }
                if ($user['id'] == $shareUserId) {
                    $weixin->response('不能扫自己的二维码哦！分享给家长朋友扫一扫吧！');
                }
                $this->request_by_curl("http://{$_SERVER['HTTP_HOST']}/index.php/ScanResponse/voteForFriend?token=" . $this->token,
                    ['open_id' => $this->data['FromUserName'], 'voteId' => $voteId, 'share_user_id' => $shareUserId]);
                $isContribution = M('contribution_record')->where(['user_id' => $user['id'], 'vote_id' => $voteId])->getField('id');
                if ($isContribution) {
                    $weixin->response("<a href='http://{$_SERVER['HTTP_HOST']}/index.php/Vote/userWorks?vote_id={$voteId}&token={$this->token}&share_user_id={$shareUserId}'>赶快分享你的作品，邀请你的朋友为你助力！</a>");
                } else {
                    $weixin->response("<a href='http://{$_SERVER['HTTP_HOST']}/index.php/Vote/contribution?vote_id={$voteId}&token={$this->token}&share_user_id={$shareUserId}&is_share=1'>点击投稿参加我们的活动，即可为你朋友的作品再助力9票！</a>");
                }
            }
            //判断是否团购
            if (strpos($key, 'isGroup')!== false) {
                $keyArr = explode('_', $key);
                $groupId = substr($keyArr[2], 8);
                $shareUserId = substr($keyArr[3], 12);
                if (!empty($shareUserId) && empty($user['up_user_id'])) {
                    D('User')->updateById($userId, ['up_user_id' => $shareUserId]);
                }
                if ($user['id'] == $shareUserId) {
                    $weixin->response('不能扫自己的二维码哦！分享给家长朋友扫一扫吧！');
                }
                $weixin->response("<a href='http://{$_SERVER['HTTP_HOST']}/index.php/Groups/getGroup?id={$groupId}&token={$this->token}&shareUserId={$shareUserId}'>点击去参加优惠团购活动咯</a>");
            }
            //判断是否有分享者
            if (strpos($key, 'shareId') !== false) {
                $keyArr = explode('_', $key);
                $shareUserId = substr($keyArr[1], 7);
                $sceneId = substr($keyArr[2], 7);
                if (!empty($shareUserId) && empty($user['up_user_id'])) {
                    D('User')->updateById($userId, ['up_user_id' => $shareUserId]);
                }
            } else {
                $shareUserId = 0;
                $sceneId = substr($key, 8);
            }
//            //判断该用户是否已存在
//            $user = D('User')->getUserByOpenid($data['FromUserName']);
//            if (!$user) {
//                $userData = [
//                    'open_id' => $data['FromUserName'],
//                    'create_time' => time(),
//                    'source' => $key,
//                    'token' => $this->token,
//                    'up_user_id' => $shareUserId,
//                ];
//                $userId = D('User')->insert($userData);
//            } else if ($user['id'] == $shareUserId) {
//                $weixin->response('不能扫自己的二维码哦');
//            } else {
//                $userId = $user['id'];
//            }
            $scanReply = D('ScanReply')->findByToken($this->token, $sceneId);
            //判断该用户是否曾有该场景扫码记录
            sleep(1);
            $isScan = M('QrcodeRecord')->where(array("user_id" => $userId, 'scene_id' => $sceneId, 'token' => $this->token))->find();
            if ($isScan && !empty($isScan)) {
                //判断是否有海报
                if ($scanReply['image'] && !empty($scanReply['image'])) {
                    $this->request_by_curl("http://{$_SERVER['HTTP_HOST']}/index.php/ScanResponse/createQrcode/token/" . $this->token,
                        array('user_id' => $userId, 'open_id' => $this->data['FromUserName'], 'image' => $scanReply['image'], 'scene_id' => $sceneId));
                }
                $weixin->response($scanReply['scan_reply']);
            }
            //添加到扫码记录
            $maxNumBySceneId = D('QrcodeRecord')->getMaxNumBySceneId($sceneId);
            $recordData = [
                'user_id' => $userId,
                'scene_id' => $sceneId,
                'share_user_id' => $shareUserId,
                'create_time' => time(),
                'token' => $this->token,
                'serial_number' => $maxNumBySceneId + 1,
            ];
            D('QrcodeRecord')->insert($recordData);
            //根据模板信息回复
            if (!$scanReply || empty($scanReply)) {
                //默认关注回复
                $subscribeReply = M('organization')->where("token='{$this->token}'")->getField('subscribe_reply');
                if (!$subscribeReply || empty($subscribeReply)) {
                    $weixin->response('欢迎关注');
                } else {
                    $weixin->response($subscribeReply);
                }
            }
            //判断有否分享用户
            if ($shareUserId && !empty($shareUserId)) {
                //获取当前场景该分享用户的助力次数
                $shareCount = D('QrcodeRecord')->getCountByUserId($sceneId, $shareUserId, $this->token);
                $shareUserOpenId = M('user')->where("id={$shareUserId}")->getField('open_id');
                $wxuser = get_wxuser($this->token);
                //第一次通知
                if ($scanReply['template_a_times'] && $scanReply['template_a_times'] > 0 && $scanReply['template_a_times'] == $shareCount) {
                    $first = $scanReply['template_a_title'];
                    $keyword1 = $scanReply['template_a_first'];
                    $keyword2 = $scanReply['template_a_second'];
                    $keyword3 = $scanReply['template_a_third'];
                    $remark = $scanReply['template_a_remark'];
                    $url = $scanReply['template_a_url'];
                    $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__REMARK__');
                    $infoFormat = array($shareUserOpenId, $url, $first, $keyword1, $keyword2, $keyword3, $remark);
                    execute_public_template('INFORM', $templeFormat, $infoFormat, $wxuser);
                }
                //第二次通知
                if ($scanReply['template_b_times'] && $scanReply['template_b_times'] > 0 && $scanReply['template_b_times'] == $shareCount) {
                    $first = $scanReply['template_b_title'];
                    $keyword1 = $scanReply['template_b_first'];
                    $keyword2 = $scanReply['template_b_second'];
                    $keyword3 = $scanReply['template_b_third'];
                    $remark = $scanReply['template_b_remark'];
                    $url = $scanReply['template_b_url'];
                    $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__REMARK__');
                    $infoFormat = array($shareUserOpenId, $url, $first, $keyword1, $keyword2, $keyword3, $remark);
                    execute_public_template('INFORM', $templeFormat, $infoFormat, $wxuser);
                }
                //第三次通知
                if ($scanReply['template_c_times'] && $scanReply['template_c_times'] > 0 && $scanReply['template_c_times'] == $shareCount) {
                    $first = $scanReply['template_c_title'];
                    $keyword1 = $scanReply['template_c_first'];
                    $keyword2 = $scanReply['template_c_second'];
                    $keyword3 = $scanReply['template_c_third'];
                    $remark = $scanReply['template_c_remark'];
                    $url = $scanReply['template_c_url'];
                    $templeFormat = array('__OPENID__', '__URL__', '__FIRST__', '__KEYWORD1__', '__KEYWORD2__', '__KEYWORD3__', '__REMARK__');
                    $infoFormat = array($shareUserOpenId, $url, $first, $keyword1, $keyword2, $keyword3, $remark);
                    execute_public_template('INFORM', $templeFormat, $infoFormat, $wxuser);
                }
            }
            //判断是否有海报
            if ($scanReply['image'] && !empty($scanReply['image'])) {
                $this->request_by_curl("http://{$_SERVER['HTTP_HOST']}/index.php/ScanResponse/createQrcode/token/" . $this->token,
                    array('user_id' => $userId, 'open_id' => $this->data['FromUserName'], 'image' => $scanReply['image'], 'scene_id' => $sceneId));
            }
            $weixin->response($scanReply['scan_reply']);
        } else {
            //默认关注回复
            $subscribeReply = M('organization')->where("token='{$this->token}'")->getField('subscribe_reply');
            if (!$subscribeReply || empty($subscribeReply)) {
                $weixin->response('欢迎关注');
            } else {
                $weixin->response($subscribeReply);
            }
        }
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
}

?>