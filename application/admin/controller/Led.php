<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/*
**LED管理 
 */
class Led extends Common{
	public function index(){
		$this->auth();
		//查询条件
		$windowid = input('windowid');
		$number = input('number');
		$usestatus = input('usestatus');
		if($usestatus!=''|$number!=''|$windowid!=''){
			if($usestatus!=''){
				$map['usestatus'] = $usestatus;
			}
			if($number!=''){
				$map['number'] = $number;
			}
			if($windowid!=''){
				$map['windowid'] = $windowid;
			}
			$data = DB::name('ph_led')->where($map)->order('createtime desc')->paginate(12,false,['query'=>array('windowid'=>$windowid,'number'=>$number,'usestatus'=>$usestatus)]);
		}else{
			$data = DB::name('ph_led')->order('createtime desc')->paginate(12);
		}
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			switch ($v['usestatus']) {
				case '0':$list[$k]['usestatus'] = '未使用';break;
				case '1':$list[$k]['usestatus'] = '使用中';break;
				case '2':$list[$k]['usestatus'] = '已作废';break;	
				default:break;
			}
			switch ($v['runtype']) {
				case '0':$list[$k]['runtype'] = '未运行';break;
				case '1':$list[$k]['runtype'] = '运行中';break;
				case '2':$list[$k]['runtype'] = '已关机';break;	
				default:break;
			}
			$list[$k]['windowid'] = Db::name('sys_window')->where('id',$v['windowid'])->value('name');
		}
		$page = $data->render();
		$window = Db::name('sys_window')->where('valid',1)->select();
		$this->assign('windowid',$windowid);
		$this->assign('number',$number);
		$this->assign('usestatus',$usestatus);

		$this->assign('window',$window);
		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}
	//新增
	public function addled(){
		if(request()->isPost()){
			$data = input('post.');
			// 检测是否窗口已占用
			$this->requestwindow($data['windowid']);

			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->check($data)){
				if(Db::name('ph_led')->insert($data)){
					$this->success('添加成功','Led/index');	
				}else{
					$this->error('添加失败,请重试');
				}				
			}else{
				$this->error($validate->getError());	
			}
		}
		$window = DB::name('sys_window')->where('valid',1)->select();
		$this->assign('win',$window);
		return $this->fetch();
	}
	//更新数据
	public function upled(){
		if(request()->isPost()){
			$data = input('post.');
			$lid = $data['id'];
			unset($data['id']);
			// 检测是否窗口已占用
			$this->requestwindow($data['windowid']);

			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->check($data)){
				if(Db::name('ph_led')->where('id',$lid)->update($data)){
					$this->success('修改成功','Led/index');	
				}else{
					$this->error('修改失败,请重试');
				}
			}else{
				$this->error($validate->getError());	
			}
		}
		$id = input('id');
		$list = DB::name('ph_led')->where('id',$id)->find();
		$window = DB::name('sys_window')->where('valid',1)->select();
		$this->assign('win',$window);
		$this->assign('list',$list);
		return $this->fetch();
	}
	//删除部门
	public function dlled(){
		$id = input('id');
		if(Db::name('ph_led')->where('id',$id)->delete()){
			$this->success('删除成功','Led/index');
		}else{
			$this->error('删除失败');
		}
	}
	
	//查询设备状态
	public function showtype(){
		
		$list = Db::name('ph_led')->field('id,lastlogin,number')->where('usestatus',1)->select();
		$nowtime = time();
		$data = array();
		foreach ($list as $k => $v) {
			$intime = strtotime($v['lastlogin']);
			// 计算当前时间和数据库中的时间相差几分钟
			$new = intval(date('i',$nowtime-$intime));
			$data[$k]['number'] = $v['number'];
			if($new>=5){
				$data[$k]['type'] =  '未运行';
			}else{
				$data[$k]['type'] = '运行中';
			}			
		}
		$this->assign('list',$data);
		return $this->fetch();
	}

	public function requestwindow($wid){
		if(empty($wid)){
			$this->error('窗口需要选择');
		}
		$find = Db::name('ph_led')->where('windowid',$wid)->value('number');
		if($find){
			$this->error($find.'设备已配置该窗口,请重新配置');
		}
	}

}