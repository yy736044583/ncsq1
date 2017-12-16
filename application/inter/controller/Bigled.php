<?php
namespace app\inter\controller;
use think\Db;
use think\Request;  
//锦江区集中大屏接口
class Bigled{
	public function index(){
		header('Access-Control-Allow-Origin:*');//允许跨域
		$action = input('action');
		
		switch ($action) {
			// 集中大屏查询最近叫号信息
			case 'showque':
				$this->showque();
				break;
			case 'window':
				$this->window();
				break;
			case 'winnumber':
				$this->winnumber();
				break;
			case 'sumcount':
				$this->sumcount();
				break;
			case 'windowcount':
				$this->windowcount(input('wid'));
				break;
			default:
				echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误']);
				return;
				break;
		}
	}

	
	/**
	 * [showque 集中大屏查询最近叫号信息]
	 * @return [data] [flownum 叫号编号 windownum 窗口编号]
	 */
	public function showque(){
		$today = date('Ymd',time());
		// $map['today'] = '20171011';
		$map['today'] = $today;
		$map['calltime'] = ['neq','0000-00-00 00:00:00'];

		// 根据条件查询最近12条叫号信息
		$list = Db::name('ph_queue')->where($map)->field('id,flownum,windowid')->order('calltime desc')->limit(12)->select();
		foreach ($list as $k => $v) {
			$windownum = Db::name('sys_window')->where('id',$v['windowid'])->value('fromnum');
			$len = strlen($windownum);

			if($len==1){
				$list[$k]['windownum'] = '0'.$windownum;
			}else{
				$list[$k]['windownum'] = $windownum;
			}
			// 如果windownum是B开头的说明是银行窗口 将窗口名称传过去
			if(substr($windownum,0,1)=='b'){
				$list[$k]['windowname'] = Db::name('sys_window')->where('id',$v['windowid'])->value('name');
			}else{
				$list[$k]['windowname'] = '';
			}
			
		}
		echo json_encode(['data'=>$list,'code'=>'200','message'=>'最近叫号信息'],JSON_UNESCAPED_UNICODE);
		return;
	}

	//查询所有窗口编号
	public function window(){
		$list = Db::name('sys_window')->order('fromnum')->field('id,fromnum,name')->select();
		echo json_encode(['data'=>$list,'code'=>200,'message'=>'成功']);
		return;
	}

	// 查询各窗口的统计数据
	public function winnumber(){
		$list = Db::name('sys_window')->order('fromnum')->field('id,name,fromnum')->select();

		$today = date('Ymd',time());

		foreach ($list as $k => $v) {
			//办件量
			$list[$k]['count'] = Db::name('ph_queue')->where('today',$today)->where('windowid',$v['id'])->where('status',3)->count();
			// 等待人数
			$busid = Db::name('sys_winbusiness')->where('windowid',$v['id'])->value('businessid');
			$list[$k]['waitcount'] = Db::name('ph_queue')->where('today',$today)->where('style',0)->whereIn('businessid',$busid)->count();
			// 当前办件号
			$list[$k]['flownum'] = Db::name('ph_queue')->whereIn('style','1,2,3')->where('windowid',$v['id'])->where('today',$today)->value('flownum');
		}

		echo json_encode(['data'=>$list,'code'=>200,'message'=>'成功']);
		return;
	}

	// 所有窗口总量统计
	public function sumcount(){
		$today = date('Ymd',time());
		// 办件量 总量
		$data['count'] = Db::name('ph_queue')->where('today',$today)->where('status',3)->count();
		// 等待人数 总量
		$data['waitcount'] = Db::name('ph_queue')->where('today',$today)->where('style',0)->count();
		echo json_encode(['data'=>$data,'code'=>200,'message'=>'成功']);
		return;
	}

	// 指定窗口统计查询
	public function windowcount($wid){
		if(empty($wid)){
			echo json_encode(['data'=>array(),'code'=>400,'message'=>'参数错误']);
			return;
		}
		$list = Db::name('sys_window')->where('id',$wid)->order('fromnum')->field('id,name,fromnum')->find();

		$today = date('Ymd',time());
		//办件量
		$list['count'] = Db::name('ph_queue')->where('today',$today)->where('windowid',$wid)->where('status',3)->count();
		// 等待人数
		$busid = Db::name('sys_winbusiness')->where('windowid',$wid)->value('businessid');
		$list['waitcount'] = Db::name('ph_queue')->where('today',$today)->where('style',0)->whereIn('businessid',$busid)->count();
		// 当前办件号
		$list['flownum'] = Db::name('ph_queue')->whereIn('style','1,2,3')->where('windowid',$wid)->where('today',$today)->value('flownum');

		echo json_encode(['data'=>$list,'code'=>200,'message'=>'成功']);
		return;
	}
}