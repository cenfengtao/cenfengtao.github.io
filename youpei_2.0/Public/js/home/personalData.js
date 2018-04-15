$.ajax({
    type:"get",
    url:"/index.php/personal/ajaxPersonalData",
    aynsc:true,
    dataType:"json",
    data:{
    },
    success:function(data){
        if(data.data.user != null){
            $("#id").val(data.data.user.id);
            $("#userName").val(data.data.user.baby_name);
            $("#age").val(data.data.user.baby_age);
            $("#mobile").val(data.data.number);
            $("#shouhuoName").val(data.data.user.name);
            $("#shouhuoPhone").val(data.data.user.mobile);
            if(data.data.user.family_province != ''){
                $(".province option:selected").text(data.data.user.family_province);
                $(".city option:selected").text(data.data.user.family_city);
                $(".county option:selected").text(data.data.user.family_area);
            }
            $(".detail").val(data.data.user.family_address);
            $(".provinces option:selected").text(data.data.user.province);
            $(".citys option:selected").text(data.data.user.city);
            $(".countys option:selected").text(data.data.user.area);
            $(".details").val(data.data.user.address);
            $(".submit").show()
            $(".submit").text('修改信息')
        }else{
            $(".submit").show()
        }
    }
})
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
})
//城市级联的实现
$(function () {
$.getScript("../../../../Public/js/home/city.js", function (data) {
    var result = JSON.parse(data);
    var cityArr = [];
    var countyArr = [];
    for (var i = 0; i < result.length; i++) {
        if (result[i].ProSort) {
            $(".provinces").append("<option value='" + result[i].ProID + "'>" + result[i].name + "</option>")//把数据拼接到.province下的option里
        } else if (result[i].CitySort) {
            cityArr.push(result[i]);//把符合的数据放入cityArr里
        } else if (result[i].DisSort == null) {
            countyArr.push(result[i]);//把符合的数据放入countyArr里
        }
    }
    $(".provinces").change(function () {
        var value = $(this).val();
        $(".citys option").remove();
        $(".provinces option").eq(0).remove();
        for (var i = 0; i < cityArr.length; i++) {
            if (cityArr[i].ProID == value) {
                $(".citys").append("<option value='" + cityArr[i].CityID + "'>" + cityArr[i].name + "</option>"); //把数据拼接到.city下的option里
            }
        }

        var value2 = $(".citys option").val();

        $(".countys option").remove();
        for (var i = 0; i < countyArr.length; i++) {
            if (countyArr[i].CityID == value2)
                $(".countys").append("<option>" + countyArr[i].DisName + "</option>")
        }
    });
    $(".citys").change(function () {
        var value = $(this).val();
        $(".countys option").remove();
        for (var i = 0; i < countyArr.length; i++) {
            if (countyArr[i].CityID == value)
                $(".countys").append("<option>" + countyArr[i].DisName + "</option>")//把数据拼接到.county下的option里
        }
    })
})
})
//      	    宝宝名字不能为空判断
    function testUsername(value){
      if(!value){
        alert("请先填写您的宝宝名字。")
        return;
      }
    }
//                  宝宝年龄不能为空判断
    function testAge(value){
      if(!value){
        alert("请先填写您的宝宝年龄。")
        return;
      }
    }
    // 手机号码正则验证
    function testTel(value){
        var reg=/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/;
        if(!reg.test(value)){
            alert("你输入的手机号码不正确，请重新输入。")
            return false;
        }
    }  
     // 家庭住址不能为空判断
   function testFamilyAdd(value){
      if(!value){
        alert("请先填写您的家庭住址。")
        return;
      }
    }
      // 收货人不能为空判断
    function testLogistical(value){
      if(!value){
        alert("请先填写收货人名字。")
        return;
      }
    }
    // 收货电话正则验证
    function testMObile(value){
        var reg=/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/;
        if(!reg.test(value)){
            alert("你输入的手机号码不正确，请重新输入。")
            return false;
        }
    }  
     // 收货地址不能为空判断
    function testLogsticalAdd(value){
      if(!value){
        alert("请先填写您的收货住址。")
        return;
      }
    }


