<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/*
**	时段管理
 */
class Intervaltime extends Common{

	public function index(){
		$this->auth();
		$list = Db::name('wy_intervaltime')->where('valid',1)->select();
		$this->assign('list',$list);
		return $this->fetch();
	}

	//添加时段
	public function addintervaltime(){
		//如果有post传值则是form表单提交数据  进行添加
		if(request()->isPost()){
			$shour = input('shour');
			$smin  = input('smin');
			$ehour = input('ehour');
			$emin  = input('emin');
			//拼接开始时段和结束时段
			$data['starttime'] = $shour.':'.$smin;
			$data['endtime'] = $ehour.':'.$emin;
			$data['valid'] = '1';
			$data['maxpeopledefault'] = input('maxpeopledefault');
			if(Db::name('wy_intervaltime')->insert($data)){
				$this->success('添加成功','Intervaltime/index');
			}else{
				$this->error('添加失败');
			}
		}
		return $this->fetch();
	}
	//修改时段
	public function upintervaltime(){
		//如果有post传值则是form表单提交数据  进行修改
		if(request()->isPost()){
			$shour = input('shour');
			$smin  = input('smin');
			$ehour = input('ehour');
			$emin  = input('emin');
			$id = input('id');
			//拼接开始时段和结束时段
			$data['starttime'] = $shour.':'.$smin;
			$data['endtime'] = $ehour.':'.$emin;
			$data['maxpeopledefault'] = input('maxpeopledefault');
		
			if(Db::name('wy_intervaltime')->where('id',$id)->update($data)){
				$this->success('修改成功','Intervaltime/index');
			}else{
				$this->error('修改失败');
			}
		}
		$id = input('id');
		$list = DB::name('wy_intervaltime')->where('id',$id)->find();
		$stime = explode(':',$list['starttime']);
		$list['shour'] = $stime['0'];
		$list['smin'] = $stime['1'];
		$etime = explode(':',$list['endtime']);
		$list['ehour'] = $etime['0'];
		$list['emin'] = $etime['1'];
		$this->assign('list',$list);
		return $this->fetch();
	}

	//删除时段 将该时段在表中设置为不显示
	public function dltime(){
		$id = input('id');
		if($id!=''){
			if(Db::name('wy_intervaltime')->where('id',$id)->update(['valid'=>'0'])){
				$this->success('删除成功','Intervaltime/index');
			}else{
				$this->error('删除失败');
			}
		}
	}
/*-----------------------------------------------------------------------------*/
	//时段人数
	public function people(){
		$this->auth();
		$data = Db::name('wy_maxpeople')->order('days desc')->paginate(12);
		$list = $data->all();
		foreach ($list as $k => $v) {
			$inter = Db::name('wy_intervaltime')->where('id',$v['intervaltimeid'])->find();

			$list[$k]['intervaltime'] = $inter['starttime'].'-'.$inter['endtime'];
			$list[$k]['business'] = Db::name('sys_business')->where('id',$v['businessid'])->value('name');
		}
		$page = $data->render();
		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}

	public function addpeople(){
		if(request()->isPost()){
			$data = input('post.');
			if(Db::name('wy_maxpeople')->insert($data)){
				$this->success('添加成功','intervaltime/people');
			}else{
				$this->error('添加失败');
			}
		}
		//查询业务名称
		$business = Db::name('sys_business')->field('name,id')->where('valid',1)->select();
		//查询时段
		$inter = Db::name('wy_intervaltime')->field('starttime,endtime,id')->where('valid',1)->select();
		//查询当前时间后一周  调用days方法
		$this->days();
		$this->assign('bus',$business);
		$this->assign('inter',$inter);
		return $this->fetch();
	}

	public function uppeople(){
		if(request()->isPost()){
			$data = input('post.');
			$mid = input('id');
			unset($data['id']);
			if(Db::name('wy_maxpeople')->where('id',$mid)->update($data)){
				$this->success('修改成功','intervaltime/people');
			}else{
				$this->error('修改失败');
			}
		}
		$id = input('id');

		//查询业务名称
		$business = Db::name('sys_business')->field('name,id')->where('valid',1)->select();
		//查询时段
		$inter = Db::name('wy_intervaltime')->field('starttime,endtime,id')->where('valid',1)->select();
		$list = Db::name('wy_maxpeople')->where('id',$id)->find();
		$this->assign('list',$list);
		//查询当前时间后一周  调用days方法
		$this->days();
		$this->assign('bus',$business);
		$this->assign('inter',$inter);
		return $this->fetch();
	}

	public function dlpeople(){
		$id = input('id');
		if($id!=''){
			if(Db::name('wy_maxpeople')->where('id',$id)->delete()){
				$this->success('删除成功','intervaltime/people');
			}else{
				$this->error('删除失败');
			}
		}
	}

	//查询未来五个工作日
	public function days(){
		$time = time();
		$week = array();
		for ($i=1; $i <=13; $i++) { 
			//循环 获取之后13天的日期
			$date = date('Y-m-d',strtotime("+$i"."day",$time));
			//将日期换成星期 判断在系统设置中是否有效
			$w = date('w',strtotime($date));
			//如果是星期日则换成7
			$w==0?$w=7:$w=$w;
			$where = 'workday_'.$w;
			
			//判断系统设置表中星期几是否有效
			if(Db::name('sys_thiscenter')->where("$where",1)->select()){
				//判断假日表中有效的数据不等于此日期
				if(!Db::name('sys_holiday')->where("day='$date' and valid=1 and workorholiday=0")->find()){
					$week[$i]  = $date;
				}
			}
			//如果是工作日就添加到数组
			if(Db::name('sys_holiday')->where("day='$date' and valid=1 and workorholiday=1")->find()){
				$week[$i]  = $date;
			}
			//如果日期大于5天退出循环
			if(count($week)>=5){
				break;
			}
		}
		$this->assign('week',$week);
	}
}