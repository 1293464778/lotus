<?php
/**
 * Created by PhpStorm.
 * User: xiaolei
 * Date: 2017/3/10
 * Time: 15:51
 */

namespace Admin\Controller;


class ApplyController extends BaseController
{
    public $db;
    function __construct()
    {
        parent::__construct();
        $this->db=M("recommend");
    }
    public function index(){
        if (!IS_AJAX) {
            $this->display();
            return ;
        }

        // AJAX请求
        //搜索

        $search = I('search', '');
//        $where['isShow']=array('eq',1);
        if (!empty($search['value'])) {
            $searchStr = html_entity_decode($search['value']);
            parse_str($searchStr,$search);
            $num = $search['num'];
            if ($num != '') {
                $where['userName'] = array('like', '%'.$num.'%');
            }
            $status=$search['status'];
            if($status!=''){
                $where['status']=array('eq',$status);
            }
        }
        if(empty($where)){
            $where=true;
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
        $lists = $this->db->join("farm_user as u on u.id=farm_recommend.userId",'left')->where($where)->limit($start,$length)->order($order)
            ->field("farm_recommend.*,u.userName")->select();
        $user=M("user");
        foreach($lists as $k=>$v){
            $friend=$user->where("id=".$v['recommendedId'])->find();
            $lists[$k]['recommenName']=$friend['userName'];
            $lists[$k]['phone']=$friend['phone'];
            $lists[$k]['realName']=$friend['realName'];
            $lists[$k]['sex']=$friend['sex'];
            $lists[$k]['wechat']=$friend['wechat'];
            $lists[$k]['alipay']=$friend['alipay'];
            $lists[$k]['createTime']=date("Y-m-d H:i:s",$v['createTime']);
            $lists[$k]['updateTime']=date("Y-m-d H:i:s",$v['updateTime']);
        }
        $result['draw'] = $draw;
        $result['recordsTotal'] = $this->db->join("farm_user as u on u.id=farm_recommend.userId",'left')->where($where)->count();
        $result['recordsFiltered'] = $this->db->join("farm_user as u on u.id=farm_recommend.userId",'left')->where($where)->count();
        $result['data'] = $lists;

        $this->ajaxReturn($result);
    }

    /**
     * @param $id
     * 审核设置密码
     */
    public function check($id){
        $this->data=$this->db->where("id=$id")->find();
        $this->display();
    }

    /**
     * 好友数量加1  推荐状态已审核   被推荐人可展示   设置密码
     */
    public function save(){
        $data=I("post.");
        $model=M();
        $model->startTrans();
        $user_garden=M("user_garden");
        $uid=$data['userid'];
        $res1=$this->db->where("id=".$data['recommendid'])->save(array('status'=>1,'updateTime'=>time()));
        $res2=M("user")->where("id=".$uid)->setInc("friendNum",1);
        //推荐成功  送种子
        $seed=M("config")->where("id=27")->getField("value");
        $res3=$user_garden->where("userId=".$data['userid'])->setInc('seed',$seed);
        //加入种子奖励记录
        $res8=M("user_seed")->add(array("userId"=>$uid,"num"=>$seed,"createTime"=>time()));
        unset($data['recommendid']);
        unset($data['userid']);
        //设置密码
        $data['password']=encrypt_pass($data['password']);
        $data['secondPwd']=encrypt_pass($data['secondPwd']);
        $data['isShow']=1;
        $data['createTime']=time();
        $data['updateTime']=time();
        $res6=M("user")->save($data);

        //新人进来送果实 开垦一块土地
        $fruit=M("config")->where("id=7")->getField("value");
        $res4=$user_garden->add(array('userId'=>$data['id'],'houseFruit'=>$fruit,'landNum'=>1,'totalNum'=>$fruit,'totalGrow'=>$fruit));
        $res7=M("user_land")->add(array('userId'=>$data['id'],'baseNum'=>$fruit,'totalNum'=>$fruit,'fruitNum'=>$fruit,'landId'=>1,'createTime'=>time(),'updateTime'=>time()));
        //判断是否送花仙子
        $scracow_fee=M("config")->where("id=17")->getField("value");//增加1个花仙子需要推荐的人数
        $max_scracow=M("config")->where("id=20")->getField("value");//花仙子上限的数量
        $scracow=$user_garden->where("userId=".$uid)->getField("fairy");
        $res5=1;$res9=1;
        if($scracow<$max_scracow){
            $friend_num=M("user")->where("id=".$uid)->getField("friendNum");
            $get_num=floor($friend_num/$scracow_fee)-$scracow;
            if($get_num>0){
                if($get_num>($max_scracow-$scracow)){
                    $get_num=$max_scracow-$scracow;
                }
                $res5=$user_garden->where("userId=".$uid)->setInc("fairy",$get_num);
                //加入花仙子记录
                $res9=M("user_prop")->add(array("userId"=>$uid,"num"=>$get_num,"type"=>1,"createTime"=>time()));
            }
        }
        if($res1&&$res2&&$res3&&$res4&&$res5&&$res6&&$res7&&$res8&&$res9){
            $model->commit();
            $this->ok('设置成功');
        }else{
            $model->rollback();
            $this->wrong('设置失败');
        }

    }
    /**
     * 拒绝申请
     */
    public function reject(){
        $id=I("post.id");
        $res=M("recommend")->where("id=$id")->save(array("status"=>2,"updateTime"=>time()));
        if($res){
            $this->ok('设置成功');
        }else{
            $this->wrong('设置失败');
        }
    }
}