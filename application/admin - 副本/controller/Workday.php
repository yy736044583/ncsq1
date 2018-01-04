<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
//节假日管理

class Workday extends Common{

	public function index(){
		$this->auth();
		$year = input('year');
		if($year!=''){
			$map['day'] = ['like',"%$year%"];
			$map['valid'] = '1';
			$list = DB::name('sys_holiday')->where($map)->paginate(12,false,['query'=>array('year'=>$year)]);
		}else{
			//默认显示当年的数据
			$year = date('Y',time());
			$map['day'] = ['like',"%$year%"];
			$map['valid'] = '1';
			$list = DB::name('sys_holiday')->where($map)->paginate(12);
		}
		
		$yeartime =  DB::name('sys_holiday')->where('valid',1)->column('day');
		$years = '';
		//将数据库中所有日期查询出来  取出年份
		foreach ($yeartime as $k => $v) {
			$years[$k] = substr($v,0,4);
		}
		if($years!=''){
			//年份去重
			$years = array_unique($years);			
		}
		$page = $list->render();
		$this->assign('year',$year);
		$this->assign('list',$list);
		$this->assign('years',$years);
		$this->assign('page',$page);
		return $this->fetch();
	}

	//添加节假日、
	public function addworkday(){
		if(request()->isPost()){
			$data = input('post.');
			$data['valid'] = '1';
			if(Db::name('sys_holiday')->insert($data)){
				$this->success('添加成功','workday/index');
			}else{
				$this->error('添加失败');
			}
		}
		return $this->fetch();
	}

	//修改节假日
	public function upworkday(){
		if(request()->isPost()){
			$data = input('post.');
			$id = input('id');
			unset($data['id']);
			if(Db::name('sys_holiday')->where('id',$id)->update($data)){
				$this->success('修改成功','workday/index');
			}else{
				$this->error('修改失败');
			}
		}	
		$id = input('id');
		$list = Db::name('sys_holiday')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	//删除节假日
	public function dlworkday(){
		$id = input('id');
		if($id!=''){
			if(Db::name('sys_holiday')->where('id',$id)->update(['valid'=>'0'])){
				$this->success('删除成功','workday/index');
			}else{
				$this->error('删除失败');
			}
		}
	}
}