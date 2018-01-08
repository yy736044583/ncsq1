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
    
}