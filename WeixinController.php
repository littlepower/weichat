<?php
class WeixinController extends Controller{
	private $WxObj;
	private $accessToken;
	private $token = 'test666';
	private $appid = 'wx813584a7c52119d3';
	private $appsecret = '1d247a096e5270612a2f0c2b74532037';

	//首页方法
	public function index()
	{
		$token = $this->token;
		$this->verify($token);

		$WxObj = $this->getWxObj();
		$this->WxObj = $WxObj;

		//关注事件处理
		if($this->isSubscribe()){
			$this->responseTextMsg("欢迎关注");
		}

		//获取access_token
		$this->accessToken = $this->getaccessToken();

		//创建自定义菜单
		//$this->createNav();

		//文本消息处理
		if($this->isText()){
			switch (trim($this->WxObj->Content)) {
				case '1':
					$this->responseTextMsg("我爱你");
					break;
				case '3':
					$this->responseTextMsg($this->accessToken);
					break;	
				default:
					$this->responseTextMsg("I love u.");
					break;
			}
		}

		//位置消息处理
		if($this->isLocation()){
			$x = $this->WxObj->Location_X;
			$y = $this->WxObj->Location_Y;
			$this->responseTextMsg("你的位置:\n经度：".$x."\n纬度：".$y);
		}

		//图片消息处理
		if($this->isImage()){
			$this->responseTextMsg("这是图片消息");
		}

		//自定义菜单点击事件
		if($this->isClick()){
			switch ($this->WxObj->EventKey) {
				case 'xgmv':
					$data = array(
						array(
							'title' => '美女表白',
							'description' => '美女主动表白竟然...',
							'picurl' => 'http://p0.so.qhimgs1.com/t0110ba5c3fd38839e0.jpg',
							'url' => 'http://www.baidu.com/'
						),
						array(
							'title' => '美女拍卖会',
							'description' => '今日，在杭州举行美女拍卖会...',
							'picurl' => 'http://p0.so.qhimgs1.com/t01db6d9b061fe37e10.jpg',
							'url' => 'http://www.sina.com/'
						),
						array(
							'title' => '美女价格连年涨价',
							'description' => '美女价格近年来飞涨...',
							'picurl' => 'http://p1.so.qhmsg.com/t01a4a32b35e421df96.jpg',
							'url' => 'http://www.baidu.com/'
						)
					);
					$this->responseNews($data);
					break;
				case 'qcmv':
					$this->responseTextMsg("清纯美女最耐看");
					break;
				case 'address':
					$this->responseTextMsg("太阳系天堂");
					break;	
				default:
					$this->responseTextMsg("你喜欢美女吗？");
					break;
			}
		}
	}

	//回复图文消息
	private function responseNews($data){
		$WxObj = $this->WxObj;
		$fromUser = $WxObj->ToUserName;
		$toUser   = $WxObj->FromUserName;
		$time = time();
		$nums = count($data);
		$str = <<<EOF
<xml>
<ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>{$nums}</ArticleCount>
<Articles>
EOF;
		foreach ($data as $key => $v) {
			$str .=<<<ABF
<item>
<Title><![CDATA[{$v['title']}]]></Title> 
<Description><![CDATA[{$v['description']}]]></Description>
<PicUrl><![CDATA[{$v['picurl']}]]></PicUrl>
<Url><![CDATA[{$v['url']}]]></Url>
</item>
ABF;
		}

		$str .= '</Articles></xml>';
		echo $str;
		die;
	}

	//自定义菜单点击事件
	private function isClick(){
		if($this->WxObj->MsgType=='event'){
			if($this->WxObj->Event=='CLICK'){
				return true;
			}
		}
		return false;
	}

