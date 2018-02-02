<?php
namespace app\admin\controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
//微信提问回答

class Question extends Common{
	//问答列表
	public  function index(){
		$question = input('question');
		$sectionid = input('sectionid');
		$map = [];
		if($question){
			$map['question'] = ['like',"%$question%"];
		}
		if($sectionid){
			$map['sectionid'] = $sectionid;
		}
		$data = Db::name('wx_qa')->where($map)->paginate(12);
		$list = $data->all();
		foreach ($list as $k => $v){
			$list[$k]['section'] = Db::name('sys_section')->where('id',$v['sectionid'])->value('name');
		}
		$page = $data->render();
		$section = Db::name('sys_section')->where('valid',1)->select();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('section',$section);
		$this->assign('sectionid',$sectionid);
		$this->assign('question',$question);
		return $this->fetch();
	}


	//回复
	public function  answer(){
		if(request()->isPost()){
			$data = input('post.');
			$id = $data['id'];
			unset($data['id']);
			$data['answertime'] = time();
			if(Db::name('wx_qa')->where('id',$id)->update($data)){
				$this->success('回复成功','question/index');
			}else{
				$this->error('回复失败,请重试');
			}
		}
		$id = input('id');
		$list = Db::name("wx_qa")->where('id',$id)->find();
		$list['section'] =  Db::name('sys_section')->where('id',$list['sectionid'])->value('name');
		$this->assign('list',$list);
		return $this->fetch();
	}

	public function  dlquestion(){
		$id = input('id');
		if(Db::name('wx_qa')->where('id',$id)->delete()){
			$this->success('删除成功','question/index');
		}else{
			$this->error('删除失败');
		}
	}
}