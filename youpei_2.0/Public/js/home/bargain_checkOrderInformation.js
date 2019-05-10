$(function(){
    $.ajax({
        type:"get",
        url:"/index.php/personal/personalDetails",
        aynsc:true,
        dataType:"json",
        data:{},
        success:function(data){
            if(data.status==1){
                var data=data.data;
                $("input[name='name']").val(data.info.name)
                $("input[name='mobile']").val(data.info.mobile)
                $("select[name='province'] option:selected").text(data.info.province)
                $("select[name='city'] option:selected").text(data.info.city)
                $("select[name='county'] option:selected").text(data.info.area)
                $("input[name='address']").val(data.info.address)
            }
        }
    })
})
//商品数量加操作
function add(obj) {
    var prd_num = obj.previousElementSibling;
    var num = prd_num.value;

    if (isNaN(num)) {
        num = 1;
    } else {
        num = parseInt(num);
    }

    num += 1;
    prd_num.value = num;
}
//商品数量改变的操作
function numChange(obj) {
    var num = obj.value;
    if (isNaN(num)) {
        num = 1;
    } else if (num == "") {
        num = 1;
    } else if (num < 1) {
        num = 1;
    }
    num = parseInt(num);
    obj.value = num;
}
//商品数量减操作
function reduce(obj) {
    var prd_num = obj.nextElementSibling;
    var num = prd_num.value;
    if (isNaN(num)) {
        num = 1;
    } else {
        num = parseInt(num);
    }

    num -= 1;
    if (num < 1) {
        num = 1
    }
    prd_num.value = num;
}

//城市级联的实现
$(function () {
    $.getScript("../../../../Public/js/home/city.js", function (data) {
        var result = JSON.parse(data);
        var cityArr = [];
        var countyArr = [];
        for (var i = 0; i < result.length; i++) {
            if (result[i].ProSort) {
                $(".province").append("<option value='" + result[i].ProID + "'>" + result[i].name + "</option>")//把数据拼接到.province下的option里
            } else if (result[i].CitySort) {
                cityArr.push(result[i]);//把符合的数据放入cityArr里
            } else if (result[i].DisSort == null) {
                countyArr.push(result[i]);//把符合的数据放入countyArr里
            }
        }
        $(".province").change(function () {
            var value = $(this).val();
            $(".city option").remove();
            $(".province option").eq(0).remove();
            for (var i = 0; i < cityArr.length; i++) {
                if (cityArr[i].ProID == value) {
                    $(".city").append("<option value='" + cityArr[i].CityID + "'>" + cityArr[i].name + "</option>"); //把数据拼接到.city下的option里
                }
            }

            var value2 = $(".city option").val();

            $(".county option").remove();
            for (var i = 0; i < countyArr.length; i++) {
                if (countyArr[i].CityID == value2)
                    $(".county").append("<option>" + countyArr[i].DisName + "</option>")
            }
        });
        $(".city").change(function () {
            var value = $(this).val();
            $(".county option").remove();
            for (var i = 0; i < countyArr.length; i++) {
                if (countyArr[i].CityID == value)
                    $(".county").append("<option>" + countyArr[i].DisName + "</option>")//把数据拼接到.county下的option里
            }
        })
    })
});

// 手机号码正则验证
var testTel = function (value) {
    var reg = /^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/;
    if (!reg.test(value)) {
        alert("你输入的手机号码不正确，请重新输入。")
        return false;
    }
};

function addProductN() {
    var n = parseFloat($('#amount').val());
    var num = parseInt(n) + 1;
    var price = parseFloat($('input[name="class-now-price"]').val());
    var total = price * num;
    if (num < 1 || num > 100) {
        return;
    }
    $('.price-off').html(total.toFixed(2));
    $('.price-full').html(total.toFixed(2));
    $('#amount').val(num);
}
function reduceProductN() {
    var n = parseFloat($('#amount').val());
    var num = parseInt(n) - 1;
    var price = parseFloat($('input[name="class-now-price"]').val());
    var total = price * num;
    if (num < 1) {
        return;
    }
    $('.price-off').html(total.toFixed(2));
    $('.price-full').html(total.toFixed(2));
    $('#amount').val(num);
    // $('.totalNum').html(num);
}

