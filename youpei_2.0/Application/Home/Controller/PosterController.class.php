<?php
namespace Home\Controller;

use Think\Controller;

require_once __DIR__ . '/../../../ThinkPHP/Library/Org/Util/JSDDK.class.php';

class PosterController extends BaseController
{
    /**
     * 拼接图片
     * $photo:边框内的图片绝对路径：win:E:\xamp\htdocs\news\images/pic.png
     *                           linux:/usr/local/apache/htdocs/site/images/pic.png
     * $kuang:边框路径：与$photo格式相同
     */
    public function index()
    {
        if (!$_GET['style_id']) {
            return show(0, '参数错误');
        }
        //设置默认分享信息
        $shareData = [
            'share_title' => '【优培圈】- 教师节感恩照',
            'share_desc' => "快来定制您的专属感恩照，分享您对老师的感激与祝福吧！",
            'share_url' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER["QUERY_STRING"] . '&share_user_id=' . $this->user['id'] . '&token=' . $this->token,
            'share_img' => 'http://www.youpei-exc.com/Public/images/logo_m.png',
        ];
        $this->assign('shareData', $shareData);
        $this->display();
    }

    public function qrcodeByPoster()
    {
        if (!$_POST['id']) {
            return show(0, 'ID不能为空');
        }
        $data = M('PosterStyle')->where(array('id' => $_POST['id']))->find();
        $_POST['background'] = $data['image'];
        $background = $_SERVER['DOCUMENT_ROOT'] . $_POST['background'];
        if ($_POST['image'] && $_POST['image_type']) {
            //查询出图片坐标
            $imagePositions = M('PosterStyle')->where(array('id' => $_POST['id']))->getField('image_position');
            $imagePositions = json_decode($imagePositions, true);
            $imagePosition = [];
            foreach ($imagePositions as $val) {
                foreach ($val as $k => $v) {
                    if ($k == $_POST['image_type']) {
                        $imagePosition = $v;
                    }
                }
            }
            $imagePosition = explode(' ', $imagePosition);
            //匹配出图片的格式
            if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['image'], $result)) {
                return show(0, '图片格式不正确');
            }
            $type = $result[2];
            $new_file = "Upload/Poster/" . date('Ymd', time()) . "/";
            if (!file_exists($new_file)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0777, true);
            }
            $filename = $new_file . time() . ".{$type}";
            if (!file_put_contents($filename, base64_decode(str_replace($result[1], '', $_POST['image'])))) {
                return show(0, '文件保存有误');
            }
            $filename = $_SERVER['DOCUMENT_ROOT'] . '/' . $filename;
            $filename = $this->circle_img($filename);
            $QR = imagecreatefromstring(file_get_contents($filename));
            $background = imagecreatefromstring(file_get_contents($background));
            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            imagecopyresampled($background, $QR, $imagePosition[0], $imagePosition[1], 0, 0, $imagePosition[2], $imagePosition[3], $QR_width, $QR_height);
            if (!empty($_POST['text']) || is_array($_POST['text'])) {
                foreach ($_POST['text'] as $k => $v) {
                    $textData[$k] = explode(':', $_POST['text'][$k]);
                    $textType[$k] = $textData[$k][0];
                    $text[$k] = $textData[$k][1];
                    //查询出文字坐标
                    $textPositions = M('PosterStyle')->where(array('id' => $_POST['id']))->getField('text_position');
                    $textPositions = json_decode($textPositions, true);
                    $textPosition = [];
                    foreach ($textPositions as $key => $val) {
                        foreach ($val as $ke => $va) {
                            if ($textType[$k] == $ke) {
                                $textPosition[$k] = explode(' ', $va);
                                if (count($text[$k]) > $textPosition[$k][3]) {
                                    return show(0, $ke . '的字数不能超过' . $textPosition[$k][3] . '个字');
                                }
                            }
                        }
                    }
                    $color = explode(' ', $data['color']);
                    $R = $color[0];
                    $G = $color[1];
                    $B = $color[2];
                    $font = 'Font/msyh.ttc';
                    imagettftext($background, $textPosition[$k][0], 0, $textPosition[$k][1], $textPosition[$k][2], imagecolorallocate($background, $R, $G, $B), $font, $text[$k]);
                }
            }

            $qrFilename = "Upload/Poster/" . date('Ymd', time()) . "/" . uniqid(time()) . '.png';
            imagepng($background, $qrFilename);
            return show(1, '', $qrFilename);
