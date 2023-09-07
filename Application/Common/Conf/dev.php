<?php
/**
 * 应用配置，框架内配置请配置到这里
 */
return [

    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '0.0.0.0', // 服务器地址
    'DB_NAME'               =>  'tp_admin',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  '123456',          // 密码
    'DB_PORT'               =>  '3310',        // 端口
    'DB_PREFIX'             =>  '',    // 数据库表前缀
    'DB_PARAMS'                 =>  array(
        PDO::ATTR_STRINGIFY_FETCHES => false,//禁止提取的时候将数值转换为字符串
        PDO::ATTR_EMULATE_PREPARES  => false//禁用预处理语句的模拟
    ), // 数据库连接参数

    'DB_DEBUG'  			=>  TRUE, // 数据库调试模式 开启后可以记录SQL日志
    'DB_FIELDS_CACHE'       =>  false,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8mb4',      // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '', // 指定从服务器序号


    /* 数据库设置 */
    'DB_ADMIN' => [
        'DB_TYPE'               =>  'mysql',     // 数据库类型
        'DB_HOST'               =>  '0.0.0.0', // 服务器地址
        'DB_NAME'               =>  'tp_admin',          // 数据库名
        'DB_USER'               =>  'root',      // 用户名
        'DB_PWD'                =>  '123456',          // 密码
        'DB_PORT'               =>  '3310',        // 端口
        'DB_PREFIX'             =>  'tp_admin_',    // 数据库表前缀
        'DB_FIELDTYPE_CHECK'    =>  false,       // 是否进行字段类型检查
        'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
        'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
        'DB_DEBUG'              => true,
        'DB_PARAMS'                 =>  array(
            //PDO::ATTR_STRINGIFY_FETCHES => false,//禁止提取的时候将数值转换为字符串
            //PDO::ATTR_EMULATE_PREPARES  => false//禁用预处理语句的模拟
        ), // 数据库连接参数
    ]
];