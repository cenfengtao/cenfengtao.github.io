 $(window).load(function () {
        setTimeout(function () {
            $('.kap-bottom').show();
        }, 2000)
    });
    function getProduct(id) {
        window.location.href = "/index.php/Groups/getGroup?product_id=" + id;
    }
//    选择机构
    function chooseOrg() {
        var token = $('select[name="org_token"] option:selected').val();
        if (token == 0) {
            $('select[name="group_id"]').empty();
            return false;
        }
        var url = "/index.php/Groups/getGroupsByOrgId";
        $.post(url, {token: token}, function (result) {
            if (result.status == 0) {
                return alert(result.message);
            }
            if (result.status == 1) {
                $('select[name="group_id"]').empty();
                $.each(result.data, function (idx, obj) {
                    $('select[name="group_id"]').append("<option value='"+obj.id+"'>"+obj.title+"</option>");
                });
                chooseProduct();
            }
        }, 'json');
    }
//    选择课程
    function chooseProduct() {
        var id = $('select[name="group_id"] option:selected').val();
        var url = "/index.php/Groups/getGroupById";
        $.post(url,{id:id},function (result) {
            if (result.status == 0){
                return alert(result.message);
            } else {
                $('.classTime').empty();
                $.each(result.data['class_time'],function (idx,obj) {
                    $('.classTime').append("<li>"+obj.class_time_day+" "+obj.class_start_hour+" - "+obj.class_end_hour+"</li>");
                });
                $('.groupTime').html(result.data['group_time']['start_time']+" - "+result.data['group_time']['end_time']);
                $('.minPeople').html(result.data['min_people']);
            }
        },'json')
    }
    //发起组团
    function launchGroup() {
        var group_id = $('select[name="group_id"] option:selected').val();
        var url = "/index.php/Groups/launchGroup";
        $.post(url,{group_id:group_id},function (result) {
            if (result.status == 0){
                return alert(result.message);
            } else {
                alert(result.message);
                setTimeout(function () {
                    window.location.href = "/index.php/Groups/index";
                },1000)
            }
        },'json')
    }

    function submitHope() {
        //获取上课时间
        var class_time = new Array();
        var add_time_length = $('select[name="class_time_day"] option:selected').length;
        // var checkedLength=$("input[name='message']:checked").length;
        // for(var j=0; j<checkedLength; j++){
        //     class_time[j] = new Array();
        //     class_time[j][0]=$("input[name='message']:checked").eq(j).parent().parent().find("select[name='class_time_day'] option:selected").val();
        //     class_time[j][1]=$("input[name='message']:checked").eq(j).parent().parent().find("input[name='class_start_hour']").val();
        //     class_time[j][2]=$("input[name='message']:checked").eq(j).parent().parent().find("input[name='class_end_hour']").val();           
        // }

        for(var i = 0;i<=add_time_length-1;i++){
            class_time[i] = new Array();
            class_time[i][0] = $(".class-time"+(i+1)+" select[name='class_time_day'] option:selected").val();
            class_time[i][1] = $(".class-time"+(i+1)+" input[name='class_start_hour']").val();
            class_time[i][2] = $(".class-time"+(i+1)+" input[name='class_end_hour']").val();
        }

        var content = $("#hope-content").val();
        var checked = $("input[name='message']:checked").length;
        var mobile = $("input[name='mobile']").val();
        if(!content || content == ''){
             alert('请写出您的愿望。');
             return
        }
        // console.log(checked)
        // if(checked==0){
        //     alert("请勾选上课时间。")
        // }
        // if (!/^1(3|4|5|7|8){1}\d{9}$/.test(mobile)) {
        //     return alert("手机号码格式不正确，请重新填写");
        // }
        var url = "/index.php/Groups/addHope";
        $.post(url,{class_time:class_time,content:content},function (result) {
            if(result.status == 1){
                alert(result.message);
                setTimeout(function(){
                    window.location.href = "/index.php/Groups/index/current/2";
                },1500);
            } else {
                alert(result.message);
            }
        },'json')
    }
    // $("#tab-create").click(function () {
    //     $("#tab-hope").removeClass();
    //     $("#tab-create").addClass('active');
    //     $('.hope-order').hide();
    //     $('.auto-create').show();
    // });
    //
    // $("#tab-hope").click(function () {
    //     $("#tab-create").removeClass();
    //     $("#tab-hope").addClass('active');
    //     $('.hope-order').show();
    //     $('.auto-create').hide();
    // });

// 底部半圆的切换
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
        },1000)
    });