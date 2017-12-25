<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/**
 * 导视机管理
 * @action index 		设备列表
 * @action adddevice	添加设备
 * @action updevice 	更新设备
 * @action dldevice 	删除设备
 * @action showtype		更新状态
 * @action down 		定时关机
 */
class Touch extends Common{
	public function index(){
		$this->auth();
		//查询条件
		$number = input('number');
		$usestatus = input('usestatus');
		if($usestatus!=''|$number!=''){
			if($usestatus!=''){
				$map['usestatus'] = $usestatus;
			}
			if($number!=''){
				$map['number'] = $number;
			}

			$data = DB::name('ds_device')->where($map)->order('createtime desc')->paginate(12,false,['query'=>array('number'=>$number,'usestatus'=>$usestatus)]);
		}else{
			$data = DB::name('ds_device')->order('createtime desc')->paginate(12);
		}
		
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			switch ($v['usestatus']) {
				case '0':$list[$k]['usestatus'] = '未使用';break;
				case '1':$list[$k]['usestatus'] = '使用中';break;
				case '2':$list[$k]['usestatus'] = '已作废';break;	
				default:break;
			}
			switch ($v['runtype']) {
				case '0':$list[$k]['runtype'] = '未运行';break;
				case '1':$list[$k]['runtype'] = '运行中';break;
				case '2':$list[$k]['runtype'] = '已关机';break;	
				default:break;
			}
		}
		$page = $data->render();

		$this->assign('number',$number);
		$this->assign('usestatus',$usestatus);


		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}

	/**
	 * 新增设备
	 */

	public function addtouch(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->scene('take')->check($data)){
				if(Db::name('ds_device')->insert($data)){
					$this->success('添加成功','touch/index');	
				}else{
					$this->error('添加失败,请重试');
				}				
			}else{
				$this->error($validate->getError());	
			}
		}
		return $this->fetch();
	}

	/**
	 * 更新数据
	 */

	public function uptouch(){
		if(request()->isPost()){
			$data = input('post.');
			$lid = $data['id'];
			unset($data['id']);
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->scene('take')->check($data)){
				if(Db::name('ds_device')->where('id',$lid)->update($data)){
					$this->success('修改成功','touch/index');	
				}else{
					$this->error('修改失败,请重试');
				}
			}else{
				$this->error($validate->getError());	
			}
		}
		$id = input('id');
		$list = DB::name('ds_device')->where('id',$id)->find();

		$this->assign('list',$list);
		return $this->fetch();
	}

	/**
	 * 删除设备
	 */

	public function dltouch(){
		$id = input('id');
		if(Db::name('ds_device')->where('id',$id)->delete()){
			$this->success('删除成功','touch/index');
		}else{
			$this->error('删除失败');
		}
	}
	/**
	 * 查询设备状态
	 */
	public function showtype(){
		
		$list = Db::name('ds_device')->field('id,lastlogin,number')->where('usestatus',1)->select();
		$nowtime = time();
		$data = array();
		foreach ($list as $k => $v) {
			$intime = strtotime($v['lastlogin']);
			// 计算当前时间和数据库中的时间相差几分钟
			$new = intval(date('i',$nowtime-$intime));
			$data[$k]['number'] = $v['number'];
			if($new>=5){
				$data[$k]['type'] =  '未运行';
			}else{
				$data[$k]['type'] = '运行中';
			}			
		}
		$this->assign('list',$data);
		return $this->fetch();
	}

	/**
	 * 定时关机
	 */
	public function down(){
		if(request()->isPost()){
			$data = input('post.');

			//将id赋值后删除data数组里面的id避免更新id
			$tid = $data['id'];
			unset($data['id']);

			//判断是否关机是否为空 如果为空就设置为0
			if(empty($data['down'])){
				$data['down'] = '0';
			}
			if(Db::name('ds_device')->where('id',$tid)->update($data)){
				$this->success('设置成功','touch/index');
			}else{
				$this->error('设置失败');
			}
		}
		$id = input('id');
		$list = Db::name('ds_device')->field('id,downtimehour,downtimemin,down')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}


	/**
	 * 截图 进行状态修改
	 * 每300毫秒接收一次
	 */
	public function screenshot(){
		$id = input('id');	
		//截图运行检测  时间 毫秒  存入数据库 
		$screentime = input('screentime');

		if($id==''|$screentime==''){
			echo '数据错误';
		}
		if(Db::name('ds_device')->where('id',$id)->update(['screentime'=>$screentime])){
			echo 1; //更新成功返回1
		}else{
			echo 0;
		}

	}

	/**
	 * [nowdown 关机]
	 * 点击关机设置关机状态
	 */
	public function nowdown(){
		$id = input('id');
		if(Db::name('ds_device')->where('id',$id)->update(['runtype'=>'2'])){
			echo '设置成功';
		}else{
			echo '请稍后';
		}
	}	


	/*导视机投诉建议*/
	public function complain(){
		$this->auth();

		$data = DB::name('ds_complain')->order('uptime desc')->paginate(12);
		
		$list = $data->all();
		$page = $data->render();

		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}


	//投诉详情
	public function showcom(){
		$id = input('id');
		$list = Db::name('ds_complain')->where('id',$id)->find();

		$this->assign('list',$list);
		return $this->fetch();
	}
	//删除投诉
	public function dlcomplain(){
		$id = input('id');
		if(Db::name('ds_complain')->where('id',$id)->delete()){
			$this->success('删除成功','touch/complain');
		}else{
			$this->error('删除失败');
		}

	}
}