<?php
namespace app\integration\controller;
use think\Db;
use think\Request;  
//一体化政务中心数据接口
class Index{

	public function index(){

	}
	//获取个人服务的主题\部门
	public function category_theme(){
		$url = 'http://202.61.88.206/sczw-iface/category';
		$data['aid'] = '-1';	//区域
		$data['typeid'] = '1'; //1主题 2部门
		$nid = ['0'=>'1','1'=>'2'];//1个人服务  2法人服务 
		foreach ($nid  as $v) {
			$data['nid'] = $v; 
			$httpdata = $this->postData($url,$data);
			$returndata = json_decode($httpdata,true);

			$return = $returndata['data'];//数据
			$aid = $return['aid'];
			foreach ($return['list'] as $k => $val) {
				if(!empty($val['tid'])){
					$id = Db::name('gra_theme')->where('tid',$val['tid'])->value('id');
					if(!$id){
						$data1[$k]['aid'] = $aid;
						$data1[$k]['nid'] = $v;
						$data1[$k]['tid'] = $val['tid'];
						$data1[$k]['tname'] = $val['tname'];
						$data1[$k]['icon'] = $val['icon'];						
					}
				}
			}
			Db::name('gra_theme')->insertAll($data1);
		}
	}
	//获取个人服务的主题\部门
	public function category_section(){
		$url = 'http://202.61.88.206/sczw-iface/category';
		$data['aid'] = '-1'; //区域
		$data['typeid'] = '2'; //1主题 2部门
		$nid = ['0'=>'1','1'=>'2'];//1个人服务  2法人服务 
		foreach ($nid  as $v) {
			$data['nid'] = $v; 
			$httpdata = $this->postData($url,$data);
			$returndata = json_decode($httpdata,true);

			$return = $returndata['data'];//数据
			$aid = $return['aid'];
			foreach ($return['list'] as $k => $val) {
				if(!empty($val['tid'])){
					$id = Db::name('gra_theme')->where('tid',$val['tid'])->value('id');
					if(!$id){
						$data1[$k]['aid'] = $aid;
						$data1[$k]['nid'] = $v;
						$data1[$k]['tid'] = $val['tid'];
						$data1[$k]['tname'] = $val['tname'];
						$data1[$k]['icon'] = $val['icon'];						
					}
				}
			}
			Db::name('gra_section')->insertAll($data1);
		}
	}

	//办理形式
	public function handle(){
		$url = 'http://202.61.88.206/sczw-iface/handle/model';
		$data = $this->getData($url,'','30');
		$data = json_decode($data,true);
		// dump($data);die;
		if($data['code']=='0000'){
			$list = $data['data'];
			foreach ($list as $k => $v) {
				$id = Db::name('gra_handle')->where('sort',$v['sort'])->value('id');
				if(!$id){
					$arr[$k]['sort'] = $v['sort'];
					$arr[$k]['key'] = $v['key'];
					$arr[$k]['value'] = $v['value'];
					$arr[$k]['name'] = $v['name'];
				}
			}
			Db::name('gra_handle')->insertAll($arr);
		}
		// dump($data);
	}
	
	//事项列表		
	public function matterlist(){
		set_time_limit(300);
		$url = 'http://202.61.88.206/sczw-iface/service/matter/list';
		$data['dpmId'] = '0'; 
		$data['nid'] = '1'; //1个人服务  2法人服务 
		$data['aid'] = '-1'; //区域
		$data['themeId'] = '0'; //主题 部门
		$data['userid'] = 'c96460d0c1e5509465e105935d22e2fe'; 
		$data['keywords'] = '';
		$data['locale'] = '1';
		$data['verify'] = '3';
		$data['size'] = '20';
		$data['page'] = '1';

		//办理形式表 1窗口办理 2原件预审 3原件核验 5全程网办
		$handle = Db::name('gra_handle')->field('key,value')->select();
		//循环获取每个形式的事项
		foreach ($handle as $key => $val) {
			$data[$val['key']] = $val['value'];
			$httpdata = $this->postData($url,$data);
			$returndata = json_decode($httpdata,true);

			// 成功获取参数
			if($returndata['code']=='0000'){
				$data1 = $returndata['data'];
				$count = $data1['total'];//总条数
				$pages = $data1['pages'];//总页数
				//一页一页的获取事项
				for ($i=1; $i <=$pages ; $i++) { 
					$data['size'] = '20';
					$data['page'] = $i;
					$httpdata = $this->postData($url,$data);
					$returndata = json_decode($httpdata,true);
					// 如果获取成功 就将数据插入数据库
					if($returndata['code']=='0000'){
						$list = $returndata['data']['list'];
						
						foreach ($list as $k => $v) {
							$id = Db::name('gra_matter')->where('from_matterid',$v['matterid'])->value('id');
							
							//如果数据库改条数据不存在则存入
							if(!$id){
								$arr[$k]["from_matterid"] =$v['matterid'];
								$arr[$k]["tid"] =$v['tid'];
								$arr[$k]["tname"] =$v['tname'];//事项名
								$arr[$k]["online"] =empty($v['online'])?'0':$v['online'];
								$arr[$k]["store"] =empty($v['store'])?'0':$v['store'];
								$arr[$k]["order"] =empty($v['order'])?'0':$v['order'];
								$arr[$k]["type"] =empty($v['type'])?'0':$v['type'];
								$arr[$k]["department"] =$v['department']; //部门
								$arr[$k]["deptid"] =$v['deptid'];
								$arr[$k]["power"] =$v['power'];
								$arr[$k]["oldCodeId"] =$v['oldCodeId'];
								$arr[$k]["handle"] =$val['value']; //1窗口办理 2原件预审 3原件核验 5全程网办							
							}
						}
						if(!empty($arr)){
							Db::name('gra_matter')->insertAll($arr);
							$arr = array();
						}
					}
				}
			}
		}
	}	

