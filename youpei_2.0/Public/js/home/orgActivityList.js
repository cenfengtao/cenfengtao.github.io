 $(function(){
// 延时加载底部导航
$(window).load(function (){
    setTimeout(function () {
        $('.footer').show();
    },2000)
});
// 底部导航选中状态
$(".footer ul li>img").css("display","none");
$(".footer ul li>img:eq(3)").css("display","block");
$(" .footer ul li").click(function(){
    $(this).addClass('on').siblings().removeClass("on");
    var index=$(this).index();
    var liLen=$(".footer ul li").length;
    for(var i=0;i<=liLen-1;i++){
        $(".footer ul li>img").eq(i).css("display","none");
    }
    $(this).find("img:eq(0)").css("display","block");
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
    url:"/index.php/Organization/ajaxActivityList",
    data:{
        id:id,
        page:0
    },
    dataType:"json",
    success:function(data){
        if(data.status==1){
            var data=data.data;
            if(data.list.length==0){
                $(".alert").show().find("p").html("亲，暂时没有活动哦！").css("background","")
            }
            for(var i=0; i<data.list.length; i++){
                if(data.list[i].activity_type==2){
                    $(".activity .match").append(
                        '<li onclick="voteList('+data.list[i].id+')" class="type1">'+
                            '<div class="img">'+
                                '<img src="'+data.list[i].image+'" alt="" class="courseImg">'+
                                '<div class="type" style="display:none;">'+
                                    '<span>48课时</span>'+
                                    '<span>名师</span>'+
                                    '<span>气质</span>'+
                                '</div>'+
                            '</div>'+
                            '<div class="text">'+
                                '<h3>'+data.list[i].title.substring(0,15)+'</h3>'+
                                '<div class="work">'+
                                    '<p class="workNum">'+
                                        '<span>参与作品数 : <strong>'+data.list[i].userWorksCount+'</strong></span>'+
                                    '</p>'+
                                    '<p class="heat heat'+i+'">'+
                                    '</p>'+
                                '</div>'+
                                '<div class="date">'+
                                    '<span>活动时间 : </span>'+
                                    '<span class="dateTime">'+data.list[i].work_start_time+'-'+data.list[i].end_time+'</span>'+
                                '</div>'+
                                '<a href="javascript:void(0);" class="join" attr="'+data.list[i].vote_status+'">报名参加</a>'+
                            '</div>'+
                        '</li>'
                    )
                        var num=".heat"+i;
                        if(data.list[i].userWorksCount>-1){
                            $(num).html(
                                    '<span>热度 : </span>'+
                                    '<img src="../../../../Public/images/star.png" alt="">'
                                )
                        }
                        if(data.list[i].userWorksCount>5){
                            $(num).html(
                                    '<span>热度 : </span>'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'
                                )
                        }
                        if(data.list[i].userWorksCount>8){
                            $(num).html(
                                    '<span>热度 : </span>'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'
                                )
                        }
                        if(data.list[i].userWorksCount>11){
                            $(num).html(
                                    '<span>热度 : </span>'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'
                                )
                        }
                        if(data.list[i].userWorksCount>14){
                            $(num).html(
                                    '<span>热度 : </span>'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'+
                                    '<img src="../../../../Public/images/star.png" alt="">'
                                )
                        }
                }else if(data.list[i].activity_type==1){
                    $(".parent").append(
                            '<li onclick="getPar('+data.list[i].id+')">'+
                                '<div class="imgs">'+
                                    '<span class="address">'+
                                        '<img src="../../../../Public/images/addr.png" alt="">'+
                                        '<a href="/index.php/Map/GPS.html?par_id='+data.list[i].id+'">'+data.list[i].province+data.list[i].city+data.list[i].area+'</a>'+
                                    '</span>'+
                                    '<img src="'+data.list[i].image+'" alt="">'+
                                    '<span class="price">￥<strong>'+data.list[i].price+'</strong>起</span>'+
                                '</div>'+
                                '<div class="texts">'+
                                    '<p class="title">'+data.list[i].title+'</p>'+
                                    '<p class="time"><span data-icon="Š">'+data.list[i].time+'</span></p>'+
                                    '<div class="types types'+i+'">'+
                                    '</div>'+
                                '</div>'+
                            '</li>'
                        )
                    var type=".types"+i;
                    if(data.list[i].tagA){
                        $(type).append('<span>'+data.list[i].tagA+'</span>')
                    }
                    if(data.list[i].tagB){
                        $(type).append('<span>'+data.list[i].tagB+'</span>')
                    }
                    if(data.list[i].tagC){
                        $(type).append('<span>'+data.list[i].tagC+'</span>')
                    }
                }
            }
            var joinLength=$(".join").length;
            for(var j=0; j<joinLength; j++){
                var vote_status=$(".join").eq(j).attr("attr");
                if(vote_status==1){
                    $(".join").eq(j).css("background","gray")
                }
            }
            
        }
    }
})
})
// 滑动加载群数据
var n=0;
var npage=10;
$(window).scroll(function () {
var scrollTop = $(this).scrollTop();
var scrollHeight = $(document).height();
var windowHeight = $(this).height();
if (scrollTop + windowHeight == scrollHeight) {
    $(this).scrollTop(scrollHeight - 50);
    //加载层
    layer.load();
    setTimeout(function () {
        layer.closeAll('loading');
    }, 1000);
    $.ajax({
            type:"get",
            url:"/index.php/Organization/ajaxActivityList",
            data:{
                id:id,
                page:npage
            },
            dataType:"json",
            success:function(data){
                if(data.status==1){
                    n+=1;
                    var data=data.data;
                    if(data.list.length==0){
                        $(".alert").show().find("p").html('没有更多了')
                    }
                    for(var i=0; i<data.list.length; i++){
                        if(data.list[i].activity_type==2){
                            $(".activity .match").append(
                                '<li onclick="voteList('+data.list[i].id+')" class="type1">'+
                                    '<div class="img">'+
                                        '<img src="'+data.list[i].image+'" alt="" class="courseImg">'+
                                        '<div class="type" style="display:none;">'+
                                            '<span>48课时</span>'+
                                            '<span>名师</span>'+
                                            '<span>气质</span>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="text">'+
                                        '<h3>'+data.list[i].title.substring(0,15)+'</h3>'+
                                        '<div class="work">'+
                                            '<p class="workNum">'+
                                                '<span>参与作品数 : <strong>'+data.list[i].userWorksCount+'</strong></span>'+
                                            '</p>'+
                                            '<p class="heat heats'+n+i+'">'+
                                            '</p>'+
                                        '</div>'+
                                        '<div class="date">'+
                                            '<span>活动时间 : </span>'+
                                            '<span class="dateTime">'+data.list[i].work_start_time+'-'+data.list[i].end_time+'</span>'+
                                        '</div>'+
                                        '<a href="javascript:void(0);" class="joins" attr="'+data.list[i].vote_status+'">报名参加</a>'+
                                    '</div>'+
                                '</li>'
                            )
                                var num=".heats"+n+i;
                                if(data.list[i].userWorksCount>-1){
                                    $(num).html(
                                            '<span>热度 : </span>'+
                                            '<img src="../../../../Public/images/star.png" alt="">'
                                        )
                                }
                                if(data.list[i].userWorksCount>5){
                                    $(num).html(
                                            '<span>热度 : </span>'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'
                                        )
                                }
                                if(data.list[i].userWorksCount>8){
                                    $(num).html(
                                            '<span>热度 : </span>'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'
                                        )
                                }
                                if(data.list[i].userWorksCount>11){
                                    $(num).html(
                                            '<span>热度 : </span>'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'
                                        )
                                }
                                if(data.list[i].userWorksCount>14){
                                    $(num).html(
                                            '<span>热度 : </span>'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'+
                                            '<img src="../../../../Public/images/star.png" alt="">'
                                        )
                                }
                        }else if(data.list[i].activity_type==1){
                            $(".parent").append(
                                    '<li onclick="getPar('+data.list[i].id+')">'+
                                        '<div class="imgs">'+
                                            '<span class="address">'+
                                                '<img src="../../../../Public/images/addr.png" alt="">'+
                                                '<a href="/index.php/Map/GPS.html?par_id='+data.list[i].id+'">'+data.list[i].province+data.list[i].city+data.list[i].area+'</a>'+
                                            '</span>'+
                                            '<img src="'+data.list[i].image+'" alt="">'+
                                            '<span class="price">￥<strong>'+data.list[i].price+'</strong>起</span>'+
                                        '</div>'+
                                        '<div class="texts">'+
                                            '<p class="title">'+data.list[i].title+'</p>'+
                                            '<p class="time"><span data-icon="Š">'+data.list[i].time+'</span></p>'+
                                            '<div class="types typess'+n+i+'">'+
                                            '</div>'+
                                        '</div>'+
                                    '</li>'
                                )
                            var types=".typess"+n+i;
                            if(data.list[i].tagA){
                                $(types).append('<span>'+data.list[i].tagA+'</span>')
                            }
                            if(data.list[i].tagB){
                                $(types).append('<span>'+data.list[i].tagB+'</span>')
                            }
                            if(data.list[i].tagC){
                                $(types).append('<span>'+data.list[i].tagC+'</span>')
                            }
                        }
                        
                    }
                    var joinLength=$(".joins").length;
                    for(var j=0; j<joinLength; j++){
                        var vote_status=$(".joins").eq(j).attr("attr");
                        if(vote_status==1){
                            $(".joins").eq(j).css("background","gray")
                        }
                    }
                }
            }
        })
    npage+=5;
}
})


// 跳转到对应的活动里去
function voteList(id){
window.location.href="/index.php/Vote/voteList.html?vote_id="+id;
}
function getPar(id) {
window.location.href = "/index.php/Parenting/productDetails.html?par_id=" + id;
}
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