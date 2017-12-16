<?php
namespace app\appraise\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
//新闻公告
class Index extends \think\Controller{

	public function index(){		
		$info = Db::name('sys_news')->where("level = 0")->limit(0,14)->select();//政务中心
		$data = Db::name('sys_news')->where("level = 1")->limit(0,14)->select();//部门
		$this->assign('info',$info);
		$this->assign('data',$data);
		return $this->fetch();
	}
	// 公告新闻
	public function announcement(){
		$page = input("page") + 1;//页码
		$count = input("count");//数量
		$id = input("id");//部门id
		$info = Db::name('sys_news')->where("level = 2")->page(($page-1)*$count,$count)->select();
		echo json_encode($info);
	}
	// 部门新闻
	public function news(){
		$page = input("page") + 1;//页码
		$count = input("count");//数量
		$id = input("id");//部门id
		$info = Db::name('sys_news')->where("level = 1")->limit(($page-1)*$count,$count)->select();
		echo json_encode($info);
	}

	public function show(){
		$id = input('id');
		$data = Db::name('sys_news')->where("id = $id")->find();
		$this->assign('data',$data);
		return $this->fetch();
	}
}