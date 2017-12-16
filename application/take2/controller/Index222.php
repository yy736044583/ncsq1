<?php
namespace app\take2\controller;
use think\View;
use think\Db;
use think\Controller;
use think\Session;

class Index extends \think\Controller{
    public function _initialize(){
        $weeks = array('星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六');
        $numbers = input('devicenum');
        $arrayName = array('tm' => time(), 'week' => $weeks[date('w')], 'numbers' => $numbers);
        $this->assign('times', $arrayName); 
    }

    public function index(){
        // 获取设备编号       
    	return $this->fetch();
    }
    public function scenetake(){
    	return $this->fetch();
    }
    
    //预约成功输出编号
    public function businesssuccsee(){
    	$id = input('queue_id');//排号队列表id
        $businessname = Db::table('ph_queue')
        ->alias('a')
        ->where("a.id = '$id'")
        ->join('sys_business w','a.businessid = w.id')
        ->value('name');//查询业务名称
    	$flownum = input('flownum');//编号
    	// $businessname = input('businessname');//业务名称
    	$arr = array('flownum' => $flownum, 'businessname' => $businessname);
    	$this->assign('arr',$arr);
    	return $this->fetch();
    }
    //预约编号查询
	public function make(){
		return $this->fetch();
	}
    //
    //预约业务
    public function business(){
        // 获取设备编号
        $number = input('devicenum');
        $takeid = Db::table('ph_take')->where("number = '$number'")->value('id');
        $businessid = Db::table('ph_takebusiness')->where("takeid = '$takeid'")->value('businessid');
        $businessids = explode(',', $businessid);
        $cond['id'] = array('in',$businessids);
        $cond['valid'] = 1;
        // $cond['valid'] = 1;
        // echo date('d');
        $info = Db::table('sys_business')->field('name,id,waitcount,day')->where($cond)->paginate(6,true,['query'=>array('devicenum'=>$number)]);
        $day = date('d',time());
        $page = $info->render();
        $this->assign('list',$info);
        $this->assign('day',$day);
        $this->assign('page',$page);
        return $this->fetch();
    }

    //预约业务
    public function business1(){
        // 获取设备编号
        $number = input('devicenum');
        $takeid = Db::table('ph_take')->where("number = '$number'")->value('id');
        $businessid = Db::table('ph_takebusiness')->where("takeid = '$takeid'")->value('businessid');
        $businessids = explode(',', $businessid);
        $cond['id'] = array('in',$businessids);
        $cond['valid'] = 1;
        $info = Db::table('sys_business')->field('name,id,fromdescribe,waitcount,day')->where($cond)->paginate(4,true,['query'=>array('devicenum'=>$number)]);
        $day = date('d',time());
        $page = $info->render();
        $this->assign('list',$info);
        $this->assign('page',$page);
        $this->assign('day',$day);
        return $this->fetch();
    }

    //预约成功输出编号
    public function businesssuccsee1(){
        $id = input('queue_id');//排号队列表id
        $businessname = Db::table('ph_queue')
        ->alias('a')
        ->where("a.id = '$id'")
        ->join('sys_business w','a.businessid = w.id')
        ->value('name');//查询业务名称
        $flownum = input('flownum');//编号
        // $businessname = input('businessname');//业务名称
        $arr = array('flownum' => $flownum, 'businessname' => $businessname);
        $this->assign('arr',$arr);
        return $this->fetch();
    }

    //打印机错误
    public function printerror(){
        $printererrorstr = input('printererrorstr');
		$printererrorstr=mb_convert_encoding($printererrorstr,"UTF-8","GB2312");
        $arr = array('printererrorstr' => $printererrorstr );
        $this->assign('arrs',$arr);
        return $this->fetch();
    }
    //打印中
    public function printnow(){
         $id = input('queue_id');//排号队列表id
        $businessname = Db::table('ph_queue')
        ->alias('a')
        ->where("a.id = '$id'")
        ->join('sys_business w','a.businessid = w.id')
        ->value('name');//查询业务名称
        $flownum = input('flownum');//编号
        // $businessname = input('businessname');//业务名称
        $arr = array('flownum' => $flownum, 'businessname' => $businessname);
        $this->assign('arr',$arr);
        return $this->fetch();
    }
    //打印成功
    public function printok(){
        $id = input('queue_id');//排号队列表id
        $businessname = Db::table('ph_queue')
        ->alias('a')
        ->where("a.id = '$id'")
        ->join('sys_business w','a.businessid = w.id')
        ->value('name');//查询业务名称
        $flownum = input('flownum');//编号
        // $businessname = input('businessname');//业务名称
        $arr = array('flownum' => $flownum, 'businessname' => $businessname);
        $this->assign('arr',$arr);
        return $this->fetch();
    }
    

}
