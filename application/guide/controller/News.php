<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
//新闻公告
class News extends \think\Controller{

    public function index(){
        $count = Db::name('sys_news')->count();
    	$info = Db::name('sys_news')->order('id desc')->paginate(16);
    	$page = $info->render();
        $this->assign('count',$count);
    	$this->assign('info', $info);
		$this->assign('page', $page);
        return  $this->fetch();
    }

    public function show(){
 		$id = input('get.pu_id');
 		$data = Db::name('sys_news')->where('id',$id)->select();
 		$this->assign('data', $data);
        return  $this->fetch();
    }

}