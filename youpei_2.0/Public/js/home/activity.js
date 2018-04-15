
// 轮播
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

//调转到对应id的页面
function getArticle(id) {
    window.location.href = "/index.php/Article/getArticle?art_id=" + id;
}
// 跳转到对应id的商品页
function getProduct(id) {
    window.location.href = "/index.php/Product/productDetails.html?pro_id=" + id;
}
// 跳转到对应id的课程页
function getCourse(id) {
    window.location.href = "/index.php/Product/productDetails.html?pro_id="+id;
}



$(function(){
    $.ajax({
         type: "post",
         url: "/index.php/Activity/indexAjax",
         aysnc: true,
         dataType: "json",
         success:function(data){
            console.log(data)
            var courseLength=data.data.courses.length;
            var procuctLength=data.data.products.length;
            if(procuctLength%2==0){
                procuctLength=procuctLength;
            }else{
                procuctLength=procuctLength-1;
            }
            var articleLength=data.data.articles.length;
            // 遍历课程数据
            for(var i=0; i<courseLength; i++){
                var courseId=data.data.courses[i].id;
                var courseImg=data.data.courses[i].pic_url;
                var course=data.data.courses[i];
                $(".courses").append(
                    '<li onclick="getCourse('+courseId+')">'+
                        '<div class="courseImg">'+
                            '<a href="###">'+
                               ' <img src="'+courseImg+'" alt="">'+
                                // '<span>优培圈</span>'+
                            '</a> '  +
                       ' </div>'+
                        '<div class="text">'+
                            '<h3>'+course.titles+'</h3>'+
                            '<div class="type">'+
                                '<span class="tagFirst">'+course.tagA+'</span>'+
                                '<span class="tagTwo">'+course.tagB+'</span>'+
                                '<span class="tagThree">'+course.tagC+'</span>'+
                            '</div>'+
                           ' <a href="###" class="address">'+
                               ' <img src="../../../../Public/images/address.png" alt="">'+
                                '<span>'+course.add+'</span>'+
                           ' </a>'+
                            '<span class="original_price">原价:￥:'+course.original_price+'</span>'+
                            '<p>'+
                               ' <span>平台价：</span>'+
                                '<span>￥</span>'+
                                '<span>'+course.price+'</span>'+
                                '<span>起</span>'+
                           ' </p>'+
                        '</div>'+
                    '</li>'
                )
                if(course.tagC == ''){
                     var tagC = document.getElementsByClassName('type')[i].lastChild;
                     tagC.style.display="none";
                 }
                 if(course.tagB ==""){
                     var tagB = document.getElementsByClassName('type')[i].lastChild;
                     tagB.style.display="none";
                 }
                 if(course.tagA ==""){
                     var tagA = document.getElementsByClassName('type')[i].lastChild;
                     tagA.style.display="none";
                 }
            }
            // 遍历商品数据
            for(var j=0; j<procuctLength; j++){
                var productImg=data.data.products[j].pic_url;
                var productId=data.data.products[j].id;
                var products=data.data.products[j];
                $(".products").append(
                    '<li onclick="getProduct('+productId+')">'+
                        '<a href="###" class="img">'+
                            '<img src="'+productImg+'" alt="">'+
                        '</a>'+
                        '<div class="text">'+
                            '<h3>'+products.titles+'</h3>'+
                            '<div class="describe">'+products.f_title+'</div>'+
                            '<span class="original_price">原价:￥:'+products.original_price+'</span>'+
                            '<p>'+
                               ' <span>现价：</span>'+
                                '<span>￥</span>'+
                                '<span>'+products.price+'</span>'+
                            '</p>'+
                        '</div>'+
                    '</li>'
                )
            }
            //遍历文章或视频数据
            for(var a=0; a<articleLength; a++){
                var articleId=data.data.articles[a].id;
                var articleImg=data.data.articles[a].image;
                var article=data.data.articles[a];
                $(".articles").append(
                     '<li class="articleLi" onclick="getArticle('+articleId+')">'+
                       ' <img src="'+articleImg+'" alt="">'+
                        '<div class="text">'+
                            '<h3>'+article.titles+'</h3>'+
                            // '<div class="describe">'+article.desc+'</div>'+
                            '<span class="biao">'+article.cate_name+'</span>'+
                            '<span class="time">'+article.create_time+'</span>'+
                            '<span class="custom">评论（'+article.count+'）| 收藏（'+article.collect+'）</span>'+
                        '</div>'+
                    '</li>'
                )
            }
         }
    })
})         
// 底部半圆图片切换
setTimeout(function(){
    $(".footer").show()
},200)
$(function(){
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
})
