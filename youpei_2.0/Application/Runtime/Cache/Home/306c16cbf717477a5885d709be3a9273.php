<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<!--headTrap<body></body><head></head><html></html>-->
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>优培圈</title>
    <link rel="stylesheet" type="text/css" href="/Public/css/home/common.css"/>
    <link rel="stylesheet" type="text/css" href="/Public/css/home/com.css"/>
        <link rel="stylesheet" type="text/css" href="/Public/css/home/pageloader.css"/>
    <link rel="stylesheet" href="/Public/vendors/upload-image/css/page-common.css">
    <link rel="stylesheet" type="text/css" href="http://www.jq22.com/jquery/font-awesome.4.6.0.css">
    <link rel="stylesheet" href="/Public/vendors/upload-image/css/upload.css"/>
    <link rel="stylesheet" type="text/css" href="/Public/css/homes/userContribution.css"/>
    <style>
      *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html{
            height: 100%;
            overflow-y:scroll;

        }
/*        body{
            background: skyblue;
        }*/
        body{
            height: 100%;
            position: relative;
            overflow-y:scroll;
        }
    .app #getFile, .app #getFiles,.app #getFiless{
      color: #fef4e9;
        border: 1px solid #da7c0c;
        vertical-align: middle;
        padding: 6px 5px;
        line-height: 1;
        background: -webkit-gradient(linear,left top,left bottom,from(#faa51a),to(#f47a20));
        background: -moz-linear-gradient(top,#faa51a,#f47a20);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#faa51a', endColorstr='#f47a20');
    }
    .copy{
      position: fixed;
        top: 0;
        /* bottom: 0; */
        height: 100%;
        width: 100%;
        z-index: 10000;
        background:blueviolet;
        display: none;
    }
    .app{
      z-index: 100000000000000;
      position: fixed!important;
    }
    #previewResult{
      border: none;;
    }
    </style>
    <script type="text/javascript">
        if(/msie/i.test(navigator.userAgent)){window.location.href="http://www.youpei-exc.com/"}
    </script>
</head>
<style>
</style>
<body class="kap-loading ty-view-all" >
<div id="bonfire-pageloader">
    <div class="bonfire-pageloader-icon">
       <svg class="kap-loading-icon" xmlns="http://www.w3.org/2000/svg"  xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="512px" height="512px"  enable-background="new 0 0 512 512" xml:space="preserve">
        <!--加载图片转码base64格式-->
        <g>
           <image class="ty-loading-icon" xlink:href="data:image/png;base64,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" id="ty_tree" height="50" width="50" y="0" x="0"/>
        </g>
        </svg>
    </div>  
</div>
<header class="header">
    <a href="javascript:history.back()" class="back">
        <img src="../../../../Public/images/back.png" alt="">
    </a>
    <h3>投稿</h3>
    <a href="../Index/index.html" class="home" data-icon="Ņ"></a>
</header>
<article class="middle" style="position: relative;"> 
      <section class="basic title">
        <h3 class="container">——/ <strong>基</strong> / <strong>本</strong> / <strong>信</strong> / <strong>息</strong>/——</h3>
      </section>
      <form  id="demo-form2" data-parsley-validate class="form-horizontal form-label-left form-youpei">
      <div class="out">
        <section class="works container">
            <span>*作品标题：</span>
            <input type="text" value="" name="title" id="title" onchange="testTitle(this.value)" maxlength="6" placeholder="亲，只能输入六个字哦。">
            <img src="../../../../Public/images/write.png" alt="">
        </section>
         <section class="works container">
            <span>*作品作者：</span>
            <input type="text" value="" name="username" id="userName" onchange="testName(this.value)" maxlength="6" placeholder="亲，只能输入六个字哦。">
            <img src="../../../../Public/images/write.png" alt="">
        </section>
         <section class="works container">
            <span>*联系电话：</span>
            <input type="tel" value="" maxlength="11" onchange="testTel(this.value)" name="mobile" id="mobile">
            <img src="../../../../Public/images/write.png" alt="">
            <input type="text" value="" name="vote_id" id="vote_id" style="display: none;">
        </section>
         <section class="works container">
             <span>*宝宝年龄段：</span>
             <select name="age_bracket" id="age_bracket">
             </select>
             <img src="../../../../Public/images/write.png" alt="">
         </section>
        </div>
        <section class="basic title" style="margin-top: 0.12rem;">
          <h3 class="container">——/ <strong>作</strong> / <strong>品</strong> / <strong>上</strong> / <strong>传</strong>/——</h3>
        </section>
        <div class="out">
          <section class="explain container">
          </section>
        </div>
        <div class="out">
          <div class="imgContent container border">
            <section class="upload">
              <a href="#img" id='fileChooseButton' class="button blue rarrow file-input-mask">上传或修改</br>第一张展示图<input class="upload-file" type="file" id="file"  capture="camera"/></a>
            </section>
            <section class="img" id="img">
              <img id="previewResult"/>
              <img id="needCropImg" />
            </section>
          </div>
          <div class="imgContent1 container border">
            <section class="upload">
               <a href="#img1"  id='fileChooseButtons' class="button blue rarrow file-input-mask">上传或修改</br>第二张展示图<input class="upload-file" type="file" id="files"  capture="camera"/></a>
            </section>
             <section class="img" id="img1">
                <img id="previewResults"/>
                <img id="needCropImgs" />
            </section>
          </div>
          <div class="imgContent2 container border" style="border: none;">
            <section class="upload">
               <a href="#img2" id='fileChooseButtonss' class="button blue rarrow file-input-mask">上传或修改</br>第三张展示图<input class="upload-file" type="file" id="filess"  capture="camera" /></a>
            </section>
            <section class="img" id="img2">
                <img id="previewResultss"/>
                <img id="needCropImgss" />
            </section>
          </div> 
        </div>      
        <p class="alert" style="text-align: center;background: white; font-size: 0.6rem; padding-bottom: 1rem; padding-top: -0.1rem;"></p>
          <section class="show" style="display: none;">
              <p>正在加载中......</p>
          </section>
          <section class="sub" style="display: none;">
              <p>正在提交中......</p>
          </section>
          <div class="upload">
            <input type="button" value="提交审核" onclick="sub()"  class="submit" />
          </div>
           
        </form>    
        <section class="hidden">
        	<input type="hidden" name="image" id="upload_image" tabindex="3" autocomplete="off"/>
        	<input type="hidden" name="image" id="upload_image1" tabindex="3" autocomplete="off"/>
        	<input type="hidden" name="image" id="upload_image2" tabindex="3" autocomplete="off"/>
        </section>
        <section class="tellBox" style="display:none;">
            <div class="tellContent">
                <img src="../../../../Public/images/close.png" alt="" class="closeBtn">
                <h3>投稿结果</h3>
                <div>
                  <p>您已成功投稿！请等待审核通过！</p>
                </div>
                <a href="javascript:void(0);">关注「优培圈」才能收到审核通过的消息通知噢！</a>
                <img src="../../../../Public/images/ypq_qrcode.jpg" alt="" class="code">
            </div>
        </section>
        <section class="tellBoxs" style="display:none;">
            <div class="tellContents">
                <img src="../../../../Public/images/close.png" alt="" class="closeBtn">
                <h3>投稿结果</h3>
                <div>
                  <p>您重新成功投稿！请等待审核通过！</p>
                </div>
                <a href="javascript:void(0);">关注「优培圈」才能收到审核通过的消息通知噢！</a>
                <img src="../../../../Public/images/ypq_qrcode.jpg" alt="" class="code">
            </div>
        </section>
         <div class="copy"> 
          <div class="app" id="uploadPage">
              <div class="upload-loading">
                  <span class="centerXY"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></span>
              </div>
              <div class="bar first"><a class="back pull-left" id="closeCrop"><i class="fa fa-reply"></i></a><a id="getFile" class="pull-right">使用</a></div>
              <div class="bar second"><a class="back pull-left" id="closeCrops"><i class="fa fa-reply"></i></a><a id="getFiles" class="pull-right">使用</a></div>
              <div class="bar third"><a class="back pull-left" id="closeCropss"><i class="fa fa-reply"></i></a><a id="getFiless" class="pull-right">使用</a></div>
              <div class="main">
                  <canvas class="upload-mask">

                  </canvas>
                  <div class="preview-box">
                      <img id="preview"/>
                  </div>
                  <canvas class="photo-canvas">

                  </canvas>
                  <a  id="rotateBtn">
                      <i class="fa fa-rotate-right  fa-3x"></i>
                      <div>旋转照片</div>
                  </a>
              </div>
          </div>
         </div> 
</article>
<footer class="footer">
    <ul class="icon">
        <li>
            <img src="/Public/image/circle.png" alt="">
            <a href="javascript:void(0);" class="voteList">
                <img src="/Public/images/voteIcon1.png"/>
                <span>作品库</span>
            </a>
        </li>
        <li>
            <img src="/Public/image/circle.png" alt="">
            <a href="javascript:void(0);" class="userVote">
                <img src="/Public/images/voteIcon2.png"/>
                <span>我的投票</span>
            </a>
        </li>
        <li>
            <img src="/Public/image/circle.png" alt="">
            <a href="javascript:void(0);" class="userWorks">
                <img src="/Public/images/voteIcon3.png"/>
                <span>我的作品</span>
            </a>
        </li>
        <li>
            <img src="/Public/image/circle.png" alt="">
            <a href="javascript:void(0);" class="userBillboard">
                <img src="/Public/images/voteIcon4.png"/>
                <span>榜单</span>
            </a>
        </li>
    </ul>
</footer>
<input type="hidden" id="userStatus" value="<?php echo ($userStatus); ?>">
<script src="/Public/Home/js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/Public/Home/js/pageloader.js" type="text/javascript" charset="utf-8"></script>
<script src="/Public/js/home/rem.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="/Public/Home/js/jweixin-1.0.0.js"></script>
<!-- <script src="/Public/vendors/jquery/dist/jquery.min.js"></script> -->
<script src="/Public/js/layer/layer.js"></script>
<script src="/Public/vendors/upload-image/js/require.js"></script>
<script src="/Public/vendors/upload-image/js/main.js"></script>
<script src="/Public/vendors/upload-image/js/canvas-toBlob.js"></script>
<script>
    //    获取url参数
    function GetRequest() {
        var url = location.search; //获取url中"?"符后的字串
        var theRequest = new Object();
        if (url.indexOf("?") != -1) {
            var str = url.substr(1);
            strs = str.split("&");
            for (var i = 0; i < strs.length; i++) {
                theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
            }
        }
        return theRequest;
    }
// 底部导航对应选中状态
 $(".footer ul li>img").css("display","none");
$(".footer ul li>img:eq(2)").css("display","block");
$(" .footer ul li").click(function(){
    $(this).addClass('on').siblings().removeClass("on");
    var index=$(this).index();
    var liLen=$(".footer ul li").length;
    for(var i=0;i<=liLen-1;i++){
        $(".footer ul li>img").eq(i).css("display","none");
    }
    $(this).find("img:eq(0)").css("display","block");
})
// 作品名不能为空判断
var testTitle=function(value){
  if(value==""){
    alert("请先填写您的作品名称。")
    return;
  }
}
// 作者名不能为空判断
var testName=function(value){
  if(value==""){
    alert("请先填写您的名字。")
    return;
  }
}
// 手机号码正则验证
var testTel=function(value){
    var reg=/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/;
    if(!reg.test(value)){
        alert("你输入的手机号码不正确，请重新输入。")
        return false;
    }
}  
// 判断手机类型
function fBrowserRedirect(){
 var sUserAgent = navigator.userAgent.toLowerCase();
 var isIphone = sUserAgent.match(/iphone/i) == "iphone";
 var isAndroid = sUserAgent.match(/android/i) == "android";
 if(isIphone){ $("#file").attr("accept","");
 $("#file").attr("capture","");
 }
// if(isAndroid){ window.location.href = "download/android.html"; }
}
fBrowserRedirect();
// 点击上传实现上传文件
    //    获取url参数
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return null;
    }
    // 给不同的底部导航不同参数进行跳转
    var vote_id = getQueryString('vote_id');
    $(".voteList").attr("href","/index.php/Vote/voteList.html?vote_id="+vote_id)
    $(".userVote").attr("href","/index.php/Vote/userVote.html?vote_id="+vote_id)
    $(".userWorks").attr("href","/index.php/Vote/userWorks.html?vote_id="+vote_id)
    $(".userBillboard").attr("href","/index.php/Vote/userBillboard.html?vote_id="+vote_id)
    $("#vote_id").attr("value",vote_id)
