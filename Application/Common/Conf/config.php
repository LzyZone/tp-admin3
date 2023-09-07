<?php
return array(
    //自定义配置文件
    'LOAD_EXT_CONFIG' => 'app',

	//'配置项'=>'配置值'
    'MODULE_DENY_LIST'      =>  array('Common','Runtime'),
    'URL_CASE_INSENSITIVE'  =>  true,   // 默认false 表示URL区分大小写 true则表示不区分大小写

    /* URL设置 */
    'URL_MODEL'             =>  2,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
    'URL_PATHINFO_DEPR'     =>  '/',	// PATHINFO模式下，各参数之间的分割符号
    'APP_AUTOLOAD_LAYER'    =>  'Controller,Model', // 自动加载的应用类库层 关闭APP_USE_NAMESPACE后有效

    /* 主题模版设置 */
    'DEFAULT_THEME'         =>  '',	// 默认模板主题名称
    'DEFAULT_MODULE'        =>  'Admin',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
);