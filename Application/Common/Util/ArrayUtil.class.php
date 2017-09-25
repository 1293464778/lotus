<?php
namespace Common\Util;


/**
 * 数组工具类
 */
class ArrayUtil {
     
    /**
     * 从字符串（以固定分隔符分割并且代表数组)或数组中得到数组
     * @param string|array|empty $param 字符串（以固定分隔符分割并且代表数组)或数组
     * @param string $delimiter 分隔符，默认','
     * @return array 数组
     * @throws Think\Exception 方法调用参数无效时（通过my_error抛出）
     */
    public static function getArrayByStringOrArray($param, $delimiter = ',') {
    	if (empty ( $param )) {
    		return array ();
    	} else if (is_array ( $param )) {
    		return $param;
    	} else if (is_string ( $param )) {
    		return explode ( $delimiter, $param );
    	} else {
    		return array ();
    	}
    }
    
    
    /**
     * 过滤数组，过滤后的数组只保留指定的键。
     * @param array $input 原始数组
     * @param string|array $keys 要保留的键列表，字符串时采用逗号分割
     * @return array 新数组，相对于原数组仅包含指定的键。若键指定了保留但是原数组中没有该键，则新数组中仍然有该键。
     */
    public static function array_filter_key($input, $keys) {
        $newArray = array ();
        
        $keys = self::getArrayByStringOrArray ( $keys );
        foreach ( $keys as $key ) {
            $newArray [$key] = $input [$key];
        }
        
        return $newArray;
    }
     
      
    /**
     * 整理VO,is[A-Z]\w*字段转换为布尔类型，\w+Url和\w+Urls字段补充为绝对路径，最后所有字段的值如果是null则转换为空字符串
     * @param array $vo 引用参数，表示值对象的数组
     * @param string|array $exculdeFileds 排除字段，字符串时逗号分割
     * @return void
     */
    public static function sortOutVo(array &$vo, $exculdeFileds = null) {
        // 主动防御
        if ($exculdeFileds != null && ! is_string ( $exculdeFileds ) && ! is_array ( 
                $exculdeFileds )) {
            return '';
        }
        
        $exculdeFileds = self::getArrayByStringOrArray ( $exculdeFileds );
        $resServer = self::getCurServerUrl ();
        
        foreach ( $vo as $key => $value ) {
            // 排除字段不处理
            if (in_array ( $key, $exculdeFileds )) {
                continue;
            }
            
            // is*强制转换为boolean
            if (preg_match ( '/^is[A-Z]\w*/', $key ) > 0) {
                if ($value) {
                    $vo [$key] = true;
                } else {
                    $vo [$key] = false;
                }
            }
            
            // *Url补充为绝对路径，如果值为空或者如果已经是绝对路径则不处理
            if (preg_match ( '/\w+Url$/', $key ) > 0) {
                if ($value != '' && preg_match ( '/^http:\/\/|^https:\/\//', $value ) == 0) {
                    $vo [$key] = $resServer . __ROOT__ . '/' . $value;
                }
            }
            
            // *Urls每一项都补充为绝对路径，如果其中的值为空或者如果已经是绝对路径则不处理
            if (preg_match ( '/\w+Urls$/', $key ) > 0) {
                if ($value) {
                    $urlArray = explode ( ',', $value );
                    foreach ( $urlArray as &$url ) {
                        if ($url != '' && preg_match ( '/^http:\/\/|^https:\/\//', $url ) == 0) {
                            $url = $resServer . __ROOT__ . '/' . $url;
                        }
                    }
                    $vo [$key] = implode ( ',', $urlArray );
                }
            }
            
            // 以上都处理后，如果值仍然为null，则转换为空字符串。
            if ($value === null) {
                $vo [$key] = '';
            }
        }
    }

    /**
     * 通过$_SERVER获取当前服务器的URL，默认端口号时返回内容不包含端口号。
     * @return string 当前服务器的URL
     */
    private static function getCurServerUrl() {
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

     
    
}