<?php
namespace Admin\Controller;


class SysController extends BaseController {
    // 控制器权限分块
    public $privilege=array(
        'remind' => array('remind'),
        'index'=>array('editremind','save','del','add','sort','edit','addNotice'),
    );

    /**
    * 系统公告列表
    */
    public function index() {
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

        $db = M('notice');
         $lists = $db->where("type=1")
             ->limit($start,$length)
             ->order($order)
             ->select();
        foreach($lists as $k=>$v){
            $lists[$k]['createTime']=date("Y-m-d H:i:s",$v['createTime']);
            $lists[$k]['updateTime']=date("Y-m-d H:i:s",$v['updateTime']);
        }
        $result['draw'] = $draw;
        $result['recordsTotal'] = $db->where(true)->count();
        $result['recordsFiltered'] = $db->count();
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
     * 公告添加保存
     */
    public function addNotice() {
        $carType = I('post.');
        $carType['createTime']=time();
        $carType['updateTime']=time();
        $carType['type']=1;
        $res = M('notice')->add($carType);

        if ($res === false) {
            $this->wrong('添加失败');
        }
        $this->ok('添加成功');
    }

    /**
     * 编辑页面
     */
    public function edit() {
        $carTypeId = I('id');
        $this->data = M("notice")->where(array('id'=>$carTypeId))->find();
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
        $data['updateTime']=time();
        $rs = M('notice')->save($data);
        if ($rs ===  false) {
            $this->wrong('保存失败！');
        }

        $this->ok('保存成功！');
    }

    /**
     * 删除
     */
    public function del($id){
        $res = M('notice')->where(array('id'=>$id))->delete();
        if ($res === false) {
            $this->wrong('删除失败');
        }
        $this->ok('删除成功');
    }

    /**
     * 排序
     */
    public function sort(){
        $data['id'] = I("id", 0, "int");
        $data['sort'] = I("sort", 50, "int");
        $res = M('notice')->save($data);
        if ($res === false) {
            $this->wrong('更新失败');
        }
        $this->ok('更新成功');
    }
    /**
     * 温馨提示
     */
    public function remind(){
        $data=M("notice")->where("type=2")->order("id desc")->select();
        $this->data=$data;
        $this->display();
    }
    public function editremind(){
        $id=I("id");
        $data=M("notice")->find($id);
        $this->data=$data;
        $this->display();
    }
}