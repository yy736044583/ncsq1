<?php
namespace app\inter\controller;
use think\Db;
/**
 * Created by PhpStorm.
 * User: yulin
 * Date: 2018/3/1
 * Time: 15:38
 */

class PickUp{
	public function index(){
		//header('Access-Control-Allow-Origin:*');//允许跨域
		$action = input('action');
		switch ($action) {
			// 取件柜取件码短信通知
			case 'pickmessage':
				$this->pickmessage(input('number'),input('phone'));
				break;
			default:
				echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误']);
				return;
				break;
		}
	}

	/**
	 * @param $number 取件码
	 * @param $phone    电话号码
	 */
	public function  pickmessage($number,$phone){
		if(empty($number)||empty($phone)){
			echo 'no'; return ;
		}
		// 查询短信签名和用户名
		$set = Db::name('dx_set')->where('id',1)->find();
		// 模板所需数据
		$json = ['number'=>$number];
		// 短信模板编号
		$code = Db::name('dx_template')->where('type',7)->value('code');

		$data1 = [
			'data'		=> $json,
			'template'	=> $code,
			'phone'		=> $phone,
			'sign'		=> $set['sign'],
			'action'	=> 'sendSms',
			'username'	=> $set['username'],
		];
		 $url = 'http://sms.scsmile.cn/internc/index';
		// url方式提交
		$httpstr = http($url, $data1, 'GET', array("Content-type: text/html; charset=utf-8"));
		echo 'ok'; return ;
	}
}