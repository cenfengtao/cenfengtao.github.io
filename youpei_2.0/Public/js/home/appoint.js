
// var getTime=new Date().getTime()+86400*1000;
var myDate=new Date();
var nowMonth=myDate.getMonth()+1;
var nowYear=myDate.getFullYear();
var nowDay=myDate.getDate();
$("#classTime").val(nowYear+'-'+nowMonth+'-'+nowDay+' (点击可选择日期)')   
// 根据id调用月历插件
    var mySchedule = new Schedule({
        el: '#schedule-box',
        //date: '2018-9-20',
        clickCb: function (y,m,d) { 
            m=m.toString();
        if(m.length==1){
            m="0"+m;
        } 
        if(d.length==1){
            d="0"+d;
        } 
            $("#classTime").val(y+'-'+m+'-'+d)         
            $(".date").hide()
        }
    })
    $(function(){
        $("#classTime").click(function(event){
            event.stopPropagation()
            $("#classTime").blur()
            $(".date").show()
        })
        $(".closeBtn").click(function(){
            $(".date").hide()
        })
        var today=$(".today").html();
        var d=today.split("-")[2];
        var daylength=$(".dayStyle").length;
        for(var s=0; s<daylength; s++){
            var now=document.getElementsByClassName("dayStyle")[s].innerHTML;
            if(now==d){
                document.getElementsByClassName("dayStyle")[s].style.background="orange";
            }
        }

    })
    function GetRequest() {   
       var url = location.search; //获取url中"?"符后的字串   
       var theRequest = new Object();   
       if (url.indexOf("?") != -1) {   
          var str = url.substr(1);   
          strs = str.split("&");   
          for(var i = 0; i < strs.length; i ++) {   
             theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);   
          }   
       }   
       return theRequest;   
    } 
    var pro_id=GetRequest().pro_id;
    $.ajax({
        type:"get",
        url:"/index.php/product/ajaxAppoint",
        aynsc:true,
        dataType:"json",
        data:{
            pro_id:pro_id
        },
        success:function(data){
            if(data.status==1){
                var data=data.data;
                $("#courseName").val(data.product.title)
                $("#courseName").attr("disabled","disabled")
                $("#classAddress").val(data.product.city+data.product.area+data.product.address)
                $("#classAddress").attr("disabled","disabled")
            }
        }
    })
    $.ajax({
        type:"get",
        url:"/index.php/personal/ajaxPersonalData",
        aynsc:true,
        dataType:"json",
        data:{},
        success:function(data){
             if(data.data.user != null){
                $("#userName").val(data.data.user.baby_name);
                $("#age").val(data.data.user.baby_age);
                $("#mobile").val(data.data.user.number);
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
          // 宝宝昵称不能为空判断
        function testUserName(value){
          if(!value){
            alert("请先填写宝宝昵称。")
            return;
          }
        }
          // 宝宝年龄不能为空判断
        function testAge(value){
          if(!value){
            alert("请先填写宝宝年龄。")
            return;
          }
        }
        var bool=true;
        // 提交预约信息
    function submitfun() {
        var classTime=$("#classTime").val().split(" ")[0];
		var username =	$("#userName").val();
    		if(username==""){
    			alert("请先填写您的宝宝昵称。")
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
            if(bool){
                bool=false;
                $.ajax({
                    type:"post",
                    url:"/index.php/product/uploadAppoint",
                    aynsc:true,
                    dataType:"json",
                    data:{
                        pro_id:pro_id,
                        book_time:classTime,
                        name:username,
                        age:age,
                        mobile:mobile
                    },
                    success:function(data){
                        if(data.status == 1){
                            bool=true;
                            alert(data.message);
                            window.history.back()
                        }else{
                            bool=true;
                            alert(data.message);
                        }
                    }
                })
            }
        
	}