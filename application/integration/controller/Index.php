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
					if(!Db::name('gra_theme')->where('tid',$val['tid'])->value('id')){
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
					if(!Db::name('gra_theme')->where('tid',$val['tid'])->value('id')){
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
				if(!Db::name('gra_handle')->where('sort',$v['sort'])->value('id')){
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
				for ($i=0; $i <$pages ; $i++) { 
					$data['size'] = '20';
					$data['page'] = $i;
					$httpdata = $this->postData($url,$data);
					$returndata = json_decode($httpdata,true);
					// 如果获取成功 就将数据插入数据库
					if($returndata['code']=='0000'){
						$list = $returndata['data']['list'];
						foreach ($list as $k => $v) {
							//如果数据库改条数据不存在则存入
							if(!Db::name('gra_matter')->where('from_matterid',$v['matterid'])->value('id')){}
								$arr[$k]["from_matterid"] =$v['matterid'];
								$arr[$k]["tid"] =$v['tid'];
								$arr[$k]["tname"] =$v['tname'];//事项名
								$arr[$k]["online"] =($v['online'])?'0':$v['online'];
								$arr[$k]["store"] =empty($v['store'])?'0':$v['store'];
								$arr[$k]["order"] =empty($v['order'])?'0':$v['order'];
								$arr[$k]["type"] =empty($v['type'])?'0':$v['type'];
								$arr[$k]["department"] =$v['department']; //部门
								$arr[$k]["deptid"] =$v['deptid'];
								$arr[$k]["power"] =$v['power'];
								$arr[$k]["oldCodeId"] =$v['oldCodeId'];
								$arr[$k]["handle"] =$val['value']; //1窗口办理 2原件预审 3原件核验 5全程网办
						}
						if(!empty($arr)){
							Db::name('gra_matter')->insertAll($arr);
						}
					}
				}
			}
		}


		// dump($returndata);
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