<?php 
header('Content-Type:application/json; charset=utf-8');
$result['msg'] = '系统出错了';
$result['status'] = 0;
$result['code'] = 1;
exit(json_encode($result));
?>