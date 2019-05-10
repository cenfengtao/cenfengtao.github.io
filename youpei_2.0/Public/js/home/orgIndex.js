$(function(){
// 延时加载底部导航
$(window).load(function (){
    setTimeout(function () {
        $('.footer').show();
    },2000)
});
  // 底部导航选中状态
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
})
// 点击关闭优惠券弹框
$(".closeButton").click(function(){
    $(".tellBoxs").hide();
})
})
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
var id=GetRequest().id;
 $.ajax({
    type:"get",
    url:"/index.php/Organization/ajaxHome",
    aynsc:true,
    data:{
        id:id
    },
    dataType:"json",
    success:function(data){
        if(data.status==1){
            var data=data.data;
            $(".header h3").text(data.organization.org_name)
            $(".logo img").attr("src",data.organization.picture)
            $(".learn").attr("onclick","getIntroduce("+data.organization.id+")")
            if(data.organization.is_show!=1){
                alert("亲，该机构暂未开放哦！")
                window.history.back()
            }
            if(data.isFollowed==1){
                $(".seen").text("已关注")
            }else{
                $(".seen").text("关注")
            }
            if(data.offerList != ''){
                $('#getOffer').show();
            }
            var env_star=data.organization.env_star;
            var quality_star=data.organization.quality_star;
            var org_star=data.organization.org_star;
            for(var i=0; i<env_star; i++){
                $(".env_star").append('<img src="../../../../Public/images/star-full.png" alt="">')
            }
            for(var i=0; i<quality_star; i++){
                $(".quality_star").append('<img src="../../../../Public/images/star-full.png" alt="">')
            }
            for(var i=0; i<org_star; i++){
                $(".org_star").append('<img src="../../../../Public/images/star-full.png" alt="">')
            }
            for(var i=0; i<data.banners.length; i++){
                if(data.banners[i].type==1){
                    $(".lunboList").append(
                        '<li class="swiper-slide"><a href="/index.php/Article/getArticle.html?art_id='+data.banners[i].type_id+'"><img src="'+data.banners[i].image+'" alt=""></a></li>'
                    )
                }else if(data.banners[i].type==2){
                    $(".lunboList").append(
                        '<li class="swiper-slide"><a href="/index.php/Product/productDetails.html?pro_id='+data.banners[i].type_id+'"><img src="'+data.banners[i].image+'" alt=""></a></li>'
                    )
                }
                
                 
            }
            var mySwiper = new Swiper('.swiper-container',{
                effect : 'coverflow',
                slidesPerView: 'auto',
                centeredSlides: true,
                loop:true,
                nextButton: '.arrow-left',  
                prevButton: '.arrow-right',  
                autoplayDisableOnInteraction : false,
                observer:true,
                observeParents:true,
                pagination: '.pagination',
                autoplay: 2000,
            //  noSwiping : true,
                loopedSlides :8,
                coverflow: {
                    rotate: 0,
                    stretch: 50,
                    depth: 50,
                    modifier: 3,
                    slideShadows : false,
                    
                }
            })

        }
    }
})
// 点击弹出优惠券弹框进行领取
function getOffer() {
    $("#getOffer").removeAttr("onclick");
    var url = "/index.php/Organization/getOfferList";
    var id = getQueryString('id');
    $.get(url, {id: id}, function (result) {
        if (result.status == 0) {
            return alert(result.message);
        }
        var offerList = result.data.offerList;
        $(".tellBoxs").fadeIn(1000);
        $.each(offerList, function (key, value) {
            var startYear=value.start_time.split(".")[0];
            var startMonth=value.start_time.split(".")[1];
            var startDay=value.start_time.split(".")[2];
            var endYear=value.end_time.split(".")[0];
            var endtMonth=value.end_time.split(".")[1];
            var endDay=value.end_time.split(".")[2];
            $(".coupons ul").html("")
            $(".coupons ul").append(
                 '<li class="coupon" id="coupon-id-'+value.id+'" onclick="getCoupon('+value.id+')">'+
                    '<div class="content">'+
                        '<div class="texts">'+
                            '<span>￥<strong>'+value.subtract.split(".")[0]+'</strong></span>'+
                            '<div>'+
                                '<span>优惠券<label>(满'+value.full.split(".")[0]+'元可用)</label></span>'+
                                '<p>优培圈平台购买商品、课程使用。</p>'+
                            '</div>'+
                        '</div>'+
                        '<div class="hr"></div>'+
                        '<p>有效期'+startYear+'年'+startMonth+'月'+startDay+'日-'+endYear+'年'+endtMonth+'月'+endDay+'日</p>'+
                    '</div>'+
                    '<span class="get red">立即<br/><i style="font-size: 0.6rem; font-style:normal;">领取</i></span>'+
                '</li>'
                   );

            if(value.is_get == 1){
                $("#coupon-id-"+value.id).attr('onclick',"goshop()").find("i").text("使用");
                $("#coupon-id-"+value.id).find(".get").css("color","orange");
            }
        });
        $("#getOffer").attr("onclick","getOffer()");
    }, 'json');
}
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}
// 进入判断优惠券领取状态
function getCoupon(id) {
    var url = "/index.php/Organization/getCoupon";
    $.get(url, {id:id}, function (result) {
        if(result.status == 0){
            layer.closeAll();
            layer.open({
                content: result.message
                ,skin: 'msg'
                ,time: 10 //2秒后自动关闭
            });
        }
        if(result.status == 1){
             $("#coupon-id-"+id).attr('onclick',"goshop()").find("i").text("使用");
             $("#coupon-id-"+value.id).find(".get").css("color","orange");
        }
    }, 'json')
}
// 获取长度
 function count(obj){
    var objType = typeof obj;
    if(objType == "string"){
      return obj.length;
    }else if(objType == "object"){
      var objLen = 0;
      for(var i in obj){
        objLen++;
      }
      return objLen;
    }
    return false;
  }