	/**
	 * [detail1 咨询电话 监督电话 地址]
	 * @return [type] [description]
	 */
	public function detail1(){
		set_time_limit(1000);
		$url = 'http://202.61.88.206/sczw-iface/gddetail?tid=0&aid=-1&uid=c96460d0c1e5509465e105935d22e2fe';
		$matterid = Db::name('gra_matter')->field('tid,telephone')->select();
		foreach ($matterid as $k => $v) {
			$url1 = $url.'&id='.$v['tid'];
			$data = $this->getData($url1,'','300');
			$data = json_decode($data,true);
			$data = $data['data'];
			if(!empty($data['0'])){
				foreach ($data['0'] as $key => $val) {
					if(!Db::name('gra_matter')->where('telephone=""')->where('tid',$v['tid'])->value('id')){
						if($key==0){//资讯电话
							$data1['telephone'] = $val['content'];
						}	
						if($key==1){//资讯电话
							$data1['sphone'] = $val['content'];
						}
						if($key==2){//办理地址
							$data1['address'] = $val['content'];
						}
						Db::name('gra_matter')->where('tid',$v['tid'])->update($data1);	
					} 
				}			
			}
		}
	}

	//1基础信息 2受理标准 3申请材料 4办理流程 5中介服务 6设定依据 7常见问题
	public function item(){
		$url = 'http://202.61.88.206/sczw-iface/item?power=&aid=1&nid=1';
		$data = $this->getData($url,'','30');
		$data = json_decode($data,true);
		foreach ($data['data'] as $k => $v) {
			if(!Db::name('gra_item')->where('sort',$v['sort'])->value('id')){
				$data1[$k]['sort'] = $v['sort'];
				$data1[$k]['name'] = $v['name'];
				$data1[$k]['tid'] = $v['tid'];
			}
		}
		if(empty($data1)){
			Db::name('gra_item')->insertAll($data1);
		}
		
	}

