<?php 
namespace app\admin\controller;
use think\Db;
use app\admin\Controller\Common;
use think\Request;
/**
 * 事项管理
 */
class Matter extends Common{

	// 事项列表
	public function index(){
		$this->auth();
		$title = input('title');
		$id = input('matterid');
		if($title!=''||$id!=''){
			if($title){
				$map['tname'] = ['like',"%$title%"];
			}
			if($id){
				$map['id'] = $id;
			}
			$data = Db::name('gra_matter')->where($map)->order('sort desc')->paginate(12,false,['query'=>array('title'=>$title)]);
			$this->assign('title',$title);
		}else{
			$data = Db::name('gra_matter')->order('sort desc')->paginate(12);
		}

		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['section'] = Db::name('gra_section')->where('tid',$v['deptid'])->value('tname');
		}
		$section = Db::name('gra_section')->select();
		$this->assign('sec',$section);
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('title',$title);
		$this->assign('matterid',$id);
		return $this->fetch();
	}

	// 添加事项
	public function addmatter(){
		if(request()->isPost()){
			$data = input('post.');
			// 将复选框数组转换成字符串
			if(!empty($data['tag'])){
				foreach ($data['tag'] as $k => $v) {
					if($v==1)$data['legal'] = 1;
					if($v==2)$data['nid'] = 1;	
				}
			}
			unset($data['tag']);
			//部门名称
			$data['department'] = Db::name('gra_section')->where('tid',$data['deptid'])->value('tname');

			if(Db::name('gra_matter')->insert($data)){
				$this->success('添加成功','matter/index');	
			}else{
				$this->error('添加失败,请重试');
			}

		}
		$section = Db::name('gra_section')->select();
		$this->assign('sec',$section);
		return $this->fetch();
	}

	// 编辑事项
	public function upmatter(){
		if(request()->isPost()){
			$data = input('post.');
			// 将复选框数组转换成字符串
			if(!empty($data['tag'])){
				foreach ($data['tag'] as $k => $v) {
					if($v==1)$data['legal'] = 1;
					if($v==2)$data['nid'] = 1;	
				}
			}
			$nid = intval($data['id']);
			unset($data['tag']);
			unset($data['id']);
			//部门名称
			$data['department'] = Db::name('gra_section')->where('tid',$data['deptid'])->value('tname');

			if(Db::name('gra_matter')->where('id',$nid)->update($data)){
				$this->success('修改成功','matter/index');	
			}else{
				$this->error('修改失败,请重试');
			}

		}
		$id = input('id');
		$list = DB::name('gra_matter')->where('id',$id)->find();
		$section = Db::name('gra_section')->select();
		$this->assign('sec',$section);
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除事项
	public function dlmatter(){
		$id = intval(input('id'));
		if(Db::name('gra_matter')->where('id',$id)->delete()){
			// 删除对应该事项对应的数据
			Db::name('gra_accept')->where('matterid',$id)->delete();
			Db::name('gra_datum')->where('matterid',$id)->delete();
			Db::name('gra_flowlimit')->where('matterid',$id)->delete();
			Db::name('gra_warrntset')->where('matterid',$id)->delete();
			$this->success('删除成功','matter/index');
		}else{
			$this->error('删除失败');
		}
	}

/**
 * 添加事项下相关数据 
 * 审批条件 
 * 办理材料
 * 办理流程
 * 收费情况
 * 法定依据
 * faq
 */
	// 审批条件
	public function conditionset(){
		$id = intval(input('id'));
		if(request()->isPost()){
			$data = input('post.');

			if(Db::name('gra_accept')->insertGetId($data)){
				$this->redirect('show/conditionset',['matterid'=>$data['matterid']]);
			}else{
				$this->error('添加失败');
			}

		}
		$list = Db::name('gra_accept')->where('matterid',$id)->find();
		$this->assign('list',$list);
		$this->assign('id',$id);
		return $this->fetch();
	}


	// 办理材料上传文件
	public function upfile(Request $request){
		// 建立上传路径 uploads下面的datum文件夹 
		// 调用common的文件上传方法
		$path = $this->createfile('datum');
		// 上传文件类型限制
		$type = 'pdf,doc,docx,xls,xlsx';
		// 获取上传文件路径 
		// 调用common的文件上传方法
		$url = $this->uploadfile('file',$type,$path);
		$today = date('Ymd',time());
		$url = '/uploads/datum/'.$url;
		return $url;
	}

	// 办理材料
	public function datum(Request $request){
		$id = intval(input('id'));
		if(request()->isPost()){
			$data = input('post.');
			if(!empty($data['shape'])){
				foreach ($data['shape'] as $k => $v) {
					if($v==1)$data['paper'] = 1;//纸质
					if($v==2)$data['electron'] = 1;	//电子
				}
			}
			unset($data['shape']);
	        if(Db::name('gra_datum')->insert($data)){
	        	$this->redirect('show/datum',['matterid'=>$data['matterid']]);
			}else{
				$this->error('添加失败');
			}

		}
		$list = Db::name('sys_datum')->where('matterid',$id)->select();
		$this->assign('list',$list);
		$this->assign('id',$id);
		return $this->fetch();
	}

	// 办理流程
	public function managementprocess(){
		$id = intval(input('id'));
		if(request()->isPost()){
			$data = input('post.');
			if(Db::name('gra_flowlimit')->insert($data)){
				$this->redirect('show/managementprocess',['matterid'=>$data['matterid']]);
			}else{
				$this->error('添加失败');
			}

		}
		$list = Db::name('gra_flowlimit')->where('matterid',$id)->find();
		$this->assign('list',$list);
		$this->assign('id',$id);
		return $this->fetch();
	}


	// 法定依据
	public function warrntset(){
		$id = intval(input('id'));
		if(request()->isPost()){
			$data = input('post.');
			$matterid = intval($data['matterid']);

			if(Db::name('gra_warrntset')->insert($data)){
				$this->redirect('show/warrntset',['matterid'=>$data['matterid']]);
			}else{
				$this->error('添加失败');
			}
		}
		$list = Db::name('gra_warrntset')->where('matterid',$id)->find();
		$this->assign('list',$list);
		$this->assign('id',$id);
		return $this->fetch();
	}



	/**
	 * [dltimeouturl 删除超时未提交产品的图片 视频]
	 * @param  [type] $time [当前时间戳]
	 */
	public function dltimeouturl($time){
		$timeout = $time-180; //30分钟前的时间戳
		$map['createtime'] = ['elt',$timeout];
		$map['matterid'] = '';

		$list = Db::name('goods_picture')->where($map)->field('id,url')->select();
		// 删除超过30分钟未提交产品的图片或者视频
		foreach ($list as $k => $v) {
			$url = ROOT_PATH. 'public' . DS.$v['url'];
			if(file_exists($url)==true){
				unlink($url);
				// 删除该数据
				Db::name('goods_picture')->where('id',$v['id'])->delete();
			}
		}

	}
}