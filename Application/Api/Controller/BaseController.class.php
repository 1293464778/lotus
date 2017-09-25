<?php
namespace Api\Controller;
use Think\Controller;
use Common\Util\AuthUtil;
use Common\CommonConstant;

/**
 * 接口基类
 * @author xiegaolei
 *
 */
class BaseController extends Controller {
  
    public function _initialize(){
         
        //接口签名验证
        $result = AuthUtil::checkSign();
        if (!$result['status']){
    
            ajax_return_error(null,$result['code']);
        }
		$start=M("config")->where("id=30")->getField("value");
        $end=M("config")->where("id=31")->getField("value");
        $time=date("H:i");
        if($time>=$start&&$time<=$end){
            $info=M("config")->where("id=33")->getField("value");
            $this->ajaxReturn(array("status"=>2,"msg"=>$info));
        }
		$user=session(C("SESSION_NAME_CUR_HOME"));
        if($user){
            $sessionid=M("user")->where("id=".$user['id'])->getField("sessionid");
            if(session_id()!=$sessionid){
                $this->ajaxReturn(array("status"=>3,"msg"=>"你已经掉线"));
            }
        }
        
    }
    /**
     * 获取配置
     */
    public function getConfig($id){
        $data=M("config")->where("id=$id")->getField("value");
        return $data;
    }
    
    
    
}