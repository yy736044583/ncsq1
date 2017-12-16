<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
//关于我们
class About extends \think\Controller{

    public function index(){
    	$info = Db::name('sys_thiscenter')->where('id',1)->field('summary,introduce')->find();
    	$this->assign('info',$info);
        return  $this->fetch();
    }

}