<?php
namespace app\serverinter\controller;
use think\Db;
use think\Request;  
//窗口接口
class Device{
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
			case 'deviceheart':
				$this->deviceheart($devicenum);
				break;
			//更新评价状态 表示收到评价
			case 'upstyle':
				$this->upstyle($devicenum,input('id'),input('status'));
				break;
			//评价
			case 'upevaluate':
				$this->upevaluate($devicenum,input('id'),input('evaluate'));
				break;
			//员工信息
			case 'userinfo':
				$this->userinfo($devicenum);
				break;
			//更改登陆 暂离 回归
			case 'upusertype':
				$this->upusertype($devicenum,input('status'));
				break;
			//投诉
			case 'common':
				$this->common($devicenum,input('userid'),input('title'),input('content'),input('status'));
				break;
			default:
				echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
				return;
				break;
		}
    }

    /**
     * [deviceheart 评价器心跳]
     * @param  [string] $devicenum [设备编号]
     * @return [array]   data      [返回数据集]
     */
    public function deviceheart($devicenum){

		//查询设备编号是否存在 不存在就创建
		if(!$led = Db::name('pj_device')->field('id,usestatus,windowid,down,downtimehour,downtimemin,online')->where('number',$devicenum)->find()){
			$data1['number'] = $devicenum;
			$data1['createtime'] = date('Y-m-d H:i:s',time());
			Db::name('pj_device')->insert($data1);
		}
		//查询该设备相关数据
		// $led = Db::name('pj_device')
		// ->field('id,usestatus,windowid,down,downtimehour,downtimemin,online')
		// ->where('number',$devicenum)->find();

		//判断该设备是否在使用
		if($led['usestatus']!='1'){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该设备未使用'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//更新最后登陆时间
		$time = date('Y-m-d H:i:s',time());
		Db::name('pj_device')->where('number',$devicenum)->update(['lastlogin'=>$time]);

		//根据查询出来的定时关机时间跟当前时间比较判断是否关机
		$fortime = date('H:i',time());//当前的小时和分钟
		$downtime = $led['downtimehour'].':'.$led['downtimemin'];//定时关机的时间
		$down =  $downtime==$fortime?'1':'0';

		if($down=='1'){
			echo json_encode(['data'=>['id'=>'0','type'=>$down],'code'=>'200','message'=>'关机'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//窗口id  判断是否设置窗口
		$wid = $led['windowid'];
		if(!$wid){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'该设备未使用'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//根据窗口id查询员工id
		 $userid = Db::name('sys_window')->where('id',$wid)->value('workmanid');
		if($userid=='0'){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'员工未登陆'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//查询设备在线状态  3暂离状态 4回归 5登陆
		//$usertype = Db::name('sys_workman')->where('id',$userid)->value('online');
		// echo $led['online'];die;
		switch ($led['online']) {
			case '3':
				echo json_encode(['data'=>['id'=>'0','type'=>$led['online']],'code'=>'200','message'=>'暂离状态'],JSON_UNESCAPED_UNICODE);
				return;
				break;
			case '4':
				echo json_encode(['data'=>['id'=>'0','type'=>$led['online']],'code'=>'200','message'=>'回归状态'],JSON_UNESCAPED_UNICODE);
				return;
				break;
			case '5':
				echo json_encode(['data'=>['id'=>'0','type'=>$led['online']],'code'=>'200','message'=>'登陆状态'],JSON_UNESCAPED_UNICODE);
				return;
				break;
			default:break;
		}

		//查询发起评价的id
		$eid = Db::name('pj_evaluate')
		->where("workmanid='$userid' and evaluatestatus=0")
		->order('id')->value('id');

		if(!$eid){
			echo json_encode(['data'=>['id'=>'0','type'=>0],'code'=>'200','message'=>'无评价'],JSON_UNESCAPED_UNICODE);
			return;
		}

		echo json_encode(['data'=>['id'=>$eid,'type'=>2],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;
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
     * [upevaluate 评价]
     * @param  [string] $devicenum [设备编号]
     * @param  [int]  $id        [评价id]
     * @param  [int]  $evaluate  [评价等级]
     * @return [array]  data     [返回数据集]
     */
    public function upevaluate($devicenum,$id,$evaluate){
    	if(empty($id)||empty($evaluate)){
			echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误'],JSON_UNESCAPED_UNICODE);
			return;	
    	}
    	//更新评价数据
    	//评价等级
    	$data['evaluatelevel']	= $evaluate;
    	//评价设备id
    	$data['deviceid'] = Db::name('pj_device')->where('number',$devicenum)->value('id');
    	$time = date('Y-m-d H:i:s',time());
    	//评价时间
    	$data['evaluatetime'] = $time;
    	//评价状态  1评价成功
    	$data['evaluatestatus']	= 1;

    	if(Db::name('pj_evaluate')->where('id',$id)->update($data)){
    		
    		//评价成功将评价状态写入排号表 并更新评价时间
    		Db::name('ph_queue')->where('evaluateid',$id)->update(['evaluatetime'=>$time,'status'=>'4']);

    		echo json_encode(['data'=>['id'=>$id,'status'=>'1'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
    	}else{
    		echo json_encode(['data'=>['id'=>$id,'status'=>'0'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
    	}
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
    			if(Db::name('pj_device')->where('number',$devicenum)->update(['online'=>'2'])){
    				echo json_encode(['data'=>['userid'=>$userid],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
					return;
    			}else{
    				echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
					return;
    			}
    			break;
    		case '4':
    			Db::name('sys_workman')->where('id',$userid)->update(['online'=>'1']);
    			if(Db::name('pj_device')->where('number',$devicenum)->update(['online'=>'1'])){
    				echo json_encode(['data'=>['userid'=>$userid],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
					return;
    			}else{
    				echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
					return;
    			}
    			break;
    		case '5':
    			Db::name('sys_workman')->where('id',$userid)->update(['online'=>'1']);
    			if(Db::name('pj_device')->where('number',$devicenum)->update(['online'=>'1'])){
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
     * [userinfo 员工信息查询]
     * @param  [string] $devicenum [设备编号]
     * @return [array]            [返回数据]
     */
    public function userinfo($devicenum){
    	//根据设备编号查询窗口id
    	$wid = DB::name('pj_device')->where('number',$devicenum)->value('windowid');
    	if(!$wid){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'该设备未设置'],JSON_UNESCAPED_UNICODE);
			return;	
    	}

    	//根据窗口查询当前窗口员工id
    	$userid = Db::name('sys_window')->where("id='$wid' and valid=1")->value('workmanid');
    	//如果未查询到员工id则表示该设备员工未登陆
    	if(!$userid){
    		echo json_encode(['data'=>['type'=>'3'],'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
			return;
    	}

    	$list = Db::name('sys_workman')
    	->field('name,id,number,mobile,summary,photo,duty,promise,posttitle,starlevel,sectionid,windowid,business,userpost,politicalstatus')
    	->where("id='$userid' and online=1")
    	->find();

    	//如果没有查询到相关数据  表示员工不在线
    	if(!$list){
    		echo json_encode(['data'=>['type'=>'3'],'code'=>'200','message'=>'员工不在线'],JSON_UNESCAPED_UNICODE);
			return;	
    	}
		//员工照片的存放路径
		$request = request();
		$path1 = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public/uploads/';

		//查询确认是显示视频还是轮播图 0是轮播 1是视频 
		$sys = Db::name('sys_web')->where("name=0")->value('type');
		$banner = array();
		$redio = '';
		//如果是1则为播放视频  否则播放轮播图
		if($sys==1){
			$redio = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public/uploads/'.Db::name('pj_banner')->where("top=1 and type=2")->value('url');
		}else{
			$banner = Db::name('pj_banner')->where("top=1 and type=1")->column('url');
			foreach ($banner as $k => $v) {
				$banner[$k] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public/uploads/'.$v;
			}
		}

		//中心网站
		$web = Db::name('sys_web')->where('name=0')->value('weburl');
		//窗口名称
		$windowname = Db::name('sys_window')->where('id',$list['windowid'])->value('fromnum');
		//部门名称
		$section = Db::name('gra_section')->where('id',$list['sectionid'])->value('tname');

		$list['sys'] = $sys;
			$data = array(
				'Id'=>"$userid",
				'UserNumber'=>$list['number'],//工号
				'Name'=>$list['name'],	//姓名
				'Business'=>$list['business'],  //办理事项
				'Duty'=>$list['duty'],	//当前职责
				'Promise'=>$list['promise'],	//我的承诺
				'Telphone'=>$list['mobile'],	//部门电话
				'Photo'=>$path1.$list['photo'],	//照片
				'PostTitle'=>$list['posttitle'],	//岗位称号
				'Level'=>$list['starlevel'],	//星际
				'SectionName'=>$section,	//部门名称
				'WindowName'=>$windowname,	//窗口名称
				'type' =>'1',	//正常返回
				'banner'=>$banner,	//banner地址
				'redio' => $redio,	//视频地址
				'web'=>$web,
				'sys'=>"$sys",//判断轮播还是视频
				'userpost'=>$list['userpost'],	//职务
				'politicalstatus'=>$list['politicalstatus'],	//政治面貌	
				'summary'=>$list['summary'], //个人简介
				);
		echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
		return;
    }

    /**
     * [common 投诉]
     * @param  [string] $devicenum [设备编号]
     * @param  [int] $userid    [员工id]
     * @param  [string] $title     [投诉标题]
     * @param  [string] $content   [投诉内容]
     * @return [array]            [返回数据]
     */
    public function common($devicenum,$userid,$title,$content,$status){
    	$did = Db::name('pj_device')->where('number',$devicenum)->value('id');
    	$data['deviceid'] = $did;
        $data['workmanid'] = $userid;
        $data['uptime'] = date('Y-m-d H:i:s',time());
        //判断 1为音频投诉  否则为文字投诉
    	if($status=='1'){
    		$path =  ROOT_PATH . 'public' . DS . 'uploads'.DS.'complain'; // 接收文件目录
	        if (!file_exists($path)) {
	            if(!mkdir($path)){
	            	echo '文件创建失败';
	            	// echo json_encode('code'=>'400','data'=>array(),'msg'=>'自动创建文件夹失败');
	            	return;
	            }
	        }
	       
	        //开启事物  如果投诉人和投诉音频都插入成功则成功  否则回滚
	        Db::startTrans();
	        try{
	        	//将投诉时间和投诉人员 设备id插入投诉表中
	        	Db::name('pj_complain')->insert($data);
	        	$id = Db::name('pj_complain')->getLastInsID();

	        	//将临时文件放入指定文件夹 存入数据库
	        	// if($_FILES['file']['name'] != ""){
				$url = $path.DS.$_FILES['file']['name'];
				copy ($_FILES['file']['tmp_name'],$url )  or die ("Could not copy file"); 
				//选择放入数据库的地址
				$url1 = 'complain/'.$_FILES['file']['name'];

				$data1['complainid'] = $id;
				$data1['url'] = $url1;
				//更新截图到数据库
				DB::name('pj_complainfile')->insert($data1);
				// }
				
				// 提交事物
	        	Db::commit();
	        	//成功返回
	        	echo json_encode(['data'=>array(),'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
				return;  	
	        } catch(\Exception $e) {
	        	// 回滚事务
    			Db::rollback();
    			//失败返回
    			echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
	        	return;
	        }
    	}else{
    		//文字投诉
    		$data['title'] = $title;
    		$data['content'] = $content;

    		//将投诉时间和投诉人员 设备id插入投诉表中
	        if(Db::name('pj_complain')->insert($data)){
	        	echo json_encode(['data'=>array(),'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
				return;
	        }else{
	        	echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
	        	return;
	        }
    	}
    }




}