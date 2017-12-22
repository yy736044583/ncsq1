<?php 
namespace app\hardware\controller;
use think\Db;

class hardwarecall{
	/**
	 * [index 硬件呼叫器接口入口]
	 * @param  [type] [呼叫类型 01叫号 02重呼 03置后 04弃号]
	 * @param  [devicenum] [设备编号]
	 */
	public function index(){
		$type = input('type');
		$devicenum = input('devicenum');//中心编号
		$number = input('number'); //呼叫器编号
		if(empty($type)||empty($devicenum)||empty($number)){
			echo json_encode(['data'=>array(),'code'=>'404','message'=>'缺少必要参数']);
			return;
		}

		switch ($type) {
			//叫号
			case '01':
				$this->call($devicenum,$number);
				break;
			//重呼
			case '02':
				$this->againcall($devicenum,$number);
				break;
			//置后
			case '03':
				$this->aftercall($devicenum,$number);
				break;
			//弃号
			case '04':
				$this->giveup($devicenum,$number);
				break;
			default:
				break;
		}
	}

	/**
	 * [call 叫号]
	 * @param  [string] $devicenum [硬件中心编号]
	 * @param  [string] $number    [呼叫器编号]
	 * @return [type]            [返回数据]
	 */
	public function call($devicenum,$number){
		//查询设备编号是否存在 不存在就创建
		if(!$call = Db::name('ph_hardwarecall')->field('id,usestatus,windowid')->where('number',$number)->find()){
			$data1['number'] = $number;
			$data1['createtime'] = date('Y-m-d H:i:s',time());
			Db::name('ph_hardwarecall')->insert($data1);
		}
		//查询该设备是否在使用
		if($call['usestatus']!='1'){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该设备未使用']);
			return;
		}

		//呼叫器id
		$did = $call['id'];

		//更新最后登陆时间
		$time = date('Y-m-d H:i:s',time());
		Db::name('ph_hardwarecall')->where('number',$number)->update(['lastlogin'=>$time]);

		//窗口id
		$wid = $call['windowid'];
		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'未设置窗口']);
			return;
		}

		$today = date('Ymd',time());
		//查询当天此窗口最近呼叫时间  如果跟当前时间对比小于3直接返回
		$min = $this->timedown($wid,$time);
		// dump($min);die;
		if($min<=2){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'请不要频繁操作']);
			return;
		}
	
		//更新当前之前的排号状态为完成
		Db::name('ph_queue')->where("today='$today' and windowid='$wid'")->whereIn('style','1,2,3')->update(['status'=>'3','style'=>'4','endtime'=>$time]);

		//根据中心编号查询id  
		$id = Db::name('ph_hardwarecenter')->where('number',$devicenum)->value('id');

		//根据id查询业务范围
		$busid = Db::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');

		//根据条件查询下一个排号id
		$map['businessid'] = ['in',$busid];
		$map['today'] = $today;
		$map['style'] = '0';	

		//根据条件查询业务范围内下一个排号id
		$que = Db::name('ph_queue')->field('id,businessid,flownum')->where($map)->order('priority,taketime')->find();
		$qid = $que['id'];
		if(!$qid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'当前无排队']);
			return;
		}

		//根据窗口id查询窗口屏的设备id
		$lid = Db::name('ph_hardwareled')->where('windowid',$wid)->value('id');
		if(empty($lid)){
			$lid = Db::name('ph_led')->where('windowid',$wid)->value('id');
		}

		//根据窗口id查询集中显示屏的设备id集
		$clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');

		//更新下一个叫号信息
		if(Db::name('ph_queue')->where('id',$qid)
		->update(
			['windowid'=>$wid,
			'style'=>'1',
			'calltime'=>$time,
			'callid'=>$did,
			'ledid'=>$lid,
			])){

			//等候人数-1
			Db::name('sys_business')->where('id',$que['businessid'])->setDec('waitcount');
			// 查询等候人数 
			$waitcount = Db::name('sys_business')->where('id',$que['businessid'])->value('waitcount');
			// 如果窗口屏id不为空则添加到队列
			if($lid){
				//添加队列到排号队列表中
				//窗口设备
				Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$qid]);
			}
			// 如果集中屏id不为空则添加到队列
			if(!empty($clid)){
				//集中显示屏设备
				foreach ($clid as  $v) {
					Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$qid]);
				}				
			}
			

			echo json_encode(['data'=>['id'=>$qid,'waitcount'=>$waitcount,'flownum'=>$que['flownum']],'code'=>'200','message'=>'成功']);
			return;
		}
	}

	/**
	 * [againcall 重呼]
	 * @param  [string] $devicenum [硬件中心编号]
	 * @param  [string] $number    [呼叫器编号]
	 * @return [type]            [返回数据]
	 */	
	public function againcall($devicenum,$number){
		//根据设备编号查询窗口id和设备id
		$call = Db::name('ph_hardwarecall')->field('id,windowid')->where('number',$number)->find();

		$wid = $call['windowid'];

		$time = date('Y-m-d H:i:s',time());
		$today = date('Ymd',time());

		//查询当前窗口呼叫的排号id
		$que = Db::name('ph_queue')->where("today='$today' and windowid='$wid'")->whereIn('style','1,2,3')->field('id,flownum,businessid')->order('id desc')->find();
		$qid = $que['id'];

		//根据窗口id查询窗口屏的设备id
		$lid = Db::name('ph_hardwareled')->where('windowid',$wid)->value('id');
		if(empty($lid)){
			$lid = Db::name('ph_led')->where('windowid',$wid)->value('id');
		}
		//根据窗口id查询集中显示屏的设备id集
		$clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');

		// 查询当天此窗口最近呼叫时间  如果跟当前时间对比小于3直接返回
		$min = $this->timedown($wid,$time);
		// dump($min);die;
		if($min<=2){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'请不要频繁操作']);
			return;
		}
		// 更新叫号时间
		Db::name('ph_queue')->where('id',$qid)->update(['calltime'=>$time]);
		// 查询等候人数 
			$waitcount = Db::name('sys_business')->where('id',$que['businessid'])->value('waitcount');
			// Db::name('ph_queue')->where('id',$qid)->update(['style'=>'1'])
			// 如果窗口屏id不为空则添加到队列
			if($lid){
				//添加队列到排号队列表中
				//窗口设备
				Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$qid]);			
			}
			// 如果集中屏id不为空则添加到队列
			if(!empty($clid)){
				//集中显示屏设备
				foreach ($clid as  $v) {
					Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$qid]);
				}				
			}
			echo json_encode(['data'=>['id'=>$qid,'status'=>'1','waitcount'=>$waitcount,'flownum'=>$que['flownum']],'code'=>'200','message'=>'成功']);
			return;

	}

	/**
	 * [aftercall 置后]
	 * @param  [string] $devicenum [硬件中心编号]
	 * @param  [string] $number    [呼叫器编号]
	 * @return [type]            [返回数据]
	 */
	public function aftercall($devicenum,$number){
		//根据设备编号查询窗口id和设备id
		$call = Db::name('ph_hardwarecall')->field('id,windowid')->where('number',$number)->find();

		$wid = $call['windowid'];
		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'窗口未设置']);
			return;
		}
		//呼叫器id
		$did = $call['id'];

		$time = date('Y-m-d H:i:s',time());
		//查询当天此窗口最近呼叫时间  如果跟当前时间对比小于3直接返回
		$min = $this->timedown($wid,$time);
		// echo $min;
		if($min<=2){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'请不要频繁操作']);
			return;
		}

		$today = date('Ymd',time());

		//查询当前窗口呼叫的排号id
		$qid = Db::name('ph_queue')->where("today='$today' and windowid='$wid'")->order('id desc')->value('id');

		//根据中心编号查询id  
		$id = Db::name('ph_hardwarecenter')->where('number',$devicenum)->value('id');

		//根据id查询业务范围
		$busid = Db::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');

		//根据条件查询下一个排号id
		$map['businessid'] = ['in',$busid];
		$map['today'] = $today;
		$map['style'] = '0';	

		//根据条件查询业务范围内下一个排号id
		$que = Db::name('ph_queue')->field('id,businessid,flownum')->where($map)->order('priority,taketime')->find();
		$nextid = $que['id'];
		//如果没有查找到排号id则返回
		if(!$nextid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'当前无排队']);
			return;
		}			

		//根据窗口id查询窗口屏的设备id
		$lid = Db::name('ph_hardwareled')->where('windowid',$wid)->value('id');
		if(empty($lid)){
			$lid = Db::name('ph_led')->where('windowid',$wid)->value('id');
		}
		//根据窗口id查询集中显示屏的设备id集
		$clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');	


		//呼叫下一个排号前 将后置的排号id叫号状态改为0
		if(Db::name('ph_queue')->where('id',$qid)->update(['style'=>'0','status'=>'0','windowid'=>'0','workmanid'=>'0'])){
			Db::name('ph_deviceqid')->where('qid',$qid)->delete();
		}else{
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败']);
			return;
		}

		//更新下一个叫号信息
		if(Db::name('ph_queue')->where('id',$nextid)
		->update(
			['windowid'=>$wid,
			'style'=>'1',
			'calltime'=>$time,
			'callid'=>$did,
			'ledid'=>$lid,
			])){
			// 如果窗口屏id不为空则添加到队列
			if($lid){
				//添加队列到排号队列表中
				//窗口设备
				Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$qid]);			
			}
			// 如果集中屏id不为空则添加到队列
			if(!empty($clid)){
				//集中显示屏设备
				foreach ($clid as  $v) {
					Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$qid]);
				}				
			}
			// 查询等候人数 
			$waitcount = Db::name('sys_business')->where('id',$que['businessid'])->value('waitcount');
			echo json_encode(['data'=>['id'=>$nextid,'waitcount'=>$waitcount,'flownum'=>$que['flownum']],'code'=>'200','message'=>'成功']);
			return;
		}

	}

	/**
	 * [giveup 弃号]
	 * @param  [string] $devicenum [硬件中心编号]
	 * @param  [string] $number    [呼叫器编号]
	 * @return [type]            [返回数据]
	 */
	public function giveup($devicenum,$number){
		//根据设备编号查询窗口id和设备id
		$call = Db::name('ph_hardwarecall')->field('id,windowid')->where('number',$number)->find();

		$wid = $call['windowid'];
		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'窗口未设置']);
			return;
		}

		$today = date('Ymd',time());

		//查询当前窗口呼叫的排号id
		$id = Db::name('ph_queue')->where("today='$today' and windowid='$wid'")->order('id desc')->value('id');	

		//根据id更新排号状态  将其改为弃号和叫号完成
		if(Db::name('ph_queue')->where('id',$id)->update(['status'=>'2','style'=>'4'])){

			//删除队列表中对应排号id的数据
			Db::name('ph_deviceqid')->where('qid',$id)->delete();

			echo json_encode(['data'=>['id'=>$id,'status'=>'1'],'code'=>'200','message'=>'成功']);
			return;
		}else{
			echo json_encode(['data'=>['id'=>$id,'status'=>'0'],'code'=>'400','message'=>'失败']);
			return;
		}
	}


	/**
	 * [timedown 查询上一个呼叫时间 计算时间差]
	 * @param  [type] $wid [窗口id]
	 * @return [type]      [description]
	 */
	public function timedown($wid,$time){
		$today = date('Ymd',time());
		//查询当天此窗口最近呼叫时间  如果跟当前时间对比小于3直接返回
		$calltime = Db::name('ph_queue')->where("today='$today' and windowid='$wid'")->order('id desc')->value('calltime');

		if($calltime){
			$min = strtotime($time)-strtotime($calltime);
			return $min;
		}else{
			return 4;
		}
	}
}