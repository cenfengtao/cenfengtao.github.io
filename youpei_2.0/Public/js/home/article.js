var itemL=$(".list-card-timeline li").length;
for(var i=0; i<itemL; i++){
	var color=document.getElementsByClassName("dt")[i].innerHTML;
	if(color=="育儿"){
   document.getElementsByClassName("dt")[i].style.backgroundColor="#ff6666";
   }
   if(color=="教育"){
      document.getElementsByClassName("dt")[i].style.backgroundColor="#66cccc";
   }
   if(color=="视频"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#66ccff";
   }
   if(color=="福利"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ff0033";
   }
   if(color=="精品"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ffcc00";
   }
   if(color=="社会"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#cc9966";
   }
   if(color=="故事"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ff9999";
   }
   if(color=="实验"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#009966";
   }
   if(color=="绘本"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ff99cc";
   }
    if(color=="专家"){
        document.getElementsByClassName("dt")[i].style.backgroundColor="#CA59EF";
    }
}
$(window).load(function (){
    setTimeout(function () {
        $('.kap-bottom').show();
    },2000)
});


function getArticle(id) {
    window.location.href = "/index.php/Article/getArticle?art_id=" + id;
}
$.ajax({
  type:"get",
  url:"/index.php/Article/ajaxIndex",
  async: true, 
  dataType:"json",
  data:{
    cate_id:0
  },
  success:function(data){
    if (data.status==1) {
      var cateList=data.data.cateList;
      var articleList=data.data.articleList;
      var cateListLenght=cateList.length;
      var articleListLenght=articleList.length;
     for(var i=0; i<cateListLenght; i++){
      $(".cateList").append(
           '<span attr_id="'+cateList[i].id+'" onclick="getCateListById('+cateList[i].id+')">'+cateList[i].title+'</span>'
        )
     }
     $(".cateList span").eq(0).addClass("on").html("全部")
     $(".cateList span").click(function(){
        $(this).addClass("on").siblings().removeClass("on")
     })
     for(var j=0; j<articleListLenght; j++){
        $(".lists").append(
         ' <li class="item" data-ty-target="#detail_activity" onclick="getArticle('+articleList[j].id+')" style="min-height: 4rem;">'+
            '<img class="thumb" src="'+articleList[j].image+'" alt="" style="width: 6rem;height: 4rem;">'+
            '<span class="desc">'+
                '<h3 class="title">'+articleList[j].title+'</h3>'+
                '<span class="time">'+
                  '<span class="lef">'+articleList[j].time+'</span>'+
                  '<span class="rig">评论（'+articleList[j].count+'）'+
                  '<i></i> 收藏（'+articleList[j].collect+'）</span>'+
                '</span>'+
                '<span class="status dt">'+articleList[j].cate_title+'</span>'+
            '</span>'+
            '<span class="icon-sale"></span>'+
          '</li>'
          )
     }
    } 
    var itemL=$(".list-card-timeline li").length;
for(var i=0; i<itemL; i++){
	var color=document.getElementsByClassName("dt")[i].innerHTML;
	if(color=="育儿"){
   document.getElementsByClassName("dt")[i].style.backgroundColor="#ff6666";
   }
   if(color=="教育"){
      document.getElementsByClassName("dt")[i].style.backgroundColor="#66cccc";
   }
   if(color=="视频"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#66ccff";
   }
   if(color=="福利"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ff0033";
   }
   if(color=="精品"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ffcc00";
   }
   if(color=="社会"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#cc9966";
   }
   if(color=="故事"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ff9999";
   }
   if(color=="实验"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#009966";
   }
   if(color=="绘本"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ff99cc";
   }
    if(color=="专家"){
        document.getElementsByClassName("dt")[i].style.backgroundColor="#CA59EF";
    }
}
  }
})

