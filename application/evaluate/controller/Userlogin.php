<?php  
namespace app\evaluate\controller;
use think\Request;  
use think\Controller;
use think\Db;
//用户登陆接口
class Userlogin extends \think\Controller{

	public function index(){
		$action = input('action');
		$username = input('username');
		$password = input('password');
		$id = input('id');
		$windowname = input('windowname');
		$type = input('type');
		$userid = input('userid');
		switch ($action) {
			case 'showuserinfo':
				$this->showuserinfo($id,$windowname);//登陆
				break;
			case 'upuserinfo':
				$this->upuserinfo($username,$password);//选择窗口
				break;
			case 'uptype':
				$this->uptype($userid,$type);//员工在线状态
				break;
			case 'windowid':
				$this->windowid($userid);//查询窗口列表
				break;
			default:
				# code...
				break;
		}
	}

	//选择窗口登陆 返回员工信息
	public function upuserinfo($username,$password){
		$password = md5($password);
		//根据用户名密码进行登陆
		$list = Db::name('sys_workman')->where("loginname='$username' and loginpass='$password'")->find();

		if($list){
			$request = request();
			$path1 = $request->domain().'/sbxt/public/upload/';

			$list['SectionName'] = Db::name('sys_section')->where('id',$list['sectionid'])->value('name');
			$windowname = Db::name('sys_window')->where('id',$list['windowid'])->value('name');
			$id = $list['id'];
			$level = $list['starlevel'];
			if($list['photo']!=''){
				$pathurl = $path1.$list['photo'];
			}else{
				$pathurl = '';
			}
			$data = array(
				'id'=>"$id",
				'UserNumber'=>$list['number'],
				'Name'=>$list['name'],
				'Business'=>$list['business'],
				'Duty'=>$list['duty'],
				'Promise'=>$list['promise'],
				'Type'=>'1',
				'Telphone'=>$list['phone'],
				'Photo'=>$pathurl,
				'PostTitle'=>$list['posttitle'],
				'Level'=>"$level",
				'SectionName'=>$list['SectionName'],
				'WindowName'=>$windowname,
				);
			echo json_encode($data,JSON_UNESCAPED_SLASHES);			
		}else{
			$data = ['error'=>'登陆未成功，请检查账号密码是否正确'];
			echo json_encode($data);	
		}
		return;
	}


	//选择窗口  用户id
	public function showuserinfo($id,$windowname){
		//转换中文编码
		$windowname = mb_convert_encoding($windowname,'UTF-8', 'gbk');
		$wid = Db::name('sys_window')->where("name='$windowname'")->value('id');
		if($wid){ 
			//判断窗口是否有用户
			$userid = Db::name('sys_window')->where('id',$wid)->value('workmanid');
			//如果该窗口有员工在线就将其强制下线并将员工在线状态改为下线
			if($userid!='0'){
				Db::name('sys_window')->where('id',$wid)->update(['workmanid'=>'0']);
				Db::name('sys_workman')->where('id',$userid)->update(['online'=>'0']);
			}
			//此窗口添加员工id
			if(Db::name('sys_window')->where('id',$wid)->update(['workmanid'=>$id])){
				//修改状态为5 登陆状态
				Db::name('sys_workman')->where('id',$id)->update(['online'=>'5']);
				Db::name('pj_device')->where('windowid',$wid)->update(['online'=>'5']);
				$data = ['type'=>'OK'];
			}else{
				$data = ['error'=>'窗口添加员工失败'];
			}
			Db::name('sys_workman')->where('id',$id)->update(['windowid'=>$wid]);
						
		}else{
			$data = ['error'=>'找不到'.$windowname.'窗口'];
		}

		echo json_encode($data);
		return;
	}

	//员工在线状态
	public function uptype($userid,$type){
		if(DB::name('sys_workman')->where('id',$userid)->update(['online'=>$type])){
			$wid = DB::name('sys_window')->where('workmanid',$userid)->value('id');
			Db::name('pj_device')->where('windowid',$wid)->update(['online'=>$type]);
			if($type=='0'){
				Db::name('sys_window')->where('id',$wid)->update(['workmanid'=>'0']);
				Db::name('pj_device')->where('windowid',$wid)->update(['online'=>'0']);
			}
			$data = ['type'=>'OK'];
		}else{
			$data = ['error'=>'修改失败'];
		}
		echo json_encode($data);
	}

	//查询员工所属部门的窗口id
	public function windowid($userid){
		$sid = Db::name('sys_workman')->where('id',$userid)->value('sectionid');

		$windowname = Db::name('sys_window')->field('name')->where("sectionid='$sid' and valid=1")->select();
		echo json_encode($windowname);
	}


}