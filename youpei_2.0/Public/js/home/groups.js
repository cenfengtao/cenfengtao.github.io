 // 底部导航选中状态
        $(".footer ul li>img").css("display","none");
        $(".footer ul li>img:eq(0)").css("display","block");
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
            },2000)
        });
        // 进入团购详情
         function getGroup(id) {
            window.location.href = "/index.php/Groups/getGroup?id=" + id;
        }
       
        //查看商品详情
        function getProduct(id) {
            window.location.href = "/index.php/Product/productDetails?pro_id=" + id;
        }
         $(function(){
             // 拼团类型
            $(".groupsType li").click(function(){
                var index=$(this).index();
                $(this).addClass("on").siblings().removeClass("on");
                $(".content >div").eq(index).show().siblings().hide();
                $(".nav>div").eq(index).show().siblings().hide();
            })
            var coursePage=10;
            var shopPage=10;
        $.ajax({
            type: "get",
            url: "/index.php/Groups/ajaxIndex",
            aysnc: true,
            dataType: "json",
            success: function (data) {
                if(data.status==1){
                    var data=data.data;
                    for(var i=0; i<data.classifyCurriculum.length; i++){
                        $(".courseNav ul").append(
                                '<li  attr_id="'+data.classifyCurriculum[i].id+'"><a href="javascript:void(0);">'+data.classifyCurriculum[i].title+'</a></li>'
                            )
                    }
                    for(var i=0; i<data.classifyProduct.length; i++){
                        $(".shopNav ul").append(
                                '<li  attr_id="'+data.classifyProduct[i].id+'"><a href="javascript:void(0);">'+data.classifyProduct[i].title+'</a></li>'
                            )
                    }
                     for(var i=0; i<data.groupCurriculum.length; i++){
                        $(".courseList ul").append(
                                '<li onclick="getGroup('+data.groupCurriculum[i].id+')">'+
                                    '<div class="img">'+
                                        '<img src="'+data.groupCurriculum[i].image+'" alt="" class="courseImg">'+
                                        '<div class="tap">'+
                                            '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                            '<img src="'+data.groupCurriculum[i].logo+'" alt="" class="imgLogo">'+
                                        '</div>'+
                                        '<div class="type type'+i+'">'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="text">'+
                                        '<div class="time">'+
                                            '<img src="../../../../Public/images/indexFilter.png" alt="">'+
                                            '<span class="endtime" value="'+data.groupCurriculum[i].end_time+'"></span>'+
                                        '</div>'+
                                        '<h3>'+data.groupCurriculum[i].title.substring(0,8)+'...</h3>'+
                                        '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+data.groupCurriculum[i].price+'</strong></span>'+
                                        '<p class="originalPrice">原价：'+data.groupCurriculum[i].original_price+'元/人</p>'+
                                        '<a href="javascript:void(0)">去拼团</a>'+
                                        '<div class="groupNum groupNum'+i+'">'+
                                            '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupCurriculum[i].groupCount+'</span>/<span class="allPeople">'+data.groupCurriculum[i].max_people+'</span>)</span>'+
                                            '<div class="bar"><span></span></div>'+
                                        '</div>'+
                                    '</div>'+
                                '</li>'
                            )
                         // 遍历描述标签
                                  var type=".type"+i;
                                  if(data.groupCurriculum[i].tagA){
                                     $(type).append(
                                          '<span>'+data.groupCurriculum[i].tagA+'</span>'
                                      )
                                }else{
                                    $(type).hide()
                                }
                                if(data.groupCurriculum[i].tagB){
                                    $(type).append(
                                            '<span>'+data.groupCurriculum[i].tagB+'</span>'
                                        )
                                }
                                if(data.groupCurriculum[i].tagC){
                                    $(type).append(
                                            '<span>'+data.groupCurriculum[i].tagC+'</span>'
                                        )
                                }
                                 // 拼团里的进度条颜色变化
                                var groupNum=".groupNum"+i;
                                var nowPeople=$(groupNum).find(".nowPeople").text();
                                var allPeople=$(groupNum).find(".allPeople").text();
                                var scale=(nowPeople/allPeople*100);
                                $(groupNum).find(".bar span").css("width",scale+"%")
                                if(scale<35){
                                    $(groupNum).find(".bar span").css("background","greenyellow")
                                }else if(scale<70){
                                    $(groupNum).find(".bar span").css("background","orange")
                                }else if(scale<101){
                                    $(groupNum).find(".bar span").css("background","red")
                                }
                    }
                     if(data.groupCurriculum.length==0){
                        $(".courseList ul").html("")
                        $(".courseList ul").append(
                                '<li style="text-align:center; font-size:0.5rem;">亲，暂时没有团购商品哦！</li>'
                            )
                    }
                    var groupProductLength=data.groupProduct.length;
                        // if(groupProductLength%2!=0){
                        //     groupProductLength=groupProductLength-1;
                        // }
                    for(var i=0; i<groupProductLength; i++){
                        $(".shopGroups ul").append(
                            // '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                            //     '<div class="img">'+
                            //         '<div class="time">'+
                            //             '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                            //         '</div>'+
                            //         '<div class="type shopGroups'+i+'">'+
                            //             '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                            //         '</div>'+
                            //         '<img src="'+data.groupProduct[i].image+'" alt="">'+
                            //     '</div>'+
                            //     '<div class="text">'+
                            //         '<h3>'+data.groupProduct[i].title.substring(0,9)+'...</h3>'+
                            //         '<div>'+
                            //             '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                            //         '<a href="javascript:void(0);">加入团购</a>'+
                            //         '</div>'+
                            //         '<div class="groupNum shopping'+i+'">'+
                            //             '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                            //             '<div class="bar"><span></span></div>'+
                            //         '</div>'+
                            //     '</div>'+
                            // '</li>'
                            '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                '<div class="img">'+
                                    '<div class="time">'+
                                        '<img src="../../../../Public/images/limitFilter.png" alt="">'+
                                        '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                    '</div>'+
                                    '<div class="type shopGroups'+i+'">'+
                                        '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                                    '</div>'+
                                    '<img src="'+data.groupProduct[i].image+'">'+
                                '</div>'+
                                '<div class="text">'+
                                    '<h3>'+data.groupProduct[i].title+'</h3>'+
                                    '<div>'+
                                        '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                                        '<a href="javascript:void(0);">加入团购</a>'+
                                    '</div>'+
                                    '<div class="groupNum shopping'+i+'">'+
                                        '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                        '<div class="bar"><span class="progress"></span></div>'+
                                    '</div>'+
                                '</div>'+
                            '</li>'
                            )
                        // 遍历描述标签
                             var shopGroups=".shopGroups"+i;
                                if(data.groupProduct[i].tagA){
                                         $(shopGroups).append(
                                            '<span class="tab">'+data.groupProduct[i].tagA+'</span>'
                                        )
                                }
                                if(data.groupProduct[i].tagB){
                                    $(shopGroups).append(
                                            '<span class="tab">'+data.groupProduct[i].tagB+'</span>'
                                        )
                                }
                                
                                if(data.groupProduct[i].tagC){
                                    $(shopGroups).append(
                                            '<span class="tab">'+data.groupProduct[i].tagC+'</span>'
                                        )
                                }
                                // 拼团里的进度条颜色变化
                                var groupNum=".shopping"+i;
                                var nowPeople=$(groupNum).find(".nowPeople").text();
                                var allPeople=$(groupNum).find(".allPeople").text();
                                var scale=(nowPeople/allPeople*100);
                                $(groupNum).find(".bar span").css("width",scale+"%")
                                if(scale<35){
                                    $(groupNum).find(".bar span").css("background","greenyellow")
                                }else if(scale<70){
                                    $(groupNum).find(".bar span").css("background","orange")
                                }else if(scale<101){
                                    $(groupNum).find(".bar span").css("background","red")
                                }
                    }
                    if(data.groupProduct.length==0){
                        $(".shopGroups ul").html("")
                        $(".shopGroups ul").append(
                                '<li style="text-align:center; font-size:0.5rem;width:100%;height:1rem;clear:both;line-height:1.5rem;">亲，暂时没有团购商品哦！</li>'
                            )
                    }
                    // 拼团导航
                    $(".courseNav li").click(function(){
                        $(this).addClass("on").siblings().removeClass("on");
                        var class_id=$(this).attr("attr_id")
                        var type=$(".groupsType li.on").attr("id")
                        $(".courseList ul").html("")
                        $('.courseList >div').html("")
                        shopPage=10;
                        coursePage=10;
                        if(class_id=="type"){
                            $.ajax({
                                type: "get",
                                url: "/index.php/Groups/getGroupByClass",
                                aysnc: true,
                                dataType: "json",
                                data:{
                                    type:type
                                },
                                success: function (data) {
                                    if(data.status==1){
                                        $(".courseList ul").html("")
                                        var data=data.data;
                                        if(data.groupProduct.length==0){
                                            $(".courseList ul").html("")
                                            $(".courseList ul").append(
                                                    '<li style="text-align:center; font-size:0.5rem;">亲，暂时没有团购商品哦！</li>'
                                                )
                                        }
                                        for(var i=0; i<data.groupProduct.length; i++){
                                            $(".courseList ul").append(
                                                    '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                        '<div class="img">'+
                                                            '<img src="'+data.groupProduct[i].image+'" alt="" class="courseImg">'+
                                                            '<div class="tap">'+
                                                                '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                                                '<img src="'+data.groupProduct[i].logo+'" alt="" class="imgLogo">'+
                                                            '</div>'+
                                                            '<div class="type type'+i+'">'+
                                                            '</div>'+
                                                        '</div>'+
                                                        '<div class="text">'+
                                                            '<div class="time">'+
                                                                '<img src="../../../../Public/images/indexFilter.png" alt="">'+
                                                                '<span class="endtime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                            '</div>'+
                                                            '<h3>'+data.groupProduct[i].title.substring(0,8)+'...</h3>'+
                                                            '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+data.groupProduct[i].price+'</strong></span>'+
                                                            '<p class="originalPrice">原价：'+data.groupProduct[i].original_price+'元/人</p>'+
                                                            '<a href="javascript:void(0)">去拼团</a>'+
                                                            '<div class="groupNum groupNum'+i+'">'+
                                                                '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                                '<div class="bar"><span></span></div>'+
                                                            '</div>'+
                                                        '</div>'+
                                                    '</li>'
                                                )
                                             // 遍历描述标签
                                                     var type=".type"+i;
                                                      if(data.groupProduct[i].tagA){
                                                             $(type).append(
                                                                '<span>'+data.groupProduct[i].tagA+'</span>'
                                                            )
                                                    }else{
                                                        $(type).hide()
                                                    }
                                                    if(data.groupProduct[i].tagB){
                                                        $(type).append(
                                                                '<span>'+data.groupProduct[i].tagB+'</span>'
                                                            )
                                                    }
                                                    if(data.groupProduct[i].tagC){
                                                        $(type).append(
                                                                '<span>'+data.groupProduct[i].tagC+'</span>'
                                                            )
                                                    }
                                                     // 拼团里的进度条颜色变化
                                                    var groupNum=".groupNum"+i;
                                                    var nowPeople=$(groupNum).find(".nowPeople").text();
                                                    var allPeople=$(groupNum).find(".allPeople").text();
                                                    var scale=(nowPeople/allPeople*100);
                                                    $(groupNum).find(".bar span").css("width",scale+"%")
                                                    if(scale<35){
                                                        $(groupNum).find(".bar span").css("background","greenyellow")
                                                    }else if(scale<70){
                                                        $(groupNum).find(".bar span").css("background","orange")
                                                    }else if(scale<101){
                                                        $(groupNum).find(".bar span").css("background","red")
                                                    }
                                        }
                                        
                                    }
                                    
                                }
                            })
                        }else{
                            $.ajax({
                                type: "get",
                                url: "/index.php/Groups/getGroupByClass",
                                aysnc: true,
                                dataType: "json",
                                data:{
                                    class_id:class_id
                                },
                                success: function (data) {
                                    if(data.status==1){
                                        $(".courseList ul").html("")
                                        var data=data.data;
                                        for(var i=0; i<data.groupProduct.length; i++){
                                            $(".courseList ul").append(
                                                    '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                        '<div class="img">'+
                                                            '<img src="'+data.groupProduct[i].image+'" alt="" class="courseImg">'+
                                                            '<div class="tap">'+
                                                                '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                                                '<img src="'+data.groupProduct[i].logo+'" alt="" class="imgLogo">'+
                                                            '</div>'+
                                                            '<div class="type type'+i+'">'+
                                                            '</div>'+
                                                        '</div>'+
                                                        '<div class="text">'+
                                                            '<div class="time">'+
                                                                '<img src="../../../../Public/images/indexFilter.png" alt="">'+
                                                                '<span class="endtime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                            '</div>'+
                                                            '<h3>'+data.groupProduct[i].title.substring(0,8)+'...</h3>'+
                                                            '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+data.groupProduct[i].price+'</strong></span>'+
                                                            '<p class="originalPrice">原价：'+data.groupProduct[i].original_price+'元/人</p>'+
                                                            '<a href="javascript:void(0);">去拼团</a>'+
                                                            '<div class="groupNum groupNum'+i+'">'+
                                                                '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                                '<div class="bar"><span></span></div>'+
                                                            '</div>'+
                                                        '</div>'+
                                                    '</li>'
                                                )
                                             // 遍历描述标签
                                                     var type=".type"+i;
                                                      if(data.groupProduct[i].tagA){
                                                             $(type).append(
                                                                '<span>'+data.groupProduct[i].tagA+'</span>'
                                                            )
                                                    }else{
                                                        $(type).hide()
                                                    }
                                                    if(data.groupProduct[i].tagB){
                                                        $(type).append(
                                                                '<span>'+data.groupProduct[i].tagB+'</span>'
                                                            )
                                                    }
                                                    if(data.groupProduct[i].tagC){
                                                        $(type).append(
                                                                '<span>'+data.groupProduct[i].tagC+'</span>'
                                                            )
                                                    }
                                                     // 拼团里的进度条颜色变化
                                                    var groupNum=".groupNum"+i;
                                                    var nowPeople=$(groupNum).find(".nowPeople").text();
                                                    var allPeople=$(groupNum).find(".allPeople").text();
                                                    var scale=(nowPeople/allPeople*100);
                                                    $(groupNum).find(".bar span").css("width",scale+"%")
                                                    if(scale<35){
                                                        $(groupNum).find(".bar span").css("background","greenyellow")
                                                    }else if(scale<70){
                                                        $(groupNum).find(".bar span").css("background","orange")
                                                    }else if(scale<101){
                                                        $(groupNum).find(".bar span").css("background","red")
                                                    }
                                        }
                                        if(data.groupProduct.length==0){
                                            $(".courseList ul").html("")
                                            $(".courseList ul").append(
                                                    '<li style="text-align:center; font-size:0.5rem;clear:both;">亲，暂时没有此类团购商品哦！</li>'
                                                )
                                        }
                                    }
                                    
                                }
                            })
                        }
                         
                    })
                    $(".shopNav li").click(function(){
                        $(this).addClass("on").siblings().removeClass("on")
                        var class_id=$(this).attr("attr_id")
                        var type=$(".groupsType li.on").attr("id")
                         $(".shopGroups ul").html("")
                         $(".shopGroups>div").html("")
                         shopPage=10;
                        coursePage=10;
                        if(class_id=="type"){
                             $.ajax({
                                type: "get",
                                url: "/index.php/Groups/getGroupByClass",
                                aysnc: true,
                                dataType: "json",
                                data:{
                                    type:type
                                },
                                success: function (data) {
                                    if(data.status==1){
                                         $(".shopGroups ul").html("")
                                         var data=data.data;
                                        var groupProductLength=data.groupProduct.length;
                                        // if(groupProductLength%2!=0){
                                        //     groupProductLength=groupProductLength-1;
                                        // }
                                        for(var i=0; i<groupProductLength; i++){
                                            $(".shopGroups ul").append(
                                                // '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                //     '<div class="img">'+
                                                //         '<div class="time">'+
                                                //             '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                //         '</div>'+
                                                //         '<div class="type shopGroups'+i+'">'+
                                                //             '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                                                //         '</div>'+
                                                //         '<img src="'+data.groupProduct[i].image+'" alt="">'+
                                                //     '</div>'+
                                                //     '<div class="text">'+
                                                //         '<h3>'+data.groupProduct[i].title.substring(0,9)+'...</h3>'+
                                                //         '<div>'+
                                                //             '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                                                //         '<a href="javascript:void(0);">加入团购</a>'+
                                                //         '</div>'+
                                                //         '<div class="groupNum shopping'+i+'">'+
                                                //             '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                //             '<div class="bar"><span></span></div>'+
                                                //         '</div>'+
                                                //     '</div>'+
                                                // '</li>'
                                                '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                '<div class="img">'+
                                                    '<div class="time">'+
                                                        '<img src="../../../../Public/images/limitFilter.png" alt="">'+
                                                        '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                    '</div>'+
                                                    '<div class="type shopGroups'+i+'">'+
                                                        '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                                                    '</div>'+
                                                    '<img src="'+data.groupProduct[i].image+'">'+
                                                '</div>'+
                                                '<div class="text">'+
                                                    '<h3>'+data.groupProduct[i].title+'</h3>'+
                                                    '<div>'+
                                                        '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                                                        '<a href="javascript:void(0);">加入团购</a>'+
                                                    '</div>'+
                                                    '<div class="groupNum shopping'+i+'">'+
                                                        '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                        '<div class="bar"><span class="progress"></span></div>'+
                                                    '</div>'+
                                                '</div>'+
                                            '</li>'
                                            )
                                            // 遍历描述标签
                                             var shopGroups=".shopGroups"+i;
                                            if(data.groupProduct[i].tagA){
                                                     $(shopGroups).append(
                                                        '<span class="tab">'+data.groupProduct[i].tagA+'</span>'
                                                    )
                                            }
                                            if(data.groupProduct[i].tagB){
                                                $(shopGroups).append(
                                                        '<span class="tab">'+data.groupProduct[i].tagB+'</span>'
                                                    )
                                            }
                                            
                                            if(data.groupProduct[i].tagC){
                                                $(shopGroups).append(
                                                        '<span class="tab">'+data.groupProduct[i].tagC+'</span>'
                                                    )
                                            }
                                            // 拼团里的进度条颜色变化
                                            var groupNum=".shopping"+i;
                                            var nowPeople=$(groupNum).find(".nowPeople").text();
                                            var allPeople=$(groupNum).find(".allPeople").text();
                                            var scale=(nowPeople/allPeople*100);
                                            $(groupNum).find(".bar span").css("width",scale+"%")
                                            if(scale<35){
                                                $(groupNum).find(".bar span").css("background","greenyellow")
                                            }else if(scale<70){
                                                $(groupNum).find(".bar span").css("background","orange")
                                            }else if(scale<101){
                                                $(groupNum).find(".bar span").css("background","red")
                                            }
                                        }
                                        if(data.groupProduct.length==0){
                                            $(".shopGroups ul").html("")
                                            $(".shopGroups ul").append(
                                                    '<li style="text-align:center; font-size:0.5rem;width:100%;height:1rem;clear:both;">亲，暂时没有团购商品哦！</li>'
                                                )
                                        }

                                    }
                                   
                                }
                            })
                        }else{
                            $.ajax({
                                type: "get",
                                url: "/index.php/Groups/getGroupByClass",
                                aysnc: true,
                                dataType: "json",
                                data:{
                                    class_id:class_id
                                },
                                success: function (data) {
                                    if(data.status==1){
                                        var data=data.data;
                                        var groupProductLength=data.groupProduct.length;
                                        // if(groupProductLength%2!=0){
                                        //     groupProductLength=groupProductLength-1;
                                        // }
                                        for(var i=0; i<groupProductLength; i++){
                                                $(".shopGroups ul").html("")
                                            $(".shopGroups ul").append(
                                                // '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                //     '<div class="img">'+
                                                //         '<div class="time">'+
                                                //             '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                //         '</div>'+
                                                //         '<div class="type shopGroups'+i+'">'+
                                                //             '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                                                //         '</div>'+
                                                //         '<img src="'+data.groupProduct[i].image+'" alt="">'+
                                                //     '</div>'+
                                                //     '<div class="text">'+
                                                //         '<h3>'+data.groupProduct[i].title.substring(0,9)+'...</h3>'+
                                                //         '<div>'+
                                                //             '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                                                //         '<a href="javascript:void(0);">加入团购</a>'+
                                                //         '</div>'+
                                                //         '<div class="groupNum shopping'+i+'">'+
                                                //             '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                //             '<div class="bar"><span></span></div>'+
                                                //         '</div>'+
                                                //     '</div>'+
                                                // '</li>'
                                                 '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                    '<div class="img">'+
                                                        '<div class="time">'+
                                                            '<img src="../../../../Public/images/limitFilter.png" alt="">'+
                                                            '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                        '</div>'+
                                                        '<div class="type shopGroups'+i+'">'+
                                                            '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                                                        '</div>'+
                                                        '<img src="'+data.groupProduct[i].image+'">'+
                                                    '</div>'+
                                                    '<div class="text">'+
                                                        '<h3>'+data.groupProduct[i].title+'</h3>'+
                                                        '<div>'+
                                                            '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                                                            '<a href="javascript:void(0);">加入团购</a>'+
                                                        '</div>'+
                                                        '<div class="groupNum shopping'+i+'">'+
                                                            '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                            '<div class="bar"><span class="progress"></span></div>'+
                                                        '</div>'+
                                                    '</div>'+
                                                '</li>'
                                                )
                                            // 遍历描述标签
                                            var shopGroups=".shopGroups"+i;
                                            if(data.groupProduct[i].tagA){
                                                     $(shopGroups).append(
                                                        '<span class="tab">'+data.groupProduct[i].tagA+'</span>'
                                                    )
                                            }
                                            if(data.groupProduct[i].tagB){
                                                $(shopGroups).append(
                                                        '<span class="tab">'+data.groupProduct[i].tagB+'</span>'
                                                    )
                                            }
                                            
                                            if(data.groupProduct[i].tagC){
                                                $(shopGroups).append(
                                                        '<span class="tab">'+data.groupProduct[i].tagC+'</span>'
                                                    )
                                            }
                                            // 拼团里的进度条颜色变化
                                            var groupNum=".shopping"+i;
                                            var nowPeople=$(groupNum).find(".nowPeople").text();
                                            var allPeople=$(groupNum).find(".allPeople").text();
                                            var scale=(nowPeople/allPeople*100);
                                            $(groupNum).find(".bar span").css("width",scale+"%")
                                            if(scale<35){
                                                $(groupNum).find(".bar span").css("background","greenyellow")
                                            }else if(scale<70){
                                                $(groupNum).find(".bar span").css("background","orange")
                                            }else if(scale<101){
                                                $(groupNum).find(".bar span").css("background","red")
                                            }
                                        }
                                         if(data.groupProduct.length==0){
                                            $(".shopGroups ul").append(
                                                    '<li style="text-align:center; font-size:0.5rem;width:100%;height:1rem; clear:both;line-height:1.5rem;">亲，暂时没有此类团购商品哦！</li>'
                                                )
                                        }
                                    }
                                
                                }
                            })
                        }
                    })
                   
                }  
            }
        })
            
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
                        var class_id=$(".classifyNav li.on").attr("attr_id");
                        var type=$(".groupsType li.on").attr("id");
                        if(class_id=="type"){
                            if(type==1){
                             $.ajax({
                                type: "get",
                                url: "/index.php/Groups/loadingGroup",
                                aysnc: true,
                                dataType: "json",
                                data:{
                                    page:coursePage,
                                    type:type
                                },
                                success: function (data) {
                                    if(data.status==1){
                                        var data=data.data;
                                            for(var i=0; i<data.groupProduct.length; i++){
                                                    $(".courseList ul").append(
                                                            '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                                '<div class="img">'+
                                                                    '<img src="'+data.groupProduct[i].image+'" alt="" class="courseImg">'+
                                                                    '<div class="tap">'+
                                                                        '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                                                        '<img src="'+data.groupProduct[i].logo+'" alt="" class="imgLogo">'+
                                                                    '</div>'+
                                                                    '<div class="type type'+i+'">'+
                                                                    '</div>'+
                                                                '</div>'+
                                                                '<div class="text">'+
                                                                    '<div class="time">'+
                                                                        '<img src="../../../../Public/images/indexFilter.png" alt="">'+
                                                                        '<span class="endtime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                                    '</div>'+
                                                                    '<h3>'+data.groupProduct[i].title.substring(0,8)+'...</h3>'+
                                                                    '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+data.groupProduct[i].price+'</strong></span>'+
                                                                    '<p class="originalPrice">原价：'+data.groupProduct[i].original_price+'元/人</p>'+
                                                                    '<a href="javascript:void(0);">去拼团</a>'+
                                                                    '<div class="groupNum groupNum'+i+'">'+
                                                                        '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                                        '<div class="bar"><span></span></div>'+
                                                                    '</div>'+
                                                                '</div>'+
                                                            '</li>'
                                                        )
                                                     // 遍历描述标签
                                                     var type=".type"+i;
                                                      if(data.groupProduct[i].tagA){
                                                             $(type).append(
                                                                '<span>'+data.groupProduct[i].tagA+'</span>'
                                                            )
                                                    }else{
                                                        $(type).hide()
                                                    }
                                                    if(data.groupProduct[i].tagB){
                                                        $(type).append(
                                                                '<span>'+data.groupProduct[i].tagB+'</span>'
                                                            )
                                                    }
                                                    if(data.groupProduct[i].tagC){
                                                        $(type).append(
                                                                '<span>'+data.groupProduct[i].tagC+'</span>'
                                                            )
                                                    }
                                                     // 拼团里的进度条颜色变化
                                                    var groupNum=".groupNum"+i;
                                                    var nowPeople=$(groupNum).find(".nowPeople").text();
                                                    var allPeople=$(groupNum).find(".allPeople").text();
                                                    var scale=(nowPeople/allPeople*100);
                                                    $(groupNum).find(".bar span").css("width",scale+"%")
                                                    if(scale<35){
                                                        $(groupNum).find(".bar span").css("background","greenyellow")
                                                    }else if(scale<70){
                                                        $(groupNum).find(".bar span").css("background","orange")
                                                    }else if(scale<101){
                                                        $(groupNum).find(".bar span").css("background","red")
                                                    }
                                                }
                                                if(data.groupProduct.length==0){
                                                    $(".courseList >div").html(
                                                            '<p style="text-align:center; font-size:0.5rem;width:100%;height:1rem;line-height:1rem;">亲，暂时没有更多团购商品了！</p>'
                                                        )
                                                }      
                                            }    
                                            
                                }
                            })
                             coursePage+=6;
                           }else{
                            $.ajax({
                                type: "get",
                                url: "/index.php/Groups/loadingGroup",
                                aysnc: true,
                                dataType: "json",
                                data:{
                                    page:shopPage,
                                    type:type
                                },
                                success: function (data) {
                                    if(data.status==1){
                                        var data=data.data;
                                        var groupProductLength=data.groupProduct.length;
                                            // if(groupProductLength%2!=0){
                                            //     groupProductLength=groupProductLength-1;
                                            // }
                                            for(var i=0; i<groupProductLength; i++){
                                                $(".shopGroups ul").append(
                                                    // '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                    //     '<div class="img">'+
                                                    //         '<div class="time">'+
                                                    //             '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                    //         '</div>'+
                                                    //         '<div class="type shopGroups'+i+'">'+
                                                    //             '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                                                    //         '</div>'+
                                                    //         '<img src="'+data.groupProduct[i].image+'" alt="">'+
                                                    //     '</div>'+
                                                    //     '<div class="text">'+
                                                    //         '<h3>'+data.groupProduct[i].title.substring(0,9)+'...</h3>'+
                                                    //         '<div>'+
                                                    //             '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                                                    //         '<a href="javascript:void(0);">加入团购</a>'+
                                                    //         '</div>'+
                                                    //         '<div class="groupNum shopping'+i+'">'+
                                                    //             '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                    //             '<div class="bar"><span></span></div>'+
                                                    //         '</div>'+
                                                    //     '</div>'+
                                                    // '</li>'
                                                     '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                        '<div class="img">'+
                                                            '<div class="time">'+
                                                                '<img src="../../../../Public/images/limitFilter.png" alt="">'+
                                                                '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                            '</div>'+
                                                            '<div class="type shopGroups'+i+'">'+
                                                                '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                                                            '</div>'+
                                                            '<img src="'+data.groupProduct[i].image+'">'+
                                                        '</div>'+
                                                        '<div class="text">'+
                                                            '<h3>'+data.groupProduct[i].title+'</h3>'+
                                                            '<div>'+
                                                                '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                                                                '<a href="javascript:void(0);">加入团购</a>'+
                                                            '</div>'+
                                                            '<div class="groupNum shopping'+i+'">'+
                                                                '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                                '<div class="bar"><span class="progress"></span></div>'+
                                                            '</div>'+
                                                        '</div>'+
                                                    '</li>'
                                                    )
                                                // 遍历描述标签
                                                var shopGroups=".shopGroups"+i;
                                                if(data.groupProduct[i].tagA){
                                                         $(shopGroups).append(
                                                            '<span class="tab">'+data.groupProduct[i].tagA+'</span>'
                                                        )
                                                }
                                                if(data.groupProduct[i].tagB){
                                                    $(shopGroups).append(
                                                            '<span class="tab">'+data.groupProduct[i].tagB+'</span>'
                                                        )
                                                }
                                                
                                                if(data.groupProduct[i].tagC){
                                                    $(shopGroups).append(
                                                            '<span class="tab">'+data.groupProduct[i].tagC+'</span>'
                                                        )
                                                }
                                                // 拼团里的进度条颜色变化
                                                var groupNum=".shopping"+i;
                                                var nowPeople=$(groupNum).find(".nowPeople").text();
                                                var allPeople=$(groupNum).find(".allPeople").text();
                                                var scale=(nowPeople/allPeople*100);
                                                $(groupNum).find(".bar span").css("width",scale+"%")
                                                if(scale<35){
                                                    $(groupNum).find(".bar span").css("background","greenyellow")
                                                }else if(scale<70){
                                                    $(groupNum).find(".bar span").css("background","orange")
                                                }else if(scale<101){
                                                    $(groupNum).find(".bar span").css("background","red")
                                                }
                                            }
                                            if(data.groupProduct.length==0){
                                                $(".shopGroups >div").html(
                                                        '<p style="text-align:center; font-size:0.5rem;width:100%;height:1rem;clear:both;line-height:1.5rem;">亲，暂时没有更多团购商品了！</p>'
                                                    )
                                            }
                                        }
                                }
                            })
                            shopPage+=6;
                           }
                           
                        }else{
                            if(type==1){
                             $.ajax({
                                type: "get",
                                url: "/index.php/Groups/loadingGroup",
                                aysnc: true,
                                dataType: "json",
                                data:{
                                    page:coursePage,
                                    class_id:class_id
                                },
                                success: function (data) {
                                    if(data.status==1){
                                        var data=data.data;
                                            for(var i=0; i<data.groupProduct.length; i++){
                                                    $(".courseList ul").append(
                                                            '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                                '<div class="img">'+
                                                                    '<img src="'+data.groupProduct[i].image+'" alt="" class="courseImg">'+
                                                                    '<div class="tap">'+
                                                                        '<img src="../../../../Public/images/imgTap.png" alt="" class="logoTap">'+
                                                                        '<img src="'+data.groupProduct[i].logo+'" alt="" class="imgLogo">'+
                                                                    '</div>'+
                                                                    '<div class="type type'+i+'">'+
                                                                    '</div>'+
                                                                '</div>'+
                                                                '<div class="text">'+
                                                                    '<div class="time">'+
                                                                        '<img src="../../../../Public/images/indexFilter.png" alt="">'+
                                                                        '<span class="endtime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                                    '</div>'+
                                                                    '<h3>'+data.groupProduct[i].title.substring(0,8)+'...</h3>'+
                                                                    '<span class="groupPrice">组团价：<span class="moneySign">￥</span><strong>'+data.groupProduct[i].price+'</strong></span>'+
                                                                    '<p class="originalPrice">原价：'+data.groupProduct[i].original_price+'元/人</p>'+
                                                                    '<a href="javascript:void(0);">去拼团</a>'+
                                                                    '<div class="groupNum groupNum'+i+'">'+
                                                                        '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                                        '<div class="bar"><span></span></div>'+
                                                                    '</div>'+
                                                                '</div>'+
                                                            '</li>'
                                                        )
                                                     // 遍历描述标签
                                                     var type=".type"+i;
                                                      if(data.groupProduct[i].tagA){
                                                             $(type).append(
                                                                '<span>'+data.groupProduct[i].tagA+'</span>'
                                                            )
                                                    }else{
                                                        $(type).hide()
                                                    }
                                                    if(data.groupProduct[i].tagB){
                                                        $(type).append(
                                                                '<span>'+data.groupProduct[i].tagB+'</span>'
                                                            )
                                                    }
                                                    if(data.groupProduct[i].tagC){
                                                        $(type).append(
                                                                '<span>'+data.groupProduct[i].tagC+'</span>'
                                                            )
                                                    }
                                                     // 拼团里的进度条颜色变化
                                                    var groupNum=".groupNum"+i;
                                                    var nowPeople=$(groupNum).find(".nowPeople").text();
                                                    var allPeople=$(groupNum).find(".allPeople").text();
                                                    var scale=(nowPeople/allPeople*100);
                                                    $(groupNum).find(".bar span").css("width",scale+"%")
                                                    if(scale<35){
                                                        $(groupNum).find(".bar span").css("background","greenyellow")
                                                    }else if(scale<70){
                                                        $(groupNum).find(".bar span").css("background","orange")
                                                    }else if(scale<101){
                                                        $(groupNum).find(".bar span").css("background","red")
                                                    }
                                                }
                                                if(data.groupProduct.length==0){
                                                    $(".courseList >div").html(
                                                            '<p style="text-align:center; font-size:0.5rem;width:100%;height:1rem;">亲，暂时没有更多团购商品了！</p>'
                                                        )
                                                }      
                                            }    
                                            
                                }
                            })
                             coursePage+=6;
                           }else{
                                $.ajax({
                                    type: "get",
                                    url: "/index.php/Groups/loadingGroup",
                                    aysnc: true,
                                    dataType: "json",
                                    data:{
                                        page:shopPage,
                                        class_id:class_id
                                    },
                                    success: function (data) {
                                        if(data.status==1){
                                            var data=data.data;
                                            var groupProductLength=data.groupProduct.length;
                                                // if(groupProductLength%2!=0){
                                                //     groupProductLength=groupProductLength-1;
                                                // }
                                                for(var i=0; i<groupProductLength; i++){
                                                    $(".shopGroups ul").append(
                                                        // '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                        //     '<div class="img">'+
                                                        //         '<div class="time">'+
                                                        //             '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                        //         '</div>'+
                                                        //         '<div class="type shopGroups'+i+'">'+
                                                        //             '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                                                        //         '</div>'+
                                                        //         '<img src="'+data.groupProduct[i].image+'" alt="">'+
                                                        //     '</div>'+
                                                        //     '<div class="text">'+
                                                        //         '<h3>'+data.groupProduct[i].title.substring(0,9)+'...</h3>'+
                                                        //         '<div>'+
                                                        //             '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                                                        //         '<a href="javascript:void(0);">加入团购</a>'+
                                                        //         '</div>'+
                                                        //         '<div class="groupNum shopping'+i+'">'+
                                                        //             '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                        //             '<div class="bar"><span></span></div>'+
                                                        //         '</div>'+
                                                        //     '</div>'+
                                                        // '</li>'
                                                         '<li onclick="getGroup('+data.groupProduct[i].id+')">'+
                                                            '<div class="img">'+
                                                                '<div class="time">'+
                                                                    '<img src="../../../../Public/images/limitFilter.png" alt="">'+
                                                                    '<span class="shopGroupsTime" value="'+data.groupProduct[i].end_time+'"></span>'+
                                                                '</div>'+
                                                                '<div class="type shopGroups'+i+'">'+
                                                                    '<span class="originalPrice">原价:￥'+data.groupProduct[i].original_price.split(".")[0]+'</span>'+
                                                                '</div>'+
                                                                '<img src="'+data.groupProduct[i].image+'">'+
                                                            '</div>'+
                                                            '<div class="text">'+
                                                                '<h3>'+data.groupProduct[i].title+'</h3>'+
                                                                '<div>'+
                                                                    '<span class="groupPrice">团购价:￥<strong>'+data.groupProduct[i].price.split(".")[0]+'</strong></span>'+
                                                                    '<a href="javascript:void(0);">加入团购</a>'+
                                                                '</div>'+
                                                                '<div class="groupNum shopping'+i+'">'+
                                                                    '<span class="finished">已拼团人数 : (<span class="nowPeople">'+data.groupProduct[i].groupCount+'</span>/<span class="allPeople">'+data.groupProduct[i].max_people+'</span>)</span>'+
                                                                    '<div class="bar"><span class="progress"></span></div>'+
                                                                '</div>'+
                                                            '</div>'+
                                                        '</li>'
                                                        )
                                                    // 遍历描述标签
                                                    var shopGroups=".shopGroups"+i;
                                                    if(data.groupProduct[i].tagA){
                                                             $(shopGroups).append(
                                                                '<span class="tab">'+data.groupProduct[i].tagA+'</span>'
                                                            )
                                                    }
                                                    if(data.groupProduct[i].tagB){
                                                        $(shopGroups).append(
                                                                '<span class="tab">'+data.groupProduct[i].tagB+'</span>'
                                                            )
                                                    }
                                                    
                                                    if(data.groupProduct[i].tagC){
                                                        $(shopGroups).append(
                                                                '<span class="tab">'+data.groupProduct[i].tagC+'</span>'
                                                            )
                                                    }
                                                    // 拼团里的进度条颜色变化
                                                    var groupNum=".shopping"+i;
                                                    var nowPeople=$(groupNum).find(".nowPeople").text();
                                                    var allPeople=$(groupNum).find(".allPeople").text();
                                                    var scale=(nowPeople/allPeople*100);
                                                    $(groupNum).find(".bar span").css("width",scale+"%")
                                                    if(scale<35){
                                                        $(groupNum).find(".bar span").css("background","greenyellow")
                                                    }else if(scale<70){
                                                        $(groupNum).find(".bar span").css("background","orange")
                                                    }else if(scale<101){
                                                        $(groupNum).find(".bar span").css("background","red")
                                                    }
                                                }
                                                if(data.groupProduct.length==0){
                                                    $(".shopGroups >div").html(
                                                            '<p style="text-align:center; font-size:0.5rem;width:100%;height:1rem;clear:both;line-height:1.5rem;">亲，暂时没有更多团购商品了！</p>'
                                                        )
                                                }
                                            }
                                    }
                                })
                                shopPage+=6;
                           }
                        }
                        
                    }
                })
     })
