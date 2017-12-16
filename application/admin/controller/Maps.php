<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/*
**部门坐标管理 
 */
class Maps extends Common{

	public function index(){
		$this->auth();
		//接收部门名称
		$name = input('name');
		if($name!=''){
			//如果部门名称有值 查询部门的id
			$sectionid = Db::name('sys_section')->where("name like '%$name%'")->column('id');
			//将部门id合并成一个字符串
			$sectionid = implode(',',$sectionid);

			$map['sectionid'] = ['in',$sectionid];
			//根据部门id查询部门坐标表的具体信息  并代入参数
			$data = Db::name('ds_maps')->where($map)->paginate(12,false,['query'=>array('name'=>$name)]);
			$this->assign('name',$name);
		}else{
			$data = Db::name('ds_maps')->paginate(12);
		}

		$list = $data->all();
		foreach ($list as $k => $v) {
			//遍历数据 将部门id转换成部门名称
			$list[$k]['name'] = Db::name('sys_section')->where('id',$v['sectionid'])->value('name');
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}

	//添加部门坐标
	public function addmap(){
		if(request()->isPost()){
			//接收数据
			$data = input('post.');
			//添加创建时间
			$data['createtime'] = date('Y-m-d H:i:s',time());
			//添加数据到数据库
			if(Db::name('ds_maps')->insert($data)){
				$this->success('添加成功','maps/index');
			}else{
				$this->error('添加失败');
			}
		}
		//部门显示
		$section = Db::name('sys_section')->where('valid',1)->select();
		$this->assign('sec',$section);
		return $this->fetch();
	}

	//修改部门坐标
	public function upmap(){
		if(request()->isPost()){
			//接收数据
			$data = input('post.');
			//将id单独列出来  再将数组里的id删除 避免更新出错
			$mid = $data['id'];
			unset($data['id']);
			//更新数据
			if(Db::name('ds_maps')->where('id',$mid)->update($data)){
				$this->success('修改成功','maps/index');
			}else{
				$this->error('修改失败');
			}
		}
		$id = input('id');
		//查询该部门的坐标信息
		$list = Db::name('ds_maps')->where('id',$id)->find();
		//部门显示
		$section = Db::name('sys_section')->where('valid',1)->select();
		$this->assign('sec',$section);
		$this->assign('list',$list);
		return $this->fetch();
	}

	//删除部门坐标
	public function dlmap(){
		$id = input('id');
		if(Db::name('ds_maps')->where('id',$id)->delete()){
			$this->success('删除成功','maps/index');
		}else{
			$this->error('删除失败');
		}
	}
}