</script>
<script type="text/javascript">
// 进入页面获取上传类型进行上传  
var upload_type;
$(function(){
    $.ajax({
        type: "get",
        url: "/index.php/Vote/uploadType",
        aysnc: true,
        dataType: "json",
        data:{
            vote_id:vote_id,
        },
        success:function(data){
            if(data.status==1){
              upload_type=data.data.type;
              var age_bracket=data.data.age_bracket;
                $.each(data.data.age_bracket,function (key,val) {
                    if(val.id == data.data.userUpload.age_bracket){
                        $('#age_bracket').append(
                                '<option value="'+val.id+'" selected="selected">'+val.title+'</option>'
                        );
                    }else{
                        $('#age_bracket').append(
                                '<option value="'+val.id+'">'+val.title+'</option>'
                        );
                    }
                });
              window.sessionStorage.setItem("upload_status",data.data.userUpload.upload_status)
              window.sessionStorage.setItem("statuss",data.data.userUpload.status)
              window.sessionStorage.setItem("upload_type",data.data.type)
                if(data.data.type==1){
                  $(".alert").html("亲，请上传图片。")
                  $(".explain").append(
                      '<h3>投稿须知：</h3>'+
                     ' <p>1、作品最多可上传3张图片进行展示，请选择最好的图片上传噢！</p>'+
                      '<p>2、投稿成功后需后台审核通过后才能进行拉票。可在“我的作品”菜单栏查看审核状态。</p>'+
                      '<p>3、投稿遇到问题请咨询客服<span>客服咨询</span></p>'
                    )
                  if(data.data.userUpload.upload_status==2){
                      var title=data.data.userUpload.title;
                      var userName=data.data.userUpload.username;
                      var mobile=data.data.userUpload.mobile;
                      $("#title").val(title)
                      $("#userName").val(userName)
                      $("#mobile").val(mobile)
                      $("#previewResult").attr('src',data.data.userUpload.path[0])
                      var imgSrc=$("#previewResult").attr("src")
                      //  if(imgSrc!=""){
                      //   $(".oneUpload").show()
                      // }else{
                      //   $("#img img").remove()
                      // }
                      $("#previewResults").attr('src',data.data.userUpload.path[1])
                      var imgSrc1=$("#previewResults").attr("src")
                      // if(imgSrc1!=""){
                      //   $(".twoUpload").show()
                      // }else{
                      //   $("#previewResults").remove()
                      // }
                      $("#previewResultss").attr('src',data.data.userUpload.path[2])
                      var imgSrc2=$("#previewResultss").attr("src")
                      // if(imgSrc2!=""){
                      // }else{
                      //   $("#previewResultss").remove()
                      // }
                      $(".alert").hide()
                      $(".border").css("border-bottom","0.01rem solid #aeaeae");
                      $(".border:last").css("border-bottom","none");
                  }
                }
                if(data.data.type==2){
                   $(".alert").html("亲，请上传视频。")
                   $(".explain").append(
                     ' <h3>投稿说明：</h3>'+
                      '<p>1.只可上传1个视频。</p>'+
                      '<p>2.上传视频建议采用微信短视频后保存上传，视频限制最大12MB。</p>'+
                      '<p>3.投稿成功后需活动方审核通过后才能在相关页面显示，可在“我的作品”页查看审核状态。</p>'
                    )
                    if(data.data.userUpload.upload_status==2){
                        var title=data.data.userUpload.title;
                        var userName=data.data.userUpload.username;
                        var mobile=data.data.userUpload.mobile;
                        var src=data.data.userUpload.path;
                        $("#title").val(title)
                        $("#userName").val(userName)
                        $("#mobile").val(mobile)
                        $("#img").css("height","7.23rem")
                        $("#img").html('<div class="video"><video src="'+src+'" id="video" autoplay></video></div>')
                         $(".alert").hide()
                    }
                }
            }    
        }
    })
})


 $(".border").css("border-bottom","0.01rem solid #aeaeae");
  $(".border:last").css("border-bottom","none");

