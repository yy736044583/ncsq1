<?php
namespace app\integration\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Progress extends \think\Controller{
	// 办件进度查询
    public function index(){
        
        return  $this->fetch();
    }
    // 我要评价
    public function appraise(){
        
        return  $this->fetch();
    }
    //评价上传
	//object 评价对象  1人员 2窗口 3事项
    public  function  upevl(){
		$data = input('post.');
		$data['createtime'] = time();
		if(Db::name('gra_evaluate')->insert($data)){
			echo 1;
		}else{
			echo 2;
		}
    }
}