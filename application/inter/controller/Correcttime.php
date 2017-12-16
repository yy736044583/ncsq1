<?php
namespace app\inter\controller;
use think\Db;
// 矫正时间接口
// app报错日志写入接口
class Correcttime{

	/**
	 * 根据方法名跳转
	 * @param [string] $[action] [方法名]
	 * @param [string] $[devicenum] [设备编号]
	 */
    public function index(){ 
		$action = input('action');
		//根据方法名跳转到各个方法
		switch ($action) {
			//矫正时间
			case 'correct':
				$this->correct();
				break;
			case 'appmessage':
				$this->appmessage(input('devicenum'),input('message'),input('appname'));
				break;	
			default:
				echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
				return;
				break;
		}
    }

    // 矫正时间接口
    public function correct(){
    	// $time = date('Y-m-d-H-i',time());
    	$time = time();
    	$data['date'] = $time;
    	echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;
    }

    /**
     * [appmessage app报错日志写入接口]
     * @param  [type] $devicenum [设备编号]
     * @param  [type] $message   [日志信息]
     * @param  [type] $appname   [app名称]
     */
    public function appmessage($devicenum,$message,$appname){
    	$data['createtime'] = date('Y-m-d H:i:s',time());
    	$data['devicenum'] = $devicenum;
    	$data['message'] = $message;
    	$data['appname'] = $appname;

    	Db::name('sys_adrmessage')->insert($data);
    }
}