function confirmOrder() {
    var pro_id = getQueryString('pro_id');
    var key = getQueryString('key');
    var name = $('input[name="name"]').val();
    var mobile = $('input[name="mobile"]').val();
    var province = $('select[name="province"] option:selected').text();
    var city = $('select[name="city"] option:selected').text();
    var county = $('select[name="county"] option:selected').text();
    var address = $('input[name="address"]').val();
    var remark = $('#remark').val();
    if (name == '') {
        alert('填写联系人姓名');
        return;
    }
    if (mobile == '') {
        alert('填写联系人电话');
        return;
    }
    if (!/^1(3|4|5|7|8){1}\d{9}$/.test(mobile)) {
        alert("手机号码格式不正确，请重新填写");
        return;
    }
    if(amount < 1){
        alert("商品数量不正确，请选择至少一个商品");
        return;
    }
    if(address){
        var address_detail = province + city + county + address;
    } else {
        var address_detail = '';
    }
    return window.location.href = "/index.php/Product/bargainConfirmationInfo?pro_id=" + pro_id + "&amount=" + amount + "&name=" + name + "&mobile=" + mobile + "&message=" + remark + "&address=" + address_detail + "&key=" + key;

    if (province == "省份") {
        alert("请选择省份。")
        return
    }
    if (province == "市") {
        alert("请选择市。")
        return
    }
    if (province == "县/区") {
        alert("请选择县/区。")
        return
    }
    if (address == "") {
        alert("请填写详细地址。");
        return
    }
    var url = "/index.php/Orders/addOrder";
    var data = {
        pro_id: pro_id,
        amount: amount,
        name: name,
        mobile: mobile,
        message: remark,
        address: province + city + county + address,
        key: key
    };
    $.post(url, data, function (result) {
        if (result.status == 0) {
            return alert(result.msg);
        } else {
            window.location.href = "/index.php/WxPay/jsapiIndex?orderId=" + result.orderId;
        }
    }, 'json');
}

function agreement() {
    layer.open({
        type: 1,
        title: '服务协议',
        shadeClose: false,
        shade: 0.8,
        area: ['80%', '70%'],
        maxmin: true, //允许全屏最小化
        anim: 2, //0-6的动画形式，-1不开启
        content: "服务协议服务协议服务协议服务协议服务协议" //iframe的url
    });
}

function Refund_rule() {
    layer.open({
        type: 1,
        title: '退款规则',
        shadeClose: false,
        shade: 0.8,
        area: ['80%', '70%'],
        maxmin: true, //允许全屏最小化
        anim: 2, //0-6的动画形式，-1不开启
        content: "<ul><li>1、本套餐产品为特殊优惠活动，一经付款<strong>不支持退改</strong>；</li><li>2、成功购买本产品后，您将收到预约凭证&mdash;&mdash;电子码的短信消息（短信可能会被手机软件拦截）；</li><li>3、收到预约凭证后，请务必在指定时间内通过网站或电话进行预约，预约时所提交的姓名必须和入住人身份证上姓名一致；</li> <li>4、请务必提前3天或以上预约；周末房间紧张，建议提前至少2周预约；节假日房间务必第一时间预约，先到先得；</li><li>5、预约结果需以酒店或景区确认为准，如遇到预约日期无法预约，请和酒店或景区协商改期使用；</li><li>6、酒店或景区一旦确认核销电子码，此电子码立即失效，预约成功后不得更改或取消；电子码在没有核销前可转赠给亲友使用；</li><li>7、本产品截止前未支付款项则视为弃权，过期未预约或未使用则自动作废；</li><li>8、本产品为特价促销产品，不提供发票；</li> <li>9、最终解释权归<strong>优培</strong>官方所有。</li></ul>" //iframe的url
    });
}

function getClassTable() {
    var pro_id = getQueryString('pro_id');
    window.location.href = "/index.php/Product/getClassTable?pro_id=" + pro_id;
}

function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}