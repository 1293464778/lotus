<?php
namespace Common\Model;
use Think\Model;
use Common\CommonConstant;
use Common\Util\JwtUtil;
use Common\Util\StringUtil;


/**
 * 会员数据层处理
 * @author xiegaolei
 *
 */
class UserModel extends Model {
 	
	/**
	 * 登录验证
	 * @param unknown $name
	 * @param unknown $pass
	 */
	public function login($name, $pass) {
        // 查找身份，验证身份
        $where ['phone'] = $name;
        $where ['password'] = encrypt_pass( $pass );
        
        $user = $this->where ( $where )
            ->find ();
        if (! $user) {
            ajax_return_error( '用户名或者密码不正确');
        }
        
        // 检测用户状态
        if ($user ['isShow'] != CommonConstant::DB_TRUE) {
            ajax_return_error( '用户已经禁用');
        }
		$get_session=session_id();
		
		$this->where($where)->setField("sessionid",$get_session);

        // 数据处理和令牌获取
        $userAccessToken = $this->processDataOfLogin ( $user ['id'] );
        
        // 添加缓存
        session ( C ( "SESSION_NAME_CUR_HOME" ), $user );
        
        // 返回
        return array ('userAccessToken' => $userAccessToken,'user' => $user );
    }
    
    
    /**
     * 公用注册
     */
    public function commonRegister( $param) {
     
    	$curTime = time ();
    	// 用户主表数据更新
    	// 用户名和密码信息
    	$data['userName'] = $param['userName'];
    	$data['pass'] = encrypt_pass( $param['pass'] );
    	  
    	$data ['regTime'] = $curTime;
    	$data ['regIp'] = StringUtil::getIp ();
    	$newId = $this->add ( $data );
    	if (! $newId) {
    		ajax_return_error( '注册失败');
    	}
    	// 返回必要信息
    	return array ('id' => $newId );
    }
     
    
    
    
    
    
    
    
    
    /**
     * 处理登录的数据，以及令牌生成和返回
     * @param int $id 用户ID
     * @return string 已编码的用户访问令牌
     */
    private function processDataOfLogin($id) {
    	// 令牌生成
    	$payload['uid'] = $id;
    	$payload['loginTime'] =  time ();
    	$userAccessToken = JwtUtil::encode ( $payload );
    	// 返回令牌
    	return $userAccessToken;
    }
	
	
	
	
	
	
	
	
}