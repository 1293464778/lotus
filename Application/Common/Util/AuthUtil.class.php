<?php
namespace Common\Util;
use Common\Util\JwtUtil;
use Common\CommonConstant;
/**
 * 权限认证
 * @author xiegaolei
 *
 */
class AuthUtil
{
	/**
	 * 验证 接口调用权限
	 * @param boolean $isCheckLogin false or true
	 */
	public static function checkApiAuth( $isCheckLogin = true) {
		
		$result1 = self::checkSign();
		$result2 = self::checkIdentity($isCheckLogin);
		
		if ($result1['status'] && $result2['status']){
			return msg_return(1,'ok');
		}else{
			return msg_return( 0 ,null,CommonConstant::E_CODE_NO_PERMISSION);
		}
		
		
	}
	
	/**
	 * 签名验证
	 */
	public static function checkSign() {
	 
		// 接口签名认证
		if ( C( "APP_SIGN_AUTH_ON" ) === true) {
			$signature = I ( "signature" ); // app端生成的签名
			$param = I ( "param." );  
			unset($param['signature']);
			if (empty($signature) || empty($param)) {
				return msg_return( 0 ,null,CommonConstant::E_CODE_PARAM_REQUIRED);
			}
			
			//数组排序
			ksort($param);
			//$str = http_build_query($param);
			$str = to_url_params($param);
			$signature1 = md5 ( sha1 ( $str ) . C( "APP_SIGN_KEY" ) );
	
			if ($signature != $signature1) {
				return msg_return( 0 ,null,CommonConstant::E_CODE_SIGN_WRONG);
			}
			 
		}
		
		return msg_return(1,'ok');
		
	}
	
	/**
	 * 验证用户身份
	 * @param boolean $isCheckLogin false or true
	 */	
	public static function checkIdentity( $isCheckLogin = true ) {
		  
		// 客户端和用户认证、用户缓存处理
		// 客户端存储的JWT和服务器端实时用户数据结合，进行用户认证
		// 在依附于会话的缓存中保留用户信息，并且在有用户缓存的时候，不再执行实时用户数据认证。
		if ($isCheckLogin) {
			// JWT访问令牌认证，令牌内容获取
			$userAccessToken = I ( 'userAccessToken' );
			$payload = JwtUtil::decode ( $userAccessToken );
			if ($payload === false || empty($payload->uid)) {
				return msg_return( 0 ,null,CommonConstant::E_CODE_USER_LOSE);
			}
	
			// 获取用户缓存
			$cacheUser = session ( C( "SESSION_NAME_CUR_HOME" ) );
	
			// 没有缓存时进行实时用户数据认证、缓存设置
			if (empty ( $cacheUser ) || $cacheUser ['id'] != $payload->uid) {
				// 实时用户数据认证：状态
				$user = M( 'user' )->getById ( $payload->uid );
				if ($user ['isShow'] != CommonConstant::DB_TRUE) {
					return msg_return( 0 ,null,CommonConstant::E_CODE_USER_DISABLED);
				}
				
				// 用户初始化和缓存用户设置
				session ( C( "SESSION_NAME_CUR_HOME" ), $user );
			}
		}
		
		return msg_return(1,'ok');
	}
	
	
	
	
	
	
	
	
}
