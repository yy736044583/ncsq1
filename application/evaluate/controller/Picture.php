<?php  
namespace app\evaluate\controller;
use think\Request;  
use think\Controller;
use think\Db;
//截图上传
class Picture extends \think\Controller{

	public function index(){
		$action = input('action');
		switch ($action) {
			case 'addevaluate':
				$this->addevaluate(input('userid'),input('status'));
				break;
			default:
				# code...
				break;
		}
	}
	//根据评论或者截图添加到数据库
	public function addevaluate($userid,$status){
				
		if($status=='1'){
		  	//文件夹是否存在   不存在创建
	    	$path =  ROOT_PATH . 'public' . DS . 'uploads'.DS.'evaluate'; // 接收文件目录
	        if (!file_exists($path)) {
	            if(!mkdir($path)){
	                echo '提交失败,自动创建文件夹失败';
	            }
	        }
	        //将临时文件移到指定文件夹
	    	if($_FILES['file']['name'] != ""){
				$url = $path.DS.time().$userid.'.png';
				copy ($_FILES['file']['tmp_name'],$url )  or die ("Could not copy file"); 
				$url1 = 'evaluate/'.time().$userid.'.png';
				//添加时间

				$data1['workmanid'] = $userid;
				// $data1['evaluatelevel'] = $status;
				$data1['photobefor'] = $url1;
				$data1['evaluatestatus'] = '0'; //未处理
				$data1['evaluatetypeevaluatetype'] = '1'; //视频
				$time = date('Y-m-d H:i:s',time());
				$data1['evaluatetime'] = $time;
				//添加截图到数据库  返回该条数据ID和路径	
				if(DB::name('pj_evaluate')->insert($data1)){
					$Id = Db::name('pj_evaluate')->getLastInsID();
					$data = ['type'=>'OK','id'=>$Id]; 
				}
			}
		}else{
			$data1['workmanid'] = $userid;
			$data1['evaluatelevel'] = $status;
			$data1['evaluatestatus'] = '0';
			$data1['evaluatetype'] = '0';  //评价

			//创建评论 返回该评论ID
			if(DB::name('pj_evaluate')->insert($data1)){
				$Id = Db::name('pj_evaluate')->getLastInsID();
				$data = ['type'=>'OK','id'=>$Id];
			}
		}
		echo json_encode($data);
		return;
	}



}