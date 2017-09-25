<?php
namespace Admin\Controller;
 
use Common\Util\TreeUtil;

/**
 * 权限管理
 * @author xiegaolei
 *
 */
class ManagerController extends BaseController {

    // 控制器权限分块
    public $privilege=array(
                    'adminList' => array('adminAdd','adminEdit','adminSave','adminDel'),
                    'rolesList'   => array('rolesAdd', 'rolesEdit', 'rolesSave', 'rolesDel'),
                    'rulesList'   => array('rulesAdd', 'rulesEdit', 'rulesSave', 'rulesDel'),
    );

    /**
     * 管理员列表
     */
    public function adminList(){
        
        if (IS_POST){
            
            $draw = I('draw',1,'intval');
            //排序设置
            $mycolumns = I('mycolumns','');
            $myorder = I('order','');
            if (empty($myorder)||empty($mycolumns)) {
                $order = 'id asc';
            }else{
                $order = '';
                foreach ($myorder as $key => $v) {
                    $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
                }
                $order = rtrim($order , ',');
            }
            
            $admin = M('admin');
            $lists = $admin->order($order)->select();
            foreach ($lists as $key => $v){
                $lists[$key]['role'] = M('auth_group_access')->alias('a')->join('farm_auth_group b on a.group_id= b.id')->where(array('a.uid'=>$v['id']))->getField('title');
                $lists[$key]['loginTime'] = $v['loginTime']?date('Y/m/d H:i:s',$v['loginTime']):'';
                if ($v['status']) {
                    $lists[$key]['status'] = '正常';
                }else{
                    $lists[$key]['status'] = '禁用';
                }
            }
            
            $result['draw'] = $draw;
            $result['recordsTotal'] = $admin->count();
            $result['recordsFiltered'] = $result['recordsTotal'];
            $result['data'] = $lists;
        
            json_return($result);
        
        }else{
            $this->display();
        }
    }
      
    
    /**
     * 添加管理员
     */
    public function adminAdd(){
        
        $groups = M('auth_group')->order('id asc')->select();
        $this->assign('groups', $groups);
        
        $this->display();
    }
    
    /**
     * 编辑管理员
     */
    public function adminEdit(){
         
        $groups = M('auth_group')->order('id asc')->select();
        $this->assign('groups', $groups);
        
        $id = I('id','0','int');
        if (empty($id)){
            exit('参数有误！');
        }
        
        $admin = M('admin');
        $info = $admin->where(array('id'=>$id))->find();
        $this->assign('info',$info);
         
        $group_id = M('auth_group_access')->where(array('uid'=>$id))->getField('group_id');
        $this->assign('group_id',$group_id);
        
        $this->display();
    }
    
    
    /**
     * 保存管理员
     */
    public function adminSave(){
        
        //接收数据
        $data = array(
                'group'  => I('group','','trim'),
                'username'  => I('username','','trim'),
                'mobile'   => I('mobile','','trim'),
                'email' => I('email','','trim'),
                'password' => I('password','','trim'),
                'status' => I('status','','trim')
        );

        $id = I('id','0','int');

        if(empty($data['group']) && $id != 1){
            ajax_return_error('请选择角色！');
        }
        
        if(empty($data['username'])){
            ajax_return_error('账号必须！');
        }

        if(empty($data['password'])){
            unset($data['password']);
        }

        if(!empty($data['mobile']) && !check_mobile($data['mobile'])){
            ajax_return_error('手机格式错误！');
        }
        
        if(!empty($data['email']) && !check_email($data['email'])){
            ajax_return_error('邮箱格式错误！');
        }

        $admin = M('admin');
        if ($id){
            if(!empty($data['password'])){
                $data['password'] = encrypt_pass($data['password']);
            }
            if($this->checkAdmin($data['username']) && $id != $this->checkAdmin($data['username'])){
                ajax_return_error('该账号已存在');
            }

            if($id == 1){
                $data['group'] = 1;
                unset($data['status']);
            }
            $res = $admin->where(array('id'=>$id))->save($data);
        }else{
            
            if(empty($data['password'])){
                ajax_return_error('密码必须！');
            }else{
                $data['password'] = encrypt_pass($data['password']);
            }
            
            
            if($this->checkAdmin($data['username'])){
                ajax_return_error('该账号已存在');
            }
             

            $data['regTime'] = time();
            $data['regIp'] = get_client_ip();
            $data['updateTime'] = time();
            
            $res = $admin->add($data);
        }

        if ($res === false){
            ajax_return_error('操作失败！');
        }

        if (!$id){
            $id = $res;
        }
        
        //角色更新
        $group_id = M('auth_group_access')->where(array('uid'=>$id))->getField('group_id');

        if(empty($group_id)){
            //添加
            M('auth_group_access')->add(array('group_id' => $data['group'],'uid'=>$id));
        }elseif($data['group'] != $group_id) {
            //更新
            M('auth_group_access')->where(array('uid'=>$id))->save(array('group_id' => $data['group']));
        } 
         
        
        ajax_return_ok(array(),'操作成功！');
    }
    
