<?php 
/**
 * 微信开发者实现类
 */
class WeixinDev extends mod{
	const TABLE = '';
	const PRIMKEY = '';

	static $baseUrl = 'https://api.weixin.qq.com/';
	static $url = '';
	static $token = '';
	static $appId = '';
	static $appSecret = '';

	/** 判断 Token 是否可用 */
	private static function tokenValid(){
		return isset($_SESSION['weixin_api_access_token'], $_SESSION['weixin_api_access_token_expire']) && time() < $_SESSION['weixin_api_access_token_expire'];
	}

	/** result() 返回请求结果 */
	private static function result($result){
		return $result['errcode'] ? error($result['errmsg']) : success($result['errmsg']);
	}

	/**
	 * validate() 方法用来检验 url 地址可用性
	 * @return [type] [description]
	 */
	static function validate(){
		if(!isset($_GET['timestamp'], $_GET['nonce'], $_GET['signature'], $_GET["echostr"])) return false;
		$tmpArr = array(self::$token, $_GET['timestamp'], $_GET['nonce']);
		sort($tmpArr, SORT_STRING);
		if(sha1(implode($tmpArr)) == $_GET['signature']){
			$file = fopen(__ROOT__.'validated', 'w');
			fwrite($file, 'Validated! :)');
			fclose($file);
			echo $_GET["echostr"];
		}
	}

	/** 
	 * getAccessToken() 获取 AccessToken
	 * @return [type] [description]
	 */
	static function getAccessToken(){
		if(!self::tokenValid()){
			$result = curl(array(
				'url'=>self::$baseUrl.'cgi-bin/token',
				'data'=>array(
					'grant_type'=>'client_credential',
					'appid'=>self::$appId,
					'secret'=>self::$appSecret
					)
				));
			if(!curl_info('error')){
				$_SESSION['weixin_api_access_token'] = $result['access_token']; //在 session 中存储 access token
				$_SESSION['weixin_api_access_token_expire'] = time() + $result['expires_in']; //过期时间
				return success('Token 获取成功。');
			}else{
				return error('Token 获取失败。');
			}
		}else{
			return error('Token 已存在。');
		}
	}

	/** createMenu() 创建菜单 */
	static function createMenu(){
		$menu = load_config_file('weixin-menu.php'); //获取菜单配置
		$menuJson = json_encode(array('button'=>$menu));
		$result = curl(array(
			'url' => self::$baseUrl.'cgi-bin/menu/create?access_token='.$_SESSION['weixin_api_access_token'],
			'method' => 'post',
			'data' => $menuJson,
			'requestHeaders' => array(
				'Content-Type' => 'application/json',
				'Content-Length' => strlen($menuJson)
				),
			));
		return self::result($result);
	}

	/** getMenu() 获取菜单 */
	static function getMenu(){
		$result = curl(self::$baseUrl.'cgi-bin/menu/get?access_token='.$_SESSION['weixin_api_access_token']);
		return self::result($result);
	}

	/** deleteMenu() 删除菜单 */
	static function deleteMenu($returnArray = false){
		if(!self::tokenValid()) self::getAccessToken();
		if(self::tokenValid()){
			$result = curl(self::$baseUrl.'cgi-bin/menu/delete?access_token='.$_SESSION['weixin_api_access_token']);
			return self::result($result);
		}else{
			return error('菜单删除失败。');
		}
	}

	/**
	 * response() 方法用来实现当易班用户向公众号发送消息时自动相应并回复
	 * @return none
	 */
	static function response(array $input){
		global $WxRes;
		if(!$WxRes)
			$WxRes = load_config_file('weixin-response.php');
		$responseMsgArray = array(
			'ToUserName' => $input['FromUserName'],
			'FromUserName' => $input['ToUserName'],
			'CreateTime' => time(),
			'MsgType' => 'text',
			'Content' => '',
			);
		if($input['MsgType'] == 'event'){
			if($input['Event'] == 'subscribe'){
				$responseMsgArray['Content'] = $WxRes['subscribeReply'];
			}
		}elseif($input['MsgType'] != 'text' && $input['MsgType'] != 'voice'){ //仅支持文本何语言消息
			$responseMsgArray['Content'] = $WxRes['invalidMsgType'];
		}else{
			self::setHistory($input['FromUserName'], isset($input['Recognition']) ? $input['Recognition'] : $input['Content']);
			$input['Content'] = $input['MsgType'] != 'voice' ? trim($input['Content']) : trim($input['Recognition']);
			$data = array('recv'=>$input, 'send'=>$responseMsgArray);
			do_hooks('WeixinDev.message', $data); //执行挂钩回调函数
			$input = $data['recv'];
			$responseMsgArray = $data['send'];
			$replyText = $responseMsgArray['MsgType'] == 'text'; //是否为纯文本消息
			if(!$replyText){
				unset($responseMsgArray['Content']);
			}elseif(!$responseMsgArray['Content']){
				$responseArray = array();
				foreach ($WxRes as $key => $value) { //从本地配置中获取答案
					if(strpos($input['Content'], $key) !== false){
						$responseArray[] = $value;
					}
				}
				$responseText = $responseArray[rand(0, count($responseArray)-1)]; //随机抽取一个回答
				if(is_array($responseText)) //二维数组
					$responseText = $responseText[rand(0, count($responseText)-1)];
				$responseMsgArray['Content'] = $responseText;
			}
		}
		if($replyText) $responseMsgArray['Content'] = trim($responseMsgArray['Content']);
		if($replyText && !$responseMsgArray['Content']){ //无回复消息时提示
			$noData = $WxRes['noMatchedData'];
			$responseMsgArray['Content'] = $noData[rand(0, count($noData)-1)];
		}
		if($replyText && strlen($responseMsgArray['Content']) > 2048){ //回复消息不能超过 2048 字节
			$responseMsgArray['Content'] = $WxRes['dataOutLimit'];
		}
		return $responseMsgArray; //返回数组数据
	}

	/** 
	 * getHistory() 获取历史记录
	 * @param  string $username 用户名，为一个 OpenId
	 * @return array
	 */
	static function getHistory($username){
		$history = array();
		if(file_exists($file = __ROOT__.'tmp/'.$username)){
			$history = explode("\n", file_get_contents($file));
		}
		return $history;
	}

	/** 
	 * setHistory() 设置历史记录
	 * @param  string $username 用户名，为一个 OpenId
	 * @param  string $data     写入数据
	 * @return bool
	 */
	static function setHistory($username, $data){
		file::open(__ROOT__.'tmp/'.$username);
		if(file::getInfo('size') >= 1024){
			file::write($data, true);
		}else{
			file::prepend($data);
		}
		return file::save();
	}
}