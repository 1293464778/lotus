<?php
namespace Common\Util;


/**
 * 上传工具类
 */
class UploadUtil {
   
    public static function upimage(){
        $imageMaxSize =  10485760;//图片最大值
        $imageAllowExts = array('jpg', 'gif', 'png', 'jpeg');//图片允许的后缀
        $imagePath = './Uploads/images/';//图片上传地址
        
        $result = self::upfiles($imageMaxSize,$imageAllowExts,$imagePath);
        if($result['status'] == false){
             ajax_return_error($result['msg']);
        }
        $res['url'] = C('SITE_ROOT').'/Uploads/images/'.$result['msg']['savepath'].$result['msg']['savename'];
        
        return $res;
    
    }
    
    public static function upvideo(){
        $videoMaxSize =  104857600;//视频最大值
        $videoAllowExts =  array('mp4','mp3','mov','MOV','wma');//视频允许的后缀
        $videoPath = './Uploads/video/';//视频上传地址
        
        $result = self::upfiles($videoMaxSize,$videoAllowExts,$videoPath);
    
        if($result['status'] == false){
            ajax_return_error($result['msg']);
        }
        $res['url'] = C('SITE_ROOT').'/Uploads/video/'.$result['msg']['savepath'].$result['msg']['savename'];
        $res['name'] = $result['msg']['savename'];
        return $res;
        
    }
    
    public static function delete(){
    
        $path = trim($_POST['path']);
        if (empty($path)) {
            ajax_return_error('path is null');
        }
        $path = str_replace(C('SITE_ROOT'), '', $path);
        $path= str_replace('../','',$path);
        $path= trim($path,'.');
        $path= trim($path,'/');
        if(file_exists($path)){
            unlink($path);
            ajax_return_ok(array(),'ok');
        }else{
            ajax_return_error('file is not exists: '.$path);
        }
     
    }
    
    public static function upfile(){
        $fileMaxSize =  104857600;//文件最大值
        $fileAllowExts = array('apk');//文件允许的后缀
        $filePath = './Uploads/files/';//图片上传地址
        
        $result = self::upfiles($fileMaxSize,$fileAllowExts,$filePath);
        if($result['status'] == false){
             ajax_return_error($result['msg']);
        }
        $res['url'] = C('SITE_ROOT').'/Uploads/files/'.$result['msg']['savepath'].$result['msg']['savename'];
        $res['name'] = $result['msg']['savename'];
        return $res;
    
    }
     
    /**
     * [upfiles 支持多上传文件]
     * @param  [string] $maxSize   [附件上传最大值]
     * @param  [array]  $allowExts [附件上传类型]
     * @param  [string] $savePath  [附件上传目录]
     * @return [type]              [description]
     */
    private function upfiles($maxSize,$allowExts,$savePath){
    
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
    
        if(!$info) {// 上传错误提示错误信息
    
            $error=$upload->getError();
            return msg_return(0 ,$error);
            
    
        }else{// 上传成功 获取上传文件信息
            
            return msg_return(1 ,$info['file']);
        }
    
    }
    
}