	//获取access_token
	private function getaccessToken()
	{
		$cacheKey = "access_token";

		//缓存有，直接返回
		if($accessToken = $this->cacheGet($cacheKey))
		{
			return $accessToken;
		}

		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential";
		$url .= "&appid=".$this->appid."&secret=".$this->appsecret;

		$json = curlGet($url);	
		$data = json_decode($json,true);

		if(array_key_exists("errcode", $data) && $data["errcode"] != 0){
			return false;
		}
		$accessToken = $data['access_token'];
		$this->cacheSet($cacheKey,$accessToken,7000);

		return $accessToken;
	}

	//创建自定义菜单
	//关键词不能有大写 click对应key view 对应url  url需以http://开头
	private function createNav(){
		$data = array(
			'button' => array(
				array(
					'type' => 'click',
					'name' => '性感美女',
					'key'  => 'xgmv',
				),
				array(
					'type' => 'click',
					'name' => '清纯美女',
					'key'  => 'qcmv',
				),
				array(
					'name' => '美女交易所',
					'sub_button'  => array(
						array(
							'type' => 'view',
							'name' => '美女收购',
							'url'  => 'http://www.sina.com',
						),
						array(
							'type' => 'view',
							'name' => '美女出售',
							'url'  => 'http://www.baidu.com',
						),
						array(
							'type' => 'click',
							'name' => '联系地址',
							'key'  => 'address',
						),
					)
				),
			)
		);

		//加入第二个参数，实现中文不转换
		$json = json_encode($data,JSON_UNESCAPED_UNICODE);
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->accessToken;
		$json = curlPost($url,$json);

	}

	//关注事件
	private function isSubscribe(){
		$WxObj = $this->WxObj;
		if($WxObj->MsgType=='event'){
			if($WxObj->Event=='subscribe'){
				return true;
			}
		}
		return false;
	}

	//返回文本数据
	private function responseTextMsg($msg)
	{
		$WxObj = $this->WxObj;
		$fromUser = $WxObj->ToUserName;
		$toUser   = $WxObj->FromUserName;
		$time = time();
		$str = <<<EOF
<xml><ToUserName><![CDATA[{$toUser}]]></ToUserName>
<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[{$msg}]]></Content>
</xml>
EOF;
		echo $str;
		die;
	}

	//判断消息是否文本类型
	private function isText(){
		if($this->WxObj->MsgType=='text'){
			return true;
		}
		return false;
	}

	//判断消息是否图片类型
	private function isImage(){
		if($this->WxObj->MsgType=='image'){
			return true;
		}
		return false;
	}

	//判断消息是否位置类型
	private function isLocation(){
		if($this->WxObj->MsgType=='location'){
			return true;
		}
		return false;
	}

	//获取用户消息对象
	private function getWxObj(){

		$postStr=$GLOBALS['HTTP_RAW_POST_DATA'];

		file_put_contents('xml.php', $postStr);

		libxml_disable_entity_loader(true); //xml安全函数
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

		return $postObj;
	}

	//验证接口
	private function verify($token)
	{
		$echostr = $_GET["echostr"];
		$signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce  = $_GET["nonce"];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature && isset($_GET["echostr"])){
        	echo $echostr;
            die;
        }else{
            return false;
        }
	}

	//消息互动接口
	public function reply(){

		//获取access_token
		$accessToken = $this->getaccessToken();
		$userListUrl = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$accessToken;
		$json = curlGet($userListUrl);
		$data = json_decode($json,1);
		$openid_arr = $data['data']['openid'];

		$user_list = array();
		if(!empty($openid_arr)){
			foreach ($openid_arr as $openid) {
				$get_user_info_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$accessToken.'&openid='.$openid.'&lang=zh_CN';
				$json_info = curlGet($get_user_info_url);
				$user_list[] = json_decode($json_info,1);
			}
		}
		//p($user_list);
		foreach ($user_list as $user) {
			$reply_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$accessToken;
			$data = array(
				'touser' => $user['openid'],
				'msgtype' => 'text',
				'text' => array('content' => '我爱你'),
			);
			$postData = json_encode($data,JSON_UNESCAPED_UNICODE);
			curlPost($reply_url,$postData);
		}
	}

}
?>