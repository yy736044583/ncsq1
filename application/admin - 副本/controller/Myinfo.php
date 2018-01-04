<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use app\admin\Controller\Common;
//我的信息
class Myinfo extends Common{
	// 查看信息
    public function index(){
        $uid = session('uid');
        $user = Db::name('sys_user')->where('id', $uid)->find();
        $this->assign('user',$user);
        return  $this->fetch();
    }
    //修改信息
    public function revise(){
    	if(request()->ispost()){
    		$u_id = input('id');
    		$data['name'] = input('name');
    		$data['phone'] = input('phone');
            $pass = input('password');
            if($pass==''){
                unset($data['password']);
            }else{
                $data['userpass'] = md5($pass);
            }
    		$users = Db::name('sys_user')->where('id', $u_id)->update($data);
    		if($users){
    			$this->success('修改成功', 'Myinfo/index');
    		}else{
    			$this->error('修改失败');
    		}
    	}

        $uid = input('userid');
        $user = Db::name('sys_user')->where('id', $uid)->find();
        $this->assign('list',$user);
        return  $this->fetch();
    }
}
