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
	public function dl($id,$table,$name='',$url=''){
		if($url){
			// 将域名地址转为物理地址 删除该路径下的文件

			$url = ROOT_PATH.DS.'public'.$url;
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
		$id = input('matterid');
		if($matter!=''||$id!=''){
			$matterid = Db::name('gra_matter')->whereLike('tname',"%$matter%")->value('id');
			$map = array();
			if($matterid){
				$map['matterid'] = $matterid;
			}
			if($id){
				$map['matterid'] = $id;
			}
			$data = Db::name('gra_accept')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter)]);
		}else{
			$data = Db::name('gra_accept')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['matter'] = Db::name('gra_matter')->where('id',$v['matterid'])->value('tname');
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('matterid',$id);
		$this->assign('matter',$matter);

		return $this->fetch();
	}

	// 编辑审批条件
	public function upconditionset(){
		if(request()->isPost()){
			$data = input('post.');
			// 提交到修改方法进行修改
			$this->up($data,'gra_accept','conditionset');
		}

		$id = intval(input('id'));
		$list = Db::name('gra_accept')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除审批条件
	public function dlconditionset(){
		$id = intval(input('id'));
		$this->dl($id,'gra_accept','conditionset');
	}

	// 办理材料
	public function datum(Request $request){
		$matter = input('matter');
		$id = input('matterid');
		if($matter!=''||$id!=''){
			$map = array();
			if($matter){
				$matterid = Db::name('gra_matter')->whereLike('tname',"%$matter%")->value('id');
				$map['matterid'] = $matterid;
			}
			if($id){
				$map['matterid'] = $id;
			}
			$data = Db::name('gra_datum')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter,'id'=>$id)]);
		}else{
			$data = Db::name('gra_datum')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['matter'] = Db::name('gra_matter')->where('id',$v['matterid'])->value('tname');
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('matter',$matter);
		$this->assign('matterid',$id);
		return $this->fetch();
	}

	// 编辑办理材料
	public function updatum(Request $request){
		if(request()->isPost()){
			$data = input('post.');
			if(!empty($_FILES['files']['tmp_name'])){
				// 创建banner文件夹 返回文件夹路径
				$path = $this->createfile('datum');

				// 上传文件 返回上传文件夹名称
				$url = $this->uploadfile('files','',$path);
				
				$data['files'] = '/uploads/datum/'.$url;				
			}
			if(!empty($_FILES['nullurl']['tmp_name'])){
				// 创建banner文件夹 返回文件夹路径
				$path = $this->createfile('datum');

				// 上传文件 返回上传文件夹名称
				$url = $this->uploadfile('nullurl','',$path);
				
				$data['nullurl'] = '/uploads/datum/'.$url;				
			}			

			// 提交到修改方法进行修改
			$this->up($data,'gra_datum','datum');
		}

		$id = intval(input('id'));
		$list = Db::name('gra_datum')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除办理材料
	public function dldatum(){
		$id = intval(input('id'));
		$url = Db::name('gra_datum')->where('id',$id)->value('files');
		$this->dl($id,'gra_datum','datum',$url);

	}

	// 办理流程
	public function managementprocess(){
		$matter = input('matter');
		$id = input('matterid');
		if($matter!=''||$id!=''){
			$matterid = Db::name('gra_matter')->whereLike('tname',"%$matter%")->value('id');
			$map = array();
			if($matterid){
				$map['matterid'] = $matterid;
			}
			if($id){
				$map['matterid'] = $id;
			}
			$data = Db::name('gra_flowlimit')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter)]);
		}else{
			$data = Db::name('gra_flowlimit')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['matter'] = Db::name('gra_matter')->where('id',$v['matterid'])->value('tname');
			switch ($v['flowlimit']) {
                case '1':
                   $list[$k]['flowlimit'] = '申请受理';
                    break;
                case '2':
                   $list[$k]['flowlimit'] = '审核';
                    break;
                case '3':
                   $list[$k]['flowlimit'] = '办结';
                    break;
                case '4':
                   $list[$k]['flowlimit'] = '制证';
                    break;
                case '5':
                   $list[$k]['flowlimit'] = '取件';
                    break;
                case '6':
                   $list[$k]['flowlimit'] = '办理';
                    break;
                case '7':
                   $list[$k]['flowlimit'] = '决定';
                    break;
                case '8':
                   $list[$k]['flowlimit'] = '证明';
                    break;
                case '9':
                   $list[$k]['flowlimit'] = '核实';
                    break;
                case '10':
                   $list[$k]['flowlimit'] = '答复';
                    break;
                case '0':
                   $list[$k]['flowlimit'] = '其他';
                    break;
                default:break;
            }
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('matter',$matter);
		$this->assign('matterid',$id);
		return $this->fetch();
	}

	// 查看办理流程
	public function showmanagementprocess(){
		$id = intval(input('id'));
		$list = Db::name('gra_flowlimit')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 编辑办理流程
	public function upmanagementprocess(){
		if(request()->isPost()){
			$data = input('post.');

			// 提交到修改方法进行修改
			$this->up($data,'gra_flowlimit','managementprocess');
		}

		$id = intval(input('id'));
		$list = Db::name('gra_flowlimit')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除办理流程
	public function dlmanagementprocess(){
		$id = intval(input('id'));
		$this->dl($id,'gra_flowlimit','managementprocess');

	}
	// 法定依据
	public function warrntset(){
		$matter = input('matter');
		$id = input('matterid');
		if($matter!=''){
			$matterid = Db::name('gra_matter')->whereLike('tname',"%$matter%")->value('id');
			$map = array();
			if($matterid){
				$map['matterid'] = $matterid;
			}
			if($id){
				$map['matterid'] = $id;
			}
			$data = Db::name('gra_warrntset')->where($map)->paginate(12,false,['query'=>array('matter'=>$matter)]);
		}else{
			$data = Db::name('gra_warrntset')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['matter'] = Db::name('gra_matter')->where('id',$v['matterid'])->value('name');
		}
		$page = $data->render();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('matter',$matter);
		$this->assign('matterid',$id);
		return $this->fetch();
	}

	// 查看法定依据
	public function showwarrntset(){
		$id = intval(input('id'));
		$list = Db::name('gra_warrntset')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}
	// 编辑法定依据
	public function upwarrntset(){
		if(request()->isPost()){
			$data = input('post.');

			// 提交到修改方法进行修改
			$this->up($data,'gra_warrntset','warrntset');
		}

		$id = intval(input('id'));
		$list = Db::name('gra_warrntset')->where('id',$id)->find();
		$this->assign('list',$list);
		return $this->fetch();
	}

	// 删除法定依据
	public function dlwarrntset(){
		$id = intval(input('id'));
		$this->dl($id,'gra_warrntset','warrntset');

	}

}