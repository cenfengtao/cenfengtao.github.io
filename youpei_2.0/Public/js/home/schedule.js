 // 时分秒插件
// laydate.render({
//   elem: '#test1',
//   type:"time"
// });
// laydate.render({
//   elem: '#test2',
//   type:"time"
// });
$(function(){
    setTimeout(function(){
        var today=$(".today").html();
        var year=today.split("-")[0];
        var month=today.split("-")[1];
        var y=today.split("-")[0];
        var m=today.split("-")[1];
        var d=today.split("-")[2];
    $(".time").html(y+'年'+m+'月'+d+'日')
    var daylength=$(".dayStyle").length;
    // 遍历日历表，给当前时间的表格加上背景颜色
    for(var s=0; s<daylength; s++){
        var now=document.getElementsByClassName("dayStyle")[s].innerHTML;
        if(now==parseInt(d)){
            document.getElementsByClassName("dayStyle")[s].style.background="orange";
            $(".uploadInfo").show()
            $(".uploadClass").attr("attr_year",y)
            $(".uploadClass").attr("attr_month",m)
            $(".uploadClass").attr("attr_day",d)
        }
    }
    // 请求需上课的数据
    $.ajax({
        type:"post",
        url:"/index.php/personal/ajaxSchedule",
        aynsc:true,
        data:{
            year:year,
            month:month
        },
        dataType:"json",
        success:function(data){
           
            if(data.status==1){
                var data=data.data;
                if(data.mobile==null){
                    $(".tellBox").show()
                }else{
                    $("input[name='mobile']").val(data.mobile)
                }
                if(data.babyName==null||data.babyName==""){
                }else{
                    $("input[name='username']").val(data.babyName)
                }
                window.sessionStorage.setItem("data",data)
                // 获取数据遍历进日历表进行判断给上背景颜色
                var daylength=$(".dayStyle").length;
                var day=$(".today").html().split("-")[2];
                for( var n in data.list){
                    var days=n.split("-")[2];
                    days=parseInt(days)
                    for(var j=0; j<daylength; j++){
                        var td=document.getElementsByClassName("dayStyle")[j].innerHTML;
                        td=parseInt(td)
                        if(days==td){
                            document.getElementsByTagName("i")[j].setAttribute('class', 'red')
                              if(days<day){
                                document.getElementsByTagName("i")[j].setAttribute('class', 'gray')
                            }
                        }

                    }
                }
                // 遍历当天的数据进行呈现
                if(data.list[today]){
                    for(var i=0; i<data.list[today].length; i++){
                    $(".arrangeList").append(
                                 '<li>'+
                                    '<div class="timeLong">'+
                                        '<span>'+data.list[today][i].start_hour_time+'</span>'+
                                        '<span>—</span>'+
                                        '<span>'+data.list[today][i].end_hour_time+'</span>'+
                                        '<a href="/index.php/personal/leave.html?id='+data.list[today][i].id+'" class="leave">请假</a>'+
                                    '</div>'+
                                    '<div class="classText">'+
                                        '<h3>'+data.list[today][i].class_title+'</h3>'+
                                        '<p class="teacher">上课老师：<span>'+data.list[today][i].teacher+'</span></p>'+
                                        '<p class="classNum">当前课次：<span>'+data.list[today][i].class_hour+'</span></p>'+
                                        '<p class="address">上课地址：<span>'+data.list[today][i].city+''+data.list[today][i].area+''+data.list[today][i].address+'</span><a href="/index.php/Map/address?class_id='+data.list[today][i].id+'"><img src="../../../../Public/images/scheduleAddress.png" alt=""></a></p>'+
                                         '<p class="phone">联系电话：<span>'+data.list[today][i].tel+'</span><a href="tel://'+data.list[today][i].tel+'"><img src="../../../../Public/images/phone.png" alt=""></a></p>'+
                                    '</div>'+
                                '</li>'
                            )
                    // 根据多给状态，对请假按钮进行隐藏
                            if(data.list[today][i].upload_type==2){
                                $(".arrangeList li").eq(i).find(".leave").hide()
                            }
                            if(data.list[today][i].leave_status==1){
                                if(data.list[today][i].is_read==1){
                                    $(".arrangeList li").eq(i).find(".leave").text("请假中").attr("href","javascript:void(0);")
                                }else{
                                    $(".arrangeList li").eq(i).find(".leave").text("已请假").attr("href","javascript:void(0);")
                                }
                                
                            }
                    }
                }else{
                    // 当没有课时进行提示
                    $(".arrangeList").html("")
                    $(".arrangeList").append(
                            '<li style="margin-top:0.5rem;color:white;text-align:center;font-size:0.5rem;color:white;">亲，您今天没有课哦 !</li>'
                        )
                }
                // 根据classText的高度赋给timelong高度
                var arrangeList=$(".arrangeList li").length;
                    for(var a=0; a<arrangeList; a++){
                        var textHeight=$(".arrangeList li").eq(a).find(".classText").height()
                        $(".arrangeList li").eq(a).find(".timeLong").css("height",textHeight)
                    }
                    // 点击表格遍历数据进去
                $(".schedule-bd li").bind("click",function(event){
                    var myDate=new Date();
                    var nowMonth=myDate.getMonth()+1;
                    var nowYear=myDate.getFullYear();
                    var nowDay=myDate.getDate();
                    $(".arrangeList").html("")
                    event.stopPropagation();
                    var d=$(this).find("span").html();
                   
                    if(m.length==1){
                        m="0"+m;
                    }
                    if(d.length==1){
                        d='0'+d
                    }
                    // 点击显示当前状态表格颜色
                    if(d!=""){
                        $(".time").html(y+'年'+m+'月'+d+'日')
                        $(".today").html(y+'-'+m+'-'+d)
                        var today=$(".today").html()
                         $(this).find("span").css("background","orange").parent().siblings().find("span").css("background","white")
                    }else{
                        $(this).find("span").css("background","white")
                        var today=$(".today").html()
                    }
                    if(data.list[today]){
                        for(var i=0; i<data.list[today].length; i++){
                            $(".arrangeList").append(
                                     '<li>'+
                                        '<div class="timeLong">'+
                                            '<span>'+data.list[today][i].start_hour_time+'</span>'+
                                            '<span>—</span>'+
                                            '<span>'+data.list[today][i].end_hour_time+'</span>'+
                                            '<a href="/index.php/personal/leave.html?id='+data.list[today][i].id+'" class="leave">请假</a>'+
                                        '</div>'+
                                        '<div class="classText">'+
                                            '<h3>'+data.list[today][i].class_title+'</h3>'+
                                            '<p class="teacher">上课老师：<span>'+data.list[today][i].teacher+'</span></p>'+
                                            '<p class="classNum">当前课次：<span>'+data.list[today][i].class_hour+'</span></p>'+
                                            '<p class="address"><label>上课地址：</label><span>'+data.list[today][i].city+''+data.list[today][i].area+''+data.list[today][i].address+'</span><a href="/index.php/Map/address?class_id='+data.list[today][i].id+'"><img src="../../../../Public/images/scheduleAddress.png" alt=""></a></p>'+
                                             '<p class="phone">联系电话：<span>'+data.list[today][i].tel+'</span><a href="tel://'+data.list[today][i].tel+'"><img src="../../../../Public/images/phone.png" alt=""></a></p>'+
                                        '</div>'+
                                    '</li>'
                                )
                             if(data.list[today][i].upload_type==2){
                                $(".arrangeList li").eq(i).find(".leave").hide()
                            }
                            if(data.list[today][i].leave_status==1){
                                if(data.list[today][i].is_read==1){
                                    $(".arrangeList li").eq(i).find(".leave").text("请假中").attr("href","javascript:void(0);")
                                }else{
                                    $(".arrangeList li").eq(i).find(".leave").text("已请假").attr("href","javascript:void(0);")
                                }
                            }
                            }
                    }else{
                        $(".arrangeList").html("")
                        $(".arrangeList").append(
                                '<li style="margin-top:0.5rem;color:white;text-align:center;font-size:0.5rem;color:white;">亲，您今天没有课哦 !</li>'
                            )
                    } 
                    // 显示当天的数据
                    if((y==nowYear&nowMonth<m)||(y>nowYear)||(y==nowYear&nowMonth==m&(d>nowDay||d==nowDay))){
                        $(".uploadInfo").show()
                        $(".uploadClass").attr("attr_year",y)
                        $(".uploadClass").attr("attr_month",m)
                        $(".uploadClass").attr("attr_day",d)
                    }else{
                        $(".uploadInfo").hide()
                        $(".leave").hide()
                    }
                    var arrangeList=$(".arrangeList li").length;
                    for(var a=0; a<arrangeList; a++){
                        var textHeight=$(".arrangeList li").eq(a).find(".classText").height()
                        $(".arrangeList li").eq(a).find(".timeLong").css("height",textHeight)
                    }

                })
                
            }  

        }
    })
    },1000)
})
// 根据id调用月历插件
  var mySchedule = new Schedule({
el: '#schedule-box',
//date: '2018-9-20',
clickCb: function (y,m,d) {           
},
nextMonthCb: function (y,m,d) {
            var today=$(".today").html();
            var year=today.split("-")[0];
            var month=today.split("-")[1];
            var y=today.split("-")[0];
            var m=today.split("-")[1];
            var d=today.split("-")[2];
            if(m.length==1){
                m="0"+m;
            }
            if(d.length==1){
                d="0"+d;
            }
            $(".time").html(y+'年'+m+'月'+d+'日')
            $(".arrangeList").html("")
            $(".uploadInfo").hide()
            var myDate=new Date();
            var nowMonth=myDate.getMonth()+1;
            var nowYear=myDate.getFullYear();
            var nowDay=myDate.getDate();
            var daylength=$(".dayStyle").length;
            for(var s=0; s<daylength; s++){
                var now=document.getElementsByClassName("dayStyle")[s].innerHTML;
                if(now==nowDay&y==nowYear&m==nowMonth){
                    document.getElementsByClassName("dayStyle")[s].style.background="orange";
                    $(".uploadInfo").show()
                }
            }
            $.ajax({
                type:"post",
                url:"/index.php/personal/ajaxSchedule",
                aynsc:true,
                data:{
                    year:year,
                    month:month
                },
                dataType:"json",
                success:function(data){
                    if(data.status==1){
                      bool=true;
                        var data=data.data;
                        var daylength=$(".dayStyle").length;
                        var day=$(".today").html().split("-")[2];
                        for( var n in data.list){
                            var days=n.split("-")[2];
                            days=parseInt(days)
                            for(var j=0; j<daylength; j++){
                                var td=document.getElementsByClassName("dayStyle")[j].innerHTML;
                                td=parseInt(td)
                                if(days==td){     
                                    m=parseInt(m)
                                    if((y==nowYear&nowMonth<m)||(y>nowYear)||(y==nowYear&nowMonth==m&(days>nowDay||days==nowDay))){
                                      document.getElementsByTagName("i")[j].setAttribute('class', 'red')
                                    }else{
                                        document.getElementsByTagName("i")[j].setAttribute('class', 'gray')
                                    }
                                }


                            }
                        }
                        if(data.list[today]){
                            for(var i=0; i<data.list[today].length; i++){
                            $(".arrangeList").append(
                                         '<li>'+
                                            '<div class="timeLong">'+
                                                '<span>'+data.list[today][i].start_hour_time+'</span>'+
                                                '<span>—</span>'+
                                                '<span>'+data.list[today][i].end_hour_time+'</span>'+
                                                '<a href="/index.php/personal/leave.html?id='+data.list[today][i].id+'" class="leave">请假</a>'+
                                            '</div>'+
                                            '<div class="classText">'+
                                                '<h3>'+data.list[today][i].class_title+'</h3>'+
                                                '<p class="teacher">上课老师：<span>'+data.list[today][i].teacher+'</span></p>'+
                                                '<p class="classNum">当前课次：<span>'+data.list[today][i].class_hour+'</span></p>'+
                                                '<p class="address">上课地址：<span>'+data.list[today][i].city+''+data.list[today][i].area+''+data.list[today][i].address+'</span><a href="/index.php/Map/address?class_id='+data.list[today][i].id+'"><img src="../../../../Public/images/scheduleAddress.png" alt=""></a></p>'+
                                                 '<p class="phone">联系电话：<span>'+data.list[today][i].tel+'</span><a href="tel://'+data.list[today][i].tel+'"><img src="../../../../Public/images/phone.png" alt=""></a></p>'+
                                            '</div>'+
                                        '</li>'
                                    )
                             if(data.list[today][i].upload_type==2){
                                $(".arrangeList li").eq(i).find(".leave").hide()
                            }
                            if(data.list[today][i].leave_status==1){
                                 if(data.list[today][i].is_read==1){
                                    $(".arrangeList li").eq(i).find(".leave").text("请假中").attr("href","javascript:void(0);")
                                }else{
                                    $(".arrangeList li").eq(i).find(".leave").text("已请假").attr("href","javascript:void(0);")
                                }
                            }
                            }
                        }else{
                            $(".arrangeList").html("")
                            $(".arrangeList").append(
                                    '<li style="margin-top:0.5rem;color:white;text-align:center;font-size:0.5rem;color:white;">亲，您今天没有课哦 !</li>'
                                )
                        }
                        var arrangeList=$(".arrangeList li").length;
                        for(var a=0; a<arrangeList; a++){
                            var textHeight=$(".arrangeList li").eq(a).find(".classText").height()
                            $(".arrangeList li").eq(a).find(".timeLong").css("height",textHeight)
                        }
                        if((y==nowYear&nowMonth<m)||(y>nowYear)||(y==nowYear&nowMonth==m&(d>nowDay||d==nowDay))){
                        }else{
                            $(".leave").hide()
                        }
                        $(".schedule-bd li").bind("click",function(event){
                            $(".arrangeList").html("")
                            event.stopPropagation();
                            var d=$(this).find("span").html();
                            var today=$(".today").html()
                            var m=today.split("-")[1];
                            if(m.length==1){
                                m="0"+m;
                            }
                            if(d.length==1){
                                d='0'+d
                            }
                            if(d!=""){
                                $(".time").html(y+'年'+m+'月'+d+'日')
                                $(".today").html(y+'-'+m+'-'+d)
                                today=$(".today").html()
                                $(this).find("span").css("background","orange").parent().siblings().find("span").css("background","white")
                                }else{
                                    $(this).find("span").css("background","white")
                                    today=$(".today").html()
                                }
                            if(data.list[today]){
                                for(var i=0; i<data.list[today].length; i++){
                                    $(".arrangeList").append(
                                             '<li>'+
                                                '<div class="timeLong">'+
                                                    '<span>'+data.list[today][i].start_hour_time+'</span>'+
                                                    '<span>—</span>'+
                                                    '<span>'+data.list[today][i].end_hour_time+'</span>'+
                                                    '<a href="/index.php/personal/leave.html?id='+data.list[today][i].id+'" class="leave">请假</a>'+
                                                '</div>'+
                                                '<div class="classText">'+
                                                    '<h3>'+data.list[today][i].class_title+'</h3>'+
                                                    '<p class="teacher">上课老师：<span>'+data.list[today][i].teacher+'</span></p>'+
                                                    '<p class="classNum">当前课次：<span>'+data.list[today][i].class_hour+'</span></p>'+
                                                    '<p class="address">上课地址：<span>'+data.list[today][i].city+''+data.list[today][i].area+''+data.list[today][i].address+'</span><a href="/index.php/Map/address?class_id='+data.list[today][i].id+'"><img src="../../../../Public/images/scheduleAddress.png" alt=""></a></p>'+
                                                     '<p class="phone">联系电话：<span>'+data.list[today][i].tel+'</span><a href="tel://'+data.list[today][i].tel+'"><img src="../../../../Public/images/phone.png" alt=""></a></p>'+
                                                '</div>'+
                                            '</li>'
                                        )
                                     if(data.list[today][i].upload_type==2){
                                        $(".arrangeList li").eq(i).find(".leave").hide()
                                    }
                                    if(data.list[today][i].leave_status==1){
                                         if(data.list[today][i].is_read==1){
                                            $(".arrangeList li").eq(i).find(".leave").text("请假中").attr("href","javascript:void(0);")
                                        }else{
                                            $(".arrangeList li").eq(i).find(".leave").text("已请假").attr("href","javascript:void(0);")
                                        }
                                    }
                                    }
                            }else{
                                $(".arrangeList").html("")
                                $(".arrangeList").append(
                                        '<li style="margin-top:0.5rem;color:white;text-align:center;font-size:0.5rem;color:white;">亲，您今天没有课哦 !</li>'
                                    )
                            } 
                             if((y==nowYear&nowMonth<m)||(y>nowYear)||(y==nowYear&nowMonth==m&(d>nowDay||d==nowDay))){
                                    $(".uploadInfo").show()
                                    $(".uploadClass").attr("attr_year",y)
                                    $(".uploadClass").attr("attr_month",m)
                                    $(".uploadClass").attr("attr_day",d)
                                }else{
                                    $(".uploadInfo").hide()
                                    $(".leave").hide()
                                }
                            var arrangeList=$(".arrangeList li").length;
                            for(var a=0; a<arrangeList; a++){
                                var textHeight=$(".arrangeList li").eq(a).find(".classText").height()
                                $(".arrangeList li").eq(a).find(".timeLong").css("height",textHeight)
                            }

                        })
                    }
                     
                }
            })
},
nextYeayCb: function (y,m,d) {
  
},
prevMonthCb: function (y,m,d) {
            var today=$(".today").html();
            var year=today.split("-")[0];
            var month=today.split("-")[1];
            var y=today.split("-")[0];
            var m=today.split("-")[1];
            var d=today.split("-")[2];
            if(m.length==1){
                m="0"+m;
            }
            if(d.length==1){
                d="0"+d;
            }
            $(".time").html(y+'年'+m+'月'+d+'日')
            $(".arrangeList").html("")
            $(".uploadInfo").hide()
            var myDate=new Date();
            var nowMonth=myDate.getMonth()+1;
            var nowYear=myDate.getFullYear();
            var nowDay=myDate.getDate();
            var daylength=$(".dayStyle").length;
            for(var s=0; s<daylength; s++){
                var now=document.getElementsByClassName("dayStyle")[s].innerHTML;
                if(now==nowDay&y==nowYear&m==nowMonth){
                    document.getElementsByClassName("dayStyle")[s].style.background="orange";
                    $(".uploadInfo").show()
                }
            }
            $.ajax({
                type:"post",
                url:"/index.php/personal/ajaxSchedule",
                aynsc:true,
                data:{
                    year:year,
                    month:month
                },
                dataType:"json",
                success:function(data){
                    if(data.status==1){
                      trigger=true;
                        var data=data.data;
                        var daylength=$(".dayStyle").length;
                        var day=$(".today").html().split("-")[2];
                        for( var n in data.list){
                            var days=n.split("-")[2];
                            days=parseInt(days)
                            for(var j=0; j<daylength; j++){
                                var td=document.getElementsByClassName("dayStyle")[j].innerHTML;
                                td=parseInt(td)
                                if(days==td){
                                     m=parseInt(m)
                                    if((y==nowYear&nowMonth<m)||(y>nowYear)||(y==nowYear&nowMonth==m&(days>nowDay||days==nowDay))){
                                      document.getElementsByTagName("i")[j].setAttribute('class', 'red')
                                    }else{
                                        document.getElementsByTagName("i")[j].setAttribute('class', 'gray')
                                    }
                                }

                            }
                        }
                        if(data.list[today]){
                            for(var i=0; i<data.list[today].length; i++){
                            $(".arrangeList").append(
                                         '<li>'+
                                            '<div class="timeLong">'+
                                                '<span>'+data.list[today][i].start_hour_time+'</span>'+
                                                '<span>—</span>'+
                                                '<span>'+data.list[today][i].end_hour_time+'</span>'+
                                                '<a href="/index.php/personal/leave.html?id='+data.list[today][i].id+'" class="leave">请假</a>'+
                                            '</div>'+
                                            '<div class="classText">'+
                                                '<h3>'+data.list[today][i].class_title+'</h3>'+
                                                '<p class="teacher">上课老师：<span>'+data.list[today][i].teacher+'</span></p>'+
                                                '<p class="classNum">当前课次：<span>'+data.list[today][i].class_hour+'</span></p>'+
                                                '<p class="address">上课地址：<span>'+data.list[today][i].city+''+data.list[today][i].area+''+data.list[today][i].address+'</span><a href="/index.php/Map/address?class_id='+data.list[today][i].id+'"><img src="../../../../Public/images/scheduleAddress.png" alt=""></a></p>'+
                                                 '<p class="phone">联系电话：<span>'+data.list[today][i].tel+'</span><a href="tel://'+data.list[today][i].tel+'"><img src="../../../../Public/images/phone.png" alt=""></a></p>'+
                                            '</div>'+
                                        '</li>'
                                    )
                                 if(data.list[today][i].upload_type==2){
                                    $(".arrangeList li").eq(i).find(".leave").hide()
                                }
                                if(data.list[today][i].leave_status==1){
                                     if(data.list[today][i].is_read==1){
                                        $(".arrangeList li").eq(i).find(".leave").text("请假中").attr("href","javascript:void(0);")
                                    }else{
                                        $(".arrangeList li").eq(i).find(".leave").text("已请假").attr("href","javascript:void(0);")
                                    }
                                }
                            }
                        }else{
                            $(".arrangeList").html("")
                            $(".arrangeList").append(
                                    '<li style="margin-top:0.5rem;color:white;text-align:center;font-size:0.5rem;color:white;">亲，您今天没有课哦 !</li>'
                                )
                        }
                        var arrangeList=$(".arrangeList li").length;
                        for(var a=0; a<arrangeList; a++){
                            var textHeight=$(".arrangeList li").eq(a).find(".classText").height()
                            $(".arrangeList li").eq(a).find(".timeLong").css("height",textHeight)
                        }
                        if((y==nowYear&nowMonth<m)||(y>nowYear)||(y==nowYear&nowMonth==m&(d>nowDay||d==nowDay))){
                        }else{
                            $(".leave").hide()
                        }
                        
                    }
                     $(".schedule-bd li").bind("click",function(event){
                        $(".arrangeList").html("")
                        event.stopPropagation();
                        var today=$(".today").html()
                        var m=today.split("-")[1];
                        var d=$(this).find("span").html();
                        if(m.length==1){
                            m="0"+m;
                        }
                        if(d.length==1){
                            d='0'+d
                        }
                        if(d!=""){
                            $(".time").html(y+'年'+m+'月'+d+'日')
                            $(".today").html(y+'-'+m+'-'+d)
                           today=$(".today").html()
                         $(this).find("span").css("background","orange").parent().siblings().find("span").css("background","white")
                            }else{
                                $(this).find("span").css("background","white")
                                today=$(".today").html()
                            }
                       

                        if(data.list[today]){
                            for(var i=0; i<data.list[today].length; i++){
                                $(".arrangeList").append(
                                         '<li>'+
                                            '<div class="timeLong">'+
                                                '<span>'+data.list[today][i].start_hour_time+'</span>'+
                                                '<span>—</span>'+
                                                '<span>'+data.list[today][i].end_hour_time+'</span>'+
                                                '<a href="/index.php/personal/leave.html?id='+data.list[today][i].id+'" class="leave">请假</a>'+
                                            '</div>'+
                                            '<div class="classText">'+
                                                '<h3>'+data.list[today][i].class_title+'</h3>'+
                                                '<p class="teacher">上课老师：<span>'+data.list[today][i].teacher+'</span></p>'+
                                                '<p class="classNum">当前课次：<span>'+data.list[today][i].class_hour+'</span></p>'+
                                                '<p class="address">上课地址：<span>'+data.list[today][i].city+''+data.list[today][i].area+''+data.list[today][i].address+'</span><a href="/index.php/Map/address?class_id='+data.list[today][i].id+'"><img src="../../../../Public/images/scheduleAddress.png" alt=""></a></p>'+
                                                '<p class="phone">联系电话：<a href="tel://'+data.list[today][i].tel+'"><span>'+data.list[today][i].tel+'</span><img src="../../../../Public/images/phone.png" alt=""></a></p>'+
                                            '</div>'+
                                        '</li>'
                                    )
                                     if(data.list[today][i].upload_type==2){
                                        $(".arrangeList li").eq(i).find(".leave").hide()
                                    }
                                    if(data.list[today][i].leave_status==1){
                                         if(data.list[today][i].is_read==1){
                                            $(".arrangeList li").eq(i).find(".leave").text("请假中").attr("href","javascript:void(0);")
                                        }else{
                                            $(".arrangeList li").eq(i).find(".leave").text("已请假").attr("href","javascript:void(0);")
                                        }
                                    }
                                }
                        }else{
                            $(".arrangeList").html("")
                            $(".arrangeList").append(
                                    '<li style="margin-top:0.5rem;color:white;text-align:center;font-size:0.5rem;color:white;">亲，您今天没有课哦 !</li>'
                                )
                        } 
                         if((y==nowYear&nowMonth<m)||(y>nowYear)||(y==nowYear&nowMonth==m&(d>nowDay||d==nowDay))){
                            $(".uploadInfo").show()
                            $(".uploadClass").attr("attr_year",y)
                            $(".uploadClass").attr("attr_month",m)
                            $(".uploadClass").attr("attr_day",d)
                        }else{
                            $(".uploadInfo").hide()
                            $(".leave").hide()
                        }
                        var arrangeList=$(".arrangeList li").length;
                        for(var a=0; a<arrangeList; a++){
                            var textHeight=$(".arrangeList li").eq(a).find(".classText").height()
                            $(".arrangeList li").eq(a).find(".timeLong").css("height",textHeight)
                        }

                    })

                }
            })
},
prevYearCb: function (y,m,d) {
  
}
});
 // 点击显示提交弹框
    $('.uploadInfo').click(function () {
        $('.uploadContent').show();
    })
    // 点击关闭提交弹框
     $('.closeCourse').click(function () {
        $('.uploadContent').hide();
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
    var toggle=true;
    // 提交上课信息
    $('#post').click(function () {
        var year = $('.uploadClass').attr('attr_year');
        var month = $('.uploadClass').attr('attr_month');
        var day = $('.uploadClass').attr('attr_day');
        var start_hour_time=$("input[name='start_hour_time']").val();
        if(start_hour_time==""){
            alert("请输入上课时间段。")
            return
        }
        var end_hour_time=$("input[name='end_hour_time']").val();
        if(end_hour_time==""){
            alert("请输入上课时间段。")
            return
        }
        var class_title=$("input[name='class_title']").val();
        if(class_title==""){
            alert("请输入课程名称。")
            return
        }
        var teacher=$("input[name='teacher']").val();
        if(teacher==""){
            alert("请输入上课老师。")
            return
        }
        var username=$("input[name='username']").val();
        if(username==""){
            alert("请输入上课学生。")
            return
        }
        // var mobile = $("input[name='mobile']").val();
        // var ret = /^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/;
        // if (!ret.test(mobile)) {
        //     return alert('手机格式不正确,请输入正确的手机号码。');
        // } 
        var class_hour=$("input[name='class_hour']").val();
        if(class_hour==""){
            alert("请输入当前课时。")
            return
        }
        var province=$(".province option:selected").html();
        if(province==""){
            alert("请输入上课地址。")
            return
        }
        var city=$(".city option:selected").html();
        if(city==""){
            alert("请输入上课地址。")
            return
        }
        var area=$(".county option:selected").html();
        if(area==""){
            alert("请输入上课地址。")
            return
        }
        var address=$("input[name='details']").val();
        if(address==""){
            alert("请输入详细的地址。")
            return
        }
        var data = {
            start_day_time : year + month + day,
            class_title : $("input[name='class_title']").val(),
            teacher : $("input[name='teacher']").val(),
            username : $("input[name='username']").val(),
            class_hour : $("input[name='class_hour']").val(),
            start_hour_time : $("input[name='start_hour_time']").val(),
            end_hour_time : $("input[name='end_hour_time']").val(),
            province : $(".province option:selected").html(),
            city : $(".city option:selected").html(),
            area : $(".county option:selected").html(),
            address : $("input[name='details']").val()
            // mobile:$("input[name='mobile']").val()
        };
        if(toggle){
            toggle=false;
            var url = '/index.php/Personal/uploadClass';
            $.post(url,data,function (result) {
                if(result.status == 1){
                    toggle=true;
                    alert(result.message);
                    $(".uploadContent").hide()
                    window.location.href="/index.php/Personal/schedule.html"
                }else{
                    toggle=true
                    alert(result.message);
                }
            },'json')
        }
        
    })
});

 function getCode(){
        var url = "/index.php/personal/getCode";
        var mobile = $("input[name='mobiles']").val();
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
function submitInfo(){
    var mobile = $("input[name='mobiles']").val();
    var ret = /^13[0-9]{9}$|14[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/;
    if (!ret.test(mobile)) {
        return alert('手机格式不正确');
    }
     var code = $("input[name='code']").val();
        if(code == ""){
             alert("请输入验证码");
            return
        }

   
    if(bool){
        bool=false;
         var url = "/index.php/personal/submitInfo";
         $.post(url, {mobile: mobile,code:code}, function (data) {
            if(data.status==1){
                bool=true;
                alert(data.message)
                $(".tellBox").hide()
                window.location.href="/index.php/personal/schedule.html"
            }else{
                bool=true;
                alert(data.message)
            }
        }, 'json');
    }
   
}
$(".closeBtn").click(function(){
    $(".tellBox").hide()
})