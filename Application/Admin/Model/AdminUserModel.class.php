<?php
namespace Admin\Model;

use Common\Model\AdminBaseModel;

class AdminUserModel extends AdminBaseModel {
    protected $tableName = 'user';

    protected $_validate = [
        ['user_name','require','请输入用户名',self::MUST_VALIDATE],
        ['user_name','checkNameExists','输入的用户名已存在',self::MUST_VALIDATE,'callback'],
        ['role_id','require','请选择角色',self::MUST_VALIDATE]
    ];

    protected $_auto = [
        ['add_time','x_datetime',self::MODEL_INSERT,'function'],
        ['token','makeToken',self::MODEL_INSERT,'callback'],
        ['user_pwd','encodePwd',self::MODEL_INSERT,'callback']
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

    protected function encodePwd($pwd){
        $salt = C('ADMIN_USER_SALT');
        return md5($pwd.$salt);
    }

    public function checkPwd($id,$pwd){
        $db_pwd = $this->where("id={$id}")->getField('user_pwd');
        $pwd = $this->encodePwd($pwd);
        if($db_pwd == $pwd)
            return true;
        else
            return false;
    }

    public function changePwd($id,$pwd){
        $pwd = $this->encodePwd($pwd);
        $token = $this->makeToken();
        return $this->where("id={$id}")->save(['user_pwd'=>$pwd,'token'=>$token]);
    }
}
