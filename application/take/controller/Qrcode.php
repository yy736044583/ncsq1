<?php 
namespace app\take\controller;
use think\Controller;
use think\Db;
use think\View;

class Qrcode extends Controller{

	public function index(){
		$data = input('get.');
		$qid = $data['queue_id'];
		$que = Db::name('ph_queue')->where('id',$qid)->field('businessid,ordernumber,windowid,flownum,matterid')->find();
		$businessid = $que['businessid'];
		if($que['ordernumber']){
			$today = date('Ymd',time());
			$count = Db::name('ph_queue')->where('businessid',$que['businessid'])->where('today',$today)->where("ordernumber!=''")->count();
			$peopleid = Db::name('wy_orderrecord')->where('number',$que['ordernumber'])->value('peopleid');
			$list['peoplename'] = Db::name('wy_peopleinfo')->where('id',$peopleid)->value('name');
		}else{
			$count = Db::name('sys_business')->where('id',$que['businessid'])->value('waitcount');
		}

		// 查询该业务可到哪些窗口办理业务
		$winds = '';
		$wids = Db::name('sys_winbusiness')->where('businessid','like',"%,$businessid,%")->whereor('businessid','like',"%,$businessid")->whereor('businessid','like',"$businessid,%")->column('windowid');
		foreach ($wids as $k => $v) {
			$winds[$k] = Db::name('sys_window')->where('id',$v)->value('fromnum');
		}
		$winds = implode(',', $winds);
			

		$list['window'] = $winds;
		$list['count'] = $count;
		$list['business'] = Db::name('sys_business')->where('id',$que['businessid'])->value('name');
		$list['matter'] = Db::name('sys_matter')->where('id',$que['matterid'])->value('name');
		$list['flownum'] = $que['flownum'];

		// dump($list);
		$this->assign('list',$list);
		$this->assign('qid',$qid);
		return $this->fetch();
	}

	// 查询该排号id的状态 0取号 4完成 1-3叫号
	public function selectstyle(){
		$qid = input('qid');
		$style = Db::name('ph_queue')->where('id',$qid)->value('style');
		if($style>0&&$style<4){
			$wid = Db::name('ph_queue')->where('id',$qid)->value('windowid');
			$wind =  Db::name('sys_window')->where('id',$que['windowid'])->value('name');
			$data['window'] = $wind;
		}
		if ($style == 0) {
			$que = Db::name('ph_queue')->where('id',$qid)->field('businessid,ordernumber,windowid,flownum,matterid')->find();
			$businessid = $que['businessid'];
			if($que['ordernumber']){
				$today = date('Ymd',time());
				$count = Db::name('ph_queue')->where('businessid',$que['businessid'])->where('today',$today)->where("ordernumber!=''")->count();
			}else{
				$count = Db::name('sys_business')->where('id',$que['businessid'])->value('waitcount');
			}
			$data['window'] = $count;
		}
		$data['style'] = $style;		
		echo json_encode($data);
	}
}