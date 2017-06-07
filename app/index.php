<?php
if(file_exists(__ROOT__.'validated')){
	$input = xml2array(file_get_contents("php://input")); //获取请求参数
	echo WeixinDev::response($input); //响应请求
}else{
	WeixinDev::validate(); //验证公众号
}