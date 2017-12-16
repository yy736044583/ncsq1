<?php
namespace app\serverinter\controller;
use think\Db;
//窗口接口
class Led{
	/**
	 * 根据方法名跳转
	 * @param [string] $[action] [方法名]
	 * @param [string] $[devicenum] [设备编号]
	 */
    public function index(){ 
		$action = input('action');
		$devicenum = input('devicenum');
		//如果设备编号为空则返回
		if(empty($devicenum)){
			echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到']);
			return;
		}
		//根据方法名跳转到各个方法
		switch ($action) {
			//心跳接口
			case 'ledheart':
				$this->ledheart($devicenum);
				break;
			//中心名称
			case 'thisbusiness':
				$this->thisbusiness($devicenum);
				break;
			//查询窗口信息
			case 'show':
				$this->show($devicenum,input('id'));
				break;
			//更改叫号状态
			case 'ledstyle':
				$this->ledstyle($devicenum,input('id'),input('pid'));
				break;
			//更改登陆 暂离 回归
			case 'upusertype':
				$this->upusertype($devicenum,input('status'));
				break;
			//查询等待人数
			case 'selectpeople':
				$this->selectpeople($devicenum);
				break;	
			default:
				echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
				return;
				break;
		}
    }

    public function ledheart($devicenum){

		//查询设备编号是否存在 不存在就创建
		if(!$led = Db::name('ph_led')->field('id,usestatus,windowid,online')->where('number',$devicenum)->find()){
			$data1['number'] = $devicenum;
			$data1['createtime'] = date('Y-m-d H:i:s',time());
			Db::name('ph_led')->insert($data1);
		}

		//判断该设备是否在使用
		if($led['usestatus']!='1'){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该设备未使用'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//更新最后登陆时间
		$time = date('Y-m-d H:i:s',time());
		Db::name('ph_led')->where('number',$devicenum)->update(['lastlogin'=>$time]);

		//窗口id
		$wid = $led['windowid'];
		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该设备未使用'],JSON_UNESCAPED_UNICODE);
			return;
		}
		// //根据窗口id查询员工id
		// $userid = Db::name('sys_window')->where('id',$wid)->value('workmanid');
		// if($userid=='0'){
		// 	echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登陆'],JSON_UNESCAPED_UNICODE);
		// 	return;
		// }

		//查询设备在线状态  3暂离状态 4回归 5登陆
		//$usertype = Db::name('ph_led')->where('id',$led['id'])->value('online');
		$data['type'] = 0;
		switch ($led['online']) {
			case '3':
				$data['type'] = $led['online'];
				break;
			case '4':
				$data['type'] = $led['online'];
				break;
			case '5':
				$data['type'] = $led['online'];
				break;
			default:break;
		}

		//根据窗口id查询该窗口可以办理哪些业务
		$busids = Db::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');
		$busid = explode(',',$busids);
		$count = 0;
		$day = date('d',time());
		if($busid){
			foreach ($busid as $v) {
				$count += Db::name('sys_business')->where('id',$v)->where('day',$day)->value('waitcount');
			}
		}


		$que = Db::name('ph_deviceqid')->field('id,qid')->where('ledid',$led['id'])->order('time')->find();
		//查询当天该窗口正在叫号的流水号 id  
		

		$data['id'] = $que['qid'];//排号id
		$data['pid'] = $que['id'];//队列表id
		$data['count'] = $count;

		echo json_encode(['data'=>$data,'code'=>'200','message'=>'正常'],JSON_UNESCAPED_UNICODE) ;	
		return;

    }


