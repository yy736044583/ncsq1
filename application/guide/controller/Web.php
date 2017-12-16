<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;

class Web extends \think\Controller{

    public function index(){
        return  $this->fetch();
    }

}