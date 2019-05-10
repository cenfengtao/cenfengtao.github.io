<?php
namespace Admin\Controller;

use Think\Controller;
use Think\Exception;

require_once __DIR__ . '/../../../ThinkPHP/Library/Vendor/ChuanglanSmsHelper/ChuanglanSmsApi.php';

class CommonController extends Controller
{
    public $user;
    public $token;
    public $isSuper;

    public function __construct()
    {
        parent::__construct();
        $this->_init();
        $this->user = $this->getAdminUser();
        $this->token = $this->_getToken($this->user['wxuser_id']);
        $this->isSuper = $this->token == "g232238gc959" ? true : false;
        //订单未读数
        $newOrderMsgCount = M('Order')->where(array('token' => $this->token, 'is_read' => 1))->count();
        //商品咨询未读数
        $newProductCommentCount = M('ProductComment')->where(array('token' => $this->token, 'is_read' => 1, 'type_id' => 0, 'father_id' => 0))->count();
        $this->assign('newOrderMsgCount', $newOrderMsgCount)->assign('newProductCommentCount', $newProductCommentCount);
    }

    /*
     * 初始化
     * */
    private function _init()
    {
        //如果已登陆
        $roleId = $this->isLogin();
        if (!$roleId) {
            //跳转到登陆页面
            redirect(U('Login/login'));
        }
        $this->assignBaseInfo();
        $this->checkAccess($roleId);
    }

    /*
     * 获取管理员信息
     * @return array
     * */
    public function getAdminUser()
    {
        return session('adminUser');
    }

    /*
     * 判断是否登陆
     * */
    public function isLogin()
    {
        $user = $this->getAdminUser();
        if ($user && is_array($user)) {
            return $user['role_id'];
        }
        return false;
    }

    /*
     * 获取管理员等级
     * */
    function getRoleName()
    {
        $roleName = D('AdminRole')->getAdminRoleByRoleId(session('adminUser')['role_id']);
        return $roleName;
    }

    function gettoken()
    {
        $plist = D('AdminUser')->getAdminBytoken(session('adminUser')['id']);
        return $plist[0]['token'];
    }

    /*
     * 获取基础信息并传值
     * */
    public function assignBaseInfo()
    {
        $adminName = session('adminUser')['username'];
        $adminId = session('adminUser')['id'];
        $roleName = $this->getRoleName();
        $menuList = D('Menu')->getAdminMenuList();
        foreach ($menuList as $k => $v) {
            $accessId = M('access')->where(array('acc_name' => $v['title'], 'father_id' => 0))->getField('id');
            $menuList[$k]['accessId'] = $accessId;
            if (!empty($v['child'])) {
                foreach ($v['child'] as $key => $val) {
                    $childAccessId = M('access')->where(array('acc_name' => $val['title'], 'father_id' => $accessId))->getField('id');
                    $menuList[$k]['child'][$key]['accessId'] = $childAccessId;
                }
            }
        }
        $accessIdList = D('AccessAuth')->getAccessIdByRoleId(session('adminUser')['role_id']);
        $this->assign('adminName', $adminName)->assign('roleName', $roleName)->assign('adminId', $adminId)
            ->assign('menuList', $menuList)->assign('accessIdList', $accessIdList);
    }

    /**
     * 检测权限
     **/
    public function checkAccess($roleId)
    {
//        判断数据库是否有该权限
        if ($_SERVER['PHP_SELF'] == '/admin.php') {
            return true;
        }
        $url = str_replace('/admin.php/Admin/', '', $_SERVER['PHP_SELF']);
        $url = str_replace('/admin.php/', '', $url);
        if (strpos($url, '.')) {
            $url = strstr($url, '.', true);
        }
        $jbUrl = explode('/', $url);
        $accessUrl = implode('/', array_slice($jbUrl, 0, 2));
        try {
            if (empty($accessUrl)) {
                return true;
            }
            $isAccess = D('Access')->isAccessByUrl($accessUrl);
            if ($isAccess) {
                $result = D('Access')->checkAccess($accessUrl, $roleId);
                if ($result) {
                    return true;
                } else {
                    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
                        return show(0, '你没有权限进行该操作');
                    } else {
                        $this->error('你没有权限进行该操作');
                    }
                }
            } else {
                if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
                    return show(0, '你没有权限进行该操作');
                } else {
                    $this->error('你没有权限进行该操作');
                }
            }
        } catch (Exception $e) {
            return show(0, $e->getMessage());
        }
    }

    /**
     * 单个上传图片
     */
    public function uploadPic()
    {
        $config = array(
            'maxSize' => 512000, //上传的文件大小限制 (0-不做限制)
            'exts' => array('jpg', 'png', 'gif', 'jpeg'), //允许上传的文件后缀
            'rootPath' => './Upload/', //保存根路径
            'driver' => 'LOCAL', // 文件上传驱动
            'subName' => array('date', 'Y-m'),
            'savePath' => I('dir', 'uploads') . "/"
        );
        $dirs = explode(",", C("YP_UPLOAD_DIR"));
        if (!in_array(I('dir', 'uploads'), $dirs)) {
            $this->ajaxReturn(['status' => 0, 'message' => '上传目录没权限'], 'JSON');
        }
        $upload = new \Think\Upload($config);
        $rs = $upload->upload($_FILES);
        $Filedata = key($_FILES);
        if (!$rs) {
            $this->ajaxReturn(['status' => 0, 'message' => $upload->getError()], 'JSON');
        } else {
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
            $rs[$Filedata]['savepath'] = "Upload/" . $rs[$Filedata]['savepath'];
            $rs[$Filedata]['savethumbname'] = $newsavename;
            $rs['status'] = 1;
            $this->ajaxReturn($rs, 'JSON');
        }
    }

    //通过公众号id获取token
    public function _getToken($wxuserId)
    {
        if (!session('token')) {
            $token = D('Wxuser')->getTokenByWxuserId($wxuserId);
            session('token', $token);
        } else {
            $token = session('token');
        }
        return $token;
    }
}