<?php 
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\request;
use \think\Exception;
/**
*  公共控制器
*  构造函数中获取session的值 再头部显示
*  判断是否登陆
*/
class Common extends \think\Controller{

	public function _initialize(){
		$this->loginv();		
	 	$username = trim(session('username'));
	    $this->assign('username',$username);

	}
	//判断登陆
	public function loginv(){
		$username = session('username');
		if($username==''){
			$this->error('请先登陆','login/login');
		}
	}

	public function auth(){
		$this->loginv();
		$request = Request::instance();
		$username = trim(session('username'));
		$auth = session('auth');
	 	if($username!='admin'){
		 	$data['au_id'] = array('in',$auth);
	    	$data['au_c'] = $request->controller();
	    	$data['au_a'] = $request->action();
	    	$auth_name = Db::name('sys_auth')->where($data)->value('au_name');	
    		if(!$auth_name){
    			$this->error('您没有权限访问！','index/show') ;
    		}		
	 	}
	}

	/**
	 * [createfile 创建上传文件夹]
	 * @param  [string] $file [要创建的文件夹]
	 * @return [string]       [文件夹路径地址]
	 */
	public function createfile($file){
	  	//文件夹是否存在   不存在创建
    	$path1 =  ROOT_PATH . 'public' . DS . 'uploads'; // 接收文件目录
        if (!file_exists($path1)) {
            if(!mkdir($path1)){
                echo '提交失败,自动创建文件夹失败';
            }
        }
    	$path =  ROOT_PATH . 'public' . DS . 'uploads'.DS.$file; // 接收文件目录
        if (!file_exists($path)) {
            if(!mkdir($path)){
                echo '提交失败,自动创建文件夹失败';
            }
        }

        return $path;

	}

	/**
	 * [uploadfile 文件上传]
	 * @param  [string] $name [html上传name]
	 * @param  [string] $type [允许上传类型]
	 * @param  [string] $path [上传路径]
	 * @return [string]       [上传名称]
	 */
	public function uploadfile($name,$type,$path){
 		// 获取表单上传文件 例如上传了001.jpg
	    $file = request()->file($name);
	    if(empty($type)){
	    	$info = $file->move($path);
	    }else{
	    	$info = $file->validate(['size'=>104857600,'ext'=>$type])->move($path);
	    }
	    
	    if($info){
	    	//获取文件名称
        	$url = $info->getSaveName();
        	//转义反斜杠
        	$url = str_replace("\\","/",$url);
        	return $url;
	    }else{
	    	//返回0表示错误信息
	    	return 0;
	    } 
	}
}
