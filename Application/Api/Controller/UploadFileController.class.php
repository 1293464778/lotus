<?php
namespace Api\Controller;
use Think\Controller;
use Common\Util\UploadUtil;
/**
 * [文件上传处理]
 */
class UploadFileController extends Controller
{
    public function upImage(){
        $img = UploadUtil::upimage();
        $data['pic'] = $img['url'];
        ajax_return_ok($data);
    }

   public function delete(){
       UploadUtil::delete();
    }
}