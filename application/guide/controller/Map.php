<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

//地图查询 
class Map extends \think\Controller{

    public function index(){
        return  $this->fetch();
    }

}