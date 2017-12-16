<?php  
namespace app\evaluate\controller;
use think\Request;  
use think\Controller;
use think\Db;
//查询用户窗口是否在线
class Windowtype extends \think\Controller{

	public function index(){
		$action = input('action');
		switch ($action) {
			case 'showtype':
				$userid = input('Userid');	//用户id
				$window = input('WindowName');//窗口名
				$this->showtype($userid,$window);
				break;
			
			default:
				# code...
				break;
		}
	}
	public function showtype($userid,$window){
		if(empty($userid)||empty($userid)){
			return;
		}
		$window = mb_convert_encoding($window,'UTF-8', 'gbk');
		if(Db::name('sys_window')->where("name='$window' and workmanid='$userid'")->find()){
			$data = ['type'=>'OK'];
		}else{
			$data = ['type'=>'down'];
		}
		echo json_encode($data);
	}

	
}