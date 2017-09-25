<?php
namespace Admin\Controller;
 
class LogController extends BaseController {
    /**
    * 后台登陆日志
    */
    public function index(){
        if (!IS_AJAX) {
            $this->display();
            return;
        }

        // AJAX请求
        //搜索
        $search = I('search','');
        if (!empty($search['value'])) {
            $searchStr = html_entity_decode($search['value']);
            parse_str($searchStr,$search);
            $username = $search['username'];
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];

            if ($endTime == '') {
                $endTime = date('Y-m-d',time());
            }
            if ($username != '') {
                $where['username'] = array('LIKE', '%'.$username.'%');
            }
            if ($startTime != '') {
                $where['loginTime'] = array('between', array(strtotime($startTime.'00:00:00'),strtotime($endTime.'23:59:59')));
            } else {
                $where['loginTime'] = array('lt', strtotime($endTime.'23:59:59'));
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
            $order = 'loginTime desc';
        } else {
            foreach ($myorder as $key => $v) {
                $order .= $mycolumns[$v['column']].' '.$v['dir'].' ,';
            }
            $order = rtrim($order , ',');
        }

        $db = M('LoginLog');
         $lists = $db
             ->field('id,uid,username,loginIp,FROM_UNIXTIME(loginTime) as loginTime,roles')
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
}