    /**
     * 删除
     */
    public function adminDel(){
        $id = I('id','0','int');
        if ($id == 0 || $id == 1) {
            exit('参数有误！');
        }else{
            
            M('auth_group_access')->where(array('uid'=>$id))->delete();
        
            if (M('admin')->where(array('id'=>$id))->delete()) {
                ajax_return_ok(array(),'操作成功！');
            }else{
                ajax_return_error('操作失败！');
            }
        
        }
    }
    
    /**
     * 角色列表
     */
    public function rolesList(){
         
        if (IS_POST){
    
            $draw = I('draw',1,'intval');
            //排序设置
            $mycolumns = I('mycolumns','');
            $myorder = I('order','');
            if (empty($myorder)||empty($mycolumns)) {
                $order = 'id asc';
            }else{
                $order = '';
                foreach ($myorder as $key => $v) {
                    $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
                }
                $order = rtrim($order , ',');
            }
    
            $roles = M('auth_group');
            $lists = $roles->order($order)->select();
            foreach ($lists as $key => $v){
                if ($v['status']) {
                    $lists[$key]['status'] = '正常';
                }else{
                    $lists[$key]['status'] = '禁用';
                }
            }
            
            $result['draw'] = $draw;
            $result['recordsTotal'] = $roles->count();
            $result['recordsFiltered'] = $result['recordsTotal'];
            $result['data'] = $lists;
             
            json_return($result);
             
        }else{
            $this->display();
        }
    }
    
    /**
     * 添加角色
     */
    public function rolesAdd(){
         
        $rules = M('auth_rule')->where(array('status'=>1))->order('sorts asc')->select();
        //格式化成树形
        $rules = TreeUtil::listToTreeMulti($rules, 0, 'id', 'pid', 'child');
        
        $this->assign('rules', $rules);
        $this->display();
    }
    
    /**
     * 编辑角色
     */
    public function rolesEdit(){
    
        $rules = M('auth_rule')->where(array('status'=>1))->order('sorts asc')->select();
        //格式化成树形
        $rules = TreeUtil::listToTreeMulti($rules, 0, 'id', 'pid', 'child');
        $this->assign('rules', $rules);
         
        $id = I('id','0','int');
        if($id == 1){
            $url = $_SERVER['HTTP_REFERER'];
            header("Location:$url");exit;
        }
        if (empty($id)){
            exit('参数有误！');
        }
         
        $roles = M('auth_group');
        $info = $roles->where(array('id'=>$id))->find();
        $info['rules'] = explode(',', $info['rules']);
        $this->assign('info',$info);
    
        $this->display();
    }
    
    /**
     * 保存角色
     */
    public function rolesSave(){
         
        //接收数据
        $data = array(
                'title'  => I('title','','trim'),
                'rules'  => I('rules',''),
                'status' => I('status','','trim')
        );
        
        if(empty($data['title'])){
            ajax_return_error('名称必填！');
        }
        
        if(empty($data['rules'])){
            ajax_return_error('权限必选！');
        }
        
          
        $data['rules'] =implode(',', $data['rules']);
        
        $id = I('id','0','int');
        $roles = M('auth_group');
        if ($id){
            
            if($this->checkRoles($data['title']) && $id != $this->checkRoles($data['title'])){
                ajax_return_error('该名称已存在');
            }
            
            $res = $roles->where(array('id'=>$id))->save($data);
        }else{
            
            if($this->checkRoles($data['title'])){
                ajax_return_error('该名称已存在');
            }
            
            $res = $roles->add($data);
        }
          
        if ($res){
            ajax_return_ok(array(),'操作成功！');
        }else{
            ajax_return_error('操作失败！');
        }
        
    }
    
    
    /**
     * 删除
     */
    public function rolesDel(){
        $id = I('id','0','int');
        if ($id == 0 || $id == 1) {
            exit('参数有误！');
        }else{
             
            if (M('auth_group')->where(array('id'=>$id))->delete()) {
                ajax_return_ok(array(),'操作成功！');
            }else{
                ajax_return_error('操作失败！');
            }
             
        }
    }
    
     
    
