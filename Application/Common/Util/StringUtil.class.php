<?php
namespace Common\Util;
 
/**
 * 字符串工具类
 */
class StringUtil {
  
	
	/**
	 * 通过$_SERVER获取当前服务器的URL，默认端口号时返回内容不包含端口号。
	 * @return string 当前服务器的URL
	 */
	public static function getCurServerUrl() {
		if ($_SERVER ['HTTPS'] && strtolower ( $_SERVER ['HTTPS'] ) != 'off') {
			$requestScheme = 'https';
		} else {
			$requestScheme = 'http';
		}
		$url = $requestScheme . '://' . $_SERVER ['SERVER_NAME'];
		if ($_SERVER ['SERVER_PORT'] != '' && $_SERVER ['SERVER_PORT'] != '80') {
			$url .= ':' . $_SERVER ['SERVER_PORT'];
		}
		return $url;
	}
	
	/**
	 * 获取本次访问IP，尝试从多种途径获取
	 * @return string IP地址
	 */
	public static function getIp() {
		$ip = '';
		if (getenv ( "HTTP_CLIENT_IP" )) {
	
			$ip = getenv ( "HTTP_CLIENT_IP" );
		} else if (getenv ( "HTTP_X_FORWARDED_FOR" )) {
	
			$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
		} else if (getenv ( "REMOTE_ADDR" )) {
	
			$ip = getenv ( "REMOTE_ADDR" );
		} else {
			$ip = "Unknow";
		}
		return $ip;
	}
	
	/**
	 * 生成随机验证码，每一位都是单独从字典中随机获取的字符，字典是0-9纯数字。
	 * @param number $length 验证码长度，默认6
	 * @return string 指定长度的随机验证码。
	 */
	public static function createRandomCode($length = 6) {
		$chars = "0123456789";
		$str = "";
		for($i = 0; $i < $length; $i ++) {
			$str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
		}
		return $str;
	}
	
	/**
	 * 生成隐藏部分内容的字符串
	 * 注意：不能用来处理带非Ascii编码的字符串
	 * @param string $str 原始字符串
	 * @param int $start 隐藏区域的起始索引,只能是正值
	 * @param int $length 隐藏区域的最大长度，为空时将隐藏起始索引后的所有字符
	 * @return string 隐藏区域变为*的新字符串，如果原始字符串的长度不够，则返回原始字符串
	 */
	public static function createMaskedStr($string, $start, $length) {
		$strLenth = strlen ( $string );
		// 如果长度不够，直接返回
		if ($strLenth < $start + 1) {
			return $string;
		}
	
		$before = substr ( $string, 0, $start );
		$remainingLength = $strLenth - $start;
		if (isset ( $length ) && $length < $remainingLength) {
			$maskedLength = $length;
			$after = substr ( $string, $start + $length );
		} else {
			$maskedLength = $remainingLength;
			$after = '';
		}
	
		return $before . str_repeat ( '*', $maskedLength ) . $after;
	}
	
	

	/**
	 * 创建绝对路径列表字符串,依据相对路径列表字符串,如果其中的某个路径是空值或已经是绝对路径则保持原样
	 * @param string $relativeUrls 相对路径列表字符串
	 * @param string $delimiter 分隔符，默认逗号
	 * @return string 所有路径都变为绝对路径的新列表字符串
	 * @see CommonUtil::createAbsoluteUrl
	 */
	public static function createAbsoluteUrls($relativeUrls, $delimiter = ',') {
		$urlArray = explode ( $delimiter, $relativeUrls );
		foreach ( $urlArray as &$url ) {
			$url = self::createAbsoluteUrl ( $url );
		}
	
		return implode ( $delimiter, $urlArray );
	}
	
