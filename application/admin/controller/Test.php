<?php 
namespace app\admin\Controller;
use think\Controller;
use think\View;
use think\Db;
use think\Cache;
class Test extends \think\Controller{


	public function index(){
		// $lock = 1;
		// $this->locktest($lock);
		// Cache::remember('auth',function(){
		// 	$data = Db::name('sys_auth')->select();
		//     return $data;
		// });
		// $data = Db::name('sys_auth')->select();
		//$data = Db::name('sys_auth')->where('au_type=1')->cache(60)->paginate(12);
		//$list = $data->all();
		// cache('auth',$data,120);			
		// $list1 = cache('auth');
		//dump($list);
		// $data = Db::name('sys_auth')->select();
		// cache::clear();
	// $list = $this->make_tree($data);
		// dump($list);die;
		// $this->assign('list',$list);
		// return $this->fetch();
	//}

	// public function make_tree($list,$pk='au_id',$pid='au_parent',$child='child',$root=0){
	//     $tree=array();
	//     foreach($list as $key=> $val){

	//         if($val[$pid]==$root){
	//             //获取当前$pid所有子类 
	//                 unset($list[$key]);
	//                 if(! empty($list)){
	//                     $child=$this->make_tree($list,$pk,$pid,$child,$val[$pk]);
	//                     if(!empty($child)){
	//                         $val['child']=$child;
	//                     }                   
	//                 }              
	//                 $tree[]=$val; 
	//         }
	//     } 
	//     return $tree;
	// }


		$businessid = input('businessid');
	//取号
		//定义一个要发送的目标URL；
		// $url = "http://192.168.0.10:8076/sbxt/index.php/inter/call";
		$url = "http://127.0.0.1:8076/ncsq/index.php/inter/take";
		//定义传递的参数数组；
		$data['action']='takebusiness';
		// $data['action']='call';
		// $data['devicenum']='awifi20:ae:42:b3:fe:4e';//call
		$data['devicenum']='1093235231';//take
		$data['businessid']= $businessid;//take
		$data['matterid']= '1';//take
		$data['mobile']= '15928552357';//take
		// $data['userid'] = 4;//call
		// $data['id'] = 92;//call
		//$data['windowid'] = 1;//call
		// $data['loginname'] = 'tt2';
		// $data['loginpass'] = '123456';
		// $data['wid'] = '4';

		//定义返回值接收变量；
		$httpstr = http($url, $data, 'GET', array("Content-type: text/html; charset=utf-8"));
		//将json转换成数组 
		//$httpdata = json_decode($httpstr,TRUE);
		dump($httpstr);



		// $node = input('node');
		// //return $node;
		// $data = [['id'=>'01',
		// 		'name'=>'A岗业务',
		// 		'describe'=>'单位参保,缴费基数核定,档案认定',
		// 	],
		// 	[
		// 		'id'=>'02',
		// 		'name'=>'B岗业务',
		// 		'describe'=>'个人参保,在职转退休,缴费证明打印',
		// 	],
		// 	[
		// 		'id'=>'03',
		// 		'name'=>'C岗业务',
		// 		'describe'=>'个人参保',
		// 	]
		// ];
		//return json_encode(['node'=>$node,'type'=>$data]);	
		//
		//return json_encode(['returnMessage'=>'no']);
		

/*
	//软叫号接口测试
		$url = "http://192.168.0.10:8076/sbxt/index.php/synchronization/Centercall";
		$data['callType'] = '03';
		$data['node'] = '510140';
		// $data['id'] = '01';
		// $data['number'] = 'A005';
		$data['windowNumber'] = '02';
		// $data['oldnumber'] = 'A001';
		$data['online'] = 3;

		//定义返回值接收变量；
		$httpstr = http($url, $data, 'GET', array("Content-type: text/html; charset=utf-8"));
		//将json转换成数组 
		$httpdata = json_decode($httpstr,TRUE);
		dump($httpdata);

*/
/*
		$url = "http://192.168.0.10:8076/sbxt/index.php/fromtable/index";
		$data['devicenum'] = '1';
		// $data['action'] = 'section';
		// $data['action'] = 'matter';
		// $data['sectionid'] = '3';
		// $data['action'] = 'showfile';
		// $data['matterid'] = '1';
		$data['action'] = 'sousuo';
		$data['name'] = '个人';

		//定义返回值接收变量；
		$httpstr = http($url, $data, 'GET', array("Content-type: text/html; charset=utf-8"));
		//将json转换成数组 
		$httpdata = json_decode($httpstr,TRUE);
		dump($httpstr);


*/
	// $wid = 24;
	// $qid = 1484;
	// $flownum = 'A077';
	// $list = cachequeue($wid,$qid,$flownum,1);
	// $list = takepeople(1);
	// $list = takepeople(1);
	//dump($list);	
	//
	

	// $classExample = array();
 //    $server= new \SoapServer(null,array('uri'=>"http://127.0.0.1/",'classExample'=>$classExample)); 
 //    $server->setClass(get_class($this)); 
 //    $server->handle();



	}
/*
	public function getConfigMessage(){
		$node = '0002_01';
		//return $node;
		$data = [['id'=>'01',
				'name'=>'A岗业务',
				'describe'=>'单位参保,缴费基数核定,档案认定',
			],
			[
				'id'=>'02',
				'name'=>'B岗业务',
				'describe'=>'个人参保,在职转退休,缴费证明打印',
			],
			[
				'id'=>'03',
				'name'=>'C岗业务',
				'describe'=>'个人参保2',
			],
			[
				'id'=>'04',
				'name'=>'D岗业务',
				'describe'=>'个人参保1',
			]
		];
		return json_encode(['node'=>$node,'type'=>$data]);	
		
		//return json_encode(['returnMessage'=>'no']);
		
	}
*/

