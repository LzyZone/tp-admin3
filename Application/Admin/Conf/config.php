<?php
return [
    'TMPL_PARSE_STRING' => [
        '__PUBLIC__' => '/Public',
        '__ADMIN__' => '/Public/admin',
        '__image__' => '/Public/',
        '__LIB__' => '/Public/lib',
    ],

    //用户随机数，用与后台用户密码加密
    'ADMIN_USER_SALT' => 'tp_admin',
    //后台用户最大登录失败次数
    'ADMIN_MAX_ERR_LIMIT' => 5,

    'ADMIN_USER_SESSION_NAME' => 'admin',

    'SITE_TITLE' => 'TP-Admin'
];
