<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
use think\Request;  
/*
**集中显示器管理 
 */
class Cled extends Common{
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

			$data = DB::name('ph_cled')->where($map)->order('createtime desc')->paginate(12,false,['query'=>array('number'=>$number,'usestatus'=>$usestatus)]);
		}else{
			$data = DB::name('ph_cled')->order('createtime desc')->paginate(12);
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
		//$window = Db::name('sys_window')->where('valid',1)->select();
		//$this->assign('windowid',$windowid);
		$this->assign('number',$number);
		$this->assign('usestatus',$usestatus);

		//$this->assign('window',$window);
		$this->assign('page',$page);
		$this->assign('list',$list);
		return $this->fetch();
	}
	//新增
	public function addcled(){
		if(request()->isPost()){
			$data = input('post.');
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->scene('take')->check($data)){
				if(Db::name('ph_cled')->insert($data)){
					$this->success('添加成功','cled/index');	
				}else{
					$this->error('添加失败,请重试');
				}				
			}else{
				$this->error($validate->getError());	
			}
		}
		//$window = DB::name('sys_window')->where('valid',1)->select();
		//$this->assign('win',$window);
		return $this->fetch();
	}
	//更新数据
	public function upcled(){
		if(request()->isPost()){
			$data = input('post.');
			$lid = $data['id'];
			unset($data['id']);
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Device');
			if($validate->scene('take')->check($data)){
				if(Db::name('ph_cled')->where('id',$lid)->update($data)){
					$this->success('修改成功','cled/index');	
				}else{
					$this->error('修改失败,请重试');
				}
			}else{
				$this->error($validate->getError());	
			}
		}
		$id = input('id');
		$list = DB::name('ph_cled')->where('id',$id)->find();
		//$window = DB::name('sys_window')->where('valid',1)->select();
		//$this->assign('win',$window);
		$this->assign('list',$list);
		return $this->fetch();
	}
	//删除集中屏
	public function dlcled(){
		$id = input('id');
		if(Db::name('ph_cled')->where('id',$id)->delete()){
			//删除集中屏窗口中的对应数据
			Db::name('ph_cledwindow')->where('cledid',$id)->delete();
			$this->success('删除成功','cled/index');
		}else{
			$this->error('删除失败');
		}
	}
	
	/**
	 * 查询设备状态
	 */
	public function showtype(){
		
		$list = Db::name('ph_cled')->field('id,lastlogin,number')->where('usestatus',1)->select();
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


	public function windows(){
		if(request()->isPost()){
			//如果有post传值执行以下
			$data = input('post.');
			
			unset($data['number']);//删除设备编号 避免更改出错
			//将数组组合成字符串
			if(!empty($data['windowid'])){
				$data['windowid'] = implode(',',$data['windowid']).',';
			}
			
			// dump($data);die;
			if(Db::name('ph_cledwindow')->where('cledid',$data['cledid'])->find()){
				if(Db::name('ph_cledwindow')->where('cledid',$data['cledid'])->update($data)){
					$this->success('修改成功','cled/index');
				}else{
					$this->error('修改失败');
				}
			}else{
				if(Db::name('ph_cledwindow')->insert($data)){
					$this->success('添加成功','cled/index');
				}else{
					$this->error('添加失败');
				}
			}
		}
		//根据id查询该设备的编号和id
		$id = input('id');
		$list = Db::name('ph_cled')->field('number,id')->where('id',$id)->find();
		//查询所有窗口
		$window = Db::name('sys_window')->field('name,id')->where('valid',1)->select();
		//根据id查询设备窗口表的窗口id  在页面显示
		foreach ($window as $k => $v) {
			$window[$k]['wids'] = Db::name('ph_cledwindow')->where('cledid',$id)->value('windowid');
		}
		$this->assign('window',$window);
		$this->assign('list',$list);
		return $this->fetch();
	} 

/*
******************************************************************************************
*前端设置	
 */	


	// 列表
	public function banner(){
		$this->auth();
		if(request()->isPost()){
			$type = input('type');
			$map = array();
			if($type){
				$map['type'] = $type;
				$this->assign('type',$type);
			}
			$list = Db::name('ph_banner')->where($map)->select();
		}else{
			$list = Db::name('ph_banner')->select();
		}
		foreach ($list as $k => $v) {
			$list[$k]['type1'] = $v['type']=='1'?'轮播图':'视频';

			
		}
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 添加视频或者轮播
	public function add(){
		if(request()->isPost()){
			$data = input('post.');
			if(empty($data['url'])){
				$this->error('文件未上传成功');
			}
	        $data['createtime'] = date('Y-m-d H:i',time());
	        //添加到数据库
			if(Db::name('ph_banner')->insert($data)){
				$this->success('新增成功','cled/banner');
			}else{
				$this->error('新增失败');
			}
		}
		return $this->fetch();
	}

	// 使用视频/轮播
	public function topbanner(){
		$id = input('id');
		$type = input('type');
		// 根据id查询该视频或者轮播是否在使用  0未使用 1使用中
		$top = Db::name('ph_banner')->where('id',$id)->value('top');
		// 根据type确认是视频还是轮播 1轮播 2视频 
		// 如果是轮播直接更改使用状态 如果是视频需要先修改其他视频的使用状态为未使用  只支持1个视频使用
		// if($type=='1'){
			//轮播
			if($top=='1'){
				if(Db::name('ph_banner')->where('id',$id)->update(['top'=>'0'])){
					echo 0;
				}
			}else{
				if(Db::name('ph_banner')->where('id',$id)->update(['top'=>'1'])){
					echo 1;
				}
			}
		// }elseif($type=='2'){
		// 	// 视频
		// 	// 先将所有的状态都改成0，然后再更新需要修改的ID状态
		// 	Db::name('ph_banner')->where("top=1 and type=2")->update(['top'=>'0']);
		// 	if($top=='0'){
		// 		if(Db::name('ph_banner')->where('id',$id)->update(['top'=>'1'])){
		// 			echo 1;
		// 		}
		// 	}
		// }
	}

	// 删除轮播/视频
	public function dlbanner(){
		$id = input('id');
		$url =  Db::name('ph_banner')->where('id',$id)->value('url');
		if(Db::name('ph_banner')->where('id',$id)->delete()){
			//删除之前的图片
			$path = ROOT_PATH . 'public' . DS . 'uploads'.DS.$url;
			if(file_exists($path)&&$url){
				unlink($path);
			}
			$this->success('删除成功','cled/banner');
		}else{
			$this->error('删除失败');
		}
	}

	/**图片上传**/
	public function fileupload(){
	  	//文件夹是否存在   不存在创建
	  	$path = $this->createfile('phbanner');
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

	  	// 文件夹是否存在   不存在创建
	  	$path = $this->createfile('phbanner');

	  	$type = 'mp4';
	  	$name = 'file';

	  	// type 上传文件类型 name 上传文件名称 path上传文件路径
	  	$url = $this->uploadfile($name,$type,$path);
	  	$url = str_replace('"',"",$url);
	  	if($url){
        	echo $url;
	  	}else{
	    	//返回0表示错误信息
	    	echo '0';
	    }
	}

	// 设置使用视频还是轮播
	public function setbanner(){
		$this->auth();
		if(request()->isPost()){
			$type = input('type');
			//如果状态为视频 则判断视频资源是否存在
			if($type=='2'){
				//查询是否有正在使用的视频  如果没有则返回
				$url = Db::name('ph_banner')->where("top=1 and type=2")->value('url');
									
			}else{
				//查询是否有正在使用的图片  如果没有则返回
				$url = Db::name('ph_banner')->where("top=1 and type=1")->value('url');
			}
			// dump($url);die;
			if($url){
				//在有使用的视频情况下判断视频文件是否存在
				$file = ROOT_PATH . 'public' . DS . 'uploads'.DS.$url;
				
				if(!file_exists($file)){
				   return	$this->error('无资源,请先上传');
				}
			}else{
				return $this->error('无资源,请先上传');
			}

			if(Db::name('ph_setup')->where('id',1)->update(['cledtype'=>$type])){
				$this->success('提交成功','cled/setbanner');
			}else{
				$this->error('提交失败');
			}
		}
		$list = Db::name('ph_setup')->where('id',1)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
}