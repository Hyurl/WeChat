<?php
if(__SCRIPT__ == 'mod.php' && session_status() != PHP_SESSION_ACTIVE)
	session_start(); //启动会话

$WxConf = load_config_file('weixin-config.php'); //配置
$WxRes = load_config_file('weixin-response.php'); //响应数据
WeixinDev::$url = $WxConf['url'];
WeixinDev::$token = $WxConf['token'];
WeixinDev::$appId = $WxConf['appId'];
WeixinDev::$appSecret = $WxConf['appSecret'];

/**
 * str_contents() 判断字符串中是否包含指定的字符串
 * @param  string       $str      待检测的字符串
 * @param  string|array $contents 子字符串列表
 * @param  boolean $case          大小写敏感
 * @return bool
 */
function str_contents($str, $contents, $case = false){
	if(!is_array($contents)) $contents = array($contents);
	$func = $case ? 'strpos' : 'stripos';
	foreach ($contents as $value) {
		if($func($str, $value) !== false) return true;
	}
	return false;
}

if(file_exists($file = __dir__.'/extra.func.php'))
	include $file; //载入额外程序

/** 查询四六级成绩 */
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$sendContent = &$input['send']['Content'];
	if($input['send']['MsgType'] == 'text' && $sendContent) return; //如果已有回复，则不再继续执行
	if(strpos($recvContent, '四级成绩') !== false || strpos($recvContent, '六级成绩') !== false){
		$_recvContent = str_replace(array("\r\n", "\n", "  ", ",", "，", '-'), " ", $recvContent); // 将标点替换成空格
		$recv = parse_cli_str($_recvContent); //解析命令
		$type = strpos($recv[0], '六级成绩') !== false ? '六' : '四'; //查询类型
		if(in_array($recv[0], array('四级成绩', '六级成绩')) && isset($recv[1], $recv[2]) && is_numeric($recv[2])){
			$result = curl(array( //CURL 请求
				'url'=>"http://www.chsi.com.cn/cet/query?zkzh={$recv[2]}&xm=".urlencode($recv[1]),
				'referer' => 'http://www.chsi.com.cn/cet/',
				));
			// 获取查询结果
			$start = '<h2>2016年下半年全国大学英语四、六级考试(含口试)成绩查询结果</h2>';
			$startStr = '<h2>2016年下半年全国大学英语四、六级考试(含口试)成绩查询结果</h2>';
			$noScore = '无法找到对应的分数';
			$start = strpos($result, $startStr);
			if($start === false || strpos($result, $noScore)){
				$sendContent = "无法查询到成绩，请确认你发送的准考证号及姓名无误。";
			}else{
				$start += strlen($startStr);
				$len = strpos($result, '注：最终结果请以《成绩报告单》为准') - $start;
				$result = str_replace(array(' ', "\t", "&nbsp;"), "", strip_tags(substr($result, $start, $len)));
				$result = trim(preg_replace("/[\r\n]+/", "\n", $result));
				$result = str_replace("：\n", ": ", $result);
				$sendContent = $result;
			}
		}else{
			$sendContent = '请发送如“'.$type.'级成绩 张三 451081162210415”。';
		}
		return $input;
	}
});

