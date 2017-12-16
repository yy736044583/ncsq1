<?php 
namespace app\hardware\controller;
use think\Db;

class Index{

	/**
	 * [index 硬件接口入口]
	 * @param  [action] [方法名]
	 * @param  [devicenum] [设备编号]
	 */
	public function index(){
		$action = input('action');
		$devicenum = input('devicenum');
		if(empty($action)||empty($devicenum)){
			echo json_encode(['data'=>array(),'code'=>'404','message'=>'缺少必要参数']);
			return;
		}

		switch ($action) {
			//硬件呼叫中心心跳
			case 'hardwareheart':
				$this->hardwareheart($devicenum);
				break;
			//收到心跳回复
			case 'uptype':
				$this->uptype($devicenum,input('qid'));
				break;
			//收到心跳回复
			case 'uponline':
				$this->uponline($devicenum,input('online'),input('ledid'));
				break;
			//收到心跳回复
			case 'online':
				$this->online($devicenum,input('online'),input('number'));
				break;
			default:
				break;
		}

	}
	/**
	 * [hardwareheart 硬件呼叫中心心跳]
	 * @param  [string] $devicenum [设备编号]
	 * @return [array]   data      [返回数据集]
	 */
	public function hardwareheart($devicenum){
		//根据设备编号查询设备是否存在 不存在就创建
		if(!$center = Db::name('ph_hardwarecenter')->where('number',$devicenum)->field('id,usestatus')->find()){
			$data1['number'] = $devicenum;
			$data1['createtime'] = date('Y-m-d H:i:s',time());
			Db::name('ph_hardwarecenter')->insert($data1);
		}

		//判断设备是否在使用
		if($center['usestatus']!='1'){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该设备未使用']);
			return;
		}

		//更新最后登陆时间
		$time = date('Y-m-d H:i:s',time());
		Db::name('ph_hardwarecenter')->where('number',$devicenum)->update(['lastlogin'=>$time]);

		//设备id
		$id = $center['id'];

		//根据设备id查询窗口屏信息集
		$lid = Db::name('ph_hardwareled')->where('hardwarecenterid',$id)->where('usestatus',1)->column('id');
		if(empty($lid)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'请配置窗口']);
			return;
		}
		$lid = implode(',',$lid);
		
		$today = date('Y-m-d',time());

		//根据窗口屏id查询队列中当天的排号id
		$deviceqid = Db::name('ph_deviceqid')->whereIn('ledid',$lid)->whereLike('time',"%$today%")->field('id,qid,ledid,online')->find();
		// dump($deviceqid);die;
		if($deviceqid['online']){
			$number = Db::name('ph_hardwareled')->where('id',$deviceqid['ledid'])->value('number');
			echo json_encode(['data'=>['qid'=>0,'businessname'=>'','lednumber'=>$number,'flownum'=>'','fromnum'=>'','online'=>$deviceqid['online'],'ledid'=>$deviceqid['ledid']],'code'=>'200','message'=>'成功']);
			return;
		}

		if(!$deviceqid['qid']){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'当前无叫号']);
			return;
		}	

		//根据排号id查询业务id 排号编号	窗口屏id
		$que = Db::name('ph_queue')->where('id',$deviceqid['qid'])->field('businessid,flownum,ledid,windowid')->find();

		//根据业务id查询业务名称
		$businessname = Db::name('sys_business')->where('id',$que['businessid'])->value('name');

		//根据窗口id查询窗口编号
		$fromnum = Db::name('sys_window')->where('id',$que['windowid'])->value('fromnum');

		//根据窗口屏id查询窗口屏编号
		$led = Db::name('ph_hardwareled')->where('id',$que['ledid'])->field('number,online')->find();
		// $ledonline = 0;
		// if($led['online']==3||$led['online']==4){
		// 	$ledonline = $led['online'];
		// }
		echo json_encode(['data'=>['qid'=>$deviceqid['id'],'businessname'=>$businessname,'lednumber'=>$led['number'],'flownum'=>$que['flownum'],'fromnum'=>$fromnum,'online'=>0,'ledid'=>0],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;
		//根据设备id查询呼叫器信息
		//$hardcall = Db::name('ph_hardwarecall')->where('hardwarecenterid',$id)->where('valid',1)->field('number,windowid')->select();

	}

	/**
	 * [uptype 收到心跳返回 删除数据]
	 * @param  [string] $devicenum [中心编号]
	 * @param  [type] $qid       [队列id]
	 * @return [type]            [返回数据]
	 */
    public function uptype($devicenum,$qid){
    	if(empty($qid)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'队列id不能为空']);
			return;
    	}
    	// 排号id
    	$id = Db::name('ph_deviceqid')->where('id',$qid)->value('qid');
    	if(Db::name('ph_queue')->where('id',$id)->value('status')<3){
	    	//更改排号表中的状态
	    	Db::name('ph_queue')->where('id',$id)->update(['style'=>'3','status'=>'1']);    		
    	}
    	if(Db::name('ph_deviceqid')->where('id',$qid)->delete()){
    		echo json_encode(['data'=>array(),'code'=>'200','message'=>'成功']);
    	}else{
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败,请重发']);
    	}
    } 

    /**
     * [uponline 修改窗口屏在线状态]
     * @param  [string] $devicenum [中心编号]
     * @param  [int] $online    [在线状态]
     * @param  [string] $number    [窗口屏编号]
     * @return [type]            [description]
     */
    public function uponline($devicenum,$online,$ledid){
    	if(empty($online)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'状态不能为空'],JSON_UNESCAPED_UNICODE);
			return;
    	}
     	if(empty($ledid)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'窗口屏id不能为空'],JSON_UNESCAPED_UNICODE);
			return;
    	}  

    	if($online==3){
    		if(Db::name('ph_hardwareled')->where('id',$ledid)->update(['online'=>2])){
    			Db::name('ph_deviceqid')->where("ledid='$ledid' and online=3")->delete();
    			echo json_encode(['data'=>array(),'code'=>'200','message'=>'成功']);
				return;
    		}else{
    			echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败']);
				return;
    		}
    		
    	}else{
    		if(Db::name('ph_hardwareled')->where('id',$ledid)->update(['online'=>1])){
    			
    			Db::name('ph_deviceqid')->where("ledid='$ledid' and online=4")->delete();
    			// 点击回归查询窗口备注并返回
    			$summary = Db::name('ph_hardwareled')->where('id',$ledid)->value('summary');
				echo json_encode(['data'=>$summary,'code'=>'200','message'=>'成功']);
				return;
    		}else{
    			echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败']);
				return;	
			}
    	}
    }

    /**
     * [online 呼叫器暂离回归]
     * @param  [string] $devicenum [中心编号]
     * @param  [int] $online    [在线状态]
     * @param  [string] $number    [呼叫器编号]
     * @return [type]            [description]
     */
    public function online($devicenum,$online,$number){
    	// 根据呼叫器编号查询窗口id
    	$wid = Db::name('ph_hardwarecall')->where('number',$number)->value('windowid');
    	$data = $this->waitcount($wid);
    	// 修改该窗口下led屏的在线状态
    	if(Db::name('ph_hardwareled')->where('windowid',$wid)->update(['online'=>$online])){

    		$time = date('Y-m-d H:i:s',time());
    		$ledid = Db::name('ph_hardwareled')->where('windowid',$wid)->value('id');
    		Db::name('ph_deviceqid')->insert(['online'=>$online,'ledid'=>$ledid,'time'=>$time]);
    		
    		echo json_encode(['data'=>['waitcount'=>$data['count'],'oldcount'=>$data['oldcount']],'code'=>'200','message'=>'成功']);
				return;
		}else{
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败']);
				return;	
		}
	}

	//根据窗口查询等待人数和已办理人数
	public function waitcount($wid){
		$today = date('Ymd',time());
		//已办理人数
		$map['today'] = $today;
		$map['windowid'] = $wid;
		$map['style'] = 4;
		$map['status'] = 3;
		$oldcount = Db::name('ph_queue')->where($map)->count();

		// 查询窗口等待人数
		$busids = Db::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');
		$count = 0;
		$day = date('d',time());
		if(!empty($busids)){
			$busids = explode(',',$busids);
			foreach ($busids as $k => $v) {
				$count += Db::name('sys_business')->where('id',$v)->where('day',$day)->value('waitcount');
			}
		}
		$data['count'] = $count;
		$data['oldcount'] = !empty($oldcount)?$oldcount:0;
		return $data;
	}

}