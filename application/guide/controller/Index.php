<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Index extends \think\Controller{

    public function index(){
     	$centerurl = DB::name('sys_web')->where('name',1)->value('weburl');
     	$this->assign('centerurl',$centerurl);
        return  $this->fetch();
    }




}

