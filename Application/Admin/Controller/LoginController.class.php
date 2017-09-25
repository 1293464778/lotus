<?php
namespace Admin\Controller;
 
/**
 * 登录
 * @author xiegaolei
 *
 */
class LoginController extends \think\Controller
{    
    
    /**
     * 登录页
     */
    public function index(){
     
        if(is_login()){
            $this->redirect('index/index');
        }
        
        $this->display();
        
    }
    
    /**
     * 登录验证
     */
    public function loginAuth(){
        if (IS_POST){
            
            //接收数据
            $data = array(
                    'username'  => I('username','','trim'),
                    'password'   =>I ('password','','trim'),
                    'verify' => I('verify','','trim')
            );
             
            if(empty($data['username'])){
                ajax_return_error('账号必须！');
            }
            
            if(empty($data['password'])){
                ajax_return_error('密码必须！');
            }
            
            if(empty($data['verify'])){
                ajax_return_error('验证码必须！');
            }
            
            if(!check_verify($data['verify'])){
                ajax_return_error('验证码输入错误！');
            }
             
            
            //账号验证
            $uid = $this->logins($data['username'], $data['password']);
            if(0 < $uid){ //登录成功
            
                ajax_return_ok(array('url'=>U("Index/index")),"登录成功！");
                 
            
            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    case -3: $error = '用户组不存在或被禁用！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                ajax_return_error($error);
            }
        }
        
        
    }
    
    
    
    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function logins($username, $password){
         //用户
        $admin = M('admin')->where(array('username'=>$username))->find();
        if (empty($admin)){
            return -1;
        }
        //角色
        $role =M('auth_group_access')
                 ->alias('a')
                 ->field('b.*')
                 ->join('farm_auth_group b on a.group_id = b.id')
                 ->where(array('uid'=>$admin['id']))
                 ->find();
            
        if (empty($role) || $role['status'] != 1) {
            return -3;
        }
    
        if(is_array($admin) && $admin['status']){
            /* 验证用户密码 */
            if(encrypt_pass($password) === $admin['password']){
                /* 记录登录SESSION */
                $auth = array(
                        'uid'             => $admin['id'],
                        'username'        => $admin['username'],
                        'loginTime'          => $admin['loginTime'],
                        'role'              => $role['title'],
                        'roleId'          => $role['id'],
                );
    
                session('admin_auth', $auth);
                session('admin_auth_sign', data_auth_sign($auth));
                $this->updateLogin($admin['id'],$admin['username'],$role['title']); //更新用户登录信息
                return $admin['id']; //登录成功，返回用户ID
            } else {
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }
    
    /**
     * 退出登录
     */
    public function logout(){
        if(is_login()){
            session('admin_auth', null);
            session('admin_auth_sign', null);
            session('[destroy]');
            $this->redirect('Login/index');
        } else {
            $this->redirect('Login/index');
        }
    }
    
    
    
    /**
     * 更新用户登录信息
     * @param  integer $uid 用户ID
     */
    protected function updateLogin($uid,$username,$role){
        $data = array(
                'loginTime'   => time(),
                'loginIp'     => get_client_ip(),
        );
        M('admin')->where(array('id'=>$uid))->save($data);
        
        $data['uid'] = $uid;
        $data['username'] = $username;
        $data['roles'] = $role;
        M('login_log')->add($data);
        
    }
    
     
    public function verify(){
        $config=array(
                'useCurve'  =>  false,    // 是否画混淆曲线
                'useNoise'  =>  false,    // 是否添加杂点
                'length'    =>  4,
    
        );
        $verify = new \Think\Verify($config);
        $verify->entry(1);
    }
    
    
    
}