$.ajax({
    type:"get",
    url:"/index.php/Organization/getHomeInfo",
    aynsc:true,
    data:{
        id:id,
        page:0
    },
    dataType:"json",
    success:function(data){
        if(data.status==1){
                var data=data.data;
                if(data.length==0){
                    $(".alert").show().find("p").html('暂时没有内容哦！')
                    $(".alert").find("p").css("background","")
                }
                $(".content").append('<section class="groups"></section>')
                    // 遍历热文文章部分
                    if(data.article){
                        $(".groups").append('<section class="classify" onclick="getArticle('+data.article.id+')"></section>')
                        $(".groups").find(".classify").append(
                                ' <div class="article">'+
                                '<div class="articleTap">'+
                                '<span>文•'+data.article.class_title+'</span>'+
                                '</div>'+
                                '<div class="left">'+
                                '<h3>'+data.article.prefix+data.article.desc.substring(0,18)+'</h3>'+
                                // '<h3 style="margin-top:0.1rem;">'+data.article.title.substring(0,12)+'</h3>'+
                                // '<p>教你如何跟宝宝拼好积木</p>'+
                                '<div class="type">'+
                                '<div class="comment">'+
                                '<img src="../../../../Public/images/comment.png" alt="">'+
                                '<span> 评论 ('+data.article.count+') | </span>'+
                                '</div>'+
                                '<div class="collect">'+
                                '<img src="../../../../Public/images/collect.png" alt="">'+
                                '<span>收藏 ('+data.article.collect+')  </span>'+
                                '</div>'+
                                // '<div class="send">'+
                                //     '<img src="../../../../Public/images/send.png" alt="">'+
                                //     '<span>转发 (65)</span>'+
                                // '</div>'+
                                '</div>'+
                                '</div>'+
                                '<div class="right">'+
                                '<img src="'+data.article.image+'" alt="">'+
                                '</div>'+
                                '</div>'
                        )
                    }
                    // 遍历课程团购
                    if(count(data.groupCurriculum)!=0){
                        $(".groups").append('<section class="courseList"><ul></ul></section>')
                        for(var j=0; j<count(data.groupCurriculum); j++){
                            $(".groups").find(".courseList ul").append(
                                    '<li onclick="getGroup('+data.groupCurriculum[j].id+')">'+
                                    '<div class="img">'+
                                    '<img src="'+data.groupCurriculum[j].image+'" alt="" class="courseImg">'+
                                    '<div class="tap">'+
                                    '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                    '<img src="'+data.groupCurriculum[j].logo+'" alt="" class="imgLogo">'+
                                    '</div>'+
                                    '<div class="type groupCurriculum'+j+'">'+
                                    '</div>'+
                                    '</div>'+
                                    '<div class="text">'+
                                    '<div class="time">'+
                                    '<img src="../../../../Public/images/indexFilter.png" alt="">'+
                                    '<span class="groupCurriculumTime" value="'+data.groupCurriculum[j].end_time+'"></span>'+
                                    '</div>'+
                                    '<h3>'+data.groupCurriculum[j].title.substring(0,10)+'</h3>'+
                                    '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+data.groupCurriculum[j].price+'</strong></span>'+
                                    '<p class="originalPrice">原价：'+data.groupCurriculum[j].original_price+'元/人</p>'+
                                    '<div class="groupNum groupNum'+j+'">'+
                                    '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupCurriculum[j].groupCount+'</span>/<span class="allPeople">'+data.groupCurriculum[j].max_people+'</span>)</span>'+
                                    '<div class="bar"><span></span></div>'+
                                    '</div>'+
                                    '<a href="javascript:void(0);">立即加入</a>'+
                                    '</div>'+
                                    '</li>'
                            )
                            // 遍历描述标签
                            var groupCurriculum=".groups .groupCurriculum"+j;
                            if(data.groupCurriculum[j].tagA){
                                $(groupCurriculum).append(
                                        '<span>'+data.groupCurriculum[j].tagA+'</span>'
                                )
                            }else{
                                $(groupCurriculum).hide()
                            }
                            if(data.groupCurriculum[j].tagB){
                                $(groupCurriculum).append(
                                        '<span>'+data.groupCurriculum[j].tagB+'</span>'
                                )
                            }
                            if(data.groupCurriculum[j].tagC){
                                $(groupCurriculum).append(
                                        '<span>'+data.groupCurriculum[j].tagC+'</span>'
                                )
                            }
                            // 拼团里的进度条颜色变化
                            var groupNum=".groups .groupNum"+j;
                            var nowPeople=$(groupNum).find(".nowPeople").text();
                            var allPeople=$(groupNum).find(".allPeople").text();
                            var scale=(nowPeople/allPeople*100);
                            $(groupNum).find(".bar span").css("width",scale+"%")
                            if(scale<35){
                                $(groupNum).find(".bar span").css("background","greenyellow")
                            }else if(scale<70){
                                $(groupNum).find(".bar span").css("background","orange")
                            }else if(scale<101){
                                $(groupNum).find(".bar span").css("background","red")
                            }
                        }
                    }
                    // 遍历精选课程
                    if(data.product){
                        $(".groups").append('<section class="course" onclick="getProduct('+data.product.id+')"></section>')
                        $(".groups").find(".course").append(
                                '<div class="courseContent">'+
                                '<div class="coursesList">'+
                                '<div class="text">'+
                                '<h3>'+data.product.title.substring(0,20)+'</h3>'+
                                '<span class="groupPrice">平台价：<span class="moneySign">￥</span><strong>'+data.product.now_price+'</strong></span>'+
                                '<p class="originalPrice">原价：'+data.product.original_price+'元/人</p>'+
                                '<p class="try">'+
                                '<a href="javascript:void(0);" onclick="groupOrder(event,'+data.product.id+')">立即下单</a>'+
                                '<a href="javascript:void(0);" onclick="appoint(event,'+data.product.id+')">预约试听</a>'+
                                '</p>'+
                                '</div>'+
                                '<div class="img">'+
                                '<img src="'+data.product.pic_url+'" alt="" class="courseImg">'+
                                '<div class="tap">'+
                                '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                '<img src="'+data.product.logo+'" alt="" class="imgLogo">'+
                                '</div>'+
                                '<div class="type types">'+
                                ' </div>'+
                                '</div>'+
                                '</div>'+
                                '</div>'
                        )
                        var groupCurriculum=".groups .types";
                        if(data.product.tagA){
                            $(groupCurriculum).append(
                                    '<span>'+data.product.tagA+'</span>'
                            )
                        }else{
                            $(groupCurriculum).hide()
                        }
                        if(data.product.tagB){
                            $(groupCurriculum).append(
                                    '<span>'+data.product.tagB+'</span>'
                            )
                        }
                        if(data.product.tagC){
                            $(groupCurriculum).append(
                                    '<span>'+data.product.tagC+'</span>'
                            )
                        }
                    }
                    // 遍历视频
                    if(data.video){
                        $(".groups").append('<section class="video" onclick="getArticle('+data.video.id+')"></section>')
                        $(".groups").find(".video").append(
                                '<div class="desc">'+
                                '<span>视频</span>'+
                                '</div>'+
                                '<img src="'+data.video.image+'" alt="">'+
                                '<img src="../../../../Public/images/play.png" alt="" class="playVideo">'
                        )
                    }
                    // 遍历商品团购
                    if(count(data.groupProducts)!=0){
                        $(".groups").append('<section class="shopGroups"><ul></ul></section>')
                        var counts=count(data.groupProducts);
                        if(counts%2!=0){
                            counts=counts-1;
                        }
                        for(var j=0; j<counts; j++){
                            $(".groups").find(".shopGroups ul").append(
                                    '<li onclick="getGroup('+data.groupProducts[j].id+')">'+
                                    '<div class="img">'+
                                    '<div class="time">'+
                                    '<span class="shopGroupsTime" value="'+data.groupProducts[j].end_time+'"></span>'+
                                    '</div>'+
                                    '<div class="type shopGroup'+j+'">'+
                                    '<span class="originalPrice">原价:￥'+data.groupProducts[j].original_price.split(".")[0]+'</span>'+
                                    '</div>'+
                                    '<img src="'+data.groupProducts[j].image+'" alt="">'+
                                    '</div>'+
                                    '<div class="text">'+
                                    '<h3>'+data.groupProducts[j].title.substring(0,10)+'</h3>'+
                                    '<div>'+
                                    '<span class="groupPrice">团购价:￥<strong>'+data.groupProducts[j].price.split(".")[0]+'</strong></span>'+
                                    '<a href="javascript:void(0);">加入团购</a>'+
                                    '</div>'+
                                    '<div class="groupNum shopping'+j+'">'+
                                    '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProducts[j].groupCount+'</span>/<span class="allPeople">'+data.groupProducts[j].max_people+'</span>)</span>'+
                                    '<div class="bar"><span></span></div>'+
                                    '</div>'+
                                    '</div>'+
                                    '</li>'
                            )
                            // 遍历描述标签
                            var shopGroups=".groups .shopGroup"+j;
                            if(data.groupProducts[j].tagA){
                                $(shopGroups).append(
                                        '<span class="tab">'+data.groupProducts[j].tagA+'</span>'
                                )
                            }
                            if(data.groupProducts[j].tagB){
                                $(shopGroups).append(
                                        '<span class="tab">'+data.groupProducts[j].tagB+'</span>'
                                )
                            }

                            if(data.groupProducts[j].tagC){
                                $(shopGroups).append(
                                        '<span class="tab">'+data.groupProducts[j].tagC+'</span>'
                                )
                            }
                            // 拼团里的进度条颜色变化
                            var groupNum=".groups .shopping"+j;
                            var nowPeople=$(groupNum).find(".nowPeople").text();
                            var allPeople=$(groupNum).find(".allPeople").text();
                            var scale=(nowPeople/allPeople*100);
                            $(groupNum).find(".bar span").css("width",scale+"%")
                            if(scale<35){
                                $(groupNum).find(".bar span").css("background","greenyellow")
                            }else if(scale<70){
                                $(groupNum).find(".bar span").css("background","orange")
                            }else if(scale<101){
                                $(groupNum).find(".bar span").css("background","red")
                            }
                        }
                    }
                    // 遍历砍价商品
                    if(data.bargain){
                        $(".groups").append('<section class="shopBargain"><ul></ul></section>')
                        var countss=data.bargain.length;
                        if(countss%2!=0){
                            countss=countss-1
                        }
                        for(var j=0; j<countss; j++){
                            $(".groups").find(".shopBargain ul").append(
                                    '<li onclick="getBargainId('+data.bargain[j].id+','+data.bargain[j].key+')">'+
                                     '<div class="tapImg" style="top:0;left:0;">'+
                                    '<img src="../../../../Public/images/bargainEax.gif" alt="" style="width:2.46rem;height:1.28rem;">'+
                                    // '<span>砍价</span>'+
                                    '</div>'+
                                    '<div class="img">'+
                                    '<img src="'+data.bargain[j].pic_url+'" alt="">'+
                                    '</div>'+
                                    '<div class="text">'+
                                    '<h3>'+data.bargain[j].title.substring(0,10)+'</h3>'+
                                    '<p>'+
                                    '<span>平台价：￥<strong>'+data.bargain[j].now_price+'</strong></span>'+
                                    '</p>'+
                                    '<div class="type bargain'+j+'">'+
                                    '</div>'+
                                    '</div>'+
                                    '</li>'
                            )
                            // 遍历描述标签
                            var bargain=".groups .bargain"+j;
                            if(data.bargain[j].tagA){
                                $(bargain).append(
                                        '<span>'+data.bargain[j].tagA+'</span>'
                                )
                            }else{
                                $(bargain).hide()
                            }
                            if(data.bargain[j].tagB){
                                $(bargain).append(
                                        '<span>'+data.bargain[j].tagB+'</span>'
                                )
                            }
                            if(data.bargain[j].tagC){
                                $(bargain).append(
                                        '<span>'+data.bargain[j].tagC+'</span>'
                                )
                            }
                        }
                }
            }
        }
})
var n=0;
var a=0;
var npage=1;
var isMore=false
$(window).scroll(function () {
var scrollTop = $(this).scrollTop();
var scrollHeight = $(document).height();
var windowHeight = $(this).height();
if (scrollTop + windowHeight == scrollHeight) {
    if(!isMore){
        isMore=true;
    $(this).scrollTop(scrollHeight - 50);
    //加载层
    layer.load();
    setTimeout(function () {
        layer.closeAll('loading');
    }, 1000);
    $.ajax({
        type:"get",
        url:"/index.php/organization/getHomeInfo",
        dataType:"json",
        aysnc:true,
        data:{
            id:id,
            page:npage
        },
        success:function(data){
            if(data.status==1){
                isMore=false
                n+=1;
                var data=data.data;
                if(data.length==0){
                     $(".alert").show().find("p").html('没有更多了')
                     $(".alert").find("p").css("margin-top",0)
                }
                $(".content").append('<section class="groups groups'+n+'"></section>')
                // for(var i=0; i<data.length; i++){
                    a+=1;
                    // 遍历热文文章部分
                    if(data.article){
                        $(".groups"+n).append('<section class="classify classify'+a+'" onclick="getArticle('+data.article.id+')"></section>')
                        $(".groups"+n).find(".classify"+a).append(
                                ' <div class="article">'+
                                '<div class="articleTap">'+
                                '<span>文•'+data.article.class_title+'</span>'+
                                '</div>'+
                                '<div class="left">'+
                                '<h3>'+data.article.prefix+data.article.desc.substring(0,18)+'</h3>'+
                                // '<h3 style="margin-top:0.1rem;">'+data.article.title.substring(0,12)+'</h3>'+
                                // '<p>教你如何跟宝宝拼好积木</p>'+
                                '<div class="type">'+
                                '<div class="comment">'+
                                '<img src="../../../../Public/images/comment.png" alt="">'+
                                '<span> 评论 ('+data.article.count+') | </span>'+
                                '</div>'+
                                '<div class="collect">'+
                                '<img src="../../../../Public/images/collect.png" alt="">'+
                                '<span>收藏 ('+data.article.collect+')  </span>'+
                                '</div>'+
                                // '<div class="send">'+
                                //     '<img src="../../../../Public/images/send.png" alt="">'+
                                //     '<span>转发 (65)</span>'+
                                // '</div>'+
                                '</div>'+
                                '</div>'+
                                '<div class="right">'+
                                '<img src="'+data.article.image+'" alt="">'+
                                '</div>'+
                                '</div>'
                        )
                    }
                    // 遍历课程团购
                    if(count(data.groupCurriculum)!=0){
                        $(".groups"+n).append('<section class="courseList courseList'+a+'"><ul></ul></section>')
                        for(var j=0; j<count(data.groupCurriculum); j++){
                            $(".groups"+n).find(".courseList"+a+" ul").append(
                                    '<li onclick="getGroup('+data.groupCurriculum[j].id+')">'+
                                    '<div class="img">'+
                                    '<img src="'+data.groupCurriculum[j].image+'" alt="" class="courseImg">'+
                                    '<div class="tap">'+
                                    '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                    '<img src="'+data.groupCurriculum[j].logo+'" alt="" class="imgLogo">'+
                                    '</div>'+
                                    '<div class="type groupCurriculum'+j+'">'+
                                    '</div>'+
                                    '</div>'+
                                    '<div class="text">'+
                                    '<div class="time">'+
                                    '<img src="../../../../Public/images/indexFilter.png" alt="">'+
                                    '<span class="groupCurriculumTime" value="'+data.groupCurriculum[j].end_time+'"></span>'+
                                    '</div>'+
                                    '<h3>'+data.groupCurriculum[j].title.substring(0,10)+'</h3>'+
                                    '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+data.groupCurriculum[j].price+'</strong></span>'+
                                    '<p class="originalPrice">原价：'+data.groupCurriculum[j].original_price+'元/人</p>'+
                                    '<div class="groupNum groupNum'+j+'">'+
                                    '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupCurriculum[j].groupCount+'</span>/<span class="allPeople">'+data.groupCurriculum[j].max_people+'</span>)</span>'+
                                    '<div class="bar"><span></span></div>'+
                                    '</div>'+
                                    '<a href="javascript:void(0);">立即加入</a>'+
                                    '</div>'+
                                    '</li>'
                            )
                            // 遍历描述标签
                            var groupCurriculum=".groups"+n+" .groupCurriculum"+j;
                            if(data.groupCurriculum[j].tagA){
                                $(groupCurriculum).append(
                                        '<span>'+data.groupCurriculum[j].tagA+'</span>'
                                )
                            }else{
                                groupCurriculum.hide()
                            }
                            if(data.groupCurriculum[j].tagB){
                                $(groupCurriculum).append(
                                        '<span>'+data.groupCurriculum[j].tagB+'</span>'
                                )
                            }
                            if(data.groupCurriculum[j].tagC){
                                $(groupCurriculum).append(
                                        '<span>'+data.groupCurriculum[j].tagC+'</span>'
                                )
                            }
                            // 拼团里的进度条颜色变化
                            var groupNum=".groups"+n+" .groupNum"+j;
                            var nowPeople=$(groupNum).find(".nowPeople").text();
                            var allPeople=$(groupNum).find(".allPeople").text();
                            var scale=(nowPeople/allPeople*100);
                            $(groupNum).find(".bar span").css("width",scale+"%")
                            if(scale<35){
                                $(groupNum).find(".bar span").css("background","greenyellow")
                            }else if(scale<70){
                                $(groupNum).find(".bar span").css("background","orange")
                            }else if(scale<101){
                                $(groupNum).find(".bar span").css("background","red")
                            }
                        }
                    }
                    // 遍历精选课程
                    if(data.product){
                        $(".groups"+n).append('<section class="course course'+a+'" onclick="getProduct('+data.product.id+')"></section>')
                        $(".groups"+n).find(".course"+a).append(
                                '<div class="courseContent">'+
                                '<div class="coursesList">'+
                                '<div class="text">'+
                                '<h3>'+data.product.title.substring(0,20)+'</h3>'+
                                '<span class="groupPrice">平台价：<span class="moneySign">￥</span><strong>'+data.product.now_price+'</strong></span>'+
                                '<p class="originalPrice">原价：'+data.product.original_price+'元/人</p>'+
                                '<p class="try">'+
                                '<a href="javascript:void(0);" onclick="groupOrder(event,'+data.product.id+')">立即下单</a>'+
                                '<a href="javascript:void(0);" onclick="appoint(event,'+data.product.id+')">预约试听</a>'+
                                '</p>'+
                                '</div>'+
                                '<div class="img">'+
                                '<img src="'+data.product.pic_url+'" alt="" class="courseImg">'+
                                '<div class="tap">'+
                                '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                '<img src="'+data.product.logo+'" alt="" class="imgLogo">'+
                                '</div>'+
                                '<div class="type type'+a+'">'+
                                ' </div>'+
                                '</div>'+
                                '</div>'+
                                '</div>'
                        )
                        var groupCurriculum=".groups"+n+" .type"+a;
                        if(data.product.tagA){
                            $(groupCurriculum).append(
                                    '<span>'+data.product.tagA+'</span>'
                            )
                        }else{
                            $(groupCurriculum).hide()
                        }
                        if(data.product.tagB){
                            $(groupCurriculum).append(
                                    '<span>'+data.product.tagB+'</span>'
                            )
                        }
                        if(data.product.tagC){
                            $(groupCurriculum).append(
                                    '<span>'+data.product.tagC+'</span>'
                            )
                        }
                    }
                    // 遍历视频
                    if(data.video){
                        $(".groups"+n).append('<section class="video video'+a+'" onclick="getArticle('+data.video.id+')"></section>')
                        $(".groups"+n).find(".video"+a).append(
                                '<div class="desc">'+
                                '<span>视频</span>'+
                                '</div>'+
                                '<img src="'+data.video.image+'" alt="">'+
                                '<img src="../../../../Public/images/play.png" alt="" class="playVideo">'
                        )
                    }
                    // 遍历商品团购
                    if(count(data.groupProducts)!=0){
                        $(".groups"+n).append('<section class="shopGroups shopGroups'+a+'"><ul></ul></section>')
                        var counts=count(data.groupProducts);
                        if(counts%2!=0){
                            counts=counts-1;
                        }
                        for(var j=0; j<counts; j++){
                            $(".groups"+n).find(".shopGroups"+a+" ul").append(
                                    '<li onclick="getGroup('+data.groupProducts[j].id+')">'+
                                    '<div class="img">'+
                                    '<div class="time">'+
                                    '<span class="shopGroupsTime" value="'+data.groupProducts[j].end_time+'"></span>'+
                                    '</div>'+
                                    '<div class="type shopGroup'+j+'">'+
                                    '<span class="originalPrice">原价:￥'+data.groupProducts[j].original_price.split(".")[0]+'</span>'+
                                    '</div>'+
                                    '<img src="'+data.groupProducts[j].image+'" alt="">'+
                                    '</div>'+
                                    '<div class="text">'+
                                    '<h3>'+data.groupProducts[j].title.substring(0,10)+'</h3>'+
                                    '<div>'+
                                    '<span class="groupPrice">团购价:￥<strong>'+data.groupProducts[j].price.split(".")[0]+'</strong></span>'+
                                    '<a href="javascript:void(0);">加入团购</a>'+
                                    '</div>'+
                                    '<div class="groupNum shopping'+j+'">'+
                                    '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProducts[j].groupCount+'</span>/<span class="allPeople">'+data.groupProducts[j].max_people+'</span>)</span>'+
                                    '<div class="bar"><span></span></div>'+
                                    '</div>'+
                                    '</div>'+
                                    '</li>'
                            )
                            // 遍历描述标签
                            var shopGroups=".groups"+n+" .shopGroup"+j;
                            if(data.groupProducts[j].tagA){
                                $(shopGroups).append(
                                        '<span class="tab">'+data.groupProducts[j].tagA+'</span>'
                                )
                            }
                            if(data.groupProducts[j].tagB){
                                $(shopGroups).append(
                                        '<span class="tab">'+data.groupProducts[j].tagB+'</span>'
                                )
                            }

                            if(data.groupProducts[j].tagC){
                                $(shopGroups).append(
                                        '<span class="tab">'+data.groupProducts[j].tagC+'</span>'
                                )
                            }
                            // 拼团里的进度条颜色变化
                            var groupNum=".groups"+n+" .shopping"+j;
                            var nowPeople=$(groupNum).find(".nowPeople").text();
                            var allPeople=$(groupNum).find(".allPeople").text();
                            var scale=(nowPeople/allPeople*100);
                            $(groupNum).find(".bar span").css("width",scale+"%")
                            if(scale<35){
                                $(groupNum).find(".bar span").css("background","greenyellow")
                            }else if(scale<70){
                                $(groupNum).find(".bar span").css("background","orange")
                            }else if(scale<101){
                                $(groupNum).find(".bar span").css("background","red")
                            }
                        }
                    }
                    // 遍历砍价商品
                    if(data.bargain){
                        $(".groups"+n).append('<section class="shopBargain shopBargain'+a+'"><ul></ul></section>')
                        var countss=data.bargain.length;
                        if(countss%2!=0){
                            countss=countss-1
                        }
                        for(var j=0; j<countss; j++){
                            $(".groups"+n).find(".shopBargain"+a+" ul").append(
                                    '<li onclick="getBargainId('+data.bargain[j].id+','+data.bargain[j].key+')">'+
                                     '<div class="tapImg" style="top:0;left:0;">'+
                                    '<img src="../../../../Public/images/bargainEax.gif" alt="" style="width:2.46rem;height:1.28rem;">'+
                                    // '<span>砍价</span>'+
                                    '</div>'+
                                    '<div class="img">'+
                                    '<img src="'+data.bargain[j].pic_url+'" alt="">'+
                                    '</div>'+
                                    '<div class="text">'+
                                    '<h3>'+data.bargain[j].title.substring(0,10)+'</h3>'+
                                    '<p>'+
                                    '<span>平台价：￥<strong>'+data.bargain[j].now_price+'</strong></span>'+
                                    '</p>'+
                                    '<div class="type bargain'+j+'">'+
                                    '</div>'+
                                    '</div>'+
                                    '</li>'
                            )
                            // 遍历描述标签
                            var bargain=".groups"+n+" .bargain"+j;
                            if(data.bargain[j].tagA){
                                $(bargain).append(
                                        '<span>'+data.bargain[j].tagA+'</span>'
                                )
                            }else{
                                $(bargain).hide()
                            }
                            if(data.bargain[j].tagB){
                                $(bargain).append(
                                        '<span>'+data.bargain[j].tagB+'</span>'
                                )
                            }
                            if(data.bargain[j].tagC){
                                $(bargain).append(
                                        '<span>'+data.bargain[j].tagC+'</span>'
                                )
                            }
                        }
                    }
                
            }
        }

    })
 npage+=1;
    }
}

})
// 进入文章详情页
function getArticle(id) {
window.location.href = "/index.php/Article/getArticle?art_id=" + id;
}
// 进入团购详情
function getGroup(id) {
window.location.href = "/index.php/Groups/getGroup?id=" + id;
}
//查看限时抢购商品
function getProduct(id) {
window.location.href = "/index.php/Product/productDetails?pro_id=" + id;
}
// 进入商品详情
function groupOrder(event,id){
event.stopPropagation();
window.location.href = "/index.php/Product/productDetails?pro_id=" + id;
}
// 进入预约表
function appoint(event,id){
event.stopPropagation();
window.location.href = "/index.php/Product/getClassTable?pro_id=" + id;
}
//砍价
function getBargainId(id,key) {
    window.location.href = "/index.php/Product/bargain?pro_id=" + id + "&key=" + key;
// }
}

