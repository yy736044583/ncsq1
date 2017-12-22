<?php
namespace app\autotable2\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Model;
 /* 
    Proposal 意见建议
 */
class Proposal extends \think\Controller{

    public function index(){
    	if(request()->isPost()){
	        $data = input('post.');
	        if($data){
	            $data['uptime'] = date('Y-m-d H:i:s',time());
	            if(Db::name('ds_complain')->insert($data)){
	                echo 1;return;
	            }else{
	                echo 2;return;
	            }
	         
	        }    		
    	}
    	return $this->fetch();  
    }
}