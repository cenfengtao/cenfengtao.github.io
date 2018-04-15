<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<!--headTrap<body></body><head></head><html></html>--><html>
<head>
    <meta charset="utf-8">
    <title>优培圈</title>
    <meta name="viewport" content="width=device-width, initial-scale=0.5, user-scalable=0, minimum-scale=0.5, maximum-scale=0.5">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /><!-- iOS webapp s-->
    <meta name="format-detection" content="telephone=no" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="keywords" content="优培圈" />
    <meta name="description" content="" />
    <link rel="apple-touch-icon-precomposed" href="styles/app_icon.png">
    <!-- iOS webapp e -->
    <link href="/Public/Home/css/kap.css" rel="stylesheet" type="text/css">
    <link href="/Public/Home/css/common.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="/Public/index/member/css/dialog.css" media="all" />
    <script type="text/javascript" src="/Public/Home/js/jweixin-1.0.0.js"></script>
    <script type="text/javascript" src="/Public/index/member/js/dialog.js"></script>
    <!--移动端兼容适配 end -->
    <script src="/Public/Home/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript">
        if(/msie/i.test(navigator.userAgent)){window.location.href="http://www.youpei-exc.com/"}
    </script>
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?548e13b07cb36e38bfd73870368a0253";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();


        // var ua = navigator.userAgent.toLowerCase();
        //     if(ua.indexOf("iphone")>0){ //app

        //         get_ios_script();
        //     }else{
        //         get_android_script();
        //         $("meta[name=viewport]").attr('content', 'width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0');
        //     }
        //     function get_ios_script() {
        //       var link = document.createElement('link');
        //         link.type = 'text/css';
        //         link.rel = 'stylesheet';
        //         link.href = '/Public/Home/css/ios.css';
        //         document.getElementsByTagName("head")[0].appendChild(link);
        //     }


    </script>
</head>
<link href="/Public/Home/css/pageloader.css" rel="stylesheet" type="text/css">
<body class="kap-loading ty-view-all" onload="">
<div class="kap-wrap">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<link rel="stylesheet" href="/Public/css/home/map.css">
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=ZL7uRt3632Y1iZWucCRIc76y3LWF6U7z"></script>
<link rel="stylesheet" href="http://api.map.baidu.com/library/SearchInfoWindow/1.5/src/SearchInfoWindow_min.css"/>
<script type="text/javascript" src="http://api.map.baidu.com/library/SearchInfoWindow/1.5/src/SearchInfoWindow_min.js"></script>
<!-- 菜单 开始 -->
<div class="kap-top ty-menu-top" id="headTitle">
    <div class="kap-top ty-menu-top">
        <div id="tools-msg" class="ty-tools ty-tools-inner ty-tools-current">
            <span class="title"><?php echo ($title); ?><span class="num"></span></span>
            <a href="javascript:void(0);" onclick="history.go(-1);" class="btn left btn-user" title="返回" data-icon="ɒ"></a>
            <a href="<?php echo U('Index/index');?>" class="btn right btn-user" title="返回首页" data-icon="Ņ"></a>
        </div>
    </div>
    <div class="seach"><input type="text" id="title" name="title" placeholder="搜索机构名称" value=""><i data-icon="Đ"></i><a href="javascript:void(0);" title="搜索" onclick="searchTitle();" style="float:right;font-size:1rem;margin-right: 0.8rem;font-weight:600;line-height:2.1rem;">搜索</a></div>
    <div id="position_result">正在获取地位中，请稍候</div>
    <div class="jump_to_app"><a href="http://a.app.qq.com/o/simple.jsp?pkgname=com.baidu.BaiduMap">使用百度地图</a></div>
</div>
<div class="kap-wrap">
    <div class="qiandao-warp" id="qiandao">
        <div id="allmap" style="display:;"></div>
        <div id="data">
            <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i; echo ($val); endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <input type="hidden" value="" id="lng">
        <input type="hidden" value="" id="lat">
    </div>
</div>

<script type="text/javascript">
    // 百度地图API功能
    var map = new BMap.Map('allmap');
    var data = $('#data').text().replace(/^\s\s*/, '').replace(/\s\s*$/, '');
    var subst = data.substr(0,data.length-1);
    var spli = subst.split(',');
    var data_info = [];
    for(var z=0;z<spli.length;z++){
        data_info[z] = spli[z].split(":");
        data_info[z][0] = parseFloat(data_info[z][0]);
        data_info[z][1] = parseFloat(data_info[z][1]);
    }
    $('#data').hide();
    for(var i=0;i<data_info.length;i++){
        map.centerAndZoom(new BMap.Point(data_info[i][0],data_info[i][1]), 16);
        map.enableScrollWheelZoom();
        var marker = new BMap.Marker(new BMap.Point(data_info[i][0],data_info[i][1])); //创建marker对象
        var title = data_info[i][2];
        var center = '<div style="margin:0;line-height:20px;padding:2px;" id="message">'+'电话：'+data_info[i][4]+'<br/>'+'地址：'+data_info[i][3]+'<br/><a onclick="bus('+data_info[i][0]+','+data_info[i][1]+')">公车</a>'+'<a onclick="walk('+data_info[i][0]+','+data_info[i][1]+')">步行</a>'+'<a onclick="car('+data_info[i][0]+','+data_info[i][1]+')">驾车</a>'+'</div>';
        addClickHandler(marker,center,title);
        map.addOverlay(marker); //在地图中添加marker
        var label = new BMap.Label(title,{offset:new BMap.Size(20,-10)});
        marker.setLabel(label);
    }
    function addClickHandler(marker,center,title){
        //创建检索信息窗口对象
        var searchInfoWindow = null;
        searchInfoWindow = new BMapLib.SearchInfoWindow(map, center, {
            title  : title,      //标题
            width  : 290,             //宽度
            height : 80,              //高度
            panel  : "panel",         //检索结果面板
            enableAutoPan : true,     //自动平移
            searchTypes   :[
                /*BMAPLIB_TAB_SEARCH,   //周边检索
                BMAPLIB_TAB_TO_HERE,  //到这里去
                BMAPLIB_TAB_FROM_HERE //从这里出发*/
            ]
        });
        marker.addEventListener("click", function(e){
            searchInfoWindow.open(marker);
        })
    }
    //个人定位
