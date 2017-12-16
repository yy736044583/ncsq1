<?php
namespace app\synchronization\controller;
use think\Db;
use think\Request; 


//呼叫器接口
class Call{
	/**
	 * 根据方法名跳转
	 * @param [string] $[action] [方法名]
	 * @param [string] $[devicenum] [设备编号]
	 */
    public function index(){

		$callType = input('callType'); //状态 确定是叫号还是其他
		$node = input('node');	//分中心编号
		$fromnum = input('id');	//业务编号
		$number = input('number'); //排号编号
		$windowNumber = input('windowNumber');	//窗口编号
		$business = input('business'); //业务编号
		$online = input('online'); //暂离2 在线1
		$list = input('list');
		//如果设备编号为空则返回
		if(empty($callType)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误'],JSON_UNESCAPED_UNICODE);
			return ;
		}
		//根据方法名跳转到各个方法
		switch ($callType) {
			//叫号
			case '01':
				$this->call($windowNumber,$node,$callType,$business,$list);
				break;
			//重呼
			case '02':
				$this->againcall($windowNumber,$number,$node,$callType,$business,$list);
				break;
			//暂离
			case '03':
				$this->popped($windowNumber,$node,$callType,$online,$list);
				break;
			default:
				echo  json_encode(['data'=>array(),'code'=>'400','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
				return;
				break;
		}
    }

    /**
     * [call 叫号]
     * @param  [string] $callType       [业务类型]
     * @param  [string] $windowNumber [窗口编号]
     * @param  [string] $node        [分中心编号]
     * @return [array]               [返回数据集]
     */
	public function call($windowNumber,$node,$callType,$business,$list){

		if(empty($windowNumber)){
			echo   json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//根据窗口编码查询id和员工id
		$window = Db::name('sys_window')->field('id,workmanid')->where('fromnum',$windowNumber)->find();
		$wid = $window['id'];
		$userid = $window['workmanid'];
		
		//根据窗口id查询窗口屏的设备id
		$led = Db::name('ph_led')->where('windowid',$wid)->field('id,online')->find();
		if(empty($led)){
			$led = Db::name('ph_hardwareled')->where('windowid',$wid)->field('id,online')->find();
		}
		$lid = $led['id'];

		if(!$wid){
			echo  json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登陆'],JSON_UNESCAPED_UNICODE);
			return;
		}
		
		if($led['online']==2||$led['online']==3){
			echo  json_encode(['data'=>array(),'code'=>'400','message'=>'员工暂时离开'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//根据窗口id查询呼叫器id
		$did = Db::name('ph_call')->where('windowid',$wid)->value('id');
		if(!$did){
			$did = Db::name('ph_hardwarecall')->where('windowid',$wid)->value('id');
		}
		$did = empty($did)?0:$did;

		$time = date('Y-m-d H:i:s',time());
		$today = date('Ymd',time());

		//将该窗口当天之前的排号状态改为完成
		Db::name('ph_queue')->where("today='$today' and windowid='$wid'")->whereIn('style','1,2,3')->update(['status'=>'3','style'=>'4','endtime'=>$time]);

		
		//根据业务编号查询业务id
		$busid = Db::name('sys_business')->whereIn('id',$list)->field('fromnum,id')->select();
		// 获取每个业务的当前等待人数
		$listcount = $this->allbusinesscount($busid);

		// 根据业务编号查询业务id
		$businessid = Db::name('sys_business')->where('fromnum',$business)->value('id');
		$map['businessid'] = $businessid;
		
		$map['today'] = $today;
		$map['style'] = '0';
		//根据条件查询业务范围内下一个排号id
		$que = Db::name('ph_queue')->field('id,businessid,flownum')->where($map)->order('priority,taketime')->find();

		$qid = $que['id'];

		//$businessid = $que['businessid'];

		//如果没有查找到id则返回
		if(!$qid){
			echo  json_encode(['data'=>array(),'code'=>'400','message'=>'当前无排队'],JSON_UNESCAPED_UNICODE);
			return;
		}

		
		//根据窗口id查询集中显示屏的设备id集
		$clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');
		
		
		//更新下一个叫号信息
		if(Db::name('ph_queue')->where('id',$qid)
		->update(
			['windowid'=>$wid,
			'style'=>'1',
			'workmanid'=>$userid,
			'calltime'=>$time,
			'callid'=>$did,
			'ledid'=>$lid,
			])){

			//等候人数-1
			Db::name('sys_business')->where('id',$businessid)->setDec('waitcount');
			//添加队列到排号队列表中
			//窗口设备
			if($lid){
				Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$qid]);
			}
			if(!empty($clid)){
				//集中显示屏设备
				foreach ($clid as  $v) {
					Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$qid]);
				}				
			}
			echo json_encode(['data'=>['windowNumber'=>$windowNumber,'number'=>$que['flownum'],'numberid'=>$qid,'node'=>$node,'callType'=>$callType,'listcount'=>$listcount],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
		}
	}

	/**
	 * [againcall 重呼]
	 * @param  [string] $number [排号编号]
     * @param  [string] $callType       [业务类型]
     * @param  [string] $windowNumber [窗口编号]
     * @param  [string] $node        [分中心编号]
	 * @return [array] data  [返回数据]
	 */
	public function againcall($windowNumber,$number,$node,$callType,$business,$list){

		$wid = Db::name('sys_window')->where('fromnum',$windowNumber)->value('id');
		//根据窗口id查询窗口屏的设备id
		$led = Db::name('ph_led')->where('windowid',$wid)->field('id,online')->find();
		if(empty($led)){
			$led = Db::name('ph_hardwareled')->where('windowid',$wid)->field('id,online')->find();
		}
		$lid = $led['id'];
		if($led['online']==2||$led['online']==3){
			echo  json_encode(['data'=>array(),'code'=>'400','message'=>'员工暂时离开'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//根据窗口id查询呼叫器id
		$did = Db::name('ph_call')->where('windowid',$wid)->value('id');
		if(!$did){
			$did = Db::name('ph_hardwarecall')->where('windowid',$wid)->value('id');
		}
		//根据窗口查询排号id
		$today = date('Ymd',time());
		$que = Db::name('ph_queue')->where('windowid',$wid)
		->where('today',$today)->whereIn('style','1,2,3')->field('id,flownum')->find();
		$id = $que['id'];

		//根据业务编号查询业务id
		$busid = Db::name('sys_business')->whereIn('id',$list)->field('fromnum,id')->select();
		// 获取每个业务的当前等待人数
		$listcount = $this->allbusinesscount($busid);

		//如果传过来的排号编号跟查询出来的当前窗口编号不同 则进行选叫
		if($number!=$que['flownum']){
			// 选叫方法
			$flownum = $this->choicecall($wid,$number,$did);
			echo json_encode(['data'=>['windowNumber'=>$windowNumber,'number'=>$flownum,'node'=>$node,'callType'=>$callType,'listcount'=>$listcount],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//根据窗口id查询窗口屏的设备id
		// $lid = Db::name('ph_led')->where('windowid',$wid)->value('id');
		
		$time = date('Y-m-d H:i:s',time());
		//根据窗口id查询集中显示屏的设备id集
		$clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');
	

		if(Db::name('ph_queue')->where('id',$id)->update(['style'=>'1'])){
			//添加队列到排号队列表中
			//窗口设备
			if($lid){
				Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$id]);
			}
			if(!empty($clid)){
				//集中显示屏设备
				foreach ($clid as  $v) {
					Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$id]);
				}				
			}
			echo json_encode(['data'=>['windowNumber'=>$windowNumber,'number'=>$number,'node'=>$node,'callType'=>$callType,'listcount'=>$listcount],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
		}else{
			echo json_encode(['data'=>['windowNumber'=>$windowNumber,'number'=>$number,'node'=>$node,'listcount'=>$listcount],'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
			return;
		}
	}

	/**
	 * [choicecall 选叫]
	 * @param  [int] $wid [窗口id]
	 * @param  [string] $number        [排号编号]
	 * @return [int]   did    [设备id]
	 */
	public function choicecall($wid,$number,$did=0){
		if(empty($wid)||empty($number)||empty($number)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'窗口或者排号编号为空,设备编号'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//根据窗口查询员工id
		$userid = Db::name('sys_window')->where('id',$wid)->value('workmanid');

		$today = date('Ymd',time());
		$time = date('Y-m-d H:i:s',time());
		//如果查询到
		//更新当前之前的排号状态为完成
		Db::name('ph_queue')->where("today='$today' and windowid='$wid'")->whereIn('style','1,2,3')->update(['status'=>'3','style'=>'4','endtime'=>$time]);

		//根据条件查询业务范围内下一个排号id
		$id = Db::name('ph_queue')->where("today='$today' and flownum='$number'")->value('id');

		//根据窗口id查询窗口屏的设备id
		$lid = Db::name('ph_led')->where('windowid',$wid)->value('id');
		if(empty($lid)){
			$lid = Db::name('ph_hardwareled')->where('windowid',$wid)->value('id');
		}

		//根据窗口id查询集中显示屏的设备id集
		$clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');
		//查询该选叫号码的完成状态
		$status = Db::name('ph_queue')->where('id',$id)->value('status');
		
		//将选叫的排号id设置为叫号状态
		if(Db::name('ph_queue')->where('id',$id)->update(
			['windowid'=>$wid,
			'style'=>'1',
			'status'=>'0',
			'workmanid'=>$userid,
			'calltime'=>$time,
			'callid'=>$did,
			'ledid'=>$lid,
			])){
			$businessid = Db::name('ph_queue')->where('id',$id)->value('businessid');
			//如果选叫的是新号则人数-1
			if($status==0){
				//等候人数-1
				Db::name('sys_business')->where('id',$businessid)->setDec('waitcount');				
			}

			//添加队列到排号队列表中
			//窗口设备
			if($lid){
				Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$id]);
			}
			if(!empty($clid)){
				//集中显示屏设备
				foreach ($clid as  $v) {
					Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$id]);
				}				
			}
				return $number;
		}else{
			return 0;
		}
	}

	/**
	 * [popped 暂离]
     * @param  [string] $callType       [业务类型]
     * @param  [string] $windowNumber [窗口编号]
     * @param  [string] $node        [分中心编号]
     * @param  [string] $online        [暂离3 4回归]
	 * @return [array] data  [返回数据]
	 */
	public function popped($windowNumber,$node,$callType,$online,$list){
		if(empty($windowNumber)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误'],JSON_UNESCAPED_UNICODE);
			return;
		}
		$wid = Db::name('sys_window')->where('fromnum',$windowNumber)->value('id');
		$id = Db::name('ph_led')->where('windowid',$wid)->value('id');

		//根据业务编号查询业务id
		$busid = Db::name('sys_business')->whereIn('id',$list)->field('fromnum,id')->select();
		// 获取每个业务的当前等待人数
		$listcount = $this->allbusinesscount($busid);

		if(!empty($id)){
			if(Db::name('ph_led')->where('windowid',$wid)->update(['online'=>$online])){
				echo json_encode(['data'=>['windowNumber'=>$windowNumber,'node'=>$node,'listcount'=>$listcount],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
				return;
			}else{
				echo json_encode(['data'=>['windowNumber'=>$windowNumber,'node'=>$node,'listcount'=>$listcount],'code'=>'400','message'=>'请重试'],JSON_UNESCAPED_UNICODE);
				return;
			}			
		}else{
			if(Db::name('ph_hardwareled')->where('windowid',$wid)->update(['online'=>$online])){
				echo json_encode(['data'=>['windowNumber'=>$windowNumber,'node'=>$node,'listcount'=>$listcount],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
				return;
			}else{
				echo json_encode(['data'=>['windowNumber'=>$windowNumber,'node'=>$node,'listcount'=>$listcount],'code'=>'400','message'=>'请重试'],JSON_UNESCAPED_UNICODE);
				return;
			}
		}

	}


	// 查询改窗口办理业务的等待人数
	// busid 业务编号和id的数组
	public function allbusinesscount($busid){
		$today = date('Ymd',time());
		foreach ($busid as $k => $v) {
			$busid[$k]['waitcount'] = Db::name('ph_queue')->where('today',$today)->where('style',0)->where('businessid',$v['id'])->count();
			unset($busid[$k]['id']);
		}
		return $busid;			

	}

}

