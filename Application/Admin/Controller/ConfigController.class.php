<?php
namespace Admin\Controller;
use Common\Util\UploadUtil;

/**
 * 配置管理
 * @author xiegaolei
 *
 */
class ConfigController extends BaseController {
    public $db;
    function __construct()
    {
        parent::__construct();
        $this->db=M("config");
    }

    /**
     * 比例设置
     */
    public function index(){
        $this->data=$this->db->where("type=1")->order("id asc")->select();
        $this->data1=$this->db->where("type=2")->order("id asc")->select();
        $this->data2=$this->db->where("type=3")->order("id asc")->select();
        $this->data3=$this->db->where("type=4")->order("id asc")->select();
        $this->display();
    }
    /**
     * 等级比例保存
     */
    function level(){
        $level=I("post.config_value");
        foreach($level as $k=>$v){
            if($k>=6){
                $i=$k+30;
            }else{
                $i=$k+1;
            }

            $res=$this->db->where("id=$i")->save(array('value'=>$v));
        }
        $this->ok('保存成功！');
    }
    /**
     * 农场比例保存
     */
    function farm(){
        $level=I("post.config_value");
        foreach($level as $k=>$v){
            $i=$k+7;
            $res=$this->db->where("id=$i")->save(array('value'=>$v));
        }
        $this->ok('保存成功！');
    }

    /**
     * 农场道具比例保存
     */
    function prop(){
        $level=I("post.config_value");
        foreach($level as $k=>$v){
            $i=$k+14;
            $res=$this->db->where("id=$i")->save(array('value'=>$v));
        }
        $this->ok('保存成功！');
    }
    /**
     * 其他比例保存
     */
    function recommen(){
        $level=I("post.config_value");
        foreach($level as $k=>$v){
            $i=$k+23;
            $res=$this->db->where("id=$i")->save(array('value'=>$v));
        }
        $this->ok('保存成功！');
    }

}
