<?php
namespace Admin\Model;

use Common\Model\AdminBaseModel;

class AdminMenuModel extends AdminBaseModel {
    protected $tableName = 'menu';
    protected $hooks;
    protected $_validate = [
        ['name','require','请输入菜单名称',self::MUST_VALIDATE],
        ['m','require','请输入模块名称',self::MUST_VALIDATE],
        ['c','require','请输入控制器名称',self::MUST_VALIDATE],
        ['a','require','请输入方法名称',self::MUST_VALIDATE]
    ];

    protected $_auto = [
        ['mca','getMCA',self::MODEL_BOTH,'callback'],
        ['update_time','x_datetime',self::MODEL_BOTH,'function'],
        ['add_time','x_datetime',self::MODEL_INSERT,'function'],
    ];

    protected function getMCA(){
        $param = I('post.*');
        $m = strtolower($param['m']);
        $c = strtolower($param['c']);
        $a = strtolower($param['a']);
        return $m.$c.$a;
    }

    protected function _before_insert(&$data,$options){
        if(empty($data['mca'])){
            $m = $data['m'] ?? 'admin';
            $c = strtolower($data['c']);
            $a = strtolower($data['a']);
            $data['mca'] = strtolower($m).$c.$a;
        }
        return true;
    }

    protected function _after_insert($data,$options){
        $id = $data['id'];
        $where = ['id'=>$id];
        $pid = $this->where($where)->getField('pid');
        if($pid){
            $path = $this->where("id={$pid}")->getField('path');
            $path = $path.','.$id;
        }else{
            $path = $id;
        }
        return $this->where($where)->save(['path'=>$path]);
    }

    // 更新数据前的回调方法
    protected function _before_update(&$data, $options)
    {
        $old = $this->where($options['where'])->find();
        //调整父级菜单
        if($old['pid'] != $data['pid']){
            $data['hook'] = [
                'type' => 'update_path',
                'data' => $old
            ];
        }
        return true;
    }

    /**
     * 数据更新后执行的方法
     * @param $data
     * @param $options
     * @return bool|void
     */
    protected function _after_update($data, $options){
        if(!empty($data['hook'])){
            $hook_type = $data['hook']['type'];
            $hook = $data['hook']['data'];
            switch ($hook_type){
                case 'update_path':
                    $change = $this->where(['id'=>$data['pid']])->field('pid,path')->find();
                    //当前为非一级菜单
                    if($hook['pid']){
                        $pos = strpos($hook['path'],strval($hook['id']));
                        $path = substr($hook['path'],0,$pos);
                        //目标菜单为非一级菜单
                        if($change['pid']){
                            $replace_path = $change['path'].',';
                        }else{//目标菜单为一级菜单
                            $replace_path = '';
                        }
                    }else{
                        $path = $hook['id'];
                        $replace_path = $change['path'].','.$hook['path'];
                    }

                    $table = $options['table'];
                    $sql = "UPDATE {$table} SET path = replace(path,'{$path}','{$replace_path}') WHERE path like '{$hook['path']}%'";
                    //这里不允许使用save方法，不然会死循环
                    $this->query($sql);
                    break;
            }
        }
        return true;
    }

    public function getRoleAndMCA($role_id){
        $rolePerModel = D('AdminRolePer');
        $table = $rolePerModel->getTableName();
        $data = $this->alias('a')->join($table.' as b ON a.id=b.role_id','LEFT')
            ->where("a.id='{$role_id}' AND a.is_valid=1")->field('a.role_name,a.role_note,b.data')->find();
        $role_info = [
            'role_name' => $data['role_name'],
            'role_note' => $data['role_note'],
            'cma' => [],
        ];
        if($data && $data['b.data']){
            $data_arr = json_decode($data['b.data'],true);
            foreach ($data_arr as $v){
                $role_info['cma'][] = $v['m'].$v['c'].$v['a'];
            }
        }
        return $role_info;
    }
}