    /**
     * 权限列表
     */
    public function rulesList(){
    
        if (IS_POST){
    
            $draw = I('draw',1,'intval');
            //排序设置
            $mycolumns = I('mycolumns','');
            $myorder = I('order','');
            if (empty($myorder)||empty($mycolumns)) {
                $order = 'id asc';
            }else{
                $order = '';
                foreach ($myorder as $key => $v) {
                    $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
                }
                $order = rtrim($order , ',');
            }
    
            $roles = M('auth_rule');
            $lists = $roles->order($order)->select();
            foreach ($lists as $key => $v){
                if ($v['status']) {
                    $lists[$key]['status'] = '正常';
                }else{
                    $lists[$key]['status'] = '禁用';
                }
            }
            //格式化处理
            $lists = TreeUtil::listToTreeOne( $lists ,  0 , '|— ' , 'id' , 'pid' , 'html');
            
            $result['draw'] = $draw;
            $result['recordsTotal'] = $roles->count();
            $result['recordsFiltered'] = $result['recordsTotal'];
            $result['data'] = $lists;
             
            json_return($result);
             
        }else{
            $this->display();
        }
    }
    
    
    
    /**
     * 添加权限
     */
    public function rulesAdd(){
    
        $rules = M('auth_rule')->where(array('status'=>1))->order('sorts asc')->select();
        //格式化处理
        $rules = TreeUtil::listToTreeOne( $rules ,  0 , '|— ' , 'id' , 'pid' , 'html');
         
        $this->assign('rules', $rules);
        $this->display();
    }
    
    /**
     * 编辑权限
     */
    public function rulesEdit(){
    
        $id = I('id','0','int');
        if (empty($id)){
            exit('参数有误！');
        }
        $rules = M('auth_rule')->where(array('status'=>1))->order('sorts asc')->select();
        //格式化处理
        $rules = TreeUtil::listToTreeOne( $rules ,  0 , '|— ' , 'id' , 'pid' , 'html');
        $this->assign('rules', $rules);
        
        $info = M('auth_rule')->where(array('id'=>$id))->find();
        $this->assign('info',$info);
        
        $this->display();
    }
    
    /**
     * 保存权限
     */
    public function rulesSave(){
    
        //接收数据
        $data = array(
                'pid'  => I('pid','','int'),
                'title'  => I('title','','trim'),
                'name'  => I('name','','trim'),
                'status' => I('status','','trim')
        );
         
        if(empty($data['title'])){
            ajax_return_error('名称必填！');
        }
         
        if(empty($data['name'])){
            ajax_return_error('标识必填！');
        }
             
         
        $id = I('id','0','int');
        $rules = M('auth_rule');
        if ($id){
            $res = $rules->where(array('id'=>$id))->save($data);
        }else{
            $res = $rules->add($data);
        }
         
        if ($res){
            ajax_return_ok(array(),'操作成功！');
        }else{
            ajax_return_error('操作失败！');
        }
         
    }
    
    
    /**
     * [sortColum 栏目排序]
     * @return [type] [description]
     */
    public function rulesSort(){
        $listOrder = $_POST['listOrder'];
        
        if (empty($listOrder)) {
            ajax_return_error('没有数据！');
        }else{
            $rules = M('auth_rule');
            foreach ($listOrder as $id => $sorts){
                $rules->where(array('id'=>$id))->save(array('sorts'=>$sorts));
            }
            
            ajax_return_ok(array(),'操作成功！');
        }
    
    }
    
    
    
    /**
     * 删除
     */
    public function rulesDel(){
        $id = I('id','0','int');
        if ($id == 0) {
            exit('参数有误！');
        }else{
             
            if (M('auth_rule')->where(array('id'=>$id))->delete()) {
                ajax_return_ok(array(),'操作成功！');
            }else{
                ajax_return_error('操作失败！');
            }
             
        }
    }
    
    
    
    /**
     * [checkAdmin 检测用户名是否存在]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    protected function checkAdmin($name){
        $id = M('admin')->where(array('username'=>$name))->getField('id');
        if (empty($id)) {
            return 0;
        }else{
            return $id;
        }
    }
    
    
    /**
     * [checkName 检测角色是否存在]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    protected function checkRoles($name){
        $id = M('auth_group')->where(array('title'=>$name))->getField('id');
        if (empty($id)) {
            return 0;
        }else{
            return $id;
        }
    }
    
    
    
}
