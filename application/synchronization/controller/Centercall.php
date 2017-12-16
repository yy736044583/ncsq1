<?php
namespace app\synchronization\controller;
use think\Db;
use think\Request;  
//中心接口
class Centercall{
	/**
	 * 中心服务器 
	 * 叫号中转
	 * 业务更新中转
	 */
    public function index(){ 

 		$classExample = array();
        $server= new \SoapServer(null,array('uri'=>"http://127.0.0.1:8076/",'classExample'=>$classExample)); 
        $server->setClass(get_class($this)); 
        $server->handle();

	}


	// 叫号
	public function sbcall($json){
		$data = json_decode($json,true);
		$callType =$data['callType']; //状态 确定是叫号还是其他
		$node =$data['node'];	//分中心编号
		
		$data['list'] = implode(',',$data['list']);
		
		if(empty($callType)||empty($node)){
			$list = json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
			return $list;
		}
		$theip = Db::name('sys_thecenter')->where('fromnum',$node)->value('subcenterip');

		$url = $theip.'/sbxt/index.php/synchronization/call';
		// return $data;
		// url方式提交
		$httpstr = http($url, $data, 'GET', array("Content-type: text/html; charset=utf-8"));
		

		return $httpstr;

	}

	// 获取更新业务  置后同步各个分中心
	public function business(){
		// 查询各个分中心编号和ip
		$node = Db::name('sys_thecenter')->field('fromnum,subcenterip')->select();

		//实例话类  开启客户端
	    // $soacp = new \SoapClient("http://192.168.0.113:8080/cdsb/services/callSystemService?wsdl");
	    $soacp = new \SoapClient(null,array('location'=>"http://192.168.0.10:8076/sbxt/index.php/admin/test",'uri' => 'soap_server.php'));

	    foreach ($node as $k => $v) {
	    	$json = json_encode(['node'=>$v['fromnum']]);
	    	
		    //方法名称  getConfigMessage
		    $data = $soacp->getConfigMessage($json);

		    //将json转换成数组
		    $returndata = json_decode($data,TRUE);
		   
		    if(empty($returndata['type'])){
		    	for ($i=0; $i <3 ; $i++) { 
		    		 $data = $soacp->getConfigMessage($json);
		    		 $returndata = json_decode($data,TRUE);
		    		 if(!empty($returndata['type'])){
		    		 	break;
		    		 }
		    	}
		    }

			//如果返回的网点编号的传输过去的编号相同则执行下一步
			if($returndata['node'] == $v['fromnum']){
				// 调用synchro方法 同步到分中心
				$rest = $this->synchro($v['subcenterip'],$returndata['type']);
	
			}


	    }

	}

	/**
	 * [synchro 业务信息同步数据到分中心]
	 * @param  [type] $url      [分中心ip]
	 * @param  [type] $fromnum  [业务编号]
	 * @param  [type] $name     [业务名称]
	 * @param  [type] $describe [业务备注]
	 */
	public function synchro($url,$type){

		//实例话类  开启客户端
	    $soacpb = new \SoapClient(null,array('location'=>"http://192.168.0.10:8076/sbxt/index.php/synchronization/business",'uri' =>$url));
	    
	    $data = json_encode($type);

	    $returndata =  $soacpb->businesssyn($data);

	    if($returndata !=='ok'){
	    	for ($i=0; $i <3 ; $i++) { 
	    		 $returndata = $soacpb->businesssyn($data);
	    		 if($returndata ==='ok'){
	    		 	return $returndata;
	    		 }
	    	}
	    }else{
	    	return $returndata;
	    }
	}


	/**
	 * [takeinfo 将分中心提交的取号数据提交到社保局]
	 * @param  string $json        [数据集合 json格式]
	 * @return [type]             [description]
	 */
	public function takeinfo($json){

		$soacp = new \SoapClient("http://192.168.0.113:8080/cdsb/services/callSystemService?wsdl");

	    //方法名称  sendNumberMessage
	    $data = $soacp->sendNumberMessage($json);

	    $returndata = json_decode($data,TRUE);
	    if($returndata['returnMessage']!='OK'){
	    	for ($i=0; $i <3 ; $i++) { 
	    		 $data = $soacp->sendNumberMessage($json);
	    		 $returndata = json_decode($data,TRUE);
	    		 if($returndata['returnMessage']=='OK'){
	    		 	return $returndata['returnMessage'];
	    		 	break;
	    		 }
	    	}
	    }
	}

}