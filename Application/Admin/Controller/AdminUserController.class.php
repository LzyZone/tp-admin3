<?php
namespace Admin\Controller;

use Admin\Logic\AdminMenuLogic;
use Admin\Util\Constant;
use Common\Controller\AdminController;
use Think\Page;

class AdminUserController extends AdminController {
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('AdminUser');
    }

    public function index(){
        $where = "a.is_delete=0";
        $total = $this->model->alias('a')
            ->join('__ROLE__ as b ON a.role_id=b.id','LEFT')
            ->where($where)->count();

        $pageObj = new Page($total);
        $list = $this->model->alias('a')
            ->field('a.*,b.role_name')
            ->join('__ROLE__ as b ON a.role_id=b.id','LEFT')
            ->where($where)
            ->limit($pageObj->firstRow,$pageObj->listRows)
            ->order('id desc')
            ->select();

        if($list){
            foreach ($list as &$v){
                $v['is_valid_label'] = '<span class="label label-default radius">禁用</span>';
                if($v['is_valid']){
                    $v['is_valid_label'] = '<span class="label label-success radius">有效</span>';
                    $_validate_url = U('ajax_change_status','type=off&id='.$v['id']);
                    $_validate_tips = '确定禁用?';
                    $_validate_icon = '&#xe631;';
                }else{
                    $_validate_url = U('ajax_change_status','type=on&id='.$v['id']);
                    $_validate_tips = '确定启用?';
                    $_validate_icon = '&#xe6e1;';
                }
                $_edit_url = U('edit','id='.$v['id']);
                $_del_url = U('ajax_del','type=del&id='.$v['id']);

                $v['reset_err_url'] = U('ajax_reset_err','type=reset_err&id='.$v['id']);
                $v['action'] = '';

                $_change_pwd_action = $_validate_action = $_del_action = '';
                //只允许修改非当前用户
                if($this->auth->id != $v['id']){
                    $_change_pwd_url = U('change_pwd','id='.$v['id']);
                    $_change_pwd_action = '<a title="修改密码" href="javascript:;" onclick="layer_show(\'修改密码\',\''.$_change_pwd_url.'\',600,300)" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe63f;</i></a>';
                    $_del_action = '<a title="删除" href="javascript:;" onclick="layer_confirm(\''.$_del_url.'\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>';
                    $_validate_action = '<a title="'.$_validate_tips.'" href="javascript:;" onclick="layer_confirm(\''.$_validate_url.'\',\''.$_validate_tips.'\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">'.$_validate_icon.'</i></a>';
                }

                if($v['id'] > 1){
                    $v['action'] .= <<<EOF
{$_validate_action}
{$_change_pwd_action}
<a title="编辑" href="javascript:;" onclick="layer_show('编辑','{$_edit_url}',700,400)" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
{$_del_action}
EOF;
                }else{
                    $v['action'] .= <<<EOF
<a title="编辑" href="javascript:;" onclick="layer_show('编辑','{$_edit_url}',700,400)" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
EOF;
                }
            }
            unset($v);
        }
        $this->assign('data',[
            'add_url' => U('add'),
            'total' => $total,
            'list' => $list
        ]);
        $this->display('Admin/User/index');
    }

    public function add(){
        $this->_modify();

        $roleModel = D('AdminRole');
        if($this->auth->role_id == Constant::SUPER_ROLE_ID){
            $roles = $roleModel->where("is_valid=1")->field('id,role_name')->select();
        }else{
            $roles = $roleModel->where("is_valid=1 AND id > 1")->field('id,role_name')->select();
        }

        $this->assign('data',[
            'roles' => $roles,
            'max_err_limit' => C('ADMIN_MAX_ERR_LIMIT')
        ]);
        $this->display('Admin/User/add');
    }

    public function change_pwd(){
        if(IS_POST){
            $json = ['status'=>'n','msg'=>'操作失败'];
            try{
                $param = I('post.');
                if(empty($param['id']))throw new \Exception('修改失败');
                if(empty($param['user_pwd']))throw new \Exception('请输入密码');
                if($param['user_pwd'] != $param['user_pwd2'])throw new \Exception('两次输入的密码不一致');
                $ret = $this->model->changePwd($param['id'],$param['user_pwd']);
                if($ret){
                    $json = ['status'=>'y','msg'=>'操作成功'];
                }
            }catch (\Exception $e){
                $json['msg'] = $e->getMessage();
            }
            $this->ajaxReturn($json);
        }
        $id = I('get.id',0,'intval');
        $user_name = $this->model->where("id={$id}")->getField('user_name');
        $this->assign('data',[
            'id' => $id,
            'user_name' => $user_name
        ]);
        $this->display('Admin/User/change_pwd');
    }

    public function edit(){
        $this->_modify();
        $id = I('get.id',0,'intval');
        $data = $this->model->where("id='{$id}'")->find();
        if(empty($data))$this->error('操作错误');
        $data['icon'] = htmlspecialchars($data['icon']);
        $options = (new AdminMenuLogic())->getMenuSelectOption($data['pid'],$data['id']);

        $roleModel = D('AdminRole');
        if($this->auth->role_id == Constant::SUPER_ROLE_ID){
            $roles = $roleModel->where("is_valid=1")->field('id,role_name')->select();
        }else{
            $roles = $roleModel->where("is_valid=1 AND id > 1")->field('id,role_name')->select();
        }
        $data['roles'] = $roles;
        $data['max_err_limit'] = C('ADMIN_MAX_ERR_LIMIT');

        $this->assign('data',$data);
        $this->assign('options',$options);
        $this->display('Admin/User/modify');
    }

    public function ajax_change_status(){
        $this->_action();
    }

    public function ajax_del(){
        $this->_action();
    }

    public function ajax_reset_err(){
        $this->_action();
    }

    private function _modify(){
        if(IS_POST){
            $json = ['status'=>'n','msg'=>'操作失败'];
            try {
                $data = I('post.');
                if(!$this->model->create($data)){
                    throw new \Exception($this->model->getError());
                }

                if($data['id']){
                    $this->model->save();
                }else{
                    unset($data['id']);
                    $this->model->add();
                }

                $json = ['status'=>'y','msg'=>'操作成功'];

            }catch (\Exception $e){
                $json['msg'] = $e->getMessage();
            }
            $this->ajaxReturn($json);
        }
    }

    private function _action(){
        $json = ['status'=>'n','msg'=>'操作失败'];
        $param = I('get.');
        if(!empty($param['type']) && $param['id']){
            $param['id'] = intval($param['id']);
            $where = "id={$param['id']}";
            switch ($param['type']){
                case 'reset_err':
                    $this->model->where($where)->save(['err_limit'=>0]);
                    break;
                case 'on':
                    $this->model->where($where)->save(['is_valid'=>1]);
                    break;
                case 'off':
                    $this->model->where($where)->save(['is_valid'=>0]);
                    break;
                case 'del':
                    $this->model->where($where)->save([
                        'is_valid' => 0,
                        'is_delete' => 1,
                        'delete_time' => date('Y-m-d H:i:s')
                    ]);
                    break;
            }
            $json = ['status'=>'y','msg'=>'操作成功'];
        }
        $this->ajaxReturn($json);
    }
}
