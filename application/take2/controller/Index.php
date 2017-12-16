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
        $numbers = input('devicenum');
        $isnumber = Db::table('ph_take')->where("number = '$numbers'")->find();
        $this->assign('isnumber',$isnumber);       
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
        $info = Db::table('sys_business')->field('name,id,waitcount,day,maxnumber,startnumber')->where($cond)->paginate(9,true,['query'=>array('devicenum'=>$number)]);
        $list = $info->all();

            foreach ($list as $key => $value) {
                $arrayselce = array('id' => $list[$key]['id'], 'today' => date('Ymd'));
                $counts = Db::table('ph_queue')->where($arrayselce)->count();
                //通过业务id判断事项
                $arrmatter = array('businessid' => $list[$key]['id'], );
                $issmatter = Db::table('sys_businessmatter')->where($arrmatter)->select();
                if($issmatter){
                    $list[$key]['smatter'] = 1;
                }else{
                     $list[$key]['smatter'] = 0;
                }
                $maxnumber = $list[$key]['maxnumber'];//当天最大人数
                $startnumber = $list[$key]['startnumber'];//起始人数
                if($counts < $maxnumber + $startnumber){
                    $list[$key]['ok'] = 1;//当天排队人数小于等于最大人数可以取号
                }else{
                    $list[$key]['ok'] = 0;
                }
                
            }
        $day = date('d',time());
        $page = $info->render();
        $this->assign('list',$list);
        $this->assign('day',$day);
        $this->assign('page',$page);
        $this->assign('devicenum',$number);
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
        $info = Db::table('sys_business')->field('name,id,fromdescribe,waitcount,day,maxnumber,startnumber')->where($cond)->paginate(4,true,['query'=>array('devicenum'=>$number)]);

        $list = $info->all();

            foreach ($list as $key => $value) {
                $arrayselce = array('id' => $list[$key]['id'], 'today' => date('Ymd'));
                $counts = Db::table('ph_queue')->where($arrayselce)->count();
                //通过业务id判断事项
                $arrmatter = array('businessid' => $list[$key]['id'], );
                $issmatter = Db::table('sys_businessmatter')->where($arrmatter)->select();
                if($issmatter){
                    $list[$key]['smatter'] = 1;
                }else{
                     $list[$key]['smatter'] = 0;
                }
                $maxnumber = $list[$key]['maxnumber'];//当天最大人数
                $startnumber = $list[$key]['startnumber'];//起始人数
                if($counts < $maxnumber + $startnumber){
                    $list[$key]['ok'] = 1;//当天排队人数小于等于最大人数可以取号
                }else{
                    $list[$key]['ok'] = 0;
                }
                
            }
        $day = date('d',time());
        $page = $info->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('day',$day);
        $this->assign('devicenum',$number);
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
    //查询预约编号
    public function selcetbh(){
        $number = input('number');
        $starttime = date("Y-m-d",time())." "."23:59:59";
        $endtime = date("Y-m-d H:i",time());
        $sql = Db::table('wy_orderrecord')->where("number = '$number' and status = 0 and endtime > '$endtime' and starttime < '$starttime'")->find();
        if (!empty($sql)) {           
           //  $addarry = array('taketime' => date("Y-m-d H:i",time()), 'status' => 1 );
           // $updateinfo = Db::table('wy_orderrecord')->where("number = '$number'")->update($addarry);
           // if($updateinfo){
                echo json_encode($sql);
           // }else{
           //      echo "0";
           // }
        }else{
            echo json_encode(['info'=>0]);
        }
    }
    //选择业务事项
    public function item(){
        $id = input('id');
        $devicenum = input("devicenum");
        //根据业务id查询事项id集(字符串)
        $matterid = Db::name('sys_businessmatter')->where('businessid',$id)->value('matterid');
        // 根据事项id集查询事项信息
        $list = Db::name('sys_matter')->whereIn('id',$matterid)->field('id,name,sectionid,summary')->select();
        $this->assign('list',$list);
        $this->assign('b_id',$id);
        $this->assign('devicenum',$devicenum);
        return $this->fetch();
    }
}
