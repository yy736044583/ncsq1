<?php
namespace app\autotable2\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Model;
 /* 
    中心网站
 */
class Introduction extends \think\Controller{

   //意见 建议
    public function index(){
        $list = Db::name('sys_thiscenter')->where('id',1)->value('introduce');
       
        $this->assign('list',$list);
        return $this->fetch();
    }




}