function isIE(){
    return (document.all && window.ActiveXObject && !window.opera) ? true : false;
  }

    var myCrop;
 // 防require缓存
    require.config({
        urlArgs:"bust="+new Date,
        baseUrl:"/Public/vendors/upload-image/js",
    });
    require(["jquery",'hammer','tomPlugin',"tomLib",'hammer.fake','hammer.showtouch'],function($,hammer,plugin,T){
      // 下面这段代码会使手机不能滑动
        // document.addEventListener("touchmove",function(e){
        //     e.preventDefault();
        // })
        //初始化图片大小300*300
        var opts={cropWidth:200,cropHeight:200},
                $file=$("#file"),
                $files=$("#files"),
                $filess=$("#filess"),
                $needCropImgss=$("#needCropImgss"),
                $needCropImgs=$("#needCropImgs"),
                $needCropImg=$("#needCropImg");
                $getFiless=$("#getFiless"),
                $getFiles=$("#getFiles"),
                $getFile=$("#getFile"),
                $previewResult=$("#previewResult"),
                $previewResults=$("#previewResults"),
                $previewResultss=$("#previewResultss"),
                previewStyle={x:0,y:0,scale:1,rotate:0,ratio:1},
                transform= T.prefixStyle("transform"),
                $previewBox=$(".preview-box"),
                $rotateBtn=$("#rotateBtn"),
                $preview=$("#preview"),
                $uploadPage=$("#uploadPage"),
                $mask=$(".upload-mask"),
                $loading=$(".upload-loading"),
                maskCtx=$mask[0].getContext("2d");
               

        //这是插件调用主体
       
       
         
        $('#fileChooseButton').on('click',function(){
          $(".copy").show()
          $(".first").show();
          $(".second").hide();
          $(".third").hide();
          setTimeout(function(){
                resetUserOpts();
            })
          var opts={cropWidth:200,cropHeight:200},
               	$file=$("#file"),
                $needCropImg=$("#needCropImg");
                $getFile=$("#getFile"),
                $previewResult=$("#previewResult"),
                previewStyle={x:0,y:0,scale:1,rotate:0,ratio:1},
                transform= T.prefixStyle("transform"),
                $previewBox=$(".preview-box"),
                $rotateBtn=$("#rotateBtn"),
                $preview=$("#preview"),
                $uploadPage=$("#uploadPage"),
                $mask=$(".upload-mask"),
                $loading=$(".upload-loading"),
                maskCtx=$mask[0].getContext("2d");
           myCrop=T.cropImage({
            bindFile:$file,//绑定Input file
//            bindFile:$needCropImg[0],//绑定一个图片
            enableRatio:true,//是否启用高清,高清得到的图片会比较大
            canvas:$(".photo-canvas")[0],  //放一个canvas对象
            cropWidth:opts.cropWidth,       //剪切大小
            cropHeight:opts.cropHeight,
            bindPreview:$preview,      //绑定一个预览的img标签
            useHammer:true,            //是否使用hammer手势，否的话将不支持缩放
            oninit:function(){
            },
            onChange:function(){
                $loading.show();
                resetUserOpts();
            },
            onLoad:function(data){
                //用户每次选择图片后执行回调
                previewStyle.ratio=data.ratio;
                $preview.attr("src",data.originSrc).css({width:data.width,height:data.height}).css(transform,'scale('+1/previewStyle.ratio+')');
                myCrop.setCropStyle(previewStyle)
                $loading.hide();
            }
        });
       

            
        })
        
         $('#fileChooseButtons').on('click',function(){
          $(".copy").show()
          $(".first").hide();
          $(".second").show();
          $(".third").hide();
            setTimeout(function(){
                resetUserOptss();
            })
            var opts={cropWidth:200,cropHeight:200},
                $file=$("#file"),
                $files=$("#files"),
                $filess=$("#filess"),
                $needCropImgss=$("#needCropImgss"),
                $needCropImgs=$("#needCropImgs"),
                $needCropImg=$("#needCropImg");
                $getFiless=$("#getFiless"),
                $getFiles=$("#getFiles"),
                $getFile=$("#getFile"),
                $previewResult=$("#previewResult"),
                $previewResults=$("#previewResults"),
                $previewResultss=$("#previewResultss"),
                previewStyle={x:0,y:0,scale:1,rotate:0,ratio:1},
                transform= T.prefixStyle("transform"),
                $previewBox=$(".preview-box"),
                $rotateBtn=$("#rotateBtn"),
                $preview=$("#preview"),
                $uploadPage=$("#uploadPage"),
                $mask=$(".upload-mask"),
                $loading=$(".upload-loading"),
                maskCtx=$mask[0].getContext("2d");
//        console.log($("#getFile").attr("class","getFil"))
        myCrop=T.cropImage({
            bindFile:$files,//绑定Input file
//            bindFile:$needCropImg[0],//绑定一个图片
            enableRatio:true,//是否启用高清,高清得到的图片会比较大
            canvas:$(".photo-canvas")[0],  //放一个canvas对象
            cropWidth:opts.cropWidth,       //剪切大小
            cropHeight:opts.cropHeight,
            bindPreview:$preview,      //绑定一个预览的img标签
            useHammer:true,            //是否使用hammer手势，否的话将不支持缩放
            oninit:function(){

            },
            onChange:function(){
                $loading.show();
                resetUserOptss();
            },
            onLoad:function(data){
                //用户每次选择图片后执行回调
                previewStyle.ratio=data.ratio;
                $preview.attr("src",data.originSrc).css({width:data.width,height:data.height}).css(transform,'scale('+1/previewStyle.ratio+')');
                myCrop.setCropStyle(previewStyle)
                $loading.hide();
            }
        });
          
        })
        $('#fileChooseButtonss').on('click',function(){
          $(".copy").show()
          $(".first").hide();
          $(".second").hide();
          $(".third").show();
            setTimeout(function(){
                resetUserOptsss();
            })
            var opts={cropWidth:300,cropHeight:300},
                $file=$("#file"),
                $files=$("#files"),
                $filess=$("#filess"),
                $needCropImgss=$("#needCropImgss"),
                $needCropImgs=$("#needCropImgs"),
                $needCropImg=$("#needCropImg");
                $getFiless=$("#getFiless"),
                $getFiles=$("#getFiles"),
                $getFile=$("#getFile"),
                $previewResult=$("#previewResult"),
                $previewResults=$("#previewResults"),
                $previewResultss=$("#previewResultss"),
                previewStyle={x:0,y:0,scale:1,rotate:0,ratio:1},
                transform= T.prefixStyle("transform"),
                $previewBox=$(".preview-box"),
                $rotateBtn=$("#rotateBtn"),
                $preview=$("#preview"),
                $uploadPage=$("#uploadPage"),
                $mask=$(".upload-mask"),
                $loading=$(".upload-loading"),
                maskCtx=$mask[0].getContext("2d");
//        console.log($("#getFile").attr("class","getFil"))
        myCrop=T.cropImage({
            bindFile:$filess,//绑定Input file
//            bindFile:$needCropImg[0],//绑定一个图片
            enableRatio:true,//是否启用高清,高清得到的图片会比较大
            canvas:$(".photo-canvas")[0],  //放一个canvas对象
            cropWidth:opts.cropWidth,       //剪切大小
            cropHeight:opts.cropHeight,
            bindPreview:$preview,      //绑定一个预览的img标签
            useHammer:true,            //是否使用hammer手势，否的话将不支持缩放
            oninit:function(){

            },
            onChange:function(){
                $loading.show();
                resetUserOptsss();
            },
            onLoad:function(data){
                //用户每次选择图片后执行回调
                previewStyle.ratio=data.ratio;
                $preview.attr("src",data.originSrc).css({width:data.width,height:data.height}).css(transform,'scale('+1/previewStyle.ratio+')');
                myCrop.setCropStyle(previewStyle)
                $loading.hide();
            }
        });
          
        })
        //获取图片并关闭弹窗返回到表单界面
        $getFiles.on("click",function(){
          $(".copy").hide()
          var imgSrc=$("#preview").attr("src");
          if(imgSrc!=""){
            var cropInfo;
            myCrop.setCropStyle(previewStyle)
            //自定义getCropFile({type:"png",background:"red",lowDpi:true})
            cropInfo=myCrop.getCropFile({});
//          console
            $previewResults.attr("src",cropInfo.src).show();
            $("#upload_image1").val(cropInfo.src)
           }
            $uploadPage.hide();
        })
        $getFile.on("click",function(){
          $(".copy").hide()
          var imgSrc=$("#preview").attr("src");
          
          if(imgSrc!=""){
            var cropInfo;
              myCrop.setCropStyle(previewStyle)
              //自定义getCropFile({type:"png",background:"red",lowDpi:true})
              cropInfo=myCrop.getCropFile({});
  //          console
              $previewResult.attr("src",cropInfo.src).show();
              $("#upload_image").val(cropInfo.src)
          }
           $uploadPage.hide();
        })
         $getFiless.on("click",function(){
          $(".copy").hide()
          var imgSrc=$("#preview").attr("src");
          if(imgSrc!=""){
            var cropInfo;
            myCrop.setCropStyle(previewStyle)
            //自定义getCropFile({type:"png",background:"red",lowDpi:true})
            cropInfo=myCrop.getCropFile({});
//          console
            $previewResultss.attr("src",cropInfo.src).show();
            $("#upload_image2").val(cropInfo.src)
           }
             $uploadPage.hide();
        })

  function resetUserOpts(){
            $(".photo-canvas").hammer('reset');
            previewStyle={scale:1,x:0,y:0,rotate:0};
//          $previewResult.attr("src",'').hide();
            $preview.attr("src",'')
        }
        function resetUserOptss(){
            $(".photo-canvas").hammer('reset');
            previewStyle={scale:1,x:0,y:0,rotate:0};
//          $previewResults.attr("src",'').hide();
            $preview.attr("src",'')
        }
         function resetUserOptsss(){
            $(".photo-canvas").hammer('reset');
            previewStyle={scale:1,x:0,y:0,rotate:0};
//          $previewResultss.attr("src",'').hide();
            $preview.attr("src",'')
        }
        $(".photo-canvas").hammer({
            gestureCb:function(o){
                //每次缩放拖拽的回调
                $.extend(previewStyle,o);
//                console.log("用户修改图片",previewStyle)
                $preview.css(transform,"translate3d("+ previewStyle.x+'px,'+ previewStyle.y+"px,0) rotate("+previewStyle.rotate+"deg) scale("+(previewStyle.scale/previewStyle.ratio)+")")
            }
        })
        //选择图片
        $rotateBtn.on("click",function(){
            previewStyle.rotate+=90;
            if(previewStyle.rotate>=360){
                previewStyle.rotate-=360;
            }
            $(".photo-canvas").hammer('setRotate',previewStyle.rotate)
            myCrop.setCropStyle(previewStyle)
            $preview.css(transform,"translate3d("+ previewStyle.x+'px,'+ previewStyle.y+"px,0) rotate("+previewStyle.rotate+"deg) scale("+(previewStyle.scale/previewStyle.ratio)+")")
        })
        //上传文件按钮&&关闭弹窗按钮
        $(document).delegate("#file","click",function(){
            $uploadPage.show();
        }).delegate("#closeCrop","click",function(){
          $(".copy").hide()
            $uploadPage.hide();
            resetUserOpts();
            myCrop.setCropStyle(previewStyle)
        })
        $(document).delegate("#files","click",function(){
            $uploadPage.show();
        }).delegate("#closeCrops","click",function(){
          $(".copy").hide()
            $uploadPage.hide();
            resetUserOptss();
            myCrop.setCropStyle(previewStyle)
        })
        $(document).delegate("#filess","click",function(){
            $uploadPage.show();
        }).delegate("#closeCropss","click",function(){
          $(".copy").hide()
            $uploadPage.hide();
            resetUserOptsss();
            myCrop.setCropStyle(previewStyle)
        })
        $file.one("click",showCropModal)
        $files.one("click",showCropModal)
        $filess.one("click",showCropModal)
//      $previewResult.on('click',showCropModal)
//      $previewRes.on('click',showCropModal)

        function showCropModal(){
            setTimeout(function(){
                $uploadPage.show();
                $mask.prop({width:$mask.width(),height:$mask.height()})
                maskCtx.fillStyle="rgba(0,0,0,0.7)";
                maskCtx.fillRect(0,0,$mask.width(),$mask.height());
                maskCtx.strokeStyle='white';
                maskCtx.lineWidth='2'
                maskCtx.clearRect(($mask.width()-opts.cropWidth)/2,($mask.height()-opts.cropHeight)/2,opts.cropWidth,opts.cropHeight)
                maskCtx.strokeRect(($mask.width()-opts.cropWidth)/2-1,($mask.height()-opts.cropHeight)/2-1,opts.cropWidth+2,opts.cropHeight+2);//Add a subpath with four points
            })
        }
        //单独绑定图片时用到
//        $needCropImg[0].addEventListener("load",showCropModal)
//        $needCropImg[0].src='./img/9-1.jpg';
    })


