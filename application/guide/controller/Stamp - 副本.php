<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

/**
* 办事指南打印
*/
class Stamp {

	public function index(){
		$action = input('action');
		switch ($action) {
			case 'work':
				$this->work(input('id'));
				break;
			
			default:
				echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
				return;
				break;
		}
	}

	public function work($id){
		$matter = Db::name('sys_matter')->where('id',$id)->field('name,limitday,promisesday')->find();
		$warrntset = Db::name('sys_warrntset')->where('matterid',$id)->column('filetitle');
		$datum = Db::name('sys_datum')->where('matterid',$id)->column('title');
		$flowlimit = Db::name('sys_flowlimit')->where('matterid',$id)->value('content');
		$list = ['matter'=>$matter,'warrntset'=>$warrntset,'datum'=>$datum,'flowlimit'=>$flowlimit];
		echo json_encode(['data'=>$list,'code'=>200,'message'=>'成功']);
		return;
		// dump($list);

	}
	
}