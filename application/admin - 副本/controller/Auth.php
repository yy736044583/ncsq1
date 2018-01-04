<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
//权限管理

class Auth extends Common{

	//权限列表
	public function index(){
		$this->auth();
		$list = Db::name('sys_auth')->paginate(12);
		$page = $list->render();
		$this->assign('page', $page);
		$this->assign('list',$list);
		return $this->fetch();
	}
	//添加权限
	public function addauth(){
		if(request()->isPost()){
			$data = input('post.');
			if(DB::name('sys_auth')->insert($data)){
				$this->success('添加成功','auth/index');
			}else{
				$this->error('添加失败');
			}
		}
		$id = input('id');
		if($id){
			$level = Db::name('sys_auth')->where('au_id',$id)->value('au_level')+1;
			$this->assign('parent',$id);
			$this->assign('level',$level);
		}
		return $this->fetch();
	}
	//编辑权限
	public function upauth(){
		if(request()->isPost()){
			$data = input('post.');
			if(empty($data['au_type'])){
				$data['au_type'] = '0';
			}
			$id = $data['au_id'];
			unset($data['au_id']);
			if(DB::name('sys_auth')->where('au_id',$id)->update($data)){
				//Db::name('sys_position')->where("po_id!=''")->setField('au_id','');
				$this->success('修改成功','auth/index');
			}else{
				$this->error('修改失败');
			}
		}	
		$id = input('id');
		$list = DB::name('sys_auth')->where('au_id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
	//删除权限
	public function dlauth(){
		$id = input('id');
		if(Db::name('sys_auth')->where('au_id',$id)->delete()){
			$this->success('删除成功','auth/index');
		}else{
			$this->error('删除失败');
		}
	}

/*----------------------------------------------------------------------------------------------------*/

	//角色列表
	public function position(){
		$this->auth();
		$list = Db::name('sys_position')->paginate(15);
		$page = $list->render();
		$this->assign('page', $page);
		$this->assign('list',$list);
		return $this->fetch();
	}

	//添加角色权限
	public function addposition(){
		if(request()->isPost()){
			$data = input('post.');
			$au_id = $data['au_id'];
			$name = $data['po_name'];
			$data1['au_id'] = implode(',',$au_id);
			$data1['po_name'] = $name;
			if(Db::name('sys_position')->insert($data1)){
				$this->success('添加成功','auth/position');
			}else{
				$this->error('添加失败');
			}
		}
		$authA = DB::name('sys_auth')->where("au_level=0 and au_type=1")->select();
        $authB = DB::name('sys_auth')->where("au_level=1 and au_type=1")->select();
        $authC = DB::name('sys_auth')->where("au_level=2 and au_type=1")->select();
        $this->assign('authA',$authA);
        $this->assign('authB',$authB);
        $this->assign('authC',$authC);
		return $this->fetch();
	}

	//修改角色权限
	public function upposition(){
		if(request()->isPost()){
			$data = input('post.');
			$au_id = $data['au_id'];
			$name = $data['po_name'];
			$poid = $data['po_id'];
			$data1['au_id'] = implode(',',$au_id);
			$data1['po_name'] = $name;
			if(Db::name('sys_position')->where('po_id',$poid)->update($data1)){
				$this->success('修改成功','auth/position');
			}else{
				$this->error('修改失败');
			}
		}
		$id = input('id');
		$list = Db::name('sys_position')->where('po_id',$id)->find();

		$authA = DB::name('sys_auth')->where("au_level=0 and au_type=1")->select();
        $authB = DB::name('sys_auth')->where("au_level=1 and au_type=1")->select();
        $authC = DB::name('sys_auth')->where("au_level=2 and au_type=1")->select();
        $this->assign('list',$list);
        $this->assign('authA',$authA);
        $this->assign('authB',$authB);
        $this->assign('authC',$authC);
		return $this->fetch();
	}

	//删除角色
	public function dlposition(){
		$id = input('id');
		if($id!=''){
			if(Db::name('sys_position')->where('po_id',$id)->delete()){
				$this->success('删除成功','auth/position');
			}else{
				$this->error('删除失败');
			}
		}
	}

/*----------------------------------------------------------------------------------------------------*/
	//角色分配
	public function fposition(){
		$this->auth();
		$list = Db::name('sys_user')->select();
		foreach ($list as $k => $v) {
			$poid = $v['po_id'];
			if($poid){
				$list[$k]['po_name'] = DB::name('sys_position')->where('po_id',$poid)->value('po_name');
			}
		}
		$this->assign('list',$list);
		return $this->fetch();
	}
	//修改角色
	public function upuserP(){
		if(request()->isPost()){
			$data = input('post.');
			$uid = $data['id'];
			unset($data['id']);
			if(Db::name('sys_user')->where('id',$uid)->update($data)){
				$this->success('修改成功','auth/fposition');
			}else{
				$this->error('修改失败');
			}
		}
		$uid = input('id');
		$user = Db::name('sys_user')->where('id',$uid)->find();
		$list = Db::name('sys_position')->field('po_id,po_name')->select();
		$this->assign('list',$list);
		$this->assign('user',$user);
		return $this->fetch();
	}

/*----------------------------------------------------------------------------------------------------*/
	//用户列表
	public function user(){
		$this->auth();
		//查询
		$username = input('username');
		if($username!=''){
			$map['username|name'] = ['like',"%$username%"];
			if(session('username')!='admin'){
				$map['username'] = ['neq','admin'];
			}
			$list = Db::name('sys_user')->where($map)->paginate(12,false,['query'=>array('username'=>$username)]);
		}else{
			if(session('username')!='admin'){
				$map1['username'] = ['neq','admin'];
			}
			$list = Db::name('sys_user')->where("username!='admin'")->paginate(12);
		}
		
		$page = $list->render();
		$this->assign('uname', $username);
		$this->assign('page', $page);
		$this->assign('list',$list);
		return $this->fetch();
	}
	//添加用户
	public function adduser(){
		if(request()->isPost()){
			$data = input('post.');
			//不能添加admin用户
			if(input('username')=='admin'){
				$this->error('非法的用户名');
			}
			//不重复添加相同的用户名到数据库
			if(Db::name('sys_user')->where('username',input('username'))->find()){
				$this->error('该用户已存在,请不要重复添加!');
			}
			if(input('userpass')==''){
				unset($data['userpass']);
			}
			//实例化验证器
			$validate = validate('User');
			if($validate->check($data)){
				$data['userpass'] = md5(input('post.userpass'));
				$data['createtime'] = date('Y-m-d H:i:s',time());
				if(Db::name('sys_user')->insert($data)){
					$this->success('添加成功','auth/user');
				}else{
					$this->error('添加失败');
				}			
			}else{
				$this->error($validate->getError());
			}			
		}
		return $this->fetch();
	}
	//编辑用户
	public function upuser(){
		if(request()->isPost()){
			$data = input('post.');
			if(input('userpass')==''){
				unset($data['userpass']);
			}else{
				$data['userpass'] = md5(input('post.userpass'));
			}
			$u_id = input('post.id');
			unset($data['id']);
			unset($data['username']);
			if($u_id!=''){
				if(Db::name('sys_user')->where('id',$u_id)->update($data)){
					$this->success('修改成功','auth/user');
				}else{
					$this->error('修改失败');
				}
			}			
		}
		$id = input('id');
		$list = Db::name('sys_user')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	//删除用户
	public function dluser(){
		$u_id = input('id');
		if($u_id!=''){
			if(Db::name('sys_user')->where('id',$u_id)->delete()){
				$this->success('删除成功','auth/user') ;
			}else{
				$this->error('删除失败');
			}
		}
	}


}