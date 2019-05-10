$(".act-tag span").on('touchstart mousedown', function (e) {
    e.preventDefault()
    $(".act-tag span").removeClass('on')
    $(this).addClass('on')
    $(".ty-detail-content .tabcon").eq($(this).index()).addClass("curr").siblings().removeClass("curr");
});
$(".act-tag span").click(function (e) {
    e.preventDefault();
});
$(function () {
    $('.act-tag span').on('touchstart mousedown', function () {
        $('html,body').animate({scrollTop: $('.act-tag').offset().top - 60}, 500);
    });
    $(".act-tag").removeClass("fixed");
});
$(window).scroll(function () {
    var bar_h = $(".act-tag").height();
    var _top = $(window).scrollTop();
    var tag_top = $("#detail_activity .ty-detail-cover").offset().top + $("#detail_activity .ty-detail-cover").height();
    if (tag_top - _top < bar_h) {
        $(".act-tag").addClass("fixed");
        if ($(".kap-top").hasClass("ty-menu-top-hide")) {
            $(".act-tag").addClass("top");
        } else {
            $(".act-tag").removeClass("top");
        }
    } else {
        $(".act-tag").removeClass("fixed");
    }
});
$('#addProduct').click(function () {
    if ($('#product_contact_memo').val().length <= 6) {
        alert('请填写不少于6个字');
        return;
    }
    var group_id = $("input[name='group_id']").val();
    var url = "/index.php/Groups/getGroup";
    var data = {
        group_id: group_id,
        content: $('#product_contact_memo').val()
    };
    $.post(url, data, function (res) {
        if (res.status == 0) {
            return alert(res.msg);
        } else if (res.status == 1) {
            alert(res.msg);
            $('.kap-bottomwin').css('display', 'none');
            $('.kap-mask-on').css('display', 'none');
            $("#product_contact_memo").val('');
            $('.list-comment').append(
                "<li class='item assign'>" +
                "<div class='user-comment' onclick='getFather(" + res.data['id'] + ")'>" +
                "<img src='" + res.data['headImg'] + "' alt=''><span class='text'>" + res.data['content'] + "</span>" +
                "</div>" +
                "<div class='replys'></div>" +
                "</li>"
            );
        }
    }, 'json');
});
//留言弹框
$(function () {
    $('.btn-comment').click(function () {
        $('.kap-bottomwin').css('display', 'block');
        $('.kap-mask-on').css('display', 'block');
    });
    $('.btn-close').click(function () {
        $('.kap-bottomwin').css('display', 'none');
        $('.kap-mask-on').css('display', 'none');
    });
});
//页内跳转
// $(document).ready(function () {
//     $("#desc").click(function () {
//         $('html, body').animate({
//             scrollTop: $("#descs").offset().top
//         }, 1000);
//         // $(this).addClass("on").siblings().removeClass("on")
//     });
//     $("#costc").click(function () {
//         $('html, body').animate({
//             scrollTop: $("#costs").offset().top
//         }, 1000);
//         // $(this).addClass("on").siblings().removeClass("on")
//     });
//     $("#comment").click(function () {
//         $('html, body').animate({
//             scrollTop: $("#comments").offset().top
//         }, 500);
//         // $(this).addClass("on").siblings().removeClass("on")
//     });
// });

function checkOrderInfo() {
    window.location.href = "/index.php/Groups/checkOrderInfo,array('groupRecordId' => $groupRecordId)";
}

$('.item').click(function () {
    $(this).addClass('assign');
    $(this).siblings('.item').removeClass('assign')
});

function getFather(id) {
    $('.kap-background').css('display', 'block');
    $('.kap-comment').css('display', 'block');
    $('.btn-close').click(function () {
        $('.kap-background').css('display', 'none');
        $('.kap-comment').css('display', 'none');
        $("#product_contact_meto").val('');
    });
    $('#addComment').attr('attr_father_id', id);
}

function getFatherId(id, type_id) {
    $('.kap-background').css('display', 'block');
    $('.kap-comment').css('display', 'block');
    $('.btn-close').click(function () {
        $('.kap-background').css('display', 'none');
        $('.kap-comment').css('display', 'none');
        $("#product_contact_meto").val('');
    });
    $('#addComment').attr('attr_father_id', id);
    $('#addComment').attr('attr_type_id', type_id);

}

