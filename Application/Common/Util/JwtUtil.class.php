<?php
namespace Common\Util;

use \Firebase\JWT\JWT;
use Think\Log;
use Common\CommonConstant;
class JwtUtil 
{

    /**
     * 使用配置文件配置的密钥和算法编码
     * @param object|array $payload 代表JWT's payload的对象或数组
     * @return string 已编码的json web tooken字符串
     * @throws Think\Exception 读取配置失败时
     */
    public static function encode($payload) {
        // 读取配置
        $secretKey = C( 'JWT_SECRET_KEY' );
        $algorithm = C( 'JWT_ALGORITHM' );
        if (! $secretKey || ! $algorithm) {
            return msg_return( 0 ,null,CommonConstant::E_CODE_MISS_CONFIG) ;
        }
        
        // 使用Firebase JWT解码并返回
        return JWT::encode ( $payload, $secretKey, $algorithm );
    }

    /**
     * 使用配置文件配置的密钥和算法解码
     * @param string $jwt 已编码的json web tooken字符串
     * @return object|boolean 签名认证通过时，代表JWT's payload的对象；解码或签名认证失败时，false；
     * @throws Think\Exception 读取配置失败时
     */
    public static function decode($jwt) {
        // 读取配置
        $secretKey = C( 'JWT_SECRET_KEY' );
        $algorithm = C( 'JWT_ALGORITHM' );
        if (! $secretKey || ! $algorithm) {
            return msg_return( 0 ,null,CommonConstant::E_CODE_MISS_CONFIG) ;
        }
        
        // 使用Firebase JWT解码
        try {
            $decode = JWT::decode ( $jwt, $secretKey, array ($algorithm ) );
            return $decode;
        } catch ( \think\Exception $e ) {
            Log::record ( '[JWT配置缺失]' . $e->__toString (), Log::INFO );
            return false;
        }
    }
}