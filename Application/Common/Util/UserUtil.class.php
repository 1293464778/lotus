<?php
namespace Common\Util;
 

/**
 * 与用户相关，例如密码加密、用户名格式判断等。
 * 注意：Util类型的工具不能包含业务逻辑，公共业务工具（例如获取终端ID）放到Tool类型的工具中。
 * 目前统一使用Session来作为当前请求的用户对象的缓存容器，所以设置和获取当前请求的主用户对象这类方法，也作为Util而不是Tool，
 * 但是方法体只能有用户缓存读写操作，不能有会话跟踪判断和反馈。会话跟踪判断只能由上层调用方法根据读取结果另行处理。
 * @author xiegaolei
 */
class UserUtil {
   
    
}