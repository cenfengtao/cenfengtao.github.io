<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>jQuery自动轮播旋转木马插件</title>
	<link type="text/css" rel="stylesheet" href="css/carousel.css">
	<style type="text/css">
		.caroursel{margin:150px auto;}
		body{background-color: #2A2A2A;}
	</style>
	<!--[if IE]>
		<script src="http://libs.baidu.com/html5shiv/3.7/html5shiv.min.js"></script>
	<![endif]-->
</head>
<body>
	<article class="jq22-container">
		
		<div class = "caroursel poster-main" data-setting = '{
	        "width":1000,
	        "height":270,
	        "posterWidth":640,
	        "posterHeight":270,
	        "scale":0.8,
	        "dealy":"2000",
	        "algin":"middle"
	    }'>
	        <ul class = "poster-list">
	            <li class = "poster-item"><img src="image/a1.png" width = "100%" height="100%"></li>
	            <li class = "poster-item"><img src="image/a2.png" width = "100%" height="100%"></li>
	            <li class = "poster-item"><img src="image/a3.png" width = "100%" height="100%"></li>
	        </ul>
	        
            
	        <div class = "poster-btn poster-prev-btn"></div>
	        <div class = "poster-btn poster-next-btn"></div>

	    </div>
		
	</article>
	
	<script src="http://www.jq22.com/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/jquery.carousel.js"></script>
    <script>
        Caroursel.init($('.caroursel'))
    </script>
</body>
</html>