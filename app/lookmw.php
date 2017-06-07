<?php
$baseUrl = 'http://www.lookmw.cn';
$result = curl(array(
	'url'=>$baseUrl.$_GET['uri'],
	'charset'=>'UTF-8',
	));
$result = preg_replace('/<img(.*)src="\//', '<img src="'.$baseUrl.'/', $result);
if(preg_match('/<div class="article">([\s\S]*)<article>([\s\S]*)<\/article>/Ui', $result, $match)){
	$article = trim(iconv('GB2312', 'UTF-8', $match[2]));
	$article = preg_replace(array('/<ul class="diggts">([\s\S]*)<\/ul>/Ui', '/<ul class="pagelist">([\s\S]*)<\/ul>/Ui'), '', $article);
	preg_match('/<h1>(.*)<\/h1>/Ui', $match[1], $match);
	$title = iconv('GB2312', 'UTF-8', $match[1]);
	preg_match('/<div class="info">([\s\S]*)<\/div>/Ui', $result, $match);
	$info = iconv('GB2312', 'UTF-8', $match[0]);
	$info = preg_replace('/<span>点击:([\s\S]*)次([\s\S]*)<\/div>/Ui', '</div>', $info);
	$info = preg_replace('/<span>来源:(.*)<\/span>/Ui', '&nbsp;&nbsp;来源: 美文网&nbsp;&nbsp;', $info);
	$info = trim(strip_tags(str_replace(':', ': ', $info)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $title ?></title>
	<style>
	body{background-color: #fff;line-height: 1.4;}
	header{padding: 40px 20px 20px;}
	h1{text-align: center;}
	article{padding: 20px 20px 40px;}
	div.info{text-align: center;color: #777;}
	img{max-width: 100%;}
	</style>
</head>
<body>
	<header>
		<h1><?php echo $title ?></h1>
		<div class="info"><small><?php echo $info ?></small></div>
	</header>
	<article>
		<?php echo $article ?>
	</article>
</body>
</html>
<?php } ?>