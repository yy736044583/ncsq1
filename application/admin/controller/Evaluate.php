<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
//评价管理  报表查询


class Evaluate extends Common{

	//评价浏览
	public function index(){
		$this->auth();
		//如果有条件则进行查询
		$name = input('name');
		$start = input('start');
		$end = input('end');
		$evaluatelevel = input('evaluatelevel');
		$deviceid = input('deviceid');
		if($name!=''|$start!=''|$end!=''|$evaluatelevel!=''|$deviceid!=''){
			$map = '';
			if($name!=''){//根据姓名查询员工id
				$workmanid = Db::name('sys_workman')->where('name','like',"%$name%")->column('id');
				$map['workmanid'] = array('in',$workmanid);
			}
			//判断时间
			if($start!=''&$end==''){
				$map['evaluatetime'] = array('gt',$start);
			}elseif($start==''&$end!=''){
				$map['evaluatetime'] = array('lt',$end);
			}elseif($start!=''&$end!=''){
				$map['evaluatetime'] = array('between time',array($start,$end));
			}
			//评价
			if($evaluatelevel!=''){
				$map['evaluatelevel'] = $evaluatelevel;
			}
			//设备
			if($deviceid!=''){
				$map['deviceid'] = $deviceid;
			}
			$map['evaluatestatus'] = '1';
			$data = Db::name('pj_evaluate')->where($map)->order('id desc')->paginate(12,false,['query'=>array('name'=>$name,'start'=>$start,'end'=>$end,'evaluatelevel'=>$evaluatelevel,'deviceid'=>$deviceid)]);
		}else{
			$data = Db::name('pj_evaluate')->where("evaluatestatus='1'")->order('id desc')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			//员工姓名
			$list[$k]['workmanid']	 = Db::name('sys_workman')->where('id',$v['workmanid'])->value('name');
			$list[$k]['deviceid']	 = Db::name('pj_device')->where('id',$v['deviceid'])->value('number');
			//评价
			switch ($v['evaluatelevel']) {
				case '0':$list[$k]['evaluatelevel'] = '态度不好';break;
				case '1':$list[$k]['evaluatelevel'] = '业务不熟';break;
				case '2':$list[$k]['evaluatelevel'] = '时间太长';break;
				case '3':$list[$k]['evaluatelevel'] = '有待改进';break;
				case '4':$list[$k]['evaluatelevel'] = '基本满意';break;
				case '5':$list[$k]['evaluatelevel'] = '非常满意';break;	
				default:break;
			}
		}
		$page = $data->render();
		$device = Db::name('pj_device')->where('usestatus',1)->select();

		$this->assign('start',$start);
		$this->assign('end',$end);
		$this->assign('name',$name);
		$this->assign('deviceid',$deviceid);
		$this->assign('evaluatelevel',$evaluatelevel);

		$this->assign('dec',$device);
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}


/* -------------------------------------------------------------------------------------- */	
	//评价统计
	public function count(){
		$this->auth();
		$data = Db::name('sys_workman')->field('id,name')->paginate(12);
		$list = $data->all();
		foreach ($list as $k => $v) {
			$map1['workmanid'] = $v['id'];
			$map1['evaluatelevel'] = '0';
			$map1['evaluatestatus'] = '1';
			$list[$k]['evaluatelevel1']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '1';
			$list[$k]['evaluatelevel2']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '2';
			$list[$k]['evaluatelevel3']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '3';
			$list[$k]['evaluatelevel4']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '4';
			$list[$k]['evaluatelevel5']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '5';
			$list[$k]['evaluatelevel6']	= Db::name('pj_evaluate')->where($map1)->count();
			//小计
			$list[$k]['sum'] = 	$list[$k]['evaluatelevel1']+	$list[$k]['evaluatelevel2']+$list[$k]['evaluatelevel3']+$list[$k]['evaluatelevel4']+$list[$k]['evaluatelevel5']+$list[$k]['evaluatelevel6'];
			//满意率
			$many = $list[$k]['evaluatelevel6']+$list[$k]['evaluatelevel5'];
			if($many!=0){
				$list[$k]['many'] = round(($many/$list[$k]['sum'])*100,2);
			}else{
				$list[$k]['many'] = '0.00';
			}
			
		}		
		$page = $data->render();
		$device = Db::name('pj_device')->where('usestatus',1)->select();

		$this->assign('dec',$device);
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}

