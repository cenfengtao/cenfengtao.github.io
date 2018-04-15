<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>优培圈</title>
    <link rel="stylesheet" href="/Public/vendors/upload-image/css/page-common.css">
    <link rel="stylesheet" type="text/css" href="http://www.jq22.com/jquery/font-awesome.4.6.0.css">
    <link rel="stylesheet" href="/Public/vendors/upload-image/css/upload.css"/>
    <link rel="stylesheet" type="text/css" href="/Public/css/home/poster.css"/>
    <script type="text/javascript" src="/Public/Home/js/jweixin-1.0.0.js"></script>
</head>
<body>
    <header class="header">
        <a href="javascript:history.back()">
            <img src="../../../../Public/images/back.png" alt="">
        </a>
        <h3>教师节</h3>
        <a href="../Index/index.html" class="home">
            <img src="../../../../Public/images/home.jpg" alt="">
        </a>
    </header>
    <article>
        <div class="wait">
            <div>
                正在合成中，请耐心等待哦！
            </div>
        </div>
        <div class="file">
            <div>
                请先上传图片再按确定哦。
            </div>
        </div>
        <div class="introduce">
            <div>
                <h3>欢迎来到教师节海报制作主页</h3>
                <p>1.请先选择你喜欢的海报模报。</p>
                <p>2.点击编辑海报上传图片进行合成。</p>
                <p>3.长按生成的海报保存图片到手机上。</p>
                <p>4.把保存的图片分享到朋友圈上，向老师们表达浓浓的敬意,感谢老师们平日里的悉心教导。</p>
            </div>
        </div>
        <div class="img">
            <img src="" alt="">
        </div>
        <div class="chose">
            <h3>海报模板</h3>
            <img src="../../../../Public/images/close.png" alt="">
            <ul class="poster">
            </ul>       
        </div>
        <div class="copy">
            <div class="content">
                <div class="text">
                    <p>请编辑图片</p>
                    <img src="../../../../Public/images/close.png" alt="">
                </div>
                <div id="height">
                    <ul class="uploadImg">
                        <li class="pic">
                            <span>图片：</span>
                            <div><img id="previewResult" alt="" /></div>
                            <a href="###" id='fileChooseButton' onclick="firstFile()">上传图片</a>
                            <input class="upload-file" type="file" id="file" accept="image/*" style="display: none" name="upload" />
                            <a href="###" id="close">移除图片</a>
                        </li>
                    </ul>
                    <ul class="uploadText">
<!--                         <li class="write">
                            <span>标题：</span> <input type="text" class="inputValue">
                        </li> -->
                    </ul> 
                        
                    <!-- <li>
                        <span>内容：</span> <textarea name="" rows="4" class="textarea"></textarea>
                    </li> -->
                </div>
                <div class="confirm">
                    <span>确定</span>
                </div>
            </div>
            <div class="edit">
                <!-- <button id='fileChooseButton' class="button blue rarrow file-input-mask">上传图片<input class="upload-file" type="file" id="file" accept="image/*"/ multiple></button> -->
                <img id="previewResult"/>
                <img id="needCropImg" />
                <div class="app" id="uploadPage">
                    <div class="upload-loading">
                    <span class="centerXY"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></span>
                    </div>
                    <div class="bar"><a class="back pull-left" id="closeCrop"><i class="fa fa-reply"></i></a><a id="getFile" class="pull-right">使用</a></div>
                    <div class="main">
                        <canvas class="upload-mask">
    
                        </canvas>
                        <div class="preview-box">
                            <img id="preview"/>
                        </div>
                        <canvas class="photo-canvas">
    
                        </canvas>
                    </div>
                </div>
            </div>
            
        </div>
    </article>
    <footer class="footer">
        <ul class="radio">
            <li><a href="###" class="need">请选择海报模板</a></li>
            <li><a href="###" class="change">编辑海报</a></li>
        </ul>
        <p>请选择海报模板后再点击编辑海报哦！</p>
    </footer>
</body>
<script src="/Public/Home/js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/Public/vendors/upload-image/js/require.js"></script>
<script src="/Public/vendors/upload-image/js/main.js"></script>
<script src="/Public/vendors/upload-image/js/canvas-toBlob.js"></script>
<script>

