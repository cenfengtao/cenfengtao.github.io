   // 底部导航选中状态
        $(".footer ul li>img").css("display","none");
        $(".footer ul li>img:eq(1)").css("display","block");
        $(" .footer ul li").click(function(){
            $(this).addClass('on').siblings().removeClass("on");
            var index=$(this).index();
            var liLen=$(".footer ul li").length;
            for(var i=0;i<=liLen-1;i++){
                $(".footer ul li>img").eq(i).css("display","none");
            }
            $(this).find("img:eq(0)").css("display","block");
        })
         $(window).load(function (){
            setTimeout(function () {
                $('.footer').show();
            },2000)
        });
        $(function(){
        	// 菜单导航随滚动黏住头部
           var bannerHeight = $(".banner").height();
           var headerHeight = $(".header").height();
           var searchHeight = $(".search").height();
           var navHeight=$(".nav").offset().top;
           $(window).scroll(function(){
               var scrollHeight=$(window).scrollTop()
               if(scrollHeight>(navHeight-headerHeight)||scrollHeight==(navHeight-headerHeight)){
                   $(".nav").addClass("fixed");
                   $(".nav").css("top",headerHeight);
               } else {
                   $(".nav").removeClass("fixed");
                   $(".nav").css("top","");
               }
           });
	        // 拼团里的进度条颜色变化
	        var nowPeople=$(".nowPeople").text();
	        var allPeople=$(".allPeople").text();
	        var scale=(nowPeople/allPeople*100);
	        $(".bar span").css("width",scale+"%")
	        if(scale<35){
	            $(".bar span").css("background","greenyellow")
	        }else if(scale<70){
	            $(".bar span").css("background","orange")
	        }else if(scale<101){
	            $(".bar span").css("background","red")
	        }
         $.ajax({
            type: "get",
            url: "/index.php/Discovery/ajaxIndex",
            aysnc: true,
            dataType: "json",
            success: function (data) {
                if(data.status==1){
                    // 遍历头部轮播图
                    var pictureData=data.data.bannerPosters;
                    for(var j=0; j<pictureData.length; j++){
                        $(".imgLi").append(
                                '<li class="swiper-slide"><a href="'+pictureData[j].url+'"><img src="'+pictureData[j].image+'" alt=""></a></li>'
                            )
                    }
                     // 首页头部轮播图功能
                    var mySwiper = new Swiper('.swiper-container1',{
                        pagination: '.pagination1',
                        loop:true,
                        grabCursor: true,
                        paginationClickable: true,
                        centeredSlides: true,
                        autoplay: 2000,
                        autoplayDisableOnInteraction: false
                    });
                    $(".navList").append(
                        '<li><a href="/index.php/Organization/index"><img src="/Public/images/organization.png" alt=""><span>机构</span></a></li>'+
                        '<li><a href="/index.php/Map/index"><img src="/Public/images/Finder_5.png" alt=""><span>地图</span></a></li>'+
                        '<li><a href="/index.php/Article/videoList"><img src="/Public/images/discoveryIcon2.png" alt=""><span>搞笑一刻</span></a></li>'+
                        '<li><a href="/index.php/Product/courseList"><img src="/Public/images/schedule1.png" alt=""><span>课程表</span></a></li>'+
                        '<li><a href="/index.php/Article/professionVideoList"><img src="/Public/images/discoveryIcon1.png" alt=""><span>专家课程</span></a></li>'+
                        '<li><a href="/index.php/Article/index"><img src="/Public/images/article.png" alt=""><span>文章</span></a></li>'+
                        '<li><a href="/index.php/Boutique/index"><img src="/Public/images/Finder_3.png" alt=""><span>购物</span></a></li>'+
                        '<li><a href="/index.php/Article/index?cate_id=9"><img src="/Public/images/Finder_2.png" alt=""><span>福利</span></a></li>'+
                        '<li><a href="/index.php/Parenting/index"><img src="/Public/images/children.png" alt=""><span>亲子</span></a></li>'+
                        '<li><a href="/index.php/Product/courseList"><img src="/Public/images/course.png" alt=""><span>课程</span></a></li>'+  
                        '<li><a href="/index.php/Lottery/index.html?token=g232238gc959"><img src="/Public/images/choujiang.png" alt=""><span>抽奖</span></a></li>' 
  
                    );
                      var num=-1;
                      var liLength=$(".navList li").length;
                      var liWidth=$(".navList li").width();
                      lunbo=function( ){
                        num+=1;
                        $(".point span").eq(num).addClass("on").siblings().removeClass("on")
                        if(num==0){
                          $(".navList").animate({"scrollLeft":"0"},2000)
                          // $(".navList").scrollLeft(0)
                        }
                        if(num==1){
                          $(".navList").animate({"scrollLeft":liWidth*liLength+"px"},3000)
                          // $(".navList").scrollLeft(200)
                        }
                        if(num>1){
                          num=-1;
                        }
                      }
                      var t=setInterval(lunbo,8000)
                       // 导航滑动改变当前状态
                      var startLeft;
                      var moveLeft;
                      var endLeft;
                      var ulList=document.querySelector(".nav ul");
                      ulList.addEventListener('touchstart',function(){
                          startLeft=$(this).scrollLeft();
                      })
                      ulList.addEventListener('touchmove',function(){
                          window.clearInterval(t)
                          moveLeft=$(this).scrollLeft();
                          if(moveLeft-startLeft>0){
                             $(".point span").eq(1).addClass("on").siblings().removeClass("on")
                             num=1
                          }else if(moveLeft-startLeft<0){
                              $(".point span").eq(0).addClass("on").siblings().removeClass("on")
                              num=0
                          }
                          t=setInterval(lunbo,8000)
                      })
                    //渲染最新内容 type:1-文章 2-视频 3-普通课程 4-团购课程 5-砍价 6-团购商品
                    var newest = data.data.newest;
                    $.each(newest, function (key,value) {
                        if (value.type == 1) {
                            $("#newest").append('<section class="article"  onclick="getArticle('+value.article.id+')"><div class="articleTap"><span>文•'+value.class_title+'</span></div>'+
                            '<div class="left"><h3>'+value.firstTitle+value.secondTitle+'</h3>'+
                            '<div class="type"><div class="comment"><img src="/Public/images/comment.png"><span> 评论 ('+value.comment_count+') | </span> </div>'+
                            '<div class="collect">'+
                                '<img src="/Public/images/collect.png"><span>收藏 ('+value.collect_count+')</span></div></div></div>'+
                            '<div class="right">'+
                            '<img src="'+value.article.image+'"></div></section>');
                        } else if(value.type == 2) {
                            $("#newest").append('<section class="video" onclick="getArticle('+value.video.id+')"><div class="desc"><span>搞笑一刻</span></div> <img src="'+value.video.image+'"></section>');
                        } else if(value.type == 3) {
                            $("#newest").append('<section class="course">'+
                                    '<div class="courseContent"><div class="swiper-container2">'+
                                    '<div class="swiper-wrapper coursesList"><div class="swiper-slide">'+
                                    '<div class="text"><h3>'+value.class.title+'</h3>'+
                                        '<span class="groupPrice">平台价：<span class="moneySign">￥</span><strong>'+value.now_price+'</strong></span>'+
                                        '<p class="originalPrice">原价：'+value.original_price+'元/'+value.class.unit+'</p><p class="try">'+
                                        '<a href="/index.php/Product/productDetails?pro_id='+value.class.id+'">立即下单</a>' +
                                        '<a href="/index.php/Product/getClassTable?pro_id='+value.class.id+'">预约试听</a></p></div>'+
                                    '<div class="img">'+
                                    '<a href="/index.php/Product/productDetails?pro_id='+value.class.id+'"><img src="'+value.class.pic_url+'" class="courseImg"></a>'+
                                    '<div class="tap"><img src="../../../../Public/images/imgTap.png" class="logoTap">'+
                                    '<img src="'+value.logo+'" class="imgLogo"></div>'+
                                    '<div class="type"><span>'+value.tagA+'</span><span>'+value.tagB+'</span><span>'+value.tagC+'</span></div></div></div>'+
                                    '<div class="pagination2" style="width: 100%;text-align: center;"></div></div></div></div></section>');
                        } else if (value.type == 4) {
                            $("#newest").append('<section class="courseList"><ul><li><div class="img">'+
                                    '<img src="'+value.group.image+'" class="courseImg">'+
                                    '<div class="tap"><img src="../../../../Public/images/imgTap.png" class="logoTap">'+
                                    '<img src="'+value.logo+'" class="imgLogo"></div>'+
                                    '<div class="type"><span>'+value.tagA+'</span><span>'+value.tagB+'</span></div></div>'+
                                    '<div class="text"><div class="time"><img src="../../../../Public/images/indexFilter.png">'+
                                    '<span class="endtime" value="'+value.group.end_time+'"></span></div><h3>'+value.title+'</h3>'+
                                    '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+value.group.price+'</strong></span>'+
                                    '<p class="originalPrice">原价：'+value.group.original_price+'元/'+value.group.unit+'</p>'+
                                    '<div class="groupNum groupNum'+value.group.id+'"><span class="finished">已拼团人数 : (<span class="nowPeople">'+value.userCount+'</span>/<span class="allPeople">'+value.group.max_people+'</span>)</span>'+
                                    '<div class="bar"><span></span></div></div><a href="/index.php/Groups/getGroup?id='+value.group.id+'">立即加入</a></div></li></ul></section>');
                                // 拼团里的进度条颜色变化
                                var groupNum=".groupNum"+value.group.id;
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
                        } else if (value.type == 5) {
                            var bargains = value.info;
                            $.each(bargains,function (k,v) {
                                //判断左右
                                if(k%2!=0){
                                  k=k-1
                                }
                                if (k == 0) {
                                    $("#newest").append('<section class="shopBargain"><ul><li class="bargain'+v.pro_id+'" onclick="getBargain('+v.pro_id+','+v.key+')">'+
                                    '<div class="tapImg" style="left:0;top:0;"><img src="../../../../Public/images/bargainEax.gif" alt="" style="width:2.46rem;height:1.28rem;">'+
                                        '</div><div class="img">'+
                                            '<img src="'+v.image+'"></div>'+
                                            '<div class="text"><h3>'+v.title+'</h3><p><span>平台价：￥<strong>'+v.price+'</strong></span></p>'+
                                            '<div class="type"><span>'+v.tagA+'</span><span>'+v.tagB+'</span></div></div></li></ul></section>');
                                } else {
                                    var lastK = k - 1;
                                    var lastId = bargains[lastK].pro_id;
                                    $(".bargain"+lastId).after('<li onclick="getBargain('+v.pro_id+','+v.key+')"><div class="tapImg" style="left:0;top:0;">'+
                                            '<img src="../../../../Public/images/bargainEax.gif" alt="" style="width:2.46rem;height:1.28rem;"></div>'+
                                            '<div class="img"><img src="'+v.image+'"></div>'+
                                            '<div class="text"><h3>'+v.title+'</h3><p><span>平台价：￥<strong>'+v.price+'</strong></span></p>'+
                                            '<div class="type"><span>'+v.tagA+'</span><span>'+v.tagB+'</span></div></div></li>');
                                }
                            })
                        } else if (value.type == 6) {
                            var groups = value.info;
                           $.each(groups, function (k,v) {
                                //判断左右
                               if (k == 0) {
                                   $("#newest").append("<section class='shopGroups'>"+
                                           "<ul> <li class='group"+v.id+"'>"+
                                           "<div class='img'>"+
                                           "<div class='time'>"+
                                           "<span class='endtime' value='"+v.end_time+"'></span>"+
                                           "</div>"+
                                           "<div class='type'><span class='originalPrice'>原价:￥"+v.original_price+"</span><span class='tab'>"+v.tagA+"</span> </div>"+
                                           "<a href='/index.php/Groups/getGroup?id="+v.id+"'><img src='"+v.image+"'></a></div>"+
                                           "<div class='text'> <h3>"+v.title+"</h3> <div>"+
                                           "<span class='groupPrice'>团购价:￥<strong>"+v.price+"</strong></span>"+
                                           "<a href='/index.php/Groups/getGroup?id="+v.id+"'>加入团购</a></div>"+
                                           "<div class='groupNum groupNum"+v.id+"'>"+
                                           "<span class='finished'>已拼团人数 : (<span class='nowPeople'>"+v.userCount+"</span>/<span class='allPeople'>"+v.max_people+"</span>)</span>"+
                                           "<div class='bar'><span></span></div></div></div></li></ul></section>");
                               } else {
                                    var lastK = k - 1;
                                    var lastId = groups[lastK].id;
                                    $(".group"+lastId).after("<li> <div class='img'><div class='time'><span class='endtime' value='"+v.end_time+"'></span></div>"+
                               "<div class='type'><span class='originalPrice'>原价:￥"+v.original_price+"</span><span class='tab'>"+v.tagA+"</span></div>"+
                                       "<a href='/index.php/Groups/getGroup?id="+v.id+"'><img src='"+v.image+"'></a></div>"+
                                       "<div class='text'><h3>"+v.title+"</h3><div>"+
                                       "<span class='groupPrice'>团购价:￥<strong>"+v.price+"</strong></span>"+
                                        "<a href='/index.php/Groups/getGroup?id="+v.id+"'>加入团购</a></div>"+
                                       "<div class='groupNum groupNum"+v.id+"'>"+
                                       "<span class='finished'>已拼团人数 : (<span class='nowPeople'>"+v.userCount+"</span>/<span class='allPeople'>"+v.max_people+"</span>)</span>"+
                               "<div class='bar'><span></span></div></div></div></li>");
                               }
                               // 拼团里的进度条颜色变化
                               var groupNum=".groupNum"+v.id;
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
                           })
                       }
                    });
                    var bargainLength=$(".shopBargain").length;
                    for(var i=0; i<bargainLength; i++){
                       var bargainLengths=$(".shopBargain").eq(i).find("li").length;
                       if(bargainLengths%2!=0){
                        $(".shopBargain").eq(i).find("li").eq(bargainLengths-1).remove()
                      }
                    }
                    var groupLength=$(".shopBargain").length;
                    for(var i=0; i<groupLength; i++){
                       var groupLengths=$(".shopBargain").eq(i).find("li").length;
                       if(groupLengths%2!=0){
                        $(".shopBargain").eq(i).find("li").eq(groupLengths-1).remove()
                      }
                    }
                }
            }
        });


            var page=3;
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
                            url:"/index.php/Discovery/loadingIndex",
                            dataType:"json",
                            aysnc:true,
                            data:{
                                page:page
                            },
                            success:function(data){
                                if(data.status==1){
                                    isMore=false


                                    //渲染最新内容 type:1-文章 2-视频 3-普通课程 4-团购课程 5-砍价 6-团购商品
                                    var newest = data.data.newest;
                                    $.each(newest, function (key,value) {
                                        if (value.type == 1) {
                                            $("#newest").append('<section class="article"  onclick="getArticle('+value.article.id+')"><div class="articleTap"><span>文•'+value.class_title+'</span></div>'+
                                                    '<div class="left"><h3>'+value.firstTitle+value.secondTitle+'</h3>'+
                                                    '<div class="type"><div class="comment"><img src="/Public/images/comment.png"><span> 评论 ('+value.comment_count+') | </span> </div>'+
                                                    '<div class="collect">'+
                                                    '<img src="/Public/images/collect.png"><span>收藏 ('+value.collect_count+')</span></div></div></div>'+
                                                    '<div class="right">'+
                                                    '<img src="'+value.article.image+'"></div></section>');
                                        } else if(value.type == 2) {
                                            $("#newest").append('<section class="video" onclick="getArticle('+value.video.id+')"><div class="desc"><span>搞笑一刻</span></div> <img src="'+value.video.image+'"></section>');
                                        } else if(value.type == 3) {
                                            $("#newest").append('<section class="course">'+
                                                    '<div class="courseContent"><div class="swiper-container2">'+
                                                    '<div class="swiper-wrapper coursesList"><div class="swiper-slide">'+
                                                    '<div class="text"><h3>'+value.class.title+'</h3>'+
                                                    '<span class="groupPrice">平台价：<span class="moneySign">￥</span><strong>'+value.now_price+'</strong></span>'+
                                                    '<p class="originalPrice">原价：'+value.original_price+'元/'+value.class.unit+'</p><p class="try">'+
                                                    '<a href="/index.php/Product/productDetails?pro_id='+value.class.id+'">立即下单</a>' +
                                                    '<a href="/index.php/Product/getClassTable?pro_id='+value.class.id+'">预约试听</a></p></div>'+
                                                    '<div class="img">'+
                                                    '<a href="/index.php/Product/productDetails?pro_id='+value.class.id+'"><img src="'+value.class.pic_url+'" class="courseImg"></a>'+
                                                    '<div class="tap"><img src="../../../../Public/images/imgTap.png" class="logoTap">'+
                                                    '<img src="'+value.logo+'" class="imgLogo"></div>'+
                                                    '<div class="type"><span>'+value.tagA+'</span><span>'+value.tagB+'</span><span>'+value.tagC+'</span></div></div></div>'+
                                                    '<div class="pagination2" style="width: 100%;text-align: center;"></div></div></div></div></section>');
                                        } else if (value.type == 4) {
                                            $("#newest").append('<section class="courseList"><ul><li><div class="img">'+
                                                    '<img src="'+value.group.image+'" class="courseImg">'+
                                                    '<div class="tap"><img src="../../../../Public/images/imgTap.png" class="logoTap">'+
                                                    '<img src="'+value.logo+'" class="imgLogo"></div>'+
                                                    '<div class="type"><span>'+value.tagA+'</span><span>'+value.tagB+'</span></div></div>'+
                                                    '<div class="text"><div class="time"><img src="../../../../Public/images/indexFilter.png">'+
                                                    '<span class="endtime" value="'+value.group.end_time+'"></span></div><h3>'+value.title+'</h3>'+
                                                    '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+value.group.price+'</strong></span>'+
                                                    '<p class="originalPrice">原价：'+value.group.original_price+'元/'+value.group.unit+'</p>'+
                                                    '<div class="groupNum groupNum'+value.group.id+'"><span class="finished">已拼团人数 : (<span class="nowPeople">'+value.userCount+'</span>/<span class="allPeople">'+value.group.max_people+'</span>)</span>'+
                                                    '<div class="bar"><span></span></div></div><a href="/index.php/Groups/getGroup?id='+value.group.id+'">立即加入</a></div></li></ul></section>');
                                            // 拼团里的进度条颜色变化
                                            var groupNum=".groupNum"+value.group.id;
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
                                        } else if (value.type == 5) {
                                            var bargains = value.info;
                                            $.each(bargains,function (k,v) {
                                                if (k == 0) {
                                                    $("#newest").append('<section class="shopBargain">' +
                                                            '<ul><li class="bargain'+v.id+'" onclick="getBargain('+v.pro_id+','+v.key+')">'+
                                                            '<div class="tapImg" style="top:0;left:0;"><img src="../../../../Public/images/bargainEax.gif" alt="" style="width:2.46rem;height:1.28rem;">'+
                                                            '</div><div class="img">'+
                                                            '<img src="'+v.image+'"></div>'+
                                                            '<div class="text"><h3>'+v.title+'</h3><p><span>平台价：￥<strong>'+v.price+'</strong></span></p>'+
                                                            '<div class="type"><span>'+v.tagA+'</span><span>'+v.tagB+'</span></div></div></li></ul></section>');
                                                } else {
                                                    var lastK = k - 1;
                                                    var lastId = bargains[lastK].id;
                                                    $(".bargain"+lastId).after('<li onclick="getBargain('+v.pro_id+','+v.key+')"><div class="tapImg" style="left:0;top:0;">'+
                                                            '<img src="../../../../Public/images/bargainEax.gif" alt="" style="width:2.46rem;height:1.28rem;"></div>'+
                                                            '<div class="img"><img src="'+v.image+'"></div>'+
                                                            '<div class="text"><h3>'+v.title+'</h3><p><span>平台价：￥<strong>'+v.price+'</strong></span></p>'+
                                                            '<div class="type"><span>'+v.tagA+'</span><span>'+v.tagB+'</span></div></div></li>');
                                                }
                                            })
                                        } else if (value.type == 6) {
                                            var groups = value.info;
                                            $.each(groups, function (k,v) {
                                                //判断左右
                                                if (k == 0) {
                                                    $("#newest").append("<section class='shopGroups'>"+
                                                            "<ul> <li class='group"+v.id+"'>"+
                                                            "<div class='img'>"+
                                                            "<div class='time'>"+
                                                            "<span class='endtime' value='"+v.end_time+"'></span>"+
                                                            "</div>"+
                                                            "<div class='type'><span class='originalPrice'>原价:￥"+v.original_price+"</span><span class='tab'>"+v.tagA+"</span> </div>"+
                                                            "<a href='/index.php/Groups/getGroup?id="+v.id+"'><img src='"+v.image+"'></a></div>"+
                                                            "<div class='text'> <h3>"+v.title+"</h3> <div>"+
                                                            "<span class='groupPrice'>团购价:￥<strong>"+v.price+"</strong></span>"+
                                                            "<a href='/index.php/Groups/getGroup?id="+v.id+"'>加入团购</a></div>"+
                                                            "<div class='groupNum groupNum"+v.id+"'>"+
                                                            "<span class='finished'>已拼团人数 : (<span class='nowPeople'>"+v.userCount+"</span>/<span class='allPeople'>"+v.max_people+"</span>)</span>"+
                                                            "<div class='bar'><span></span></div></div></div></li></ul></section>");
                                                } else {
                                                    var lastK = k - 1;
                                                    var lastId = groups[lastK].id;
                                                    $(".group"+lastId).after("<li> <div class='img'><div class='time'><span class='endtime' value='"+v.end_time+"'></span></div>"+
                                                            "<div class='type'><span class='originalPrice'>原价:￥"+v.original_price+"</span><span class='tab'>"+v.tagA+"</span></div>"+
                                                            "<a href='/index.php/Groups/getGroup?id="+v.id+"'><img src='"+v.image+"'></a></div>"+
                                                            "<div class='text'><h3>"+v.title+"</h3><div>"+
                                                            "<span class='groupPrice'>团购价:￥<strong>"+v.price+"</strong></span>"+
                                                            "<a href='/index.php/Groups/getGroup?id="+v.id+"'>加入团购</a></div>"+
                                                            "<div class='groupNum groupNum"+v.id+"'>"+
                                                            "<span class='finished'>已拼团人数 : (<span class='nowPeople'>"+v.userCount+"</span>/<span class='allPeople'>"+v.max_people+"</span>)</span>"+
                                                            "<div class='bar'><span></span></div></div></div></li>");
                                                }
                                                // 拼团里的进度条颜色变化
                                                var groupNum=".groupNum"+v.id;
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
                                            })
                                        }
                                    });

                                    var bargainLength=$(".shopBargain").length;
                                    for(var i=0; i<bargainLength; i++){
                                       var bargainLengths=$(".shopBargain").eq(i).find("li").length;
                                       if(bargainLengths%2!=0){
                                        $(".shopBargain").eq(i).find("li").eq(bargainLengths-1).remove()
                                      }
                                    }
                                    var groupLength=$(".shopBargain").length;
                                    for(var i=0; i<groupLength; i++){
                                       var groupLengths=$(".shopBargain").eq(i).find("li").length;
                                       if(groupLengths%2!=0){
                                        $(".shopBargain").eq(i).find("li").eq(groupLengths-1).remove()
                                      }
                                    }
                                }else{
                                    $(".alert").show().find("p").html(data.message)
                                }
                            }

                        })
                        page+=6;
                    }
                }

            })


        //限时抢购倒计时;
        var time_current = (new Date()).valueOf();//获取当前时间
        var dateTime = new Date();
        var difference = dateTime.getTime() - time_current;
        setInterval(function () {
            $(".endtime").each(function () {
                var obj = $(this);
                var endTime = new Date(parseInt(obj.attr('value')) * 1000);
                var nowTime = new Date();
                var nMS = endTime.getTime() - nowTime.getTime() + difference;
                var myD = Math.floor(nMS / (1000 * 60 * 60 * 24));
                var myH = Math.floor(nMS / (1000 * 60 * 60)) % 24;
                var myM = Math.floor(nMS / (1000 * 60)) % 60;
                if(myM < 10) {
                    myM = '0' + myM;
                }
                var myS = Math.floor(nMS / 1000) % 60;
                if(myS < 10) {
                    myS = '0' + myS;
                }
                var myMS = Math.floor(nMS / 100) % 10;
                if (myD >= 0) {
                    var str = "剩余下" + myD + "天  " + myH + ":" + myM + ":" + myS;
                } else {
                    var str = "已结束！";
                }
                if(myH==-1){
                    var str = "已结束！";
                }
                obj.html(str);
            });
        }, 100);
});
 //查询
 function searchWord() {
     var word = $("#searchWord").val();
     var type = $("select[name='searchType'] option:selected").val();
     if (!word) {
         return alert('请输入关键词');
     }
     window.location.href = "/index.php/Index/searchResult?type="+type+"&word="+word;
 }

 function getArticle(id) {
     window.location.href = '/index.php/Article/getArticle?art_id='+id;
 }

 function getBargain(id,key) {
     window.location.href = '/index.php/Product/bargain?pro_id='+id+'&key='+key;
 }
// 调用地图时触发，隐藏头部
    function hideHead(i) {
        var head = i.contentWindow.document.getElementById('headTitle');
        head.style.display = 'none';
        var top = i.contentWindow.document.getElementById('qiandao');
        top.style.margin = '-10rem auto 0 auto';
        var allmap = i.contentWindow.document.getElementById('allmap');
        allmap.style.height = '38rem';
        // var message = i.contentWindow.document.getElementById('message');
        // message.style.size = '6vh';
    }
// 页面加载时加载图标的位置
    function resizenow() {
        var browserwidth = jQuery(window).width();
        var browserheight = jQuery(window).height();
        jQuery('.bonfire-pageloader-icon').css('right', ((browserwidth - jQuery(".bonfire-pageloader-icon").width()) / 2)).css('top', ((browserheight - jQuery(".bonfire-pageloader-icon").height()) / 2));
    }
    resizenow();
    // 跳转到对应的商品或课程页面
    function getProduct(id) {
        window.location.href = "/index.php/Product/productDetails?pro_id=" + id;
    }