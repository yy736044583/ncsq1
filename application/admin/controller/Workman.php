<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
//工作人员个人信息

class Workman extends Common{

	//个人信息列表
	public function index(){
		$this->auth();
		//根据条件查询
		$secid = input('sectionid');
		$title = input('title');
		if($secid!=''|$title!=''){
			$map = '';
			if($secid!=''){
				$map['sectionid'] = $secid;
				
			}
			if($title!=''){
				$map['name|phone|mobile'] = array('like',"%$title%");
				$this->assign('title',$title);
			}

			$data = Db::name('sys_workman')->where($map)->paginate(12,false,['query'=>array('sectionid'=>$secid,'title'=>$title)]);	
		}else{
			$data = Db::name('sys_workman')->paginate(12);
		}	
		$section = Db::name('gra_section')->where('valid',1)->select();
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['sectionid'] = Db::name('gra_section')->where('id',$v['sectionid'])->value('tname');
			//登陆窗口
			$list[$k]['loginwindowid'] = Db::name('sys_window')->where('id',$v['loginwindowid'])->value('name');
			//是否在线
			switch ($v['online']) {
				case '0':
					$list[$k]['online'] = '离线';
					break;
				case '1':
					$list[$k]['online'] = '在线';
					break;
				case '2':
					$list[$k]['online'] = '暂离';
					break;
				case '3':
					$list[$k]['online'] = '正在暂离';
					break;
				case '4':
					$list[$k]['online'] = '回归';
					break;
				case '5':
					$list[$k]['online'] = '登陆中';
					break;	
				default:	break;
			}
		}
		$page = $data->render();
		$this->assign('secid',$secid);
		$this->assign('sec',$section);
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}
	//添加个人信息
	public function addworkman(){
		if(request()->isPost()){
			$data = input('post.');
			if(input('loginpass')==''){
				$data['loginpass'] = '123456';
			}
			$data['loginpass'] = md5(input('loginpass'));
		  	//文件夹是否存在   不存在创建
		  	$path1 =  ROOT_PATH . 'public' . DS . 'uploads';
	        if (!file_exists($path1)) {
	            if(!mkdir($path1)){
	                echo '提交失败,自动创建文件夹失败';
	            }
	        }
	    	$path =  ROOT_PATH . 'public' . DS . 'uploads'.DS.'workman'; // 接收文件目录
	        if (!file_exists($path)) {
	            if(!mkdir($path)){
	                echo '提交失败,自动创建文件夹失败';
	            }
	        }
	        $file = request()->file('photo');
	        if($file){
		        $info = $file->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'workman');
		        if($info){
		        	//获取文件名称
		        	$data['photo'] = $info->getSaveName();
		        	$data['photo'] = 'workman/'.str_replace("\\","/",$data['photo']);
		        }else{
		        	$this->error($file->getError());
		        }	        	
	        }
	        $data['createtime'] = date('Y-m-d H:i:s',time());
	        $validate = validate('Workman');
			if($validate->check($data)){
				$username = $data['loginname']; 
				if($username!=''){
					if(Db::name('sys_workman')->where('loginname',$username)->find()){
						$this->error('用户名不能重复');
					}
				}
		        //添加到数据库
				if(Db::name('sys_workman')->insert($data)){
					$this->success('新增成功','Workman/index');
				}else{
					$this->error('新增失败');
				}
			}else{
				$this->error($validate->getError());
			}
		}
		$section = Db::name('gra_section')->where('valid',1)->select();
		$this->assign('sec',$section);
		return $this->fetch();
	}

	// 更新信息
	public function upworkman(){
		if(request()->isPost()){
			$data = input('post.');
			if(input('loginpass')==''){
				unset($data['loginpass']);
			}else{
				$data['loginpass'] = md5(input('loginpass'));
			}
			
			$id = $data['id'];
			unset($data['id']);
	        $file = request()->file('photo');
	        if($file){
		        $info = $file->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads'.DS.'workman');
		        if($info){
		        	$data['photo'] = $info->getSaveName();
		        	$data['photo'] = 'workman/'.str_replace("\\","/",$data['photo']);
		        }else{
		        	$this->error($file->getError());
		        }	        	
	        }
	        //更新前获取图片名称
	        $url =  Db::name('sys_workman')->where('id',$id)->value('photo');
	        $data['createtime'] = date('Y-m-d H:i:s',time());
	        $validate = validate('Workman');
			if($validate->scene('up')->check($data)){ 
		        //更新最新的信息
				if(Db::name('sys_workman')->where('id',$id)->update($data)){
					//删除之前的图片
					if($file){
						$path = ROOT_PATH . 'public' . DS . 'uploads'.DS.$url;
						if(file_exists($path)&$url!=''){
							unlink($path);
						}
					}
					$this->success('修改成功','Workman/index');
				}else{
					$this->error('修改失败');
				}
			}else{
				$this->error($validate->getError());
			}
		}
		$id = input('id');
		$section = Db::name('gra_section')->where('valid',1)->select();
		$list = Db::name('sys_workman')->where('id',$id)->find();
		$this->assign('list',$list);
		$this->assign('sec',$section);
		return $this->fetch();
	}
	//删除员工
	public function dlworkman(){
		$id = input('id');
		$url =  Db::name('sys_workman')->where('id',$id)->value('photo');
		if(Db::name('sys_workman')->where('id',$id)->delete()){
			//删除之前的图片
			$path = ROOT_PATH . 'public' . DS . 'uploads'.DS.$url;
			if(file_exists($path)&&$url!=''){
				unlink($path);
			}
			$this->success('删除成功','Workman/index');
		}else{
			$this->error('删除失败');
		}
	}
}
