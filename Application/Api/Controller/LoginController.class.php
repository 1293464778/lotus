<?php
namespace Api\Controller;
use Think\Controller;
use Common\Util\AuthUtil;
use Common\Util\ArrayUtil;

/**
 * 用户登录退出
 */
class LoginController extends Controller {
	public function _initialize(){

        //接口签名验证
        $result = AuthUtil::checkSign();
        if (!$result['status']){

            ajax_return_error(null,$result['code']);
        }
    }

    /**
     * 登录
     */
    public function login() {
        $start=M("config")->where("id=30")->getField("value");
        $end=M("config")->where("id=31")->getField("value");
        $time=date("H:i");
        if($time>=$start&&$time<=$end){
            $info=M("config")->where("id=33")->getField("value");
            ajax_return_error($info);
        }
        // 登录主名称：手机号
        $name = I ( 'phone', '' ,'trim');
        // 未加密的密码
        $pass = I ( 'password', '' ,'trim');
        if (empty($name)) {
            ajax_return_error('请填写账号！');
        }
        if (empty($pass)){
            ajax_return_error('请填写密码！');
        }
        // 登录验证并获取包含访问令牌的用户
        $result = D('User')->login ( $name, $pass );
        $data = array ('userAccessToken' => $result ['userAccessToken'],'user' => $result['user'] );
        ajax_return_ok($data,'登录成功');
    }

    /**
     * 登出
     * 已知bug：登出的附加操作依赖session中的用户缓存，而logout方法自身并不提供用户缓存，因此这并不总是有效。
     */
    public function logout() {
        // 当前用户缓存删除
        $user=session(C("SESSION_NAME_CUR_HOME"));
        $uid=$user['id'];
        M("user")->where("id=$uid")->setField("sessionid",'');
        session ( C( "SESSION_NAME_CUR_HOME" ), null );

        ajax_return_ok();
    }
    /**
     * 获取短信验证码
     */
    public function getCode(){
        include "./Api/MessageSend.class.php";
        $phone=I("post.phone",'','trim');
        $username=I("post.username",'','trim');
        if(empty($phone)){
            ajax_return_error("手机号不能为空");
        }
        if(empty($username)){
            ajax_return_error("用户名不能为空");
        }
        if($user=M("user")->where(array("userName"=>$username,"phone"=>$phone))->find()){

        }else{
            ajax_return_error("用户名和手机号输入不匹配");
        }
        $start_time=strtotime(date("Y-m-d"));
        $end_time=$start_time+24*3600-1;
        //判断今天发几次了
        $count=M("sms_record")->where("sphone='{$phone}' and type=1 and saddtime>=$start_time and saddtime<=$end_time")->count();
        if($count>2){
            ajax_return_error("你已超过3次机会，请明天再试");
        }
        $time = 5;
        $code = rand(1000,9999);
        $context = "【龙湖庄园】您的验证码为：".$code."，此验证码".$time."分钟之内输入有效。";
        $username="longhuzhuangyuan";							//改为实际账户名
        $password="M2d59ef9fe";							//改为实际短信发送密码
        $mobiles=$phone;					//目标手机号码，多个用半角“,”分隔
        $content=$context;
        $extnumber="";
        //定时短信发送时间,格式 2016-12-06T08:09:10+08:00，null或空串表示为非定时短信(即时发送)
        $plansendtime='';
        //$plansendtime='2016-12-06T08:09:10+08:00'
        $result=\WsMessageSend::send($username, $password, $mobiles, $content,$extnumber,$plansendtime);
		
        if($result==null)
        {
            ajax_return_error('验证码发送失败');
        }
        else
        {
            $data['sphone'] = $phone;
            $data['scontext'] = $context;
            $data['sis_use'] = 0;
            $data['sendtime'] = time() + $time*60;
            $data['saddtime'] = time();
            $data['code'] = $code;
            $data['type'] = 1;
            M('sms_record')->add($data);
            ajax_return_ok($time);
        }
    }
    /**
     * 忘记密码
     */
    public function forget(){
        $phone=I("post.phone",'','trim');
        $code=I("post.code",'','trim');
        $password=I("post.password",'','trim');
        $repassword=I("post.repassword",'','trim');
        $secondpwd=I("post.secondpwd",'','trim');
        $resecondpwd=I("post.resecondpwd",'','trim');
        $data=M("sms_record")->where(array("sphone"=>$phone,"sis_use"=>0,'type'=>1))->order("id desc")->find();
        if($data){
            if(time()<=$data['sendtime']){
                if($code==$data['code']){
                    //判断一级密码
                    $map=array();
                    if($password){
                        if(preg_match("/^[0-9a-zA-Z]{6,20}$/",$password)){
                            if($password==$repassword){
                                $map['password']=encrypt_pass($password);
                            }else{
                                ajax_return_error("一级密码确认有误");
                            }
                        }else{
                            ajax_return_error("请输入由数字和字母组成的6-20位的一级密码");
                        }

                    }else{
                        ajax_return_error("一级密码输入不能为空");
                    }
                    if($secondpwd){
                        if(preg_match("/^[0-9a-zA-Z]{6,20}$/",$password)){
                            if($secondpwd==$resecondpwd){
                                $map['secondPwd']=encrypt_pass($secondpwd);
                            }else{
                                ajax_return_error("二级密码确认有误");
                            }
                        }else{
                            ajax_return_error("请输入由数字和字母组成的6-20位的二级密码");
                        }
                    }
                    $res=M("user")->where(array("phone"=>$phone))->save($map);

                    ajax_return_ok("","设置成功");

                }else{
                    ajax_return_error("验证码输入错误");
                }
            }else{
                ajax_return_error("请重新获取验证码");
            }
        }else{
            ajax_return_error("请先获取验证码");
        }
    }


}

