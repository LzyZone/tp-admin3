<?php
namespace Common\Controller;

use Think\Controller;

/**
 * 后台基类，后台所有控制器需要继承
 * @package Common\Controller
 * @author GaryLee<321539047@qq.com>
 * @create 2023/8/7 10:55
 */
abstract class AdminController extends Controller {
    /**
     * @var bool 是否要验证登录
     */
    protected $checkLogin = true;
    /**
     * @var array 不需要验证权限
     */
    protected $notCheckRules = [];

    /**
     * @var null
     */
    protected $auth = null;

    public function _initialize(){
        $this->_setAuth();

        //检查是否需要登录
        if($this->checkLogin){
            if(!$this->isLogin()){
                if(IS_AJAX){
                    $this->ajaxReturn(['status'=>'n','msg'=>'请选登录系统']);
                }else{
                    redirect(U('AdminLogin/index'));exit;
                }
            }

            //检查权限
            if(!$this->checkRule()){
                if(IS_AJAX){
                    $this->ajaxReturn(['status'=>'n','msg'=>'无权限操作']);
                }else{
                    $this->error('无权限操作');
                }
            }
        }

        $this->assign('auth',$this->auth);
        $this->assign('site',[
            'v' => APP_STATUS == 'prod' ? C('static_v') : time(),
            'title' => C('SITE_TITLE'),
            'url' => U('ndex/index')
        ]);
    }

    private function _setAuth(){
        $session_prefix = C('ADMIN_USER_SESSION_NAME');
        $user_auth = session($session_prefix);
        if(empty($user_auth)){
            return false;
        }
        $this->auth = (object)$user_auth;
    }

    /**
     * 验证是否登录
     */
    protected function isLogin(){
        if(empty($this->auth)){
            return false;
        }
        return true;
    }

    /**
     * 验证权限
     * @return bool
     */
    protected function checkRule(){
        $action = strtolower(ACTION_NAME);
        /**
         * 以public_开头则跳过验证
         */
        if(strpos($action,'public_') === 0){
            return true;
        }

        //检查是否不需要验证
        if(is_string($this->notCheckRules)){
            //跳过该controller所有的方法
            if($this->notCheckRules == '*')return true;
        }else{
            foreach ($this->notCheckRules as $a){
                if($a == $action){
                    return true;
                }
            }
        }

        //检查用户权限
        //超级管理员跳过权限
        if($this->auth->role_id == 1){
            return true;
        }

        $model = str_replace('/','',__MODULE__);
        $controller = CONTROLLER_NAME;
        $mca = strtolower($model.$controller.$action);

        //验证权限
        if(in_array($mca,$this->auth->mca)){
            return true;
        }

        return false;
    }
}
