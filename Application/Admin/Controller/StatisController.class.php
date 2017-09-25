<?php
/**
 * Created by PhpStorm.
 * User: xiaolei
 * Date: 2017/3/14
 * Time: 10:58
 */

namespace Admin\Controller;


class StatisController extends BaseController
{
    // 控制器权限分块
    public $privilege=array(
        'index' => array('getDirection'),
        'commission'=>array('getCommission'),
    );
    /**
     * 定向订单统计
     */
    public function index(){
        $got = $this->select(1,2);
        // 更改返回数据的格式
        $data['date'] = json_encode(array_get_column($got, 'day'));
        $data['gotNum'] = json_encode(array_get_column($got, 'num'));
        $data['gotTotal'] = json_encode(array_get_column($got, 'totalNum'));
        $this->data = $data;
        $this->display();
    }
    public function getDirection($status){
        $got = $this->select($status,2);
        // 更改返回数据的格式
        $dateArr = array(1=>'day', 2=>'month', '3'=>'year');
        $data['date'] = json_encode(array_get_column($got, $dateArr[$status]));
        $data['gotNum'] = json_encode(array_get_column($got, 'num'));
        $data['gotTotal'] = json_encode(array_get_column($got, 'totalNum'));
        $res = array('status'=>1, 'data'=>$data);
        $this->ajaxReturn($res);
    }
    /**
     * 委托订单统计
     */
    public function commission(){
        $got = $this->select(1,3);
        // 更改返回数据的格式
        $data['date'] = json_encode(array_get_column($got, 'day'));
        $data['gotNum'] = json_encode(array_get_column($got, 'num'));
        $data['gotTotal'] = json_encode(array_get_column($got, 'totalNum'));
        $this->data = $data;
        $this->display();
    }
    public function getCommission($status){
        $got = $this->select($status,3);
        // 更改返回数据的格式
        $dateArr = array(1=>'day', 2=>'month', '3'=>'year');
        $data['date'] = json_encode(array_get_column($got, $dateArr[$status]));
        $data['gotNum'] = json_encode(array_get_column($got, 'num'));
        $data['gotTotal'] = json_encode(array_get_column($got, 'totalNum'));
        $res = array('status'=>1, 'data'=>$data);
        $this->ajaxReturn($res);
    }
}