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
$(function(){
    $(".explain").click(function(){
        $(".tellBox").fadeIn(1500);
    })
    $(".closeBtn").click(function(){
        $(".tellBox").hide();
    })
})


//    获取url参数
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
var voteId=GetRequest().vote_id;
$(".voteList").attr("href","/index.php/Vote/voteList.html?vote_id="+voteId)
$(".userVote").attr("href","/index.php/Vote/userVote.html?vote_id="+voteId)
$(".userWorks").attr("href","/index.php/Vote/userWorks.html?vote_id="+voteId)
$(".userBillboard").attr("href","/index.php/Vote/userBillboard.html?vote_id="+voteId)

// function count(obj){
//         var objType = typeof obj;
//         if(objType == "string"){
//             return obj.length;
//         }else if(objType == "object"){
//             var objLen = 0;
//             for(var i in obj){
//                 objLen++;
//             }
//             return objLen;
//         }
//         return false;
//     }
    $.ajax({
        type:"get",
        url:"/index.php/Vote/ajaxuserVote",
        async: true,
        dataType:"json",
        data:{
            vote_id:voteId
        },
        success:function(data){
            if(data.data.is_null=="0"){
                $(".comfirm strong").text(data.data.integral)
                $(".worksList").append('<p style="text-align:center; font-size:0.5rem;margin-top:5rem;">您还没有投票哦，赶快去<a href="/index.php/Vote/voteList.html?vote_id='+voteId+' "style="text-decoration: underline;font-size:0.7rem; color:black;">投票</a>吧!</p>')
            }else{
                if(data.status==1){
                $(".comfirm strong").text(data.data.integral)
                var dataList=data.data.workList;
                var dataLength=dataList.length;
                 for(var i=0; i<dataLength; i++){
                    if(data.data.voteStatus==1){
                        if(data.data.upload_type==1){
                            var chtml='<li onclick="toVote('+dataList[i].id+','+dataList[i].vote_id+')">'+
                                    '<div class="img">'+
                                         '<img class="lazy" alt="" src="'+dataList[i].path+'" alt="">'+
                                         '<img class="greenTap" src="../../../../Public/images/greenTap.png"/>'+
                                         '<span class="order">'+dataList[i].number+'号</span>'+
                                    '</div>'+
                                    '<p class="name">作品名称：<span>'+dataList[i].title+'</span></p>'+
                                    '<p>作品作者：<span>'+dataList[i].username+'</span></p>'+
                                    '<div class="text">'+
                                        '<span class="getNum"><strong>'+dataList[i].vote_count+'</strong>票</span>'+
                                        '<span class="vote">给TA投票</span>'+
                                    '</div>'+
                                '</li>'
                                 if($('.voteLeft').height() > $('.voteRight').height()){
                                        $('.voteRight').append(chtml);
                                    }else{
                                        $('.voteLeft').append(chtml);
                                    }
                        }
                        if(data.data.upload_type==2){
                            var chtm='<li onclick="toVote('+dataList[i].id+','+dataList[i].vote_id+')">'+
                                    '<div class="img">'+
                                         '<div class="img">'+
                                         '<img class="lazy" alt="" src="'+dataList[i].path+'" alt="">'+
                                         '<img class="greenTap" src="../../../../Public/images/greenTap.png"/>'+
                                         '<span class="order">'+dataList[i].number+'号</span>'+
                                         '<img src="../../../../Public/images/play.png" alt="" class="playVideo">'+
                                    '</div>'+
                                    '</div>'+
                                    '<p class="name">作品名称：<span>'+dataList[i].title+'</span></p>'+
                                    '<p>作品作者：<span>'+dataList[i].username+'</span></p>'+
                                    '<div class="text">'+
                                        '<span class="getNum"><strong>'+dataList[i].vote_count+'</strong>票</span>'+
                                        '<span class="vote">给TA投票</span>'+
                                    '</div>'+
                                '</li>'
                                if($('.voteLeft').height() > $('.voteRight').height()){
                                        $('.voteRight').append(chtm);
                                    }else{
                                        $('.voteLeft').append(chtm);
                                    }
                        } 
                        $(".vote").hide();
                        $(".getNum").css("float","none")
                        $(".text").css("textAlign","center")
                    }
                    if(data.data.voteStatus==2){
                        if(data.data.upload_type==1){
                            var chtml='<li onclick="toVote('+dataList[i].id+','+dataList[i].vote_id+')">'+
                                    '<div class="img">'+
                                         '<img class="lazy" alt="" src="'+dataList[i].path+'" alt="">'+
                                         '<img class="greenTap" src="../../../../Public/images/greenTap.png"/>'+
                                         '<span class="order">'+dataList[i].number+'号</span>'+
                                    '</div>'+
                                    '<p class="name">作品名称：<span>'+dataList[i].title+'</span></p>'+
                                    '<p>作品作者：<span>'+dataList[i].username+'</span></p>'+
                                    '<div class="text">'+
                                        '<span class="getNum"><strong>'+dataList[i].vote_count+'</strong>票</span>'+
                                        '<span class="vote">给TA投票</span>'+
                                    '</div>'+
                                '</li>'
                                 if($('.voteLeft').height() > $('.voteRight').height()){
                                        $('.voteRight').append(chtml);
                                    }else{
                                        $('.voteLeft').append(chtml);
                                    }
                        }
                        if(data.data.upload_type==2){
                            var chtm='<li onclick="toVote('+dataList[i].id+','+dataList[i].vote_id+')">'+
                                   '<div class="img">'+
                                         '<img class="lazy" alt="" src="'+dataList[i].path+'" alt="">'+
                                         '<img class="greenTap" src="../../../../Public/images/greenTap.png"/>'+
                                         '<span class="order">'+dataList[i].number+'号</span>'+
                                        '<img src="../../../../Public/images/play.png" alt="" class="playVideo">'+
                                    '</div>'+
                                    '<p class="name">作品名称：<span>'+dataList[i].title+'</span></p>'+
                                    '<p>作品作者：<span>'+dataList[i].username+'</span></p>'+
                                    '<div class="text">'+
                                        '<span class="getNum"><strong>'+dataList[i].vote_count+'</strong>票</span>'+
                                        '<span class="vote">给TA投票</span>'+
                                    '</div>'+
                                '</li>'
                                if($('.voteLeft').height() > $('.voteRight').height()){
                                        $('.voteRight').append(chtm);
                                    }else{
                                        $('.voteLeft').append(chtm);
                                    }
                        } 
                    }
                }
            }
            
               
            }
            
        }
    })
    $.ajax({
        type:"post",
        url:"/index.php/Vote/lookVoteCount",
        async: true,
        dataType:"json",
        data:{
        },
        success:function(data){
            $(".num").html(data.data.count)
        }
    })
