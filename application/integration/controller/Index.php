<?php
namespace app\integration\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Index extends \think\Controller{

    public function index(){
        return  $this->fetch();
    }
}