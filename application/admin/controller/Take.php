<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
/**
* 排号机管理 
*/
class Take extends Common{
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

			$data = DB::name('ph_take')->where($map)->order('createtime desc')->paginate(12,false,['query'=>array('number'=>$number,'usestatus'=>$usestatus)]);
		}else{
			$data = DB::name('ph_take')->order('createtime desc')->paginate(12);
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
	public function addtake(){
		if(request()->isPost()){
			$data = input('post.');
			// 判断取号方法 现场取号 身份证取号 社保卡取号 是否为空
			$data['scenetake'] = empty($data['take']['scenetake'])?'0':$data['take']['scenetake'];
			$data['idcardtake'] = empty($data['take']['idcardtake'])?'0':$data['take']['idcardtake'];
			$data['socialsecuritycardtake'] = empty($data['take']['socialsecuritycardtake'])?'0':$data['take']['socialsecuritycardtake'];
			unset($data['take']);
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->scene('take')->check($data)){
				if(Db::name('ph_take')->insert($data)){
					$this->success('添加成功','take/index');	
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
	 * [uptake 更新数据]
	 */
	public function uptake(){
		if(request()->isPost()){
			$data = input('post.');
			
			// 判断取号方法 现场取号 身份证取号 社保卡取号 是否为空
			$data['scenetake'] = empty($data['take']['scenetake'])?'0':$data['take']['scenetake'];
			$data['idcardtake'] = empty($data['take']['idcardtake'])?'0':$data['take']['idcardtake'];
			$data['phone'] = empty($data['take']['phone'])?'0':$data['take']['phone'];
			$data['socialsecuritycardtake'] = empty($data['take']['socialsecuritycardtake'])?'0':$data['take']['socialsecuritycardtake'];
			unset($data['take']);

			$lid = $data['id'];
			unset($data['id']);
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->scene('take')->check($data)){
				if(Db::name('ph_take')->where('id',$lid)->update($data)){
					$this->success('修改成功','take/index');	
				}else{
					$this->error('修改失败,请重试');
				}
			}else{
				$this->error($validate->getError());	
			}
		}
		$id = input('id');
		$list = DB::name('ph_take')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}


	/**
	 * [dltake 删除部门]
	 */
	public function dltake(){
		$id = input('id');
		if(Db::name('ph_take')->where('id',$id)->delete()){
			$this->success('删除成功','take/index');
		}else{
			$this->error('删除失败');
		}
	}


	/**
	 * [business 排号机业务]
	 * 可以修改排号机业务
	 */
	public function business(){
		if(request()->isPost()){
			$data = input('post.');
			unset($data['number']);
			if(!empty($data['businessid'])){
				$data['businessid'] = implode(',',$data['businessid']);
			}
			
			if(Db::name('ph_takebusiness')->where('takeid',$data['takeid'])->find()){
				if(Db::name('ph_takebusiness')->where('takeid',$data['takeid'])->update($data)){
					$this->success('修改成功','take/index');
				}else{
					$this->error('修改失败');
				}
			}else{
				if(Db::name('ph_takebusiness')->insert($data)){
					$this->success('添加成功','take/index');
				}else{
					$this->error('添加失败');
				}
			}	
			
		}
		$id = input('id');
		$list = Db::name('ph_take')->where('id',$id)->find();
		$bus = Db::name('sys_business')->field('name,id')->where('valid',1)->where('cantake',1)->select();
		foreach ($bus as $k => $v) {
			//查询该窗口的所有业务id
			$bus[$k]['tbus'] = Db::name('ph_takebusiness')->where('takeid',$id)->value('businessid');
		}
		$this->assign('list',$list);
		$this->assign('bus',$bus);
		return $this->fetch();
	}

	/**
	 * 查询设备状态
	 */
	public function showtype(){
		
		$list = Db::name('ph_take')->field('id,lastlogin,number')->where('usestatus',1)->select();
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
	 * 设置定时关机时间
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
			if(Db::name('ph_take')->where('id',$tid)->update($data)){
				$this->success('设置成功','take/index');
			}else{
				$this->error('设置失败');
			}
		}
		$id = input('id');
		$list = Db::name('ph_take')->field('id,downtimehour,downtimemin,down')->where('id',$id)->find();
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
		if(Db::name('ph_take')->where('id',$id)->update(['screentime'=>$screentime])){
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
		if(Db::name('ph_take')->where('id',$id)->update(['runtype'=>'2'])){
			$this->success('设置成功','take/index');
		}else{
			$this->error('请稍后');
		}
	}


}