    /**
     * [cledstyle 收到正在呼叫的数据后返回]
     * @param  [string] $devicenum [集中显示屏设备编号]
     * @param  [int] $id    [排队号id]
     * @param  [int] $pid    [队列表id]
     * @return [array]   $data   [返回数据集]
     */
    public function ledstyle($devicenum,$id,$pid){
    	if(empty($id)||empty($pid)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'数据参数错误'],JSON_UNESCAPED_UNICODE);
			return;
    	}
    	if(Db::name('ph_queue')->where('id',$id)->value('status')<3){
	    	//更改排号表中的状态
	    	Db::name('ph_queue')->where('id',$id)->update(['style'=>'3','status'=>'1']);    		
    	}

    	//删除队列表中的对应id
    	if(Db::name('ph_deviceqid')->where('id',$pid)->delete()){
    		echo json_encode(['data'=>array(),'code'=>'200','message'=>'正常'],JSON_UNESCAPED_UNICODE);
			return;
    	}else{
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
			return;
    	}


    }


    /**
     * [thisbusiness 查询该窗口办理业务和窗口名称]
     * @param  [string] $devicenum [设备编号]
     * @return [array]   $data    [返回数据集]
     */
    public function thisbusiness($devicenum){
    	$wid = Db::name('ph_led')->where('number',$devicenum)->value('windowid');	
    	//窗口名
		$windowname = Db::name('sys_window')->where('id',$wid)->value('fromnum');
    	//根据窗口id查询该窗口可以办理哪些业务
		$busids = Db::name('sys_winbusiness')->where('id',$wid)->value('businessid');
		$busids = explode(',',$busids);

		//查询每个业务的名称 并拼接成字符串
		$name = '';
		foreach ($busids as $k=> $v) {
			$name .= Db::name('sys_business')->where('id',$v)->value('name').' ';	
		}
		$name = rtrim($name,',');

		$data['businessname'] = $name;
		$data['windowname'] = $windowname;

		echo json_encode(['data'=>$data,'code'=>'200','message'=>'正常'],JSON_UNESCAPED_UNICODE) ;	
		return;
    }


    /**
     * [show 查询窗口显示屏信息]
     * @param  [string] $devicenum [设备编号]
     * @param  [int] $id        [排号id]
     * @return [array]            [返回数据]
     */
   public function show($devicenum,$id){
   		if(!$id){
   			echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误'],JSON_UNESCAPED_UNICODE);
			return;
   		}
		// //查询当天该窗口正在叫号的流水号 id  
		$que = Db::name('ph_queue')->field('id,flownum')->where('id',$id)->find();

		// $data['count'] = $count;
		$data['flownum'] = $que['flownum'];
		//$data['list'] = $list;
		$data['id'] = $que['id'];

		echo json_encode(['data'=>$data,'code'=>'200','message'=>'正常'],JSON_UNESCAPED_UNICODE) ;	
		return;
   } 

    /**
     * [upusertype 收到员工登陆状态后回复]
     * @param  [type] $devicenum [设备id]
     * @param  [type] $userid    [员工id]
     * @param  [type] $status    [状态 3暂离 4回归 5登陆]
     * @return [type]            [description]
     */
    public function upusertype($devicenum,$status){
    	if(empty($status)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误'],JSON_UNESCAPED_UNICODE);
			return;
    	}
    	//根据设备编号查询窗口id
    	$wid = Db::name('pj_device')->where('number',$devicenum)->value('windowid');
    	//根据窗口id查询员工id
    	$userid = Db::name('sys_window')->where('id',$wid)->value('workmanid');

    	switch ($status) {
    		case '3':
    			Db::name('sys_workman')->where('id',$userid)->update(['online'=>'2']);
    			if(Db::name('ph_led')->where('number',$devicenum)->update(['online'=>'2'])){
    				echo json_encode(['data'=>['userid'=>$userid],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
					return;
    			}else{
    				echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
					return;
    			}
    			break;
    		case '4':
    			Db::name('sys_workman')->where('id',$userid)->update(['online'=>'1']);
    			if(Db::name('ph_led')->where('number',$devicenum)->update(['online'=>'1'])){
    				echo json_encode(['data'=>['userid'=>$userid],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
					return;
    			}else{
    				echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
					return;
    			}
    			break;
    		case '5':
    			Db::name('sys_workman')->where('id',$userid)->update(['online'=>'1']);
    			if(Db::name('ph_led')->where('number',$devicenum)->update(['online'=>'1'])){
    				echo json_encode(['data'=>['userid'=>$userid],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
					return;
    			}else{
    				echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
					return;
    			}
    			break;
    		default:break;
    	}

    }   
	/**
	 * [selectpeople 查询呼叫器等待人数]
	 * @param  [type] $devicenum [呼叫器设备编号]
	 * @return [type]            [description]
	 */
	public function selectpeople($devicenum){
		$wid = DB::name('ph_led')->where('number',$devicenum)->value('windowid');
		$busids = DB::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');
		$busids = explode(',',$busids);

		$count = 0;
		$day = date('d',time());
		foreach ($busids as $k => $v) {
			$count += Db::name('sys_business')->where('id',$v)->where('day',$day)->value('waitcount');
		}
		echo json_encode(['data'=>['count'=>$count],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;
	}
}