<?php
namespace Home\Controller;

use Think\Controller;
use Think\Exception;

class MapController extends BaseController
{
    public function index()
    {
        $this->title = ("优培大地图");
        $this->display();
    }

    //发现页的地图 （用weixin.js定位，避免重复提示授权）
    public function discoverIndex()
    {
        $this->title = ("优培大地图");
        $this->display();
    }

    //初始化地图
    public function ajaxIndex()
    {
        $orgAddress = M('Organization')->field('org_name,tel,city,area,address')->select();
        $orgList = [];
        foreach ($orgAddress as $k => $v) {
            $addressPoint = file_get_contents("http://api.map.baidu.com/geocoder/v2/?output=json&ak=ZL7uRt3632Y1iZWucCRIc76y3LWF6U7z&address=" . $v['city'] . $v['area'] . $v['address']);
            $addressPoint = json_decode($addressPoint, true);
            if ($addressPoint['status'] != 0) { //不等于0代表查询失败
                continue;
            }
            $orgList[] = [
                'lng' => $addressPoint['result']['location']['lng'],
                'lat' => $addressPoint['result']['location']['lat'],
                'address' => $v['city'] . $v['area'] . $v['address'],
                'mobile' => $v['tel'],
                'title' => $v['org_name']
            ];
        }
        return show(1, '', ['list' => $orgList]);
    }

    //多点，需异步再次加载避免缓慢
    public function ajaxSecondIndex()
    {
        $mapAddress = M('map')->field('id,title,mobile,city,area,address,lat,lng')->select();
        $mapList = [];
        foreach ($mapAddress as $key => $val) { //不等于0代表查询失败
            if (empty($val['lat']) || empty($val['lng'])) {
                $addressPoint = file_get_contents("http://api.map.baidu.com/geocoder/v2/?output=json&ak=ZL7uRt3632Y1iZWucCRIc76y3LWF6U7z&address=" . $val['city'] . $val['area'] . $val['address']);
                $addressPoint = json_decode($addressPoint, true);
                if ($addressPoint['status'] != 0) {
                    continue;
                }
                $mapList[] = [
                    'lng' => $addressPoint['result']['location']['lng'],
                    'lat' => $addressPoint['result']['location']['lat'],
                    'address' => $val['city'] . $val['area'] . $val['address'],
                    'mobile' => $val['mobile'],
                    'title' => $val['title'],
                ];
                M('map')->where("id={$val['id']}")->save(['lng' => $addressPoint['result']['location']['lng'], 'lat' => $addressPoint['result']['location']['lat']]);
            } else {
                $mapList[] = [
                    'lng' => $val['lng'],
                    'lat' => $val['lat'],
                    'address' => $val['city'] . $val['area'] . $val['address'],
                    'mobile' => $val['mobile'],
                    'title' => $val['title']
                ];
            }
        }
        return show(1, '', ['list' => $mapList]);
    }

    public function GPS()
    {
        $this->title = ("优培大地图");
        $token = '';
        if ($_GET['pro_id']) {
            $token = M('Product')->where("id={$_GET['pro_id']}")->getField('token');
        }
        if ($_GET['par_id']) {
            $token = M('parenting')->where("id={$_GET['par_id']}")->getField('token');
        }
        $site = M('Organization')->where(array('token' => $token))->find();
        $file = file_get_contents("http://api.map.baidu.com/geocoder/v2/?output=json&ak=ZL7uRt3632Y1iZWucCRIc76y3LWF6U7z&address=" . $site['city'] . $site['area'] . $site['address']);
        $res = str_replace(array("\"", "{", "}"), "", $file);
        $arr = explode(":", $res);
        $x = explode(",", $arr[4]);
        $y = explode(",", $arr[5]);
        $data = $x[0] . ':' . $y[0] . ':' . $site['org_name'] . ':' . $site['city'] . $site['area'] . $site['address'] . ':' . $site['tel'] . ',';
        $this->assign('data', $data);
        $this->display();
    }

