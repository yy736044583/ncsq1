<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
//预约记录
class Orderrecord extends Common{

	//当前预约记录
	public function index(){
		$this->auth();
		//如果有post提交则进行查询
		$bid = input('business');
		$name = input('name');
		if($bid!=''||$name!=''){
			if($bid!=''){
				$map['businessid'] = $bid;
			}
			if($name!=''){
				$uid = Db::name('wy_peopleinfo')->where('name',$name)->column('id');
				if($uid){
					$map['peopleid'] = array('in',$uid);
				}
			}
			$time = date('Y-m-d H:i:s',time());
			$map['endtime'] = array('gt',$time);
			$list = DB('wy_orderrecord')->where($map)->paginate(12,false,['query'=>array('business'=>$bid,'name'=>$name)]);	
		}else{

			//获取当前时间 如果预约结束时间比当前时间大则显示
			$time = date('Y-m-d H:i:s',time());
			$map1['endtime'] = array('gt',$time);	
			$list = DB('wy_orderrecord')->where($map1)->paginate(12);			
		}

		//查询业务名称
		$business = Db::name('sys_business')->field('name,id')->where('Valid',1)->select();

		// 因为是分页  获取当前对象的数据 方便进行赋值
		$data = $list->all();
		foreach ($data as $k => $v) {
			//业务名称
			$data[$k]['businessid'] = Db::name('sys_business ')->where('id',$v['businessid'])->value('name');

			//用户姓名
			$data[$k]['peopleid'] = Db::name('wy_peopleinfo ')->where('id',$v['peopleid'])->value('name');
			switch ($v['status']) {
				case '0':$data[$k]['status'] = '已预约';break;
				case '1':$data[$k]['status'] = '已取号';break;
				case '2':$data[$k]['status'] = '超时未取号';break;
				case '3':$data[$k]['status'] = '预约取消';break;
				default:break;
			}
		}
		$page = $list->render();
		$this->assign('bid',$bid);
		$this->assign('name',$name);

		$this->assign('page',$page);
		$this->assign('bus',$business);
		$this->assign('list',$data);
		return $this->fetch();
	}

	//历史预约记录
	public function history(){
		$this->auth();
		//如果有post提交则进行查询
		$bid = input('business');
		$name = input('name');
		if($bid!=''||$name!=''){
			$bid = input('business');
			$name = input('name');
			if($bid!=''){
				$map['businessid'] = $bid;
			}
			if($name!=''){
				$uid = Db::name('wy_peopleinfo')->where('name',$name)->column('id');
				if($uid){
					$map['peopleid'] = array('in',$uid);
				}
			}
			$time = date('Y-m-d H:i:s',time());
			$map['endtime'] = array('lt',$time);
			$list = DB('wy_orderrecord')->where($map)->paginate(12,false,['query'=>array('business'=>$bid,'name'=>$name)]);	
		}else{

			//获取当前时间 如果预约结束时间比当前时间小则显示
			$time = date('Y-m-d H:i:s',time());
			$map1['endtime'] = array('lt',$time);
			$list = DB('wy_orderrecord')->where($map1)->paginate(12);			
		}

		//查询业务名称
		$business = Db::name('sys_business')->field('name,id')->where('valid',1)->select();

		// 因为是分页  获取当前对象的数据 方便进行赋值
		$data = $list->all();
		foreach ($data as $k => $v) {
			//业务名称
			$data[$k]['businessid'] = Db::name('sys_business ')->where('id',$v['businessid'])->value('name');

			//用户姓名
			$data[$k]['peopleid'] = Db::name('wy_peopleinfo ')->where('id',$v['peopleid'])->value('name');
			switch ($v['status']) {
				case '0':$data[$k]['status'] = '已预约';break;
				case '1':$data[$k]['status'] = '已取号';break;
				case '2':$data[$k]['status'] = '超时未取号';break;
				case '3':$data[$k]['status'] = '预约取消';break;
				default:break;
			}
		}
		$page = $list->render();
		$this->assign('bid',$bid);
		$this->assign('name',$name);
		
		$this->assign('page',$page);
		$this->assign('bus',$business);
		$this->assign('list',$data);
		return $this->fetch();
	}
}