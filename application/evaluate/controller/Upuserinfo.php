<?php  
namespace app\evaluate\controller;
use think\Request;  
use think\Controller;
use think\Db;
//修改用户密码
class Upuserinfo extends \think\Controller{

	public function index(){
		$action = input('action');
		$Passwordold = input('Passwordold');
		$Passwordnew = input('Passwordnew');
		$Userid	 = input('Userid');;
		switch ($action) {
			case 'update':
				$this->update($Passwordold,$Passwordnew,$Userid);
				break;
			
			default:
				# code...
				break;
		}
	}
	
	//修改密码
	public function update($Passwordold,$Passwordnew,$Userid){
		$Passwordold = md5($Passwordold);
		$Passwordnew = md5($Passwordnew);
		$password = Db::name('sys_workman')->where("id",$Userid)->value('loginpass');
		
		if($password===$Passwordold){
			if(Db::name('sys_workman')->where("id",$Userid)->update(['loginpass'=>$Passwordnew])){
				$data = ['type'=>'OK'];
			}else{
				$data = ['error'=>'修改失败'];
			}
		}else{
			$data = ['error'=>'旧密码错误'];
		}
		echo json_encode($data);
	}


}