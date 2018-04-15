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
 $(function(){
    // 关注优培圈的显示与隐藏
     $('.headRight').click(function(){
       $(".ty-qrcode-area").css("display","block");
     
     })
    $(".ty-qrcode-area").bind('click',function(){
        $('.ty-qrcode-area').css('display','none');
    });
    // 获取未完成任务数
    var redLength=$(".myTasks .red").length;
    $(".situation strong").text(redLength);
    // 未完成任务总积分数
    var num=0;
    for(var i=0; i<redLength; i++){
       number=parseInt($(".myTasks .red").eq(i).siblings("a").children(".mark").text().split("分")[0]);
       num+=number;
    }
    $(".getAll").text(num)
    // 获取更多积分提示
    $(".getMore").click(function(){
        $(".tellBox").fadeIn(1000);
    })
    $(".closeBtn").click(function(){
        $(".tellBox").hide();
        $(".tellBoxs").hide();
    })
    // 发展学友弹框
     $(".getClassmates").click(function(){
        $(".tellBoxs").fadeIn(1000);
    })
 })
 function student(){
    $(".tellBoxs").fadeIn(1000);
 }
// 积分兑换红包
function red(id) {
    layer.open({
        type: 2,
        title: '积分兑红包',
        shadeClose: false,
        shade: 0.8,
        area: ['80%', '60%'],
        content: ["/index.php/Personal/red,array('id'=>" + id + "", 'no'] //iframe的url
    });
}
// 活动奖品
function getGamesPrize() {
    window.location.href = "/index.php/Games/prize.html";
}
// 签到任务
 function signin(){
    window.location.href = "/index.php/Sign/index.html";
}
// 文章任务
function today_share() {
    window.location.href = "/index.php/Article/index.html";
}
 //机构
    function getOrgId(id) {
        window.location.href = "/index.php/Organization/home.html?id=" + id;
    }
    //商品
    function getProduct() {
        window.location.href = "/index.php/Boutique/index.html";
    }
 //商品
    function personal() {
        window.location.href = "/index.php/Personal/personalData.html";
    }

// <!-- 20160621 updated -->
// <!-- 获取微信个人信息 start-->
function getUrlArg(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null)return unescape(r[2]);
    return null;
}
function is_weixn() {
    var ua = navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == "micromessenger") {
        return true;
    } else {
        return false;
    }
}
function setCookie(name, value) {
    var Days = 30;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}

function delCookie(c_name, value) {
    var date = new Date();
    //将date设置为过去的时间
    date.setTime(date.getTime() - 10000);
    //将userId这个cookie删除
    document.cookie = c_name + "=" + escape(value) + " ; expires=" + date.toGMTString();
}

function getCookie(name) {
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null) return unescape(arr[2]);
    return null;
}
var timestamp = (new Date()).valueOf();
var hash = location.hash;
var source = getUrlArg("source");
function handleOpenURL(url) {
    setTimeout(function () {
        var link = "";
        if (link.indexOf("http://www.youpei-exc.com") == 0) window.location.href = link;
    }, 0);
}
// 获取微信个人信息 end

function haveMoney() {
    var nowMoney = $("#nowMoney").val();
    layer.open({
        type: 1,
        title: '提现',
        shade: 0.8,
        skin: 'layui-layer-rim',
        area: ['80%', '60%'],
        content: "<section>" +
        "<input type='number' min='1' value='"+nowMoney+"' name='money' style='margin:0.5rem auto;display: block;' onchange='checkMoney()'>" +
        "<button onclick='getMoney()' id='getMoney' style='display:block;margin:0 auto;background: #d7595f;padding:0.4rem 0.6rem;font-size:0.5rem;color:#fff;'>立即兑换</button>" +
        "</section>"
    });
    if(nowMoney < 1) {
        $("#getMoney").removeAttr('onclick').css('background','#999');
    }
}

function checkMoney() {
    var money = $("input[name='money']").val();
    if (money < 1 || money > 50) {
        $("#getMoney").removeAttr('onclick').css('background','#999');
    } else {
        $("#getMoney").attr('onclick', 'getMoney()').css('background','#d7595f');
    }
}

function getMoney() {
    var money = $("input[name='money']").val();
    if (money < 1 || money > 50) {
        $("#getMoney").css('background','#999');
        return alert('提现金额不正确');
    }
    var url = "/index.php/Personal/getMoney";
    $.post(url,{money:money},function (result) {
        if (result.status == 0) {
            layer.closeAll();
            return alert(result.message);
        } else {
            layer.closeAll();
            alert(result.message);
            setTimeout(function () {
                location.reload();
            },2000);
        }
    }, 'json');
}