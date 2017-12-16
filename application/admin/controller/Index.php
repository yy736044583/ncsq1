<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\View;
use app\admin\controller\Common;
use think\request;
use think\Cache;

class Index extends Common{

    public function index(){
        $request = Request::instance();
        $auth = session('auth');
       
        $username = trim(session('username'));
        if($username!='admin'){
            $map['au_id'] = ['in',$auth];
            $map['au_level'] = '0';
            $map['au_type'] = '1';
            $authA = DB::name('sys_auth')->where($map)->select();
          
            $map1['au_id'] = ['in',$auth];
            $map1['au_level'] = '1';
            $map1['au_type'] = '1';
            $authB = DB::name('sys_auth')->where($map1)->select();
            
            $map2['au_id'] = ['in',$auth];
            $map2['au_level'] = '2';
            $map2['au_type'] = '1';
            $authC = DB::name('sys_auth')->where($map2)->select();           
        }else{
            $authA = DB::name('sys_auth')->where("au_level=0")->select();
          
            $authB = DB::name('sys_auth')->where("au_level=1")->select();
            $authC = DB::name('sys_auth')->where("au_level=2")->select();
        }

        $this->assign('authA',$authA);
        $this->assign('authB',$authB);
        $this->assign('authC',$authC);
        return  $this->fetch();
    }
    public function main(){
    	return $this->fetch();
    }

    public function clear(){
        if(Cache::clear()){
            $this->redirect('index/index');
        }
    }

    // public function upload(){
    //     return  $this->fetch();
    // }
}