	//受理标准详情
	public function item_detail_2(){
		set_time_limit(1000);
		$url = 'http://202.61.88.206/sczw-iface/gddetail?aid=16a93eccc43c4fa98fa5162897548135&uid=c96460d0c1e5509465e105935d22e2fe';
		$matterid = Db::name('gra_matter')->field('tid,id')->select();//查询事项id和tid(从行政来的事项id)
		$data1 = array();
		foreach ($matterid as $k => $v) {
			//拼接事项id和受理标准的标志 
			$url1 = $url.'&id='.$v['tid'].'&tid=2';
			$data = $this->getData($url1,'','30');
			$data = json_decode($data,true);
			$data = $data['data'];
			if(!empty($data)){
				//可能存在多条数据
				foreach ($data as $kk => $vv) {
					$map['matterid'] = $v['id'];
					if(!empty($vv['0']['content'])){
						$map['sort'] = $vv['0']['content'];
						$id = Db::name('gra_accept')->where($map)->value('id');
					}
					if(!$id){
						//每条数据获取内容
						foreach ($vv as $key => $val) {
							if($key==0){//获取序号
								$data1['sort'] = $val['content'];//序号
							}
							if($key==1){//获取内容
								$data1['name'] = $val['name']; //标题名称
								$data1['content'] = $val['content']; //内容
								$data1['matterid'] = $v['id'];//事项id
							} 
						}
						if(!empty($data1)){
							Db::name('gra_accept')->insert($data1);
						}
					}
				}			
			}
		}
	}
	//申请材料详情
	public function item_detail_3(){

		set_time_limit(1000);
		$url = 'http://202.61.88.206/sczw-iface/gddetail?aid=16a93eccc43c4fa98fa5162897548135&uid=c96460d0c1e5509465e105935d22e2fe';
		$matterid = Db::name('gra_matter')->field('tid,id')->select();//查询事项id和tid(从行政来的事项id)
		$data1 = array();
		foreach ($matterid as $k => $v) {
			//拼接事项id和受理标准的标志 
			$url1 = $url.'&id='.$v['tid'].'&tid=3';
			$data = $this->getData($url1,'','30');
			$data = json_decode($data,true);
			$data = $data['data'];
			
			if(!empty($data)){
				//可能存在多条数据
				foreach ($data as $kk => $vv) {
					if(!empty($vv['8']['content'])){
						$id = Db::name('gra_datum')->where('DatumID',$vv['8']['content'])->value('id');
					}
					if(!$id){
						//每条数据获取内容
						foreach ($vv as $key => $val) {
							if($key==8){//材料id 行政过来的id
								$data1['DatumID'] = empty($val['content'])?'0':$val['content'];
							}

							if($key==0){//序号
								$data1['sort'] = empty($val['content'])?'0':$val['content'];
							}
							if($key==1){//标题
								$data1['title'] = empty($val['content'])?'0':$val['content'];
							}
							if($key==2){//示范文本材料样本
								$data1['dnumber'] = empty($val['dnumber'])?'0':$val['dnumber'];
								$data1['filesname'] = empty($val['files']['name'])?'0':$val['files']['name'];
								$data1['files'] = empty($val['files']['download'])?'0':$val['files']['download'];
							}
							if($key==3){//纸质
								$data1['paper'] = empty($val['content'])?'0':$val['content'];
							}
							if($key==4){//电子
								$data1['electronic'] = empty($val['content'])?'0':$val['content'];
							}
							if($key==5){//详情介绍
								$data1['summary'] =empty($val['content'])?'0':$val['content'];
							}
							if($key==6){
								$data1[$val['name']] = empty($val['content'])?'0':$val['content'];
							}
							if($key==7){
								$data1[$val['name']] = empty($val['content'])?'0':$val['content'];
							}
							
							$data1['matterid'] = $v['id'];//事项id					
						}
						
						// 如果要插入的数据不为空则插入数据库
						if(!empty($data1)){
							Db::name('gra_datum')->insert($data1);
						}
					}					
				}				
			}
		}
	}

	/**
	 * [postData post提交]
	 * @param  [type] $url  [url]
	 * @param  [type] $data [参数]
	 * @return [type]       [description]
	 */
	public function postData($url, $data){        
	    $ch = curl_init();        
	    $timeout = 300;
	    $data = http_build_query($data);
	    curl_setopt ($ch, CURLOPT_HEADER, 0 );  
	    $header = array ();  
	    $header [] = 'Host:www.XXXX.co';  
	    $header [] = 'Connection: keep-alive';  
	    $header [] = 'User-Agent: ozilla/5.0 (X11; Linux i686) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1';  
	    $header [] = 'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';  
	    $header [] = 'Accept-Language: zh-CN,zh;q=0.8';  
	    $header [] = 'Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3';  
	    $header [] = 'Cache-Control:max-age=0';  
	    $header [] = 'Cookie:t_skey=p5gdu1nrke856futitemkld661; t__CkCkey_=29f7d98';  
	    $header [] = 'Content-Type:application/x-www-form-urlencoded';  
	    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header );           
	    curl_setopt($ch, CURLOPT_URL, $url);   //请求地址      
	    curl_setopt($ch, CURLOPT_POST, true);  //post请求     
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);      //数据  
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //当CURLOPT_RETURNTRANSFER设置为1时 $head 有请求的返回值      
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);    //设置请求超时时间    
	    $handles = curl_exec($ch);        
	    curl_close($ch);          
	    return $handles;  
	} 

	//get提交
	function getData($url,$data,$timeout = 5){
		 if($url == "" || $timeout <= 0){
		 	return false;
		 }
		 $url = $url;
		 $con = curl_init((string)$url);
		 curl_setopt($con, CURLOPT_HEADER, false);
		 curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
		 curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);
		  
		 return curl_exec($con);
	}

}