	//评价统计查询页面
	 public function count1(){
	 	$this->auth();
		$name = input('name');
		$start = input('start');
		$end = input('end');
		$deviceid = input('deviceid');
		//判断是否为空并作为条件
		$map = '';
		if($name!=''){//根据姓名查询员工id
			$map['name'] = array('like',"%$name%");
		}
		$data = Db::name('sys_workman')->where($map)->paginate(12);
		$list = $data->all();
		foreach ($list as $k => $v) {
			//判断时间
			if($start!=''&$end==''){
				$map1['evaluatetime'] = array('gt',$start);
			}elseif($start==''&$end!=''){
				$map1['evaluatetime'] = array('lt',$end);
			}elseif($start!=''&$end!=''){
				$map1['evaluatetime'] = array('between time',array($start,$end));
			}
			//设备id
			if($deviceid!=''){
				$map1['deviceid'] = $deviceid;
			}
			$map1['evaluatestatus'] = '1';
			// dump($map1);die;
			$map1['workmanid'] = $v['id'];
			$map1['evaluatelevel'] = '0';
			$list[$k]['evaluatelevel1']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '1';
			$list[$k]['evaluatelevel2']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '2';
			$list[$k]['evaluatelevel3']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '3';
			$list[$k]['evaluatelevel4']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '4';
			$list[$k]['evaluatelevel5']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '5';
			$list[$k]['evaluatelevel6']	= Db::name('pj_evaluate')->where($map1)->count();
			//小计
			$list[$k]['sum'] = 	$list[$k]['evaluatelevel1']+$list[$k]['evaluatelevel2']+$list[$k]['evaluatelevel3']+$list[$k]['evaluatelevel4']+$list[$k]['evaluatelevel5']+$list[$k]['evaluatelevel6'];
			//满意率
			$many = $list[$k]['evaluatelevel6']+$list[$k]['evaluatelevel5'];
			if($many!=0){
				$list[$k]['many'] = round(($many/$list[$k]['sum']),2);
			}else{
				$list[$k]['many'] = '0.00';
			}
			
		}		
		$page = $data->render();
		$device = Db::name('pj_device')->where('usestatus',1)->select();

		$this->assign('start',$start);
		$this->assign('end',$end);
		$this->assign('name',$name);
		$this->assign('deviceid',$deviceid);

		$this->assign('dec',$device);
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();

	 }


	//评价截图
	public function picture(){
		$this->auth();
		$sectionid = input('sectionid');
		$name = input('name');
		$start = input('start');
		$end = input('end');

		$section = Db::name('sys_section')->where('valid',1)->select();
		if($sectionid||$name||$start||$end){
			$data = Db::name('pj_evaluate')->where("evaluatetype='1' and evaluatestatus='1'")->order('id desc')->paginate(8,false,['query'=>['sectionid'=>$sectionid,'name'=>$name,'start'=>$start,'end'=>$end]]);
		}else{
			$data = Db::name('pj_evaluate')->where("evaluatetype='1' and evaluatestatus='1'")->order('id desc')->paginate(8);
		}
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			//员工姓名
			$list[$k]['workmanid']	 = Db::name('sys_workman')->where('id',$v['workmanid'])->value('name');
		}
		$page = $data->render();
		$this->assign('sec',$section);
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('sectionid',$sectionid);
		$this->assign('name',$name);
		$this->assign('start',$start);
		$this->assign('end',$end);
		return $this->fetch();
	}

}