//                var_dump($qrFilename);exit;
            /*Header("Content-type: image/png");  //      下面2句用来调试到页面上
            imagepng($background);
            return $qrFilename;*/
        }
    }

    public function qrcodeFirst()
    {
        if (!$_POST['style_id']) {
            return show(0, '参数错误');
        }
        $data['data'] = D('PosterStyle')->find($_POST['style_id']);
        $data['imageData'] = M('PosterStyle')->order('create_time desc')->limit(5)->field('id,title,image')->select();
        $imageData = substr($data['data']['image_position'], 0, -1);
        $imageStyle = ltrim($imageData, "{");
        $imageAr = explode(',', $imageStyle);
        foreach ($imageAr as $k => $v) {
            $imageArr[$k] = substr(substr(substr($imageAr[$k], 4), 0, -1), 1);
            $imageEnd[$k] = explode(':', $imageArr[$k]);
            $data['data']['image_title'][$k] = $imageEnd[$k][0];
        }
        $textData = substr($data['data']['text_position'], 0, -1);
        $textStyle = ltrim($textData, "{");
        $textAr = explode(',', $textStyle);
        foreach ($textAr as $k => $v) {
            $textArr[$k] = substr(substr(substr($textAr[$k], 4), 0, -1), 1);
            $textEnd[$k] = explode(':', $textArr[$k]);
            $data['data']['text_title'][$k] = $textEnd[$k][0];
            $textPosition[$k] = explode(' ', substr($textEnd[$k][1], 0, -1));
            $data['data']['fontCount'][$k] = $textPosition[$k][3];
            $data['data']['message'][$k] = '"' . $textPosition[$k][4] . '"';
        }
        return show(1, '获取成功', $data);
    }

    public function qrcodeBack()
    {
        $filename = "D:/phpStudy/WWW/youpei_2.0/Public/images/1496288270.png";
        $background = "C:/Users/kato/Desktop/beijingtu.png";
        $QR = imagecreatefromstring(file_get_contents($filename));
        $background = imagecreatefromstring(file_get_contents($background));
        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);
        imagecopyresampled($background, $QR, 100, 200, 0, 0, 400, 400, $QR_width, $QR_height);
        $qrFilename = 'Upload/' . uniqid(time()) . '.png';
//                imagepng($background, $qrFilename);
        Header("Content-type: image/png");  //      下面2句用来调试到页面上
        imagepng($background);
        return $qrFilename;
    }

    function circle_img($imgpath)
    {
        $ext = pathinfo($imgpath);
        $src_img = null;
        $file = "Upload/Poster/" . date('Ymd', time()) . "/" . uniqid(time()) . '.png';
        switch ($ext['extension']) {
            case 'jpg':
                $src_img = imagecreatefromjpeg($imgpath);
                break;
            case 'png':
                $src_img = imagecreatefrompng($imgpath);
                break;
        }
        $wh = getimagesize($imgpath);
        $w = $wh[0];
        $h = $wh[1];
        $w = min($w, $h);
        $h = $w;
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $w / 2; //圆半径
        $y_x = $r; //圆心X坐标
        $y_y = $r; //圆心Y坐标
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        imagepng($img, $file);
        return $file;
    }

    public function newYearDay()
    {
        $this->display();
    }

    public function createNewYearPoster()
    {
        if (!$_POST['username']) {
            return show(0, '姓名不能为空');
        }
        //判断是否生成过
        $isCreate = M('invite_info')->where(['user_id' => $this->user['id'], 'activity_id' => 9999, 'username' => $_POST['username']])->getField('remark');
        if (!empty($isCreate)) {
            return show(1, '', $isCreate);
        }
        $imageArray = ['/Public/images/year1.png', '/Public/images/year2.png', '/Public/images/year3.png', '/Public/images/year4.png'
            , '/Public/images/year5.png', '/Public/images/year6.png', '/Public/images/year7.png', '/Public/images/year8.png', '/images/year9.png',
            '/Public/images/year10.png', '/Public/images/year11.png', '/Public/images/year12.png', '/Public/images/year13.png',
            '/Public/images/year14.png', '/Public/images/year15.png'];
        $background = imagecreatetruecolor(1080, 1400); // 背景图片
        $color = imagecolorallocate($background, 255, 255, 255); // 为真彩色画布创建白色背景，再设置为透明
        imagefill($background, 0, 0, $color);
        $gdImage = imagecreatefrompng('.' . $imageArray[array_rand($imageArray, 1)]);
        imagecopyresized($background, $gdImage, 0, 0, 0, 0, 1080, 1400, imagesx($gdImage), imagesy($gdImage));
        imagettftext($background, 70, 0, 400, 380, imagecolorallocate($background, 0, 0, 0), "Font/msyh.ttc", $_POST['username']);
//        header("content-type:image/png");
//        imagepng($background);
        $posterDir = "Upload/" . date("Ymd", time()) . '/';
        if (!file_exists($posterDir)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($posterDir, 0777, true);
        }
        $posterFilename = "Upload/" . date('Ymd', time()) . "/" . uniqid(time()) . '.png';
        imagepng($background, $posterFilename);
        //添加记录
        $insertData = [
            'create_time' => time(),
            'user_id' => $this->user['id'],
            'activity_id' => 9999,
            'remark' => '/' . $posterFilename,
            'username' => $_POST['username'],
        ];
        D('InviteInfo')->insert($insertData);
        return show(1, '', '/' . $posterFilename);
    }

    //广告
    public function advertising()
    {
        //1-首页 2-发现 3-投票 4-单页
        if (!$_GET['id']) {
            return show(0, 'ID参数错误');
        }
        //type 1-文章 2-课程 3-图片
        $list = M('poster')->where(['location' => $_GET['id']])->field('location,sort',true)->order('sort desc')->select();
        if (!$list) {
            return show(0, '暂无广告');
        } else {
            return show(1, '', ['list' => $list]);
        }
    }
}

?>