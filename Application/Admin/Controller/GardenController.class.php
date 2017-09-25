<?php
/**
 * Created by PhpStorm.
 * User: xiaolei
 * Date: 2017/3/10
 * Time: 14:27
 */

namespace Admin\Controller;


class GardenController extends BaseController
{
    public $db;
    public function __construct()
    {
        parent::__construct();
        $this->db=M("user_garden");
    }
    public function index(){
//        echo date("Y-m-d H:i:s",1494115707);exit;
        if (!IS_AJAX) {
            $this->display();
            return ;
        }

        // AJAX请求
        //搜索
        $search = I('search', '');
        $where['isShow']=array('eq',1);
        if (!empty($search['value'])) {
            $searchStr = html_entity_decode($search['value']);
            parse_str($searchStr,$search);
            $num = $search['num'];
            if ($num != '') {
                $where['userName'] = array('like', '%'.$num.'%');
            }
            $level=$search['level'];
            if($level!=0){
                $where['level']=array('eq',$level);
            }
        }
        $draw = I('draw',1,'intval');
        $start = I('start',0,'intval');
        $length = I('length',10,'intval');
        //排序设置
        $mycolumns = I('mycolumns','');
        $myorder = I('order','');
        $order='';
        if (empty($myorder) || empty($mycolumns)) {
            $order = 'id desc';
        } else {
            foreach ($myorder as $key => $v) {
                $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
            }
            $order = rtrim($order , ',');
        }
        $level=array('贫农','中农','富农','财主','大富豪','董事');
//        var_dump($where);
        $lists = $this->db->join("farm_user as u on u.id=farm_user_garden.userId")->where($where)->limit($start,$length)->order($order)->field("farm_user_garden.*,u.userName,u.level,u.friendNum")->select();
        foreach($lists as $k=>$v){
            $i=$v['level']-1;
            $lists[$k]['level']=$level[$i];
        }

        $result['draw'] = $draw;
        $result['recordsTotal'] = $this->db->join("farm_user as u on u.id=farm_user_garden.userId")->where($where)->count();
        $result['recordsFiltered'] = $this->db->join("farm_user as u on u.id=farm_user_garden.userId")->where($where)->count();
        $result['data'] = $lists;

        $this->ajaxReturn($result);
    }

    /**
     * @param $id 果园id
     * 编辑果园
     */
    public function edit($id){
        $garden=$this->db->where("id=$id")->find();
        $this->data=$garden;
        $this->display();
    }