function firstFile() { 
    document.getElementById("file").click(); 
    $(".content").hide();
    $("body .img img").hide();
} 
function GetRequest() {   
   var url = location.search; //获取url中"?"符后的字串   
   var theRequest = new Object();   
   if (url.indexOf("?") != -1) {   
      var str = url.substr(1);   
      strs = str.split("&");   
      for(var i = 0; i < strs.length; i ++) {   
         theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);   
      }   
   }   
   return theRequest;   
}   
var style_id=GetRequest().style_id;
$(function(){
    // var windowHeight=$(window).height();
    // var headerHeight=$(".header").height();
    // var changeHeight=$(".change").height();
    // var imgHeight=windowHeight-headerHeight-changeHeight;
    // $(".img img").attr("height",imgHeight)
    // console.log(style_id)
    $(".introduce").click(function(){
        $(".introduce").hide()
    })
    $(".file").click(function(){
        $(".file").hide();
        $(".copy").show();
    })
    $(".chose>img").click(function(){
       $(".chose").animate({height:'0px'},1000)
    })
    $(".text>img").click(function(){
        $(".copy").hide();
    })
    $(".need").click(function(){
        $(".chose").animate({height:'282px'},1000)
    })
    $(".change").click(function(){
        $(".copy").show()
    })
    var picHeigtht=$("#height .pic").height();
    var picLength=$("#height .pic").length;
    var writeHeigtht=$("#height .write").height();
    var writeLength=$("#height .write").length; 
    var textHeight=$(".text").height();
    var confirmHeight=$(".confirm").height();
    $(".content").css("height",(picHeigtht*picLength+textHeight+confirmHeight+writeLength*writeHeigtht)+"px")
    $.ajax({
        url:'../Poster/qrcodeFirst',
        async: true,
        type:"post",
        dataType:"json",
        data:{
            style_id:style_id
        },
        success:function(data){
            console.log(data)
            // 默认进来获取图片路径
            var image=data.data.data.image;
            var image_type=JSON.parse(data.data.data.image_position);
            var imageName=image_type["1"];
            for(key in imageName){
                var addName=key;
            }
            var text_type=JSON.parse(data.data.data.text_position);
            var textName=text_type["1"];
            for(key in textName){
                var title=key;
            }
            $(".img img").attr("src",image);

            //遍历上传文字
            var text_count=data.data.data.text_count;
            for(var a=0; a<=text_count-1; a++){
                var text_title=data.data.data.text_title[a];
                var font_count=data.data.data.fontCount[a];
                var message=data.data.data.message[a];
                 text_title=JSON.parse(text_title);
                 message=JSON.parse(message);
                // console.log(text_title)
                $(".uploadText").append(
                        '<li class="write">'+
                            '<span>'+text_title+'</span> <input type="text" class="inputValue'+a+'" attr_title="'+text_title+'" placeholder="字数不能超过'+font_count+'个" maxlength="'+font_count+'" value="'+message+'">'+
                        '</li>'
                    )
            }
            $(".uploadText").append(
                        '<input type="hidden" class="text_count" value="'+text_count+'">'
                    )



            



   
                    

                // 遍历海报
            var imgL=data.data.imageData.length;
            var short=data.data.imageData;
            for(var i=0; i<=imgL-1; i++){
                // // console.log(short[i].image_position)
                // var image_type=JSON.parse(short[i].image_position);
                // var imageName=image_type["1"];
                //     for(key in imageName){
                //         var addName=key;
                //     }
                $(".poster").append(
                '<li>'+
                    '<img src="'+short[i].image+'" alt="" class="'+short[i].id+'">'+
                    '<span>'+short[i].title+'</span>'+
                    // '<input type="hidden" value="'+addName+'">'+
                '</li>'
                    )
            }

            // for(var i=0; i<)
             $(".poster li img").click(function(){
                 $(".uploadText").html("");



                var imgSrc=$(this).attr("src")
                $(".img img").attr("src",imgSrc)


                var imgClass=$(this).attr("class");
                $(".confirm span").attr("class",imgClass);
                var image_position=$(this).next().next().val()

                $(".chose").css("height","0px")
                $('html,body').animate({scrollTop: '0px'}, 1000);


                $.ajax({
                    url:'../Poster/qrcodeFirst',
                    async: true,
                    type:"post",
                    dataType:"json",
                    data:{
                        style_id:imgClass
                    },
                    success:function(data){
//                  	console.log(data)
                        var text_count=data.data.data.text_count;
                        for(var a=0; a<=text_count-1; a++){
                            var text_title=data.data.data.text_title[a];
                            var font_count=data.data.data.fontCount[a];
                            var message=data.data.data.message[a];
                             text_title=JSON.parse(text_title);
                             message=JSON.parse(message);
                            // console.log(text_title)
                            $(".uploadText").append(
                                    '<li class="write">'+
                                        '<span>'+text_title+'</span> <input type="text" class="inputValue'+a+'" attr_title="'+text_title+'" placeholder="字数不能超过'+font_count+'个" maxlength="'+font_count+'" value="'+message+'">'+
                                    '</li>'
                                )
                        }
                        $(".uploadText").append(
                                    '<input type="hidden" class="text_count" value="'+text_count+'">'
                                )
//                      $(".confirm span").click(function(){
//                          var count = $('.text_count').val();
//                          var text_title = {};
//                          for (var i = 0; i < count; i++) {
//                              text_title[i] = $('.inputValue'+i).attr('attr_title')+':'+ $('.inputValue'+i).val();
//                          }
//
//
//                          if($(this).attr("class")){
//                              style_id=$(this).attr("class");
//                          }else{
//                              style_id=GetRequest().style_id;
//                          }
//          //                 console.log(image)
//                          $(".copy").hide()
//                          var url=$("#previewResult").attr("src")
//                          var inputValue=$(".inputValue").val();
//                          var textarea=$(".textarea").val();
//                          if(url){
//                              $(".wait").show();
//                               $.ajax({
//                                  url:'../Poster/qrcodeByPoster',
//                                  async: true,
//                                  type:"post",
//                                  dataType:"json",
//                                  data:{
//                                      id:style_id,
//                                      background:image,
//                                      image_type:addName,
//                                      text:text_title,
//                                      image:url
//                                  },//后端无需在过滤头
//                                  success:function(data){
//                                      console.log(data)
//                                      $(".img img").attr("src","/"+data.data);
//                                      $(".wait").hide();
//                                  }
//                              })
//                          }else{
//                              $(".file").show();
//                          }
//
//                      })
                    }
                })
            })
             $(".confirm span").click(function(){
                var count = $('.text_count').val();
                var text_title = {};
                for (var i = 0; i < count; i++) {
                    text_title[i] = $('.inputValue'+i).attr('attr_title')+':'+ $('.inputValue'+i).val();
                }
                if($(this).attr("class")){
                    style_id=$(this).attr("class");
                }else{
                    style_id=GetRequest().style_id;
                }
//                 console.log(image)
                $(".copy").hide()
                var url=$("#previewResult").attr("src")
                // var inputValue=$(".inputValue").val();
                // var textarea=$(".textarea").val();
                if(url){
                    $(".wait").show();
                     $.ajax({
                        url:'../Poster/qrcodeByPoster',
                        async: true,
                        type:"post",
                        dataType:"json",
                        data:{
                            id:style_id,
                            background:image,
                            image_type:addName,
                            text:text_title,
                            image:url
                        },//后端无需在过滤头
                        success:function(data){
//                                  console.log(data)
                            $(".img img").attr("src","/"+data.data);
                            setTimeout(function(){
                            	 $(".wait").hide();
                            },3000)
                           
                        }
                    })
                }else{
                    $(".file").show();
                }

            })  
             // console.log(image_position)
             // console.log(style_id)
        }
    })
    $("#close").click(function(){
        $("#previewResult").removeAttr("src")
    })
    $(".fa").click(function(){
        $(".copy").show()

    })

})

    var myCrop;
   // 防require缓存
    require.config({
        urlArgs:"bust="+new Date,
        baseUrl:"/Public/vendors/upload-image/js",
    });
    require(["jquery",'hammer','tomPlugin',"tomLib",'hammer.fake','hammer.showtouch'],function($,hammer,plugin,T){
        // document.addEventListener("touchmove",function(e){
        //     e.preventDefault();
        // });
        //初始化图片大小300*300
        var opts={cropWidth:180,cropHeight:250},
                $file=$("#file"),
                previewStyle={x:0,y:0,scale:1,rotate:0,ratio:1},
                transform= T.prefixStyle("transform"),
                $previewResult=$("#previewResult"),
                $previewBox=$(".preview-box"),
                $rotateBtn=$("#rotateBtn"),
                $getFile=$("#getFile"),
                $preview=$("#preview"),
                $uploadPage=$("#uploadPage"),
                $mask=$(".upload-mask"),
                $loading=$(".upload-loading"),
                maskCtx=$mask[0].getContext("2d"),
                $needCropImg=$("#needCropImg");

        //这是插件调用主体
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
        function resetUserOpts(){
            $(".photo-canvas").hammer('reset');
            previewStyle={scale:1,x:0,y:0,rotate:0};
            // $previewResult.attr("src",'').hide();
            $preview.attr("src",'')
        }
        $('#fileChooseButton').on('click',function(){
            setTimeout(function(){
                resetUserOpts();
            })

        })
        $(".photo-canvas").hammer({
            gestureCb:function(o){
                //每次缩放拖拽的回调
                $.extend(previewStyle,o);
//                console.log("用户修改图片",previewStyle)
                $preview.css(transform,"translate3d("+ previewStyle.x+'px,'+ previewStyle.y+"px,0) rotate("+previewStyle.rotate+"deg) scale("+(previewStyle.scale/previewStyle.ratio)+")")
            }
        });
        //选择图片
        $rotateBtn.on("click",function(){
            previewStyle.rotate+=90;
            if(previewStyle.rotate>=360){
                previewStyle.rotate-=360;
            }
            $(".photo-canvas").hammer('setRotate',previewStyle.rotate)
            myCrop.setCropStyle(previewStyle)
            $preview.css(transform,"translate3d("+ previewStyle.x+'px,'+ previewStyle.y+"px,0) rotate("+previewStyle.rotate+"deg) scale("+(previewStyle.scale/previewStyle.ratio)+")")
        });
        //获取图片并关闭弹窗返回到表单界面
        $getFile.on("click",function(){
            $(".content").show()
            $("body .img img").show();
            var cropInfo;
            $uploadPage.hide();
            myCrop.setCropStyle(previewStyle)
            //自定义getCropFile({type:"png",background:"red",lowDpi:true})
            cropInfo=myCrop.getCropFile({});
            $previewResult.attr("src",cropInfo.src).show();
            // var url=cropInfo.src;
            //可选传base64或者file对象
            //cropInfo.src cropInfo.dfd

            //you can upload img base64  :cheers:)
            // $.ajax({
            //     url:'../Test/testUpload',
            //     async: true,
            //     type:"post",
            //     dataType:"json",
            //     data:{base64:cropInfo.src.substr(22),uploadType:'base64'},//后端无需在过滤头
            //     success:function(data){
            //         if(data.result==1){
            //             console.log(data.imgPath)
            //         }
            //     }
            // })
            //you can upload new img file :cheers:)
            // cropInfo.dfd.done(function(blob){
            //     if(blob){
            //         var formData=new FormData;
            //         blob.name='imgFile'
            //         formData.append("imgFile",blob)
            //         formData.append("uploadType",'imgFile');
            //         $.ajax({
            //             url:'http://127.0.0.1/testAdjustImg/upload.php',
            //             type:"post",
            //             data:formData,
            //             processData:false,
            //             contentType: false,
            //             dataType:"json",
            //             success:function(data){
            //                 console.log(data)
            //                 if(data.result==1){
            //                     console.log(data.imgPath)
            //                 }
            //             }
            //         })
            //     }
            // })

        })
        //上传文件按钮&&关闭弹窗按钮
        $(document).delegate("#file","click",function(){
            $uploadPage.show();
        }).delegate("#closeCrop","click",function(){
            $uploadPage.hide();
            $(".content").show()
            $("body .img img").show();
            resetUserOpts();
            myCrop.setCropStyle(previewStyle)
        })
        $file.one("click",showCropModal)
        $previewResult.on('click',showCropModal)

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
        title: "<?php echo ($shareData['share_title']); ?>",
        desc: "<?php echo ($shareData['share_desc']); ?>",
        link: "<?php echo ($shareData['share_url']); ?>",
        imgUrl: "<?php echo ($shareData['share_img']); ?>",
        success: function () {
            alert('分享成功');
        },
        cancel: function () {
            alert('已取消');
        }
    });
    wx.onMenuShareTimeline({
        title: "<?php echo ($shareData['share_title']); ?>",
        link: "<?php echo ($shareData['share_url']); ?>",
        imgUrl: "<?php echo ($shareData['share_img']); ?>",
        success: function () {
            alert('分享成功');// 用户确认分享后执行的回调函数
        },
        cancel: function () {
            alert('已取消');// 用户取消分享后执行的回调函数
        }
    });
    wx.error(function (res) {
//            alert(JSON.stringify(res));
    });
});
</script>
</html>