<?php
namespace app\serverinter\controller;
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
		$action = input('action');
		$devicenum = input('devicenum');
		
		//如果设备编号为空则返回
		if(empty($devicenum)){
			echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//根据方法名跳转到各个方法
		switch ($action) {
			//心跳接口
			case 'callheart':
				$this->callheart($devicenum);
				break;
			//pc登陆接口(pc)
			// case 'calllogin':
			// 	$this->calllogin($devicenum,input('loginname'),input('loginpass'));
			// 	break;
			//安卓登陆
			case 'calladrlogin':
				$this->calladrlogin($devicenum,input('loginname'),input('loginpass'),input('wid'));
				break;
			//所有窗口(pc)
			// case 'callwindows':
			// 	$this->callwindows($devicenum,input('userid'));
			// 	break;
			//窗口选择(pc)
			// case 'window':
			// 	$this->window($devicenum,input('userid'),input('windowid'));
			// 	break;
			//修改员工在线状态
			case 'online':
				$this->online(input('userid'),input('online'));
				break;
			//呼叫器信息查询
			case 'callinfo':
				$this->callinfo($devicenum,input('userid'));
				break;
			//叫号
			case 'call':
				$this->call($devicenum,input('userid'));
				break;
			//重呼
			case 'againcall':
				$this->againcall($devicenum,input('id'));
				break;
			//置后
			case 'aftercall':
				$this->aftercall($devicenum,input('id'),input('userid'));
				break;
			//选叫
			case 'choicecall':
				$this->choicecall($devicenum,input('id'),input('userid'),input('oldid'));
				break;
			//选叫排队号码集
			case 'choicenumber':
				$this->choicenumber($devicenum,input('userid'));
				break;
			//弃号
			case 'giveup':
				$this->giveup($devicenum,input('id'));
				break;
			//开始评价
			case 'startevaluation':
				$this->startevaluation($devicenum,input('userid'),input('id'));
				break;
			//提交评价
			// case 'upstyle':
			// 	$this->upstyle($devicenum,input('id'),input('status'));
			// 	break;
			//登陆状态更改
			case 'upusertype':
				$this->upusertype($devicenum,input('status'));
				break;
			//查询评价状态
			case 'showtype':
				$this->showtype(input('id'));
				break;
			//修改密码
			case 'uppass':
				$this->uppass(input('passwordold'),input('passwordnew'),input('userid'));
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

    /**
     * [callheart 心跳接口]
     * @param  [string] $devicenum [设备编号]
     * @return [array]    $data   [返回数据集]
     */
    public function callheart($devicenum){
		
		$call = Db::name('ph_call')->field('id,windowid')->where('number',$devicenum)->find();
		$did = $call['id'];
		//更新最后登陆时间
		$time = date('Y-m-d H:i:s',time());
		Db::name('ph_call')->where('number',$devicenum)->update(['lastlogin'=>$time]);

		//窗口id
		// $wid = Db::name('ph_call')->where('number',$devicenum)->value('windowid');
		$wid = $call['windowid'];
		
		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该设备未设置'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//根据窗口id查询员工id
		$userid = Db::name('sys_window')->where('id',$wid)->value('workmanid');
		if(!$userid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'用户未登陆'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//查询当天等候人数
		//根据窗口id查询该窗口可以办理哪些业务
		$busids = Db::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');
		$day = date('d',time());
		$count = 0;
		if($businessid = explode(',',$busids)){
			foreach ($businessid as $v) {
				$count += Db::name('sys_business')->where('id',$v)->where('day',$day)->value('waitcount');
			}
		}

		$today = date('Ymd',time());
		//条件 查询当前的业务流水号
		$map['businessid'] = ['in',$busids];
		$map['today'] = $today;
		$map['style'] = ['in','1,2,3'];
		$map['status'] = '1';
		$map['callid'] = $did;

		
		//查询当前业务编号
		$que = Db::name('ph_queue')->field('id,flownum')->where($map)->find();


		$data = ['id'=>$userid,'count'=>$count,'flownum'=>$que['flownum'],'qid'=>$que['id']];
		echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;
    }


    /**
     * [calllogin pc登陆]
     * @param  [string] $devicenum [设备编号]
     * @param  [string] $loginname [登陆账号]
     * @param  [string] $loginpass [登陆密码]
     * @return [array]   $data    [返回数据集]
     */
    public function calllogin($devicenum,$loginname,$loginpass){
    	$loginpass = md5($loginpass);
    	$list = Db::name('sys_workman')->field('id')->where("loginname='$loginname' and loginpass='$loginpass'")->find();
    	if(!$list){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'账号密码错误'],JSON_UNESCAPED_UNICODE);
			return;
    	}

		$data['id'] = $list['id'];
		echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;
    }

    /**
     * [calladrlogin 安卓端登陆]
     * @param  [string] $devicenum [设备编号]
     * @param  [string] $loginname [登陆账号]
     * @param  [string] $loginpass [登陆密码]
     * @return [array]   $data    [返回数据集]
     */
    public function calladrlogin($devicenum,$loginname,$loginpass,$wid){

		//查询设备编号是否存在 不存在就创建
		if(!$call = Db::name('ph_call')->field('id,usestatus,windowid')->where('number',$devicenum)->find()){
			$data1['number'] = $devicenum;
			$data1['createtime'] = date('Y-m-d H:i:s',time());
			Db::name('ph_call')->insert($data1);
		}
		//查询该设备是否在使用
		//$call = Db::name('ph_call')->field('id,usestatus,windowid')->where('number',$devicenum)->find();
		if($call['usestatus']!='1'){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该设备未使用'],JSON_UNESCAPED_UNICODE);
			return;
		}

    	$loginpass = md5($loginpass);
    	$list = Db::name('sys_workman')->where("loginname='$loginname' and loginpass='$loginpass'")->value('id');
    	if(!$list){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'账号密码错误'],JSON_UNESCAPED_UNICODE);
			return;
    	}
    	//窗口id
    	$windowid = $call['windowid'];

    	

   		//如果传参有窗口id  表明是第二次访问 需要将其他窗口的员工下线
    	if($wid){
    		// 如果在其他窗口登陆 则该窗口下线
    		downnumber($wid);
    		Db::name('sys_window')->where('id',$wid)->update(['workmanid'=>'0']);
    	}
    	
		//检测该用户是否在其他窗口登陆
    	$id = Db::name('sys_window')->where('workmanid',$list)->value('id');
    	//判断如果有在线则返回窗口id
    	if($id){
    		echo json_encode(['data'=>['wid'=>$id,'id'=>0],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
    	}

    	//如果当前窗口更新员工成功 更改员工表的在线状态和最后登陆时间
    	if(Db::name('sys_window')->where('id',$windowid)->update(['workmanid'=>$list])){
    		$data['online'] = '5';
    		$data['loginwindowid'] = $windowid;
    		$data['lastlogin'] = date('Y-m-d H:i:s',time());
    		//将设备的在线状态改为登陆中
    		Db::name('pj_device')->where('windowid',$windowid)->update(['online'=>'5']);
    		//更改员工的在线状态
    		Db::name('sys_workman')->where('id',$list)->update($data);

    		//将登陆状态放入队列中
    		onfine($windowid);

			echo json_encode(['data'=>['id'=>$list,'wid'=>0],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;  		
    	}else{
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'窗口添加员工失败'],JSON_UNESCAPED_UNICODE);
    		return; 
    	}
    }



    /**
     * [callwindows pc查询所属部门的窗口]  
     * @param  [string] $devicenum [设备编号]
     * @param  [int] $userid [工作人员id]
     * @return [array]   $data    [返回数据集]
     */
    public function callwindows($devicenum,$userid){
    	//根据用户id查询员工的所属部门
    	$sid = Db::name('sys_workman')->where('id',$userid)->value('sectionid');
    	//根据部门id查询该部门下的所有窗口
    	$window = Db::name('sys_window')->field('fromnum,id')->where("sectionid='$sid' and valid=1")->select();

    	echo json_encode(['data'=>$window,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
    	return;
    }


    /**
     * [window pc选择窗口]  
     * @param  [string] $devicenum [设备编号]
     * @param  [int] $userid    [员工id]
     * @param  [int] $windowid  [窗口id]
     * @return [array]   $data    [返回数据集]
     */
    public function window($devicenum,$userid,$windowid){
    	//判断窗口id是否为空
    	if(empty($windowid)||empty($userid)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'未选择窗口'],JSON_UNESCAPED_UNICODE);
			return;
    	}
    	//查询该窗口是否有员工在线
    	$user = Db::name('sys_window')->where('id',$windowid)->value('workmanid');
    	//如果员工在其他窗口有登陆 将其他窗口的在线状态改为0

    	//Db::name('sys_window')->where('id',$id)->update(['workmanid'=>'0']);
    	//如果该窗口有员工在线就强制下线
    	if($user!='0'){
    		Db::name('sys_window')->where('id',$windowid)->update(['workmanid'=>'0']);
    		Db::name('sys_workman')->where('id',$user)->update(['online'=>'0']);
    	}

    	if(Db::name('sys_window')->where('id',$windowid)->update(['workmanid'=>$userid])){
    		$data['online'] = '5';
    		$data['loginwindowid'] = $windowid;
    		Db::name('sys_workman')->where('id',$userid)->update($data);
			echo json_encode(['data'=>array(),'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;  		
    	}else{
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'窗口添加员工失败'],JSON_UNESCAPED_UNICODE);
    		return; 
    	}
    }

    /**
     * [online 更改员工在线状态]
     * @param  [type] $userid [员工id]
     * @param  [type] $online [在线状态 3点击暂离 4回归 5登陆 0 离线]
     * @return [array]  data  [数组]
     */
	public function online($userid,$online){
		if(empty($userid)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'未选择窗口'],JSON_UNESCAPED_UNICODE);
			return;
		}
		
		if(DB::name('sys_workman')->where('id',$userid)->update(['online'=>$online])){
			//根据员工查询当前的窗口id
			$wid = DB::name('sys_window')->where('workmanid',$userid)->value('id');

			//根据窗口id查询窗口设备id 和评价器设备id
			$lid = Db::name('ph_led')->where('windowid',$wid)->value('id');
			$did = Db::name('pj_device')->where('windowid',$wid)->value('id');

			//根据设备id更改该设备的状态
			Db::name('ph_led')->where('id',$lid)->update(['online'=>$online]);
			Db::name('pj_device')->where('id',$did)->update(['online'=>$online]);

			// 将设备在线状态提交到队列 common.php
			uponline($lid,$did,$online);

			if($online=='0'){
				Db::name('sys_window')->where('id',$wid)->update(['workmanid'=>'0']);
			}

			echo json_encode(['data'=>array(),'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		}else{
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'修改失败'],JSON_UNESCAPED_UNICODE);
		}
		return;
	}

	/**
	 * [callinfo 呼叫器信息查询]
	 * @param  [string] $devicenum [设备编号]
	 * @param  [int] $userid    [员工id]
	 * @return [array]  $data     [返回数据]
	 */
	public function callinfo($devicenum,$userid){
		if(empty($userid)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//根据员工id查询当前员工信息
		$list = Db::name('sys_workman')->field('id,name,number,sectionid,photo')->where('id',$userid)->find();
		//将员工信息赋值给data
		$data['name'] = $list['name'];
		$data['number'] = $list['number'];
		$data['section'] = Db::name('gra_section')->where('id',$list['sectionid'])->value('tname');
		//根据设备编号查询窗口id  再查询窗口编号
		$wid = Db::name('ph_call')->where('number',$devicenum)->value('windowid');
		$windowname = Db::name('sys_window')->where('id',$wid)->value('fromnum');

		$today = date('Ymd',time());
		//根据员工id查询当前正在办理的流水号
		//$flownum = Db::name('ph_queue')->where("today='$today' and workmanid='$userid' and (style='1' or style='2')")->value('flownum');
		
		//查询当天等候人数
		//根据窗口id查询该窗口可以办理哪些业务
		//$busids = Db::name('sys_winbusiness')->where('id',$wid)->value('businessid');

		//条件
		// $map['businessid'] = ['in',$busids];
		// $map['today'] = $today;
		// $map['status'] = '0';
		//计算窗口业务总排队人数
		//$count = Db::name('ph_queue')->where($map)->count();

		//查询当天该员工办理的人数
		//$sum =  Db::name('ph_queue')->where("workmanid='$userid' and status>2 and today='$today'")->count();

		//窗口名称 
		$data['windowname'] = $windowname;
		// $data['flownum'] = $flownum;

		$request = request();
		$path1 = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public/uploads/';
		$data['photo'] = $path1.$list['photo']; 

		echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;

	}


	/**
	 * [call 叫号]
	 * @param  [string] $devicenum [设备编号]
	 * @param  [int] $userid    [员工id]
	 * @return [array]  $data     [返回数据]
	 */
	public function call($devicenum,$userid){
		if(empty($userid)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登录'],JSON_UNESCAPED_UNICODE);
			return;
		}
		$starttime = explode(' ',microtime());
		// echo microtime();

		//根据设备编号查询呼叫器设备id和窗口id 
		$call = Db::name('ph_call')->field('id,windowid')->where('number',$devicenum)->find();
		$did = $call['id'];
		//窗口id
		$wid = $call['windowid'];
		//Db::name('sys_window')->where('workmanid',$userid)->value('id');
		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登陆'],JSON_UNESCAPED_UNICODE);
			return;
		}

		$time = date('Y-m-d H:i:s',time());
		$today = date('Ymd',time());

		//如果没有oldid 则将该窗口当天之前的排号状态改为完成
		Db::name('ph_queue')->where("today='$today' and windowid='$wid'")->whereIn('style','1,2,3')->update(['status'=>'3','style'=>'4','endtime'=>$time]);

		// 根据窗口id查询窗口编号
		$windownum = Db::name('sys_window')->where('id',$wid)->value('fromnum');
		//根据窗口id查询业务范围
		$busid = Db::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');

		$map['businessid'] = ['in',$busid];
		$map['today'] = $today;
		$map['style'] = '0';
		//根据条件查询业务范围内下一个排号id
		$que = Db::name('ph_queue')->field('id,businessid,flownum')->where($map)->order('ordernumber desc,taketime')->find();
		$qid = $que['id'];
		//如果没有查找到id则返回
		if(!$qid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'当前无排队'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//根据窗口id查询窗口屏的设备id
		$lid = Db::name('ph_led')->where('windowid',$wid)->value('id');
		if(empty($lid)){
			$lid = Db::name('ph_hardwareled')->where('windowid',$wid)->value('id');
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

			//添加队列到排号队列表中
			//窗口设备
//			 if($lid){
//			 	Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$qid]);
//			 }
			 if(!empty($clid)){
			 	//集中显示屏设备
			 	foreach ($clid as  $v) {
			 		Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$qid]);
			 	}
			 }

			//等候人数-1
			Db::name('sys_business')->where('id',$que['businessid'])->setDec('waitcount');
			// 短信开关 1开 0关
			$set = Db::name('dx_set')->where('id',1)->find();
			$messageoff = $set['messageoff'];// 短信总开关
			$calloff = $set['calloff'];	//临近短信开关
			$callnowoff = $set['callnowoff']; //叫号短信开关

			// 如果叫号短信开关和总开关打开则发送临近叫号短信
			if($messageoff==1&&$callnowoff==1){
				$this->callmessage($que['flownum'],$windownum,$que['mobile'],$set['sign'],$set['username']);
			}

			// 如果临近短信开关和总开关打开则发送临近叫号短信
			if($messageoff==1&&$calloff==1){
				// 查询等候人数 如果等于XX人时发送短信
				$count = Db::name('sys_business')->where('id',$que['businessid'])->value('waitcount');

				// 判断是否有人数 如果有才判断进行短信发送
				if(!empty($set['nearmessage1'])){
					// 查询当前办理号后面的10个号码
					$wait = Db::name('ph_queue')->field('id,flownum,mobile,message')->where($map)->order('ordernumber desc,taketime')->limit($set['nearmessage1'])->select();

					// 取第set['nearmessage1']个号码  如果电话不为空则发送短信
					$num = $set['nearmessage1'];
					if(!empty($wait[$num])){
						$nearwait = $wait[$num];
						if(!empty($nearwait['mobile'])&&$nearwait['message']!=1){

							// 调用发送短信接口
							$this->countmessage($nearwait['flownum'],$set['nearmessage1'],$nearwait['mobile'],$set['sign'],$set['username']);
							// 短信发送完成 则该条不再发送
							Db::name('ph_queue')->where('id',$nearwait['id'])->update(['message'=>1]);
						}
					}
				}
				// 判断是否有人数 如果有才判断进行短信发送
				if(!empty($set['nearmessage2'])){
					// 查询当前办理号后面的10个号码
					$wait = Db::name('ph_queue')->field('id,flownum,mobile,message')->where($map)->order('ordernumber desc,taketime')->limit($set['nearmessage1'])->select();

					// 取第set['nearmessage2']个号码  如果电话不为空则发送短信
					$num = $set['nearmessage2'];
					if(!empty($wait[$num])){
						$nearwait = $wait[$num];
						if(!empty($nearwait['mobile'])&&$nearwait['message']!=2){
							// 调用发送短信接口
							$this->countmessage($nearwait['flownum'],$set['nearmessage2'],$nearwait['mobile'],$set['sign'],$set['username']);
							// 短信发送完成 则该条不再发送
							Db::name('ph_queue')->where('id',$nearwait['id'])->update(['message'=>2]);
						}
					}

				}
			}
			// 叫号插入队列
			cachequeue($wid,$qid,$que['flownum'],$que['businessid']);
			

			 $endtime = explode(' ',microtime());
			 $thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
			 $thistime = round($thistime,3);

			echo json_encode(['data'=>['id'=>$qid,'time'=>$thistime],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
		}
	}

	/**
	 * [againcall 重呼]
	 * @param  [string] $devicenum [设备编号]
	 * @param  [int] $id        [排号id]
	 * @return [array] data  [返回数据]
	 */
	public function againcall($devicenum,$id){
		if(empty($id)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'请先叫号'],JSON_UNESCAPED_UNICODE);
			return;
		}
		$wid = Db::name('ph_call')->where('number',$devicenum)->value('windowid');

		// 如果是弃号就直接返回
		$status = Db::name('ph_queue')->where('id',$id)->value('status');
		if($status==2){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该号已弃'],JSON_UNESCAPED_UNICODE);
			return;
		}

		$time = date('Y-m-d H:i:s',time());
		//根据窗口id查询集中显示屏的设备id集
		 $clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');
		
		if(Db::name('ph_queue')->where('id',$id)->update(['style'=>'1'])){
			$que = Db::name('ph_queue')->where('id',$id)->field('flownum,mobile')->find();
			$flownum = $que['flownum'];
			// 叫号插入队列
			cachequeue($wid,$id,$flownum);
			// 根据窗口id查询窗口编号
			$windownum = Db::name('sys_window')->where('id',$wid)->value('fromnum');
			// 短信开关 1开 0关
			$set = Db::name('dx_set')->where('id',1)->find();
			$messageoff = $set['messageoff']; // 总开关
			$callagainoff = $set['callagainoff']; //叫号短信开关
			// 如果重呼短信开关和总开关打开则发送临近叫号短信
			if($messageoff==1&&$callagainoff==1){
				$this->callmessage($flownum,$windownum,$que['mobile'],$set['sign'],$set['username']);
			}
			//添加队列到排号队列表中
			//窗口设备
//			 if($lid){
//			 	Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$id]);
//			 }
			 if(!empty($clid)){
			 	//集中显示屏设备
			 	foreach ($clid as  $v) {
			 		Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$id]);
			 	}
			 }
			echo json_encode(['data'=>['id'=>$id,'status'=>'1'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
		}else{
			echo json_encode(['data'=>['id'=>$id,'status'=>'0'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
		}

	}

	/**
	 * [aftercall 置后]
	 * @param  [string] $devicenum [设备编号]
	 * @param  [int] $id        [排号id]
	 * @param  [int] $userid    [用户id]
	 * @return [array]  data    [返回数据]
	 */
	public function aftercall($devicenum,$id,$userid){
		if(empty($userid)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登录'],JSON_UNESCAPED_UNICODE);
			return;
		}
		if(empty($id)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'请先叫号'],JSON_UNESCAPED_UNICODE);
			return;
		}

		// 如果是弃号就直接返回
		$status = Db::name('ph_queue')->where('id',$id)->value('status');
		if($status==2){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该号已弃'],JSON_UNESCAPED_UNICODE);
			return;
		}
		
		//根据设备编号查询呼叫器设备id和窗口id 
		$call = Db::name('ph_call')->field('id,windowid')->where('number',$devicenum)->find();
		$did = $call['id'];
		//窗口id
		$wid = $call['windowid'];

		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登陆'],JSON_UNESCAPED_UNICODE);
			return;
		}

		$today = date('Ymd',time());
		//根据窗口id查询业务范围
		$busid = Db::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');

		$map['businessid'] = ['in',$busid];
		$map['today'] = $today;
		$map['style'] = '0';
		//根据条件查询业务范围内下一个排号id
		$que = Db::name('ph_queue')->field('id,businessid,flownum')->where($map)->order('ordernumber desc,taketime')->find();
		$qid = $que['id'];

		//如果没有查找到排号id则返回
		if(!$qid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'当前无排队'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//根据窗口id查询窗口屏的设备id
		$lid = Db::name('ph_led')->where('windowid',$wid)->value('id');
		if(empty($lid)){
			$lid = Db::name('ph_hardwareled')->where('windowid',$wid)->value('id');
		}
		// 根据窗口id查询窗口编号
		$windownum = Db::name('sys_window')->where('id',$wid)->value('fromnum');
		//根据窗口id查询集中显示屏的设备id集
		 $clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');

		//呼叫下一个排号前 将后置的排号id叫号状态改为0
		if(Db::name('ph_queue')->where('id',$id)->update(['style'=>'0','status'=>'0','windowid'=>'0','workmanid'=>'0'])){
			Db::name('ph_deviceqid')->where('qid',$id)->delete();
		}else{
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
			return;
		}

		$time = date('Y-m-d H:i:s',time());
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

			// 叫号插入队列
			cachequeue($wid,$qid,$que['flownum'],$que['businessid']);
			deleteoldid($id);
			// 短信开关 1开 0关
			$set = Db::name('dx_set')->where('id',1)->find();
			$messageoff = $set['messageoff'];
			$calloff = $set['calloff'];
			$callnowoff = $set['callnowoff']; //叫号短信开关

			// 如果叫号短信开关和总开关打开则发送临近叫号短信
			if($messageoff==1&&$callnowoff==1){
				$this->callmessage($que['flownum'],$windownum,$que['mobile'],$set['sign'],$set['username']);
			}

			if($messageoff==1&&$calloff==1){
				// 查询等候人数 如果等于XX人时发送短信
				$count = Db::name('sys_business')->where('id',$que['businessid'])->value('waitcount');
				// 判断是否有人数 如果有才判断进行短信发送
				if(!empty($set['nearmessage1'])){
					// 查询当前办理号后面的10个号码
					$wait = Db::name('ph_queue')->field('id,flownum,mobile,message')->where($map)->order('ordernumber desc,taketime')->limit($set['nearmessage1'])->select();

					// 取第十个号码  如果电话不为空则发送短信
					$num = $set['nearmessage1'];
					if(!empty($wait[$num])){
						$nearwait = $wait[$num];
						if(!empty($nearwait['mobile'])&&$nearwait['message']!=1){
							// 调用发送短信接口
							$this->countmessage($nearwait['flownum'],$set['nearmessage1'],$nearwait['mobile'],$set['sign'],$set['username']);
							// 短信发送完成 则该条不再发送
							Db::name('ph_queue')->where('id',$nearwait['id'])->update(['message'=>1]);
						}
					}
				}
				// 判断是否有人数 如果有才判断进行短信发送
				if(!empty($set['nearmessage2'])){
					// 查询当前办理号后面的10个号码
					$wait = Db::name('ph_queue')->field('id,flownum,mobile,message')->where($map)->order('ordernumber desc,taketime')->limit($set['nearmessage1'])->select();

					// 取第十个号码  如果电话不为空则发送短信
					$num = $set['nearmessage2']-2;
					if(!empty($wait[$num])){
						$nearwait = $wait[$num];
						if(!empty($nearwait['mobile'])&&$nearwait['message']!=2){
							// 调用发送短信接口
							$this->countmessage($nearwait['flownum'],$set['nearmessage2'],$nearwait['mobile'],$set['sign'],$set['username']);
							// 短信发送完成 则该条不再发送
							Db::name('ph_queue')->where('id',$nearwait['id'])->update(['message'=>2]);
						}
					}

				}
			}
			//添加队列到排号队列表中
			//窗口设备
//			 if($lid){
//			 	Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$qid]);
//			 }
			 if(!empty($clid)){
			 	//集中显示屏设备
			 	foreach ($clid as  $v) {
			 		Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$qid]);
			 	}
			 }
			echo json_encode(['data'=>['id'=>$qid],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
		}
	}

	/**
	 * [choicenumber 查询选叫的号码集]
	 * @param  [string] $devicenum [设备编号]
	 * @param  [int] $userid    [员工id]
	 * @return [array]    data  [返回数据集]
	 */
	public function choicenumber($devicenum,$userid){
		if(empty($userid)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登录'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//根据员工id查询窗口id
		$wid = Db::name('sys_window')->where('workmanid',$userid)->value('id');

		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登陆'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//根据窗口id查询业务范围
		$busid = Db::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');

		//根据业务条件查询等待的排队流水号
		$map['businessid'] = ['in',$busid];
		$map['style'] = '0';
		$map['today'] = date('Ymd',time());
		$queid = Db::name('ph_queue')->field('id,flownum')->where($map)->select();
		if(empty($queid)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'无排队'],JSON_UNESCAPED_UNICODE);
			return;
		}
		echo json_encode(['data'=>$queid,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;
	}


	/**
	 * [choicecall 选叫]
	 * @param  [string] $devicenum [设备编号]
	 * @param  [int] $id        [排队id]
	 * @return [array]   data    [返回数据集]
	 */
	public function choicecall($devicenum,$id,$userid,$oldid){
		if(empty($id)||empty($userid)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误'],JSON_UNESCAPED_UNICODE);
			return;
		}
		$call = Db::name('ph_call')->where('number',$devicenum)->field('id,windowid')->find();
		
		//设备id	
		$did = $call['id'];
		//窗口id
		$wid = $call['windowid'];
		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登陆'],JSON_UNESCAPED_UNICODE);
			return;
		}

		$time = date('Y-m-d H:i:s',time());
		$today = date('Ymd',time());
		//更新当前排号的状态  完成
		Db::name('ph_queue')->where('windowid',$wid)->where('today',$today)->update(['status'=>'3','style'=>'4','endtime'=>$time]);
				


		//根据窗口id查询窗口屏的设备id
		$lid = Db::name('ph_led')->where('windowid',$wid)->value('id');
		if(empty($lid)){
			$lid = Db::name('ph_hardwareled')->where('windowid',$wid)->value('id');
		}
		
		//根据窗口id查询集中显示屏的设备id集
		 $clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');

		$que = Db::name('ph_queue')->where('id',$id)->field('businessid,flownum')->find();

		//将选叫的排号id设置为叫号状态
		if(Db::name('ph_queue')->where('id',$id)->update(
			['windowid'=>$wid,
			'style'=>'1',
			'workmanid'=>$userid,
			'calltime'=>$time,
			'callid'=>$did,
			'ledid'=>$lid,
			])){
			
			//等候人数-1
			Db::name('sys_business')->where('id',$que['businessid'])->setDec('waitcount');

			// 叫号插入队列
			cachequeue($wid,$id,$que['flownum'],$que['businessid']);

			//添加队列到排号队列表中
			//窗口设备
//			 if($lid){
//			 	Db::name('ph_deviceqid')->insert(['ledid'=>$lid,'time'=>$time,'qid'=>$id]);
//			 }
			 if(!empty($clid)){
			 	//集中显示屏设备
			 	foreach ($clid as  $v) {
			 		Db::name('ph_deviceqid')->insert(['cledid'=>$v,'time'=>$time,'qid'=>$id]);
			 	}
			 }
			echo json_encode(['data'=>['id'=>$id,'status'=>'1'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
				return;
		}else{
			echo json_encode(['data'=>['id'=>$id,'status'=>'0'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
		}

	}


	/**
	 * [giveup 弃号]
	 * @param  [string] $devicenum [设备编号]
	 * @param  [int] $id        [排队id]
	 * @return [array]  data    [返回数据集]
	 */
	public function giveup($devicenum,$id){
		//判断参数id是否为空
		if(empty($id)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'请先叫号'],JSON_UNESCAPED_UNICODE);
			return;
		}
		$time = date('Y-m-d H:i:s',time());
		//根据id更新排号状态  将其改为弃号和叫号完成
		if(Db::name('ph_queue')->where('id',$id)->update(['status'=>'2','style'=>'4','endtime'=>$time])){

			//删除队列表中对应排号id的数据
			Db::name('ph_deviceqid')->where('qid',$id)->delete();

			echo json_encode(['data'=>['id'=>$id,'status'=>'1'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
		}else{
			echo json_encode(['data'=>['id'=>$id,'status'=>'0'],'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
			return;
		}	
	}


	/**
	 * [startevaluation 发起评价]
	 * @param  [string] $devicenum [设备编号]
	 * @param  [int]    $userid    [员工id]
	 * @param  [int]    $id        [排号id]
	 * @return [array]  data       [返回数据集]
	 */
	public function startevaluation($devicenum,$userid,$id){
		if(empty($userid)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登录'],JSON_UNESCAPED_UNICODE);
			return;
		}
		if(empty($id)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'请先叫号'],JSON_UNESCAPED_UNICODE);
			return;
		}
		$data['workmanid'] = $userid;
		$data['queueid'] = $id;
		//如果同一个排号id已经在评价表中 表示重复 直接返回
		if(Db::name('pj_evaluate')->where('queueid',$id)->value('id')){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'请不要重复评价'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//创建评价 返回评价id
		if(Db::name('pj_evaluate')->insert($data)){
			$eid = Db::name('pj_evaluate')->getLastInsID();
			//将评价id存入排号表中
			Db::name('ph_queue')->where('id',$id)->update(['evaluateid'=>$eid]);
			//将评价id插入队列中
			upevaluation($eid,$devicenum);

			echo json_encode(['data'=>['id'=>$eid],'code'=>'200','message'=>'发起成功'],JSON_UNESCAPED_UNICODE);
			return;
		}else{
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'发起失败'],JSON_UNESCAPED_UNICODE);
			return;
		}
	}

    /**
     * [upstyle 更新评价状态]
     * @param  [type] $devicenum [设备id]
     * @param  [type] $id        [评价id]
     * @param  [type] $status    [评价状态 2评价取消，3评价超时，4等待评价器显示]
     * @return [type]            [返回id]
     */
    public function upstyle($devicenum,$id,$status){
    	if(empty($id)||empty($status)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误'],JSON_UNESCAPED_UNICODE);
			return;	
    	}
    	if(Db::name('pj_evaluate')->where('id',$id)->update(['evaluatestatus'=>$status])){
    		echo json_encode(['data'=>['id'=>$id,'status'=>'1'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
    	}else{
    		echo json_encode(['data'=>['id'=>$id,'status'=>'0'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
    	}
    }


    /**
     * [showtype 查询评价状态]
     * @param  [int] $id [评价id]
     * @return [array] data[返回数据集]
     */
	public function showtype($id){
		$type = Db::name('pj_evaluate')->where('id',$id)->value('evaluatestatus');
		echo json_encode(['data'=>['type'=>$type,'id'=>$id],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;
	}

	/**
	 * [uppass 修改密码]
	 * @param  [string] $passwordold [旧密码]
	 * @param  [string] $passwordnew [新密码]
	 * @param  [int] $userid      [员工id]
	 * @return [array]  data    [返回数据]
	 */
	public function uppass($passwordold,$passwordnew,$userid){
		$passwordold = md5($passwordold);
		$passwordnew = md5($passwordnew);
		$password = Db::name('sys_workman')->where("id",$userid)->value('loginpass');
		
		if($password===$passwordold){
			if(Db::name('sys_workman')->where("id",$userid)->update(['loginpass'=>$passwordnew])){
				echo json_encode(['data'=>['id'=>$userid,'status'=>'1'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
				return;
			}else{
				echo json_encode(['data'=>['id'=>$userid,'status'=>'0'],'code'=>'200','message'=>'失败'],JSON_UNESCAPED_UNICODE);
				return;
			}
		}else{
			echo json_encode(['data'=>['id'=>$userid,'status'=>'2'],'code'=>'200','message'=>'原始密码错误'],JSON_UNESCAPED_UNICODE);
				return;
		}
	}

	/**
	 * [selectpeople 查询呼叫器等待人数]
	 * @param  [type] $devicenum [呼叫器设备编号]
	 * @return [type]            [description]
	 */
	public function selectpeople($devicenum){
		$wid = DB::name('ph_call')->where('number',$devicenum)->value('windowid');
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

	/**
	 * [takemessage 临近短信提醒]
	 * @param  [type] $flownum  [description]
	 * @param  [type] $count    [description]
	 * @param  [type] $phone    [description]
	 * @return [type]           [description]
	 */
	public function countmessage($flownum,$count,$phone,$sign,$username){

		$json = ['flownum'=>$flownum,'count'=>$count];
		// 短信模板编号
		$code = Db::name('dx_template')->where('type',3)->value('code');

		$data1 = [
			'data'		=> $json,
			'template'	=> $code,
			'phone'		=> $phone,
			'sign'		=> $sign,
			'action'	=> 'sendSms',
			'username'	=> $username,
		];
		$url = 'http://sms.scsmile.cn/internc/index';
		// $url = 'http://192.168.0.10:8076/smileSMS/index.php/inter/index';
		// url方式提交
		$httpstr = http($url, $data1, 'GET', array("Content-type: text/html; charset=utf-8"));
	}
	/**
	 * [takemessage 叫号短信提醒]
	 * @param  [type] $flownum  [description]
	 * @param  [type] $count    [description]
	 * @param  [type] $phone    [description]
	 * @return [type]           [description]
	 */
	public function callmessage($flownum,$window,$phone,$sign,$username){

		$json = ['flownum'=>$flownum,'window'=>$window];
		// 短信模板编号
		$code = Db::name('dx_template')->where('type',4)->value('code');

		$data1 = [
			'data'		=> $json,
			'template'	=> $code,
			'phone'		=> $phone,
			'sign'		=> $sign,
			'action'	=> 'sendSms',
			'username'	=> $username,
		];
		$url = 'http://sms.scsmile.cn/internc/index';
		// url方式提交
		$httpstr = http($url, $data1, 'GET', array("Content-type: text/html; charset=utf-8"));
	}
}