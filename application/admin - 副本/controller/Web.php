<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;

//中心网站管理

class Web extends Common{

	public function index(){
		$this->auth();
		if(request()->isPost()){
			$id = input('id');
			$url = input('url');
			if($id){
				if(Db::name('sys_web')->where('id',$id)->update(['weburl'=>$url])){
					$this->success('提交成功','web/index');
				}else{
					$this->error('提交失败');
				}
			}
		}
		$pj = Db::name('sys_web')->where('name=0')->find();
		$ds = Db::name('sys_web')->where('name=1')->find();
		$this->assign('pj',$pj);
		$this->assign('ds',$ds);
		return $this->fetch();
	}
}