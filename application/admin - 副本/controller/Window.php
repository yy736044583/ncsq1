<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
use think\Validate;
/*
**窗口管理
 */
class Window extends Common{
	public function index(){
		$this->auth();
		$sectionid = input('sectionid');
		if($sectionid!=''){
			$map['sectionid'] = $sectionid;
			$data = DB::name('sys_window')->where('valid',1)->where($map)->order('name')->paginate(12,false,['query'=>array('sectionid'=>$sectionid)]);
		}else{
			$data = DB::name('sys_window')->where('valid',1)->order('name')->paginate(12);
		}
		$page = $data->render();
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['sectionid'] = Db::name('sys_section')->where('id',$v['sectionid'])->value('name');
			$list[$k]['workmanid'] = Db::name('sys_workman')->where('id',$v['workmanid'])->value('name');
			$list[$k]['leddeviceid'] = Db::name('ph_led')->where('id',$v['leddeviceid'])->value('number');
			$list[$k]['calldeviceid'] = Db::name('ph_call')->where('id',$v['calldeviceid'])->value('number');
			$list[$k]['pjdeviceid'] = Db::name('pj_device')->where('id',$v['pjdeviceid'])->value('number');

		}
		$section = DB::name('sys_section')->where('valid',1)->select();
		$this->assign('sectionid',$sectionid);
		$this->assign('sec',$section);
		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}
	//添加窗口
	public function addwindow(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$data['valid'] = '1';
			$data['fromname'] = $data['name'];
			$validate = validate('Window');
			if($validate->check($data)){
				if(Db::name('sys_window')->insert($data)){
					$this->success('添加成功','Window/index');	
				}else{
					$this->error('添加失败,请重试');
				}				
			}else{
				$this->error($validate->getError());
			}

		}

		$section = DB::name('sys_section')->where('valid',1)->select();
		$led = DB::name('ph_led')->select();
		$call = DB::name('ph_call')->select();
		$device = DB::name('pj_device')->select();
		$this->assign('sec',$section);
		$this->assign('led',$led);
		$this->assign('call',$call);
		$this->assign('device',$device);
		return $this->fetch();
	}
	//更新窗口
	public function upwindow(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$wid = $data['id'];
			$data['fromname'] = $data['name'];
			$validate = validate('Window');
			if($validate->check($data)){
				if(Db::name('sys_window')->where('id',$wid)->update($data)){
					$this->success('修改成功','Window/index');	
				}else{
					$this->error('修改失败,请重试');
				}				
			}else{
				$this->error($validate->getError());
			}

		}
		$id = input('id');
		$list = Db::name('sys_window')->where('id',$id)->find();
		$section = DB::name('sys_section')->where('valid',1)->select();
		$led = DB::name('ph_led')->select();
		$call = DB::name('ph_call')->select();
		$device = DB::name('pj_device')->select();
		$this->assign('list',$list);
		$this->assign('sec',$section);
		$this->assign('led',$led);
		$this->assign('call',$call);
		$this->assign('device',$device);
		return $this->fetch();
	}	

	//删除窗口
	public function dlwindow(){
		$id = input('id');
		if(Db::name('sys_window')->where('id',$id)->delete()){
			//删除窗口业务表中的对应数据
			Db::name('sys_winbusiness')->where('windowid',$id)->delete();
			$this->success('删除成功','window/index');
		}else{
			$this->error('删除失败');
		}
	}

	//窗口业务
	public function business(){
		if(request()->isPost()){
			$data = input('post.');
			unset($data['name']);
			if(!empty($data['businessid'])){
				$data['businessid'] = implode(',',$data['businessid']).',';
			}
			
			//如果没有值则赋值为0
			if(empty($data['valid'])){
				$data['valid'] = '0';
			}
			if(Db::name('sys_winbusiness')->where('windowid',$data['windowid'])->find()){
				if(Db::name('sys_winbusiness')->where('windowid',$data['windowid'])->update($data)){
					$this->success('修改成功','window/index');
				}else{
					$this->error('修改失败');
				}
			}else{
				if(Db::name('sys_winbusiness')->insert($data)){
					$this->success('添加成功','window/index');
				}else{
					$this->error('添加失败');
				}
			}	
			
		}
		$id = input('id');
		$list = Db::name('sys_window')->field('id,name')->where('id',$id)->find();
		$wbus = Db::name('sys_winbusiness')->field('id,valid')->where('windowid',$id)->find();
		$bus = Db::name('sys_business')->field('name,id')->where('valid',1)->select();
		foreach ($bus as $k => $v) {
			//查询该窗口的所有业务id
			$bus[$k]['wbus'] = Db::name('sys_winbusiness')->where('windowid',$id)->value('businessid');
		}
		$this->assign('list',$list);
		$this->assign('wbus',$wbus);
		$this->assign('bus',$bus);
		return $this->fetch();
	}


	//实时更新窗口员工
	public function showname(){

		$list = Db::name('sys_window')->field('id,name,workmanid')->where('valid',1)->select();
		$name = '';
		$data = array();
		foreach ($list as $k => $v) {
			$data[$k]['username'] = '';
			$userid = $v['workmanid'];
			$data[$k]['name'] = $v['name'];
			if($userid){
				$data[$k]['username'] = Db::name('sys_workman')->where('id',$userid)->value('name');
			}
			
		}
		$this->assign('list',$data);
		return $this->fetch();
	}

	public function adrela(){
		$data = Db::name('sys_window')->field('id,name')->where('valid',1)->paginate(12);
		$list = $data->all();
		foreach ($list as $k => $v) {
			$id = $v['id'];
			$led = Db::name('ph_led')->where('windowid',$id)->column('number');
			$call = Db::name('ph_call')->where('windowid',$id)->column('number');
			$cledid = Db::name('ph_cledwindow')->where('windowid','like',"%,$id,%")->whereor('windowid','like',"%,$id")->whereor('windowid','like',"$id,%")->column('cledid');
			$device = Db::name('pj_device')->where('windowid',$id)->column('number');
			$businessid = Db::name('sys_winbusiness')->where('windowid',$id)->value('businessid');
			$cled = '';
			foreach ($cledid as $key => $val) {
				$cled[$key] = Db::name('ph_cled')->where('id',$val)->value('number');
			}
			$businessid = explode(',', rtrim($businessid,','));
			$business ='';
			foreach ($businessid as $key => $val) {
				$business[$key] = Db::name('sys_business')->where('id',$val)->value('name');
			}
		
			$list[$k]['lednumber'] = empty($led)?'':implode(',',$led);
			$list[$k]['callnumber'] = empty($call)?'':implode(',',$call);
			$list[$k]['business'] = empty($business)?'':implode(',',$business);

			$list[$k]['clednumber'] = empty($cled)?'':implode(',',$cled);
			
			$list[$k]['devicenumber'] = empty($device)?'':implode(',',$device);
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}
}