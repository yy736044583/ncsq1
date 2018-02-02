<?php
namespace app\wechat\controller;
use think\Request;
use think\View;
use think\Db;
use think\Session;
use app\wechat\controller\WeChat;

class Complain extends WeChat{
	// 全部问题
	public function index(){
		$data1 = input('get.');
		$openid = session('openid');
		if(empty($openid)){
			if(!empty($data1)){
				$code =  $data1['code'];
				$token = $this->get_access_token($code);
				/*
				 * access_token   网页授权接口调用凭证
				 * refresh_token    用户刷新access_token
				 * expires_in       access_token接口调用凭证超时时间，单位（秒）
				 * openid   用户唯一标识
				 * scope    用户授权的作用域，使用逗号（,）分隔
				 * */
				if($token){
					$openid = $token['openid'];//用户openid
					$access_token = $token['access_token'];//access_token
					//获取用户信息
					$userinfo = $this->get_user_info($access_token,$openid);
					/*
					 * openid
					 * nickname 昵称
					 * sex  性别 1男 2女 0未知
					 * city 城市
					 * headimgurl 头像地址
					 * */
					$nickname = $userinfo['nickname'];
					$icon = $userinfo['headimgurl'];
					session('openid',$openid);
					$data['nickname'] = $nickname;
					$data['icon'] = $icon;
					$time = date('Y-m-d H:i:s',time());
					// 查询数据表是否有该用户  如果有就更新数据 没有就添加
					if($id = Db::name('wy_peopleinfo')->where('openid',$openid)->value('id')){
						$data['lasttime'] = $time;
						Db::name('wy_peopleinfo')->where('id',$id)->update($data);
					}else{
						$data['openid'] = $openid;
						$data['jointime'] = $time;
						Db::name('wy_peopleinfo')->insert($data);
					}
				}
			}else{
				$state = $this->sofn_generate_num(6);
				$url = $this->get_authorize_url($state);
				$this->redirect($url);
			}
		}
		return  $this->fetch();

	}



	//投诉页面
//	public  function complain(){
//		$openid = session('openid');
//		if(empty($openid)){
//			$this->redirect('consultation/index');
//		}
//		return  $this->fetch();
//	}

	//提交投诉
	public function upcomplain(){
		$openid = session('openid');
		$peopleid = Db::name('wy_peopleinfo')->where('openid',$openid)->value('id');
		//提交时查询当天改用户提交的次数
		$count = $this->upnum($peopleid);
		if($count>=2){
			echo 3;return;
		}
		$data = input('post.');
		$data['uptime'] = date('Y-m-d H:i:s');
		$data['peopleid'] = $peopleid;
		$data['valid'] = '0';
		if(Db::name('ds_complain')->insert($data)){
			echo 1;
		}else{
			echo 2;
		}
	}
	//我的投诉
	public  function myinfo(){
		$openid = session('openid');
		$people = Db::name('wy_peopleinfo')->where('openid',$openid)->field('id,icon,nickname')->find();
		$list = Db::name('ds_complain')->where('peopleid',$people['id'])->order('uptime desc')->select();
		$this->assign('list',$list);
		$this->assign('people',$people);
		return  $this->fetch();
	}
	/**
	 * 查询该用户当天提交次数
	 * @param $peopleid
	 * @return int|提交次数
	 */
	public function upnum($peopleid){
		$time = date('Y-m-d',time());
		$count = Db::name('ds_complain')->where('peopleid',$peopleid)->whereLike('uptime',"%$time%")->count();
		return $count;
	}
	/**
	 * 生成随机码
	 * @param string $len 长度
	 * @return int|mixed  随机码
	 */
	public function sofn_generate_num($len='') {
		$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$num = rand('10000','99999');//生成一个5位数的随机数
		$string=time()-$num;//时间戳减去随机数 增加一层变量
		for($len=$len;$len>=1;$len--){
			$position=rand()%strlen($chars);
			$position2=rand()%strlen($string);
			//随机添加一个chars里面的字符到时间戳随机位置上
			$string=substr_replace($string,substr($chars,$position,1),$position2,0);
		}
		return $string;
	}
}