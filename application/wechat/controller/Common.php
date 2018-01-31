<?php
namespace app\wechat\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Common extends \think\Controller{

	public function _initialize(){
		$appId = 'wx71c245013bbb9567';
		$appsecret = '04a01d2090ddc5173393515572544c1a';
		$Sessionopenid = Session('openid');
		if(!isset($Sessionopenid)){
			if (isset($_GET['code']))
			{
				if($_GET['code']) 
				{
					$code = $_GET['code'];
					$TOKEN_URL="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appId."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
					$json = file_get_contents($TOKEN_URL);
					$result = json_decode($json,true);
					$openid = $result['openid'];

					
					if(empty($result['access_token'])){
						$TOKEN_URL="https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$appId."&grant_type=refresh_token&refresh_token=".$result['refresh_token'];
						$json = file_get_contents($TOKEN_URL);
						$result = json_decode($json,true);
						$openid = $result['openid'];
					}
					Session('openid',$openid);

				}
			}
		}
		// dump(Session('openid'));exit();			
	}
}
