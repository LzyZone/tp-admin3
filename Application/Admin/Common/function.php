<?php

function x_datetime(){
    return date('Y-m-d H:i:s');
}

function nav_path(){
    $controller = CONTROLLER_NAME;
    $action = ACTION_NAME;
    $navs = (new \Admin\Logic\AdminMenuLogic())->getNav($controller,$action);
    $html = '<nav class="breadcrumb" style="background-color:#fff;padding: 0 24px">首页<span class="c-gray en">/</span> ';
    $nav_count = count($navs);
    foreach ($navs as $k=>$v){
        $html .= $v['name'];
        if($k+1 < $nav_count)$html .= '<span class="c-gray en">/</span> ';
    }
    $html .= '<a class="btn btn-success radius f-r" style="line-height:1.6em;margin-top:3px" href="javascript:location.reload(true);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a>';
    $html .= '</nav>';
    return $html;
}