<?php
/**
 * Created by PhpStorm.
 * User: xiaolei
 * Date: 2017/3/20
 * Time: 11:38
 */

namespace Api\Controller;
use Common\Util\AuthUtil;
use Common\CommonConstant;

class StaticController extends BaseController
{
    //初始化
    public function _initialize(){

        parent::_initialize();
        //用户身份验证
        $result = AuthUtil::checkIdentity();
        if (!$result['status']){
            ajax_return_error(null,$result['code']);
        }
    }
    /**
     * 统计用户最近7天的收益率折线图
     */
    public function index(){
        $user=session(C("SESSION_NAME_CUR_HOME"));
        $uid=$user['id'];
        $data=array();
        $user_profit=M("user_profit");
        for($i=1;$i<=7;$i++){
            $time=$this->time($i);
            $arr=$user_profit->where("userId=$uid and dateTime>={$time['start_time']} and dateTime<={$time['end_time']}")->getField("sys_rate");
            array_push($data,$arr);
        }
        ajax_return_ok($data,"请求成功");
    }
    public function time($type){
        switch($type){
            case 1:
                $start_time=strtotime(date('Y-m-d',strtotime('-6 days')));
                break;
            case 2:
                $start_time=strtotime(date('Y-m-d',strtotime('-5 days')));
                break;
            case 3:
                $start_time=strtotime(date('Y-m-d',strtotime('-4 days')));
                break;
            case 4:
                $start_time=strtotime(date('Y-m-d',strtotime('-3 days')));
                break;
            case 5:
                $start_time=strtotime(date('Y-m-d',strtotime('-2 days')));
                break;
            case 6:
                $start_time=strtotime(date('Y-m-d',strtotime('-1 days')));
                break;
            case 7:
                $start_time=strtotime(date('Y-m-d'));
                break;
        }
        $end_time=$start_time+24*3600;
        return array('start_time'=>$start_time,'end_time'=>$end_time);
    }
}