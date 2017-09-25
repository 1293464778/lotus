<?php
/**
 * Created by PhpStorm.
 * User: xiaolei
 * Date: 2017/3/16
 * Time: 16:01
 */

namespace Api\Controller;
use Common\Util\AuthUtil;
use Common\CommonConstant;

class GardenController extends BaseController
{
    public $user;
    public $db;
    public $fee;
    public $ordinary;
    public $notordinary;
    public function __construct()
    {
        parent::__construct();
        //用户身份验证
        $result = AuthUtil::checkIdentity();
        if (!$result['status']){
            ajax_return_error(null,$result['code']);
        }
        $this->user=session(C("SESSION_NAME_CUR_HOME"));
        $this->db=M("user_garden");
        $this->fee=M("config")->where("id=13")->getField("value"); //果实不生长的基数
        $this->ordinary=M("config")->where("id=7")->getField('value'); //普通土地果实的基数
        $this->notordinary=M("config")->where("id=8")->getField('value'); //金土地果实的基数
    }
    /**
     * 获取庄园信息
     */
    public function index(){
        $uid=$this->user['id'];
        if(!$uid){
            ajax_return_error(null,101);
        }

        $start_time=strtotime(date('Y-m-d'));
        $end_time=$start_time+24*3600;


        //系统派送化肥
        //判断该用户今天是否领取过
        $is_get=M("user_profit")->where("userId=$uid and dateTime>=$start_time and dateTime<=$end_time")->find();
        if(!$is_get){
            $register_time=M("user")->where("id=$uid")->getField("createTime");
            if($register_time<$start_time){
                $this->getAddFertilizer($uid);
            }
        }

        $garden=$this->db->where("userId=".$uid)->find();
        $level=M("user")->where("id=$uid")->find();
        $garden['level']=$level['level'];
        $garden['userName']=$level['userName'];
        $garden['phone']=$level['phone'];
        //查看可采摘的蜂蜜
        $garden['honey']=$this->getHarvest($uid);
        $user_land=M("user_land")->where("userId=$uid")->order("id asc")->select();
        $garden['ord_fee']=$this->ordinary;
        $garden['gold_fee']=$this->notordinary;
        $garden['full_fee']=$this->fee;
        foreach($user_land as $k=>$v){
            $total_num=$v['fruitNum'];
            if($v['landId']<11){
                if($total_num<$garden['ord_fee']*$garden['full_fee']){
                    $user_land[$k]['is_full']=0;//未达到生长极限
                }else{
                    $user_land[$k]['is_full']=1;//达到生长极限
                }
            }else{
                if($total_num<$garden['gold_fee']*$garden['full_fee']){
                    $user_land[$k]['is_full']=0;//未达到生长极限
                }else{
                    $user_land[$k]['is_full']=1;//达到生长极限
                }
            }
        }
        $garden['user_land']=$user_land;
        ajax_return_ok($garden);
    }
    /**
     * 查看用户可采摘的蜂蜜
     */
    public function getHarvest($uid){
        $friend=M("recommend")->join("farm_user as u on u.id=farm_recommend.recommendedId")
            ->where("userId=$uid and u.isShow=1 and status=1")->order("farm_recommend.id desc")->field("farm_recommend.*")
            ->select();
        $start_time=strtotime(date("Y-m-d"));
        $end_time=$start_time+24*3600;
        $total_harvest=0.00;
        foreach($friend as $k=>$v){
            $friend_id=$v['recommendedId'];
            //判断好友今天是否施肥
            $is_do=M("user_fertilizer")->where("userId=$friend_id and createTime>=$start_time and createTime<=$end_time")->find();
            if(!$is_do){
                continue;
            }
            $find=M("user_harvest")->where("userId=$uid and friendId=".$friend_id." and createTime>=$start_time and createTime<=$end_time")->find();
            if($find){
                continue;
            }
            $friend_garden=$this->db->where("userId=$friend_id")->find();
            $friend_garden['level']=M("user")->where("id=$friend_id")->getField("level");
            $sys=$this->getFriendFertilizer($friend_id,$start_time,$end_time,$friend_garden);
            $total_harvest+=$sys;
        }
        return $total_harvest;
    }
    /**
     * 开垦
     */
    public function open(){
        $uid=$this->user['id'];
        if(!$uid){
            ajax_return_error(null,101);
//            $uid=4;
        }
        $garden=$this->db->where("userId=$uid")->find();
        if($garden['landNum']<10){
            $pay_fruit=$this->ordinary;
        }else{
            $pay_fruit=$this->notordinary;
        }
        if($pay_fruit>$garden['houseFruit']){
            ajax_return_error("你的仓库荷花数量不足");
        }else{
            $model=M();
            $model->startTrans();
            //1 扣掉仓库的果实
            $res1=$this->db->where("userId=$uid")->setDec('houseFruit',$pay_fruit);
            //2 加入一块土地
            $res2=M("user_land")->add(array('userId'=>$uid,'landId'=>$garden['landNum']+1,'fruitNum'=>$pay_fruit,'createTime'=>time(),'updateTime'=>time(),'baseNum'=>$pay_fruit));
            //3 增加土地数量
            $res3=$this->db->where("userId=$uid")->setInc('landNum',1);
            //4 增加果园总量  生长总量
            $res4=$this->db->where("userId=$uid")->setInc('totalNum',$pay_fruit);
            //$res5=$this->db->where("userId=$uid")->setInc('totalGrow',$pay_fruit);
            //5  根据土地数量 是否增加等级
            $res6=1;
            if($garden['landNum']==9){
                $res6=M("user")->where("id=$uid")->setField("level",2);
            }
            if($garden['landNum']==14){
                $res6=M("user")->where("id=$uid")->setField("level",3);
            }
            if($res1&&$res2&&$res3&&$res4&&$res6){
                $model->commit();
                ajax_return_ok('',"开垦成功");
            }else{
                $model->rollback();
                ajax_return_error("开垦失败");
            }
        }
    }
    /**
     * 播种  植果
     */
    public function sow(){
        $uid=$this->user['id'];
        if(!$uid){
            ajax_return_error(null,101);
//            $uid=4;
        }
        $land_id=I("post.land_id");//土地编号
        $fruit_num=I("post.fruit_num");//植果数量
        if(empty($land_id)){
            ajax_return_error("土地编号参数为空");
        }
        if(empty($fruit_num)){
            ajax_return_error("植果数量不能为空");
        }
        $garden=$this->db->where("userId=$uid")->find();
        $fee=$this->fee;
        $ordinary=$this->ordinary;
        $notordinary=$this->notordinary;
        if($fruit_num>$garden['houseFruit']){
            ajax_return_error("你的仓库荷花数量不足");
        }else{
            $user_land=M("user_land")->where("userId=$uid and landId=$land_id")->find();
            if($land_id<11){
                if($fee*$ordinary<=$user_land['fruitNum']+$fruit_num){
                    $fruit_num=$fee*$ordinary-$user_land['fruitNum'];
                }
            }else{
                if($fee*$notordinary<=$user_land['fruitNum']+$fruit_num){
                    $fruit_num=$fee*$notordinary-$user_land['fruitNum'];
                }
            }
            if($fruit_num<=0){
                ajax_return_error("该荷花已达到生长上限");
            }
            $model=M();
            $model->startTrans();
            //1  扣掉仓库果实数量
            $res1=$this->db->where("userId=$uid")->setDec("houseFruit",$fruit_num);
            //2  加上植果数量
            $res2=M("user_land")->where("id=".$user_land['id'])->setInc('fruitNum',$fruit_num);
            //3 果园总量 增加
            $res4=$this->db->where("userId=$uid")->setInc('totalNum',$fruit_num);
            //4 加入播种记录表
            $land_type=$land_id>10?2:1;
            $map=array(
                'userId'=>$uid,
                'landId'=>$land_id,
                'land_type'=>$land_type,
                'sown_type'=>1,
                'num'=>$fruit_num,
                'createTime'=>time(),
            );
            $res5=M("user_sown")->add($map);
            //判断是否为5级
            if($garden['landNum']==15){
                $level_=M("user")->where("id=$uid")->getField("level");
                if($level_<5){
                    $land=M("user_land")->where("userid=$uid")->select();
                    $flag=1;
                    foreach($land as $k=>$v){
                        if($v['totalNum']<=$v['baseNum']*$this->fee){
                            $flag=0;
                        }
                    }
                    if($flag==1){
                        M("user")->where("id=$uid")->setField("level",5);
                    }
                }

            }
            if($res1&&$res2&&$res4&&$res5){
                $model->commit();
                ajax_return_ok('',"播种成功");
            }else{
                $model->rollback();
                ajax_return_error("播种失败");
            }
        }
    }
    /**
     * 施肥
     */
    public function fertilize(){
        $fee=$this->fee;
        $ordinary=$this->ordinary;
        $notordinary=$this->notordinary;
        $uid=$this->user['id'];
        if(!$uid){
            ajax_return_error(null,101);
//            $uid=4;
        }
        $user_level=M("user")->where("id=$uid")->getField("level");
        if($user_level==6){
            ajax_return_error("董事级别不能施肥");
        }
        $garden=$this->db->where("userId=$uid")->find();
        //判断是否有推荐人
        $tui_num=M("recommend")->where("userId=$uid and status=1")->count();
        if($tui_num<1){
            if($garden['doFertilizer']>=1000){
                ajax_return_error("你需要先去开发新果园");
            }
        }
        //施肥增加

        if($garden['landNum']<=0){
            ajax_return_error("请先去开垦荷花池");
        }
        $totalFertilizer=$garden['totalFertilizer'];//化肥总量
        if($totalFertilizer<=0){
            ajax_return_error("你的化肥数量不足");
        }
        $do_fee=M("config")->where("id=11")->getField('value');
        $add=round($totalFertilizer*$do_fee,2);
        $user_land=M("user_land")->where("userId=$uid")->order("id asc")->select();

        $real_total_add=0;//实际增加的果实收益
        //循环施肥
        $model=M();
        $model->startTrans();
        foreach($user_land as $k=>$v){
            if($add>0){
                //普通土地的计算
                $real_add=0;
                if($v['landId']<11&&$v['fruitNum']<$fee*$ordinary){
                    if($v['fruitNum']+$add>$fee*$ordinary){
                        $real_add=$fee*$ordinary-$v['fruitNum'];//实际增加的收益
                    }else{
                        $real_add=$add;
                    }

                }

                //金土地的计算
                if($v['landId']>10&&$v['fruitNum']<$fee*$notordinary){
                    if($v['fruitNum']+$add>$fee*$notordinary){
                        $real_add=$fee*$notordinary-$v['fruitNum'];//实际增加的收益
                    }else{
                        $real_add=$add;
                    }
                }
                if($real_add==0){
                    $res4=1;$res5=1;$res11=1;
                }else{
                    $res4=M("user_land")->where("id=".$v['id'])->setInc('fruitNum',$real_add);
                    $res5=M("user_land")->where("id=".$v['id'])->setInc('totalNum',$real_add);
                    //加入施肥记录
                    $map=array(
                        "userId"=>$uid,
                        "landId"=>$v['landId'],
                        "fertilizer_num"=>round($real_add/$do_fee,2),
                        "fruit_add"=>$real_add,
                        "createTime"=>time(),
                    );
                    $res11=M("user_fertilizer")->add($map);
                }

                if(!$res4||!$res5||!$res11){
                    $model->rollback();
                    ajax_return_error("施肥失败");
                }
                $add=$add-$real_add;
                $real_total_add+=$real_add;
                
            }


        }
        //1 扣掉用户化肥数量
        if($real_total_add>0){
            $res1=$this->db->where("userId=$uid")->setDec('totalFertilizer',round($real_total_add/$do_fee,2));
            //2 果园增加总收益量
            $res2=$this->db->where("userId=$uid")->setInc("totalNum",$real_total_add);
            $res3=$this->db->where("userId=$uid")->setInc("totalGrow",$real_total_add);
            //3 增加施肥数量
            $res6=$this->db->where("userId=$uid")->setInc('doFertilizer',round($real_total_add/$do_fee,2));
        }else{
            $res1=1;
            $res2=1;
            $res3=1;
            $res6=1;
            ajax_return_error("荷花池已达到最大收益值");
        }

        $add_angel=$this->getConfig(19);//增加一个花天使需要施肥的数量
        $add_wizard=$this->getConfig(18);//增加一个花精灵需要施肥的数量
        $max_angel=$this->getConfig(21);
        $max_wizard=$this->getConfig(22);
        $doFertilizer=$this->db->where("userId=$uid")->getField("doFertilizer");//施肥总量
        $res7=1;$res12=1;
        if($garden['angel']<$max_angel){
            $get_angel=floor($doFertilizer/$add_angel)-$garden['angel'];
            if($get_angel>0){
                if($get_angel>($max_angel-$garden['angel'])){
                    $get_angel=$max_angel-$garden['angel'];
                }
                //4增加花天使的数量
                $res7=$this->db->where("userId=$uid")->setInc("angel",$get_angel);
                //加入花天使记录表
                $res12=M("user_prop")->add(array("userId"=>$uid,"type"=>2,"num"=>$get_angel,"createTime"=>time()));
            }
        }
        $res8=1;$res13=1;
        if($garden['wizard']<$max_wizard){
            $get_wizard=floor($doFertilizer/$add_wizard)-$garden['wizard'];
            if($get_wizard>0){
                if($get_wizard>($max_wizard-$garden['wizard'])){
                    $get_wizard=$max_wizard-$garden['wizard'];
                }
                //4增加花精灵的数量
                $res8=$this->db->where("userId=$uid")->setInc("wizard",$get_wizard);
                //加入花精灵记录表
                $res13=M("user_prop")->add(array("userId"=>$uid,"type"=>3,"num"=>$get_wizard,"createTime"=>time()));
            }
        }
        //判断是否是财主等级
        $level=$this->getConfig(6);
        $level_1=$this->getConfig(36);
        $res10=1;

        if($doFertilizer>=$level_1){
            if($user_level<6){
                $res10=M("user")->where("id=$uid")->setField("level",6);
            }
        }elseif($doFertilizer>=$level){
            if($user_level<4){
                $res10=M("user")->where("id=$uid")->setField("level",4);
            }
        }
        //判断是否为5级
        if($garden['landNum']==15){
            $level_=M("user")->where("id=$uid")->getField("level");
            if($level_<5){
                $land=M("user_land")->where("userid=$uid")->select();
                $flag=1;
                foreach($land as $k=>$v){
                    if($v['totalNum']<=$v['baseNum']*$this->fee){
                        $flag=0;
                    }
                }
                if($flag==1){
                    M("user")->where("id=$uid")->setField("level",5);
                }
            }

        }
        if($res1&&$res2&&$res3&&$res6&&$res7&&$res8&&$res10&&$res12&&$res13){
            $model->commit();
            ajax_return_ok('','施肥成功');
        }else{
            $model->rollback();
            ajax_return_error("施肥失败");
        }
    }
    /**
     * 获取系统派送的化肥
     */
    public function getAddFertilizer($uid){
		if($uid==1){
				return false;
			}
        $level=M("user")->where("id=$uid")->getField("level");
        if($level==6){
            $this->db->where("userId=$uid")->setField("totalFertilizer",0);
            return false;
        }
        $garden=$this->db->where("userId=$uid")->find();
        //判断是否有推荐人
        $tui_num=M("recommend")->where("userId=$uid and status=1")->count();
        if($tui_num<1){
            if($garden['doFertilizer']>=1000){
                return false;
            }
        }

        $fairy=$garden['fairy'];
        $angel=$garden['angel'];
        $wizard=$garden['wizard'];
        $level=M("user")->where("id=$uid")->getField("level");
        $fee=countProfit($fairy,$angel,$wizard,$level);//每天的收益率
        $num=$garden['totalNum']*$fee['total_rate'];

        $res1=$this->db->where("id={$garden['id']}")->setField("totalFertilizer",$num);

        //插入记录表
        $map1=array(
            "userId"=>$uid,
            "sys_rate"=>$fee['sys_rate']?:0,
            "fairy_rate"=>$fee['fairy_rate']?:0,
            "angel_rate"=>$fee['angel_rate']?:0,
            "wizard_rate"=>$fee['wizard_rate']?:0,
            "total_rate"=>$fee['total_rate']?:0,
            "level_rate"=>$fee['level_rate']?:0,
            "total_num"=>$num,
            "houseFruit"=>$garden['houseFruit'],
            "dateTime"=>time(),
        );
        $res2=M("user_profit")->add($map1);

    }
    /**
     * 收割
     */
    public function harvest(){
        $land_id=I("post.land_id");
        //$fruit_num=I("post.fruit_num");
        $uid=$this->user['id'];
        if(!$uid){
            ajax_return_error(null,101);
//            $uid=4;
        }
        $land=M("user_land");
        $user_land=$land->where("userId=$uid and landId=$land_id")->find();
        if(!$user_land){
            ajax_return_error("请选择开垦过的荷花池");
        }
        $model=M();
        $model->startTrans();
        if($land_id<11){
            $harvest_num=$user_land['fruitNum']-$this->ordinary;//可收割的数量
        }else{
            $harvest_num=$user_land['fruitNum']-$this->notordinary;
        }

        if($harvest_num<=0){
            ajax_return_error("该荷花池暂不能收割");
        }
        //1 扣掉果树上数量

        $res=$land->where("id=".$user_land['id'])->setDec('fruitNum',$harvest_num);
        //2 扣掉果园总量
        $res2=$this->db->where("userId=$uid")->setDec("totalNum",$harvest_num);
        

        //3 加上果园仓库果实数量
        $res1=$this->db->where("userId=$uid")->setInc("houseFruit",$harvest_num);
        //4 加入收割记录表
        $land_type=$land_id>10?2:1;
        $map=array(
            "userId"=>$uid,
            "landId"=>$land_id,
            "land_type"=>$land_type,
            "num"=>$harvest_num,
            "createTime"=>time(),
        );
        $res3=M("user_get")->add($map);
        if($res1&&$res&&$res2&&$res3){
            $model->commit();
            ajax_return_ok("","收割成功");
        }else{
            ajax_return_error("收割失败");
        }
    }
    /**
     * 采蜜列表
     */
    public function picklist(){
        $uid=$this->user['id'];
        if(!$uid){
            ajax_return_error(null,101);
        }
//        $page=I("post.page")?:1;
//        $page_num=I("post.page_num")?:10;
//        $left=($page-1)*$page_num;
        $friend=M("recommend")->join("farm_user as u on u.id=farm_recommend.recommendedId")
            ->where("userId=$uid and u.isShow=1 and status=1")->order("farm_recommend.id desc")->field("farm_recommend.*,u.userName,u.realName,u.phone")
            ->select();
        $start_time=strtotime(date('Y-m-d'));
        $end_time=$start_time+24*3600;
        foreach($friend as $k=>$v){
            //判断好友今天是否施肥
            $friend_id=$v['recommendedId'];
            $is_do=M("user_fertilizer")->where("userId=$friend_id and createTime>=$start_time and createTime<=$end_time")->find();
            if(!$is_do){
                $is_do=0;//未施肥
                $friend[$k]['is_do']=$is_do;
                $friend[$k]['is_pick']=1;
            }else{
                $is_do=1;//已施肥
                $friend[$k]['is_do']=$is_do;
                //判断是否采蜜
                $isPick=M("user_harvest")->where("userid=$uid and friendId=$friend_id and createTime>=$start_time and createTime<=$end_time")->find();
                if($isPick){
                    $friend[$k]['is_pick']=1;
                }else{
                    //是否派送化肥
                    $is_get=M("user_profit")->where("userId=$friend_id and dateTime>=$start_time and dateTime<=$end_time")->find();
                    if($is_get){
                        $fertilizer=$is_get['total_num'];//化肥数量
                    }else{
                        $createTime=M("user")->where("id=$friend_id")->getField("createTime");
                        if($createTime>strtotime(date("Y-m-d"))){
                            $fertilizer=0;
                        }else{
                            $fertilizer=M("user_garden")->where("userId=$friend_id")->getField("totalNum");
                        }

                    }
                    if($fertilizer>0){
                        $friend[$k]['is_pick']=0;
                    }else{
                        $friend[$k]['is_pick']=1;
                    }
                }

            }
        }
        ajax_return_ok($friend);
    }
    /**
     * 计算好友今天领取的化肥
     */
    public function getFriendFertilizer($friend_id,$start_time,$end_time,$garden){
        $sys2=$this->getConfig(10);//好友采蜜为好友化肥数量的比例
        //是否派送化肥
        $is_get=M("user_profit")->where("userId=$friend_id and dateTime>=$start_time and dateTime<=$end_time")->find();
        if($is_get){
            $fertilizer=$is_get['total_num'];//化肥数量
        }else{
            $create_time=M("user")->where("id=$friend_id")->getField("createTime");
            if($create_time>strtotime(date('Y-m-d'))){
                $fertilizer=0;
            }else{
                $house_fruit=M("user_garden")->where("userId=$friend_id")->getField("totalNum");
                $profit=countProfit($garden['fairy'],$garden['angel'],$garden['wizard'],$garden['level']);
                $fertilizer=$house_fruit*$profit['total_rate'];
            }

        }
        $sys=round($fertilizer*$sys2,2);
        return $sys;
    }
    /**
     * 好友果园展示
     */
    public function friendGarden(){
        $uid=I("post.friend_id");//好友id
        $me=$this->user['id'];
        $garden=$this->db->where("userId=".$uid)->find();
        $level=M("user")->where("id=$uid")->find();
        $garden['honey']=$this->getHarvest($uid);
        $garden['level']=$level['level'];
        $garden['userName']=$level['realName'];
        $garden['phone']=$level['phone'];
        $garden['friendNum']=$level['friendNum'];
        $user_land=M("user_land")->where("userId=$uid")->order("id asc")->select();
        $garden['user_land']=$user_land;
        //判断好友今天可领的化肥数量
        $start_time=strtotime(date("Y-m-d"));
        $end_time=$start_time+24*3600;

        //判断好友今天是否施肥
        $is_do=M("user_fertilizer")->where("userId=$uid and createTime>=$start_time and createTime<=$end_time")->find();
        if(!$is_do){
            $is_do=0;//未施肥
            $sys=0.00;
        }else{
            $is_do=1;//已施肥
        }
        if($is_do==1){
            //判断今天是否采摘过
            if($find=M("user_harvest")->where("userId=$me and friendId=$uid and createTime>=$start_time and createTime<=$end_time")->find()){
                $sys=0.00;
            }else{
                $sys=$this->getFriendFertilizer($uid,$start_time,$end_time,$garden);
            }
        }

        $garden['picknum']=$sys;
        $garden['is_do']=$is_do;
        ajax_return_ok($garden);
    }
    /**
     * 到好友果园采蜜
     */
    public function pick(){
        $uid=$this->user['id'];
        $friend_id=I("post.friend_id");//好友id

        $start_time=strtotime(date("Y-m-d"));
        $end_time=$start_time+24*3600;
        //判断好友今天是否施肥
        $is_do=M("user_fertilizer")->where("userId=$friend_id and createTime>=$start_time and createTime<=$end_time")->find();
        if(!$is_do){
            ajax_return_error("你现在不能采蜜");
        }
        $find=M("user_harvest")->where("userId=$uid and friendId=".$friend_id." and createTime>=$start_time and createTime<=$end_time")->find();
        if($find){
            ajax_return_error("你今天已采摘过");
        }
        $friend_garden=$this->db->where("userId=$friend_id")->find();
        $friend_garden['level']=M("user")->where("id=$friend_id")->getField("level");

        $sys=$this->getFriendFertilizer($friend_id,$start_time,$end_time,$friend_garden);
        //1 增加蜂蜜数量
        if($sys<=0){
            ajax_return_error("该荷花池没有可采摘的蜂蜜");
        }
        $model=M();
        $model->startTrans();
        $res1=$this->db->where("userId=$uid")->setInc('houseFruit',$sys);
        //2 加入用户采蜜表
        $map=array(
            'userId'=>$uid,
            'friendId'=>$friend_id,
            'fruitNum'=>$sys,
            'createTime'=>time(),
        );
        $res2=M("user_harvest")->add($map);
        if($res1&&$res2){
            $model->commit();
            ajax_return_ok('','采蜜成功');
        }else{
            $model->rollback();
            ajax_return_error("采蜜失败");
        }
    }
    /**
     * 判断是否订购采蜜大师
     */
    public function isBuyHoney($type=null){
        $uid=$this->user['id'];
        $data=M("buy_honey")->where("userId=$uid")->order("id desc")->find();
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
     * 一键采取所有好友果实
     */
    public function pickAll(){
        $uid=$this->user['id'];
        if(!$this->isBuyHoney(1)){
            ajax_return_error("请先去购买采蜜大师");
        }
        $friend=M("recommend")->join("farm_user as u on u.id=farm_recommend.recommendedId")
            ->where("userId=$uid and u.isShow=1 and status=1")->order("farm_recommend.id desc")->field("farm_recommend.*")
            ->select();
        $start_time=strtotime(date("Y-m-d"));
        $end_time=$start_time+24*3600;
        $total_sys=0;
        foreach($friend as $k=>$v){
            $friend_id=$v['recommendedId'];
            //判断好友今天是否施肥
            $is_do=M("user_fertilizer")->where("userId=$friend_id and createTime>=$start_time and createTime<=$end_time")->find();
            if(!$is_do){
                continue;
            }
            $find=M("user_harvest")->where("userId=$uid and friendId=".$friend_id." and createTime>=$start_time and createTime<=$end_time")->find();
            if($find){
                continue;
            }
            $friend_garden=$this->db->where("userId=$friend_id")->find();
            $friend_garden['level']=M("user")->where("id=$friend_id")->getField("level");
            $sys=$this->getFriendFertilizer($friend_id,$start_time,$end_time,$friend_garden);
            $total_sys+=$sys;
            //1 增加蜂蜜数量
            $model=M();
            $model->startTrans();
            if($sys<=0){
                $res1=1;$res2=1;
            }else{
                $res1=$this->db->where("userId=$uid")->setInc('houseFruit',$sys);
                //2 加入用户采蜜表
                $map=array(
                    'userId'=>$uid,
                    'friendId'=>$friend_id,
                    'fruitNum'=>$sys,
                    'createTime'=>time(),
                );
                $res2=M("user_harvest")->add($map);
            }


            if($res1&&$res2){
                $model->commit();
            }else{
                $model->rollback();
                ajax_return_error("系统开小差了,请稍后再试");
            }
        }
        if($total_sys>0){
            ajax_return_ok('','采蜜成功');
        }else{
            ajax_return_ok('','今天没有什么收获~');
        }

    }
}