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



     $(window).load(function (){
            setTimeout(function () {
                $('.footer').show();
            },2000)
        });
        $(function(){
             // 判断用户进来时是否关注公众号
            var attentionStatus=$("#attentionStatus").val();
            var hintStatus=$("#hintStatus").val();
            if(attentionStatus==2){
                $(".tellBox").show();
            }
            if(hintStatus==2){
                $(".explainBox").show();
            }
            $(".closeBtn").click(function(){
                $(".tellBox").hide();
                $(".explainBox").hide();
            })
            // 拼团商品导航切换
            $(".classifyNav li").click(function(){
                $(this).addClass("on").siblings().removeClass("on")
            })

        })
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
   
    // $(function(){
           $.ajax({
            type: "get",
            url: "/index.php/Index/ajaxIndex",
            aysnc: true,
            dataType: "json",
            success: function (data) {
                if(data.status==1){
                    if(data.data.message!=0){
                        $(".message").show().text(data.data.message)
                    }
                    // 遍历头部轮播图
                    var pictureData=data.data.picture;
                    for(var j=0; j<pictureData.length; j++){
                        $(".imgLi").append(
                                '<li class="swiper-slide"><a href="'+pictureData[j].url+'"><img src="/'+pictureData[j].image+'" alt=""></a></li>'
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
                    // 遍历循环导航菜单栏数据
                    var homeData = data.data.homeClass;
                    for(var i=0; i<homeData.length; i++){
                        $(".navList").append(
                            '<li><a href="'+homeData[i].url+'"><img src="/'+homeData[i].image+'" alt=""><span>'+homeData[i].title+'</span></a></li>'
                        )
                    }
                    // 遍历限时抢购商品数据
                    var limitData=data.data.limitBuy;
                    for(var s=0; s<limitData.length; s++){
                        $(".limitList").append(
                            '<div class="swiper-slide" onclick="getProduct('+limitData[s].id+')">'+
                                '<div class="limitHead">'+
                                    '<div class="text">'+
                                        '<img src="../../../../Public/images/indexTime.png" alt="">'+
                                        '<span>限时抢购</span>'+
                                    '</div>'+
                                    '<div class="time">'+
                                        '<img src="../../../../Public/images/indexFilter.png" alt="">'+
                                        '<span class="endtime" value="'+limitData[s].end_time+'"></span>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="limitContent">'+
                                    '<div class="img">'+
                                        '<img src="'+limitData[s].thumb_image+'" alt="">'+
                                        '<div class="tap">'+
                                            '<span>抢购</span>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="text">'+
                                        '<h3>'+limitData[s].title+'</h3>'+
                                        '<div class="type'+s+' type">'+
                                        '</div>'+
                                        '<p class="add">'+
                                            '<img src="../../../../Public/images/indexAddr.png" alt="">'+
                                            '<span>'+limitData[s].city+limitData[s].area+limitData[s].address.substring(0,10)+'</span>'+
                                        '</p>'+
                                        '<p class="price">'+
                                            '<span class="originalPrice">原价:￥'+limitData[s].original_price+'</span>'+
                                            '<span class="limitPrice">抢购价：<span class="moneySign">￥</span><strong>'+limitData[s].now_price+'</strong></span>'+
                                        '</p>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'
                        )
                        var type=".type"+s;
                        if(limitData[s].tagA){
                            $(type).append(
                                    '<span>'+limitData[s].tagA+'</span>'
                                )
                        }
                        if(limitData[s].tagB){
                            $(type).append(
                                    '<span>'+limitData[s].tagB+'</span>'
                                )
                        }
                        if(limitData[s].tagC){
                            $(type).append(
                                    '<span>'+limitData[s].tagC+'</span>'
                                )
                        }
                    }
                    // 限时抢购图片切换
                    var mySwiper = new Swiper('.swiper-container',{
                        pagination: '.pagination',
                        loop:true,
                        grabCursor: true,
                        paginationClickable: true,
                        centeredSlides: true,
                        autoplay:10000
                    });

                    // 遍历精选商品列表
                    var siftData=data.data.siftsClass;
                    for(var a=0; a<siftData.length; a++){
                        $(".goodsNav ul").append(
                                '<li>'+
                                    '<div>'+
                                        '<span class="on"></span>'+
                                        '<a href="javascript:void(0);">'+siftData[a].title+'</a>'+
                                    '</div>'+
                                '</li>'
                            )
                        $(".goodsNav ul li div").eq(1).addClass("stationery")
                        $(".goodsNav ul li div").eq(2).addClass("toy")
                        $(".goodsNav ul li div").eq(0).addClass("living")
                        $(".goodsList").append('<ul></ul>')
                        $(".goodsList ul:gt(0)").hide()
                        var siftsProduct=siftData[a].siftsProduct;
                        for(var i=0; i<siftsProduct.length; i++){
                            $(".goodsList ul").eq(a).append(
                                 '<li onclick="getProduct('+siftsProduct[i].id+')">'+
                                    '<img src="'+siftsProduct[i].thumb_image+'" alt="">'+
                                    '<h3>'+siftsProduct[i].title.substring(0,9)+'</h3>'+
                                    '<span class="onlinePrice"><span class="moneySign">￥</span><strong>'+siftsProduct[i].now_price+'</strong></span>'+
                                    '<div class="type sift'+a+i+'">'+
                                    '</div>'+
                                '</li>'
                            )
                            var sift=".sift"+a+i;
                            if(siftsProduct[i].tagA){
                                $(sift).append(
                                        '<span>'+siftsProduct[i].tagA+'</span>'
                                    )
                            }
                            if(siftsProduct[i].tagB){
                                $(sift).append(
                                        '<span>'+siftsProduct[i].tagB+'</span>'
                                    )
                            }
                            // if(siftsProduct[i].tagC){
                            //     $(sift).append(
                            //             '<span>'+siftsProduct[i].tagC+'</span>'
                            //         )
                            // }
                        }
                    }
                    
                    var num=0;
                        lunbo=function(){
                        num+=1;
                        $(".goodsNav ul li").eq(num-1).find("span").addClass("on").show().parent().parent().siblings().find("span").removeClass("on").hide()
                        var background=$(".goodsNav ul li").eq(num-1).find("a").css("background-color");
                        $(".goodsList").css("background-color",background)
                         $(".goodsList ul").eq(num-1).show().siblings().hide()
                        if(num>siftData.length-1){
                            num=0;
                        }
                    }
                    var t=setInterval(lunbo,10000)
                    $(".goodsNav li").click(function(){
                        window.clearInterval(t)
                        var index=$(this).index();
                        num=index;
                        $(this).find("span").addClass("on").show().parent().parent().siblings().find("span").removeClass("on").hide()
                        var background=$(this).find("a").css("background-color");
                        $(".goodsList").css("background-color",background)
                        $(".goodsList ul").eq(index).show().siblings().hide()
                        // $(".goodsList ul").html("")
                        // var siftsProduct=siftData[index].siftsProduct;
                        // for(var i=0; i<siftsProduct.length; i++){
                        //     $(".goodsList ul").append(
                        //          '<li onclick="getProduct('+siftsProduct[i].id+')">'+
                        //             '<img src="'+siftsProduct[i].thumb_image+'" alt="">'+
                        //             '<h3>'+siftsProduct[i].title.substring(0,9)+'</h3>'+
                        //             '<span class="onlinePrice"><span class="moneySign">￥</span><strong>'+siftsProduct[i].now_price+'</strong></span>'+
                        //             '<div class="type sift'+i+'">'+
                        //             '</div>'+
                        //         '</li>'
                        //     )
                        //     var sift=".sift"+i;
                        //     if(siftsProduct[i].tagA){
                        //         $(sift).append(
                        //                 '<span>'+siftsProduct[i].tagA+'</span>'
                        //             )
                        //     }
                        //     if(siftsProduct[i].tagB){
                        //         $(sift).append(
                        //                 '<span>'+siftsProduct[i].tagB+'</span>'
                        //             )
                        //     }
                        //     // if(siftsProduct[i].tagC){
                        //     //     $(sift).append(
                        //     //             '<span>'+siftsProduct[i].tagC+'</span>'
                        //     //         )
                        //     // }
                        // }
                        t=setInterval(lunbo,10000)
                    })
                    
                }
            }
        })
 var npage=0;
    var n=0;
    var a=0;
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
                    url:"/index.php/Index/getGroupProduct",
                    dataType:"json",
                    aysnc:true,
                    data:{
                        page:npage
                    },
                    success:function(data){
                        if(data.status==1){
                            isMore=false
                            n+=1;
                            var data=data.data;
                            if(data.length==0){
                                 $(".alert").show().find("p").html('没有更多了')
                            }
                            $(".content").append('<section class="groups groups'+n+'"></section>')
                            for(var i=0; i<data.length; i++){
                                a+=1;
                                // 遍历热文文章部分
                                if(data[i].article!=null){
                                    $(".groups"+n).append('<section class="classify classify'+a+'" onclick="getArticle('+data[i].article.id+')"></section>')
                                    $(".groups"+n).find(".classify"+a).append(
                                            ' <div class="article">'+
                                            '<div class="articleTap">'+
                                            '<span>文•'+data[i].article.class_title+'</span>'+
                                            '</div>'+
                                            '<div class="left">'+
                                            '<h3>'+data[i].article.prefix+data[i].article.desc.substring(0,18)+'</h3>'+
                                            // '<h3 style="margin-top:0.1rem;">'+data[i].article.title.substring(0,13)+'</h3>'+
                                            // '<p>教你如何跟宝宝拼好积木</p>'+
                                            '<div class="type">'+
                                            '<div class="comment">'+
                                            '<img src="../../../../Public/images/comment.png" alt="">'+
                                            '<span> 评论 ('+data[i].article.count+') | </span>'+
                                            '</div>'+
                                            '<div class="collect">'+
                                            '<img src="../../../../Public/images/collect.png" alt="">'+
                                            '<span>收藏 ('+data[i].article.collect+')  </span>'+
                                            '</div>'+
                                            // '<div class="send">'+
                                            //     '<img src="../../../../Public/images/send.png" alt="">'+
                                            //     '<span>转发 (65)</span>'+
                                            // '</div>'+
                                            '</div>'+
                                            '</div>'+
                                            '<div class="right">'+
                                            '<img src="'+data[i].article.image+'" alt="">'+
                                            '</div>'+
                                            '</div>'
                                    )
                                }
                                // 遍历课程团购
                                if(count(data[i].groupCurriculum)!=0){
                                    $(".groups"+n).append('<section class="courseList courseList'+a+'"><ul></ul></section>')
                                    for(var j=0; j<count(data[i].groupCurriculum); j++){
                                        $(".groups"+n).find(".courseList"+a+" ul").append(
                                                '<li onclick="getGroup('+data[i].groupCurriculum[j].id+')">'+
                                                '<div class="img">'+
                                                '<img src="'+data[i].groupCurriculum[j].image+'" alt="" class="courseImg">'+
                                                '<div class="tap">'+
                                                '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                                '<img src="'+data[i].groupCurriculum[j].logo+'" alt="" class="imgLogo">'+
                                                '</div>'+
                                                '<div class="type groupCurriculum'+j+'">'+
                                                '</div>'+
                                                '</div>'+
                                                '<div class="text">'+
                                                '<div class="time">'+
                                                '<img src="../../../../Public/images/indexFilter.png" alt="">'+
                                                '<span class="groupCurriculumTime" value="'+data[i].groupCurriculum[j].end_time+'"></span>'+
                                                '</div>'+
                                                '<h3>'+data[i].groupCurriculum[j].title.substring(0,10)+'</h3>'+
                                                '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+data[i].groupCurriculum[j].price+'</strong></span>'+
                                                '<p class="originalPrice">原价：'+data[i].groupCurriculum[j].original_price+'元/人</p>'+
                                                '<div class="groupNum groupNum'+j+'">'+
                                                '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data[i].groupCurriculum[j].groupCount+'</span>/<span class="allPeople">'+data[i].groupCurriculum[j].max_people+'</span>)</span>'+
                                                '<div class="bar"><span></span></div>'+
                                                '</div>'+
                                                '<a href="javascript:void(0);">立即加入</a>'+
                                                '</div>'+
                                                '</li>'
                                        )
                                        // 遍历描述标签
                                        var groupCurriculum=".groups"+n+" .courseList"+a+" .groupCurriculum"+j;
                                        if(data[i].groupCurriculum[j].tagA){
                                            $(groupCurriculum).append(
                                                    '<span>'+data[i].groupCurriculum[j].tagA+'</span>'
                                            )
                                        }else{
                                            $(groupCurriculum).hide()
                                        }
                                        if(data[i].groupCurriculum[j].tagB){
                                            $(groupCurriculum).append(
                                                    '<span>'+data[i].groupCurriculum[j].tagB+'</span>'
                                            )
                                        }
                                        if(data[i].groupCurriculum[j].tagC){
                                            $(groupCurriculum).append(
                                                    '<span>'+data[i].groupCurriculum[j].tagC+'</span>'
                                            )
                                        }
                                        // 拼团里的进度条颜色变化
                                        var groupNum=".groups"+n+" .courseList"+a+" .groupNum"+j;
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
                                if(data[i].product!=""){
                                    $(".groups"+n).append('<section class="course course'+a+'" onclick="getProduct('+data[i].product.id+')"></section>')
                                    $(".groups"+n).find(".course"+a).append(
                                            '<div class="courseContent">'+
                                            '<div class="coursesList">'+
                                            '<div class="text">'+
                                            '<h3>'+data[i].product.title.substring(0,20)+'</h3>'+
                                            '<span class="groupPrice">平台价：<span class="moneySign">￥</span><strong>'+data[i].product.now_price+'</strong></span>'+
                                            '<p class="originalPrice">原价：'+data[i].product.original_price+'元/人</p>'+
                                            '<p class="try">'+
                                            '<a href="javascript:void(0);" onclick="groupOrder(event,'+data[i].product.id+')">立即下单</a>'+
                                            '<a href="javascript:void(0);" onclick="appoint(event,'+data[i].product.id+')">预约试听</a>'+
                                            '</p>'+
                                            '</div>'+
                                            '<div class="img">'+
                                            '<img src="'+data[i].product.pic_url+'" alt="" class="courseImg">'+
                                            '<div class="tap">'+
                                            '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                            '<img src="'+data[i].product.logo+'" alt="" class="imgLogo">'+
                                            '</div>'+
                                            '<div class="type type'+i+'">'+
                                            ' </div>'+
                                            '</div>'+
                                            '</div>'+
                                            '</div>'
                                    )
                                    var groupCurriculum=".groups"+n+" .course"+a+" .type"+i;
                                    if(data[i].product.tagA){
                                        $(groupCurriculum).append(
                                                '<span>'+data[i].product.tagA+'</span>'
                                        )
                                    }else{
                                        $(groupCurriculum).hide()
                                    }
                                    if(data[i].product.tagB){
                                        $(groupCurriculum).append(
                                                '<span>'+data[i].product.tagB+'</span>'
                                        )
                                    }
                                    if(data[i].product.tagC){
                                        $(groupCurriculum).append(
                                                '<span>'+data[i].product.tagC+'</span>'
                                        )
                                    }
                                }
                                // 遍历视频
                                if(data[i].video!=null){
                                    $(".groups"+n).append('<section class="video video'+a+'" onclick="getArticle('+data[i].video.id+')"></section>')
                                    $(".groups"+n).find(".video"+a).append(
                                            '<div class="desc">'+
                                            '<span>视频</span>'+
                                            '</div>'+
                                            '<img src="'+data[i].video.image+'" alt="">'+
                                            '<img src="../../../../Public/images/play.png" alt="" class="playVideo">'
                                    )
                                }
                                // 遍历商品团购
                                if(count(data[i].groupProduct)){
                                    $(".groups"+n).append('<section class="shopGroups shopGroups'+a+'"><ul></ul></section>')
                                    var counts=count(data[i].groupProduct);
                                    if(counts%2!=0){
                                        counts=counts-1;
                                    }
                                    for(var j=0; j<counts; j++){
                                        $(".groups"+n).find(".shopGroups"+a+" ul").append(
                                                '<li onclick="getGroup('+data[i].groupProduct[j].id+')">'+
                                                '<div class="img">'+
                                                '<div class="time">'+
                                                '<span class="shopGroupsTime" value="'+data[i].groupProduct[j].end_time+'"></span>'+
                                                '</div>'+
                                                '<div class="type shopGroup'+j+'">'+
                                                '<span class="originalPrice">原价:￥'+data[i].groupProduct[j].original_price.split(".")[0]+'</span>'+
                                                '</div>'+
                                                '<img src="'+data[i].groupProduct[j].image+'" alt="">'+
                                                '</div>'+
                                                '<div class="text">'+
                                                '<h3>'+data[i].groupProduct[j].title.substring(0,10)+'</h3>'+
                                                '<div>'+
                                                '<span class="groupPrice">团购价:￥<strong>'+data[i].groupProduct[j].price.split(".")[0]+'</strong></span>'+
                                                '<a href="javascript:void(0);">加入团购</a>'+
                                                '</div>'+
                                                '<div class="groupNum shopping'+j+'">'+
                                                '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data[i].groupProduct[j].groupCount+'</span>/<span class="allPeople">'+data[i].groupProduct[j].max_people+'</span>)</span>'+
                                                '<div class="bar"><span></span></div>'+
                                                '</div>'+
                                                '</div>'+
                                                '</li>'
                                        )
                                        // 遍历描述标签
                                        var shopGroups=".groups"+n+" .shopGroups"+a+" .shopGroup"+j;
                                        if(data[i].groupProduct[j].tagA){
                                            $(shopGroups).append(
                                                    '<span class="tab">'+data[i].groupProduct[j].tagA+'</span>'
                                            )
                                        }
                                        if(data[i].groupProduct[j].tagB){
                                            $(shopGroups).append(
                                                    '<span class="tab">'+data[i].groupProduct[j].tagB+'</span>'
                                            )
                                        }

                                        if(data[i].groupProduct[j].tagC){
                                            $(shopGroups).append(
                                                    '<span class="tab">'+data[i].groupProduct[j].tagC+'</span>'
                                            )
                                        }
                                        // 拼团里的进度条颜色变化
                                        var groupNum=".groups"+n+" .shopGroups"+a+" .shopping"+j;
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
                                if(data[i].bargain){
                                    $(".groups"+n).append('<section class="shopBargain shopBargain'+a+'"><ul></ul></section>')
                                    var countss=data[i].bargain.length;
                                    if(countss%2!=0){
                                        countss=countss-1
                                    }
                                    for(var j=0; j<countss; j++){
                                        $(".groups"+n).find(".shopBargain"+a+" ul").append(
                                                '<li onclick="getBargainId('+data[i].bargain[j].id+','+data[i].bargain[j].key+')">'+
                                                '<div class="tapImg" style="top:0;left:0;">'+
                                                '<img src="/Public/images/bargainEax.png" alt="" style="width:2.46rem;height:1.28rem;"/>'+
                                                // '<span>砍价</span>'+
                                                '</div>'+
                                                '<div class="img">'+
                                                '<img src="'+data[i].bargain[j].pic_url+'" alt="">'+
                                                '</div>'+
                                                '<div class="text">'+
                                                '<h3>'+data[i].bargain[j].title.substring(0,10)+'</h3>'+
                                                '<p>'+
                                                '<span>平台价：￥<strong>'+data[i].bargain[j].now_price+'</strong></span>'+
                                                '</p>'+
                                                '<div class="type bargain'+j+'">'+
                                                '</div>'+
                                                '</div>'+
                                                '</li>'
                                        )
                                        // 遍历描述标签
                                        var bargain=".groups"+n+" .course"+a+" .bargain"+j;
                                        if(data[i].bargain[j].tagA){
                                            $(bargain).append(
                                                    '<span>'+data[i].bargain[j].tagA+'</span>'
                                            )
                                        }else{
                                            $(bargain).hide()
                                        }
                                        if(data[i].bargain[j].tagB){
                                            $(bargain).append(
                                                    '<span>'+data[i].bargain[j].tagB+'</span>'
                                            )
                                        }
                                        if(data[i].bargain[j].tagC){
                                            $(bargain).append(
                                                    '<span>'+data[i].bargain[j].tagC+'</span>'
                                            )
                                        }
                                    }
                                }
                            }
                        }
                    }

                })
             npage+=2;
                }
            }

        })
    // })
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
    // 抢购倒计时
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
            obj.html(str)
        });
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
                if(myH==-1){
                    var str = "已结束！";
                }
                obj.html(str);
            });
        }, 100);
    })
    
});