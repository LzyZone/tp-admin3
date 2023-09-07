<?php
namespace Admin\Controller;

use Admin\Logic\AdminMenuLogic;
use Admin\Logic\AdminRolePerLogic;
use Common\Controller\AdminController;
use Think\Page;

class AdminRoleController extends AdminController {
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('AdminRole');
    }

    public function index(){
        $where = "is_delete=0";
        $total = $this->model->where($where)->count('id');
        $pageObj = new Page($total);
        $list = $this->model->where($where)->order('id desc')->limit($pageObj->firstRow,$pageObj->listRows)->select();
        if($list){
            foreach ($list as &$v){
                $v['is_valid_label'] = '<span class="label label-default radius">无效</span>';

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
                $_permission_url = U('permission','id='.$v['id']);

                $v['action'] = '';
                if($v['id'] > 1){
                    $v['action'] .= <<<EOF
<a title="设置权限" href="javascript:;" onclick="layer_show('设置权限[{$v['role_name']}]','{$_permission_url}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe60e;</i></a>
<a title="{$_validate_tips}" href="javascript:;" onclick="layer_confirm('{$_validate_url}','{$_validate_tips}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">{$_validate_icon}</i></a>
<a title="编辑" href="javascript:;" onclick="layer_show('编辑[{$v['role_name']}]','{$_edit_url}',600,300)" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
<a title="删除" href="javascript:;" onclick="layer_confirm('{$_del_url}')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a>
EOF;
                }else{
                    $v['action'] .= <<<EOF
<a title="编辑" href="javascript:;" onclick="layer_show('编辑','{$_edit_url}',600,300)" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
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
        $this->display('Admin/Role/index');
    }

    public function add(){
        $this->_modify();
        $this->assign('data',[]);
        $this->display('Admin/Role/modify');
    }

    public function edit(){
        $this->_modify();
        $id = I('get.id',0,'intval');
        $data = $this->model->where("id='{$id}'")->find();
        if(empty($data))$this->error('操作错误');
        $data['icon'] = htmlspecialchars($data['icon']);
        $options = (new AdminMenuLogic())->getMenuSelectOption($data['pid'],$data['id']);
        $this->assign('data',$data);
        $this->assign('options',$options);
        $this->display('Admin/Role/modify');
    }

    /**
     * 权限设置
     */
    public function permission(){
        $rolePerLogic = new AdminRolePerLogic();
        if(IS_POST){
            $data = I('post.');
            $rolePerLogic->setPermission($data['id'],$data['menu_ids']);
            $this->ajaxReturn(['status'=>'y','msg'=>'操作成功']);
        }
        $id = I('get.id',0,'intval');
        $permission = $rolePerLogic->getPermission($id);
        $this->assign('data',[
            'id'    => $id,
            'menus' => (new AdminMenuLogic())->getMenuCheckbox($permission)
        ]);
        $this->display('Admin/Role/permission');
    }

    public function ajax_change_status(){
        $this->_action();
    }

    public function ajax_del(){
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
                case 'on':
                    $this->model->where($where)->save(['is_valid'=>1]);
                    break;
                case 'off':
                    $this->model->where($where)->save(['is_valid'=>0]);
                    break;
                case 'del':
                    $this->model->where($where)->save(['is_valid'=>0,'is_delete'=>1,'delete_time'=>date('Y-m-d H:i:s')]);
                    break;
            }
            $json = ['status'=>'y','msg'=>'操作成功'];
        }
        $this->ajaxReturn($json);
    }
}
