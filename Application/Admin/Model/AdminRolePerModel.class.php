<?php
namespace Admin\Model;

use Common\Model\AdminBaseModel;

class AdminRolePerModel extends AdminBaseModel {
    protected $tableName = 'role_per';

    protected $_validate = [
        ['role_name','require','请输入用户名',self::MUST_VALIDATE],
        ['role_name','checkNameExists','输入的用户名已存在',self::MUST_VALIDATE,'callback'],
        ['role_id','require','请选择角色',self::MUST_VALIDATE]
    ];

    protected $_auto = [
        ['add_time','x_datetime',self::MODEL_INSERT,'function'],
        ['token','makeToken',self::MODEL_INSERT,'callback']
    ];

    protected function checkNameExists($name){
        $where = ['user_name'=>$name,'is_delete'=>0];
        $id = I('post.id',0);
        if($id)$where['id'] = ['neq',$id];
        $exists = $this->where($where)->find();
        return $exists ? false : true;
    }

    protected function makeToken(){
        return md5(uniqid());
    }
}
