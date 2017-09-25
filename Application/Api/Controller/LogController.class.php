<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/10 0010
 * Time: 上午 10:29
 */

namespace Api\Controller;


use Common\Util\AuthUtil;

class LogController extends BaseController
{
    public $uid;
    public function __construct()
    {
        parent::__construct();
        //用户身份验证
        $result = AuthUtil::checkIdentity();
        if (!$result['status']){
            ajax_return_error(null,$result['code']);
        }
        $user=session(C("SESSION_NAME_CUR_HOME"));
        $this->uid=$user['id'];
    }
    /**
     * 施肥记录
     */
    public function fertilizer(){
        $uid=$this->uid;
        $page=I("post.page")?:1;
        $page_num=I("post.page_num")?:10;
        $left=($page-1)*$page_num;
        $data=M("user_fertilizer")->where("userId=$uid")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data,'请求成功');
    }
    /**
     * 播种记录
     */
    public function sown(){
        $uid=$this->uid;
        $page=I("post.page")?:1;
        $page_num=I("post.page_num")?:10;
        $left=($page-1)*$page_num;
        $data=M("user_sown")->where("userId=$uid")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data,'请求成功');
    }
    /**
     * 收割记录
     */
    public function get(){
        $uid=$this->uid;
        $page=I("post.page")?:1;
        $page_num=I("post.page_num")?:10;
        $left=($page-1)*$page_num;
        $data=M("user_get")->where("userId=$uid")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data,'请求成功');
    }
    /**
     * 花园生长记录
     */
    public function grown(){
        $uid=$this->uid;
        $page=I("post.page")?:1;
        $page_num=I("post.page_num")?:10;
        $left=($page-1)*$page_num;
        $data=M("user_profit")->where("userId=$uid")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            $data[$k]['dateTime']=date("Y-m-d H:i",$v['dateTime']);
            $data[$k]['total_num']=round($v['total_num'],2);
        }
        ajax_return_ok($data,'请求成功');
    }
    /**
     * 采蜜记录
     */
    public function harvest(){
        $uid=$this->uid;
        $page=I("post.page")?:1;
        $page_num=I("post.page_num")?:10;
        $left=($page-1)*$page_num;
        $data=M("user_harvest")->where("userId=$uid")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data,'请求成功');
    }

    /**
     * @param $type 1花仙子2花天使3花精灵
     * 道具记录
     */
    public function prop($type){
        $uid=$this->uid;
        $data=M("user_prop")->where("userId=$uid and type=$type")->order("id desc")->select();
        foreach($data as $k=>$v){
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data,"请求成功");
    }
    /**
     * 种子奖励记录
     */
    public function seed(){
        $uid=$this->uid;
        $page=I("post.page")?:1;
        $page_num=I("post.page_num")?:10;
        $left=($page-1)*$page_num;
        $data=M("user_seed")->where("userId=$uid")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data,"请求成功");
    }
    /**
     * 订购验证码
     */
    public function buyCode(){
        $month=I("post.month","","trim");
        $uid=$this->uid;
        $data=M("config")->where("id=29")->getField("value");
        $fee=$data*$month;
        $houseFruit=M("user_garden")->where("userId=$uid")->getField("houseFruit");
        if($houseFruit<$fee){
            ajax_return_error("仓库荷花数量不足");
        }
        $model=M();
        $model->startTrans();
        //1扣掉仓库数量
        $res1=M("user_garden")->where("userId=$uid")->setDec("houseFruit",$fee);
        //2加入记录表
        $map=array(
            "userId"=>$uid,
            "start_time"=>time(),
            "end_time"=>strtotime('+1 month'),
            "fruit_num"=>$fee,
            "month"=>$month,
        );
        $res2=M("buy_code")->add($map);
        if($res1&&$res2){
            $model->commit();
            ajax_return_ok("","订购成功");
        }else{
            $model->rollback();
            ajax_return_error("订购失败");
        }
    }
    /**
     * 订购采蜜大师
     */
    public function buyHoney(){
        $month=I("post.month","","trim");
        $uid=$this->uid;
        $data=M("config")->where("id=28")->getField("value");
        $fee=$data*$month;
        $houseFruit=M("user_garden")->where("userId=$uid")->getField("houseFruit");
        if($houseFruit<$fee){
            ajax_return_error("仓库荷花数量不足");
        }
        $model=M();
        $model->startTrans();
        //1扣掉仓库数量
        $res1=M("user_garden")->where("userId=$uid")->setDec("houseFruit",$fee);
        //2加入记录表
        $map=array(
            "userId"=>$uid,
            "start_time"=>time(),
            "end_time"=>strtotime('+1 month'),
            "fruit_num"=>$fee,
            "month"=>$month,
        );
        $res2=M("buy_honey")->add($map);
        if($res1&&$res2){
            $model->commit();
            ajax_return_ok("","订购成功");
        }else{
            $model->rollback();
            ajax_return_error("订购失败");
        }
    }
    
}