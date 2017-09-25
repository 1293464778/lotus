<?php
namespace Admin\Controller;
use Common\Util\UploadUtil;

class UploadFileController extends \think\Controller {

    public function upImage(){
        $data = UploadUtil::upimage();
        $data['status'] = 1;
        $this->ajaxReturn($data);
    }

   public function delete(){
       UploadUtil::delete();
    }
}