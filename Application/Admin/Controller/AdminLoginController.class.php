<?php
namespace Admin\Controller;

use Admin\Logic\AdminUserLogic;
use Common\Controller\AdminController;
use Think\Verify;

class AdminLoginController extends AdminController {
    protected $checkLogin = false;

    public function index(){
        if(IS_POST){
            $loginLogic = new AdminUserLogic();
            $json = ['status'=>'n','msg'=>'登录失败'];
            try{
                $param = I('post.');
                if(empty($param['user_name']))throw new \Exception('请输入登录账号');
                if(empty($param['user_pwd']))throw new \Exception('请输入登录账号');
                if(empty($param['code']))throw new \Exception('请输入验证码');

//                if(!(new Verify())->check($param['code'])){
//                    throw new \Exception('验证码输入错误');
//                }

                $ret = $loginLogic->login($param['user_name'],$param['user_pwd']);
                if(empty($ret)){
                    throw new \Exception('账号或密码错误');
                }elseif($ret == -1){
                    throw new \Exception('该账号被禁止登录');
                }elseif($ret == -2){
                    throw new \Exception('该账号被锁定');
                }elseif($ret == -3){
                    throw new \Exception('账号或密码错误');
                }

                $json = ['status'=>'y','msg'=>'登录成功','url'=>U('Index/index')];

            }catch (\Exception $e){
                $json['msg'] = $e->getMessage();
            }

            $this->ajaxReturn($json);
        }
        $this->assign('data',[
            'verify_url' => U('verify')
        ]);
        $this->display();
    }

    /**
     * 退出
     */
    public function logout(){
        //$session_prefix = C('ADMIN_USER_SESSION_NAME');
        session(null);
        redirect('index');
    }

    /**
     * 验证码
     */
    public function verify(){
        $verify = new Verify();
        $verify->entry();
    }
}
