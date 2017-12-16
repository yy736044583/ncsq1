<?php
namespace app\wechat\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Common extends \think\Controller{

	public function _initialize(){
		
		//Session('openid',input('openid'));
		//session_start();
		// Session::set('Openid',input('openid'));
		Session::set('Openid',"abddddd");
		//echo Session::get('Openid');die;
		

	}
}
