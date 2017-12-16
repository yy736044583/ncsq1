<?php 
namespace app\serverinter\controller;
use think\Db;
/**
 * 通讯服务器心跳接口
 * 呼叫器/窗口屏/集中屏/评价器 心跳
 * 收到数据后回传队列id 进行删除
 */
class Communication{
	/**
	 * 根据方法名跳转
	 * @param [string] $[action] [方法名]
	 * @param [string] $[devicenum] [设备编号]
	 */
	public function index(){
		$action = input('action');
		//根据方法名跳转到各个方法
		switch ($action) {
			// 呼叫器心跳
			case 'callheart':
				$this->callheart(input('callnumber'));
				break;
			// 窗口屏心跳
			case 'ledheart':
				$this->ledheart(input('lednumber'));
				break;
			// 评价器心跳
			case 'deviceheart':
				$this->deviceheart(input('devicenumber'));
				break;	
			// 集中屏心跳	
			case 'cledheart':
				$this->cledheart(input('clednumber'));
				break;	
			// 删除队列	更新排队状态
			case 'deletequeue':
				$this->deletequeue(input('id'),input('qid'));
				break;			
			default:
				echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到']);
				return;
				break;
		}
	}

	
	public function callheart($callnumber){
		if(!empty($callnumber)){
			//将设备编号转换成数组
			$callnumber = explode(',',$callnumber);
			$time = date('Y-m-d H:i:s',time());
			// 循环数组 判断该设备是否已存在数据库 如果不在就添加 否则更新最新登陆时间
			foreach ($callnumber as $k => $v) {
				if($id = Db::name('ph_call')->where('number',$v)->value('id')){
					Db::name('ph_call')->where('id',$id)->update(['lastlogin'=>$time]);
				}else{
					Db::name('ph_call')->insert(['number'=>$v,'createtime'=>$time]);
				}
			}			
		}


		// 查询最新呼叫队列数据
		$data = Db::name('ph_cachequeue')->where("callnumber!=''")->field('id,callnumber,qid,flownum,count,online')->order('time desc')->select();
		
		echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功']);
		return;
	}

	public function ledheart($lednumber){
		if(!empty($lednumber)){
			//将设备编号转换成数组
			$lednumber = explode(',',$lednumber);
			$time = date('Y-m-d H:i:s',time());
			// 循环数组 判断该设备是否已存在数据库 如果不在就添加 否则更新最新登陆时间
			foreach ($lednumber as $k => $v) {
				if($id = Db::name('ph_led')->where('number',$v)->value('id')){
					Db::name('ph_led')->where('id',$id)->update(['lastlogin'=>$time]);
				}else{
					Db::name('ph_led')->insert(['number'=>$v,'createtime'=>$time]);
				}
			}			
		}


		$data = Db::name('ph_cachequeue')->where("lednumber!=''")->field('id,lednumber,qid,flownum,online,count')->order('time desc')->select();
		echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功']);
		return;
	}

	public function deviceheart($devicenumber){
		if(!empty($devicenumber)){
			//将设备编号转换成数组
			$devicenumber = explode(',',$devicenumber);
			$time = date('Y-m-d H:i:s',time());
			// 循环数组 判断该设备是否已存在数据库 如果不在就添加 否则更新最新登陆时间
			foreach ($devicenumber as $k => $v) {
				if($device = Db::name('pj_device')->where('number',$v)->field('id,downtimehour,downtimemin')->find()){

					// 检测定时关机状态 如果需要关机则存入队列表
					$this->downtime($v,$device['downtimehour'],$device['downtimemin']);

					Db::name('pj_device')->where('id',$device['id'])->update(['lastlogin'=>$time]);
				}else{
					Db::name('pj_device')->insert(['number'=>$v,'createtime'=>$time]);
				}
			}			
		}

		$data = Db::name('ph_cachequeue')->where("devicenumber!=''")->field('id,devicenumber,eid,online')->order('time desc')->select();
		echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功']);
		return;
	}

	public function cledheart($clednumber){
		if(!empty($clednumber)){
			//将设备编号转换成数组
			$clednumber = explode(',',$clednumber);
			$time = date('Y-m-d H:i:s',time());
			// 循环数组 判断该设备是否已存在数据库 如果不在就添加 否则更新最新登陆时间
			foreach ($clednumber as $k => $v) {
				if($id = Db::name('ph_cled')->where('number',$v)->value('id')){
					Db::name('ph_cled')->where('id',$id)->update(['lastlogin'=>$time]);
				}else{
					Db::name('ph_cled')->insert(['number'=>$v,'createtime'=>$time]);
				}
			}			
		}


		$data = Db::name('ph_cachequeue')->where("clednumber!=''")->field('id,clednumber,windowflownum,flownum,qid')->order('time desc')->select();
		echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功']);
	}

	// 删除队列 id队列id集合  字符串拼接
	// 更新排号表中的叫号状态
	public function deletequeue($id,$qid){
		if(Db::name('ph_cachequeue')->delete($id)){
			Db::name('ph_queue')->whereIn('id',$qid)->setField(['style'=>2,'status'=>1]);
			echo 'ok';
			return;
		}else{
			echo 'error';
			return; 
		}
	}

	// 比较定时关机时间是否等于当前时间  如果是则在队列中插入关机命令
	public function downtime($number,$downtimehour,$downtimemin){
		$time = date('Y-m-d H:i:s',time());
		//根据查询出来的定时关机时间跟当前时间比较判断是否关机
		$fortime = date('H:i',time());//当前的小时和分钟
		$downtime = $downtimehour.':'.$downtimemin;//定时关机的时间
		$down =  $downtime==$fortime?'1':'0';
		if($down=='1'){
			$data['devicenumber'] = $number;
			$data['time'] = $time;
			$data['online'] = 1;
			Db::name('ph_cachequeue')->insert($data);	
		}
	}
}