/** 查询校车 */
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$sendContent = &$input['send']['Content'];
	if($input['send']['MsgType'] == 'text' && $sendContent) return;
	$recvContent = str_replace(array("\r\n", "\n", "  ", ",", "，", '-'), " ", $recvContent);
	if(strpos($recvContent, ',')){
		$recv = explode(',', $recvContent);
	}elseif(strpos($recvContent, '，')){
		$recv = explode('，', $recvContent);
	}elseif(strpos($recvContent, '的')){
		$recv = explode("的", $recvContent);
	}else{
		$recv = explode(' ', $recvContent);
	}
	if(isset($recv[1]) && strpos($recv[0], '校车') === false && strpos($recv[1], '校车') !== false){
		$_recv = $recv;
		$recv[0] = $_recv[1];
		$recv[1] = $_recv[0];
	}
	if(strpos($recv[0], '校车') !== false){
		$busTable = include template_path('school-bus.php');
		$weekday = date('w');
		$isWeekend = $weekday == 0 || $weekday == 6;
		if(isset($recv[1])){
			if(strpos($recv[1], "王城到育才") !== false || strpos($recv[1], '王城到雁山') !== false){
				$timeTable = $busTable['wangcheng-yucai'];
			}elseif(strpos($recv[1], "育才到雁山") !== false){
				$timeTable = $busTable['yucai-yanshan'];
			}elseif(strpos($recv[1], '雁山到育才') !== false){
				$timeTable = $busTable['yanshan-yucai'];
			}elseif(strpos($recv[1], "育才到王城") !== false){
				$timeTable = $busTable['yucai-wangcheng'];
			}elseif(strpos($recv[1], '雁山到王城') !== false){
				$timeTable = $isWeekend ? array('weekend'=>array()) : $busTable['yanshan-yucai'];
			}else{
				$incorrect = true;
			}
			$timeTable = $isWeekend ? $timeTable['weekend'] : $timeTable['weekdays'];
			if(empty($timeTable)){
				$sendContent = $isWeekend && !isset($incorrect) ? '今日没有所查询的车次。' : '请发送如“校车 雁山到育才”。';
			}else{
				$times = array_map(function($v){
					return strtotime($v);
				}, $timeTable);
				for($i=0; $i<count($times); $i++) {
					if($times[$i] >= time()){
						$timeTable[$i] .= "(最近)";
						break;
					}
				}
				$sendContent = "所查询的今日车次如下：\n".implode("\n", $timeTable)."\n可能存在误差，请提前候车。";
			}
		}else{
			$sendContent = '请发送如“校车 雁山到育才”。';
		}
		return $input;
	}
});

/** 查询98路公交 */
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$sendContent = &$input['send']['Content'];
	if($input['send']['MsgType'] == 'text' && $sendContent) return;
	if(strpos($recvContent, '98路') !== false || (strpos($recvContent, '雁山') !== false && strpos($recvContent, '公交') !== false)){
		$ycToYs = strpos($recvContent, '育才到雁山') !== false;
		$timeTable = include template_path('98-bus.php');
		$times = array_map(function($v){
			return array(strtotime($v[0]), strtotime($v[1]));
		}, $timeTable);
		$str = "育才(南门) --- 雁山(一区教学楼前)\n";
		$noted = false;
		for($i=0; $i<count($timeTable); $i++){
			$str .= $timeTable[$i][0]." --- ".$timeTable[$i][1];
			if((($times[$i][0] >= time() && $ycToYs) || ($times[$i][1] >= time() && !$ycToYs)) && !$noted){
				$str .= "(最近)\n";
				$noted = true;
			}else{
				$str .= "\n";
			}
		}
		$sendContent = "98路公交车的运行时刻表如下：\n".$str."可能存在误差，请提前候车。";
		return $input;
	}
});

/** 冷笑话 */
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$sendContent = &$input['send']['Content'];
	if($input['send']['MsgType'] == 'text' && $sendContent) return;
	$history = WeixinDev::getHistory($input['recv']['FromUserName']); //获取对话历史
	$history = isset($history[1]) ? $history[1] : '';
	$anotherOne = strpos($recvContent, '再来') !== false && strpos($history, '笑话') !== false; //将“再来”识别为“笑话”
	if(strpos($recvContent, '笑话') !== false || $anotherOne){
		if($anotherOne) WeixinDev::setHistory($input['recv']['FromUserName'], $history); //设置历史纪录
		$result = curl('http://xhkong.com/tag/'); //获取标签
		if(preg_match_all('/<span(.*)<a href="\/tag\/(.*)" target="_blank">(.*)<\/a><\/span>/Ui', $result, $matches)){
			$tag = $matches[2][rand(0, count($matches[2])-1)];
			$result = curl('http://xhkong.com/tag/'.$tag);
			//获取冷笑话列表
			if(preg_match_all('/<article class="xhk-list" id="(.*)">([\s\S]+)<\/article>/Ui', $result, $matches)){
				$result = $matches[2][rand(0, count($matches[2])-1)];
				preg_match('/<header class="title">([\s\S]+)<\/header>/', $result, $match);
				$title = trim(strip_tags($match[1]));
				preg_match('/<header class="title">[\s\S]+href="(.+)"[\s\S]+<\/header>/', $result, $match);
				$url = 'http://xhkong.com'.$match[1];
				if(preg_match('/<img src="(.*)"/Ui', $result, $match)){
					// 图文消息
					$imgSrc = $match[1];
					$input['send']['MsgType'] = 'news';
					$input['send']['ArticleCount'] = 1;
					$input['send']['Articles'] = array(
						'item' => array(
							'Title'=>$title,
							'Description'=>'',
							'PicUrl'=>$imgSrc,
							'Url'=>$url,
							),
						);
				}elseif(preg_match('/<div class="con"><p>([\s\S]+)<\/p><\/div>/Ui', $result, $match)){
					$sendContent = trim(str_replace('</p><p>', "\n", $match[1])); //纯文本消息
				}
				return $input;
			}
		}
	}
});

