<?php
return array(
        
        // 数据库配置
        'DB_TYPE'               =>  'mysql',  // 数据库类型
        'DB_HOST'               =>  'localhost',  // 服务器地址
        'DB_NAME'               =>  'lotus',  // 数据库名
        'DB_USER'               =>  'root',  // 用户名
        'DB_PWD'                =>  'root',  // 密码
        'DB_PORT'               =>  '3306',  // 端口
        'DB_PREFIX'             =>  'farm_',  // 数据库表前缀
        'DB_PARAMS'             =>  array(PDO::ATTR_CASE => PDO::CASE_NATURAL), // 数据库连接参数  是数据库查询字段区分大小写
        
        // 模块设置
        'DEFAULT_MODULE'        =>'Home',
        
        // URL设置
        'URL_CASE_INSENSITIVE'  =>  true,   // true则表示不区分大小写
        'URL_MODEL'             =>  1,        //PATHINFO 模式
        'URL_HTML_SUFFIX'       =>  'html',  // URL伪静态后缀设置
        
        /* 日志设置 */
        'LOG_RECORD'            =>  false,   // 默认不记录日志
        'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式
        'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
        'LOG_FILE_SIZE'         =>  2097152,    // 日志文件大小限制
        
        //附近的人当前列表主键缓存
        'SESSION_NAME_NEARBY_KEY_LIST' => 'CUR_NEARBY_KEY_LIST',
        
        //各系统当前用户信息。
        'SESSION_NAME_CUR_HOME' => 'CUR_HOME',
        'SESSION_NAME_CUR_ADMIN' => 'CUR_ADMIN' ,
        
        
        // *********************************错误码对应的默认消息定义*********************************
        'E_MSG_DEFAULT' => array(
                1 => '一般错误',
                2 => '系统错误，参数无效',
                3 => '表单令牌错误',
                4 => '类无效',
                5 => '数据无效',
                6 => '接口签名验证失败',
                7 => '缺少配置',
                8 => '配置无效',
                9 => '接口或方法的参数项是必须的',
                10 => '与支付服务通信失败',
                101 => '用户身份已失效，请重新登录',
                102 => '用户已被禁用',
                201 => '无权操作',
                1001 => '详细信息已不存在',
                1002 => '数据同步接口初始化失败，可能是基础配置错误',
                1003 => '数据同步接口调用失败',
                1004 => '此车位上已有车辆',
                1005 => '此车已经录入',
        ),
        
         
        'PASS_SALT' => '58&%97@*^54*89',
        
        // jwt签名密钥
        'JWT_SECRET_KEY' => '58&%97@*^54*89',
        // jwt算法 ，可配置的值取决于使用的jwt包支持哪些算法
        'JWT_ALGORITHM' => 'HS256',
        
        // 是否开启app接口签名认证，为方便测试可以关闭，正式环境必须开启
        'APP_SIGN_AUTH_ON' => false,
        // app接口加密安全key
        'APP_SIGN_KEY'      =>  '#l_vle_ll_e%+$^@0608)[',
        // APP接口请求失败提示
        "APP_FEI_FA"    => "请求非法",
        "SITE_ROOT"=>'http://tingcheshoufei.65g.cn',
        
        
        
    
        
        
        
        
);