	// 缩略图
	// public function image(){
	// 	$path = 'D:\phpStudy\WWW\sbxt\public\uploads\matter/20170712/9ae123b3d861a995951a412cce64d3e3.jpg';
	// 	// $path = ROOT_PATH . 'public' . DS . 'uploads'.DS.'takepicture/160eadc66.png';
	// 	// echo $path;die;
	// 	$images = \think\Image::open($path);
	// 	$images->thumb(150, 150)->save(ROOT_PATH . 'public' . DS . 'uploads'.DS.'takepicture/thumb.png');
	// }	
/*	
	public function imagick(){
		if(!extension_loaded('imagick'))  
	   {    
	     echo '没有找到imagick！' ; die; 
	   } 
		$im = new imagick( 'a.jpg' );
		// resize by 200 width and keep the ratio
		$im->thumbnailImage( 200, 0);
		// write to disk
		$im->writeImage( 'a_thumbnail.jpg' );
	}
	*/

	public function call(){
		$soacp = new \SoapClient(null,array('location'=>"http://192.168.0.10:8076/sbxt/index.php/synchronization/Centercall",'uri' => 'soap_server.php', 'connection_timeout'=>0.1));

		$data['node'] = '510140';
		$data['callType'] = '03';
		$data['windowNumber'] = '02';
		$data['business'] = '01';
		$data['number'] = 'A1001';
		$data['list'] = ['0'=>'01','1'=>'02','2'=>'03'];
		$data['online'] = '4';
		
		$json = json_encode($data);
		// dump($json);die;
		$data1 = $soacp->sbcall($json);
		dump($data1);
	}

/*
	public function locktest(){
		$sum = '';
		for ($i=0; $i <10000000 ; $i++) { 
			$sum += $i;
		}
		return $sum;			
	}

	public function filelock(){

		$file = fopen("test.txt","w+");
		if(!$file){
			echo '文件被锁了';
		}
	    if(flock($file,LOCK_EX)){
	        $a = $this->locktest();
	        fwrite($file,$a);
	        flock($file,LOCK_UN);
	    }else{
	        echo  "文件正在被其他程序占用" ;
	    }
	    fclose($file);
	    clearstatcache();
	}
	*/


}