	/**
	 * 提取相对路径（参照目录是index.php所在目录），从绝对路径中提取，如果是空值或已经是相对路径则原样返回。
	 * @param string $absoluteUrl 绝对路径
	 * @return string 从index.php同级开始的相对路径。
	 * @throws Think\Exception 当无法提取时
	 */
	public static function extractRelativeUrl($absoluteUrl) {
		if ($absoluteUrl == '' || preg_match ( '/^http:\/\/|^https:\/\/|^\//', $absoluteUrl ) == 0) {
			return $absoluteUrl;
		}
	
		// 域名路径移除域名部分，统一取得Web意义上的根路径
		$preUrl = preg_replace ( '/(^http:\/\/|^https:\/\/)[^\/]+(?=\/)/', '', $absoluteUrl );
		if ($preUrl === null) {
			return '';
		}
	
		// 移除Web意义上的跟路径中项目意义上的根路径起始字符串，得到相对路径
		if (__ROOT__ == '/') {
			$rootStr = '/';
		} else {
			$rootStr = __ROOT__ . '/';
		}
	
		$relativeUrl = substr ( $preUrl, strlen ( $rootStr ) );
	
		// 返回
		return $relativeUrl;
	}
	
	/**
	 * 提取相对路径列表字符串，从绝对路径列表字符串中提取，如果其中的某个路径是空值或已经是相对路径则保持原样。
	 * @param string $absoluteUrls 路径列表字符串
	 * @param string $delimiter 分隔符，默认逗号
	 * @return string 所有路径都变为相对路径的新列表字符串。
	 * @throws Think\Exception 当有无法提取的路径时。
	 * @see CommonUtil::extractRelativeUrl
	 */
	public static function extractRelativeUrls($absoluteUrls, $delimiter = ',') {
		$urlArray = explode ( $delimiter, $absoluteUrls );
		foreach ( $urlArray as &$url ) {
			$url = self::extractRelativeUrl ( $url );
		}
	
		return implode ( $delimiter, $urlArray );
	}
	
	
	
	/**
	 * 处理HTML编辑器内容中的src属性，不带域名的绝对路径（根路径）变为相对路径，带域名的绝对路径（域名路径）不变。
	 * 要求原内容中的路径都是绝对路径，域名路径表示网络资源，根路径表示上传的资源。
	 * 注：使用正则表达式替换字符串处理，目前只处理img标签的src属性。并且要求属性值必须用双引号包围。
	 * @param string $content HTML编辑器传递给服务器准备保存的内容字符串，
	 * @return string 服务器持久化保存的内容字符串
	 * @see CommonUtil::reverseContentSrc
	 */
	public static function processContentSrc(&$content) {
		// 正则表达式原理等待补充
		$pattern = '/(<img\s+(?:(?!src).)*src\s*=\")([^\"]*)(?=\")/';
	
		$newContent = preg_replace_callback ( $pattern,
				function ($matches) {
					if ($matches [2] == '' || strpos ( $matches [2], '/' ) !== 0) {
						// 为空或者不是根路径时不替换
						return $matches [0];
					} else {
						// 替换为相对路径
						return $matches [1] . self::extractRelativeUrl ( $matches [2] );
					}
				}, $content );
	
		return $newContent;
	}
	
	/**
	 * 反转处理HTML编辑器内容中的src属性，将相对路径变为根路径（或者域名路径），域名路径不变。
	 * 要求原内容中的路径只有相对路径和域名路径，域名路径表示网络资源，相对路径表示上传的资源。
	 * 注：使用正则表达式替换字符串处理，目前只处理img标签的src属性。并且要求属性值必须用双引号包围。
	 * @param string $content 服务器持久化保存的内容字符串，
	 * @param boolean $isAlwaysDomain 默认false，是否总是反转为域名路径
	 * @return string 传递给HTML编辑器的内容字符串，或者直接显示的HTML源码。
	 * @see CommonUtil::processContentSrc
	 */
	public static function reverseContentSrc($content, $isAlwaysDomain = false) {
		// 正则表达式原理等待补充
		$pattern = '/(<img\s+(?:(?!src).)*src\s*=\")([^\"]*)(?=\")/';
	
		if ($isAlwaysDomain) {
			$callBack = function ($matches) {
				// 替换为域名路径
				return $matches [1] . self::createAbsoluteUrl ( $matches [2], false );
			};
		} else {
			$callBack = function ($matches) {
				// 替换为根路径
				return $matches [1] . self::createAbsoluteUrl ( $matches [2], true );
			};
		}
	
		$newContent = preg_replace_callback ( $pattern, $callBack, $content );
	
		return $newContent;
	}
	
	
    
}