//    var geolocation = new BMap.Geolocation();
//    var gectrl=new BMap.GeolocationControl( {
//        anchor:BMAP_ANCHOR_TOP_LEFT,
//        enableAutoLocation: true });
//    map.addControl(gectrl); //添加定位控件
//    geolocation.getCurrentPosition(function(r){
//        if(this.getStatus() == BMAP_STATUS_SUCCESS){
//            var mk = new BMap.Marker(r.point);
//            var myIcon = new BMap.Icon("http://www.youpei-exc.com/Public/images/blank.gif", new BMap.Size(14,14));
//            var marker2 = new BMap.Marker(r.point,{icon:myIcon});  // 创建标注
//            map.addOverlay(marker2);
////            map.addOverlay(mk);
//            map.panTo(r.point);
//            $('#lng').val(r.point.lng);
//            $('#lat').val(r.point.lat);
//        }
//        else {
//            alert('failed'+this.getStatus());
//        }
//    },{enableHighAccuracy: true});

    //公交导航
    function bus(lng,lat) {
        var x_lng = $('#lng').val();
        var y_lat = $('#lat').val();
        var map = new BMap.Map("allmap");            // 创建Map实例
        map.centerAndZoom(new BMap.Point(116.404, 39.915), 12);
        var p1 = new BMap.Point(x_lng,y_lat);
        var p2 = new BMap.Point(lng,lat);
        var transit = new BMap.TransitRoute(map, {
            renderOptions: {map: map}
        });
        transit.search(p1, p2);
        console.log(transit);
        console.log(transit.kb);
        /*if(transit.kb == -1){
            walk(lng,lat);
        }else if(transit.kb == 0){
            bus(lng,lat);
        }*/
    }
    //驾车导航
    function car(lng,lat){
        var x_lng = $('#lng').val();
        var y_lat = $('#lat').val();
        var map = new BMap.Map("allmap");            // 创建Map实例
        map.centerAndZoom(new BMap.Point(116.404, 39.915), 12);
        var p1 = new BMap.Point(x_lng,y_lat);
        var p2 = new BMap.Point(lng,lat);
        var driving = new BMap.DrivingRoute(map, {renderOptions:{map: map, autoViewport: true}});
        driving.search(p1, p2);
    }
    //步行导航
    function walk(lng,lat) {
        var x_lng = $('#lng').val();
        var y_lat = $('#lat').val();
        var map = new BMap.Map("allmap");            // 创建Map实例
        map.centerAndZoom(new BMap.Point(116.404, 39.915), 12);
        var p1 = new BMap.Point(x_lng,y_lat);
        var p2 = new BMap.Point(lng,lat);
        var walking = new BMap.WalkingRoute(map, {renderOptions:{map: map, autoViewport: true}});
        walking.search({title: '起点', point: p1},
                {title: '终点', point: p2});
    }
    /*
    //步行导航
    var p1 = new BMap.Point(origin);
    var p2 = new BMap.Point(116.508328,39.919141);
    var walking = new BMap.WalkingRoute(map, {renderOptions:{map: map, autoViewport: true}});
    driving.search({title: '我这里', point: p1},
            {title: '你这里', point: p2});*/

</script>
    <script>
        function searchTitle() {
            var title = $("input[name='title']").val();
            if(!title){
                return alert("请输入搜索内容");
            }
            window.location.href = "<?php echo U('Map/search');?>?title="+title;
        }
    </script>
<!--tailTrap<body></body><head></head><html></html>-->
</div>
<script src="/Public/Home/js/jquery.min.js"></script>
<script type="text/javascript">
    wx.config({
        debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: "<?php echo ($signPackage['appId']); ?>", // 必填，公众号的唯一标识
        timestamp: "<?php echo ($signPackage['timestamp']); ?>", // 必填，生成签名的时间戳
        nonceStr: "<?php echo ($signPackage['nonceStr']); ?>", // 必填，生成签名的随机串
        signature: "<?php echo ($signPackage['signature']); ?>",// 必填，签名，见附录1
        jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'closeWindow', 'chooseImage', 'uploadImage', 'getLocation'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    });
    wx.ready(function () {
        wx.getLocation({
            success: function (res) {
                $.ajax({
                    url: "/index.php/Map/tranPosition",
                    type: "POST",
                    data: JSON.stringify(res),
                    dataType: "json",
                    success: function(json){
                        var latitudeNew = json.latitudeNew;
                        var longitudeNew = json.longitudeNew;
                        $('#lng').val(longitudeNew);
                        $('#lat').val(latitudeNew);
                        //创建小狐狸
                        var pt = new BMap.Point(longitudeNew, latitudeNew);
                        var myIcon = new BMap.Icon("http://www.youpei-exc.com/Public/images/blank.gif", new BMap.Size(14,14));
                        var marker2 = new BMap.Marker(pt,{icon:myIcon});  // 创建标注
                        map.addOverlay(marker2);              // 将标注添加到地图中
                        $("#position_result").hide();
                    },
                    error: function(){
                        $("#position_result").text("获取定位失败，请刷新再试");
                    }
                })
            }
        });
        wx.error(function (res) {
            alert(JSON.stringify(res));
        });
    })
</script>