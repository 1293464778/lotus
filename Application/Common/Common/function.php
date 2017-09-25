<?php
    /**
     * [p 格式化输出]
     * @param  [array] $arr [待处理的数组]
     * @return [type]       [无]
     */
    function p($arr) {
        dump($arr,true, null, false);
    }

    /**
     * [check_mobile 校验 手机格式]
     * @param  [type] $phone [description]
     * @return [type]        [description]
     */
    function check_mobile($phone){
        return preg_match("/1\d{10}$/",$phone);
    }

    /**
     * [check_email 校验邮箱格式]
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    function check_email($email){
        $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        return preg_match($pattern,$email);
    }

	  /**
     * 字符串截取，支持中文和其他编码
     * @static
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
        if(function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice.'...' : $slice;
    }


    /**
     * CURL快捷方法，post提交数据
     * @param string $url 提交目的地址
     * @param array $data post数据
     * @return url访问结果
     */
    function curl_post($url, $data) {
    	$ch = curl_init ();
    	$header = array ("Accept-Charset: utf-8",'Expect:' );
    	curl_setopt ( $ch, CURLOPT_URL, $url );
    	curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
    	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
    	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
    	curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
    	curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
    	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
    	curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
    	curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
    	// 最好加上http_build_query 转换，防止有些服务器不兼容
    	curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
    	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
    	$result = curl_exec ( $ch );
    	curl_close ( $ch );
    	return $result;
    }
    
    /**
     * 发送短信，当前代码以吉信通为短信平台，使用其他平台时需要重写方法
     * @param string $phone 短信目的手机号，格式由短信平台决定，一般为单手机号或者逗号分割的多手机号
     * @param string $content 短信内容
     * @return boolean 是否发送成功
     */
    function sendSms_bak($phone, $content) {
    	// 数据库读取短信平台账号密码
    	$smsConfig = M ( 'SysConfig' )->where ( array ('configCode' => 'SMS' ) )
    	->find ();
    	$userName = $smsConfig ['value1'];
    	$pass = $smsConfig ['value2'];
    	// 从配置读取短信平台服务器
    	$sendUrl = C ( 'SMS_SEND_URL' );
    
    	// 短信要转换成gbk的 不然短信是乱码
    	$oriContent = $content;
    	$content = iconv ( "UTF-8", "gb2312", $content );
    
    	// 调用短信平台接口发送
    	$data = array ("id" => $userName,"pwd" => $pass,"to" => $phone,"content" => $content,
    			"time" => '' );
    	$sendResult = curl_post ( $sendUrl, $data );
    
    	// 接口返回结果解析，日志记录
    	$smsInfo = '[目的手机]：' . $phone . '[短信内容]：' . $oriContent . '。';
    	$tempArray = explode ( '/', $sendResult, 2 );
    	if ($tempArray [0] == '000') {
    		Log::record ( '[短信发送成功]。' . $smsInfo, Log::INFO );
    		return true;
    	} else {
    		Log::record ( '[短信发送失败]' . $sendResult . '。' . $smsInfo, Log::WARN );
    		return false;
    	}
    }
    
    /**
     * 返回格式化信息
     * @param string $msg 信息内容
     * @param string $code 错误码
     * @param number $status 状态 0 错误 ，1 成功
     * @return array
     */
    function msg_return($status = 0  ,$msg = null , $code = 0) {
    	
    	return array ('status' => $status, "code" => $code ,"msg" => $msg );
    }
     
    

    /**
     * ajax 请求正确返回
     * @param string $msg
     * @param array $data
     * @return json
     */
    function ajax_return_ok($data = array(),$msg = ''){
    
    	$result['status'] = 1;
    	$result['data'] = $data;
    	$result['msg'] = $msg ;
    	// 返回JSON数据格式到客户端 包含状态信息
    	header('Content-Type:application/json; charset=utf-8');
    	exit(json_encode($result));
    }
    
    /**
     * ajax 请求错误返回
     * @param string $msg
     * @param string $code
     * @return json
     */
    function ajax_return_error($msg = null,$code = 1){
    	
    	if ($msg == null){
    		$msgDefault = C ( 'E_MSG_DEFAULT' );
    		$result['msg'] = $msgDefault [$code];
    	}else{
    		$result['msg'] = $msg ;
    	}
    	
    	$result['status'] = 0;
    	$result['code'] = $code;
    	// 返回JSON数据格式到客户端 包含状态信息
    	header('Content-Type:application/json; charset=utf-8');
    	exit(json_encode($result));
    }
    
    
    
    /**
     * 返回json
     * @param array $data
     */
    function json_return( $data = array() ){
    	// 返回JSON数据格式到客户端 包含状态信息
    	header('Content-Type:application/json; charset=utf-8');
    	exit(json_encode($data));
    }
    
    /**
     * 手机验证码检测
     * @param string $phone
     * @param string $inputCode
     * @param string $pre :register , login, findpwd
     * @return boolean
     */
    function check_sms_verify_code($phone, $inputCode,$pre = '') {
    	$sendedCode = session ( $pre . '_' . $phone );
    	return $sendedCode && $sendedCode == $inputCode;
    }
    
    /**
     * 清空服务器保存的验证码
     * @param string $phone
     * @return void
     */
    function clear_sms_verify_code($phone ,$pre = '') {
    	session ( $pre . '_' . $phone, null );
    }
    
    /**
     * 用户密码加密方法，可以考虑盐值包含时间（例如注册时间），
     * @param string $pass 原始密码
     * @return string 多重加密后的32位小写MD5码
     */
    function encrypt_pass($pass) {
    	if ('' == $pass) {
    		return '';
    	}
    
    	$salt = C ( 'PASS_SALT', null, '' );
    	return md5 ( sha1 ( $pass ) . $salt );
    }
		/**
     * 格式化参数格式化成url参数
     */
    function to_url_params($data)
    {
        $buff = "";
        foreach ($data as $k => $v)
        {
            if(!is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        
        $buff = trim($buff, "&");
        return $buff;
    }
	/**
	 * 计算总收益
	 */
	function countProfit($fairy,$angel,$wizard,$level){
		$config=M("config");
		$fairy_fee=$config->where("id=14")->getField("value");
		$angel_fee=$config->where("id=16")->getField("value");
		$wizard_fee=$config->where("id=15")->getField("value");
		$sys_fee=$config->where("id=9")->getField("value");
		$level_fee=$config->where("type=1")->order("id asc")->select();
		$profit=$sys_fee+$fairy_fee*$fairy+$angel_fee*$angel+$wizard*$wizard_fee+$level_fee[$level-1]['value'];//每天的收益率
		if($profit<0){
			$profit=0;
		}
		return array('total_rate'=>$profit,'sys_rate'=>$sys_fee,'fairy_rate'=>$fairy_fee*$fairy,'angel_rate'=>$angel_fee*$angel,'wizard_rate'=>$wizard*$wizard_fee,'level_rate'=>$level_fee[$level-1]['value']);
	}
	

?>