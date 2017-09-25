<?php
namespace Admin\Controller;


class ChargeController extends BaseController {
    /**
    * 收费方式列表
    */
    public function index() {
        if (!IS_AJAX) {
            $this->display();
            return ;
        }


        $draw = I('draw',1,'intval');
        $start = I('start',0,'intval');
        $length = I('length',10,'intval');
        //排序设置
        $mycolumns = I('mycolumns','');
        $myorder = I('order','');
        if (empty($myorder)||empty($mycolumns)) {
            $order = 'id desc';
        }else{
            foreach ($myorder as $key => $v) {
                $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
            }
            $order = rtrim($order , ',');
        }

        $db = M('charge');
         $lists = $db
             ->limit($start,$length)
             ->order($order)
             ->select();

        $result['draw'] = $draw;
        $result['recordsTotal'] = $db->where(true)->count();
        $result['recordsFiltered'] = $db->where($where)->count();
        $result['data'] = $lists;

        $this->ajaxReturn($result);
    }

    /**
     * 添加页面表示
     */
    public function add() {
        $this->display();
    }

    /**
     * 计费方式添加保存
     */
    public function addHandle() {
        $charge = I('post.');
        $res = M('charge')->add($charge);

        if ($res === false) {
            $this->wrong('添加失败');
        }
        $this->ok('添加成功');
    }

    /**
     * 编辑页面
     */
    public function edit() {
        $chargeId = I('id');
        $this->data = M("charge")->where(array('id'=>$chargeId))->find();
        // 获取跳转前页面
        $url = $_SERVER['HTTP_REFERER'];
        if (!$this->data) {
            header("Location:$url");exit;
        }

        $this->display();
    }

    /**
     * 编辑保存
     */
    public function save() {
        $data = I('post.');
        $rs = M('charge')->save($data);
        if ($rs ===  false) {
            $this->wrong('保存失败！');
        }

        $this->ok('保存成功！');
    }

    /**
     * 删除
     */
    public function del($id){
        $res = M('charge')->where(array('id'=>$id))->delete();
        if ($res === false) {
            $this->wrong('删除失败');
        }
        $this->ok('删除成功');
    }
}