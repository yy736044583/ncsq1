<?php
namespace app\inter\controller;
use think\Db;
use think\Request;  
//集中显示屏接口
class Cled{
	/**
	 * 根据方法名跳转
	 * @param [string] $[action] [方法名]
	 * @param [string] $[devicenum] [设备编号]
	 */
    public function index(){ 
		$action = input('action');
		$devicenum = input('devicenum');
		//如果设备编号为空则返回
		if(empty($devicenum)){
			echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到']);
			return;
		}
		//根据方法名跳转到各个方法
		switch ($action) {
			//心跳接口
			case 'cledheart':
				$this->cledheart($devicenum);
				break;
			//中心名称
			case 'thiscenter':
				$this->thiscenter();
				break;
			//收到心跳数据返回
			case 'cledstyle':
				$this->cledstyle($devicenum,input('id'),input('pid'));
				break;	
			//查询呼叫信息
			case 'showled':
				$this->showled($devicenum,input('id'));
				break;	
			//查询轮播和视频
			case 'bannerinfo':
				$this->bannerinfo($devicenum);
				break;
			//收到显示通知公告 回复
			case 'topdown':
				$this->topdown();
				break;
			// 查询置顶的通知公告
			case 'shownotice':
				$this->shownotice();
				break;				
			default:
				echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
				return;
				break;
		}
    }

    /**
     * [cledheart 集中显示屏心跳数据]
     * @param  [string] $devicenum [集中显示屏设备编号]
     * @return [array]  $data     [返回数据集]
     */
    public function cledheart($devicenum){

		//查询设备编号是否存在 不存在就创建
		if(!$cled = Db::name('ph_cled')->field('usestatus,id')->where('number',$devicenum)->find()){
			$data1['number'] = $devicenum;
			$data1['createtime'] = date('Y-m-d H:i:s',time());
			Db::name('ph_cled')->insert($data1);
		}

		if($cled['usestatus']!='1'){
			echo json_encode(['data'=>array(),'code'=>'201','message'=>'该设备未使用'],JSON_UNESCAPED_UNICODE);
			return;
		}

		//更新最后登陆时间
		$time = date('Y-m-d H:i:s',time());
		Db::name('ph_cled')->where('number',$devicenum)->update(['lastlogin'=>$time]);

		$top = Db::name('sys_thiscenter')->where('id',1)->value('top');

		$today = date('Y-m-d',time());

		//查询队列表中对应该设备的排号id和队列id
		$que = Db::name('ph_deviceqid')->field('id,qid')->where('cledid',$cled['id'])->whereLike('time',"%$today%")->order('time')->find();
		
		$data['id'] = $que['qid'];//排号id 
		$data['pid'] = $que['id'];//队列id
		$data['top'] = $top;
		// // 如果已经发送了的并且是一分钟前发送的 进行删除
		// // 将已发送的发送状态改为1
		// $this->sendanddl($que['id']);
		echo json_encode(['data'=>$data,'code'=>'200','message'=>'正常'],JSON_UNESCAPED_UNICODE);
		return;			
		
    }


    /**
     * [thiscenter 分中心名称]
     * @return [array] $data [返回数据集]
     */
    public function thiscenter(){
    	$data['name'] = Db::name('sys_thiscenter')->value('name');

		echo json_encode(['data'=>$data,'code'=>'200','message'=>'正常'],JSON_UNESCAPED_UNICODE);
		return;
    }

    /**
     * [cledstyle 收到正在呼叫的数据后返回]
     * @param  [string] $devicenum [集中显示屏设备编号]
     * @param  [int] $id    [排队号id]
     * @return [array]   $data   [返回数据集]
     */
    public function cledstyle($devicenum,$id,$pid){
    	if(empty($id)||empty($pid)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'数据参数错误'],JSON_UNESCAPED_UNICODE);
			return;
    	}
    	if(Db::name('ph_queue')->where('id',$id)->value('status')<3){
	    	//更改排号表中的状态
	    	Db::name('ph_queue')->where('id',$id)->update(['style'=>'2','status'=>'1']);    		
    	}

    	//删除队列表中的对应id
    	if(Db::name('ph_deviceqid')->where('id',$pid)->delete()){
    		$today = date('Y-m-d',time());
    		Db::name('ph_deviceqid')->where('time','<',$today)->delete();
    		echo json_encode(['data'=>array(),'code'=>'200','message'=>'正常'],JSON_UNESCAPED_UNICODE);
			return;
    	}else{
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
			return;
    	}

    }

    /**
     * [showled 查询呼叫信息]
     * @param  [string] $devicenum [集中显示屏设备编号]
     * @param  [int] $id        [排队号id]
     * @return [array]   $data   [返回数据集]
     */
    public function showled($devicenum,$id){
    	if(!$id){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'数据错误'],JSON_UNESCAPED_UNICODE);
			return;
    	}
    	//查询排号表中正在呼叫和正在办理的数据 返回id
		$list = Db::name('ph_queue')->field('flownum,id,windowid')->where('id',$id)->find();

		$data['flownum'] = $list['flownum'];
		$window = Db::name('sys_window')->where('id',$list['windowid'])->where('valid',1)->find();
		$data['windownum'] = $window['fromnum'];
		$data['windowname'] = $window['fromname'];

		$data['id'] = $list['id'];
		echo json_encode(['data'=>$data,'code'=>'200','message'=>'正常'],JSON_UNESCAPED_UNICODE);
		return;
    }


    /**
     * [bannerinfo 查询轮播和视频]
     * @param  [type] $devicenum [设备编号]
     * @return [type]            [description]
     */
    public function bannerinfo($devicenum){
    	// 查询视频地址
    	$redio = Db::name('ph_banner')->where('type',2)->where('top',1)->column('url');
    	// 查询轮播地址
    	$banner = Db::name('ph_banner')->where('type',1)->where('top',1)->column('url');

    	// 轮播图片或者视频的存放路径
		$request = request();
		$path1 = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public/uploads/';
		// 查询显示视频还是轮播图
		$sys = Db::name('ph_setup')->where('id',1)->value('cledtype');
		// 轮播图 设置完整地址
    	foreach ($banner as $k => $v) {
    		$banner[$k] = $path1.$v;
    	}
    	foreach ($redio as $k => $v) {
    		$redio[$k] = $path1.$v;
    	}
    	// 返回数据集
    	$data = [
    		'redio' => $redio,
    		'banner' => $banner,
    		'sys'	=> "$sys",
    	];

    	echo json_encode(['data'=>$data,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
    	return;
    }

    public function topdown(){
    	if(Db::name('sys_thiscenter')->where('id',1)->update(['top'=>0])){
    		echo json_encode(['data'=>array(),'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
    		return;
    	}else{
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
    		return;
    	}
    }

    //查询公告
    public function shownotice(){
    	$list = Db::name('sys_news')->where('top',1)->field('title,content')->find();
    	echo json_encode(['data'=>$list,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
    	return;
    }
}