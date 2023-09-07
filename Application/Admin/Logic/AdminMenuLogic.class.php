<?php
namespace Admin\Logic;

class AdminMenuLogic {
    private $model;

    private $char2 = [
        1 => '┣',
        2 => '┗',
        3 => '',
    ];

    private $char = [
        0 => '',
        1 => '└─',
        2 => '├─ ',
    ];

    public function __construct(){
        $this->model = D('AdminMenu');
    }

    /**
     * 后台菜单
     * @return mixed
     */
    public function getMenuOnLeft(){
        $data = $this->model->where("is_display=1 AND pid=0")->field('id,pid,name,m,c,a,param,icon')->order('sort desc,id asc')->select();
        if($data){
            foreach ($data as &$v){
                $v['children'] = $this->model->where("pid = {$v['id']} AND is_display=1")->field('id,name,m,c,a,param')->order('sort desc,id asc')->select();
                foreach ($v['children'] as $ck=>$cv){
                    $cv['url'] = U($cv['m'].'/'.$cv['c'].'/'.$cv['a'],$cv['param']);
                    $v['children'][$ck] = $cv;
                }
            }
            unset($v);
        }
        return $data;
    }


    /**
     * 获取table菜单
     * @return string
     */
    public function getMenuCheckbox($checked_id_arr=[]){
        $menus = $this->model->field("id,pid,name,mca,param,path,icon,sort")->order('id desc')->select();
        $data = [];
        foreach ($menus as $k=>$v){
            if($v['pid'] == 0){
                unset($menus[$k]);
                $v['children'] = $this->_getChildren($menus,$v['id']);
                $data[] = $v;
            }
        }
        $tbody = $this->_makeCheckbox($data,$checked_id_arr);
        return $tbody;
    }


    /**
     * 获取table菜单
     * @return string
     */
    public function getMenuTable(){
        $menus = $this->model->field("id,pid,name,mca,param,path,icon,sort")->order('id desc')->select();
        $data = [];
        foreach ($menus as $k=>$v){
            if($v['pid'] == 0){
                unset($menus[$k]);
                $v['children'] = $this->_getChildren($menus,$v['id']);
                $data[] = $v;
            }
        }
        //print_r($data);
        $tbody = $this->_makeTBody($data);
        return $tbody;
    }


    /**
     * 获取select菜单
     * @param int $selected_id
     * @param int $exclude_id
     * @return string
     */
    public function getMenuSelectOption($selected_id=0,$exclude_id=0){
        $where = '';
        $menus = $this->model->field("id,pid,name,mca,param,path,icon,sort")->where($where)->order('id desc')->select();
        $exclude_id_arr = [];

        if($exclude_id){
            $parent = $this->model->where("id={$exclude_id}")->field('pid,path')->find();
            if($parent['pid']){
                $path = "{$parent['path']}";
            }else{
                $path = $exclude_id;
            }
            $exclude_id_arr = $this->model->where("path like '{$path}%'")->getField('id',true);
            $exclude_id_arr[] = $exclude_id;
        }

        $data = [];
        foreach ($menus as $k=>$v){
            if($v['pid'] == 0){
                unset($menus[$k]);
                $v['children'] = $this->_getChildren($menus,$v['id']);
                $data[] = $v;
            }
        }
        $option = $this->_makeOption($data,0,$selected_id,$exclude_id_arr);
        return $option;
    }


    public function getNav($controller,$action){
        $last = $this->model->where("c='{$controller}' AND a='{$action}'")->field('id,name,pid,path,m,c,a')->find();
        if(empty($last))return false;
        $path_arr = explode(',',$last['path']);
        array_pop($path_arr);
        $count = count($path_arr);
        //var_dump($count,$path_arr);
        $where = [];
        for($i=$count;$i>0;$i--){
            $a = array_slice($path_arr,0,$i);
            $where['path'][] = ['eq',join(',',$a)];
        }
        $where['path'][] = 'or';

        //$path = str_replace(','.$last['id'],'',$last['path']);
        //echo "(path like '%,{$path}') OR (path = '{$path}')";
        //$menus = $this->model->where("(path like '%,{$path}') OR (path = '{$path}')")->field('id,name,pid,path,m,c,a')->select();
        $menus = $this->model->where($where)->field('id,name,pid,path,m,c,a')->order('pid asc,id asc')->select();
        $menus[] = $last;
        return $menus;
    }

