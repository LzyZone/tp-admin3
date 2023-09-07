<?php
namespace Admin\Controller;

use Admin\Logic\AdminMenuLogic;
use Common\Controller\AdminController;

class IndexController extends AdminController {
    protected $notCheckRules = ['index','welcome','change_pwd'];

    public function index(){
        $this->assign('data',[
            'menus' => (new AdminMenuLogic())->getMenuOnLeft(),
            'welcome_url' => U('welcome'),
            'change_pwd_url' => U('change_pwd'),
            'logout_url' => U('AdminLogin/logout'),
        ]);
        $this->display('Admin/Index/index');
    }

    public function welcome(){
        $this->display('Admin/Index/welcome');
    }


    public function change_pwd(){
        $userModel = D('AdminUser');
        if(IS_POST){
            $json = ['status'=>'n','msg'=>'操作失败'];
            try{
                $param = I('post.');
                if(empty($param['old_pwd']))throw new \Exception('请输入旧密码');
                if(empty($param['user_pwd']))throw new \Exception('请输入新密码');
                if($param['user_pwd'] != $param['user_pwd2'])throw new \Exception('两次输入的密码不一致');
                $checked = $userModel->checkPwd($this->auth->id,$param['old_pwd']);
                if(!$checked){
                    throw new \Exception('旧密码输入错误');
                }
                $ret = $userModel->changePwd($this->auth->id,$param['user_pwd']);
                if($ret){
                    $json = ['status'=>'y','msg'=>'操作成功','url'=>U('Login/logout')];
                }
            }catch (\Exception $e){
                $json['msg'] = $e->getMessage();
            }
            $this->ajaxReturn($json);
        }

        $this->display('Admin/Index/change_pwd');
    }

    public function init(){
        $model = D('AdminUser');
        $data = [
            'user_name' => 'admin',
            'user_pwd' => '123456',
            'role_id' => 1
        ];

        $flag = $model->create($data);
        if(!$flag){
            var_dump($model->getError(),1111);exit;
        }

        $flag = $model->add();
        var_dump($flag,2222);
    }
}
