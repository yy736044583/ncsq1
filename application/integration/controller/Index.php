<?php
namespace app\integration\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Index extends \think\Controller{

    public function index(){
    	$time = time();
		$this->assign('time',$time);
        return  $this->fetch();
    }
    // 打印复印
    public function prints(){
        return  $this->fetch();
    }
}