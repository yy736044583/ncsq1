<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/**
 * 评价设备管理
 * @action index 		设备列表
 * @action adddevice	添加设备
 * @action updevice 	更新设备
 * @action dldevice 	删除设备
 * @action showtype		更新状态
 * @action down 		定时关机
 */
class Device extends Common{
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
			$data = DB::name('pj_device')->where($map)->order('createtime desc')->paginate(12,false,['query'=>array('windowid'=>$windowid,'number'=>$number,'usestatus'=>$usestatus)]);
		}else{
			$data = DB::name('pj_device')->order('createtime desc')->paginate(12);
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

	/**
	 * 新增设备
	 */

	public function adddevice(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->check($data)){
				//查询其他评价器是否设置了该窗口
				$did = Db::name('pj_device')->where('windowid',$data['windowid'])->value('id');
				if($did){
					$this->error('该窗口已经配置，请选择其他窗口');
				}
				if(Db::name('pj_device')->insert($data)){
					$this->success('添加成功','device/index');	
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

	/**
	 * 更新数据
	 */

	public function updevice(){
		if(request()->isPost()){
			$data = input('post.');
			$lid = $data['id'];
			unset($data['id']);
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->check($data)){
				//查询其他评价器是否设置了该窗口
				$did = Db::name('pj_device')->where('windowid',$data['windowid'])->column('id');
				//如果其他窗口已经设置了该窗口则将其他窗口改为未设置
				if(!empty($did)){
					foreach ($did as $v) {
						Db::name('pj_device')->where('id',$v)->update(['windowid'=>'0']);
					}
					
				}				
				if(Db::name('pj_device')->where('id',$lid)->update($data)){
					
					$this->success('修改成功','device/index');	
				}else{
					$this->error('修改失败,请重试');
				}
			}else{
				$this->error($validate->getError());	
			}
		}
		$id = input('id');
		$list = DB::name('pj_device')->where('id',$id)->find();
		$window = DB::name('sys_window')->where('valid',1)->select();
		$this->assign('win',$window);
		$this->assign('list',$list);
		return $this->fetch();
	}

	/**
	 * 删除设备
	 */

	public function dldevice(){
		$id = input('id');
		if(Db::name('pj_device')->where('id',$id)->delete()){
			$this->success('删除成功','device/index');
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 查询设备状态
	 */
	public function showtype(){
		
		$list = Db::name('pj_device')->field('id,lastlogin,number')->where('usestatus',1)->select();
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

	/**
	 * 定时关机
	 */
	public function down(){
		if(request()->isPost()){
			$data = input('post.');

			//将id赋值后删除data数组里面的id避免更新id
			$tid = $data['id'];
			unset($data['id']);

			//判断是否关机是否为空 如果为空就设置为0
			if(empty($data['down'])){
				$data['down'] = '0';
			}
			if(Db::name('pj_device')->where('id',$tid)->update($data)){
				$this->success('设置成功','device/index');
			}else{
				$this->error('设置失败');
			}
		}
		$id = input('id');
		$list = Db::name('pj_device')->field('id,downtimehour,downtimemin,down')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
}