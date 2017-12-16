<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
//排号报表查询

class Queue extends Common{
	
	public function index(){
		$this->auth();
		$type = input('type');
		$businessid = input('businessid');
		// 根据type 值判断是查询什么时候的数据 1当天数据 2一周数据 3一月数据 4当年数据
		$time = $this->timesource($type);
		//条件为大于选择时间
		$map['today'] = ['gt',$time];
		
		if($businessid!=''){
			$map['businessid'] = $businessid;
			$data = Db::name('ph_queue')->where($map)->order('taketime desc')->paginate(12,false,['query'=>array('businessid'=>$businessid,'type'=>$type)]);
		}else{
			$data = Db::name('ph_queue')->where($map)->order('taketime desc')->paginate(12,false,['query'=>array('type'=>$type)]);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['business'] = Db::name('sys_business')->where('id',$v['businessid'])->value('name');
			switch ($v['status']) {
				case '0':$list[$k]['status'] = '等待中'; break;
				case '1':$list[$k]['status'] = '成功叫号'; break;
				case '2':$list[$k]['status'] = '弃号'; break;
				case '3':$list[$k]['status'] = '办理成功'; break;
				case '4':$list[$k]['status'] = '成功评价'; break;	
				default:break;	
			}
		}
		$page = $data->render();

		$bus = Db::name('sys_business')->where('valid',1)->select();
		$this->assign('bus', $bus);
		//页面上显示查询条件
		$this->assign('businessid', $businessid);

		$this->assign('page', $page);
		$this->assign('type', $type);
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 等候时长
	public function waittime(){
		$this->auth();
		$type = input('type');
		$businessid = input('businessid');
		// 根据type 值判断是查询什么时候的数据 1当天数据 2一周数据 3一月数据 4当年数据
		$time = $this->timesource($type);
		//条件为大于选择时间  
		$map['today'] = ['gt',$time];
		$map['style'] = 4;
		if($businessid!=''){
			$map['businessid'] = $businessid;
			$data = Db::name('ph_queue')->where($map)->field('id,taketime,calltime,businessid,flownum')->order('taketime desc')->paginate(12,false,['query'=>array('businessid'=>$businessid,'type'=>$type)]);
			$data1 = Db::name('ph_queue')->where($map)->field('id,taketime,calltime,businessid,flownum')->order('taketime desc')->select();
		}else{
			$data = Db::name('ph_queue')->where($map)->field('id,taketime,calltime,businessid,flownum')->order('taketime desc')->paginate(12,false,['query'=>array('type'=>$type)]);
			$data1 = Db::name('ph_queue')->where($map)->field('id,taketime,calltime,businessid,flownum')->order('taketime desc')->select();
		}
		$list = $data->all();
		$max = 0;
		$min = 30;

		foreach ($list as $k => $v) {
			$list[$k]['business'] = Db::name('sys_business')->where('id',$v['businessid'])->value('name');
			$list[$k]['waittime'] = number_format((strtotime($v['calltime'])-strtotime($v['taketime']))/60,2,'.','');
		}
		$sum = 0;
		$count = count($data1);
		foreach ($data1 as $k => $v) {
			$waittime = number_format((strtotime($v['calltime'])-strtotime($v['taketime']))/60,2,'.','');
			$sum += $waittime;
			$max = max($waittime,$max);
			$min = min($waittime,$min);
		}
		$prev = $sum == 0?0:number_format($sum/$count,2,'.','');
		$min = $min==30?0:$min;
		$page = $data->render();

		$bus = Db::name('sys_business')->where('valid',1)->select();
		$this->assign('bus', $bus);
		//页面上显示查询条件
		$this->assign('businessid', $businessid);

		$this->assign('page', $page);
		$this->assign('min', $min);
		$this->assign('prev', $prev);
		$this->assign('max', $max);
		$this->assign('type', $type);
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 办理时长
	public function handletime(){
		$this->auth();
		$type = input('type');
		$businessid = input('businessid');
		// 根据type 值判断是查询什么时候的数据 1当天数据 2一周数据 3一月数据 4当年数据
		$time = $this->timesource($type);
		//条件为大于选择时间  
		$map['today'] = ['gt',$time];
		$map['status'] = 3;
		if($businessid!=''){
			$map['businessid'] = $businessid;
			$data = Db::name('ph_queue')->where($map)->field('id,taketime,endtime,businessid,flownum,today')->order('taketime desc')->paginate(12,false,['query'=>array('businessid'=>$businessid,'type'=>$type)]);
			//查询所有  求总数和平均
			$data1 = Db::name('ph_queue')->where($map)->field('id,taketime,endtime,businessid,flownum')->select();
		}else{
			$data = Db::name('ph_queue')->where($map)->field('id,taketime,endtime,businessid,flownum,today')->order('taketime desc')->paginate(12,false,['query'=>array('type'=>$type)]);
			//查询所有  求总数和平均
			$data1 = Db::name('ph_queue')->where($map)->field('id,taketime,endtime,businessid,flownum')->select();
		}
		$list = $data->all();
		$max = 0;
		$min = 30;
		
		foreach ($list as $k => $v) {
			$list[$k]['business'] = Db::name('sys_business')->where('id',$v['businessid'])->value('name');
			$list[$k]['handletime'] = number_format((strtotime($v['endtime'])-strtotime($v['taketime']))/60,2,'.','');
		}
		$sum = 0;
		$count = count($data1);
		foreach ($data1 as $k => $v) {
			$waittime = number_format((strtotime($v['endtime'])-strtotime($v['taketime']))/60,2,'.','');
			$sum += $waittime;
			$max = max($waittime,$max);
			$min = min($waittime,$min);
		}
		
		//平均时长 总时长/条数  如果总时长为0 则平均为0
		$prev = $sum == 0?0:number_format($sum/$count,2,'.','');
		//最小时长  如果最小时长为初始值30 则为0
		$min = $min==30?0:$min;

		$page = $data->render();

		$bus = Db::name('sys_business')->where('valid',1)->select();
		$this->assign('bus', $bus);
		//页面上显示查询条件
		$this->assign('businessid', $businessid);

		$this->assign('page', $page);
		$this->assign('min', $min);
		$this->assign('prev', $prev);
		$this->assign('max', $max);
		$this->assign('type', $type);
		$this->assign('list',$list);
		return $this->fetch();
	}





	//办件量
	public function dealnumber(){
		$this->auth();
		$name = input('name');
		$number = input('number');
		$type = input('type');
		if($name||$number){
			if($name){
				$map['name'] = ['like',"%$name%"];
				$this->assign('name',$name);
			}
			if($number){
				$map['number'] = $number;
				$this->assign('number',$number);
			}
			$data = Db::name('sys_workman')->where($map)->order('loginwindowid')->field('id,name,number')->paginate(12,false,['query'=>['type'=>$type,'number'=>$number,'name'=>$name]]);	
		}else{
			$data = Db::name('sys_workman')->order('loginwindowid')->field('id,name,number')->paginate(12,false,['query'=>['type'=>$type]]);
		}
		$workman = $data->all();

		// 根据type 值判断是查询什么时候的数据 1当天数据 2一周数据 3一月数据 4当年数据
		$time = $this->timesource($type);

		//统计一周内员工的办件量
		foreach ($workman as $k => $v) {
			$workman[$k]['count'] = Db::name('ph_queue')->where('today','gt',$time)
			->where('workmanid',$v['id'])->where('status',3)->count();
		}

		$page = $data->render();	
		$this->assign('workman',$workman);
		$this->assign('page',$page);
		$this->assign('type',$type);
		$this->assign('name',$name);
		$this->assign('number',$number);
		return $this->fetch();	
	}

	public function countview(){
		$this->auth();
		$businessid = input('businessid');
		$type = input('type');//时间段选择
		// 根据type 值判断是查询什么时候的数据 1当天数据 2一周数据 3一月数据 4当年数据
		$time = $this->timesource($type);
		// $time = substr($time,6,2);
		
		if($businessid){
			$data = Db::name('sys_business')->where('valid',1)->where('id',$businessid)->field('id,name')->paginate(12,false,['query'=>['businessid'=>$businessid]]);
		}else{
			$data = Db::name('sys_business')->where('valid',1)->field('id,name')->paginate(12);
		}

		//条件为大于1天前的日期  最近1天
		$map['today'] = $time;
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			$map['businessid'] = $v['id'];
			$map['today'] = ['egt',$time];
			//查询该业务的取号人数
			$allcount = Db::name('ph_queue')->where($map)->count();
			//查询该业务的办理人数
			$map['style'] = 4;
			$callcount = Db::name('ph_queue')->where($map)->count();
			//查询该业务的等待人数
			$map['style'] = 0;
			$waitcount = Db::name('ph_queue')->where($map)->count();
			unset($map['style']);
			$list[$k]['allcount']= $allcount;
			$list[$k]['callcount']= $callcount;
			$list[$k]['waitcount']= $waitcount;
		}
		

		$page = $data->render();

		$bus = Db::name('sys_business')->where('valid',1)->select();
		$this->assign('bus', $bus);
		//页面上显示查询条件
		$this->assign('businessid', $businessid);

		$this->assign('page', $page);
		$this->assign('type',$type);
		$this->assign('list',$list);		
		return $this->fetch();	
	}

	// 窗口办件量
	public function winnumber(){
		$this->auth();
		$sectionid = input('sectionid');//部门id
		$wid = input('wid');//窗口id
		$type = input('type');//时间段选择
		
		if($sectionid||$wid){
			if($sectionid){
				// 根据部门id 查询该部门下所有窗口id
				$wids = Db::name('sys_window')->where('sectionid',$sectionid)->column('id');
				$wids = !empty($wids)? implode(',',$wids) :0;
				$map['id'] = ['in',$wids];
			}
			if($wid){
				$map['id'] = $wid;
			}
			$data = Db::name('sys_window')->where($map)->order('fromnum')->field('id,fromnum,name')->paginate(12,false,array('query'=>['sectionid'=>$sectionid,'wid'=>$wid,'type'=>$type]));
			// 作为查询总办件量的数据
			$data1 = Db::name('sys_window')->where($map)->order('fromnum')->field('id,fromnum,name')->select();

		}else{
			$data = Db::name('sys_window')->order('fromnum')->field('id,name,fromnum')->paginate(12);
			$data1 = Db::name('sys_window')->order('fromnum')->field('id,name,fromnum')->select();
		}
		$list = $data->all();

		// 根据type 值判断是查询什么时候的数据 1当天数据 2一周数据 3一月数据 4当年数据
		$time = $this->timesource($type);
		$today = date('Ymd',time());	
		// 总办件量
		$sumcount = 0;

		// 将时间段作为查询作为条件查询总办件量
		foreach ($data1 as $k => $v) {
			$data1[$k]['count'] = Db::name('ph_queue')->where('today','>=',$time)
			->where('windowid',$v['id'])->where('status',3)->count();
			$sumcount += $data1[$k]['count'];
		}

		foreach ($list as $k => $v) {
			//办件量
			$list[$k]['count'] = Db::name('ph_queue')->where('today','>=',$time)
			->where('windowid',$v['id'])->where('status',3)->count();
			// 等待人数
			$busid = Db::name('sys_winbusiness')->where('windowid',$v['id'])->value('businessid');
			$list[$k]['waitcount'] = Db::name('ph_queue')->where('today','>=',$time)->where('style',0)->whereIn('businessid',$busid)->count();
			// 当前办件号
			$list[$k]['flownum'] = Db::name('ph_queue')->whereIn('style','1,2,3')->where('windowid',$v['id'])->where('today',$today)->value('flownum');

		}

		// 查询所有部门
		$sec = Db::name('sys_section')->where('valid',1)->select();
		// 查询所有窗口
		$window = Db::name('sys_window')->where('valid',1)->select();
		$page = $data->render();	
		$this->assign('list',$list);
		$this->assign('sec',$sec);
		$this->assign('window',$window);
		$this->assign('page',$page);
		$this->assign('sectionid',$sectionid);
		$this->assign('wid',$wid);
		$this->assign('sumcount',$sumcount);
		$this->assign('type',$type);
		return $this->fetch();
	}

	/**
	 * [timesource 根据时间查询数据 时间选择类型]
	 * @param  [type] $type [1当天数据 2一周数据 3一月数据 4当年数据]
	 * @return [type]       [description]
	 */
	public function timesource($type){
		switch ($type) {
			case '1':
				$time = date('Ymd',time());
				break;
			case '2':
				//7天前的时间
				$time = date('Ymd',strtotime('-7 days'));
				break;
			case '3':
				$time = date('Ymd',strtotime('-1 month'));
				break;
			case '4':
				$time = date('Y',time());
				$time = $time.'0101';
				break;
			case '5':
				$time = '';
				break;
			default:
				$time = date('Ymd',time());
				break;
		}
		return $time;
	}
}