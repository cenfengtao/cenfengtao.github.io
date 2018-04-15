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
});
$(function(){
    $.ajax({
        type:"get",
        url:"/index.php/Boutique/ajaxIndex",
        aysnc:true,
        dataType:"json",
        success:function(data){
            var classList=data.data.class;
            var classLength=classList.length;
            var listList=data.data.list;
            var listLength=listList.length;
            for(var i=0; i<classLength; i++){
                $(".smallNav").append(
                        '<li id="'+classList[i].id+'"><a href="javascript:void(0)">'+classList[i].title+'</a></li>'
                    )
            }
            for(var j=0; j<listLength; j++){
                $(".products").append(
                        '<li onclick="getProduct('+listList[j].id+')">'+
                            '<a href="###" class="img">'+
                                '<img src="'+listList[j].pic_url+'" alt="">'+
                            '</a>'+
                            '<div class="text">'+
                                '<h3>'+listList[j].title+'</h3>'+
                                '<div class="describe">'+listList[j].f_title+'</div>'+
                                '<span class="original_price">原价：￥'+listList[j].price.original_price+'</span>'+
                                '<p>'+
                                    '<span>现价：</span>'+
                                    '<span>￥</span>'+
                                    '<span>'+listList[j].price.now_price+'</span>'+
                                '</p>'+
                            '</div>'+
                        '</li>'
                    )
            }
            $(".smallNav li").click(function(){
                $(this).addClass("on").siblings().removeClass("on");
                var id=$(this).attr("id")
                $.ajax({
                    type:"get",
                    url:"/index.php/Boutique/ajaxIndex",
                    aysnc:true,
                    dataType:"json",
                    data:{
                        class_id:id
                    },
                    success:function(data){
                        $(".products").html("");
                        $(".empty").html("");
                        var listList=data.data.list;
                        var listLength=listList.length;
                        for(var j=0; j<listLength; j++){
                            $(".products").append(
                                    '<li onclick="getProduct('+listList[j].id+')">'+
                                        '<a href="###" class="img">'+
                                            '<img src="'+listList[j].pic_url+'" alt="">'+
                                        '</a>'+
                                        '<div class="text">'+
                                            '<h3>'+listList[j].title+'</h3>'+
                                            '<div class="describe">'+listList[j].f_title+'</div>'+
                                            '<span class="original_price">原价：￥'+listList[j].price.original_price+'</span>'+
                                            '<p>'+
                                                '<span>现价：</span>'+
                                                '<span>￥</span>'+
                                                '<span>'+listList[j].price.now_price+'</span>'+
                                            '</p>'+
                                        '</div>'+
                                    '</li>'
                                )
                        }
                    }
                })
            })
        }
    })
})
function getProduct(id) {
    window.location.href = "/index.php/Product/productDetails?pro_id=" + id;
}
var npage=10;
$(function () {
    $(window).scroll(function () {
        var scrollTop = $(this).scrollTop();
        var scrollHeight = $(document).height();
        var windowHeight = $(this).height();
        if (scrollTop + windowHeight == scrollHeight) {
            //加载层
            layer.load();
            setTimeout(function () {
                layer.closeAll('loading');
            }, 1000);
            var id=$(".on").attr("id");
            // console.log(id)
            var url = "/index.php/Boutique/loadingProduct";
            var params = {};
            params.npage = npage;
            params.class_id=id;

            $.post(url, params, function (result) {
                if (result.status == 0) {
                    $(".empty").remove();
                    return $('.productContent').append("<div class='empty'>没有更多了</div>");
                }
                $.each(result.data, function (index, value) {
                    $(".products").append(
                        '<li onclick="getProduct('+value['id']+')">'+
                            '<a href="###" class="img">'+
                                '<img src="'+value['pic_url']+'" alt="">'+
                            '</a>'+
                            '<div class="text">'+
                                '<h3>'+value['title']+'</h3>'+
                                '<div class="describe">'+value['f_title']+'</div>'+
                                '<span class="original_price">原价：￥'+value['price']['original_price']+'</span>'+
                                '<p>'+
                                    '<span>现价：</span>'+
                                    '<span>￥</span>'+
                                    '<span>'+value['price']['now_price']+'</span>'+
                                '</p>'+
                            '</div>'+
                        '</li>'
                    )
                });
            });
            npage += 6;
        }
    });
});