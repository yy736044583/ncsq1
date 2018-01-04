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

		if($title!=''){
			$map['name'] = ['like',"%$title%"];
			$data = Db::name('sys_matter')->where($map)->order('createtime desc')->paginate(12,false,['query'=>array('title'=>$title)]);
			$this->assign('title',$title);
		}else{
			$data = Db::name('sys_matter')->order('createtime desc')->paginate(12);
		}

		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['section'] = Db::name('sys_section')->where('id',$v['sectionid'])->value('name');
			$list[$k]['type'] =$v['tag']==1?'法人办事':'个人办事';
		}
		$section = Db::name('sys_section')->select();
		$this->assign('sec',$section);
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}

	// 添加事项
	public function addmatter(){
		if(request()->isPost()){
			$data = input('post.');
			// 将复选框数组转换成字符串
			if(!empty($data['type'])){
				$data['type'] = implode(',', $data['type']);
			}
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$validate = validate('Matter');
			if($validate->check($data)){
				if(Db::name('sys_matter')->insert($data)){
					$this->success('添加成功','matter/index');	
				}else{
					$this->error('添加失败,请重试');
				}
			}else{
				$this->error($validate->getError());
			}
		}
		$section = Db::name('sys_section')->select();
		$this->assign('sec',$section);
		return $this->fetch();
	}

	// 编辑事项
	public function upmatter(){
		if(request()->isPost()){
			$data = input('post.');
			// 将复选框数组转换成字符串
			if(empty($data['tag'])){
				$data['tag'] = '0';
			}else{
				$data['tag'] = implode(',', $data['tag']);
			}
			$nid = intval($data['id']);
			unset($data['id']);
			$data['createtime'] = date('Y-m-d H:i:s',time());

			$validate = validate('Matter');
			if($validate->check($data)){
				if(Db::name('sys_matter')->where('id',$nid)->update($data)){
					$this->success('修改成功','matter/index');	
				}else{
					$this->error('修改失败,请重试');
				}
			}else{
				$this->error($validate->getError());
			}
		}
		$id = input('id');
		$list = DB::name('sys_matter')->where('id',$id)->find();
		$section = Db::name('sys_section')->select();
		$this->assign('sec',$section);
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除事项
	public function dlmatter(){
		$id = intval(input('id'));
		if(Db::name('sys_matter')->where('id',$id)->delete()){
			// conditionset datum management_process charge warrntset FAQ 
			// 删除对应该事项对应的数据
			Db::name('sys_conditionset')->where('matterid',$id)->delete();
			Db::name('sys_datum')->where('matterid',$id)->delete();
			Db::name('sys_flowlimit')->where('matterid',$id)->delete();
			// Db::name('sys_charge')->where('matterid',$id)->delete();
			Db::name('sys_warrntset')->where('matterid',$id)->delete();
			// Db::name('FAQ')->where('matterid',$id)->delete();
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
			$matterid = intval($data['matterid']);
			$cid = Db::name('sys_conditionset')->where('matterid',$matterid)->value('id');
			if(empty($cid)){
				if(Db::name('sys_conditionset')->insert($data)){
					$this->success('添加成功','matter/index');
				}else{
					$this->error('添加失败');
				}
			}else{
				if(Db::name('sys_conditionset')->where('matterid',$matterid)->update($data)){
					$this->success('修改成功','matter/index');
				}else{
					$this->error('修改失败');
				}
			}
		}
		$list = Db::name('sys_conditionset')->where('matterid',$id)->find();
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
			$matterid = intval($data['matterid']);
	        // 标题
	        foreach ($data['title'] as $k => $v) {
	        	$data1[$k]['matterid'] = $matterid;
			    $data1[$k]['title'] = $v;
	        }
	        // 材料形式
	        foreach ($data['material_form'] as $k => $v) {
	        	$data1[$k]['material_form'] = $v;
	        }
	        // 材料详细要求
	        foreach ($data['material_request'] as $k => $v) {
	        	$data1[$k]['material_request'] = $v;
	        }
	        // 必要性及描述
	        foreach ($data['describe'] as $k => $v) {
	        	$data1[$k]['describe'] = $v;
	        }
	        // 必要性及描述
	        foreach ($data['summary'] as $k => $v) {
	        	$data1[$k]['summary'] = $v;
	        }
	        // 实例文件
	        foreach ($data['sampleurl'] as $k => $v) {
	        	$data1[$k]['sampleurl'] = $v;
	        }
	        // 空白文件
	        foreach ($data['url'] as $k => $v) {
	        	$data1[$k]['url'] = $v;
	        }
	        // dump($data1);die;
	        if(!empty($data1)){
		        if(Db::name('sys_datum')->insertAll($data1)){
					$this->success('添加成功','matter/index');
				}else{
					$this->error('添加失败');
				}
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
			$matterid = intval($data['matterid']);
			$cid = Db::name('sys_flowlimit')->where('matterid',$matterid)->value('id');
			if(empty($cid)){
				if(Db::name('sys_flowlimit')->insert($data)){
					$this->success('添加成功','matter/index');
				}else{
					$this->error('添加失败');
				}
			}else{
				if(Db::name('sys_flowlimit')->where('matterid',$matterid)->update($data)){
					$this->success('修改成功','matter/index');
				}else{
					$this->error('修改失败');
				}
			}
		}
		$list = Db::name('sys_flowlimit')->where('matterid',$id)->find();
		$this->assign('list',$list);
		$this->assign('id',$id);
		return $this->fetch();
	}

	// 收费情况
	// public function charge(){
	// 	$id = intval(input('id'));
	// 	if(request()->isPost()){
	// 		$data = input('post.');
	// 		$matterid = intval($data['matterid']);
	// 		$cid = Db::name('charge')->where('matterid',$matterid)->value('id');
	// 		if(empty($cid)){
	// 			if(Db::name('charge')->insert($data)){
	// 				$this->success('添加成功','matter/index');
	// 			}else{
	// 				$this->error('添加失败');
	// 			}
	// 		}else{
	// 			if(Db::name('charge')->where('matterid',$matterid)->update($data)){
	// 				$this->success('修改成功','matter/index');
	// 			}else{
	// 				$this->error('修改失败');
	// 			}
	// 		}
	// 	}
	// 	$list = Db::name('charge')->where('matterid',$id)->find();
	// 	$this->assign('list',$list);
	// 	$this->assign('id',$id);
	// 	return $this->fetch();
	// }

	// 法定依据
	public function warrntset(){
		$id = intval(input('id'));
		if(request()->isPost()){
			$data = input('post.');

			$matterid = intval($data['matterid']);
			$cid = Db::name('sys_warrntset')->where('matterid',$matterid)->value('id');
			if(empty($cid)){
				if(Db::name('sys_warrntset')->insert($data)){
					$this->success('添加成功','matter/index');
				}else{
					$this->error('添加失败');
				}
			}else{
				if(Db::name('sys_warrntset')->where('matterid',$matterid)->update($data)){
					$this->success('修改成功','matter/index');
				}else{
					$this->error('修改失败');
				}
			}
		}
		$list = Db::name('sys_warrntset')->where('matterid',$id)->find();
		$this->assign('list',$list);
		$this->assign('id',$id);
		return $this->fetch();
	}

	// 常规问题及解答
	// public function FAQ(){
	// 	$id = intval(input('id'));
	// 	if(request()->isPost()){
	// 		$data = input('post.');

	// 		$matterid = intval($data['matterid']);
	// 		$data1 = array();
	// 		foreach ($data['problem'] as $k => $v) {
	// 			$data1[$k]['problem'] = $v;
	// 			$data1[$k]['matterid'] = $matterid;

	// 		}
	// 		foreach ($data['questions'] as $k => $v) {
	// 			$data1[$k]['questions'] = $v;
	// 		}

	// 		if(Db::name('faq')->insertAll($data1)){
	// 			$this->success('添加成功','matter/index');
	// 		}else{
	// 			$this->error('添加失败');
	// 		}

	// 	}
	// 	$list = Db::name('warrntset')->where('matterid',$id)->find();
	// 	$this->assign('list',$list);
	// 	$this->assign('id',$id);
	// 	return $this->fetch();
	// }


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