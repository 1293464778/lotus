<?php
namespace Api\Controller;
use Think\Controller;
use Common\Util\AuthUtil;
/**
 * 首页
 * @author 念小七
 */
class IndexController extends Controller {
    /**
     * 添加站内信
     */
    public function mail(){
        //用户身份验证
        $result = AuthUtil::checkIdentity();
        if (!$result['status']){
            ajax_return_error(null,$result['code']);
        }
        $user=session(C("SESSION_NAME_CUR_HOME"));
        $uid=$user['id'];

        $title=I("post.title");
        if(empty($title)){
            ajax_return_error("请填写标题");
        }
        $content=I("post.content");
        if(empty($content)){
            ajax_return_error("请填写内容");
        }
        //发布站内信需要扣除的果实数量
        $num=M("config")->where("id=26")->getField("value");
        $houseFruit=M("user_garden")->where("userId=$uid")->getField("houseFruit");
        if($num>$houseFruit){
            ajax_return_error("你仓库的荷花数量不足");
        }
        $file=$_FILES['file'];
        $pic='';
//        ajax_return_ok($file);exit;
        if($file){
            //图片上传
            $maxSize =  10485760;//图片最大值
            $allowExts = array('jpg', 'gif', 'png', 'jpeg');//图片允许的后缀
            $savePath = './Uploads/images/';//图片上传地址
            $config = array(
                'mimes'         =>  array(), //允许上传的文件MiMe类型
                'maxSize'       =>  $maxSize, //上传的文件大小限制 (0-不做限制)
                'exts'          =>  $allowExts, //允许上传的文件后缀
                'autoSub'       =>  true, //自动子目录保存文件
                'subName'       =>  array('date', 'Ymd'), //子目录创建方式
                'rootPath'      =>  $savePath, //保存根路径
                'savePath'      =>  '', //保存路径
                'saveName'      =>  array('uniqid', ''), //上传文件命名规
                'callback'      =>  false, //检测文件是否存在回调，如果存在返回文件信息数组
            );

            $upload = new \Think\Upload($config);// 实例化上传类
            $info   =   $upload->upload();
//        ajax_return_ok($info);
            if(!$info) {// 上传错误提示错误信息
                $error=$upload->getError();
                return msg_return(0 ,$error);
            }else{// 上传成功 获取上传文件信息
                $pic='';
                foreach($info as $k=>$v){
                    $pic.=$v['savepath'].$v['savename'].'|';
                }
            }
        }

        $model=M();
        $model->startTrans();
        //1 减掉仓库果实数量
        $res1=M("user_garden")->where("userId=$uid")->setDec("houseFruit",$num);
        //2 加入站内信表
        $map=array(
            'userId'=>$uid,
            'title'=>$title,
            'content'=>$content,
            'pic'=>$pic,
            'fruitNum'=>$num,
            'status'=>0,
            'createTime'=>time(),
            'updateTime'=>time(),
            'phone'=>M("user")->where("id=$uid")->getField("phone"),
        );
        $res2=M("mail")->add($map);
        if($res1&&$res2){
            $model->commit();
            ajax_return_ok('','提交成功');
        }else{
            $model->rollback();
            ajax_return_error("提交失败");
        }
    }
    /**
     * 温馨提示
     */
    public function remind(){
        $id=I("post.id");
        $data=M("notice")->find($id);
        $data['content']=htmlspecialchars_decode($data['content']);
        ajax_return_ok($data);
    }
    /**
     * wap站添加站内信
     */
    public function add(){
        //用户身份验证
        $result = AuthUtil::checkIdentity();
        if (!$result['status']){
            ajax_return_error(null,$result['code']);
        }
        $user=session(C("SESSION_NAME_CUR_HOME"));
        $uid=$user['id'];

        $title=I("post.title");
        if(empty($title)){
            ajax_return_error("请填写标题");
        }
        $content=I("post.content");
        if(empty($content)){
            ajax_return_error("请填写内容");
        }
        //发布站内信需要扣除的果实数量
        $num=M("config")->where("id=26")->getField("value");
        $houseFruit=M("user_garden")->where("userId=$uid")->getField("houseFruit");
        if($num>$houseFruit){
            ajax_return_error("你仓库的荷花数量不足");
        }
        $message1 = $_POST['message']; //接收以base64编码的图片数据
//        var_dump($message1);exit;
        $path='';
        foreach($message1 as $v){
            $num1=strpos($v,"base64");
            $num1=substr($v,0,$num1);
            $message = base64_decode(substr($v,strlen($num1.'base64,')));
            $filename = uniqid().date('Ymd').'.jpeg';
            $furl = "./Uploads/images/";
            $file = fopen($furl.$filename,"w");
            file_put_contents($furl.$filename,$message);
            $path.=$filename.'|';
        }
        $model=M();
        $model->startTrans();
        //1 减掉仓库果实数量
        $res1=M("user_garden")->where("userId=$uid")->setDec("houseFruit",$num);
        //2 加入站内信表
        $map=array(
            'userId'=>$uid,
            'title'=>$title,
            'content'=>$content,
            'pic'=>$path,
            'fruitNum'=>$num,
            'status'=>0,
            'createTime'=>time(),
            'updateTime'=>time(),
            'phone'=>M("user")->where("id=$uid")->getField("phone"),
        );
        $res2=M("mail")->add($map);
        if($res1&&$res2){
            $model->commit();
            ajax_return_ok('','提交成功');
        }else{
            $model->rollback();
            ajax_return_error("提交失败");
        }

    }
}