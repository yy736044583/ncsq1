<?php
namespace app\inter\controller;
use think\Db;
//导视机接口
class Dsdevice{

	/**
	 * 根据方法名跳转
	 * @param [string] $[action] [方法名]
	 * @param [string] $[devicenum] [设备编号]
	 */
    public function index(){ 
		$action = input('action');
		$devicenum = input('devicenum');
		//如果设备编号为空则返回
		if(empty(input('devicenum'))){
			echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到']);
			return;
		}
		//根据方法名跳转到各个方法
		switch ($action) {
			//心跳接口
			case 'dsdeviceheart':
				$this->dsdeviceheart($devicenum);
				break;
			//截图接口	
			case'picture':
				$this->picture($devicenum);
				break;
			//关机接口	
			case'nowdown':
				$this->nowdown($devicenum,input('down'));
				break;
			default:
				echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
				return;
				break;
		}
    }



    /**
     * [deviceheart 导视机设备心跳]
     * @param  [string] $devicenum [取号设备编号]
     * @return [array]  $data      [返回数据集]
     */
    
	public function dsdeviceheart($devicenum){
		//查询设备编号是否存在 不存在就创建
		if(!Db::name('ds_device')->field('id')->where('number',$devicenum)->find()){
			$data1['number'] = $devicenum;
			$data1['createtime'] = date('Y-m-d H:i:s',time());
			Db::name('ds_device')->insert($data1);
		}
		if(Db::name('ds_device')->where('number',$devicenum)->value('usestatus')!='1'){
			echo json_encode(['data'=>array(),'code'=>'201','message'=>'该设备未使用']);
			return;
		}

		//更新最后登陆时间
		$time = date('Y-m-d H:i:s',time());
		Db::name('ds_device')->where('number',$devicenum)->update(['lastlogin'=>$time]);

		//查询设备信息
		$list = Db::name('ds_device')
		->field('id,number,runtype,down,downtimehour,downtimemin')
		->where('number',$devicenum)->find();
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
		$data['screenshot'] = $this->screentime($list['id']);

		echo json_encode(['data'=>$data,'code'=>'200','message'=>'正常']);
		return;
	}



	/**
	 * 截图接口
	 * @param  [string] $devicenum [设备编号]
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
        $path =  ROOT_PATH . 'public' . DS . 'uploads'.DS.'dsdevicepicture'; // 接收文件目录
	        if (!file_exists($path)) {
	            if(!mkdir($path)){
	                echo '提交失败,自动创建文件夹失败';
	            }
	    }

	    if($_FILES['picture']['name'] != ''){
	    	//将截图文件保存到指定文件夹下  文件名为设备编号名称
	    	$url = $path.DS.$devicenum.'.png';
			copy ($_FILES['picture']['tmp_name'],$url )  or die ("Could not copy file");
			$url1 = 'dsdevicepicture/'.$devicenum.'.png';

			//将文件保存地址存到数据库
			if(Db::name('ds_device')->where('number',$devicenum)->update(['screenurl'=>$url1])){
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
	 * [screentime 查询导视机设备截图毫秒时间戳]
	 * @return [int] [0 不截图  1 截图]
	 */
	public function screentime($id){
		//查询数据库 截图时间
		$screentime = Db::name('ds_device')->where('id',$id)->value('screentime');
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
	 * @param  [string] $devicenum [设备编号]
	 * @param  [int] $down      [确认收到关机]
	 * @return [array] $data         [返回数据集]
	 */
	public function nowdown($devicenum,$down){
		//如果down为1则表示数据正确  
		if($down===1){
			// 如果是立即关机将关机状态更改为否
			if(Db::name('ds_device')->where('number',$devicenum)->update(['runtype'=>'0'])){
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

}
