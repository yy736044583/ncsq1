<?php
namespace app\wechat\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use app\wechat\Controller\Common;

class Business extends Common{
	// 首页
    public function index(){
     	
        return  $this->fetch();

    }
    //选择事项
    public function matter(){
     	
        return  $this->fetch();

    }
    //事项详情
    public function mattershow(){
     	
        return  $this->fetch();

    }
    // 乡镇政务中心
    public function govlist(){
     	
        return  $this->fetch();

    }
}