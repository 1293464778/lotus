<?php
/**
 * Created by PhpStorm.
 * User: xiaolei
 * Date: 2017/3/23
 * Time: 11:37
 */

namespace Api\Controller;


class SysController extends BaseController
{
    /**
     * 公告列表  详情信息
     */
    public function notice(){
        $data=M("notice")->where("type=1")->order("id desc")->limit(15)->select();
        foreach($data as $k=>$v){
            $pub=strtotime($v['publishTime']);
            $data[$k]['time']=date("Y-m-d H:i",$pub);
            $data[$k]['content']=htmlspecialchars_decode($v['content']);
        }
        ajax_return_ok($data);
    }
    /**
     * 获取订购验证码所需的果实
     */
    public function getFruit(){
        $data=M("config")->where("id=29")->getField("value");
        ajax_return_ok($data,"请求成功");
    }
    /**
     * 采蜜大师购买所需的果实
     */
    public function getFruit1(){
        $data=M("config")->where("id=28")->getField("value");
        ajax_return_ok($data,"请求成功");
    }
    
}