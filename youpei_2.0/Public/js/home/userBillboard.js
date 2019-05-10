// 第三个li起隐藏头冠
$(".chart ul li:gt(2) .hat").hide();

// 底部半圆的切换
 $(".footer ul li>img").css("display","none");
$(".footer ul li>img:eq(3)").css("display","block");
$(" .footer ul li").click(function(){
    $(this).addClass('on').siblings().removeClass("on");
    var index=$(this).index();
    var liLen=$(".footer ul li").length;
    for(var i=0;i<=liLen-1;i++){
        $(".footer ul li>img").eq(i).css("display","none");
    }
    $(this).find("img:eq(0)").css("display","block");
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
var vote_id=GetRequest().vote_id;
$(".voteList").attr("href","/index.php/Vote/voteList.html?vote_id="+vote_id)
$(".userVote").attr("href","/index.php/Vote/userVote.html?vote_id="+vote_id)
$(".userWorks").attr("href","/index.php/Vote/userWorks.html?vote_id="+vote_id)
$(".userBillboard").attr("href","/index.php/Vote/userBillboard.html?vote_id="+vote_id)
  $.ajax({
        type:"get",
        url:"/index.php/Vote/ajaxUserBillboard",
        async: true,
        dataType:"json",
        data:{
          vote_id:vote_id
        },
        success:function(data){
          var titles=data.data.title;
            if(titles){
                $(".header h3").html(titles)
            }else{
                $(".header h3").html("榜单")
            }
          if(data.data.is_null==0){
            $(".chartList").append('<p style="text-align:center; font-size:0.5rem;margin-top:5rem;">还没有人入围哦，赶快去<a href="javascript:void(0);" onclick="toVote('+vote_id+')" style="text-decoration: underline;font-size:0.7rem; color:black;" class="judge">投稿</a>吧!</p>')
            if(data.data.voteStatus==3){
              var year=data.data.start_time.split("-")[0];
              var month=data.data.start_time.split("-")[1];
              var day=data.data.start_time.split("-")[2];
              $(".judge").attr("onclick","")
                $(".judge").click(function(){
                        alert("该活动未开始，将在"+year+"年"+month+"月"+day+"日开始，欢迎到时来参加！")
                    })
            }
            if(data.data.voteStatus==2){
              if(data.data.isContribute==1){
                $(".judge").attr("onclick","")
                $(".judge").click(function(){
                        alert("该活动已截止投稿，欢迎下次活动时来投稿！")
                    })
              }else{
                $(".judge").click(function(){
                        window.location.href="/index.php/Vote/contribution.html?vote_id="+vote_id;
                    })
                 
              }
            }
          }else{
              // console.log(data.data.voteStatus)
          if(data.data.voteStatus==1){
            $(".countdown").html("<h3>活动结束</h3><h3>欢迎下次来参与</h3>")
          }
          var list=data.data.list;
          var listLength=list.length;
          for(var i=0; i<listLength; i++){
            $(".chartList").append(
              '<li onclick="details('+list[i].id+')">'+
                 '<div class="img">'+
                     '<img src="../../../../Public/images/hat.png" alt="" class="hat">'+
                     '<img src="'+list[i].image+'" alt="" class="head">'+
                 '</div>'+
                 '<div class="text">'+
                     '<p><i></i>作品名称：<span>'+list[i].title+'</span></p>'+
                     '<p><i></i>作品作者：<span>'+list[i].username+'</span></p>'+
                     '<p><i></i>获得票数：<span class="count">'+list[i].vote_count+'</span>票</p>'+
                 '</div>'+
                 '<div class="order">'+
                     '<img src="../../../../Public/images/blue.png" alt="">'+
                     '<span class="num">'+list[i].ranking+'</span>'+
                 '</div>'+
             '</li>'
            )
             // $(".chart ul li:gt(2) .hat").hide();
            var num=document.getElementsByClassName("num")[i].innerHTML;
            if(num=="1"){
               $(".order").eq(i).find("img").attr("src","../../../../Public/images/prize.png")
                $(".hat").eq(i).show();
                $(".img").eq(i).css({"float":"left","margin-left":"0.56rem","margin-top":"0.77rem"})
                $(".hat").eq(i).show();
                $(".hat").eq(i).css({"position":"absolute","top":"-0.7rem","left":"0.15rem"})
            }
            if(num=="2"){
              $(".order").eq(i).find("img").attr("src","../../../../Public/images/silver.png")
              $(".hat").eq(i).show();
              $(".img").eq(i).css({"float":"left","margin-left":"0.56rem","margin-top":"0.77rem"})
              $(".hat").eq(i).show();
              $(".hat").eq(i).css({"position":"absolute","top":"-0.7rem","left":"0.15rem"})
            }
            if(num=="3"){
              $(".order").eq(i).find("img").attr("src","../../../../Public/images/bronze.png")
              $(".img").eq(i).css({"float":"left","margin-left":"0.56rem","margin-top":"0.77rem"})
              $(".hat").eq(i).show();
              $(".hat").eq(i).css({"position":"absolute","top":"-0.7rem","left":"0.15rem"})
            }
          }
          
          }
          $(".time").attr("value",data.data.endTime)
                 //倒计时
          var time_current = (new Date()).valueOf();//获取当前时间
          $(function () {
              var dateTime = new Date();
              var difference = dateTime.getTime() - time_current;
              setInterval(function () {
                      var endTime = new Date(parseInt($(".time").attr('value')) * 1000);
                      var nowTime = new Date();
                      var nMS = endTime.getTime() - nowTime.getTime() + difference;
                      var myD = Math.floor(nMS / (1000 * 60 * 60 * 24));
                      var myH = Math.floor(nMS / (1000 * 60 * 60)) % 24;
                      var myM = Math.floor(nMS / (1000 * 60)) % 60;
                      var myS = Math.floor(nMS / 1000) % 60;
                      var myMS = Math.floor(nMS / 100) % 10;  
                        $(".day").html(myD);
                        $(".hours").html(myH);
                        $(".minute").html(myM);   
                        $(".second").html(myS);
                        if(myM==-1){
                           $(".countdown").html("<h3>活动结束</h3><h3>欢迎下次来参与</h3>")
                        }
              }, 100);
          });         
          
        }
    })
  function details(id){
    window.location.href="/index.php/Vote/voteDetail.html?id="+id+"&vote_id="+vote_id;
  }
  function toVote(id){
     window.location.href="/index.php/Vote/contribution.html?vote_id="+vote_id;
  }