<?php
namespace Api\Controller;
use Common\Util\AuthUtil;
use Common\CommonConstant;

/**
 * 会员中心基类
 *
 *
 */
class CenterController extends BaseController {
    public $uid;
    //初始化
    public function _initialize(){

        parent::_initialize();
        //用户身份验证
        $result = AuthUtil::checkIdentity();
        if (!$result['status']){
            ajax_return_error(null,$result['code']);
        }
        $user=session(C("SESSION_NAME_CUR_HOME"));
        $this->uid=$user['id'];
    }
    /**
     * 获取用户的账号和果园等级   发展好友需要的果实数量
     */
    public function getInfo(){
        $uid=$this->uid;
        $data=M("user")->where("id=$uid")->field("userName,level")->find();
        $apply_fee=$this->getConfig(23);
        $data['apply_fee']=$apply_fee;
        $max_level=$this->getConfig(32);
        $data['max_level']=$max_level;
        ajax_return_ok($data);
    }
    /**
     * 推荐好友
     */
    public function recommen(){
		$model=M();
        $model->startTrans();
        $uid=$this->uid;
        $post=I("post.");
        foreach($post as $k=>$v){
            if(empty($v)){
                ajax_return_error(null,2);
            }
        }
        //1 判断新用户名是否存在
        $where['userName']=array('eq',$post['userName']);
//        $where['isShow']=1;
        if($find=M("user")->where($where)->find()){
            ajax_return_error("新庄园用户名已存在，请换名");
        }
        //2 判断手机号输入是否合法
        preg_match('/^1[0-9]{10}$/',trim($post['phone']),$matches);
        if(!$matches){
            ajax_return_error("手机号输入有误");
        }
        //1 判断手机号是否存在
        $where2['phone']=array('eq',$post['phone']);
        if($find=M("user")->where($where2)->find()){
            ajax_return_error("手机号已存在");
        }
        //3 判断推荐人
        $where1['userName']=array('eq',$post['name']);
        $user=M("user")->where($where1)->find();
		if(!$user){
            ajax_return_error("该推荐人不存在，请换号");
        }
        $garden=M("user_garden")->where("userId=".$uid)->find();
        $apply_fee=$this->getConfig(23);//发展好友需要扣掉的果实数量
        if($apply_fee>$garden['houseFruit']){
            ajax_return_error("你的仓库荷花数量不足");
        }
        //4 扣掉该用户的仓库果实
        $res1=M("user_garden")->where("userId=".$uid)->setDec('houseFruit',$apply_fee);
        //5 将新用户插入用户表
        $password=$this->getConfig(34);
        $second=$this->getConfig(35);
        $map=array(
            'userName'=>$post['userName'],
            'sex'=>$post['sex'],
            'realName'=>$post['realName'],
            'phone'=>$post['phone'],
            'createTime'=>time(),
            'updateTime'=>time(),
            'isShow'=>1,
            'password'=>encrypt_pass($password),
            'secondPwd'=>encrypt_pass($second),
        );
        $res2=M("user")->add($map);
        //6 插入推荐表
        $map1=array(
            'userId'=>$user['id'],
            'recommendedId'=>$res2,
            'createTime'=>time(),
            'updateTime'=>time(),
            'seedNum'=>M("config")->where("id=27")->getField('value'),
            'status'=>1,
        );
        $res3=M("recommend")->add($map1);
        $res4=$this->save($user['id'],$res2);
        if($res1&&$res3&&$res4){
			$model->commit();
            ajax_return_ok('','提交成功');
        }else{
			$model->rollback();
            ajax_return_error("提交失败");
        }
    }
    public function save($uid,$friend_id){
        
        $user_garden=M("user_garden");
        $res2=M("user")->where("id=".$uid)->setInc("friendNum",1);
        //推荐成功  送种子
        $seed=M("config")->where("id=27")->getField("value");
        $res3=$user_garden->where("userId=".$uid)->setInc('seed',$seed);
        //加入种子奖励记录
        $res8=M("user_seed")->add(array("userId"=>$uid,"num"=>$seed,"createTime"=>time()));

        //新人进来送果实 开垦一块土地
        $fruit=M("config")->where("id=7")->getField("value");
        $res4=$user_garden->add(array('userId'=>$friend_id,'landNum'=>1,'totalNum'=>$fruit));
        $res7=M("user_land")->add(array('userId'=>$friend_id,'baseNum'=>$fruit,'fruitNum'=>$fruit,'landId'=>1,'createTime'=>time(),'updateTime'=>time()));
        //判断是否送花仙子
        $scracow_fee=M("config")->where("id=17")->getField("value");//增加1个花仙子需要推荐的人数
        $max_scracow=M("config")->where("id=20")->getField("value");//花仙子上限的数量
        $scracow=$user_garden->where("userId=".$uid)->getField("fairy");
        $res5=1;$res9=1;
        if($scracow<$max_scracow){
            $friend_num=M("user")->where("id=".$uid)->getField("friendNum");
            $get_num=floor($friend_num/$scracow_fee)-$scracow;
            if($get_num>0){
                if($get_num>($max_scracow-$scracow)){
                    $get_num=$max_scracow-$scracow;
                }
                $res5=$user_garden->where("userId=".$uid)->setInc("fairy",$get_num);
                //加入花仙子记录
                $res9=M("user_prop")->add(array("userId"=>$uid,"num"=>$get_num,"type"=>1,"createTime"=>time()));
            }
        }
        if($res2&&$res3&&$res4&&$res5&&$res7&&$res8&&$res9){
            
            return true;
        }else{
            
            return false;
        }

    }
    /**
     * 判断二级密码
     */
    public function checkSecPwd(){
        $uid=$this->uid;
        $secondPwd=I("post.secondPwd");
        $pwd=M("user")->where("id=$uid")->getField("secondPwd");
        if($pwd!=encrypt_pass($secondPwd)){
            ajax_return_error("二级密码输入错误");
        }else{
            ajax_return_ok('','输入正确');
        }
    }
    /**
     * 个人信息展示
     */
    public function getUser(){
        $uid=$this->uid;
        $data=M("user")->where("id=$uid")->find();
        ajax_return_ok($data);
    }
    /**
     * 个人信息修改
     */
    public function editUser(){
        $uid=$this->uid;
        $post=I("post.");
        foreach($post as $v){
            if(empty($v)){
                ajax_return_error("请把信息填写完整");
            }
        }
        $where['userName']=$post['userName'];
        $where['id']=array('neq',$uid);
        $find=M("user")->where($where)->find();
        if($find){
            ajax_return_error("该用户名已存在");
        }
        $res=M("user")->where("id=$uid")->save($post);
        if($res){
            ajax_return_ok("","提交成功");
        }else{
            ajax_return_error("信息未作修改");
        }

    }