$('#addComment').click(function () {
    if ($('#product_contact_meto').val() == '' && $('#product_contact_meto').val() > 0) {
        alert('内容不能为空');
        return;
    }
    if ($('#product_contact_meto').val().length <= 6) {
        alert('请填写不少于6个字');
        return;
    }
    if ($('#addComment').attr('attr_father_id') != '' && $('#addComment').attr('attr_type_id') == '') {
        var url = "/index.php/Groups/comment";
        var group_id = $("input[name='group_id']").val();
        var father_id = $('#addComment').attr('attr_father_id');
        var type_id = $('#addComment').attr('attr_father_id');
        var data = {
            group_id: group_id,
            father_id: father_id,
            type_id: type_id,
            content: $('#product_contact_meto').val()
        };
        $('.kap-background').css('display', 'none');
        $('.kap-comment').css('display', 'none');
        $("#product_contact_meto").val('');
        $.post(url, data, function (res) {
            if (res.status == 0) {
                alert(res.msg);
            } else if (res.status == 1) {
                alert(res.msg);
                $('.assign .replys').append(
                    "<div class='reply' onclick='getFatherId(" + res.data['id'] + "," + res.data['type_id'] + ")'>" +
                    "<div class='image'><img src='" + res.data['headImg'] + "' alt=''>回复：<img src='" + res.data['headImgs'] + "' alt=''></div><span class='text'>" + res.data['content'] + "</span>" +
                    "</div>"
                );
            }
        }, 'json');
    } else if ($('#addComment').attr('attr_father_id') != '' && $('#addComment').attr('attr_type_id') != '') {
        var url = "/index.php/Groups/comment";
        var group_id = $("input[name='group_id']").val();
        var father_id = $('#addComment').attr('attr_father_id');
        var type_id = $('#addComment').attr('attr_type_id');
        var data = {};
        data.group_id = group_id;
        data.father_id = father_id;
        data.type_id = type_id;
        data.content = $('#product_contact_meto').val();
        $('.kap-background').css('display', 'none');
        $('.kap-comment').css('display', 'none');
        $("#product_contact_meto").val('');
        $.post(url, data, function (res) {
            if (res.status == 0) {
                alert(res.msg);
            } else if (res.status == 1) {
                alert(res.msg);
                $('.assign .replys').append(
                    "<div class='reply' onclick='getFatherId(" + res.data['id'] + "," + res.data['type_id'] + ")'>" +
                    "<div class='image'><img src='" + res.data['headImg'] + "' alt=''>回复：<img src='" + res.data['headImgs'] + "' alt=''></div><span class='text'>" + res.data['content'] + "</span>" +
                    "</div>"
                );
            }
        }, 'json');
    }
})
//    机构
function organ(id) {
    window.location.href = "/index.php/Organization/home?id=" + id;
}
//    全景
function fullShot(id) {
    window.location.href = "/index.php/Organization/fullShot?id=" + id;
}
// 获取url中的参数
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


// 生成海报图片
function createGroupPoster() {
    layer.msg('生成海报中，请耐心等候');
    var id = GetRequest().id;
    var ajaxUrl = "/index.php/Groups/createGroupPoster";
    $.get(ajaxUrl, {id: id}, function (result) {
        if (result.status == 0) {
            return alert(result.message);
        }
        if (result.status == 1) {
            var imageUrl = result.data;
            layer.open({
                type: 1,
                title: '分享团购海报',
                skin: 'layui-layer-rim',
                area: ['80%', '31rem'], //宽高
                content: "<div><img src='" + imageUrl + "' style='width: 100%;height:30.5rem;'></div>",
                cancel : function () {
                    $.post('/index.php/ScanResponse/unlinkImage',{image:imageUrl});
                }
            });
        }
    }, 'json');
}


$(function(){
     $(".more").click(function(){
        $(".morePeople").show()
    })
    $(".morePeople").click(function(){
        $(".morePeople").hide()
    });
    $(".act-tag a").click(function(){
        var index=$(this).index();
            $(this).addClass("on").siblings().removeClass("on")
            $(".ty-detail-content>div").eq(index).show().siblings("div").hide()
        })

    //倒计时
    var time_current = (new Date()).valueOf();//获取当前时间
    $(function () {
        var dateTime = new Date();
        var difference = dateTime.getTime() - time_current;
        setInterval(function () {
            var endTime = new Date(parseInt($("#end_time").attr("attr_value")) * 1000);
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
                var str = "剩余时间 还剩" + myD + "天  " + myH + ":" + myM + ":" + myS;
            } else {
                var str = "已结束！";
            }
            $("#end_time").html(str);
        }, 100);
    });
})