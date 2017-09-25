<?php
namespace Admin\Controller;


class ParkController extends BaseController {
    /**
    * 停车场列表
    */
    public function index() {
        if (!IS_AJAX) {
            $this->province = M("chinaProvince")->getField('provinceId,province');
            $this->display();
            return ;
        }

        // AJAX请求
        //搜索
        $search = I('search','');
        if (!empty($search['value'])) {
            $searchStr = html_entity_decode($search['value']);
            parse_str($searchStr,$search);
            $provinceId = $search['province'];
            $cityId = $search['city'];
            $areaId = $search['area'];
            $name = $search['parkName'];

            if ($provinceId != '') {
                $where['provinceId'] = $provinceId;
            }
            if ($cityId != '') {
                $where['cityId'] = $cityId;
            }
            if ($areaId != '') {
                $where['areaId'] = $areaId;
            }
            if ($name != '') {
                $where['name'] = array('like', '%'.$name.'%');
            }
        }

        $where= empty($where)?true:$where;

        $draw = I('draw',1,'intval');
        $start = I('start',0,'intval');
        $length = I('length',10,'intval');
        //排序设置
        $mycolumns = I('mycolumns','');
        $myorder = I('order','');
        if (empty($myorder)||empty($mycolumns)) {
            $order = 'id desc';
        } else {
            foreach ($myorder as $key => $v) {
                if($mycolumns[$v['column']] != 'id'){
                    $order .= $mycolumns[$v['column']].'Id '.$v['dir'].' ,';
                }else{
                    $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
                }
            }
            $order = rtrim($order , ',');
        }

        $db = D('parkView');
         $lists = $db
             ->field('id,name,province,city,area,charge')
             ->where($where)
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
        // 省份获取
        $this->province = M("chinaProvince")->getField('provinceId,province');
        // 收费方式获取
        $this->charge = M("charge")->getField('id,name');

        $this->display();
    }


    /**
     * 停车场添加保存
     */
    public function addHandle() {
        $park = I('post.');
        $res = M('park')->add($park);

        if ($res === false) {
            $this->wrong('添加失败');
        }
        $this->ok('添加成功');
    }

    /**
     * 编辑页面
     */
    public function edit() {
        $parkId = I('id');
        $this->data = M("park")->where(array('id'=>$parkId))->find();
        // 获取跳转前页面
        $url = $_SERVER['HTTP_REFERER'];
        if (!$this->data) {
            header("Location:$url");exit;
        }

        // 省份获取
        $this->province = M("chinaProvince")->getField('provinceId,province');
        // 城市获取
        $this->city = M("chinaCity")->where(array('provinceId'=>$this->data['provinceId']))->getField('cityId,city');
        // 区域获取
        $this->area = M("chinaArea")->where(array('father'=>$this->data['cityId']))->getField('areaId,area');
        // 收费方式获取
        $this->charge = M("charge")->getField('id,name');

        $this->display();
    }

    /**
     * 编辑保存
     */
    public function save() {
        $data = I('post.');
        $rs = M('park')->save($data);
        if ($rs ===  false) {
            $this->wrong('保存失败！');
        }

        $this->ok('保存成功！');
    }

    /**
     * 删除
     */
    public function del($id){
        $res = M('park')->where(array('id'=>$id))->delete();
        if ($res === false) {
            $this->wrong('删除失败');
        }
        $this->ok('删除成功');
    }
}