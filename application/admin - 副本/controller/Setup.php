<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/*
**预约设置 
 */
class Setup extends Common{
	public function index(){
		if(request()->isPost()){
			$data = input('post.');
			if(empty($data['allow'])){
				$data['allow'] = '0';
			}
			$setup = Db::name('wy_setup')->find();
			if($setup){
				if(Db::name('wy_setup')->where('id',1)->update($data)){
					$this->success('提交成功','Setup/index');	
				}else{
					$this->error('提交失败,请重试');
				}
			}else{
				// if($data['maxpeopledefault']=='')
				// 	$data['maxpeopledefault']='15';
				if(Db::name('wy_setup')->insert($data)){
					$this->success('提交成功','Setup/index');
				}else{
					$this->error('提交失败,请重试');
				}
			}
		}
		$list =  Db::name('wy_setup')->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
}