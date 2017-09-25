<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Util\TreeUtil;
/**
 * 后台密码修改
 * @author [zhangqiang] <[zhq1ang@foxmail.com]>
 */
class PwdController extends Controller
{
    public function _initialize(){
        if (is_login()==0) {
            $this->redirect('Login/index');
        }
        $myrules = M('auth_group')->where(array('id'=>$_SESSION['admin_auth']['roleId']))->getfield('rules');
        $myrules = explode(',', $myrules);
        $mymenus = M('auth_rule')->where(array('id'=>array('in',$myrules)))->order('sorts asc,id asc')->select();

        $mymenus = TreeUtil::listToTreeMulti($mymenus, 0, 'id', 'pid', 'child');

        $this->mymenus = $mymenus;
    }
    /**
     * [密码修改]
     * @access public
     * @return void
     */
    public function index(){
        if(!IS_AJAX){
            $this->display();
            return;
        }

        
        $this->display();
    }

    public function savePwd() {
        // 密码修改
        $oldPwd = I('post.oldPwd');
        $pwd = I('post.pwd');
        $confirmPwd = I('post.confirmPwd');

        $uid = session('admin_auth.uid');
        $password = M('admin')->where(array('id'=>$uid))->getfield('password');

        if (encrypt_pass($oldPwd) !== $password) {
            $this->wrong('您输入的密码不正确！');
        }
        
        if ($pwd !== $confirmPwd) {
            $this->wrong('两次输入的密码不一致！');
        }

        $admin['password'] = encrypt_pass($pwd, C('UC_AUTH_KEY'));
        $result = M('admin')->where(array('id'=>$uid))->save($admin);
        if ($result === false) {
            $this->wrong('密码修改失败');
        } else {
            session('admin_auth', null);
            $this->ok('密码修改成功, 请重新登陆');
        }
    }

    /**
     * [ok 成功ajax返回]
     * @param [type] $msg [description]
     * @param array $data [description]
     * @return [type] [description]
     */
    protected function ok($msg, $data = array()) {
        $info = array (
            'status' => 1,
            'info' => $msg );
        if (! empty ( $data )) {
            $info ['data'] = $data;
        }
        
        $this->ajaxReturn ( $info );
    }

    /**
     * [wrong 失败ajax返回]
     * @param [type] $msg [description]
     * @return [type] [description]
     */
    protected function wrong($msg) {
        $info = array (
            'status' => 0,
            'info' => $msg );
        $this->ajaxReturn ( $info );
    }
}