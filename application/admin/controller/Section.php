<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/*
**部门管理 
 */
class Section extends Common{

	public function index(){
		$this->auth();
		//查询
		$name = input('name');
		if($name!=''){
			$map['name'] = ['like',"%$name%"];
			$list = DB::name('sys_section')->where('valid',1)->where($map)->order('name')->paginate(12,false,['query'=>array('name'=>$name)]);
		}else{
			$list = DB::name('sys_section')->where('valid',1)->order('name')->paginate(12);
		}
		
		$page = $list->render();
		$this->assign('name',$name);
		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}
	public function addsec(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$data['top'] = empty($data['top'])?'0':$data['top'];
			$data['valid'] = '1';
			if($data['name']==''){
				$this->error('部门名称不能为空');return;
			}
			if(Db::name('sys_section')->insert($data)){
				$this->success('添加成功','section/index');	
			}else{
				$this->error('添加失败,请重试');
			}
		}
		return $this->fetch();
	}
	public function upsec(){
		if(request()->isPost()){
			$data = input('post.');
			$data['top'] = empty($data['top'])?'0':$data['top'];
			$sid = $data['id'];
			unset($data['id']);
			if($data['name']==''){
				$this->error('部门名称不能为空');return;
			}
			if(Db::name('sys_section')->where('id',$sid)->update($data)){
				$this->success('修改成功','section/index');	
			}else{
				$this->error('修改失败,请重试');
			}
		}
		$id = input('id');
		$list = DB::name('sys_section')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
	//删除部门
	public function dlsec(){
		$id = input('id');
		if(Db::name('sys_section')->where('id',$id)->delete()){
			$this->success('删除成功','section/index');
		}else{
			$this->error('删除失败');
		}
	}
}