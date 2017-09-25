<?php
namespace Admin\Controller;
 

class OrderController extends BaseController {

    // 控制器权限分块
    public $privilege=array(
        'direction' => array('finish'),
        'commission'=>array('check_'),
    );

    public function direction(){
            if (!IS_AJAX) {
                $this->display();
                return ;
            }

            // AJAX请求
            // 搜索
            $search = I('search','');
            $where['isShow']=array('eq',1);
            if (!empty($search['value'])) {
                $searchStr = html_entity_decode($search['value']);
                parse_str($searchStr,$search);
                $username = $search['userName'];
                $phone = $search['phone'];
                $status = $search['status'];
                if ($username != '') {
                    $where['userName'] = array('like', '%'.$username.'%');
                }
                if ($phone != '') {
                    $where['phone'] = array('like', '%'.$phone.'%');
                }
                if ($status !=-1) {
                    $where['status'] = array('eq',$status);
                }

            }
            $draw = I('draw',1,'intval');
            $start = I('start',0,'intval');
            $length = I('length',10,'intval');
            //排序设置
            $mycolumns = I('mycolumns','');
            $myorder = I('order','');
            $order='';
            if (empty($myorder)||empty($mycolumns)) {
                $order = 'farm_direction_sale.id desc';
            }else{
                foreach ($myorder as $key => $v) {
                    $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
                }
                $order = rtrim($order , ',');
            }

            $db = M("direction_sale");
             $lists = $db->join("farm_user as u on u.id=farm_direction_sale.saleId")
                 ->field('farm_direction_sale.*,u.userName,u.phone')
                 ->where($where)
                 ->limit($start,$length)
                 ->order($order)
                 ->select();
        $time=time()-24*3600;//买家确认1天后，卖家未确认
        foreach($lists as $k=>$v){
            if($v['status']==1&&$v['updateTime']<=$time){
                $lists[$k]['isCheck']=1;
            }else{
                $lists[$k]['isCheck']=0;
            }
            $lists[$k]['createTime']=date("Y-m-d H:i:s",$v['createTime']);
            $lists[$k]['updateTime']=date("Y-m-d H:i:s",$v['updateTime']);
        }
            $result['draw'] = $draw;
            $result['recordsTotal'] = $db->join("farm_user as u on u.id=farm_direction_sale.saleId")
                ->field('farm_direction_sale.*,u.userName,u.phone')->where($where)->count();
            $result['recordsFiltered'] = $result['recordsTotal'];
            $result['data'] = $lists;


            $this->ajaxReturn($result);
    }

    /**
     * 交易完成
     */
    public function finish(){
        $db = M("direction_sale");
        $ids=trim(I("post.ids"),'|');
        $arr=explode("|",$ids);
        $garden=M("user_garden");
        foreach($arr as $v){
            $data=$db->where("id=$v")->find();
            if($user=$garden->where("userId=".$data['buyId'])->find()){
                if($data['status']==1){//买家已确认
                    $res=$garden->where("id=".$user['id'])->setInc('houseFruit',$data['realNum']);
                    $db->where("id=$v")->save(array('status'=>2,'updateTime'=>time()));//卖家已确认
                }

            }else{
                //当表里不存在该用户，向果园表里插入数据
                $map=array('userId'=>$data['buyId'],'houseFruit'=>$data['realNum']);
                $res=$garden->add($map);
            }
        }
        $this->ok("交易成功");
    }
    /**
     * 委托出售列表
     */
    public function commission(){
        if (!IS_AJAX) {
            $this->display();
            return ;
        }

        // AJAX请求
        // 搜索
        $search = I('search','');
        $where['isShow']=array('eq',1);
        if (!empty($search['value'])) {
            $searchStr = html_entity_decode($search['value']);
            parse_str($searchStr,$search);
            $username = $search['userName'];
            $phone = $search['phone'];
            $status = $search['status'];
            if ($username != '') {
                $where['userName'] = array('like', '%'.$username.'%');
            }
            if ($phone != '') {
                $where['phone'] = array('like', '%'.$phone.'%');
            }
            if ($status >0) {
                $where['status'] = array('eq',$status);
            }

        }
        $draw = I('draw',1,'intval');
        $start = I('start',0,'intval');
        $length = I('length',10,'intval');
        //排序设置
        $mycolumns = I('mycolumns','');
        $myorder = I('order','');
        $order='';
        if (empty($myorder)||empty($mycolumns)) {
            $order = 'farm_commission_sale.id desc';
        }else{
            foreach ($myorder as $key => $v) {
                $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
            }
            $order = rtrim($order , ',');
        }

        $db = M("commission_sale");
        $lists = $db->join("farm_user as u on u.id=farm_commission_sale.saleId")
            ->field('farm_commission_sale.*,u.userName,u.phone')
            ->where($where)
            ->limit($start,$length)
            ->order($order)
            ->select();
        foreach($lists as $k=>$v){
            $lists[$k]['createTime']=date("Y-m-d H:i:s",$v['createTime']);
            $lists[$k]['updateTime']=date("Y-m-d H:i:s",$v['updateTime']);
        }
        $result['draw'] = $draw;
        $result['recordsTotal'] = $db->join("farm_user as u on u.id=farm_commission_sale.saleId")
            ->field('farm_commission_sale.*,u.userName,u.phone')->where($where)->count();
        $result['recordsFiltered'] = $result['recordsTotal'];
        $result['data'] = $lists;
        $this->ajaxReturn($result);
    }
    /**
     * 委托出售审核
     */
    public function check_(){
        $id=I("post.id");
        $res=M("commission_sale")->where("id=$id")->save(array('status'=>2,'updateTime'=>time()));
        if($res){
            $this->ok('处理成功');
        }else{
            $this->wrong("处理失败");
        }
    }
}