    /**
     * 修改密码
     */
    public function editPwd(){
        $uid=$this->uid;
        $post=I("post.");
        $model=M("user");
        $user=$model->where("id=$uid")->field("password,secondPwd")->find();
        $res1=0;$res2=0;
        // 修改一级密码
        if($post['newPwd']||$post['rePwd']){
            if($post['newPwd']!=$post['rePwd']){
                ajax_return_error("一级新密码两次输入不一致");
            }
            $str=strlen($post['newPwd']);
            if($str<6||$str>20){
                ajax_return_error("密码的长度应该在6-20位");
            }
            if($user['password']!=encrypt_pass($post['password'])){
                ajax_return_error("一级旧密码输入错误");
            }

            $data['password']=encrypt_pass($post['newPwd']);
            $res1=$model->where("id=$uid")->save($data);
        }
        // 修改二级密码
        if($post['newSecondPwd']||$post['reSecondPwd']){
            if($post['newSecondPwd']!=$post['reSecondPwd']){
                ajax_return_error("二级新密码两次输入不一致");
            }
            $str=strlen($post['newSecondPwd']);
            if($str<6||$str>20){
                ajax_return_error("密码的长度应该在6-20位");
            }
            if($user['secondPwd']!=encrypt_pass($post['secondPwd'])){
                ajax_return_error("二级旧密码输入错误");
            }
            $data1['secondPwd']=encrypt_pass($post['newSecondPwd']);
            $res2=$model->where("id=$uid")->save($data1);
        }
        if(!$res1&&!$res2){
            ajax_return_error("你并未做修改");
        }else{
            ajax_return_ok('',"修改成功");
        }
    }
}