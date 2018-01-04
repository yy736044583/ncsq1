<?php 
namespace app\admin\controller;
use think\Db;
use think\Request;
use app\admin\Controller\Common;

/**
* window窗口屏管理
*/
class Area extends Common{

	public function index(){
		$this->auth();
		//查询条件
		$number = input('number');
		if($number!=''){
			$map['number'] = ['like',"%$number%"];
			$data = Db::name('ph_area')->where($map)->paginate(12,false,['query'=>['number'=>$number]]);
		}else{
			$data = Db::name('ph_area')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['winname'] = $this->winname($v['windows']);
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('number',$number);
		return $this->fetch();
	}

	//添加windows窗口屏
	public function addarea(){
		if(request()->isPost()){
			$data = input('post.');
			$data['windows'] = empty($data['windows'])?'0':implode(',',$data['windows']);
			$data['createtime'] = date('Y-m-d H:i:s',time());
			if(Db::name('ph_area')->insert($data)){
				$this->success('添加成功','area/index');
			}else{
				$this->error('添加失败');
			}
		}
		$windows = Db::name('sys_window')->field('id,name,fromname')->where('valid',1)->select();
		$this->assign('windows',$windows);
		return $this->fetch();
	}

	//修改windows窗口屏
	public function uparea(){
		if(request()->isPost()){
			$data = input('post.');
			$id = $data['id'];
			unset($data['id']);
			$data['windows'] = empty($data['windows'])?'0':implode(',',$data['windows']);
			
			if(Db::name('ph_area')->where('id',$id)->update($data)){
				$this->success('修改成功','area/index');
			}else{
				$this->error('修改失败');
			}
		}
		$id = input('id');
		$list = Db::name('ph_area')->where('id',$id)->find();
		$windows = Db::name('sys_window')->field('id,name,fromname')->where('valid',1)->select();
		$this->assign('windows',$windows);
		$this->assign('list',$list);
		return $this->fetch();
	}

	//删除windows窗口屏
	public function dlarea(){
		$id = input('id');
		if(Db::name('ph_area')->where('id',$id)->delete()){
			$this->success('删除成功','area/index');
		}else{
			$this->error('删除失败');
		}
	}

	/**
	 * [winname 将窗口id转换成窗口名称]
	 * @param  [type] $wids [description]
	 * @return [type]       [description]
	 */
	public function winname($wids){
		$wids = explode(',',$wids);
		// 根据窗口id查询窗口编号
		$data1['windows'] = '';
		foreach ($wids as  $v) {
			$windownum = Db::name('sys_window')->where('id',$v)->value('name');
			if($windownum){
				$data1['windows'] .= $windownum.',';
			}
		}
		$data1['windows'] = rtrim($data1['windows'],',');
		return $data1['windows'];
	}
}