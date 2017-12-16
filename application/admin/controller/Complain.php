<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/*
**投诉管理 
 */
class Complain extends Common{

	public function index(){
		$this->auth();
		$workman = input('workman');
		$number = input('number');
		//根据条件查询
		if($workman!=''|$number!=''){
			$map = '';
			if($workman!=''){
				$map1['name'] = ['like',"%$workman%"];
				$workmanid = Db::name('sys_workman')->where($map1)->column('id');
				$map['workmanid'] = ['in',$workmanid];
			}
			if($number!=''){
				$map['deviceid'] = Db::name('pj_device')->where('number',$number)->value('id');
			}
			$data = DB::name('pj_complain')->where($map)->order('uptime desc')->paginate(12,false,['query'=>array('number'=>$number,'workman'=>$workman)]);
		}else{
			//如果没有查询条件查询所有
			$data = DB::name('pj_complain')->order('uptime desc')->paginate(12);
		}
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['workmanid'] = Db::name('sys_workman')->where('id',$v['workmanid'])->value('name');
			$list[$k]['deviceid'] = Db::name('pj_device')->where('id',$v['deviceid'])->value('number');
			//查询投诉附件
			$list[$k]['file'] = Db::name('pj_complainfile')->where('complainid',$v['id'])->value('url');
		}
		$page = $data->render();
		$this->assign('workman',$workman);
		$this->assign('number',$number);

		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}


	//投诉详情
	public function showcom(){
		$id = input('id');
		$list = Db::name('pj_complain')->where('id',$id)->find();
		$list['workman'] = Db::name('sys_workman')->where('id',$list['workmanid'])->value('name');
		$list['device'] = Db::name('pj_device')->where('id',$list['deviceid'])->value('number');
		$this->assign('list',$list);
		return $this->fetch();
	}
}