function toVote(id,vote_id){
        window.location.href="/index.php/Vote/voteDetail.html?id="+id+"&vote_id="+vote_id;
    }
       //商品数量加操作
function adds(obj){
    var voteNum=$(".comfirm .integral").text()
    var prd_num = obj.previousElementSibling;   
    var num = prd_num.value;
    
    if(isNaN(num)){
        num = 1;
    }else{
        num = parseInt(num);
    }
    
    num+=1;
    if(num>voteNum){
        num=voteNum
    }
    if(voteNum==0){
        num=0
    }
    prd_num.value = num;
}
//商品数量改变的操作
function numChanges(obj){
    var num = obj.value;
    if(isNaN(num)){
        num = 1;
    }else if(num==""){
        num=1;
    }else if(num<1){
        num=1;
    }
    num = parseInt(num);
    obj.value = num;
}
//商品数量减操作
function reduces(obj){
    var prd_num = obj.nextElementSibling;
    var num = prd_num.value;
    if(isNaN(num)){
        num = 1;
    }else{
        num = parseInt(num);
    }
    
    num-=1;
    if(num<1){
        num=0
    }
    prd_num.value = num;
}   
var bool=true;
     $(".confirms").click(function(){
         var count=$(".numbers").val()
         if(bool){
            bool=false;
            $.ajax({
              type: "post",  
              url:"/index.php/Vote/exchange",  
              async: true, 
              dataType:"json",
              data:{
                count:count
              },
              success:function(data){
                if(data.status==1){
                    bool=true;
                    alert(data.message)
                    $(".tellBox").hide()
                    window.location.href="/index.php/Vote/userVote.html?vote_id="+voteId
                }else{
                    bool=true;
                    alert(data.message)
                    // $(".tellBox").hide()
                }
                
              },
            });
         }
        
    })