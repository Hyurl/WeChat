<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
	<title>我的位置</title>
	<style>
	html,body,#container{width: 100%;height: 100%;overflow: hidden;padding: 0;margin: 0;}
	</style>
</head>
<body>
	<div id="container">
		<!-- 地图 -->
	</div>
	<script src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $WxConf['baiduMapKey'] ?>" type="text/javascript"></script>
	<script>
		window.onload = function(){
			navigator.geolocation.getCurrentPosition(function(result){
				var map = new BMap.Map('container');
				map.centerAndZoom(new BMap.Point(result.coords.longitude, result.coords.latitude), 16);
				map.enableScrollWheelZoom();
				map.enableKeyboard();
				map.addControl(new BMap.NavigationControl());
				map.addControl(new BMap.ScaleControl());
				map.addControl(new BMap.GeolocationControl());
				map.addControl(new BMap.MapTypeControl());
			});
		}
	</script>
</body>
</html>