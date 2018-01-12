<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
use think\Request;  
//前端管理

class Dsnose extends Common{
	
	//前端设置  选择视频还是轮播
	public function index(){
		$this->auth();
		if(request()->isPost()){
			$type = input('type');
			//如果状态为视频 则判断视频资源是否存在
			if($type=='1'){
				//查询是否有正在使用的视频  如果没有则返回
				$url = Db::name('ds_banner')->where("top=1 and type=2")->value('url');
				if($url){
					//在有使用的视频情况下判断视频文件是否存在
					$file = ROOT_PATH . 'public' . DS . 'uploads'.DS.$url;
					
					if(!file_exists($file)){
					   return	$this->error('无视频资源,请先上传视频');
					}
				}else{
					return $this->error('无视频资源,请先上传视频');

				}
								
			}
			if(Db::name('sys_web')->where('name=1')->update(['type'=>$type])){
				$this->success('提交成功','dsnose/index');
			}else{
				$this->error('提交失败');
			}
		}
		$list = Db::name('sys_web')->where('name=1')->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	//视频 轮播列表
	public function banner(){
		$this->auth();
		if(request()->isPost()){
			$type = input('type');
			$map = array();
			if($type){
				$map['type'] = $type;
				$this->assign('type',$type);
			}
			$list = Db::name('ds_banner')->where($map)->select();
		}else{
			$list = Db::name('ds_banner')->select();
		}
		foreach ($list as $k => $v) {
			$list[$k]['type1'] = $v['type']=='1'?'轮播图':'视频';

			
		}
		$this->assign('list',$list);
		return $this->fetch();
	}

	//添加视频或者轮播
	public function add(){
		if(request()->isPost()){
			$data = input('post.');
	        $data['createtime'] = date('Y-m-d H:i',time());
			if(empty($data['url'])){
				$this->error('文件未上传成功');
			}
	        //添加到数据库
			if(Db::name('ds_banner')->insert($data)){
				$this->success('新增成功','dsnose/banner');
			}else{
				$this->error('新增失败');
			}
		}
		return $this->fetch();
	}

	//使用视频或者轮播信息
	public function topbanner(){
		$id = input('id');
		$type = input('type');
		//根据id查询该视频或者轮播是否在使用  0未使用 1使用中
		$top = Db::name('ds_banner')->where('id',$id)->value('top');
		//根据type确认是视频还是轮播 1轮播 2视频 
		//如果是轮播直接更改使用状态 如果是视频需要先修改其他视频的使用状态为未使用  只支持1个视频使用
		if($type=='1'){
			//轮播
			if($top=='1'){
				if(Db::name('ds_banner')->where('id',$id)->update(['top'=>'0'])){
					echo 0;
				}
			}else{
				if(Db::name('ds_banner')->where('id',$id)->update(['top'=>'1'])){
					echo 1;
				}
			}
		}elseif($type=='2'){
			//视频
			//先将所有的状态都改成0，然后再更新需要修改的ID状态
			Db::name('ds_banner')->where("top=1 and type=2")->update(['top'=>'0']);
			if($top=='0'){
				if(Db::name('ds_banner')->where('id',$id)->update(['top'=>'1'])){
					echo 1;
				}
			}
		}
	}

	//删除视频或者轮播
	public function dlbanner(){
		$id = input('id');
		$url =  Db::name('ds_banner')->where('id',$id)->value('url');
		if(Db::name('ds_banner')->where('id',$id)->delete()){
			//删除之前的图片
			$path = ROOT_PATH . 'public' . DS . 'uploads'.DS.$url;
			if(file_exists($path)&&$url){
				unlink($path);
			}
			$this->success('删除成功','dsnose/banner');
		}else{
			$this->error('删除失败');
		}
	}

	/**图片上传**/
	public function fileupload(){
	  //文件夹是否存在   不存在创建
	  	$path = $this->createfile('dsbanner');
	  	$type = 'jpg,png,gif,jpeg';
	  	$name = 'imge';
	    // type 上传文件类型 name 上传文件名称 path上传文件路径
	  	$url = $this->uploadfile($name,$type,$path);
	  	
	  	if($url){
        	echo json_encode($url);
	  	}else{
	    	//返回0表示错误信息
	    	echo json_encode(['info'=>'0']);
	    } 
	    
	}

	/**视频上传**/
	public function redioupload(){
	  	//文件夹是否存在   不存在创建
	  	$path = $this->createfile('dsbanner');
	  	$type = 'mp4';
	  	$name = 'file';
	    // type 上传文件类型 name 上传文件名称 path上传文件路径
	  	$url = $this->uploadfile($name,$type,$path);
	  	
	  	if($url){
        	echo $url;
	  	}else{
	    	//返回0表示错误信息
	    	echo '0';
	    }  
	}
	//导视设置 坐标 标题
	public function dsset(){
		$data = input('post.');
		if(request()->isPost()){
			//如果有数据就直接修改
			if(Db::name('ds_set')->find()){
				if(Db::name('ds_set')->where('id',1)->update($data)){
					$this->success('修改成功','dsnose/dsset');
				}else{
					$this->error('修改失败');	
				}					
			}else{
				if(Db::name('ds_set')->insert($data)){
					$this->success('添加成功','dsnose/dsset');
				}else{
					$this->error('添加失败');
				}
			}			
		}
		$list = Db::name('ds_set')->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

}