/** 美文推荐 */
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$sendContent = &$input['send']['Content'];
	if(($input['send']['MsgType'] == 'text' && $sendContent) || !empty($input['send']['Articles'])) return;
	$history = WeixinDev::getHistory($input['recv']['FromUserName']);
	$history = isset($history[1]) ? $history[1] : '';
	$anotherOne = (strpos($history, '美文') !== false || strpos($history, '文章') !== false) && (strpos($recvContent, '再来') !== false || strpos($recvContent, '再来一篇') !== false);
	if(strpos($recvContent, '美文') !== false || strpos($recvContent, '文章') !== false || $anotherOne){
		if($anotherOne) WeixinDev::setHistory($input['recv']['FromUserName'], $history);
		$baseUrl = 'https://www.lookmw.cn';
		$result = curl(array(
			'url'=>$baseUrl,
			'charset'=>'UTF-8',
			));
		if(preg_match_all('/<ul class="picAtc pl">([\s\S]*)<\/ul>/Ui', $result, $matches)){
			$result = trim($matches[1][rand(0, count($matches[1])-1)]);
			preg_match('/<a href="(.+)"(.+)title="(.+)"/Ui', $result, $match);
			$uri = $match[1];
			$title = $match[3];
			preg_match('/<img data-original="(.+)"/Ui', $result, $match);
			$imgSrc = $match[1];
			preg_match('/<p>([\s\S]*)<\/p>/Ui', $result, $match);
			$desc = $match[1].'...';
			// 回复图文消息
			$input['send']['MsgType'] = 'news';
			$input['send']['ArticleCount'] = 1;
			$input['send']['Articles'] = array(
				'item' => array(
					'Title'=>$title,
					'Description'=>$desc,
					'PicUrl'=>$imgSrc,
					'Url'=>site_url('lookmw.php?uri=').$uri, //本地详情页面
					),
				);
			return $input;
		}
	}
});

/** 新闻 */
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$sendContent = &$input['send']['Content'];
	if(($input['send']['MsgType'] == 'text' && $sendContent) || !empty($input['send']['Articles'])) return;
	if(strpos($recvContent, '新闻') !== false || strpos($recvContent, '资讯') !== false){
		$result = curl(array(
			'url'=>'http://news.sina.cn/',
			'userAgent'=>'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
			'followLocation'=>1,
			));
		if(preg_match('/<section class="card_module" id="top_feed" data-sudaclick="feedlist_conf_4">([\s\S]*)<\/section>/Ui', $result, $match)){
			if(preg_match_all('/<a href="(.*)"(.*)>([\s\S]*)<\/a>/Ui', $match[1], $matches)){ //获取新闻列表
				$urls = array_map(function($v){ //新闻链接
					return substr($v, 0, strpos($v, '?'));
				}, $matches[1]);
				$imgs = $titles = array();
				foreach ($matches[3] as $html) { //获取列表数据
					if(preg_match('/<img(.*)data-src="(.*)"(.*)alt="(.*)">/Ui', $html, $match)){
						$imgs[] = $match[2]; //缩略图
						$titles[] = $match[4]; //标题
					}
				}
				$start = rand(0, count($urls)-5); //随机开始抽取新闻
				$input['send']['MsgType'] = 'news';
				$input['send']['ArticleCount'] = 4; //4条消息
				$input['send']['Articles'] = array();
				$input['send']['Articles']['item'] = array();
				for ($i=$start; $i < $start+4; $i++) { 
					$input['send']['Articles']['item'][] = array( //图文消息列表
						'Title'=>$titles[$i],
						'Description'=>'',
						'PicUrl'=>$imgs[$i],
						'Url'=>$urls[$i],
					);
				}
				return $input;
			}
		}
	}
});