    /**
     * @param $data
     * @param int $level
     * @return string
     */
    private function _makeTBody($data,$level=0){
        $tbody = '';
        $count = count($data);
        foreach ($data as $k=>$v){
            $_val = $v;
            $_prefix = '';
            if($level > 0){
                $_prefix = $this->_getSpace($level*2);
                if($count == 1){
                    $_prefix .= $this->char[1];
                }elseif($count == $k+1){
                    $_prefix .= $this->char[1];
                }else{
                    $_prefix .= $this->char[2];
                }
            }else{
                $_prefix = $v['icon'] ? '<i class="Hui-iconfont">'.$v['icon'].'</i> ' : '';
            }
            $_add_url = U('AdminMenu/add','pid='.$v['id']);
            $_edit_url = U('AdminMenu/edit','id='.$v['id']);
            $_del_url = U('AdminMenu/ajax_del','type=del&id='.$v['id']);

            $_action = <<<EOF
<a title="添加" href="javascript:;" onclick="layer_show('添加','{$_add_url}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe600;</i></a>
<a title="编辑" href="javascript:;" onclick="layer_show('编辑','{$_edit_url}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
<a title="删除" href="javascript:;" onclick="layer_confirm('{$_del_url}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>
EOF;

            $tbody .= "<tr class='text-c'>
<td>{$_val['id']}</td>
<td class='text-l'>{$_prefix}{$_val['name']}</td>
<td>
<input type='number' class='input-text js-sort' value='{$v['sort']}' min='0' data-value='{$v['sort']}' data-id='{$v['id']}'></td>
<td class='td-manage'>{$_action}</td>
</tr>";
            if($_val['children']){
                $tbody .= $this->_makeTBody($v['children'],$level+1);
            }
        }
        return $tbody;
    }

    private function _makeCheckbox($data,$checked_id_arr=[],$level=0){
        $tbody = '';
        $count = count($data);
        foreach ($data as $k=>$v){
            $_val = $v;
            $_prefix = $_space = $_icon = '';
            if($level > 0){
                $_space = $this->_getSpace($level*2+$level);
                if($count == 1){
                    $_prefix .= $this->char[1];
                }elseif($count == $k+1){
                    $_prefix .= $this->char[1];
                }else{
                    $_prefix .= $this->char[2];
                }
            }else{
                $_icon = $v['icon'] ? '<i class="Hui-iconfont">'.$v['icon'].'</i> ' : '';
            }

            $_checked = '';
            if($checked_id_arr && in_array($v['id'],$checked_id_arr)){
                $_checked = 'checked="checked"';
            }

            $_path_arr = explode(',',$v['path']);
            $tbody .= <<<EOF
<tr>
<td class='text-l' colspan="2">
    {$_space}
    <div class="check-box skin-minimal menu-id-{$v['id']}">
        <input type="checkbox" class="menu-group-{$_path_arr[0]}" id="menu-{$v['id']}" name="menu_ids[]" value="{$v['id']}" data-pid="{$v['pid']}" data-path="{$v['path']}" {$_checked}>
        <label for="menu-{$v['id']}">{$_prefix}{$_icon}{$_val['name']}</label>
    </div>
</td>
</tr>
EOF;
            if($_val['children']){
                $tbody .= $this->_makeCheckbox($v['children'],$checked_id_arr,$level+1);
            }
        }
        return $tbody;
    }

    private function _makeOption($data,$level=0,$selected_id=0,$exclude_id_arr=[]){
        $option = '';
        $count = count($data);
        foreach ($data as $k=>$v){
            $_val = $v;
            $_prefix = '';
            if($level > 0){
                $_prefix = $this->_getSpace($level*2);
                if($count == 1){
                    $_prefix .= $this->char[1];
                }elseif($count == $k+1){
                    $_prefix .= $this->char[1];
                }else{
                    $_prefix .= $this->char[2];
                }
            }

            $selected = '';
            if($selected_id && $selected_id == $v['id'])$selected = 'selected="selected"';

            $disabled = '';
            if($exclude_id_arr && in_array($v['id'],$exclude_id_arr))$disabled = 'disabled="disabled"';

            $option .= "<option value='{$v['id']}' {$disabled} {$selected}>{$_prefix}{$v['name']}</option>";
            if($_val['children']){
                $option .= $this->_makeOption($v['children'],$level+1,$selected_id,$exclude_id_arr);
            }
        }
        return $option;
    }

    private function _getChildren($menu,$id,$count=0){
        $data = [];
        $count += 1;
        foreach ($menu as $k=>$v){
            if($v['pid'] == $id){
                $val = [
                    'id'    => $v['id'],
                    'name'  => $v['name'],
                    'pid'   => $v['pid'],
                    'path'  => $v['path'],
                    'sort'  => $v['sort'],
                    'children' => []
                ];
                unset($menu[$k]);
                $_children = $this->_getChildren($menu,$v['id'],$count);
                if($_children){
                    $val['children'] = $_children;
                }
                $data[] = $val;
            }
        }
        return $data;
    }

    private function _getSpace($num){
        $space = '';
        for($i=0;$i<$num;$i++){
            $space .= "&nbsp;";
        }
        return $space;
    }
}
