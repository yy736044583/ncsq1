<?php
namespace app\winled\controller;
use think\Db;
use think\Request;  
//顺庆windows窗口屏
class Index{
	public function index(){
		// header('Access-Control-Allow-Origin:*');//允许跨域
		$action = input('action');
		switch ($action) {
			//windows窗口屏最近叫号信息
			case 'showque':
				$this->showque(input('number'));
				break;
			default:
				echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误']);
				return;
				break;
		}
	}
	/**
	 * [showque windows窗口屏最近叫号信息]
	 * @param  [string] $number [windows通屏的编号]
	 * @return [json]         [description]
	 */
	public function showque($number){
		//根据编号查询windows窗口屏下的所有窗口
		$windows = Db::name('ph_area')->where('number',$number)->value('windows');
		//对所有的窗口编号进行排序
		$windows = $this->winname($windows);
		$windowque = [];
		$today = date('Ymd',time());
		//根据窗口id查询当天最近的叫号信息
		foreach ($windows as $k => $v) {
			//根据窗口编号查询窗口信息
			$led = Db::name('sys_window')->where('fromnum',$v)->field('id,workmanid')->find();
			$id = $led['id'];
			//是否在线，0离线，1在线，2暂离 3点击暂离  4回归  5登陆
			$online = Db::name('sys_workman')->where('id',$led['workmanid'])->value('online');
			//该窗口员工的在线状态
			$windowque[$k]['online'] = empty($online)?'0':$online;
			//查询该窗口的叫号信息
			$windowque[$k]['queue'] = Db::name('ph_queue')->where('windowid',$id)
			->where('today',$today)->whereIn('style','1,2,3')->order('id desc')->value('flownum');
		}
		echo json_encode(['data'=>$windowque,'code'=>'200','message'=>'成功']);
		return;
	}

	// 对窗口编号进行排序
	public function winname($windows){
		$windows = explode(',',$windows);
		$winnum = [];
		foreach ($windows as $k => $v) {
			$winnum[$k] = Db::name('sys_window')->where('id',$v)->value('fromnum');
		}
		sort($winnum);
		return $winnum;
	}

}