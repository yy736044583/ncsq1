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
		//事项 名称 办理时限 到场次数 是否收费
		$matter = Db::name('gra_matter')->where('id',$id)->field('tname,limitday,number,charge')->find();
		//法定依据
		$warrntset = Db::name('gra_warrntset')->where('matterid',$id)->column('title');
		//申请材料
		$datum = Db::name('gra_datum')->where('matterid',$id)->column('title');
		//办理流程
		//$flowlimit = Db::name('gra_flowlimit')->where('matterid',$id)->value('content');
		$list = ['matter'=>$matter,'warrntset'=>$warrntset,'datum'=>$datum];
		echo json_encode(['data'=>$list,'code'=>200,'message'=>'成功']);
		return;
		// dump($list);

	}
	
}