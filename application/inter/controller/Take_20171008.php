<?php
namespace app\inter\controller;
use think\Db;
//排号机接口
class Take{

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
			case 'takeheart':
				$this->takeheart($devicenum);
				break;
			//截图接口	
			case'picture':
				$this->picture($devicenum);
				break;
			//关机接口	
			case'nowdown':
				$this->nowdown($devicenum,input('down'));
				break;
			//取号接口	
			case'takebusiness':
				//业务编号  身份号  社保号  手机号 预约编号
				$this->takebusiness($devicenum,input('businessid'),input('idcard'),input('socialsecuritycard'),input('mobile'),input('ordernumber'),input('matterid'));
				break;
			default:
				echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
				return;
				break;
		}
    }



    /**
     * [deviceheart 评价设备心跳]
     * @param  [string] $devicenum [取号设备编号]
     * @return [array]  $data      [返回数据集]
     */
    
	public function takeheart($devicenum){

		//查询设备编号是否存在 不存在就创建
		if(!$list = Db::name('ph_take')->field('id,number,runtype,down,downtimehour,downtimemin')->where('number',$devicenum)->find()){
			$data1['number'] = $devicenum;
			$data1['createtime'] = date('Y-m-d H:i:s',time());
			Db::name('ph_take')->insert($data1);
		}
		if(Db::name('ph_take')->where('number',$devicenum)->value('usestatus')!='1'){
			echo json_encode(['data'=>array(),'code'=>'201','message'=>'该设备未使用']);
			return;
		}

		//更新最后登陆时间
		$time = date('Y-m-d H:i:s',time());
		Db::name('ph_take')->where('number',$devicenum)->update(['lastlogin'=>$time]);

		/**
		 * id 			设备id
		 * number 		设备编号
		 * runtype 		运行状态，0未运行，1运行中，2关机
		 * down 		是否定时关机 0不定时  1定时
		 * downtimehour 关机时间 小时
		 * downtimemin  关机时间 分钟
		 * screenshot 	是否截图  0不截图  1截图
		 */
		
		$data['id'] = $list['id'];				
		$data['number'] = $list['number'];
		$data['runtype'] = $list['runtype'];
		$data['down'] = $list['down'];
		$data['downtimehour'] = $list['downtimehour'];
		$data['downtimemin'] = $list['downtimemin'];
		//调用截图时间方法  screentime
		//0 不截图  1截图
		$data['screenshot'] = $this->screentime($list['id']);

		echo json_encode(['data'=>$data,'code'=>'200','message'=>'正常']);
		return;
	}



	/**
	 * 截图接口
	 * @param  [string] $devicenum [取号设备编号]
	 * @param  [file] picture [图片文件名]
	 * @return [array]  $data      [返回数据集]
	 */
	public function picture($devicenum){
		//确保uploads文件夹存在
	  	$path1 =  ROOT_PATH . 'public' . DS . 'uploads';
        if (!file_exists($path1)) {
            if(!mkdir($path1)){
                echo '提交失败,自动创建文件夹失败';
            }
        }
        //如果排号机截图文件夹不存在则创建
        $path =  ROOT_PATH . 'public' . DS . 'uploads'.DS.'takepicture'; // 接收文件目录
	        if (!file_exists($path)) {
	            if(!mkdir($path)){
	                echo '提交失败,自动创建文件夹失败';
	            }
	    }

	    if($_FILES['picture']['name'] != ''){
	    	//将截图文件保存到指定文件夹下  文件名为设备编号名称
	    	$url = $path.DS.$devicenum.'.png';
			copy ($_FILES['picture']['tmp_name'],$url )  or die ("Could not copy file");
			$url1 = 'takepicture/'.$devicenum.'.png';

			//将文件保存地址存到数据库
			if(Db::name('ph_take')->where('number',$devicenum)->update(['screenurl'=>$url1])){
				$data['type'] = 'ok';
				echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功']);
				return;
			}else{
				$data['type'] = 'error';
				echo json_encode(['data'=>$data,'code'=>'400','message'=>'失败']);
				return;
			}
	    }
	}



	/**
	 * [screentime 查询评价设备截图毫秒时间戳]
	 * @return [int] [0 不截图  1 截图]
	 */
	public function screentime($id){
		//查询数据库 截图时间
		$screentime = Db::name('ph_take')->where('id',$id)->value('screentime');
		//当前时间戳 毫秒
		list($usec, $sec) = explode(" ", microtime());
   		$time = (float)sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000);
   		//当前时间减去数据库时间 如果大于1秒则返回 0 不截图
		$us = ($time-$screentime)/10;

		if($us>300){
			return 0;
		}else{
			return 1;
		}
	}



	/**
	 * [nowdown 确实是否收到关机命令 收到关机命令返回]
	 * @param  [string] $devicenum [取号设备编号]
	 * @param  [int] $down      [确认收到关机]
	 * @return [array] $data         [返回数据集]
	 */
	public function nowdown($devicenum,$down){
		//如果down为1则表示数据正确  
		if($down==1){
			// 如果是立即关机将关机状态更改为否
			if(Db::name('ph_take')->where('number',$devicenum)->update(['runtype'=>'0'])){
				$data['type'] = 'ok';
				echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功']);
				return;
			}else{
				$data['type'] = 'error';
				echo json_encode(['data'=>$data,'code'=>'400','message'=>'失败']);
				return;
			}
		}else{
			$data['type'] = 'error';
			echo json_encode(['data'=>$data,'code'=>'400','message'=>'数据错误']);
			return;
		}
	}



	/**
	 * [takebusiness 取号接口]
	 * @param  [string] $devicenum [取号设备编号]
	 * @param  [string] $businessid   [业务流水id]
	 * @param  [string] $idcard   [身份证号]
	 * @param  [string] $socialsecuritycard   [社保卡号]
	 * @param  [string] $mobile   [手机号]
	 * @param  [string] $ordernumber   [预约编号]
	 * @return [array]  $data      [返回数据集]
	 */
	public function takebusiness($devicenum,$businessid,$idcard,$socialsecuritycard,$mobile,$ordernumber,$matterid){
		// 预约编号
		// 根据预约编号查询电话号码和身份证
		if(!empty($ordernumber)){
			$data['ordernumber'] = $ordernumber;
			// 根据预约编号查询用户id
			$people = Db::name('wy_orderrecord')->where('number',$ordernumber)->field('peopleid,businessid')->find();
			if(empty($people)){
				$data2['type'] = 'error';
				echo json_encode(['data'=>$data2,'code'=>'400','message'=>'预约号错误']);
				return;
			}

			// 根据用户查询手机号和身份证号
			$peopleinfo = Db::name('wy_peopleinfo')->where('id',$people['peopleid'])->find();
			$mobile = $peopleinfo['mobile'];
			$data['mobile'] = empty($mobile)?0:$mobile;
			$data['idcard'] = empty($peopleinfo['idcard'])?0:$peopleinfo['idcard'];
			$data['priority'] = 2;
			$businessid = $people['businessid'];
		}
		//如果流水号为空则直接返回
		if(empty($businessid)){
			$data2['type'] = 'error';
			echo json_encode(['data'=>$data2,'code'=>'400','message'=>'数据错误']);
			return;
		}
		// 如果改业务锁死则返回
		if(Db::name('sys_business')->where('id',$businessid)->value('lock')==2){
			return;
		}	
		// 将该业务锁死 完成取号后释放
		Db::name('sys_business')->where('id',$businessid)->update(['lock'=>2]);

		//根据设备编号查询设备id
		$tid = Db::name('ph_take')->where('number',$devicenum)->where('usestatus',1)->value('id');

		//根据设备id查询业务id数据集
		$busid = Db::name('ph_takebusiness')->where('takeid',$tid)->value('businessid');

		//根据业务id号查询业务流水号
		
		$bus = Db::name('sys_business')->field('flownum,name,startnumber,maxnumber,summary,fromnum')->where('id',$businessid)->find();
		$flownum = $bus['flownum'];

		//根据业务id号查询业务名称
		$businessname = $bus['name'];
		//起始号码和最大人数
		$startnumber = $bus['startnumber'];
		$maxnumber = $bus['maxnumber'];

		//查询默认起始号码和最大人数
		$setup = Db::name('ph_setup')->find();

		if(!$startnumber){
			$startnumber = $setup['startnumber'];
		}
		if(!$maxnumber){
			$maxnumber = $setup['maxnumber'];
		}		

		$sum = $maxnumber+$startnumber;

		//将业务id转换成数据  判断如果业务id不在业务id数据集中则返回错误信息
		$busid = explode(',',$busid);
		if(!in_array($businessid,$busid)){
			$data1['type'] = 'error';
			echo json_encode(['data'=>$data1,'code'=>'400','message'=>'该业务无法取号']);
			Db::name('sys_business')->where('id',$businessid)->update(['lock'=>1]);
			return;
		}

		//当天日期
		$today = date('Ymd',time());
		$data['today'] = $today;
		$data['businessid'] = $businessid;
		//如果身份证号 社保号 手机号 预约编号 不为空则插入到数据库
		if($idcard){
			$data['idcard'] = $idcard;
		}
		if($socialsecuritycard){
			$data['socialsecuritycard'] = $socialsecuritycard;
		}
		if($mobile){
			$data['mobile'] = $mobile;
		}
	
		//查询排队表中当天该业务编号的流水编号
		$num = Db::name('ph_queue')->where("businessid=$businessid and today= '$today'")->order('id desc')->value('flownum');
	
		//如果当天没有该业务取号则设初始值为001
		if(!$num){
			$leng = 4-strlen($startnumber);
			$startnumber = substr(strval(10000),1,$leng).$startnumber;
			$num = $flownum.$startnumber;
		}else{
			$num = str_replace('Y','',$num);
			//替换掉A 业务流水号
			$num = str_replace($flownum, '', $num);
			
			$sunlen = strlen($num);//字符总长 用于截取
			//排号流水号+1
			$num += '1';
			
			$len = intval($num);	//转换成整型
			$sumlen = $len-$startnumber;

			if($sumlen>$maxnumber){
				$data1['type'] = 'error';
				echo json_encode(['data'=>$data1,'code'=>'400','message'=>'该业务今日取号已满,无法再取号']);
				Db::name('sys_business')->where('id',$businessid)->update(['lock'=>1]);
				return;
			}
			$len = strlen($len);	//整形的长度
			//如果长度小于3位则补0 否则直接加业务流水号
			if($len<4){
				//用于截取的长度
				$len = $sunlen-$len;
				//$num = $flownum.substr(strval(1000),1,$len).$num;
				//如果预约编号不为空在排号编号前面加Y 表示预约
				$num =$ordernumber? 'Y'.$flownum.substr(strval(10000),1,$len).$num:$flownum.substr(strval(10000),1,$len).$num;

			}else{	
				$num = $ordernumber?'Y'.$flownum.$num:$flownum.$num;
			}
			
		}
		// dump($num);die;
		// 流水编号
		$data['flownum'] = $num;
		$data['taketime'] = date('Y-m-d H:i:s',time());
		$data['takeid'] = $tid;
		if($matterid){
			$data['matterid'] = $matterid;
		}
		
		//将数据插入数据库
		if($id =Db::name('ph_queue')->insertGetId($data)){
			// 释放锁
			Db::name('sys_business')->where('id',$businessid)->update(['lock'=>1]);

			// 如果业务编号不为空才向社保发送编号
			// if(!empty($bus['fromnum'])){
			// 	//提交排号编号和业务信息到社保系统
			// 	$this->buttoninfo($num,$businessid,$id);
			// }
			
			
			// 根据业务id查询可办理的窗口id集合
			$wids = Db::name('sys_winbusiness')->where('businessid','like',"%,$businessid,%")->whereor('businessid','like',"%,$businessid")->whereor('businessid','like',"$businessid,%")->column('windowid');

			// 根据窗口id查询窗口编号
			$data1['windows'] = '';
			foreach ($wids as  $v) {
				$windownum = Db::name('sys_window')->where('id',$v)->value('fromnum');
				if($windownum){
					$data1['windows'] .= $windownum.',';
				}
			}
			$data1['windows'] = rtrim($data1['windows'],',');

			// 将字符串转成数组 进行排序
			$data1['windows'] = explode(',',$data1['windows']);
			// 使用自然排序法对字符串进行大小比较，不区分大小写
			sort($data1['windows']);

			// 将排序好的数组转成字符串
			$data1['windows'] = implode(',',$data1['windows']);
			
			// 等候人数+1  调用方法
			$this->waitcount($businessid);

			// 返回的data数据
			// 返回的数据 排队人数 业务编号 排队编号 当日序号
			// 如果有预约就统计预约的排队人数  否则统计总排队人数
			if($ordernumber){
				$data1['count'] = Db::name('ph_queue')->where('businessid',$businessid)->where('today',$today)->where("ordernumber!=''")->where('style',0)->count();

				//修改预约表取号状态
				Db::name('wy_orderrecord')->where('number',$ordernumber)->update(['status'=>1,'taketime'=>date('Y-m-d H:i:s',time())]);
			}else{
				$day = date('d',time());
				$data1['count'] = Db::name('sys_business')->where('id',$businessid)->where('day',$day)->value('waitcount');	
			}


			// 查询业务区域
			$region = Db::name('sys_business')->where('id',$businessid)->value('region');


			$data1['flownum'] = $num; //排队编号
			$data1['businessid'] = $businessid; //业务id
			$data1['businessname'] = $businessname;	//业务名称
			$data1['queueid'] = $id;	// 排号id
			$data1['region'] = $region; //业务区域
			$data1['summary'] = $bus['summary']; //业务备注
			echo json_encode(['data'=>$data1,'code'=>'200','message'=>'正常']);
			return;
		}
		
	}

	/**
	 * [waitcount 排队等候人数]
	 * @param  [type] $businessid [业务id ]
	 */
	public function waitcount($businessid){
		$day = date('d',time());
		$day1 = Db::name('sys_business')->where('id',$businessid)->value('day');
		// 如果不是当天 则数据清零 从当天重新计数
		// 否则排队人数+1
		if($day!=$day1){	
			Db::name('sys_business')->where('id',$businessid)->update(['day'=>$day,'waitcount'=>1]);
		}else{
			Db::name('sys_business')->where('id',$businessid)->setInc('waitcount');
		}
	}


	/**
	 * [test 提交排号编号和业务到社保系统]
	 * @param  string $num        [排号编号]
	 * @param  string $businessid [业务id]
	 * @return [type]             [description]
	 */
	protected function buttoninfo($num='A001',$businessid='1',$id){
		
		//分中心编号
		$node = Db::name('sys_thiscenter')->value('fromnum');
		//业务信息
		$buscontent = Db::name('sys_business')->field('fromnum,fromname,fromdescribe')->where('id',$businessid)->find();

		$soacp = new \SoapClient(null,array('location'=>"http://192.168.0.10:8076/sbxt/index.php/synchronization/centercall",'uri' =>'192.168.0.10:8076'));


		$datainter['node'] = $node;//分中心编号
		$datainter['number'] = $num;//排号编号
		$datainter['date'] = date('Y-m-d H:i:s',time());//当前时间
		$datainter['type']['id'] = $buscontent['fromnum'];//业务编号
		$datainter['type']['name'] = $buscontent['fromname'];//业务名称
		$datainter['type']['describe'] = $buscontent['fromdescribe'];//业务备注
		$datainter['takeid'] = $id;
		
		//提交取号信息
	    $json = json_encode($datainter);

	    //方法名称 中心服务器方法
	    $data = $soacp->takeinfo($json);

	    //$returndata = json_decode($data,TRUE);
	    if($data!='OK'){
	    	for ($i=0; $i <3 ; $i++) { 
	    		 $data = $soacp->takeinfo($json);
	    		 //$returndata = json_decode($data,TRUE);
	    		 if($data=='OK'){
	    		 	break;
	    		 }
	    	}
	    }
	}

	// 取号时判断窗口编号 如果是b开头的返回true 
	public function winname($windownum){
		$str = substr($windownum,0,1);
		if($str=='b'){
			return true;
		}else{
			return false;
		}
	
	}

}
