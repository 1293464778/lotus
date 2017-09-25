<?php
namespace Common;

/**
 * 一般/公用常量定义
 * @author xiegaolei
 */
class CommonConstant {
   
    // *********************************魔术文本数字定义*********************************
    // 数据库中表示真假的int值
    const DB_TRUE = 1;
    const DB_FALSE = 0;
    // 数据库中表示正常和禁用状态的int值
    const DB_ENABLED = 1;
    const DB_DISABLED = 0;
    // 数据库中表示性别的int值
    const DB_SEX_MALE = 1;
    const DB_SEX_FEMALE = 2;
    const DB_SEX_OTHER = 0;
    // 客户端类型
    const CLIENT_TYPE_ANDROID = 'android';
    const CLIENT_TYPE_IOS = 'ios';
  
    // 手机号正则
    const PHONE_REG_EXPRESSION = "^(13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[57])[0-9]{8}$";
    
    // *********************************错误码定义*********************************
    // **暂定100以下，业务无关；101-199，用户相关；201-299，权限相关；1001+具体业务相关。
    // 一般错误
    const E_CODE_GENERAL = 1;
    // 方法调用参数无效
    const E_CODE_INVALID_ARG = 2;
    // 表单令牌错误
    const E_CODE_TOKEN_ERROR = 3;
    // 无效的类
    const E_CODE_INVALID_CLASS = 4;
    // 数据无效
    const E_CODE_INVALID_DATA = 5;
    // 接口签名失败
    const E_CODE_SIGN_WRONG = 6;
    // 配置缺失
    const E_CODE_MISS_CONFIG = 7;
    // 配置无效
    const E_CODE_INVALID_CONFIG = 8;
    // 接口或方法的参数项是必须的
    const E_CODE_PARAM_REQUIRED = 9;
    // 第三方支付接口调用失败
    const E_CODE_PAY_INTEFACE_CALL_FAIL = 10;
    // 用户身份失效
    const E_CODE_USER_LOSE = 101;
    // 用户已被禁用
    const E_CODE_USER_DISABLED = 102;
    // 没有权限
    const E_CODE_NO_PERMISSION = 201;
    // 通过ID查询详情但详情不存在
    const E_CODE_DETAIL_NOEXIST = 1001;
    // 环信接口初始化失败，可能是基础配置错误
    const E_CODE_EASEMOB_INIT_FAIL = 1002;
    // 环信接口调用失败
    const E_CODE_EASEMOB_CALL_FAIL = 1003;
    // 车位上车辆已经存在
    const E_CODE_POSITION_HAS_CAR = 1004;
    // 车辆已经录入过了
    const E_CODE_CAR_HAS_INPUT = 1005;
}