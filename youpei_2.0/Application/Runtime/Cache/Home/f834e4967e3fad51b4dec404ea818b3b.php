<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="favicon.ico"/>
<title><?php echo ($CONF['mallTitle']); ?>-系统提示</title>
	<meta name="description" content="" />
	<meta name="Keywords" content="" />


</head>
<body class="root61">
	
	<div class="w">
		<div>
			<div class="wst-sys-msg-pb">
					<div class="wst-sys-msg-cb">
						<div class="wst-sys-msg-vb">
							<?php if(count($orderInfos) > 0): ?><img src="/Apps/Home/View/<?php echo ($WST_STYLE); ?>/images/icon-succ.png" alt="" /><?php endif; ?>
						</div>
						<br/>
							<div class="wst-sys-msg-l25">				
	   							<div class="wst-sys-msg-ub"><?php echo ($msg); ?></div>
	   						</div>							
						<br/>
						<div style="clear: both;"></div>
					</div>					
					
					<div style="clear: both;"></div>
					<div style="margin-top:15px; ">			
						<div id="checkout" class="wst-checkout" >							
							<a class="btn-submit" href="/index.php">
								<span id="saveConsigneeTitleDiv" class="wst_btn-continue"></span>
							</a>
							<div style="clear: both;"></div>
						</div>
					</div>				
				</div>							
			</div>			
		</div>
	<div style="clear: both;"></div>
    <div style="height: 20px;"></div>

</body>
</html>