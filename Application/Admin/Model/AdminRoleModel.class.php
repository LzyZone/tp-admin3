<?php
namespace Admin\Model;

use Common\Model\AdminBaseModel;

class AdminRoleModel extends AdminBaseModel {
    protected $tableName = 'role';

    protected $_validate = [
        ['role_name','require','请输入角色名称',self::MUST_VALIDATE],
        ['role_name','checkRoleExists','输入的角色名称已存在',self::MUST_VALIDATE,'unique']
    ];

    protected $_auto = [
        ['add_time','x_datetime',self::MODEL_INSERT,'function']
    ];

    protected function checkRoleExists($role_name){
        $where = ['is_delete'=>0,'role_name'=>$role_name];
        $role = $this->where($where)->find();
        return $role ? true : false;
    }


    public function getRoleAndMCA($role_id){
        $rolePerModel = D('AdminRolePer');
        $data = $this->where("id='{$role_id}' AND is_valid=1")->field('role_name,role_note')->find();
        $role_permission = $rolePerModel->where("role_id={$role_id}")->field('m,c,a')->select();
        $role_info = [
            'role_name' => $data['role_name'],
            'role_note' => $data['role_note'],
            'cma' => [],
        ];
        if($data && $role_permission){
            foreach ($role_permission as $v){
                $role_info['mca'][] = strtolower($v['m'].$v['c'].$v['a']);
            }
        }
        return $role_info;
    }
}
