<?php
if($_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.0'){ //微信
	if(file_exists(__ROOT__.'validated')){
		$input = xml2array(file_get_contents("php://input")); //获取请求参数
		echo array2xml(WeixinDev::response($input)); //响应请求，输出 XML 数据
	}else{
		WeixinDev::validate(); //验证公众号
	}
}else{
	set_content_type('application/json');
	echo json_encode(WeixinDev::response($_REQUEST)); //响应请求，输出 JSON 数据
}