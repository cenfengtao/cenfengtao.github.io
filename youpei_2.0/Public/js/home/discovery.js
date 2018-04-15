$(function(){
    var winW=$(window).width();
    $(winW).resize(function(){
        location.reload()
    })
            //获取小圆点的集合
        var  circleList =  $(".hd ul>li");
        //获取banner图集合
        var bannerList = $(".bd ul li");
    var len=circleList.length;
        //左右箭头切换标记
        var circleTag = 0;
        var liLen=bannerList.length;
        //自动轮播标记
              var autoTag = 0;
        $(".bd ul").css({"width":winW*liLen+"px"});
        $(".bd li").width(winW);
        //小圆点切换banner图
        circleList.each(function(i){
            //为每一个小圆点绑定mouseover事件
            $(this).click(function(){
                clearlunbo();
                //鼠标移动到哪个圆点上，为该圆点增加active样式
              circleList.eq(i).addClass("on");
                //其他圆点取消active样式
                circleList.not(circleList.eq(i)).removeClass("on");
                //切换对应的banner图
                bannerList.eq(i).show();
                bannerList.not(bannerList.eq(i)).hide();
                
                    circleTag = i;
                    autoTag = i;
            })
            
        })
            //左箭头切换上一张
        function prev(){
                
                circleTag--;
                // console.log(circleTag)
                if(circleTag<0){
                    circleTag =len-1;
                }
                circleList.eq(circleTag).trigger("click");
            }
        
        //右箭头切换下一张
        function next(){
                circleTag++;
                //当下一页为最后一页时，则重置索引值，显示第一页
                if(circleTag>len-1){
                    circleTag =0;
                }
                circleList.eq(circleTag).trigger("click");
        }
      $(".bd ul li").swipe({
        swipeLeft: function(){
            next();
//          clearlunbo();
        },
        swipeRight: function(){
            prev();
//          clearlunbo();
        },
    })
        
            //自动轮播
        var autoTag=0;
//          var bool=true;
        var downtime =  setInterval(action,3000);//每隔一秒调用action函数
        //执行自动轮播的函数
        function action(){
                autoTag++;
            //如果已经是最后一页了，则重置为第一页
            if(autoTag>len-1){
                autoTag = 0;
            }
            circleList.eq(autoTag).trigger("click") 
        }       
        function clearlunbo(){
            clearInterval(downtime);
            setTimeout(function(){
                downtime=setInterval(action,3000)
            },1)
        }
})


    var mySwiper = new Swiper('.swiper-container',{
        pagination: '.pagination',
        loop:true,
        grabCursor: true,
        paginationClickable: true
    });
    $('.arrow-left').on('click', function(e){
        e.preventDefault();
        mySwiper.swipePrev();
    });
    $('.arrow-right').on('click', function(e){
        e.preventDefault();
        mySwiper.swipeNext();
    });
    var mySwiper1 = new Swiper('.swiper-container1',{
        pagination: '.pagination1',
        loop:true,
        grabCursor: true,
        paginationClickable: true
    });
    $('.arrow-left1').on('click', function(e){
        e.preventDefault();
        mySwiper1.swipePrev();
    });
    $('.arrow-right1').on('click', function(e){
        e.preventDefault();
        mySwiper1.swipeNext();
    });
    var npage = 7;
    $(function() {
        $(window).scroll(function() {
            var scrollTop = $(this).scrollTop();
            var scrollHeight = $(document).height();
            var windowHeight = $(this).height();
            if (scrollTop + windowHeight == scrollHeight) {
                //加载层
                layer.load();
                setTimeout(function(){layer.closeAll('loading');}, 1000);
                var url = "/index.php/Discovery/loadingProduct";
                var params = {};
                params.npage = npage;
                $.post(url,params,function(result) {
                    if (result.status == 0) {
                        $(".empty").remove();
                        return $('.productss').append("<div class='empty'><a href='/index.php/Boutique/index.html/'>点击进入精选商品列表页</a></div>");
                    }
                    $.each(result.data, function (index, value) {
                        $(".productss").append(
                            // "<li class='item selling' onclick='getProduct( " + value['id'] + ")'>" +
                            //     "<img class='thumb'  style='width:23vh;height:18vh;' src='" + value['pic_url'] + "'>" +
                            //     "<div class='triangle'><p class='text'>折扣</p><i class='fireico'>&nbsp;</i></div>" +
                            //     "<span class='title'>" + value['title'] + "</span>" +
                            //     "<span class='abstract'>简介：" + value['f_title'] + "</span>" +
                            //     "<span class='discounts'>优惠价：<span class='price'>" + value['price']['now_price'] + "</span></span>" +
                            //     "</li>"
                                "<li onclick='getProduct( " + value['id'] + ")'>"+
                                    "<a href='###' class='img'>"+
                                        "<img src='" + value['pic_url'] + "' alt=''>"+
                                    "</a>"+
                                    "<div class='texts'>"+
                                        "<h3>" + value['title'] + "</h3>"+
                                        "<div class='describe'>简介：" + value['f_title'] + "</div>"+
                                        "<div class='original-price'>原价：￥" + value['price']['original_price'] + "</div>"+
                                        "<p>"+
                                            "<span>现价：</span>"+
                                           " <span>￥</span>"+
                                           " <span>" + value['price']['now_price'] + "</span>"+
                                        "</p>"+
                                    "</div>"+
                                "</li>"
                                );
                    });
                });
                npage += 6;
            }
        });
    });
    function getCateListById(id){
        window.location.href = "/index.php/Article/index?cate_id="+ id;
    }
    $(document).ready(function () {
        // $(".mainvisual").hover(function(){
        //     $("#btnprev,#btnnext").fadeIn()
        // },function(){
        //     $("#btnprev,#btnnext").fadeOut()
        // });
        $dragBln = false;
        $(".mainimage").touchSlider({
            flexible: true,
            speed: 500,
            btn_prev: $("#btnprev"),
            btn_next: $("#btnnext"),
            paging: $(".flickingcon a"),
            counter: function (e) {
                $(".flickingcon a").removeClass("on").eq(e.current - 1).addClass("on");
            }
        });
        $(".mainimage").bind("mousedown", function () {
            $dragBln = false;
        });
        $(".mainimage").bind("dragstart", function () {
            $dragBln = true;
        });
        $(".mainimage a").click(function () {
            if ($dragBln) {
                return false;
            }
        });
        // timer = setInterval(function(){
        //     $("#btnnext").click();
        // }, 5000);

        // $(".mainvisual").hover(function(){
        //     clearInterval(timer);
        // },function(){
        //     timer = setInterval(function(){
        //         $("#btnnext").click();
        //     },5000);
        // });

        // $(".mainimage").bind("touchstart",function(){
        //     clearInterval(timer);
        // }).bind("touchend", function(){
        //     timer = setInterval(function(){
        //         $("#btnnext").click();
        //     }, 5000);
        // });
    });
    $(document).ready(function () {
        $dragBln = false;
        $(".mainimage1").touchSlider({
            flexible: true,
            speed: 500,
            btn_prev: $("#btnprev1"),
            btn_next: $("#btnnext1"),
            paging: $(".flickingcon1 a"),
            counter: function (e) {
                $(".flickingcon1 a").removeClass("on").eq(e.current - 1).addClass("on");
            }
        });
        $(".mainimage1").bind("mousedown", function () {
            $dragBln = false;
        });
        $(".mainimage1").bind("dragstart", function () {
            $dragBln = true;
        });
        $(".mainimage1 a").click(function () {
            if ($dragBln) {
                return false;
            }
        });
        // timer = setInterval(function(){
        //     $("#btnnext1").click();
        // }, 5000);

        // $(".mainvisual1").hover(function(){
        //     clearInterval(timer);
        // },function(){
        //     timer = setInterval(function(){
        //         $("#btnnext1").click();
        //     },5000);
        // });

        // $(".mainimage1").bind("touchstart",function(){
        //     clearInterval(timer);
        // }).bind("touchend", function(){
        //     timer = setInterval(function(){
        //         $("#btnnext1").click();
        //     }, 5000);
        // });
    });
    function getArticle(id) {
        window.location.href = "/index.php/Article/getArticle?art_id=" + id;
    }
    function getProduct(id) {
        window.location.href = "/index.php/Product/productDetails?pro_id=" + id;
    }
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