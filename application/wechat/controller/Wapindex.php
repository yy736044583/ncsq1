<?php
namespace app\wechat\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use app\wechat\Controller\Common;

class Wapindex extends Common{

    public function index(){
     	return  $this->fetch();
    }

}

