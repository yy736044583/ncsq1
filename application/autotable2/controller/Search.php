<?php
namespace app\autotable2\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Model;
 /* 
    网上办事 
    办事部门    index
    事项标题    matter
    办事内容    matterinfo
 */
class Search extends \think\Controller{

	public function index(){
        return  $this->fetch();
    }


	public function errorhtml(){
        return  $this->fetch();
    }
}