    /**
     * 编辑保存
     */
    public function save(){
		/*
        $data=I("post.");
        $res=$this->db->save($data);
        if ($res === false) {
            $this->wrong('编辑失败');
        }
        $this->ok('编辑成功');
		*/
		include "./Api/MessageSend.class.php";
        $data=I("post.");
        $old=$this->db->find($data['id']);
        $res=$this->db->save($data);
        if ($res === false) {
            $this->wrong('编辑失败');
        }
        $phone=M("user")->where("id=".$old['userId'])->getField("phone");
        $str='【龙湖庄园】手机号'.$phone."的用户";
        if($old['houseFruit']!=$data['houseFruit']){
            $str.=",仓库荷花数量由".$old['houseFruit']."改为".$data['houseFruit'];
        }
        if($old['totalFertilizer']!=$data['totalFertilizer']){
            $str.=",仓库化肥数量由".$old['totalFertilizer']."改为".$data['totalFertilizer'];
        }
        $username="longhuzhuangyuan";							//改为实际账户名
        $password="M2d59ef9fe";							//改为实际短信发送密码
        $mobiles="15570270000,18603883888,17739305888";					//目标手机号码，多个用半角“,”分隔
        $content=$str;
        $extnumber="";

        //定时短信发送时间,格式 2016-12-06T08:09:10+08:00，null或空串表示为非定时短信(即时发送)
        $plansendtime='';
        //$plansendtime='2016-12-06T08:09:10+08:00'
        $result=\WsMessageSend::send($username, $password, $mobiles, $content,$extnumber,$plansendtime);
        if($result['SuccessCounts']==0){
            $this->wrong('发送短信失败');
        }
        $this->ok('编辑成功');
    }
    /**
     * 衰减率配置
     */
    public function sys(){
        $this->data=M("config")->where("type=5")->order("id asc")->select();
        $this->display();
    }
    /**
     * 添加衰减率配置
     */
    public function addSys(){
        $data=I("post.");
        if($data){
            $data['type']=5;//类型
            $res=M("config")->add($data);
            if ($res === false) {
                $this->wrong('添加失败');
            }
            $this->ok('添加成功');
        }else{
            $this->display();
        }
    }
    /**
     * 编辑衰减率配置
     */
    public function editSys(){
        if(IS_POST){
            $data=I("post.");
            $res=M("config")->save($data);
            if ($res === false) {
                $this->wrong('编辑失败');
            }
            $this->ok('编辑成功');
        }else{
            $id=I("get.id");
            $this->data=M("config")->where("id=$id")->find();
            $this->display();
        }
    }
    /**
     * 删除衰减率配置
     */
    public function delSys($id){
        $res=M("config")->delete($id);
        if ($res === false) {
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }
    /**
     * 计算每个果园的今日收益率
     */
    public function todayProfit(){
        $model=M();
        $i=I("post.i");
        $admin=session('admin_auth');
        $admin_id=$admin['uid'];
        $start_time=strtotime(date('Y-m-d'));
        $end_time=$start_time+24*3600;
//        $where['clickTime']=array(array('gt',$start_time),array('lt',$end_time));
//        $commit_profit=M("commit_profit");
//        if($data=$commit_profit->where($where)->find()){
//            $this->ok("今天收益已发放");
//            return false;
//        }

        $garden=$this->db->join("farm_user as u on u.id=farm_user_garden.userId")->where("isShow=1 and u.createTime<$start_time and u.level<6")->order("farm_user_garden.id desc")->limit($i,50)->field("farm_user_garden.*,u.level,u.createTime as create_time")->select();
        $count=count($garden);
        $user_profit=M("user_profit");
        $model->startTrans();
        foreach($garden as $k=>$v){
			if($v['userId']==1){
				continue;
			}
            if($v['create_time']>strtotime(date("Y-m-d"))){
                continue;
            }
            //判断是否有推荐人
            $tui_num=M("recommend")->where("userId={$v['userId']} and status=1")->count();
            if($tui_num<1){
                if($v['doFertilizer']>=1000){
                    continue;
                }
            }
            $fairy=$v['fairy'];
            $angel=$v['angel'];
            $wizard=$v['wizard'];
            $fee=countProfit($fairy,$angel,$wizard,$v['level']);//每天的收益率$fairy=$v['fairy'];

            $map['userId']=array("eq",$v['userId']);
            $map['dateTime']=array(array('gt',$start_time),array('lt',$end_time));
            if($user_profit1=$user_profit->where($map)->find()){
            }else{
                //2、计算化肥
                $num=$v['totalNum']*$fee['total_rate'];

                $res1=$this->db->where("userId={$v['userId']}")->setField("totalFertilizer",$num);
                //插入记录表
                $map1=array(
                    "userId"=>$v['userId'],
                    "sys_rate"=>$fee['sys_rate']?:0,
                    "fairy_rate"=>$fee['fairy_rate']?:0,
                    "angel_rate"=>$fee['angel_rate']?:0,
                    "wizard_rate"=>$fee['wizard_rate']?:0,
                    "total_rate"=>$fee['total_rate']?:0,
                    "level_rate"=>$fee['level_rate']?:0,
                    "total_num"=>$num,
                    "houseFruit"=>$v['houseFruit'],
                    "dateTime"=>time(),
                );
                $res2=M("user_profit")->add($map1);
                if($res2){

                }else{
                    $model->rollback();
                    $this->wrong(($i*50+$k)."计算失败1");
                }


            }
        }
        $res3=1;
//        $res3=$commit_profit->add(array('adminId'=>$admin_id,'clickTime'=>time()));
        if(!$res3){
            $model->rollback();
            $this->wrong("计算失败2");
        }else{
            $model->commit();

            $str=($count+$i*50).'条数据计算完毕';
            $this->ok($str);
        }

    }
    /**
     * @param $id 用户id
     * 查看用户果园
     */
    public function land($id){
        $data=M("user_land")->where("userId=$id")->order("id asc")->select();
        $this->data=$data;
        $this->display();
    }
    public function statis(){
        layout(false);
        $where['isShow']=array('eq',1);
        $total_num=$this->db->join("farm_user as u on u.id=farm_user_garden.userId")->where($where)->count();
        $this->page_num=ceil($total_num/50);
        $this->display();
    }
}