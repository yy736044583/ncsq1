<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
use think\Validate;
/*
**业务管理 
 */
class Business extends Common{
	public function index(){
		$this->auth();
		//根据条件查询
		$name = input('name');
		$cantake = input('cantake');
		$canorder = input('canorder');
		if($name!=''|$canorder!=''|$cantake!=''){
			$map = '';
			if($name){
				$map['name'] = ['like',"%$name%"];
			}
			if($cantake!=''){
				$map['cantake'] = $cantake;
			}
			if($canorder!=''){
				$map['canorder'] = $canorder;
			}

			$data = DB::name('sys_business')->where('valid',1)->where($map)->order('name')->paginate(12,false,['query'=>array('name'=>$name,'canorder'=>$canorder,'cantake'=>$cantake)]);
		}else{
			$data = DB::name('sys_business')->where('valid',1)->order('name')->paginate(12);
		}
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			$v['canorder'] ==1 ?$list[$k]['canorder'] ='是':$list[$k]['canorder'] ='否';
			$v['cantake'] ==1 ?$list[$k]['cantake'] ='是':$list[$k]['cantake'] ='否';
		}
		$page = $data->render();

		$this->assign('name',$name);
		$this->assign('cantake',$cantake);
		$this->assign('canorder',$canorder);

		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}

	//添加业务
	public function addbus(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$data['valid'] = '1';
			//将业务流水号转换成大写
			$data['flownum'] = strtoupper($data['flownum']);
			if($data['name']==''){
				$this->error('业务名称不能为空');return;
			}
			if(Db::name('sys_business')->insert($data)){
				$this->success('添加成功','business/index');	
			}else{
				$this->error('添加失败,请重试');
			}
		}
		return $this->fetch();
	}

	//更新业务
	public function upbus(){
		if(request()->isPost()){
			$data = input('post.');
			//备注去掉回车
			$data['summary'] = json_encode($data['summary'],JSON_UNESCAPED_UNICODE);
			$data['summary'] = trim($data['summary'],'"');
			$data['summary'] = str_replace("\\r\\n","",$data['summary']);
			
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$bid = $data['id'];
			unset($data['id']);
			//将业务流水号转换成大写
			$data['flownum'] = strtoupper($data['flownum']);
			if($data['name']==''){
				$this->error('业务名称不能为空');return;
			}
			if($data['flownum']==''){
				$this->error('业务流水号不能为空');return;
			}
			//判断预约和取号是否有值 如果没有值就赋值0
			empty($data['cantake'])?$data['cantake']='0':$data['cantake']='1';
			empty($data['canorder'])?$data['canorder']='0':$data['canorder']='1';

			if(Db::name('sys_business')->where('id',$bid)->update($data)){
				$this->success('修改成功','business/index');	
			}else{
				$this->error('修改失败,请重试');
			}
		}
		$id = input('id');
		$list = DB::name('sys_business')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
	
	public function dlbus(){
		$id = input('id');
		if(Db::name('sys_business')->where('id',$id)->delete()){
			$this->success('删除成功','business/index');
		}else{
			$this->error('删除失败');
		}
	}

	//业务事项
	public function matter(){
		if(request()->isPost()){
			$data = input('post.');
			//如果事项为空 则赋值为0
			if(empty($data['matterid'])){
				$data['matterid'] = '0';
			}else{
				$data['matterid']  = implode(',', $data['matterid']);
			}	
			//根据业务id查询 业务事项表中是否有数据  如果没有就添加  否则更新
			$businessid = input('businessid');
			if($id = Db::name('sys_businessmatter')->where('businessid',$businessid)->value('id')){

				if(Db::name('sys_businessmatter')->where('businessid',$businessid)->update($data)){
					$this->success('提交成功','business/index');
				}else{
					$this->error('提交失败','business/index');
				}
			}else{
				if(Db::name('sys_businessmatter')->insert($data)){
					$this->success('提交成功','business/index');
				}else{
					$this->error('提交失败','business/index');
				}
			}
		}

		$businessid = input('id');
		//更具业务id查询事项id集
		$bmatter = Db::name('sys_businessmatter')->where('businessid',$businessid)->value('matterid');
		$wid = Db::name('sys_winbusiness')->where('businessid','like',"%,$businessid,%")->whereor('businessid','like',"%,$businessid")->whereor('businessid','like',"$businessid,%")->column('windowid');
		$map = array();
		// 根据业务查询窗口id  再根据窗口id查询部门id
		// 根据部门查询所有的事项
		if($wid){
			$wid = implode(',',$wid);
			$section = Db::name('sys_window')->whereIn('id',$wid)->column('sectionid');
			$section = implode(',',$section);
			$map['sectionid'] = ['in',$section];
		}
		$map['mattertype'] = 1;
		$list = Db::name('sys_matter')->where($map)->select();
		$this->assign('businessid',$businessid);
		$this->assign('list',$list);
		$this->assign('bmatter',$bmatter);
		return $this->fetch();
	}

		// 添加下一级业务
	public function nextbus(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$data['valid'] = '1';
			$data['level'] = 1; // 2级业务
			//将业务流水号转换成大写
			$data['flownum'] = strtoupper($data['flownum']);
			if($data['name']==''){
				$this->error('业务名称不能为空');return;
			}
			if(Db::name('sys_business')->insert($data)){
				$this->success('添加成功','business/index');	
			}else{
				$this->error('添加失败,请重试');
			}
		}
		$id = input('id');
		$this->assign('id',$id);
		return $this->fetch();
	}
}