function getCode(){
        var url = "/index.php/personal/getCode";
        var mobile = $("input[name='mobile']").val();
        var ret = /^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/;
        if (!ret.test(mobile)) {
            return alert('手机格式不正确');
        }else{
            times()
             $.post(url, {mobile: mobile}, function (result) {
                if (result.status == 0) {
                    return alert(result.message);
                }
            }, 'json');
        }    
    }

var time=60;
function times(){
     if(time==0){
        $("#code").removeAttr("disabled")
        $("#code").html("接受验证码")
        time=60
    }else{
        $("#code").html(time+'s后可重新发送')
        $("#code").attr("disabled","disabled")
        time--
        setTimeout(function(){
            times()
        },1000)
    }
}
var bool=true;
function submitfun() {
	var username =	$("#userName").val();
		if(username==""){
			alert("请先填写您的宝宝名字。")
            return false;
		}
	var age=$("#age").val();
	if(age==""){
		 alert("请先填写您的宝宝年龄。")
        return;
	}
	var mobile=$("#mobile").val();
	var reg=/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/;
        if(!reg.test(mobile)){
            alert("你输入的手机号码不正确，请重新输入。")
            return false;
        }
    var code = $("input[name='code']").val();
    if(code == ""){
         alert("请输入验证码");
        return
    }
	var address=$("#address").val();
	if(address==""){
        alert("请先填写您的家庭住址。")
        return;
	}
	var shouhuoName=$("#shouhuoName").val();
	if(shouhuoName==""){
		 alert("请先填写收货人名字。")
        return;
	}
	
	var shouhuoPhone=$("#shouhuoPhone").val();
	var reg=/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/;
        if(!reg.test(shouhuoPhone)){
            alert("你输入的手机号码不正确，请重新输入。")
            return false;
        }
    var id=$("#id").val();
    var detail = $(".detail").val();
    var details=$(".details").val();
    if(id != ''){
        if(detail == ""){
            alert("请先填写完整您的家庭住址。")
            return;
        }

        if(details == ""){
            alert("请先填写完整您的收货住址。")
            return;
        }
        if($(".province option:selected").val() > 0){
            var province=$(".province option:selected").text();
            var city=$(".city option:selected").text();
            var county=$(".county option:selected").text();
        }else{
            province=null;
            city=null;
            county=null;
        }
        if($(".provinces option:selected").val() > 0){
            var provinces=$(".provinces option:selected").text();
            var citys=$(".citys option:selected").text();
            var countys=$(".countys option:selected").text();
        }else{
            provinces=null;
            citys=null;
            countys=null;
        }
    }else{
        var province=$(".province option:selected").text();
        var city=$(".city option:selected").text();
        var county=$(".county option:selected").text();
        var provinces=$(".provinces option:selected").text();
        var citys=$(".citys option:selected").text();
        var countys=$(".countys option:selected").text();
        if(province==null || city == null || county == null || detail == ""){
            alert("请先填写完整您的家庭住址。")
            return;
        }

        if(provinces==null || citys == null || countys == null || details == ""){
            alert("请先填写完整您的收货住址。")
            return;
        }
    }

    if(bool){
        bool=false;
        $.ajax({
            type:"post",
            url:"/index.php/personal/uploadPersonalData",
            aynsc:true,
            dataType:"json",
            data:{
                id:id,
                baby_name:username,
                baby_age:age,
                number:mobile,
                family_province:province,
                family_city:city,
                family_area:county,
                family_address:detail,
                name:shouhuoName,
                mobile:shouhuoPhone,
                province:provinces,
                city:citys,
                area:countys,
                address:details,
                code:code
            },
            success:function(data){
                if(data.status == 1){
                    bool=true;
                    alert(data.message);
                    setTimeout(function(){
                        window.location.reload();
                    },1000);
                }else{
                    bool=true;
                    alert(data.message);
                }
            }
        })
    }
}