//var input = document.getElementById("files");
//input.addEventListener('change',images,false);
//function images(){
//   if($("input[type='file']").val()==""){
//          return
//      }
//  	var name=this.value;
//      var fileName = name.substring(name.lastIndexOf(".")+1).toLowerCase();
//      if(fileName !="jpg" && fileName !="jpeg" && fileName !="pdf" && fileName !="png" && fileName !="dwg" && fileName !="gif" && fileName !="png"){
//        alert("请选择图片格式文件上传(jpg,jpeg,png,gif,dwg,pdf,gif等)！");
//          this.value="";
//          $uploadPage.hide();
//           $(".copy").hide()
//          return
//      }
//      var fileSize = 0; 
//     if (isIE() && !this.files) {     
//       var filePath = this.value;     
//       var fileSystem = new ActiveXObject("Scripting.FileSystemObject");        
//       var file = fileSystem.GetFile (filePath);     
//       fileSize = file.Size;    
//      } else {    
//      fileSize = this.files[0].size;     
//      }       
//       var size = fileSize / 1024;    
//      if(size>1000){
//          alert("附件不能大于1M");
//          this.value="";
//           $uploadPage.hide();
//           $(".copy").hide()
//          return
//      }
//}


    // 提交按钮进行相对应的判断
    function sub(){
      // var attentions=window.sessionStorage.getItem("attentions")
      var userStatus=$("#userStatus").val();
      var upload_status=window.sessionStorage.getItem("upload_status")
      var statuss=window.sessionStorage.getItem("statuss")
      var upload_type=window.sessionStorage.getItem("upload_type")
       if($("#title").val()==""){
        alert("请先填写您的作品名称。")
        return
      }
      if($("#userName").val()==""){
        alert("请先填写您的名字。")
        return
      }
      if($("#mobile").val()==""){
        alert("请先填写您的电话号码。")
        return
      }
        var path = [];
        if(upload_type == 1){
            path = [
                $("#upload_image").val(),
                $("#upload_image1").val(),
                $("#upload_image2").val()
            ];
        }else if(upload_type == 2){
            path = $("#upload_image").val();
        }
        var is_share=GetRequest().is_share;
        var share_user_id=GetRequest().share_user_id;
      if(upload_status==1){
        $(".sub").show()
        $.ajax({   
          type: "post",  
          url:"/index.php/Vote/ajaxContribution",  
          async: true, 
          dataType:"json",
          data:{
              title:$("#title").val(),
              vote_id:$("#vote_id").val(),
              username:$("#userName").val(),
              mobile:$("#mobile").val(),
              age_bracket:$("#age_bracket option:selected").val(),
              path:path,
              share_user_id:share_user_id,
              is_share:is_share
          }, 
          success: function(data) {
            $(".sub").hide()
             if(data.status==0){     
                setTimeout(function(){
                    alert(data.message+",请重新上传。")
                },500)
             }
              if(data.status==1){
                 if(userStatus==1){
                    setTimeout(function(){
                       alert(data.message)
                       window.location.href="/index.php/Vote/userWorks.html?vote_id="+vote_id;         
                     },500) 
                   }else if(userStatus==2){
                    $(".tellBox").fadeIn(1500);
                    $(".closeBtn").click(function(){
                      window.location.href="/index.php/Vote/userWorks.html?vote_id="+vote_id;
                    })
                   }else{
                    setTimeout(function(){
                       alert(data.message)
                        window.location.href="/index.php/Vote/userWorks.html?vote_id="+vote_id;        
                     },500) 
                   }
               }
          }  
        }) 
      }
      if(upload_status==2){
        if(statuss==2){
          alert("您的投稿已经通过了，不能重新进行投稿了，赶快去我的作品页点击图片到作品详情页分享朋友圈拉票吧！") 
          window.location.href="/index.php/Vote/userWorks.html?vote_id="+vote_id;        
        }
        else{
          var confirm=window.confirm("重新投稿将会换掉之前投稿的图片，您确定更改吗？")
          if(confirm==true){
            $(".sub").show()
            $.ajax({   
              type: "post",  
              url:"/index.php/Vote/ajaxContribution",  
              async: true, 
              dataType:"json",
              data:{
                  title:$("#title").val(),
                  vote_id:$("#vote_id").val(),
                  username:$("#userName").val(),
                  mobile:$("#mobile").val(),
                  age_bracket:$("#age_bracket option:selected").val(),
                  path:path
              }, 
              success: function(data) {
                $(".sub").hide()
                 if(data.status==0){     
                    setTimeout(function(){
                        alert(data.message+",请重新上传。")
                    },500)
                 }
                  if(data.status==1){
                    if(userStatus==1){
                          setTimeout(function(){
                           alert(data.message)
                           window.location.href="/index.php/Vote/userWorks.html?vote_id="+vote_id;      
                         },500)  
                          
                         }else if(userStatus==2){
                          $(".tellBoxs").fadeIn(1500);
                          $(".closeBtn").click(function(){
                            window.location.href="/index.php/Vote/userWorks.html?vote_id="+vote_id;
                          })
                         }else{
                           setTimeout(function(){
                           alert(data.message)
                           window.location.href="/index.php/Vote/userWorks.html?vote_id="+vote_id;        
                         },500)  
                      }
                   }
              }  
          }) 
        }
        }
        
      }
    }
    // 微信端分享
    $.ajax({
        type: "get",
        url: "/index.php/Vote/voteListContribution",
        aysnc: true,
        dataType: "json",
        data:{
            vote_id:vote_id,
        },
        success:function(data){
            // console.log(data);
            var title=data.data.share_title;
            var f_title=data.data.share_desc;
            var shareUrl=data.data.share_url;
            var shareImg=data.data.share_img;
            // 调用微信的分享接口
            wx.config({
                debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                appId: "<?php echo ($signPackage['appId']); ?>", // 必填，公众号的唯一标识
                timestamp: "<?php echo ($signPackage['timestamp']); ?>", // 必填，生成签名的时间戳
                nonceStr: "<?php echo ($signPackage['nonceStr']); ?>", // 必填，生成签名的随机串
                signature: "<?php echo ($signPackage['signature']); ?>",// 必填，签名，见附录1
                jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'closeWindow', 'chooseImage', 'uploadImage', 'getLocation'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            });
            wx.ready(function () {
                wx.onMenuShareAppMessage({
                    title: title,
                    desc: f_title,
                    link: shareUrl,
                    imgUrl: shareImg,
                    success: function () {
                        share_record();
                    },
                    cancel: function () {
                        alert('已取消');
                    }
                });
                wx.onMenuShareTimeline({
                    title: title,
                    link: shareUrl,
                    imgUrl: shareImg,
                    success: function () {
                        share_record();// 用户确认分享后执行的回调函数
                    },
                    cancel: function () {
                        alert('已取消');// 用户取消分享后执行的回调函数
                    }
                });

                function share_record() {
                    var data = new Array();
                    data[id] = getQueryString(id);
                    data[share_user_id] = getQueryString(share_user_id);
                 var url = "/index.php/Vote/userContributionRecord";
                 $.post(url, data, function (result) {
                 alert(result.message);
                 }, 'json');
                 }
            });
        }
    });
</script>
</body>
</html>
<!--tailTrap<body></body><head></head><html></html>-->