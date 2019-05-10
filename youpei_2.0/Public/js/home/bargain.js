 $(function(){
    // 点击邀请更多人帮忙的弹框显示与隐藏
    $(".more").click(function(){
        $(".morePeople").show()
    })
    $(".morePeople").click(function(){
        $(".morePeople").hide()
    })
    $(".type").click(function(){
        $(".type").hide()
    })
});
// 获取url中的参数
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
var share_user_id=GetRequest().share_user_id;
var pro_id = GetRequest().pro_id;
var key = GetRequest().key;
// 点击果断买单按钮跳转到买单页
function buy(){
    var count = $('#count').text();
    if(count < 1){
        alert('暂时缺货');
    }else{
        location.href="/index.php/Product/bargainCheckOrderInformation.html?pro_id="+pro_id+"&key="+key;
    }
}

// 遍历循环砍价页和帮TA砍价页
var inputTitle;
var inputFtitle;
var inputUrl;
var inputImg;
if(share_user_id){
     $.ajax({
             type: "get",
             url: "/index.php/Product/ajaxBargain",
             aysnc: true,
             dataType: "json",
             data:{
                 pro_id:pro_id,
                 share_user_id:share_user_id,
                 key:key,
             },
             success:function(data){
                 var isMe=data.data.isMe;
                 var original_price=data.data.product.now_price;
                 var img=data.data.product.pic_url;
                 var title=data.data.product.title;
                 var f_title=data.data.product.f_title;
                 var shareUrl=data.data.shareUrl;
                 var shareImg=data.data.shareImg;
                 var bargainPeople=data.data.bargainPeople;
                 var bargainPrice=data.data.bargainPrice;
                 var userImg=data.data.userImage;
                 var userPrice=data.data.userPrice;
                 var totalBargainPrice = data.data.totalBargainPrice;
                 var unit = data.data.product.unit;
                 inputUrl=shareUrl;
                 inputImg=shareImg;
                 inputTitle='[优培·砍价]'+ title;
                 inputFtitle=f_title;
                    $(".product").append(
	                        '<div class="img">'+
	                            '<img src="'+img+'" alt="">'+
	                        '</div>'+
	                        '<div class="content">'+
	                            '<h3>'+title+data.data.product.class_normal+'</h3>'+
	                            '<div class="now">'+
	                                '<span>原价：</span>'+
	                                '<span>￥</span>'+
	                                '<span>'+original_price+'</span>'+
	                                '<span>/'+unit+'</span>'+
	                            '</div>'+
	                        '</div>'
                    )
                 $(".num").html(bargainPeople);
                 // 遍历帮忙砍价头像
                  for(var i=0; i<bargainPeople; i++){
                    $(".userImg").append(
                            '<li>'+
                                '<img src="'+userImg[i]+'" alt="">'+
                                '<span style="text-align: center;width: 100%; display: block; color: #436e98; padding: 0.05rem 0;font-size:0.35rem;">'+userPrice[i]+'元</span>'+
                            '</li>'
                        )
                 }


                 // 进度条
                  if(bargainPrice==null){
                     bargainPrice=0;
                 }
                 $(".much").html(bargainPrice)
                 // $(".green").html(bargainPrice/totalBargainPrice*100+"%");
                 // $(".gray span").html(bargainPrice+'元');
                 var width=bargainPrice/totalBargainPrice*100;
                 $(".green").css("width",width+"%");
                 var realPrice = original_price - bargainPrice;
                 $('#addOrder').text("实付："+ realPrice.toFixed(2) + '元');
                 // 判断是否本人的Id
                    if(isMe==1){
                        $(".bargain").hide();
                        $(".helpBargain").show();
                        $(".footer").show();
                        $(".num").html(bargainPeople);
                        $(".header h3").html("帮TA砍价");
                        $("title").html("帮TA砍价")
                        $(".footer ul li>img").css("display","none");
                        $(".footer ul li>img:eq(0)").css("display","block");
                        $(" .footer ul li").click(function(){
                            $(this).addClass('on').siblings().removeClass("on");
                            var index=$(this).index();
                            var liLen=$(".footer ul li").length;
                            for(var i=0;i<=liLen-1;i++){
                                $(".footer ul li>img").eq(i).css("display","none");
                            }
                            $(this).find("img:eq(0)").css("display","block");
                        });
                        // $(".also").click(function(){
                        //     $(".share").show();
                        // })
                        // $(".share").click(function(){
                        //     $(".share").hide()
                        // })
                    }else{
                        var isBuy=data.data.isBuy;
                        var id=data.data.product.id;
                        var desc=data.data.product.desc;
                        inputUrl=shareUrl;
                        inputImg=shareImg;
                        inputTitle=title;
                        inputFtitle=f_title;
                        $(".detailsText").append(desc)
                        $(".bargain").show();
                        $(".header h3").html("优培·砍价");
                        $("title").html("优培·砍价");
                        $(".num").html(bargainPeople);
                        // 判断进来状态
                        if(isBuy=="0"){
                            $(".type").show();
                            $(".type p").html("您选购的商品未付款哦，请到“个人中心-待付款”处付款。")
                        }
                        if(isBuy=="1"||isBuy=="4"||isBuy=="6"||isBuy=="7"){
                            $(".type").show();
                            $(".type p").html("当前砍价活动优惠每人仅限购一次，您已经买过了，欢迎下次再来参与！")
                        }
                        // 进度条
                        if(bargainPrice==null){
                            bargainPrice=0;
                        }
                        $(".green-child").html((bargainPrice)+"/"+totalBargainPrice.split(".")[0]);
                        // $(".gray span").html(bargainPrice+'元');
                        var width=bargainPrice/totalBargainPrice*100;
                        $(".green").css("width",width+"%");
                        var realPrice = original_price - bargainPrice;
                        $('#addOrder').text("实付："+ realPrice.toFixed(2) + '元');
                    }

             }
        })
}else{
         $.ajax({
             type: "get",
             url: "/index.php/Product/ajaxBargain",
             aysnc: true,
             dataType: "json",
             data:{
                 pro_id:pro_id,
                 share_user_id:0,
                 key:key
             },
             success:function(data){
                if(data.status==0){
                    alert(data.message);
                    window.history.go(-1);
                }
                var isBuy=data.data.isBuy;
                var original_price=data.data.product.now_price;
                var img=data.data.product.pic_url;
                var title=data.data.product.title;
                var f_title=data.data.product.f_title;
                var shareUrl=data.data.shareUrl;
                var shareImg=data.data.shareImg;
                var bargainPeople=data.data.bargainPeople;
                var bargainPrice=data.data.bargainPrice;
                var userImg=data.data.userImage;
                var userPrice=data.data.userPrice;
                var totalBargainPrice = data.data.totalBargainPrice;
                var id=data.data.product.id;
                var desc=data.data.product.desc;
                 var unit = data.data.product.unit;
                inputUrl=shareUrl;
                inputImg=shareImg;
                inputTitle=title;
                inputFtitle=f_title;
                $(".detailsText").append(desc)
                $(".product").append(
                	'<div onclick="getProduct('+id+')">'+
                        '<div class="img">'+
                            '<img src="'+img+'" alt="">'+
                        '</div>'+
                        '<div class="content">'+
                            '<h3>'+title+data.data.product.class_normal+'</h3>'+
                            '<div class="now">'+
                                '<span>原价：</span>'+
                                '<span>￥</span>'+
                                '<span>'+original_price+'</span>'+
                                '<span>/'+unit+'</span>'+
                            '</div>'+
                        '</div>'+
                    '</div> '
                    );
                $(".bargain").show();
                $(".header h3").html("优培·砍价");
                $("title").html("优培·砍价");
                $(".num").html(bargainPeople);
                 // 遍历帮忙砍价头像
                  for(var i=0; i<bargainPeople; i++){
                    $(".userImg").append(
                            '<li>'+
                                '<img src="'+userImg[i]+'" alt="">'+
                                '<span style="text-align: center;width: 100%; display: block; color: #436e98; padding: 0.05rem 0; font-size:0.35rem;">'+userPrice[i]+'元</span>'+
                            '</li>'
                        )
                 }
                  // 判断进来状态
                 if(isBuy=="0"){
                    $(".type").show();
                    $(".type p").html("您选购的商品未付款哦，请到“个人中心-待付款”处付款。")
                }
                if(isBuy=="1"||isBuy=="4"||isBuy=="6"||isBuy=="7"){
                    $(".type").show();
                    $(".type p").html("当前砍价活动优惠每人仅限购一次，您已经买过了，欢迎下次再来参与！")
                }
                 // 进度条
                if(bargainPrice==null){
                    bargainPrice=0;
                }
                $(".green-child").html((bargainPrice)+"/"+totalBargainPrice.split(".")[0]);
                // $(".gray span").html(bargainPrice+'元');
                 var width=bargainPrice/totalBargainPrice*100;
                 $(".green").css("width",width+"%");
                 var realPrice = original_price - bargainPrice;
                 $('#addOrder').text("实付："+ realPrice.toFixed(2));
             }
    })
}

    // 点击"果断帮忙减价"按钮获取状态提示不同信息
