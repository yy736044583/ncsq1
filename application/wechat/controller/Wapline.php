<?php
namespace app\wechat\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use app\wechat\Controller\Common;

class Wapline extends Common{

    public function index(){
     	//获取Openid
        $Openid = Session('openid');
        if(!empty($Openid)){
     	$mix = date("Y-m-d",time())." "."00:00:00";
     	$max = date("Y-m-d",time())." "."23:59:59";
     	$sql = Db::name('wy_peopleinfo')
     	->alias('a')
     	->where("a.openid = '$Openid'")
     	->where("w.starttime > '$mix' and w.endtime <= '$max' and w.status = 1")
     	->join('wy_orderrecord w','a.Id = w.peopleid')
     	->join('sys_business z','w.businessid  = z.id')
     	->order('w.id desc')
     	->select();
        $day = date('d',time());
     	// dump($sql);
         }else{
            $sql = null;
         }
        $this->assign('day',$day);
     	$this->assign('info',$sql);
        return  $this->fetch();

    }
}