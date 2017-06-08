<?php
/**
 * 微信回复
 */
$hello = array(
	"你好，请问你想要了解什么？",
	'礼貌伴随你我他，发送你好可了解相关信息。',
	'试试发送一些有意义的消息，如“校车 雁山到育才”。',
	'试试发送一些有意义的消息，如“雁山到育才的公交车”。',
	'试试发送一些有意义的消息，如“四级成绩 张三 451081162210415”。',
	'试试发送一些有意义的消息，如“查看新闻”。',
	'试试发送一些有意义的消息，如“讲个冷笑话”。',
	'试试发送一些有意义的消息，如“介绍一下爱因斯坦。”。',
	'试试发送一些有意义的消息，如“你知道成龙吗？”。',
	);
return array(
	'invalidMsgType' => "不支持的消息类型，请发送文本或语音消息。", //不支持的消息类型回复
	'subscribeReply' => '你好，请问你想要了解什么？', //关注后回复
	'dataOutLimit' => '匹配的数据太多了，请尝试发送更加精确的消息。', //包含数据超出限制回复
	'noMatchedData' => array_merge(array( //无匹配数据回复
		'没有找到你想要的信息。',
		'不懂你说的是什么。',
		'请原谅我知识面不够广。',
		'这个我真的不懂。',
		'对不起，我的能力有限。',
		'这个问题超出了我的认知水平。',
		'请不要为难我。',
		'或者我们应该换个话题。',
		'你难道不考虑换个话题吗？',
		'不是我不想回答你，实在是我不懂啊。',
		), $hello),
	'你好' => $hello,
	'嗯' => array(
		'然后呢？',
		'接着说啊。',
		'/::>',
		),
	'呵呵' => array(
		'嗯。',
		'呵呵。',
		'呵呵哒。',
		),
	'不知道' => array(
		'我也不知道，但也请不要笑话我。',
		'或者我们应该换个话题。',
		),
	'校车' => '试试发送如“校车 雁山到育才”。',
	'公交车' => '试试发送如“雁山到育才的公交车”。',
	'四级' => '试试发送如“四级成绩 张三 451081162210415”。',
	'六级' => '试试发送如“六级成绩 张三 451081162210415”。',
	'命令' => "你可以使用这些推荐的方式来获取相关信息哦！如：\n校车 雁山到育才\n雁山到育才的公交车\n四级成绩 张三 451081162210415\n查看新闻\n...",
	'笑话' => '暂时不能提供该类服务。',
	'文章' => '暂时不能提供该类服务。',
	'新闻' => '暂时不能提供该类服务。',
	'电影' => '暂时不能提供该类服务。',
	'你是机器' => '我是阿远开发的在线聊天机器人，可以为你提供相应问题的参考答案。',
	'阿远' => $hello,
	'我在哪'=>array(
		'这个你得看地图。',
		'问问你附近的人。',
		'安装一个地图应用试试。',
		),
	);