//限时抢购倒计时
var time_current = (new Date()).valueOf();//获取当前时间
$(function () {
    var dateTime = new Date();
    var difference = dateTime.getTime() - time_current;
    // 课程团购倒计时
    setInterval(function () {
        $(".endtime").each(function () {
            var obj = $(this);
            var endTime = new Date(parseInt(obj.attr('value')) * 1000);
            var nowTime = new Date();
            var nMS = endTime.getTime() - nowTime.getTime() + difference;
            var myD = Math.floor(nMS / (1000 * 60 * 60 * 24));
            var myH = Math.floor(nMS / (1000 * 60 * 60)) % 24;
            var myM = Math.floor(nMS / (1000 * 60)) % 60;
            var myS = Math.floor(nMS / 1000) % 60;
            var myMS = Math.floor(nMS / 100) % 10;
            // 
            if (myD >= 0) {
                var str = "剩余下" + myD + "天  " + myH + ":" + myM + ":" + myS;
            } else {
                var str = "已结束！";
            }
            if(myH==-1){
                var str = "已结束！";
            }
            obj.html(str);
        });
    }, 100);
    // 商品团购倒计时
    setInterval(function () {
        $(".shopGroupsTime").each(function () {
            var obj = $(this);
            var endTime = new Date(parseInt(obj.attr('value')) * 1000);
            var nowTime = new Date();
            var nMS = endTime.getTime() - nowTime.getTime() + difference;
            var myD = Math.floor(nMS / (1000 * 60 * 60 * 24));
            var myH = Math.floor(nMS / (1000 * 60 * 60)) % 24;
            var myM = Math.floor(nMS / (1000 * 60)) % 60;
            var myS = Math.floor(nMS / 1000) % 60;
            var myMS = Math.floor(nMS / 100) % 10;
            if (myD >= 0) {
                var str = "剩余下" + myD + "天  " + myH + ":" + myM + ":" + myS;
            } else {
                var str = "已结束！";
            }
            if(myH==-1){
                var str = "已结束！";
            }
            obj.html(str);
        });
    }, 100);
});