/** 电影 */
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$sendContent = &$input['send']['Content'];
	if(($input['send']['MsgType'] == 'text' && $sendContent) || !empty($input['send']['Articles'])) return;
	if(strpos($recvContent, '电影') !== false || strpos($recvContent, '影片') !== false){
		$baseUrl = 'https://mdianying.baidu.com';
		$result = curl(array( //CURL 请求
			'url'=>$baseUrl.'/?sfrom=newnuomi&source=nuomi&c=142&sub_channel=nuomi_wap_rukou4',
			'userAgent'=>'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
			'followLocation'=>1,
			));
		if(preg_match_all('/<a class="item" href="\/movie\/detail\?movieId=([\s\S]*)<\/a>/Ui', $result, $matches)){ //获取电影列表
			$urls = $imgs = $titles = array();
			foreach ($matches[0] as $html) { //获取列表数据
				if(preg_match('/<a class="item" href="(.*)"/Ui', $html, $match) && preg_match('/url\((.*)\);/Ui', $html, $match2)){
					$urls[] = $baseUrl.$match[1]; //电影链接
					$url = substr($match2[1], strpos($match2[1], '?')+1);
					parse_str($url, $get);
					$imgs[] = $get['src']; //电影图片
					preg_match('/ <p class="movie-name">(.*)<\/p>/', $html, $match);
					$titles[] = $match[1]; //标题
				}
			}
			if(!empty($urls)){
				$count = count($urls);
				$input['send']['MsgType'] = 'news';
				$input['send']['ArticleCount'] = $count;
				$input['send']['Articles'] = array();
				$input['send']['Articles']['item'] = array();
				for ($i=0; $i < $count; $i++) { 
					$input['send']['Articles']['item'][] = array( //多则图文消息
						'Title'=>$titles[$i],
						'Description'=>'',
						'PicUrl'=>$imgs[$i],
						'Url'=>$urls[$i],
					);
				}
				return $input;
			}
		}
	}
});

/** MD5 加密/解密 */
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$sendContent = &$input['send']['Content'];
	if(($input['send']['MsgType'] == 'text' && $sendContent) || !empty($input['send']['Articles'])) return;
	if(stripos($recvContent, 'MD5') !== false){
		$_recvContent = str_replace(array("\r\n", "\n", "  ", ",", "，", '-'), " ", $recvContent); //更改标点符号为空格
		$recv = parse_cli_str($_recvContent); //解析命令
		if(isset($recv[1])){
			if(strlen($recv[1]) == 32 && strlen($recv[1]) == mb_strlen($recv[1])){ //解密
				$html = curl('http://pmd5.com/');
				if(preg_match('/__VIEWSTATE[\s\S]*value=["\'](\S*)["\']/U', $html, $match)){
					if(preg_match('/__EVENTVALIDATION[\s\S]*value=["\'](\S*)["\']/U', $html, $_match)){
						$html = curl(array( //CURL 远程请求
							'url'=>'http://pmd5.com/',
							'method'=>'post', // POST 请求
							'data'=>array(
								'key'=>$recv[1],
								'jiemi'=>'MD5解密',
								'__VIEWSTATE'=>$match[1],
								'__EVENTVALIDATION'=>$_match[1]
								),
							'userAgent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.113 Safari/537.36',
							));
						if(preg_match_all('/<em>(\S*)<\/em>/U', $html, $match) && count($match[1]) == 2){
							$sendContent = "解密结果为: ".$match[1][1];
						}
					}
				}
				$sendContent = $sendContent ?: '暂时无法解开这个密码。';
			}else{ //加密
				$sendContent = '加密结果为: '.md5($recv[1]);
			}
		}elseif(!rand(0,3)){
			$sendContent = '请尝试发送如“MD5 202cb962ac59075b964b07152d234b70”或者“MD5 "123"”';
		}
		return $input;
	}
});

