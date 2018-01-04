<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
use think\Validate;
/*
**中心信息
 */
class Thiscenter extends Common{

	public function index(){
		$this->auth();
		$list = Db::name('sys_thiscenter')->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	//提交信息进行添加和修改
	public function upthis(){
		$data = input('post.');
		//判断周一到周末 如果未选就赋值0
		for ($i=1; $i<=7 ; $i++) { 
			if(empty($data["workday_".$i])){
				$data['workday_'.$i] = 0;
			}
		}
		$validate = validate('Thiscenter');
		if($validate->check($data)){
			//如果有数据就直接修改
			if(Db::name('sys_thiscenter')->find()){
				if(Db::name('sys_thiscenter')->where('id',1)->update($data)){
					$this->success('修改成功','thiscenter/index');
				}else{
					$this->error('修改失败');	
				}					

			}else{
				if(Db::name('sys_thiscenter')->insert($data)){
					$this->success('添加成功','thiscenter/index');
				}else{
					$this->error('添加失败');
				}
			}
		}else{
			$this->error($validate->getError());
		}
		
	}
}