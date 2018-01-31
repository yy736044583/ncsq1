<?php
/**
 * Created by PhpStorm.
 * Date: 2018/1/29
 * Time: 16:29
 */
namespace app\admin\controller;
use think\Db;
use app\admin\Controller\Common;

/**
 * 政务终端
 */
class Terminal extends Common{
	//主题列表
	public  function  index(){
		$title = input('title');
		$map = array();
		if($title){
			$map['tname'] = ['like',"%$title%"];
		}
		$data = Db::name('gra_theme')->where($map)->paginate(12);
		$page = $data->render();
		$list = $data->all();
		foreach ($list as $k => $v){
			$list[$k]['nid'] = $v['nid']==1?'个人办事':'法人办事';
		}

		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('tname',$title);
		return $this->fetch();
	}

	//添加主题
	public function  addtheme(){
		if(request()->isPost()){
			$data = input('post.');
			$path = $this->createfile('icon');
			$type = 'jpg,png,gif,jpeg';
		  	$name = 'icon';
		    // type 上传文件类型 name 上传文件名称 path上传文件路径
		  	$url = $this->uploadfile($name,$type,$path);
		  	if($url){
		  		//如果回调的url为0说明上传失败
		  		$data['icon'] = 'uploads/icon/'.$url;
		  	}else{
		  		$this->error('上传失败');
		  	}	
		  	
		  	if(Db::name('gra_theme')->insert($data)){
		  		$this->success('上传成功','terminal/index');
		  	}else{
		  		$this->error('上传失败');
		  	}		
		}

		return $this->fetch();
	}
	//编辑主题
	public function uptheme(){
		if(request()->isPost()){
			$data = input('post.');
			$id = $data['id'];
			unset($data['id']);
			if(!empty($_FILES['icon']['name'])){
				$oldurl = Db::name('gra_theme')->where('id',$id)->value('icon');//查询之前的图标地址
				$path = $this->createfile('icon');
				$type = 'jpg,png,gif,jpeg';
			  	$name = 'icon';
			    // type 上传文件类型 name 上传文件名称 path上传文件路径
			  	$url = $this->uploadfile($name,$type,$path);
			  	if($url){
			  		//如果回调的url为0说明上传失败
			  		$data['icon'] = 'uploads/icon/'.$url;
			  	}else{
			  		$this->error('上传失败');
			  	}
			  	//如果之前的图标存在则删除	
			  	if($oldurl){
			  		$dlurl = ROOT_PATH.'public/'.$oldurl;
			  		if(file_exists($dlurl)){
			  			unlink($dlurl);
			  		}
			  	}			
			}

		  	if(Db::name('gra_theme')->where('id',$id)->update($data)){
		  		$this->success('编辑成功','terminal/index');
		  	}else{
		  		$this->error('编辑失败');
		  	}		
		}
		$id = input('id');
		$list = Db::name('gra_theme')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	//删除主题
	public function dltheme(){
		$id = input('id');
		//要删除的图标
		$url = Db::name('gra_theme')->where('id',$id)->value('icon');
		if(Db::name('gra_theme')->where('id',$id)->delete()){
			$dlurl = ROOT_PATH.'public/'.$url;
			if(file_exists($dlurl)){
				unlink($dlurl);
			}
			$this->success('删除成功','terminal/index');
		}else{
			$this->error('删除失败');
		}
	}

	//乡镇便民中心
	public function  towns(){
		$name = input('name');
		$map = array();
		if($name){
			$map['name'] = ['like',"%$name%"];
		}
		$data = Db::name('gra_towns')->where($map)->paginate(12);
		$list = $data->all();
		$page =  $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('name',$name);
		return $this->fetch();
	}
	//添加乡镇便民中心
	public  function  addtowns(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = strtotime($data['createtime']);
			$path = $this->createfile('towns');
			$type = 'jpg,png,gif,jpeg';
//			dump($_FILES);die;
			// type 上传文件类型 name 上传文件名称 path上传文件路径
			if($_FILES['file1']['name']){

				$url1 = $this->uploadfile('file1',$type,$path);
			}
			if($_FILES['file2']['name']){
				$url2 = $this->uploadfile('file2',$type,$path);
			}
			if(!empty($url1)){
				//如果回调的url为0说明上传失败
				$data['url'] = 'uploads/towns/'.$url1;
			}
			if(!empty($url2)){
				//如果回调的url为0说明上传失败
				$data['inurl'] = 'uploads/towns/'.$url2;
			}

			if(Db::name('gra_towns')->insert($data)){
				$this->success('上传成功','terminal/towns');
			}else{
				$this->error('上传失败');
			}
		}
		return $this->fetch();
	}
	//编辑乡镇便民中心
	public  function  uptowns(){
		if(request()->isPost()){
			$data = input('post.');
			$id = $data['id'];
			unset($data['id']);
			$data['createtime'] = strtotime($data['createtime']);
			$path = $this->createfile('towns');
			$type = 'jpg,png,gif,jpeg';

			// type 上传文件类型 name 上传文件名称 path上传文件路径
			if($_FILES['file1']['name']){
				//获取需要删除的url 进行删除
				$dlurl = Db::name('gra_towns')->where('id',$id)->value('url');
				if($dlurl){
					$dlurl = ROOT_PATH.'public/'.$dlurl;
				}
				//上传图片
				$url1 = $this->uploadfile('file1',$type,$path);
				if(file_exists($dlurl)){
					unlink($dlurl);
				}
			}
			//获取需要删除的url 进行删除
			if($_FILES['file2']['name']){
				$dlurl1 = Db::name('gra_towns')->where('id',$id)->value('inurl');
				if($dlurl1){
					$dlurl1 = ROOT_PATH.'public/'.$dlurl1;
				}
				//上传图片
				$url2 = $this->uploadfile('file2',$type,$path);
				if(file_exists($dlurl1)){
					unlink($dlurl1);
				}
			}
			if(!empty($url1)){
				//如果回调的url为0说明上传失败
				$data['url'] = 'uploads/towns/'.$url1;
			}
			if(!empty($url2)){
				//如果回调的url为0说明上传失败
				$data['inurl'] = 'uploads/towns/'.$url2;
			}

			if(Db::name('gra_towns')->where('id',$id)->update($data)){
				$this->success('更新成功','terminal/towns');
			}else{
				$this->error('更新失败');
			}
		}
		$id = input('id');
		$list = Db::name('gra_towns')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
	//删除便民中心
	public function  dltowns(){
		$id = input('id');
		$url = Db::name('gra_towns')->where('id',$id)->field('inurl,url')->find();
		$dlurl = ROOT_PATH.'public/'.$url['url'];
//		echo $dlurl;die;
		if(Db::name('gra_towns')->where('id',$id)->delete()){
			if($url['url']){
				$dlurl = ROOT_PATH.'public/'.$url['url'];
			}
			if(file_exists($dlurl)){
				unlink($dlurl);
			}
			if($url['inurl']){
				$dlurl1 = ROOT_PATH.'public/'.$url['inurl'];
			}
			if(file_exists($dlurl1)){
				unlink($dlurl1);
			}
			$this->success('删除成功','terminal/towns');
		}else{
			$this->error('删除失败');
		}
	}
}
