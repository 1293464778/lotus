<?php
/**
 * Created by PhpStorm.
 * User: xiaolei
 * Date: 2017/3/9
 * Time: 14:47
 */

namespace Admin\Controller;


class MailController extends BaseController
{

    public $db;
    function __construct()
    {
        parent::__construct();
        $this->db=M('mail');
    }

    /**
     * 站内信列表
     */
    public function index(){
        if (!IS_AJAX) {
            $this->display();
            return ;
        }
        // AJAX请求
        $draw = I('draw',1,'intval');
        $start = I('start',0,'intval');
        $length = I('length',10,'intval');
        //排序设置
        $mycolumns = I('mycolumns','');
        $myorder = I('order','');
        $order='';
        if (empty($myorder)||empty($mycolumns)) {
            $order = 'id desc';
        }else{
            foreach ($myorder as $key => $v) {
                $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
            }
            $order = rtrim($order , ',');
        }
        $lists = $this->db->join("farm_user as u on u.id=farm_mail.userId")
            ->limit($start,$length)
            ->order($order)->field("farm_mail.*,u.userName")
            ->select();
//        dump($lists);exit;
        foreach($lists as $k=>$v){
            $lists[$k]['createTime']=date("Y-m-d H:i:s",$v['createTime']);
            $lists[$k]['updateTime']=date("Y-m-d H:i:s",$v['updateTime']);
            $img=explode('|',trim($v['pic'],'|'));
            $lists[$k]['pic']=$img;
        }
        $result['draw'] = $draw;
        $result['recordsTotal'] = $this->db->where(true)->count();
        $result['recordsFiltered'] = $this->db->count();
        $result['data'] = $lists;

        $this->ajaxReturn($result);
    }

    /**
     * 审核
     */
    function check(){
        layout(false);
        $id=I("post.id");
        $res=$this->db->where("id=$id")->save(array('status'=>1,'updateTime'=>time()));
        if ($res === false) {
            $this->wrong('审核失败');
        }
        $this->ok('审核成功');
    }

    /**
     * 删除
     */
    function del(){
        $id=I("post.id");
        $res=$this->db->delete($id);
        if ($res === false) {
            $this->wrong('删除失败');
        }
        $this->ok('删除成功');
    }
    /**
     * 详情展示
     */
    public function detail($id){
        $data=$this->db->find($id);
        echo $data['content'];
    }
}