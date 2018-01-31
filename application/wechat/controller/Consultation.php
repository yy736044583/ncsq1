<?php
namespace app\wechat\controller;
use think\View;
use think\Db;
use think\Session;
use app\wechat\controller\WeChat;

class Consultation extends WeChat{
	// 全部问题
    public function index(){
	    $data = input('get.');
	    $openid = session('openid');
	    if(empty($openid)){
		    if(!empty($data)){
			    $code =  $data['code'];
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
				    $nikename = $userinfo['nikename'];
				    $icon = $userinfo['headimgurl'];
				    session('openid',$openid);
				    $data['nikename'] = $nikename;
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

	    $data = Db::name('wx_qa')->where('valid',1)->paginate(12);
	    $list = $data->all();
	    foreach ($list as $k => $v) {
		    $pid = $v['peopleid'];
		    $people = Db::name('wy_peopleinfo')->where('id',$pid)->field('nickname,icon')->find();
		    $list[$k]['nickname'] = $people['nickname'];
		    $list[$k]['icon'] = $people['icon'];
	    }
	    $page = $data->render();
	    $this->assign('list',$list);
	    $this->assign('page',$page);
        return  $this->fetch();

    }
    // 我的问题
    public function myinfo(){
	    $openid = session('openid');
	    $people = Db::name('wy_peopleinfo')->where('openid',$openid)->field('id,icon,nickname')->find();
	    $list = Db::name('wx_qa')->where('peopleid',$people['id'])->select();
	    $this->assign('list',$list);
	    $this->assign('people',$people);
        return  $this->fetch();

    }
    // 我要提问 部门列表
    public function address(){
     	$data = Db::name('sys_section')->where('valid',1)->paginate(12);
     	$list = $data->all();
     	$page = $data->render();
	    $this->assign('list',$list);
	    $this->assign('page',$page);
        return  $this->fetch();

    }
    // 提交问题
    public function question(){
     	$sid = input('id');//部门id
	    $sname = Db::name('sys_section')->where('id',$sid)->value('name');
	    $openid = session('openid');

	    $this->assign('sname',$sname);
	    $this->assign('openid',$openid);
        return  $this->fetch();

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