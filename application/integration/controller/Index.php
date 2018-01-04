<?php
namespace app\integration\controller;
use think\Db;
use think\Request;  
//一体化政务中心数据接口
class Index{
	//定义获取那个地区的数据
	protected $district='顺庆区';

	public function __construct(){
		$district = $this->district;
	}
	public function index(){

	}
	//获取所有地区和其编码
	public function district(){
		set_time_limit(1500);
		$code = 'f6b98d8a08a14858ad09a0ad4cc9ee5b';
		$url = 'http://202.61.88.206/sczw-iface/area?&code='.$code;
		$data = $this->getData($url,'','30');
		$data = json_decode($data,true);
		$data = $data['data'];
	
		if(!empty($data['0'])){
			foreach ($data['0'] as $k => $v) {
				if(!Db::name('gra_district')->where('from_id',$k)->find()){
					$data1['name'] = $v;
					$data1['from_id'] = $k;
					$id = Db::name('gra_district')->insertGetId($data1);

					$this->districtchild('0',$k,$id);
				}
			}
		}	
	}
	//获取所有地区下级数据
	public function districtchild($lv,$code,$id){
		$url = 'http://202.61.88.206/sczw-iface/area?&code='.$code;
		$data = $this->getData($url,'','30');
		$data = json_decode($data,true);
		$data = $data['data'];
	
		if(!empty($data['0'])){
			foreach ($data['0'] as $k => $v) {
				if(!Db::name('gra_district')->where('from_id',$k)->find()){
					$data1['name'] = $v;
					$data1['from_id'] = $k;
					$lv1 =  $lv+1;
					$data1['level'] = $lv1;
					$data1['parent'] = $id;
					$newid = Db::name('gra_district')->insertGetId($data1);
					
					$this->districtchild($lv1,$k,$newid);
				}
			}
		}else{
			return;
		}
	}
	//获取个人服务的主题\部门
	public function category_theme(){
		$url = 'http://202.61.88.206/sczw-iface/category';
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$data['aid'] = $aid;	//区域
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
					$id = Db::name('gra_theme')->where('tid',$val['tid'])->where('nid',$v)->value('id');
					if(!$id){
						$data1[$k]['aid'] = $aid;
						$data1[$k]['nid'] = $v;
						$data1[$k]['tid'] = $val['tid'];
						$data1[$k]['tname'] = $val['tname'];
						$data1[$k]['icon'] = $val['icon'];						
					}
				}
			}
			if(!empty($data1)){
				Db::name('gra_theme')->insertAll($data1);
			}
		}
	}
	//获取个人服务的主题\部门
	public function category_section(){
		$url = 'http://202.61.88.206/sczw-iface/category';
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$data['aid'] = $aid;	//区域
		$data['typeid'] = '2'; //1主题 2部门
		$nid = ['0'=>'1','1'=>'2'];//1个人服务  2法人服务 
		foreach ($nid  as $v) {
			$data['nid'] = $v; 
			$httpdata = $this->postData($url,$data);
			$returndata = json_decode($httpdata,true);
			$data1 = array();
			$return = $returndata['data'];//数据
			
			foreach ($return['list'] as $k => $val) {
				if(!empty($val['tid'])){
					$id = Db::name('gra_section')->where('tid',$val['tid'])->value('id');

					if(!$id){
						$data1[$k]['aid'] = $aid;
						$data1[$k]['tid'] = $val['tid'];
						$data1[$k]['tname'] = $val['tname'];
						$data1[$k]['icon'] = $val['icon'];	
						if($v==2){
							$data1[$k]['legal'] = 1; //法人服务
							$data1[$k]['nid'] = '0'; //个人服务
						}else{
							$data1[$k]['nid'] = 1; //个人服务
							$data1[$k]['legal'] = '0'; //法人服务
						}					
					}else{
						if($v==2){
							$data2['legal'] = 1; //法人服务
							Db::name('gra_section')->where('id',$id)->update($data2);
						}
					}
				}
			}
			if(!empty($data1)){
				Db::name('gra_section')->insertAll($data1);
			}
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
		
	}
	
	//事项列表	部门	
	public function matterlist(){
		set_time_limit(1500);
		$url = 'http://202.61.88.206/sczw-iface/service/matter/list';
		$data['dpmId'] = '0'; 
		// $data['dpmId'] = '9356520acf91449dac044da0c9f81666'; 
		//$data['nid'] = '2'; //1个人服务  2法人服务 
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$data['aid'] = $aid;
		// $data['aid'] = 'f6b98d8a08a14858ad09a0ad4cc9ee5b'; //区域
		$data['themeId'] = '0'; //主题 部门
		$data['userid'] = 'c96460d0c1e5509465e105935d22e2fe'; 
		$data['keywords'] = '';
		$data['size'] = '20';
		$data['page'] = '1';
		$data['online'] = '5';
		$data['locale'] = '1';
		$data['auditing'] = '2';
		$data['verify'] = '3';

		$nid = ['0'=>'1','1'=>'2'];//1个人服务  2法人服务 
		//办理形式表 1窗口办理 2原件预审 3原件核验 5全程网办
		$handle = Db::name('gra_handle')->field('key,value')->select();
		foreach ($nid as $kk => $vv) {
			$data['nid'] = $vv; //1个人服务  2法人服务 
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
									//$arr = $this->detailinfo($v['tid']);//基本信息
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
									//$arr[$k]["handle"] =$val['value']; //1窗口办理 2原件预审 3原件核验 5全程网办	
									if($vv==2){
										$arr[$k]['legal'] = 1; //法人服务
										$arr[$k]['nid'] = '0'; //个人服务
									}else{
										$arr[$k]['nid'] = 1; //个人服务
										$arr[$k]['legal'] = '0'; //法人服务
									}
									
									// Db::name('gra_matter')->insert($arr);
								}else{
									if($vv==2){
										$data2['legal'] = 1; //法人服务
										Db::name('gra_matter')->where('id',$id)->update($data2);
									}
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
			// dump($arr);die;			
		}
	
	}	
	//事项列表  主题
	public function matterlist_theme(){
		set_time_limit(1500);
		$url = 'http://202.61.88.206/sczw-iface/service/matter/list';
		$data['dpmId'] = '0'; 
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$data['aid'] = $aid; //地区
		$data['themeId'] = ''; //主题id
		$data['userid'] = 'c96460d0c1e5509465e105935d22e2fe'; 
		$data['keywords'] = '';
		$data['size'] = '20';
		$data['page'] = '1';

		$theme = Db::name('gra_theme')->field('id,tid,nid')->select();
		//办理形式表 1窗口办理 2原件预审 3原件核验 5全程网办
		$handle = Db::name('gra_handle')->field('key,value')->select();
		foreach ($theme as $kk => $vv) {
			$data['themeId'] = $vv['tid']; //主题id
			// dump($data);die;
			//循环获取每个形式的事项
			foreach ($handle as $key => $val) {
				$data[$val['key']] = $val['value'];
				$httpdata = $this->postData($url,$data);
				$returndata = json_decode($httpdata,true);
				// dump($returndata);die;
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
								// echo $id;die;
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
									$arr[$k]["themeid"] =$vv['id']; //主题id
									if($vv['nid']==2){
										$arr[$k]['legal'] = 1; //法人服务
										$arr[$k]['nid'] = '0'; //个人服务
									}else{
										$arr[$k]['nid'] = 1; //个人服务
										$arr[$k]['legal'] = '0'; //法人服务
									}
									
									// Db::name('gra_matter')->insert($arr);
								}else{
									if(!empty($vv['id'])){
										$data2['themeid'] = $vv['id']; //主题id
										Db::name('gra_matter')->where('id',$id)->update($data2);
									}
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
			// dump($arr);die;			
		}
	
	}	

	/**
	 * [detail1 咨询电话 监督电话 地址]
	 * [tid 0电话信息 1详细信息 2受理标准 3申请材料 4办理流程 5中介服务 6设定依据 7常见问题]
	 * @return [type] [description]
	 */
	public function detail1(){
		set_time_limit(1500);
		$url = 'http://202.61.88.206/sczw-iface/gddetail?tid=0&uid=c96460d0c1e5509465e105935d22e2fe';
		$matter = Db::name('gra_matter')->where("telephone=''")->field('tid,id')->select();
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		foreach ($matter as $k => $v) {
			$url1 = $url.'&id='.$v['tid'].'&aid='.$aid;
			$data = $this->getData($url1,'','300');
			$data = json_decode($data,true);
			$data = $data['data'];

			if(!empty($data['0'])){
				foreach ($data['0'] as $key => $val) {
					if($key==0){//资讯电话
						$data1['telephone'] = $val['content'];
					}	
					if($key==1){//资讯电话
						$data1['sphone'] = $val['content'];
					}
					if($key==2){//办理地址
						$data1['address'] = $val['content'];
					}
					Db::name('gra_matter')->where('id',$v['id'])->update($data1);	

				}			
			}
		}
	}
/*
	//在插入事项时获取 一起插入
	public function detail($tid){
		set_time_limit(1000);
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$url = 'http://202.61.88.206/sczw-iface/gddetail?tid=0&uid=c96460d0c1e5509465e105935d22e2fe';
		$url1 = $url.'&id='.$tid.'&aid='.$aid;
		$data = $this->getData($url1,'','300');
		$data = json_decode($data,true);
		$data = $data['data'];
		if(!empty($data['0'])){
			foreach ($data['0'] as $key => $val) {
				if($key==0){//资讯电话
					$data1['telephone'] = $val['content'];
				}	
				if($key==1){//资讯电话
					$data1['sphone'] = $val['content'];
				}
				if($key==2){//办理地址
					$data1['address'] = $val['content'];
				}
			}
			return $data1;			
		}
	}
*/
	//事项详情
	//tid 0电话信息 1详细信息 2受理标准 3申请材料 4办理流程 5中介服务 6设定依据 7常见问题
	public function detailinfo(){
		set_time_limit(2500);
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$url ='http://202.61.88.206/sczw-iface/gddetail?tid=1&uid=c96460d0c1e5509465e105935d22e2fe';
		$matter = Db::name('gra_matter')->where("hierarchy=''")->field('tid,id')->select();
		foreach ($matter as $k => $v) {
			$url1 = $url.'&id='.$v['tid'].'&aid='.$aid;
			$data = $this->getData($url1,'','300');
			$data = json_decode($data,true);
			$data = $data['data'];

			if(!empty($data['0'])){
				foreach ($data['0'] as $key => $val) {
					switch ($key) {
						case '1':
							$data1['hierarchy'] = $val['content'];break;//hierarchy 行使层级
						case '2':
							$data1['service_object'] = $val['content'];break;//service_object 服务对象
						case '3':
							$data1['operation'] = $val['content'];break;//operation 运行系统
						case '4':
							$data1['enforcement'] = $val['content'];break;//enforcement 实施机构 
						case '5':
							$data1['matter_type'] = $val['content'];break;//matter_type 事项类型 
						case '6':
							$data1['other_section'] = $val['content'];break;//other_section 其他共同办理部门
						case '7':
							$data1['limitday'] = $val['content'];break;//limitday 办理时限
						case '8':
							$data1['terrace'] = $val['content'];break;//terrace 事项办理承载平台
						case '9':
							$data1['number'] = $val['content'];break;//number 办事者到办事现场次数
						case '10':
							$data1['content'] = $val['content'];break;//content 服务内容
						case '11':
							$data1['theme'] = $val['content'];break;//theme 服务主题分类
						case '12':
							$data1['handle_type'] = $val['content'];break;//handle_type 办理类型
						case '13':
							$data1['online'] = $val['content'];break;//onlin 是否支持网上预约
						case '14':
							$data1['levels'] = $val['content'];break;//levels 认证等级需求	
						case '15':
							$data1['intake'] = $val['content'];break;//intake 是否纳入省政务服务和资源交易服务中心
						case '16':
							$data1['charge'] = $val['content'];break;// charge 收费情况  
						case '17':
							$data1['online_pay'] = $val['content'];break;//online_pay 是否支持在线支付
						case '18':
							$data1['express'] = $val['content'];break;// express 是否支持快递取件
						case '19':
							$data1['visit'] = $val['content'];break;// visit 是否支持上门收取申请材料
						case '20':
							$data1['intermediary'] = $val['content'];break;//intermediary 涉及的中介机构  
						case '21':
							$data1['time'] = $val['content'];break;	//time 办理时间
						default:
							break;
					}					
				}
				Db::name('gra_matter')->where('id',$v['id'])->update($data1);	
			}
			// return $data1;
		}
	}


	//1基础信息 2受理标准 3申请材料 4办理流程 5中介服务 6设定依据 7常见问题
	public function item(){
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$url = 'http://202.61.88.206/sczw-iface/item?power=&nid=1&aid='.$aid;
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
	//tid 0电话信息 1详细信息 2受理标准 3申请材料 4办理流程 5中介服务 6设定依据 7常见问题
	public function item_detail_2(){
		set_time_limit(1000);
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$url = 'http://202.61.88.206/sczw-iface/gddetail?&uid=c96460d0c1e5509465e105935d22e2fe&aid='.$aid;
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
	//tid 0电话信息 1详细信息 2受理标准 3申请材料 4办理流程 5中介服务 6设定依据 7常见问题
	public function item_detail_3(){
		set_time_limit(1000);
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$url = 'http://202.61.88.206/sczw-iface/gddetail?&uid=c96460d0c1e5509465e105935d22e2fe';
		$matterid = Db::name('gra_matter')->field('tid,id')->select();//查询事项id和tid(从行政来的事项id)
		$data1 = array();
		foreach ($matterid as $k => $v) {
			//拼接事项id和受理标准的标志 
			$url1 = $url.'&id='.$v['tid'].'&tid=3&aid='.$aid;
			$data = $this->getData($url1,'','30');
			$data = json_decode($data,true);
			$data = $data['data'];
			if(!empty($data)){
				//可能存在多条数据
				foreach ($data as $kk => $vv) {
					if(!empty($vv['8']['content'])){
						$id = Db::name('gra_datum')->where('DatumID',$vv['8']['content'])->value('id');
					}
					//如果材料id不存在则添加
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
								$data1['paper'] = empty($val['content'])?'0':1;
							}
							if($key==4){//电子
								$data1['paper'] = empty($val['content'])?'0':2;
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

	//办理流程
	//tid 0电话信息 1详细信息 2受理标准 3申请材料 4办理流程 5中介服务 6设定依据 7常见问题
	public function item_detail_4(){
		set_time_limit(1000);
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$url = 'http://202.61.88.206/sczw-iface/gddetail?&uid=c96460d0c1e5509465e105935d22e2fe';
		$matter = Db::name('gra_matter')->field('tid,id')->select();//查询事项id和tid(从行政来的事项id)
		$data1 = array();
		
		foreach ($matter as $k => $v) {
			//拼接事项id和受理标准的标志 
			$url1 = $url.'&id='.$v['tid'].'&tid=4&aid='.$aid;
			$data = $this->getData($url1,'','30');
			$data = json_decode($data,true);
			// echo $data['message'].'<br>';
			$data = $data['data'];
			// dump($data);die;
			if(!empty($data)){
				// dump($data);die;
				//可能存在多条数据
				foreach ($data as $kk => $vv) {
					//每条数据获取内容
					foreach ($vv as $key => $val) {
						$data1['matterid'] = $v['id'];//事项id
						$data1['lctime'] = $val['lctime'];
						$data1['yjtime'] = $val['yjtime'];
						$data1['wztime'] = $val['wztime'];
						$data1['remark'] = $val['remark'];
						switch ($val['name']) {
							case '申请/受理':
								$data1['flowlimit'] = 1;
								break;
							case '审核':
								$data1['flowlimit'] = 2;
								break;
							case '办结':
								$data1['flowlimit'] = 3;
								break;
							case '制证':
								$data1['flowlimit'] = 4;
								break;
							case '取件':
								$data1['flowlimit'] = 5;
								break;
							case '办理':
								$data1['flowlimit'] = 6;
								break;
							case '决定':
								$data1['flowlimit'] = 7;
								break;
							case '证明':
								$data1['flowlimit'] = 8;
								break;
							case '核实':
								$data1['flowlimit'] = 9;
								break;
							case '答复':
								$data1['flowlimit'] = 10;
								break;
							default:$data1['flowlimit'] = 0;
							break;
						}

						//如果数据库已经添加就删除数组
						$id = Db::name('gra_flowlimit')->where('matterid',$v['id'])->where('flowlimit',$data1['flowlimit'])->value('id');
						if($id){
							unset($data1);
						}					
					}	
					// 如果要插入的数据不为空则插入数据库
					if(!empty($data1)){
						Db::name('gra_flowlimit')->insert($data1);
					}				
				}	
						
			}
		}	
	}

	//设定依据
	//tid 0电话信息 1详细信息 2受理标准 3申请材料 4办理流程 5中介服务 6设定依据 7常见问题
	public function item_detail_6(){
		set_time_limit(1000);
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$url = 'http://202.61.88.206/sczw-iface/gddetail?&uid=c96460d0c1e5509465e105935d22e2fe';
		$matter = Db::name('gra_matter')->field('tid,id')->select();//查询事项id和tid(从行政来的事项id)
		$data1 = array();
		
		foreach ($matter as $k => $v) {
			//拼接事项id和受理标准的标志 
			$url1 = $url.'&id='.$v['tid'].'&tid=6&aid='.$aid;
			$data = $this->getData($url1,'','30');
			$data = json_decode($data,true);
			$data = $data['data'];
		
			if(!empty($data)){
				// dump($data);die;
				//可能存在多条数据
				foreach ($data as $kk => $vv) {
					//每条数据获取内容
					foreach ($vv as $key => $val) {
						$data1['matterid'] = $v['id'];//事项id
						switch ($key) {
							case '0':
								$data1['sort'] = $val['content'];//序号
								//如果数据库已经添加就删除数组
								$id = Db::name('gra_warrntset')->where('matterid',$v['id'])->where('sort',$val['content'])->value('id');
								break;
							case '1':
								$data1['title'] = empty($val['content'])?'0':$val['content'];//名称
								break;
							case '2':
								$data1['type'] = empty($val['content'])?'0':$val['content'];//种类
								break;
							case '3':
								$data1['proof'] = empty($val['content'])?'0':$val['content'];//文号
								break;
							case '4':
								$data1['terms'] = empty($val['content'])?'0':$val['content'];//条款
								break;
							case '5':
								$data1['content'] = empty($val['content'])?'0':$val['content'];//内容
								break;
							default:break;
						}						
					}
					if(!empty($id)){
						unset($data1);
					}
					// 如果要插入的数据不为空则插入数据库
					if(!empty($data1)){
						Db::name('gra_warrntset')->insert($data1);
					}				
				}	
						
			}
		}	
	}
	//常见问题
	//tid 0电话信息 1详细信息 2受理标准 3申请材料 4办理流程 5中介服务 6设定依据 7常见问题
	public function item_detail_7(){
		set_time_limit(1000);
		$district = $this->district;//地区
		$aid = Db::name('gra_district')->where('name',$district)->value('from_id');
		$url = 'http://202.61.88.206/sczw-iface/service/faq';
		$matter = Db::name('gra_matter')->field('tid,id')->select();//查询事项id和tid(从行政来的事项id)
		$data1 = array();
		
		foreach ($matter as $k => $v) {
			//拼接事项id和受理标准的标志 
			$url1 = $url.'&id='.$v['tid'].'&tid=7&aid='.$aid;
			$data = $this->getData($url1,'','30');
			$data = json_decode($data,true);
			$data = $data['data'];
		
		
			if(!empty($data)){
				dump($data);die;
				//可能存在多条数据
				foreach ($data as $kk => $vv) {
					//每条数据获取内容
					foreach ($vv as $key => $val) {
						$data1['matterid'] = $v['id'];//事项id
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
	public function getData($url,$data,$timeout = 5){
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