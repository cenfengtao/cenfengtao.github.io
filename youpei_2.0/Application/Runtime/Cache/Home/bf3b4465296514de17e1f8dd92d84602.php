<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>优培圈</title>
    <meta name="viewport"
          content="width=device-width, initial-scale=0.5, user-scalable=0, minimum-scale=0.5, maximum-scale=0.5">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/><!-- iOS webapp s-->
    <meta name="format-detection" content="telephone=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <link rel="apple-touch-icon-precomposed" href="styles/app_icon.png">
    <!-- iOS webapp e -->
    <link href="/Public/Home/css/kap.css" rel="stylesheet" type="text/css">
    <link href="/Public/Home/css/common.css" rel="stylesheet" type="text/css">
    <link href="/Public/Home/css/index.css" rel="stylesheet" type="text/css">
    <link href="/Public/Home/css/search.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="/Public/index/member/css/dialog.css" media="all"/>
    <script type="text/javascript" src="/Public/index/member/js/dialog.js"></script>
    <!--移动端兼容适配 end -->
    <script src="/Public/Home/js/jquery-1.7.1.min.js"></script>
</head>
<link href="/Public/Home/css/pageloader.css" rel="stylesheet" type="text/css">
<style>
</style>
<body class="kap-loading ty-view-all">
<div class="kap-wrap">
    <link rel="stylesheet" href="/Public/Home/css/style_v3.css">
    <link rel="stylesheet" href="/Public/Home/css/new.css">
    <div id="page-user" title="<?php echo ($title); ?>" class="kap-page kap-page-current">
        <div class="kap-top ty-menu-top">
            <div id="tools-msg" class="ty-tools ty-tools-inner ty-tools-current">
                <span class="title"><?php echo ($title); ?><span class="num"></span></span>
                <a href="javascript:void(0);" onclick="history.go(-1);" class="btn left btn-user" title="返回"
                   data-icon="ɒ"></a>
                <div class="tools-search">
                    <div class="ty-tools-search">
                        <form id="page_hot_search">
                            <div class="ty-tools-typeswitch">
                                <div data-kap="_kap_1" class="kap-input sel-type-switch" style="position: relative;">
                                    <select data-kap-skin="sel-type-switch" data-icon="Ė" name="searchType" id="sel_type_switch" style="opacity: 0; position: absolute; border: none; left: 0px; top: 0px; width: 100%; height: 100%;">
                                        <option value="1" >文章</option>
                                        <option value="2" >课程</option>
                                        <option value="3" >商品</option>
                                        <option value="4" >机构</option>
                                    </select>
                                    <span data-icon="Ė" class="kap-input-value">文章</span>
                                </div>
                            </div>
                            <input class="input" name="searchWord" id="page_hot_keyword" type="search" placeholder="搜索课程、文章">
                        </form>
                        <i data-icon="Đ"></i>
                    </div>
                </div>
                <a href="javascript:void(0);" title="搜索" onclick="searchWord();" style="float:right;font-size:1rem;
            margin-right: 1rem;font-weight:600;">搜索</a>
            </div>
        </div>
        <hr>
        <div class="ty-page search">
                <div class="ty-hot-search">
            <span class="title">热搜</span>
            <?php if(is_array($searchTags)): foreach($searchTags as $key=>$val): ?><span class="item" onclick="searchWord('<?php echo ($val); ?>')"><?php echo ($val); ?></span><?php endforeach; endif; ?>
        </div>

        <div class="ty-search-history">
            <span class="title">历史搜索</span>
            <ul id="searchHistory">
                <?php if(is_array($searchHistory)): foreach($searchHistory as $key=>$val): ?><li onclick="searchWord('<?php echo ($val); ?>')"><span class="item"><?php echo ($val['word']); ?></span></li><?php endforeach; endif; ?>
            </ul>
            <span class="btn-delete-history" onclick="clearHistory()">清空搜索记录</span>
        </div>
        </div>
    </div>
</div>
<script src="/Public/js/home/search.js" type="text/javascript" charset="utf-8"></script>
</body>
</html>