function getCateListById(id) {
	layer.load();
    setTimeout(function () {
        layer.closeAll('loading');
    }, 1000);
    // window.location.href = "/index.php/Article/index?cate_id=" + id;
    $(".lists").html("");
    $.ajax({
    type:"get",
    url:"/index.php/Article/ajaxIndex",
    async: true, 
    dataType:"json",
    data:{
      cate_id:id
    },
    success:function(data){
      $(".cateList span").click(function(){
        $(this).addClass("on").siblings().removeClass("on")
     })
      if (data.status==1) {
          $("#page").val(10);
          $(".lists").html("");
        var articleList=data.data.articleList;
        var articleListLenght=articleList.length;
          if(articleList == ''){
              $(".empty").remove();
              return $('.list').append("<li class='empty'>暂无内容</li>");
          }
       for(var j=0; j<articleListLenght; j++){
          $(".lists").append(
           ' <li class="item" data-ty-target="#detail_activity" onclick="getArticle('+articleList[j].id+')" style="min-height: 4rem;">'+
              '<img class="thumb" src="'+articleList[j].image+'" alt="" style="width: 6rem;height: 4rem;">'+
              '<span class="desc">'+
                  '<h3 class="title">'+articleList[j].title+'</h3>'+
                  '<span class="time">'+
                    '<span class="lef">'+articleList[j].time+'</span>'+
                    '<span class="rig">评论（'+articleList[j].count+'）'+
                    '<i></i> 收藏（'+articleList[j].collect+'）</span>'+
                  '</span>'+
                  '<span class="status dt">'+articleList[j].cate_title+'</span>'+
              '</span>'+
              '<span class="icon-sale"></span>'+
            '</li>'
            )
       }
      }
      var itemL=$(".list-card-timeline li").length;
for(var i=0; i<itemL; i++){
	var color=document.getElementsByClassName("dt")[i].innerHTML;
	if(color=="育儿"){
   document.getElementsByClassName("dt")[i].style.backgroundColor="#ff6666";
   }
   if(color=="教育"){
      document.getElementsByClassName("dt")[i].style.backgroundColor="#66cccc";
   }
   if(color=="视频"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#66ccff";
   }
   if(color=="福利"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ff0033";
   }
   if(color=="精品"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ffcc00";
   }
   if(color=="社会"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#cc9966";
   }
   if(color=="故事"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ff9999";
   }
   if(color=="实验"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#009966";
   }
   if(color=="绘本"){
       document.getElementsByClassName("dt")[i].style.backgroundColor="#ff99cc";
   }
    if(color=="专家"){
        document.getElementsByClassName("dt")[i].style.backgroundColor="#CA59EF";
    }
}
    }
  })
}

$(function () {
    $(window).scroll(function () {
        var npage = parseInt($("#page").val());
        var scrollTop = $(this).scrollTop();
        var scrollHeight = $(document).height();
        var windowHeight = $(this).height();
        var cate_id = $('.ty-cat-tags .on').attr('attr_id');
        if (scrollTop + windowHeight == scrollHeight) {
            //加载层
            layer.load();
            setTimeout(function () {
                layer.closeAll('loading');
            }, 1000);
            var url = "/index.php/Article/loadingProduct";
            var params = {};
            params.cate_id = cate_id;
            params.npage = npage;
            $.post(url, params, function (result) {

                if (result.status == 0) {
                    $(".empty").remove();
                    return $('.list').append("<li class='empty'>没有更多了</li>");
                }

                $.each(result.data, function (index, value) {
                    if(value['collect'] == null){
                        collect = 0;
                    }else{
                        collect = value['collect'];
                    }
                    if(value['count'] == null){
                        num = 0;
                    }else{
                        num = value['count'];
                    }
                    $(".lists").append("<li class='item' onclick='getArticle( " + value['id'] + ")' style='min-height: 4rem;'>" +
                            "<img class='thumb' src='" + value['image'] + "' style='width: 6rem;height: 4rem;'>" +
                            "<span class='desc'>" +
                            "<h3 class='title'>" + value['title'] + "</h3>" +
                            "<span class='time'><span class='lef'>" + value['time'] + "</span>" +
                            "<span class='rig'>评论（" + num + "） <i></i> 收藏（" + collect + "）</span></span>" +
                            "<span class='status dt'>" + value['cate_title'] + "</span>" +
                            "</span>" +
                            "<span class='icon-sale'></span>" +
                            "</li>");
                });
      var itemL=$(".list-card-timeline li").length;
      for(var i=0; i<itemL; i++){
        var color=document.getElementsByClassName("dt")[i].innerHTML;
        if(color=="育儿"){
             document.getElementsByClassName("dt")[i].style.backgroundColor="#ff6666";
           }
           if(color=="教育"){
              document.getElementsByClassName("dt")[i].style.backgroundColor="#66cccc";
           }
           if(color=="视频"){
               document.getElementsByClassName("dt")[i].style.backgroundColor="#66ccff";
           }
           if(color=="福利"){
               document.getElementsByClassName("dt")[i].style.backgroundColor="#ff0033";
           }
           if(color=="精品"){
               document.getElementsByClassName("dt")[i].style.backgroundColor="#ffcc00";
           }
           if(color=="社会"){
               document.getElementsByClassName("dt")[i].style.backgroundColor="#cc9966";
           }
           if(color=="故事"){
               document.getElementsByClassName("dt")[i].style.backgroundColor="#ff9999";
           }
           if(color=="实验"){
               document.getElementsByClassName("dt")[i].style.backgroundColor="#009966";
           }
           if(color=="绘本"){
               document.getElementsByClassName("dt")[i].style.backgroundColor="#ff99cc";
           }
        }   
            })
            npage += 6;
            $("#page").val(npage);
        }
    });
});