    public function search()
    {
        $this->title = ("优培大地图");
        if (!$_GET['title'] || empty($_GET['title'])) {
            return show(0, '标题不能为空');
        }
        $where['org_name'] = array('LIKE', '%' . $_GET['title'] . '%');
        $add = D('Organization')->getOrgByTitle($where);
        if (!$add || empty($add)) {
            $add = M('map')->where(array('title' => array('LIKE', '%' . $_GET['title'] . '%')))->select();
            if (!$add || empty($add)) {
                $data = [];
            } else {
                foreach ($add as $k => $v) {
                    $file[$k] = file_get_contents("http://api.map.baidu.com/geocoder/v2/?output=json&ak=ZL7uRt3632Y1iZWucCRIc76y3LWF6U7z&address=" . $v['city'] . $v['area'] . $v['address']);
                    $res[$k] = str_replace(array("\"", "{", "}"), "", $file[$k]);
                    $arr[$k] = explode(":", $res[$k]);
                    $x[$k] = explode(",", $arr[$k][4]);
                    $y[$k] = explode(",", $arr[$k][5]);
                    $data[$k] = $x[$k][0] . ':' . $y[$k][0] . ':' . $v['title'] . ':' . $v['city'] . $v['area'] . $v['address'] . ':' . $v['mobile'] . ',';
                }
            }
        } else {
            foreach ($add as $k => $v) {
                $file[$k] = file_get_contents("http://api.map.baidu.com/geocoder/v2/?output=json&ak=ZL7uRt3632Y1iZWucCRIc76y3LWF6U7z&address=" . $v['city'] . $v['area'] . $v['address']);
                $res[$k] = str_replace(array("\"", "{", "}"), "", $file[$k]);
                $arr[$k] = explode(":", $res[$k]);
                $x[$k] = explode(",", $arr[$k][4]);
                $y[$k] = explode(",", $arr[$k][5]);
                $data[$k] = $x[$k][0] . ':' . $y[$k][0] . ':' . $v['org_name'] . ':' . $v['city'] . $v['area'] . $v['address'] . ':' . $v['tel'] . ',';
            }
        }
        $this->assign('data', $data);
        $this->display();
    }

    //微信转换百度坐标
    public function tranPosition()
    {
        header('Content-type:text/json');
        $data = file_get_contents('php://input');
        $newData = json_decode($data, true);
        $latitude = $newData["latitude"];
        $longitude = $newData["longitude"];
        //百度地图坐标转换官网：http://lbsyun.baidu.com/index.php?title=webapi/guide/changeposition
        $q = "http://api.map.baidu.com/geoconv/v1/?coords=" . $longitude . "," . $latitude . "&from=1&to=5&ak=ZL7uRt3632Y1iZWucCRIc76y3LWF6U7z";
        $resultQ = json_decode(file_get_contents($q), true);
        $latitudeNew = $resultQ["result"][0]["y"];
        $longitudeNew = $resultQ["result"][0]["x"];
        $returnDataArray = array("latitudeNew" => $latitudeNew, "longitudeNew" => $longitudeNew);
        $returnData = json_encode($returnDataArray);
        echo $returnData; //只有输出才能回传到$.ajax中的success，这样ajax中的success:function(data)中的data就收到数据了
    }

    //课程表地图
    public function address()
    {
        $this->title = ("优培大地图");
        $classTime = '';
        if ($_GET['class_id']) {
            $classTime = M('class_time')->where("id={$_GET['class_id']}")->find();
        }
        if ($classTime['upload_type'] == 1) {
            $org = M('Organization')->where(array('token' => $classTime['token']))->find();
            $site['org_name'] = $org['org_name'] . $classTime['class_title'];
            $site['tel'] = $org['tel'];
        } else {
            $site['org_name'] = $classTime['class_title'];
            $site['tel'] = '暂无电话';
        }
        $file = file_get_contents("http://api.map.baidu.com/geocoder/v2/?output=json&ak=ZL7uRt3632Y1iZWucCRIc76y3LWF6U7z&address=" . $classTime['city'] . $classTime['area'] . $classTime['address']);
        $res = str_replace(array("\"", "{", "}"), "", $file);
        $arr = explode(":", $res);
        $x = explode(",", $arr[4]);
        $y = explode(",", $arr[5]);
        $data = $x[0] . ':' . $y[0] . ':' . $site['org_name'] . ':' . $classTime['city'] . $classTime['area'] . $classTime['address'] . ':' . $site['tel'] . ',';
        $this->assign('data', $data);
        $this->display();
    }
}