<?php
namespace app\synchronization\controller;
use think\Db;
use think\Request;  
//同步业务 
class Business{
	public function index(){
		//开启服务器
		$classExample = array();
	    $server= new \SoapServer(null,array('uri'=>"http://127.0.0.1/")); 
	    $server->setClass(get_class($this)); 
	    $server->handle();

	}

	// 将中心提交过来的业务 更新到数据库
	public function businesssyn($data){
		$data = json_decode($data,true);
		foreach ($data as $k => $v) {
			$fromnum = $v['id'];
			$name = $v['name'];
			$describe = $v['describe'];

			//查询业务编号  如果数据库存在则更新
			if(!$this->sel_business($fromnum,$name,$describe)){
				//如果不存在存入数组 
				$data1[$k]['fromnum'] = $fromnum;
				$data1[$k]['fromname'] = $name;
				$data1[$k]['name'] = $name;
				$data1[$k]['fromdescribe'] = $describe;
				$data1[$k]['createtime'] = date('Y-m-d H:i:s',time());
				$data1[$k]['valid'] = 1;
			}
			
		}
			//如果数组有值 则将其添加至业务表中
			if(!empty($data1)){
				if(Db::name('sys_business')->insertAll($data1)){
					return 'ok';
				}else{
					return 'error';
				}
			}
		
	}

	/**
	 * [sel_business 检查是否存在业务编号  更新已有信息]
	 * @param  [type] $fromnum  [从社保系统来的编号]
	 * @param  [type] $name     [从社保系统来的名称]
	 * @param  [type] $describe [从社保系统来的备注]
	 * @return [type]          [description]
	 */
	public function sel_business($fromnum,$name,$describe){
		if(Db::name('sys_business')->where('fromnum',$fromnum)->value('id')){
			$time = date('Y-m-d H:i:s',time());
			Db::name('sys_business')->where('fromnum',$fromnum)->update(['fromname'=>$name,'fromdescribe'=>$describe,'createtime'=>$time,'valid'=>1,'name'=>$name]);
			return true;
		}else{
			return false;
		}
	}



}