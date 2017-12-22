<?php
namespace app\autotable2\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Model;
 /* 
     
    公告公告    index
    公告详情    show

 */
class Notice extends \think\Controller{

    public function index(){
    	$data = Db::name('sys_news')->order('id desc')->paginate(7,true);
    	$list = $data->all();
    	foreach ($list as $k => $v) {
    		$list[$k]['createtime'] = date('Y-m-d',strtotime($v['createtime']));
    	}
    	$page = $data->render();
    	$this->assign('list', $list);
		$this->assign('page', $page);
        return  $this->fetch();
    }
    public function show(){
    	$id = input('id');
 		$data = Db::name('sys_news')->where('id',$id)->find();
 		$data['createtime'] = date('Y-m-d',strtotime($data['createtime']));

 		$this->assign('data', $data);
        return  $this->fetch();
    }
}