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
        $cond['level'] = 0;
        // $cond['valid'] = 1;
        // echo date('d');
        $info = Db::table('sys_business')->field('name,id,waitcount,day,maxnumber,startnumber,maxnumberam')->where($cond)->order('sort')->paginate(9,true,['query'=>array('devicenum'=>$number)]);
        $list = $info->all();
        // 获取默认排队人数
        $setup = Db::name('ph_setup')->where('id',1)->find();
        $setnumber = $setup['maxnumber']+$setup['maxnumberam'];
        $work = date('H:i:s',time());
        // 查询工作时间
        $worktime = Db::name('sys_thiscenter')->where('id',1)->field('worktime_e_am,worktime_s_am,worktime_e_pm,worktime_s_pm')->find();
        foreach ($list as $key => $value) {
            $arrayselce = array('id' => $list[$key]['id'], 'today' => date('Ymd'));
            $counts = Db::table('ph_queue')->where($arrayselce)->count();
            $maxnumber = $value['maxnumber']+$value['maxnumberam'];
            //如果当前时间大于下午的开始时间和小于下午结束时间则取下午的最大人数
            //如果当前时间大于上午午的结束时间则取上午的最大人数
            //下午结束时间后为休息时间 不取号
            //中午为休息时间 不取号 输出abc前端判断输出文字
            
            if($work>$worktime['worktime_s_pm']&&$work<$worktime['worktime_e_pm']){
                $maxnumber = $maxnumber;
                $setnumber = $setnumber;
            }elseif($work<$worktime['worktime_e_am']){
                $maxnumber = $value['maxnumberam'];
                $setnumber = $setup['maxnumberam'];
            }elseif($work>$worktime['worktime_e_pm']){
                $list[$key]['error'] = 2;
            }else{
                $list[$key]['error'] = 2;
            }
            //通过业务id判断事项
            $arrmatter = array('businessid' => $list[$key]['id'], );
            $issmatter = Db::table('sys_businessmatter')->where($arrmatter)->select();
            if($issmatter){
                $list[$key]['smatter'] = 1;
            }else{
                 $list[$key]['smatter'] = 0;
            }
            // 获取默认排队人数
            $setup = Db::name('ph_setup')->where('id',1)->find();

            // 如果该业务没有设置排队人数则使用默认排队人数
            $maxnumber = !empty($maxnumber)?$maxnumber:$setnumber;//当天最大人数
            $startnumber = !empty($list[$key]['startnumber'])?$list[$key]['startnumber']:$setup['startnumber'];//起始人数
            if($counts < $maxnumber){
                $list[$key]['ok'] = 1;//当天排队人数小于等于最大人数可以取号
            }else{
                $list[$key]['ok'] = 0;
            }
            //根据业务id查询是否有二级业务
            $bus2 = Db::name('sys_business')->where('parent',$value['id'])->value('id');
            $list[$key]['bus2'] = !empty($bus2)?1:0;
            $day = date('d',time());
            // 如果有二级业务就查询一级业务下的所有二级业务的排对人数
            if($bus2){
                $sumcount = 0;
                $waitcount2 = Db::name('sys_business')->where('parent',$value['id'])->where('day',$day)->column('waitcount');
                foreach ($waitcount2 as $k => $v) {
                    $sumcount += $v;
                }
                $list[$key]['waitcount'] = $sumcount;
            }
            
        }
        $isnumber = Db::table('ph_take')->where("number = '$number'")->find();
        //查询事是否可现场 手机取号
        $this->assign('isnumber',$isnumber); 
        $day = date('d',time());
        $page = $info->render();
        $this->assign('list',$list);
        $this->assign('day',$day);
        $this->assign('page',$page);
        $this->assign('devicenum',$number);
        return $this->fetch();
    }
    public function businesschild(){
        // 获取设备编号
        $number = input('devicenum');
        $id = input('id');
        $takeid = Db::table('ph_take')->where("number = '$number'")->value('id');
        $businessid = Db::table('ph_takebusiness')->where("takeid = '$takeid'")->value('businessid');
        $businessids = explode(',', $businessid);
        $cond['id'] = array('in',$businessids);
        $cond['valid'] = 1;
        $cond['parent'] = $id;

        $info = Db::table('sys_business')->field('name,id,waitcount,day,maxnumber,startnumber,maxnumberam')->where($cond)->paginate(12,true,['query'=>array('devicenum'=>$number)]);
        $list = $info->all();
        // dump($list);die;
        foreach ($list as $key => $value) {
            $arrayselce = array('businessid' => $value['id'], 'today' => date('Ymd'));
            $counts = Db::table('ph_queue')->where($arrayselce)->count();
            //通过业务id判断事项
            $arrmatter = array('businessid' => $value['id'], );
            $issmatter = Db::table('sys_businessmatter')->where($arrmatter)->find();
            
            if($issmatter['matterid']){
                $list[$key]['smatter'] = 1;
            }else{
                 $list[$key]['smatter'] = 0;
            }
            // 获取默认排队人数
            $setup = Db::name('ph_setup')->where('id',1)->find();
            // 如果该业务没有设置排队人数则使用默认排队人数
            $maxnumber = !empty($value['maxnumber'])?$value['maxnumber']:$setup['maxnumber'];//当天最大人数
            $startnumber = !empty($value['startnumber'])?$value['startnumber']:$setup['startnumber'];//起始人数
            // echo $counts.'<br>';
            if($counts < $maxnumber){
                $list[$key]['ok'] = 1;//当天排队人数小于等于最大人数可以取号
            }else{
                $list[$key]['ok'] = 0;
            }
            
        }
        $isnumber = Db::table('ph_take')->where("number = '$number'")->find();//查询事是否可现场 手机取号
        $this->assign('isnumber',$isnumber); 
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
        $list = Db::name('gra_matter')->whereIn('id',$matterid)->field('id,tname,deptid,content')->select();
        $isnumber = Db::table('ph_take')->where("number = '$devicenum'")->find();//查询事是否可现场 手机取号
        $this->assign('isnumber',$isnumber);  
        $this->assign('list',$list);
        $this->assign('b_id',$id);
        $this->assign('devicenum',$devicenum);
        return $this->fetch();
    }
    // 提交相关信息
    public function datainfo(){
        $devicenum = input("devicenum");
        $idcard = input("idcard");
        $data['devicenum'] = input("devicenum");
        $data['b_id'] = input("b_id");
        $data['s_id'] = input("s_id");
        $data['idcard'] = input("idcard");
        $data['tel'] = input("tel");
        $isnumber = Db::table('ph_take')->where("number = '$devicenum'")->find();//查询是否可身份证或者手机取号
        if($isnumber['phone'] == 1 && !empty(input("idcard")) && empty(input("tel"))){
            //查询上次扫描身份证所留手机号
            $tel = Db::table('sys_peoples')->where("idcard = '$idcard'")->value('phone');
            $data['tel'] = $tel;
            
        }
        $this->assign('info',$data);
        $this->assign('isnumber',$isnumber);
        return $this->fetch();
    }
}
