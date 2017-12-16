<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;


class Video extends \think\Controller{

    public function index(){
    	$top = Db::name('sys_web')->where('name',1)->value('type');
    	if($top==1){
    		$list = Db::name('ds_banner')->where("top = 1 and type=2")->select();
    	}else{
    		$list = Db::name('ds_banner')->where("top = 1 and type=1")->select();
    	}
     	
     	// dump($list);die;
     	$this->assign('list',$list);
        return  $this->fetch();
    }

}