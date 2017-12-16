<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Login extends \think\Controller{
    //实现登陆
    //登陆后设置session 存用户名
    public function logindex(){
        $username = input('username');
        $password = md5(input('password'));
        $map = array();
        if($username ==''){
            $this->error('用户名不能为空');
        }
        if($password ==''){
            $this->error('密码不能为空');
        }
        $map['username'] = $username;
        $map['userpass'] = $password;
        $user = Db::name('sys_user')->where($map)->find();
        if($user){
            //更新最后登陆时间
            $time = date('Y-m-d H:i:s',time());
            $id = $user['id'];
            Db::name('sys_user')->where('id',$id)->update(['lastlogin'=>$time]);
            //将用户名 用户id存入session
            $uname = $user['username'];
            $uid = $user['id'];
            $poid = $user['po_id'];
            session::set('username',$uname);
            session::set('uid',$uid);
            //根据uid查询对应的au_id
            $au_id = DB::name('sys_position')->where('po_id',$poid)->value('au_id');
           
      
            session::set('auth',$au_id);
            $this->redirect('index/index');
        }else{
            $this->error('账号密码错误');
        }
    }

        //登陆页面
    public function login(){
        return $this->fetch();
    } 

     //退出登陆状态
    public function outlogin(){
         session::set('username',null);
         session::set('auth',null);
         $this->success('退出成功','login/login'); 
    }      
}
