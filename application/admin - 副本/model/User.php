<?php 
namespace app\admin\model;
use think\Model;
use think\Validate;
use think\Db;

class User extends Model{

	public function __construct(){
		$user = model('User');
	}
	//添加数据
	public function insert($data){
		$user = model('User');
		$result = $user->validate('User')->saveAll($data);
		if(false===$result){
			echo $user->validate('User')->getError();
		}else{
			echo 'success';
		}		
	}

	//查询所有数据
	public function select(){
		$list = Db::name('User')->select();
		return $list;
	}

	//查询一条数据
	public function one($id){
		$user = model('User');
		$list = $user::get($id);
		return $list;
	}

	//更新数据
	public function edit($id,$data){
		$user->save($data,['u_id'=>$id]);
	}

}