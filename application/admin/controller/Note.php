<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/*
**  短信平台管理 
 */
class Note extends Common{

	//短信配置
	public function index(){
		$this->auth();
		
		// 查询用户账号密码  如果为空则跳转存入
		$data1 = $this->inuser();
		// 接口url
		$url = 'http://sms.scsmile.cn/inter/showunitinfo';
		// $url = 'http://192.168.0.10:8076/smilesms/index.php/inter/showunitinfo';
		// 接口所需数据
		$data1['action'] = 'showsign';
		
		// 调用接口返回json数据
		$httpstr = http($url, $data1, 'GET', array("Content-type: text/html; charset=utf-8"));
		//将json转成数组
		$httpdata = json_decode($httpstr,true);
		if($httpdata['code']==400){
			$mg = $httpdata['message'];
			if($mg=='账号密码错误'){
				$this->error('账号密码错误','note/loginmessage');
			}
			$this->assign('mg',$mg);
		}
		$list = $httpdata['data'];

		//将签名存入设置表
		Db::name('dx_set')->where('id',1)->update(['sign'=>$list['sign']]);	
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 查询用户账号密码  如果为空则跳转存入用户页面
	public function inuser(){
		// 查询用户账号密码
		$user = Db::name('dx_set')->where('id',1)->find();
		$username = $user['username'];
		$pass = $user['password'];

		// 如果账号或者密码为空跳转 存入用户
		if(empty($username)||empty($pass)){
			$this->redirect('note/loginmessage');
		}

		$data1['username'] = $username;
		$data1['pass'] = $pass;	
		// 返回用户名密码
		return $data1;	
	}

	// 存储用户名 密码到短信dx_set表
	public function loginmessage(){
		if(request()->isPost()){
			$data = input('post.');
			// 先查询该表是否有该调数据 没有就创建
			if(Db::name('dx_set')->where('id',1)->value('id')){
				if(Db::name('dx_set')->where('id',1)->update(['username'=>$data['username'],'password'=>md5($data['password'])])){
					$this->success('存入成功','note/index');
				}else{
					$this->error('存入失败');
				}				
			}else{
				if(Db::name('dx_set')->where('id',1)->insert(['username'=>$data['username'],'password'=>md5($data['password'])])){
					$this->success('存入成功','note/index');
				}else{
					$this->error('存入失败');
				}
			}

		}
		return $this->fetch();
	}
/*--------------------------------------------------------------------------------------
**短信模板	
*/
	public function template(){
		$this->auth();
		$list = Db::name('dx_template')->select();
		foreach ($list as $k => $v) {
			switch ($v['type']) {
				case '1':
					$list[$k]['type'] = '预约成功短信通知';
					break;
				case '2':
					$list[$k]['type'] = '取号成功短信通知';
					break;
				case '3':
					$list[$k]['type'] = '临近叫号短信通知';
					break;
				case '4':
					$list[$k]['type'] = '叫号短信通知';
					break;	
				default:
					$list[$k]['type'] = '未同步';
					break;
			}
		}
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 从服务器获取短信模板信息
	public function templateinfo(){
		// 查询用户账号密码  如果为空则跳转存入
		$data1 = $this->inuser();
		// 接口url
		$url = 'http://sms.scsmile.cn/inter/Showunitinfo';
		// $url = 'http://192.168.0.10:8076/smilesms/index.php/inter/showunitinfo';
		// 接口所需数据
		$data1['action'] = 'showtemplet';
		
		// 调用接口返回json数据
		$httpstr = http($url, $data1, 'GET', array("Content-type: text/html; charset=utf-8"));
		
		$httpdata = json_decode($httpstr,true);
		if($httpdata['code']==400){
			$mg = $httpdata['message'];
			echo $mg;
		}
		$list = $httpdata['data'];

		foreach ($list as $k => $v) {
			$data['code'] = $v['title'];
			$data['summary'] = $v['summary'];
			$data['content'] = $v['content'];
			$data['type'] = $v['type'];

			if(Db::name('dx_template')->where('code',$data['code'])->value('id')){
				Db::name('dx_template')->where('code',$data['code'])->update($data);
			}else{
				Db::name('dx_template')->insert($data);
			}
		}
	}





/*--------------------------------------------------------------------------------------
**短信记录	
*/

	//默认只显示最近7天的数据
	public function  msgRecorder(){
		$this->auth();
		$time = date('Y-m-d',strtotime('-7 days'));
		$data = Db::name('dx_history')->where("time>'$time'")->order('time desc')->paginate(10);
		$year = Db::name('dx_history')->group('year')->column('year');
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			if($v['status']==1){
				$list[$k]['status'] = '等待回执';
			}elseif($v['status']==3){
				$list[$k]['status'] = '成功';
			}else{
				$list[$k]['status'] = '失败';
			}
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('year',$year);
		$this->assign('page',$page);
		return $this->fetch();
	}

	//查询可以查询当年的所有数据
	public function msgRecorderForm(){
		$this->auth();
		/**
		 * [$num 电话]
		 * [$start 开始时间]
		 * [$end 结束时间]
		 * [$status 状态]
		 */
		
		$num = input('num');
		$start = input('start');
		$end = input('end');
		$status = input('status');
		$year = input('year');

		//判断查询条件是否存在
		if($num){
			$map['num'] = $num;
		}

		if($status!=''){
			$map['status'] = $status;
		}
		//如果有选择年份 按照年份查询  否则查询当年数据
		if($year){
			$map['year'] = $year;
			$time = $year;
		}else{
			$time = date('Y',time());
		}

		//如果开始和结束时间都有值 取开始结束之间的数据
		if($start&&$end){
			$map['time'] = ['between',[$start,$end]];
		}elseif ($start&&!$end) {
			//如果只有开始时间 取开始时间之后的数据
			$map['time'] = ['gt',$start];
		}elseif (!$start&&$end) {
			//如果只有结束时间  取当年结束时间之前的数据
			// $map['time'] = ['lt',$end];
			$map['time'] = ['between',["$time-01-01",$end]];
		}else{
			//如果两个数据为空  取当年数据
			$map['time'] = ['between',["$time-01-01","$time-12-31"]];
		}

		$data = Db::name('dx_history')->where($map)->paginate(10,false,['query'=>array('start'=>$start,'num'=>$num,'end'=>$end,'status'=>$status,'year'=>$year)]);

		$list = $data->all();
		$page = $data->render();

		//查询年份
		$years = Db::name('dx_history')->group('year')->column('year');
		$this->assign('year',$years);
		$this->assign('list',$list);
		$this->assign('page',$page);

		$this->assign('start',$start);
		$this->assign('num',$num);
		$this->assign('end',$end);
		$this->assign('status',$status);
		$this->assign('y',$year);
		return $this->fetch();
	}

	//删除短信记录
	public function dlmsg(){
		$id = input('id');
		if($id!=''){
			if(Db::name('dx_history')->where('id',$id)->delete()){
				$this->success('删除成功','note/msgRecorder');
			}else{
				$this->error('删除失败');
			}
		}
	}

	public function synmsg(){
		// 查询用户账号密码  如果为空则跳转存入
		$data1 = $this->inuser();
		// 接口url
		// $url = 'http://192.168.0.10:8076/smilesms/index.php/inter/showunitinfo';
		$url = 'http://sms.scsmile.cn/inter/showunitinfo';
		// 接口所需数据
		$data1['action'] = 'msgrecorder';
		$data1['time'] = Db::name('dx_history')->order('time desc')->value('time');

		// 调用接口返回json数据
		$httpstr = http($url, $data1, 'GET', array("Content-type: text/html; charset=utf-8"));

		$httpdata = json_decode($httpstr,true);
		
		if($httpdata['code']==400){
			$mg = $httpdata['message'];
			echo $mg;return;
		}
		$list = $httpdata['data'];
		// dump($list);die;
		// foreach ($list as $k => $v) {
		// 	$data[$k]['template'] = $v['template'];
		// 	$data[$k]['num'] = $v['recvphone'];
		// 	$data[$k]['content'] = $v['content'];
		// 	$data[$k]['status'] = $v['status'];
		// 	$data[$k]['time'] = $v['time'];
		// 	$data[$k]['year'] = $v['year'];
		// }
		foreach ($list as $k => $v) {
			$data['template'] = $v['template'];
			$data['num'] = $v['recvphone'];
			$data['content'] = $v['content'];
			$data['status'] = $v['status'];
			$data['time'] = $v['time'];
			$data['year'] = $v['year'];
			Db::name('dx_history')->insert($data);
			if($k>300){
				return;
			}	
		}		
		// Db::name('dx_history')->insertAll($data);	
	}

/**
 * 短信开关 1开 0关
 */	
	public function messageoff(){
		if(request()->isPost()){
			$data = input('post.');
			if(empty($data['messageoff'])){
				$data['messageoff'] = '0'; //短信总开关
			}
			if(empty($data['orderoff'])){
				$data['orderoff'] = '0';	//预约短信开关
			}
			if(empty($data['takeoff'])){
				$data['takeoff'] = '0';		//取号短信开关
			}
			if(empty($data['calloff'])){
				$data['calloff'] = '0';		//临近叫号短信开关
			}
			if(empty($data['callnowoff'])){
				$data['callnowoff'] = '0';		//叫号短信开关
			}
			if(empty($data['callagainoff'])){
				$data['callagainoff'] = '0';		//重呼短信开关
			}
			$setup = Db::name('dx_set')->find();
			if($setup){
				if(Db::name('dx_set')->where('id',1)->update($data)){
					$this->success('提交成功','note/messageoff');	
				}else{
					$this->error('提交失败,请重试');
				}
			}else{

				if(Db::name('dx_set')->insert($data)){
					$this->success('提交成功','Setup/messageoff');
				}else{
					$this->error('提交失败,请重试');
				}
			}
		}
		$list =  Db::name('dx_set')->where('id',1)->find();
		$this->assign('list',$list);		
		return $this->fetch();
	}

}