<?php
namespace Admin\Controller;
 

class IndexController extends BaseController {
/**
    * 系统主页
    */
    public function index(){
        /* 统计信息获取 */
        $this->user=M("user")->where("isShow=1")->count();
        $order1=M("direction_sale")->count();
        $order2=M("commission_sale")->count();
        $this->order=$order1+$order2;
        $num1=M("direction_sale")->sum("realNum");
        $num2=M("commission_sale")->sum("realNum");
        $this->num=$num1+$num2;
        $this->houseFruit=M("user_garden")->sum("houseFruit");
        $this->totalNum=M("user_garden")->sum("totalNum");
        // 默认获取30天内的数据
        $got = $this->select(1,1);
       // 更改返回数据的格式
        $data['date'] = json_encode(array_get_column($got, 'day'));
        $data['gotUser'] = json_encode(array_get_column($got, 'num'));
        $this->data = $data;
        $this->display();
    }

    /**
     * 列表示例
     */
    public function getData($status){
        $got = $this->select($status,1);
        // 更改返回数据的格式
        $dateArr = array(1=>'day', 2=>'month', '3'=>'year');
        $data['date'] = json_encode(array_get_column($got, $dateArr[$status]));
        $data['gotUser'] = json_encode(array_get_column($got, 'num'));
        $res = array('status'=>1, 'data'=>$data);
        $this->ajaxReturn($res);
    }

    
}