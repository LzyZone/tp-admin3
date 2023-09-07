<?php
namespace Admin\Logic;

class AdminUserLogic {
    private $model;
    public function __construct(){
        $this->model = D('AdminUser');
    }

    public function login($user_name,$user_pwd){
        $user_name = addslashes($user_name);
        $salt = C('ADMIN_USER_SALT');
        $pwd = md5($user_pwd.$salt);
        $user = $this->model->where(['user_name'=>$user_name,'is_delete'=>0])->find();
        //用户不存在
        if(empty($user)){
            return false;
        }

        //用户被禁止登录
        if($user['is_valid'] != 1){
            return -1;
        }

        $max_err_limit = C('ADMIN_MAX_ERR_LIMIT');
        //登录失败次数达到上限
        if($user['err_limit'] && $user['err_limit'] > $max_err_limit){
            return -2;
        }

        //匹配密码
        if($pwd !== $user['user_pwd']){
            $this->model->where("id={$user['id']}")->setInc('err_limit');
            return -3;
        }

        unset($user['user_pwd']);

        //更新最后登录时间和ip
        $this->model->where("id={$user['id']}")->save([
            'err_limit' => 0,
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => get_client_ip(),
        ]);

        $role_info = D('AdminRole')->getRoleAndMCA($user['role_id']);
        $user['role_name'] = $role_info['role_name'];
        $user['role_note'] = $role_info['role_note'];
        $user['mca'] = $role_info['mca'];

        //设置session
        $session_prefix = C('ADMIN_USER_SESSION_NAME');
        session($session_prefix,$user);

        return $user;
    }
}
