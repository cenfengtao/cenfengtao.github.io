// 底部半圆图片切换
$(function(){
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
})