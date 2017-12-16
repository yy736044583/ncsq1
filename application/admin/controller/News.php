<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
use think\Validate;
/*
**新闻/公告
 */
class News extends Common{

	public function index(){
		$this->auth();
		//查询  如果有查询条件则加入条件查询
		$starttime = input('starttime');
		$endtime = input('endtime');
		$title = input('title');
		$level = input('level');
		if($starttime!=''|$endtime!=''|$title!=''|$level!=''){
			// $map = '';
			if($starttime!=''){
				$map['starttime'] = ['egt',$starttime];
			}
			if($endtime!=''){
				$map['endtime'] = ['elt',$endtime];
			}
			if($title!=''){
				$map['title'] = ['like',"%$title%"];
			}
			if($level!=''){
				$map['level'] = $level;
			}
			$data = Db::name('sys_news')->where('neworpub',2)->where($map)->order('createtime desc')->paginate(12,false,['query'=>array('starttime'=>$starttime,'endtime'=>$endtime,'title'=>$title,'level'=>$level)]);
		}else{
			$data = Db::name('sys_news')->where('neworpub',2)->order('createtime desc')->paginate(12);
		}
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			$v['neworpub']==1?$list[$k]['neworpub'] = '公告':$list[$k]['neworpub'] = '新闻';
			$v['level']==1?$list[$k]['level'] = '部门':$list[$k]['level'] = '中心';
			$list[$k]['section'] = Db::name('sys_section')->where('id',$v['sectionid'])->value('name'); 
		}
		$section = DB::name('sys_section')->where('valid',1)->select();
		$page = $data->render();

		$this->assign('starttime',$starttime);
		$this->assign('endtime',$endtime);
		$this->assign('title',$title);
		$this->assign('level',$level);

		$this->assign('page',$page);
		$this->assign('list',$list);
		$this->assign('sec',$section);
		return $this->fetch();
	}
	//添加新闻
	public function addnews(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$data['neworpub'] = '2';
			$validate = validate('News');
			if($validate->check($data)){
				if(Db::name('sys_news')->insert($data)){
					$this->success('添加成功','news/index');	
				}else{
					$this->error('添加失败,请重试');
				}
			}else{
				$this->error($validate->getError());
			}
		}
		$section = DB::name('sys_section')->where('valid',1)->select();
		$this->assign('sec',$section);
		return $this->fetch();
	}

	//编辑新闻内容
	public function upnews(){
		if(request()->isPost()){
			$data = input('post.');
			$nid = $data['id'];
			unset($data['id']);
			//如果修改为中心则部门id赋值为0
			if($data['level'] =='0'){
				$data['sectionid'] = '0';
			}
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('News');
			if($validate->check($data)){
				if(Db::name('sys_news')->where('id',$nid)->update($data)){
					$this->success('修改成功','news/index');	
				}else{
					$this->error('修改失败,请重试');
				}
			}else{
				$this->error($validate->getError());
			}
		}
		$id = input('id');
		$list = DB::name('sys_news')->where('id',$id)->find();
		$section = DB::name('sys_section')->where('valid',1)->select();
		$this->assign('list',$list);
		$this->assign('sec',$section);
		return $this->fetch();
	}

	//删除新闻/公告
	public function dlnews(){
		$id = input('id');
		if(Db::name('sys_news')->where('id',$id)->delete()){
			$this->success('删除成功','news/index');
		}else{
			$this->error('删除失败');
		}
	}

	//显示新闻/公告具体内容
	public function shownews(){
		$id = input('id');
		$list = Db::name('sys_news')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}



/*----------------------------------------------------------------------------------*/	
	public function notice(){
		$this->auth();
		//查询  如果有查询条件则加入条件查询
		$starttime = input('starttime');
		$endtime = input('endtime');
		$title = input('title');
		$level = input('level');
		if($starttime!=''|$endtime!=''|$title!=''|$level!=''){
			// $map = '';
			if($starttime!=''){
				$map['starttime'] = ['egt',$starttime];
			}
			if($endtime!=''){
				$map['endtime'] = ['elt',$endtime];
			}
			if($title!=''){
				$map['title'] = ['like',"%$title%"];
			}
			if($level!=''){
				$map['level'] = $level;
			}
			$data = Db::name('sys_news')->where('neworpub',1)->where($map)->order('createtime desc')->paginate(12,false,['query'=>array('starttime'=>$starttime,'endtime'=>$endtime,'title'=>$title,'level'=>$level)]);
		}else{
			$data = Db::name('sys_news')->where('neworpub',1)->order('createtime desc')->paginate(12);
		}
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			$v['neworpub']==1?$list[$k]['neworpub'] = '公告':$list[$k]['neworpub'] = '新闻';
			$v['level']==1?$list[$k]['level'] = '部门':$list[$k]['level'] = '中心';
			$list[$k]['section'] = Db::name('sys_section')->where('id',$v['sectionid'])->value('name'); 
		}
		$section = DB::name('sys_section')->where('valid',1)->select();
		$page = $data->render();

		$this->assign('starttime',$starttime);
		$this->assign('endtime',$endtime);
		$this->assign('title',$title);
		$this->assign('level',$level);

		$this->assign('page',$page);
		$this->assign('list',$list);
		$this->assign('sec',$section);
		return $this->fetch();
	}
	//添加公告
	public function addnotice(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$data['neworpub'] = '1';
			$validate = validate('News');
			if($validate->check($data)){
				if(Db::name('sys_news')->insert($data)){
					$this->success('添加成功','news/notice');	
				}else{
					$this->error('添加失败,请重试');
				}
			}else{
				$this->error($validate->getError());
			}
		}
		$section = DB::name('sys_section')->where('valid',1)->select();
		$this->assign('sec',$section);
		return $this->fetch();
	}

	//编辑公告内容
	public function upnotice(){
		if(request()->isPost()){
			$data = input('post.');
			$nid = $data['id'];
			unset($data['id']);
			//如果修改为中心则部门id赋值为0
			if($data['level'] =='0'){
				$data['sectionid'] = '0';
			}
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('News');
			if($validate->check($data)){
				if(Db::name('sys_news')->where('id',$nid)->update($data)){
					$this->success('修改成功','news/notice');	
				}else{
					$this->error('修改失败,请重试');
				}
			}else{
				$this->error($validate->getError());
			}
		}
		$id = input('id');
		$list = DB::name('sys_news')->where('id',$id)->find();
		$section = DB::name('sys_section')->where('valid',1)->select();
		$this->assign('list',$list);
		$this->assign('sec',$section);
		return $this->fetch();
	}

	//删除公告
	public function dlnotice(){
		$id = input('id');
		if(Db::name('sys_news')->where('id',$id)->delete()){
			$this->success('删除成功','news/notice');
		}else{
			$this->error('删除失败');
		}
	}

	//显示公告具体内容
	public function shownotice(){
		$id = input('id');
		$list = Db::name('sys_news')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 设置指定公告显示
	public function noticetop(){
		$id = input('id');
		// 根据id查询该视频或者轮播是否在使用  0未使用 1使用中
		$top = Db::name('sys_news')->where('id',$id)->value('top');
		if($top=='1'){
			Db::name('sys_news')->where('id',$id)->update(['top'=>0]);
			echo '0';
		}else{
			if(Db::name('sys_news')->where('id',$id)->update(['top'=>1])){
				Db::name('sys_news')->where('id','<>',$id)->update(['top'=>0]);
				Db::name('sys_thiscenter')->where('id',1)->update(['top'=>1]);
				echo '1';
			}else{
				echo '2';
			}			
		}

	}

}