//限时抢购倒计时
var time_current = (new Date()).valueOf();//获取当前时间
$(function () {
var dateTime = new Date();
var difference = dateTime.getTime() - time_current;
// 课程团购倒计时
setInterval(function () {
$(".groupCurriculumTime").each(function () {
    var obj = $(this);
    var endTime = new Date(parseInt(obj.attr('value')) * 1000);
    var nowTime = new Date();
    var nMS = endTime.getTime() - nowTime.getTime() + difference;
    var myD = Math.floor(nMS / (1000 * 60 * 60 * 24));
    var myH = Math.floor(nMS / (1000 * 60 * 60)) % 24;
    var myM = Math.floor(nMS / (1000 * 60)) % 60;
    var myS = Math.floor(nMS / 1000) % 60;
    var myMS = Math.floor(nMS / 100) % 10;
    if (myD >= 0) {
        var str = "剩余下" + myD + "天  " + myH + ":" + myM + ":" + myS;
    } else {
        var str = "已结束！";
    }
    obj.html(str);
});
}, 100);
// 商品团购倒计时
setInterval(function () {
$(".shopGroupsTime").each(function () {
    var obj = $(this);
    var endTime = new Date(parseInt(obj.attr('value')) * 1000);
    var nowTime = new Date();
    var nMS = endTime.getTime() - nowTime.getTime() + difference;
    var myD = Math.floor(nMS / (1000 * 60 * 60 * 24));
    var myH = Math.floor(nMS / (1000 * 60 * 60)) % 24;
    var myM = Math.floor(nMS / (1000 * 60)) % 60;
    var myS = Math.floor(nMS / 1000) % 60;
    var myMS = Math.floor(nMS / 100) % 10;
    if (myD >= 0) {
        var str = "剩余下" + myD + "天  " + myH + ":" + myM + ":" + myS;
    } else {
        var str = "已结束！";
    }
    obj.html(str);
});
}, 100);
})

// 点击优惠券去购物
function goshop(){
    window.location.href="/index.php/Organization/demo.html?id="+id;
}
// 关注状态
function followed() {
    var url = "{:U('Organization/followed')}";
    $.post(url, {id: id}, function (res) {
        if (res.status == 1) {
            alert('成功关注');
            $('.seen').text('已关注');
        } else if (res.status == 2) {
            alert('成功取消关注');
            $('.seen').text('关注');
        }
    }, 'json')
}
// 了解TA按钮
function getIntroduce() {
    window.location.href = "/index.php/Organization/getOrganizationIntroduce.html?id=" + id;
}
// 底部导航图解按钮
function fullshot(){
    window.location.href ="/index.php/Organization/fullShot.html?id="+id;
}
 // 底部导航群组按钮
function association(){
    window.location.href ="/index.php/Organization/association.html?id="+id;
}
// 底部导航最新活动按钮
function activity(){
    window.location.href ="/index.php/Organization/activityList.html?id="+id;
}
// 底部导航TA首页按钮
function orgIndex(){
    window.location.href= "/index.php/Organization/home.html?id="+id;
}