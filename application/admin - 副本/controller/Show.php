<?php 
namespace app\admin\controller;
use think\Db;
use app\admin\controller\Common;
use think\Request;
/**
 * 事项详情显示
 */
class Show extends Common{

/**
 * 显示事项下相关数据列表 编辑 删除
 * 审批条件 
 * 办理材料
 * 办理流程
 * 收费情况
 * 法定依据
 * faq
 */
	
	/**
	 * [dl 删除]
	 * @param  [type] $id    [要删除的id]
	 * @param  [type] $table [要删除的表名]
	 * @param  [type] $name  [要跳转的方法]
	 */
	public function dl($id='',$table='',$name='',$url=''){
		if($url){
			// 将域名地址转为物理地址 删除该路径下的文件
			$url = explode('/',$url);
			foreach ($url as $k => $v) {
				if($k<4){
					unset($url[$k]);
				}
			}
			$url = implode('/',$url);
			$url = ROOT_PATH.DS.$url;
			if(file_exists($url)==true){
				unlink($url);
			}
		}
		if(Db::name($table)->where('id',$id)->delete()){
			// 如果有传url 则删除该url的文件
			
			$this->success('删除成功',"show/$name");
		}else{
			$this->error('删除失败');
		}
	}
	
	/**
	 * [up 修改]
	 * @param  [type] $data  [需要修改的数据]
	 * @param  [type] $table [要删除的表名]
	 * @param  [type] $name  [要跳转的方法]
	 */
	public function up($data,$table,$name){
		$id = intval($data['id']);
		unset($data['id']);
		if(Db::name($table)->where('id',$id)->update($data)){
			$this->success('修改成功',"show/$name");
		}else{
			$this->error('修改失败');
		}
	}


