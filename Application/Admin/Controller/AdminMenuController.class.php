<?php
namespace Admin\Controller;

use Admin\Logic\AdminMenuLogic;
use Common\Controller\AdminController;

class AdminMenuController extends AdminController {
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('AdminMenu');
    }

    public function index(){
        $logic = new AdminMenuLogic();
        $tbody = $logic->getMenuTable();

        $this->assign('data',[
            'tbody' => ($tbody),
            'add_url' => U('add'),
            'set_sort_url' => U('ajax_set_sort','type=sort'),
            'batch_add_url' => U('batch_add')
        ]);
        $this->display('Admin/Menu/index');
    }

    public function add(){
        $this->_modify();
        $param = I('get.');
        $pid = !empty($param['pid']) ? $param['pid'] : 0;
        $options = (new AdminMenuLogic())->getMenuSelectOption($pid);
        $this->assign('data',['is_display'=>1]);
        $this->assign('options',$options);
        $this->display('Admin/Menu/modify');
    }

    public function batch_add(){
        if(IS_POST){
            $json = ['status'=>'n','msg'=>'操作失败'];
            try {
                $param = I('post.');
                if(empty($param['content']))throw new \Exception('请输入菜单名称');
                $pid = isset($param['pid']) ? intval($param['pid']) : 0;
                $is_parent = isset($param['is_parent']) ? intval($param['is_parent']) : 0;

                $content_arr = explode("\n",$param['content']);
                $this->model->startTrans();
                $ret_arr = [];
                foreach ($content_arr as $k=>$v){
                    list($name,$mca) = explode(' ',$v);
                    $mca = ltrim($mca,'/');
                    $sp_count = substr_count($mca,'/');
                    if($sp_count == 1)
                        $mca = 'admin/'.$mca;
                    elseif($sp_count != 2){
                        throw new \Exception('菜单名称格式输入错误1');
                    }

                    list($m,$c,$a) = explode('/',$mca);
                    if(empty($m) || empty($c) || empty($a))throw new \Exception('菜单名称格式输入错误2');

                    $_pid = $pid;
                    if($is_parent){
                        if($k == 0)
                            $_pid = $pid;
                        elseif($k == 1)
                            $_pid = $ret_arr[0];
                    }

                    $_val = [
                        'pid' => $_pid,
                        'name' => $name,
                        'm' => $m,
                        'c' => $c,
                        'a' => $a
                    ];

                    $ret_arr[$k] = $this->model->add($_val);
                }

                if(array_search($ret_arr,false)){
                    $this->model->rollback();
                    throw new \Exception('操作失败，服务器错误');
                }else{
                    $this->model->commit();
                }

                $json = ['status'=>'y','msg'=>'操作成功'];

            }catch (\Exception $e){
                $json['msg'] = $e->getMessage();
            }
            $this->ajaxReturn($json);
        }
        $options = (new AdminMenuLogic())->getMenuSelectOption();
        $this->assign('data',['is_parent'=>0]);
        $this->assign('options',$options);
        $this->display('Admin/Menu/batch_add');
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
        $this->display('Admin/Menu/modify');
    }

    public function ajax_set_sort(){
        $this->_action();
    }

    public function ajax_del(){
        $this->_action();
    }

    private function _modify(){
        if(IS_POST){
            $json = ['status'=>'n','msg'=>'操作失败'];
            try {
                //$this->model->startTrans();
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

                //$this->model->rollback();
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
                case 'sort':
                    $sort = isset($param['val']) ? intval($param['val']) : 0;
                    $this->model->where($where)->save(['sort'=>$sort]);
                    break;
                case 'del':
                    $data = $this->model->where($where)->find();
                    $this->model->startTrans();
                    //删除自己
                    $ret1 = $this->model->where("id={$data['id']}")->delete();
                    $child = $this->model->where("path like '{$data['path']},%'")->find();
                    if($child){
                        //删除子节点菜单
                        $ret2 = $this->model->where("path like '{$data['path']},%'")->delete();
                        if(!$ret1 || !$ret2){
                            $this->model->rollback();
                        }else{
                            $this->model->commit();
                        }
                    }elseif(!$ret1){
                        $this->model->rollback();
                    }else{
                        $this->model->commit();
                    }
                    break;
            }
            $json = ['status'=>'y','msg'=>'操作成功'];
        }
        $this->ajaxReturn($json);
    }
}
