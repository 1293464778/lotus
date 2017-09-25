<?php
namespace Api\Controller;
use Think\Controller;
use Common\Util\JwtUtil;
use Common\Util\AuthUtil;
use Common\CommonConstant;
/**
 * 首页
 *
 */
class OrderController extends BaseController {
    public $uid;
    //初始化
    public function _initialize(){

        parent::_initialize();
        //用户身份验证
        $result = AuthUtil::checkIdentity();
        if (!$result['status']){
            ajax_return_error(null,$result['code']);
        }
        $user=session(C("SESSION_NAME_CUR_HOME"));
        $this->uid=$user['id'];
    }
    /**
     * 获取仓库荷花数量
     */
    public function getNum(){
        $uid=$this->uid;
        $num=M("user_garden")->where("userId=$uid")->getField("houseFruit");
        $fee=$this->getConfig(27);
        $sale_num=round($num*$fee,2);
        ajax_return_ok(array('num'=>$num,'sale_num'=>$num));
    }
    /**
     * 获取短信验证码
     */
    public function getCode(){
        include "./Api/MessageSend.class.php";
        $uid=$this->uid;
        $user_phone=M("user")->where("id=$uid")->getField("phone");
        $type=I("post.type","","trim");//2委托出售3定向出售

        $time = 5;
        $code = rand(1000,9999);
        $context = "【龙湖庄园】您的验证码为：".$code."，此验证码".$time."分钟之内输入有效。";
        $username="longhuzhuangyuan";							//改为实际账户名
        $password="M2d59ef9fe";							//改为实际短信发送密码
        $mobiles=$user_phone;					//目标手机号码，多个用半角“,”分隔
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
            $data['sphone'] = $user_phone;
            $data['scontext'] = $context;
            $data['sis_use'] = 0;
            $data['sendtime'] = time() + $time*60;
            $data['saddtime'] = time();
            $data['code'] = $code;
            $data['type'] = $type;
            M('sms_record')->add($data);
            ajax_return_ok($time);
        }
    }
    /**
     * 判断是否订购验证码
     */
    public function isBuyCode($type=null){
        $uid=$this->uid;
        $data=M("buy_code")->where("userId=$uid")->order("id desc")->find();
        if($data){
            $end_time=$data['end_time'];
            if($end_time<time()){
                $is_buy_code=0;
            }else{
                $is_buy_code=1;
            }
        }else{
            $is_buy_code=0;
        }
        if($type){
            return $is_buy_code;
        }else{
            ajax_return_ok(array("is_buy_code"=>$is_buy_code),"请求成功");
        }
    }
    /**
     * 验证验证码
     */
    public function verifyCode($type,$code,$phone){
        $data=M("sms_record")->where(array("sphone"=>$phone,"sis_use"=>0,'type'=>$type))->order("id desc")->find();
        if($data){
            if(time()<=$data['sendtime']){
                if($code==$data['code']){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /**
     * 委托出售
     */
    public function commission() {
        $sale_num=I("post.sale_num");
        $code=I("post.code","","trim");
        $uid=$this->uid;
        $phone=M("user")->where("id=$uid")->getField("phone");
        if($this->isBuyCode(1)){
            if($code){
                //判断验证码是否正确
                if(!$this->verifyCode(2,$code,$phone)){
                    ajax_return_error("验证码输入错误");
                }
            }else{
                ajax_return_error("请输入验证码");
            }
        }
        if($sale_num<100||$sale_num>10000){
            ajax_return_error("出售荷花的数量为100-10000");
        }
        //1 扣去佣金数量
        $level=M("user")->where("id=$uid")->getField("level");
        $sub=$this->getConfig(37);
        if($level>=$sub||$uid==1){
            $fee=0;
            $real_num=$sale_num;
        }else{
            $fee=$this->getConfig(24);
            $real_num=round($sale_num*(1-$fee),2);
        }
        $num=M("user_garden")->where("userId=$uid")->getField("houseFruit");
        if($num<$real_num){
            ajax_return_error("你的仓库荷花数量不足");
        }

        $model=M();
        $model->startTrans();
        //2 减去仓库果实数量
        $res1=M("user_garden")->where("userId=$uid")->setDec("houseFruit",$sale_num);
        //3 加入委托出售表
        $map=array(
            'saleId'=>$uid,
            'saleNum'=>$sale_num,
            'realNum'=>$real_num,
            'fee'=>$fee,
            'status'=>1,
            'createTime'=>time(),
            'updateTime'=>time(),
        );
        $res2=M("commission_sale")->add($map);
        if($res1&&$res2){
            $model->commit();
            ajax_return_ok('','提交成功');
        }else{
            $model->rollback();
            ajax_return_error("提交失败");
        }
    }
    /**
     * 定向出售
     */
    public function direction(){
        $uid=$this->uid;
        $sale_num=I("post.sale_num");
        $userName=I("post.userName");
        $realName=I("post.realName");
        $code=I("post.code","","trim");
        $phone=M("user")->where("id=$uid")->getField("phone");
        if($this->isBuyCode(1)){
            if($code){
                //判断验证码是否正确
                if(!$this->verifyCode(3,$code,$phone)){
                    ajax_return_error("验证码输入错误");
                }
            }else{
                ajax_return_error("请输入验证码");
            }
        }
        if($sale_num%10!=0){
            ajax_return_error("出售荷花的数量需是10的倍数");
        }
        $where['phone']=array('eq',$userName);
        $where['realName']=array('eq',$realName);
        $user=M("user")->where($where)->find();
        if(!$user){
            ajax_return_error("你输入的目标会员信息有误");
        }
        if($user['id']==$uid){
            ajax_return_error("不可以向自己出售哦");
        }
        //1 扣掉佣金数量
        $level=M("user")->where("id=$uid")->getField("level");
        $sub=$this->getConfig(37);
        if($level>=$sub||$uid==1){
            $fee=0;
            $real_num=$sale_num;
        }else{
            $fee=$this->getConfig(25);
            $real_num=round($sale_num*(1-$fee),2);
        }

        $num=M("user_garden")->where("userId=$uid")->getField("houseFruit");
        if($num<$real_num){
            ajax_return_error("你仓库的荷花数量不足");
        }
        $model=M();
        $model->startTrans();


        //2 扣掉仓库果实数量
        $res1=M("user_garden")->where("userId=$uid")->setDec("houseFruit",$sale_num);
        //3 加入定向出售表
        $map=array(
            'saleId'=>$uid,
            'saleNum'=>$sale_num,
            'buyId'=>$user['id'],
            'buyUsername'=>$userName,
            'buyRealname'=>$realName,
            'realNum'=>$real_num,
            'fee'=>$fee,
            'status'=>0,
            'createTime'=>time(),
            'updateTime'=>time(),
        );
        $res2=M("direction_sale")->add($map);
        if($res1&&$res2){
            $model->commit();
            ajax_return_ok("","等待对方付米");
        }else{
            $model->rollback();
            ajax_return_error("提交失败");
        }
    }
    /**
     * 定向出售记录
     */
    public function directionList(){
        $uid=$this->uid;
        $page=I("post.page")?:1;
        $page_num=I("post.page_num")?:10;
        $left=($page-1)*$page_num;
        $data=M("direction_sale")->where("saleId=$uid")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            //判断24小时是否确认
            if($v['status']<2){
                if($v['createTime']+24*3600<time()){
                    M("direction_sale")->where("id=".$v['id'])->setField("status",2);
                    $data[$k]['status']=2;
                    M("user_garden")->where("userId={$v['buyId']}")->setInc("houseFruit",$v['realNum']);
                }
            }
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data);
    }
    /**
     * 定向出售确认
     */
    public function commitDirection(){
        $order_id=I("post.order_id");
        $info=M("direction_sale")->where("id=$order_id")->find();
        $model=M();
        $model->startTrans();
        //1 改变订单状态
        $res1=M("direction_sale")->where("id=$order_id")->save(array('status'=>2,'updateTime'=>time()));//卖家确认
        //2 买家增加果实数量
        $res2=M("user_garden")->where("userId={$info['buyId']}")->setInc("houseFruit",$info['realNum']);
        if($res1&&$res2){
            $model->commit();
            ajax_return_ok('','确认成功');
        }else{
            $model->rollback();
            ajax_return_error("确认失败");
        }

    }
    /**
     *委托出售记录
     */
    public function commissionList(){
        $uid=$this->uid;
        $page=I("post.page")?:1;
        $page_num=I("post.page_num")?:10;
        $left=($page-1)*$page_num;
        $data=M("commission_sale")->where("saleId=$uid")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data);
    }
    /**
     * 购买记录
     */
    public function buyList(){
        $uid=$this->uid;
        $page=I("post.page")?:1;
        $page_num=I("post.page_num")?:10;
        $left=($page-1)*$page_num;
        $data=M("direction_sale")->join("farm_user as u on u.id=farm_direction_sale.saleId")->where("buyId=$uid")
            ->field("farm_direction_sale.*,u.userName,u.realName")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            //判断24小时是否确认
            if($v['status']<2){
                if($v['createTime']+24*3600<time()){
                    M("direction_sale")->where("id=".$v['id'])->setField("status",2);
                    $data[$k]['status']=2;
                    M("user_garden")->where("userId={$v['buyId']}")->setInc("houseFruit",$v['realNum']);
                }
            }
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data);
    }
    /**
     * 购买记录确认
     */
    public function commitBuy(){
        $order_id=I("order_id");
        $res=M("direction_sale")->where("id=$order_id")->save(array('status'=>1,'updateTime'=>time()));
        if($res){
            ajax_return_ok('','等待对方收米');
        }else{
            ajax_return_error("确认失败");
        }
    }
    /**
     * 获取种子数量
     */
    public function getSeed(){
        $uid=$this->uid;
        $seed=M("user_garden")->where("userId=$uid")->getField("seed");
        ajax_return_ok($seed);
    }
    /**
     * 种子兑换果实
     */
    public function seedTofruit(){
        $seed_num=I("post.seed_num");
        $uid=$this->uid;
        $seed=M("user_garden")->where("userId=$uid")->getField("seed");
        if($seed_num>$seed){
            ajax_return_error("兑换的数量最大为".$seed);
        }
        $fee=$this->getConfig(12);
        //1 计算转化的果实数量
        $fruit_num=$seed_num*$fee;
        if($fruit_num<=0){
            ajax_return_error("你兑换的数量至少要大于0");
        }
        if($seed_num%100!=0){
            ajax_return_error("你兑换的数量需是100的倍数");
        }
        $model=M();
        $model->startTrans();
        //2 加入仓库果实数量
        $res1=M("user_garden")->where("userId=$uid")->setInc("houseFruit",$fruit_num);
        //3 加入种子转化果实表
        $map=array(
            'userId'=>$uid,
            'seedNum'=>$seed,
            'fruitNum'=>$fruit_num,
            'fee'=>$fee,
            'createTime'=>time(),
            'changeNum'=>$seed_num,
        );
        $res2=M("seed_fruit")->add($map);
        //4 减去种子数量
        $res3=M("user_garden")->where("userId=$uid")->setDec("seed",$seed_num);
        if($res1&&$res2&&$res3){
            $model->commit();
            ajax_return_ok("","兑换成功");
        }else{
            $model->rollback();
            ajax_return_error("兑换失败");
        }
    }
    /**
     * 种子兑换记录
     */
    public function seedList(){
        $uid=$this->uid;
        $page=I("post.page",'','trim')?:1;
        $page_num=I("post.page_num",'','trim')?:10;
        $left=($page-1)*$page_num;
        $data=M("seed_fruit")->where("userId=$uid")->order("id desc")->limit($left,$page_num)->select();
        foreach($data as $k=>$v){
            $data[$k]['createTime']=date("Y-m-d H:i",$v['createTime']);
        }
        ajax_return_ok($data);
    }

}