	// 审批条件
	public function conditionset(){
		$matter = input('matter');
		if($matter!=''){
			$matterid = Db::name('sys_matter')->whereLike('name',"%$matter%")->value('id');
			$map = array();
			if($matterid){
				$map['matterid'] = $matterid;
			}
			$data = Db::name('sys_conditionset')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter)]);
		}else{
			$data = Db::name('sys_conditionset')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['matter'] = Db::name('sys_matter')->where('id',$v['matterid'])->value('name');
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);

		return $this->fetch();
	}

	// 编辑审批条件
	public function upconditionset(){
		if(request()->isPost()){
			$data = input('post.');

			// 提交到修改方法进行修改
			$this->up($data,'sys_conditionset','conditionset');
		}

		$id = intval(input('id'));
		$list = Db::name('sys_conditionset')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除审批条件
	public function dlconditionset(){
		$id = intval(input('id'));
		$this->dl($id,'sys_conditionset','conditionset');
	}

	// 办理材料
	public function datum(Request $request){
		$matter = input('matter');
		$id = input('id');
		if($matter!=''||$id!=''){
			$map = array();
			if($matter){
				$matterid = Db::name('sys_matter')->whereLike('name',"%$matter%")->value('id');
				$map['matterid'] = $matterid;
			}
			if($id){
				$map['matterid'] = $id;
			}
			$data = Db::name('sys_datum')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter,'id'=>$id)]);
		}else{
			$data = Db::name('sys_datum')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['matter'] = Db::name('sys_matter')->where('id',$v['matterid'])->value('name');
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}

	// 编辑办理材料
	public function updatum(Request $request){
		if(request()->isPost()){
			$data = input('post.');
			if(!empty($_FILES['sampleurl']['tmp_name'])){
				// 创建banner文件夹 返回文件夹路径
				$path = $this->createfile('datum');

				// 上传文件 返回上传文件夹名称
				$url = $this->uploadfile('sampleurl','jpg,jpeg,png',$path);
				
				$data['sampleurl'] = '/uploads/datum/'.$url;				
			}
			if(!empty($_FILES['url']['tmp_name'])){
				// 创建banner文件夹 返回文件夹路径
				$path = $this->createfile('datum');

				// 上传文件 返回上传文件夹名称
				$url = $this->uploadfile('url','jpg,jpeg,png',$path);
				
				$data['url'] = '/uploads/datum/'.$url;				
			}			

			// 提交到修改方法进行修改
			$this->up($data,'datum','datum');
		}

		$id = intval(input('id'));
		$list = Db::name('sys_datum')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除办理材料
	public function dldatum(){
		$id = intval(input('id'));
		$url = Db::name('sys_datum')->where('id',$id)->value('sampleurl');
		$this->dl($id,'sys_datum','datum',$url);

	}

	// 办理流程
	public function managementprocess(){
		$matter = input('matter');
		if($matter!=''){
			$matterid = Db::name('sys_matter')->whereLike('name',"%$matter%")->value('id');
			$map = array();
			if($matterid){
				$map['matterid'] = $matterid;
			}
			$data = Db::name('sys_flowlimit')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter)]);
		}else{
			$data = Db::name('sys_flowlimit')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['matter'] = Db::name('sys_matter')->where('id',$v['matterid'])->value('name');
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}

	// 查看办理流程
	public function showmanagementprocess(){
		$id = intval(input('id'));
		$list = Db::name('sys_flowlimit')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 编辑办理流程
	public function upmanagementprocess(){
		if(request()->isPost()){
			$data = input('post.');

			// 提交到修改方法进行修改
			$this->up($data,'sys_flowlimit','managementprocess');
		}

		$id = intval(input('id'));
		$list = Db::name('sys_flowlimit')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除办理流程
	public function dlmanagementprocess(){
		$id = intval(input('id'));
		$this->dl($id,'sys_flowlimit','managementprocess');

	}
	// 法定依据
	public function warrntset(){
		$matter = input('matter');
		if($matter!=''){
			$matterid = Db::name('sys_matter')->whereLike('name',"%$matter%")->value('id');
			$map = array();
			if($matterid){
				$map['matterid'] = $matterid;
			}
			$data = Db::name('sys_warrntset')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter)]);
		}else{
			$data = Db::name('sys_warrntset')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['matter'] = Db::name('sys_matter')->where('id',$v['matterid'])->value('name');
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}

	// 查看法定依据
	public function showwarrntset(){
		$id = intval(input('id'));
		$list = Db::name('sys_warrntset')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
	// 编辑法定依据
	public function upwarrntset(){
		if(request()->isPost()){
			$data = input('post.');

			// 提交到修改方法进行修改
			$this->up($data,'warrntset','warrntset');
		}

		$id = intval(input('id'));
		$list = Db::name('sys_warrntset')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除法定依据
	public function dlwarrntset(){
		$id = intval(input('id'));
		$this->dl($id,'sys_warrntset','warrntset');

	}

	// 收费情况
	// public function charge(){
	// 	$matter = input('matter');
	// 	if($matter!=''){
	// 		$matterid = Db::name('sys_matter')->whereLike('title',"%$matter%")->value('id');
	// 		$map = array();
	// 		if($matterid){
	// 			$map['matterid'] = $matterid;
	// 		}
	// 		$data = Db::name('charge')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter)]);
	// 	}else{
	// 		$data = Db::name('charge')->paginate(12);
	// 	}
	// 	$list = $data->all();
	// 	foreach ($list as $k => $v) {
	// 		$list[$k]['matter'] = Db::name('sys_matter')->where('id',$v['matterid'])->value('title');
	// 	}
	// 	$page = $data->render();
	// 	$this->assign('list',$list);
	// 	$this->assign('page',$page);
	// 	return $this->fetch();
	// }

	// // 编辑收费情况
	// public function upcharge(){
	// 	if(request()->isPost()){
	// 		$data = input('post.');

	// 		// 提交到修改方法进行修改
	// 		$this->up($data,'charge','charge');
	// 	}

	// 	$id = intval(input('id'));
	// 	$list = Db::name('charge')->where('id',$id)->find();
	// 	$this->assign('list',$list);
	// 	return $this->fetch();
	// }

	// // 删除收费情况
	// public function dlcharge(){
	// 	$id = intval(input('id'));
	// 	$this->dl($id,'charge','charge');

	// }




	// 常规问题及解答
	// public function FAQ(){
	// 	$matter = input('matter');
	// 	if($matter!=''){
	// 		$matterid = Db::name('sys_matter')->whereLike('name',"%$matter%")->value('id');
	// 		$map = array();
	// 		if($matterid){
	// 			$map['matterid'] = $matterid;
	// 		}
	// 		$data = Db::name('faq')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter)]);
	// 	}else{
	// 		$data = Db::name('faq')->paginate(12);
	// 	}
	// 	$list = $data->all();
	// 	foreach ($list as $k => $v) {
	// 		$list[$k]['matter'] = Db::name('sys_matter')->where('id',$v['matterid'])->value('name');
	// 	}
	// 	$page = $data->render();
	// 	$this->assign('list',$list);
	// 	$this->assign('page',$page);
	// 	return $this->fetch();
	// }

	// // 编辑常规问题及解答
	// public function upfaq(){
	// 	if(request()->isPost()){
	// 		$data = input('post.');

	// 		// 提交到修改方法进行修改
	// 		$this->up($data,'faq','faq');
	// 	}

	// 	$id = intval(input('id'));
	// 	$list = Db::name('faq')->where('id',$id)->find();
	// 	$this->assign('list',$list);
	// 	return $this->fetch();
	// }

	// // 删除常规问题及解答
	// public function dlfaq(){
	// 	$id = intval(input('id'));
	// 	$this->dl($id,'faq','faq');

	// }
}