// 使用百度获取答案
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$sendContent = &$input['send']['Content'];
	if(($input['send']['MsgType'] == 'text' && $sendContent) || !empty($input['send']['Articles'])) return;
	global $WxRes;
	$keys = array_keys($WxRes);
	if(!str_contents($recvContent, $keys) || !rand(0, 3)){ // 1/4 概率忽略本地答案而从百度获取
		// 获取关键字
		if(!rand(0, 3) && preg_match('/什么叫做(.*)/', $recvContent, $match)){
			$wd = rtrim($match[1], '吗么麽吧啊？');
		}elseif(preg_match('/(什么叫|什么是|啥叫|啥是|谁是|哪个是)(.*)/', $recvContent, $match)){
			$wd = rtrim($match[2], '吗么麽吧啊？');
		}elseif(preg_match('/(知道|了解|认识|听说过|晓得|懂|会|看过|见过)(.*)(吗|么|麽|吧|啊)/U', $recvContent, $match)){
			$wd = $match[2];
		}elseif(preg_match('/(.*)是(什么|谁|哪|啥|何|？)/U', $recvContent, $match)){
			$wd = $match[1];
		}else{
			$wd = $recvContent;
		}
		// 请求参数
		$arg = array(
			'url'=>'http://baike.baidu.com/search/word?word='.urlencode(trim($wd)),
			'followLocation'=>5, //最多跟踪 5 次跳转
			'requestHeaders'=>array(
				'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
				'Referer'=>'https://www.baidu.com/',
				'User-Agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
				),
			);
		// 百度百科
		if(($wd != $recvContent || !rand(0,3))) { // 1/4 概率会忽略配置而从百度百科获取知识
			$data = curl($arg); //CURL 远程请求
			if(!curl_info('error') && preg_match('/<meta name="description" content="(.*)">/U', $data, $match)){ //获取摘要
				if(strpos($match[1], '百度百科') !== 0 && !strpos($match[1], '分享贡献你的知识'))
					$sendContent = trim(substr($match[1], 0, strrpos($match[1], '。')+3)); //将摘要作为答案
				if(strlen($sendContent) <= 3) //答案少于2中文长度则忽略
					$sendContent = null;
			}
		}
		// 百度知道
		if(!$sendContent || !rand(0, 3)){ // 1/4 概率摒弃百度百科答案而从百度知道获取
			$wd = trim($wd);
			$arg['url'] = 'http://zhidao.baidu.com/search?word='.urlencode($wd);
			$data = curl($arg);
			if(!curl_info('error') && preg_match_all('/<dl.*>([\s\S]*)<\/dl>/U', $data, $matches)){ //获取答案列表
				array_walk($matches[1], function(&$v){
					$v = trim(strip_tags($v)); //获取纯文本
				});
				$data = array();
				foreach ($matches[1] as $k => $match) {
					$match = str_replace("\r\n", "\n", $match);
					if(($i = strpos($match, "推荐答案")) !== false){ //有推荐答案
						$start = $i+strlen("推荐答案");
						$length = strrpos($match, '。')-$start;
						if($length <= 0)
							$length = strrpos($match, '...')-$start;
						if($length <= 0)
							$length = strpos($match, "[详细]")-$start-3;
						if($length > 0){
							$sendContent = trim(substr($match, $start, $length+3));
						}else{
							$sendContent = trim(substr($match, $start));
						}
						break;
					}else{
						$match = ltrim(substr($match, strpos($match, '">')+2)) ?: $match;
						$title = rtrim(substr($match, 0, strpos($match, "\n")+1));
						$i = strpos($match, '答：');
						if($i !== false){ //获取网友回答
							$match = substr($match, $i+strlen("答："));
							$match = trim(substr($match, 0, strpos($match, "\n\n"))); //获取回答
							if(strpos($match, '你好， 3.0版本') !== 0 && strpos($match, 'Du知道君是') !== 0){ //只使用有效回答
								if(strlen($match) > 3){ //只选择大于 1 个中文的答案
									// $index = abs(strcasecmp($title, $wd));
									// $data[$index] = $match;
									$data[] = $match;
								}
							}
						}
					}
				}
				if(!$sendContent && $data){
					// ksort($data);
					// echo $sendContent = array_shift($data);
					$sendContent = $data[rand(0, count($data)-1)];
				}
			}
		}
		return $input;
	}
});

/** 多指令重定向 */
add_action('WeixinDev.message', function($input){
	$recvContent = &$input['recv']['Content'];
	$rand = rand(1, 10);
	if(strpos($recvContent, '指令') !== false || strpos($recvContent, '功能') !== false || ($rand == 10 && (strpos($recvContent, '你好') !== false || !$recvContent))){
		$recvContent = str_replace(array('指令', '功能', '你好'), '命令', $recvContent);
	}elseif(!$recvContent){
		$recvContent = '你好';
	}
	return $input;
});