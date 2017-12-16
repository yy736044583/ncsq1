<?php 
namespace app\admin\controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
//排叫号人数设置
class Syntc extends Common{

	//对应业务排叫号人数设置  最大人数和起始号码
	public function takenumberset(){
		$this->auth();
		$name = input('name');
		if($name!=''){
			$map['name'] = ['like',"%$name%"];
			$data = DB::name('sys_business')->where('valid',1)->where($map)->order('name')->paginate(12,false,['query'=>array('name'=>$name)]);
		}else{
			$data = Db::name('sys_business')->where('valid',1)->paginate(12);
		}
		
		$page = $data->render();
		$list = $data->all();
		foreach ($list as $k => $v) {
			$v['cantake'] ==1 ?$list[$k]['cantake'] ='是':$list[$k]['cantake'] ='否';
			
		}
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}

	//设置人数
	public function setnumber(){
		if(request()->isPost()){
			$data = input('post.');
			$id = $data['id'];
			unset($data['id']);
			if(Db::name('sys_business')->where('id',$id)->update($data)){
				$this->success('设置成功','syntc/takenumberset');
			}else{
				$this->error('设置失败');
			}
		}
		$bid = input('id');
		$list = Db::name('sys_business')->where('id',$bid)->field('startnumber,maxnumber,id,maxnumberam')->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	//默认最大人数和起始号码设置
	public function takedefaultnumber(){
		if(request()->isPost()){
			$data = input('post.');
			$id = Db::name('ph_setup')->value('id');
			if($id){
				if(Db::name('ph_setup')->where('id',$id)->update($data)){
					$this->success('设置成功','syntc/takedefaultnumber');
				}else{
					$this->error('设置失败');
				}				
			}else{
				if(Db::name('ph_setup')->insert($data)){
					$this->success('设置成功','syntc/takedefaultnumber');
				}else{
					$this->error('设置失败');
				}		
			}

		}

		$list = Db::name('ph_setup')->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
}