$(".help").click(function(){
    $.ajax({
        type:"get",
        aysnc:true,
        url:"/index.php/Product/bargainHelp",
        dataType:"json",
        data:{
            share_user_id:share_user_id,
            pro_id:pro_id,
            key:key,
        },
        success:function(data){
            console.log(data)
            var reducePrice=data.data;
            if(data.status==1){
                $(".box").show();
                $(".reducePrice").html(reducePrice+"元")
            }
            if(data.status==2){
                $(".box").show();
                $(".reduce p").html("你已经帮他砍过价了呢,更多精彩请长按下方二维码进行识别关注哦！")
            }
            if(data.status==3){
                $(".box").show();
                $(".reduce p").html("人家已经完成砍价了呢,更多精彩请长按下方二维码进行识别关注哦！")
            }
        }
    })
});
// 点击隐藏弹框
$(".confirm").click(function(){
    $(".box").hide()
});
$(".close").click(function(){
     $(".box").hide()
});
//查看商品
function getProduct(id) {
    window.location.href = "/index.php/Product/productDetails?pro_id=" + id;
}

// 生成海报图片
 function createBargainPoster() {
     layer.msg('生成海报中，请耐心等候');
     var pro_id = GetRequest().pro_id;
     var key = GetRequest().key;
     var ajaxUrl = "/index.php/Product/createBargainPoster";
     $.get(ajaxUrl, {pro_id:pro_id,key:key},function (result) {
         if (result.status == 0) {
             return alert(result.message);
         }
         if(result.status == 1) {
             var imageUrl = result.data;
             layer.open({
                 type: 1,
                 title: '分享砍价海报',
                 skin: 'layui-layer-rim',
                 area: ['80%', '17rem'], //宽高
                 content:"<div><img src='"+imageUrl+"' style='width: 100%;height:15.5rem;'></div>",
                 cancel:function () {
                     $.post('/index.php/ScanResponse/unlinkImage',{image:imageUrl});
                 }
             });
         }
     }, 'json');
 }