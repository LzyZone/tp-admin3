<?php
namespace Admin\Logic;

class AdminRolePerLogic {
    private $model;
    public function __construct(){
        $this->model = D('AdminRolePer');
    }

    public function getPermission($role_id){
        return (array)$this->model->where("role_id={$role_id}")->getField('menu_id',true);
    }

    public function setPermission($role_id,$menu_ids){
        $this->model->where("role_id={$role_id}")->delete();
        if(!empty($menu_ids)){
            $menus = D('AdminMenu')->where(['id'=>['in',$menu_ids]])->field('id,m,c,a')->select();
            $data = [];
            foreach ($menus as $menu){
                $data[] = [
                    'role_id' => $role_id,
                    'menu_id' => $menu['id'],
                    'm' => $menu['m'],
                    'c' => $menu['c'],
                    'a' => $menu['a']
                ];
            }
            $this->model->addAll($data);
        }
        return true;
    }

}
