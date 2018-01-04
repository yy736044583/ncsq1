<?php
namespace app\wechat\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use app\wechat\Controller\Common;

class Wapbespoke extends Common{

    public function index(){
     	
        return  $this->fetch();
    }

    public function work(){
     	$work = DB::name('sys_business')->select();
        //通过业务id判断事项id
        foreach ($work as $key => $value) {
            
            $matterid = DB::name('sys_businessmatter')->where('businessid',$value['id'])->value('matterid');

            if(!empty($matterid)){
                $work[$key]['matterok'] = 1;
            }else{
                $work[$key]['matterok'] = 0;
            }

        }

        $this->assign('work',$work);
        return  $this->fetch();
    }
    //ajax业务id查询事项名称
    public function mattername(){
        $id = input('id');
        //根据业务id查询事项id集(字符串)
        $matterid = Db::name('sys_businessmatter')->where('businessid',$id)->value('matterid');
        // 根据事项id集查询事项信息
        $list = Db::name('sys_matter')->whereIn('id',$matterid)->field('id,name,sectionid,summary')->select();
        echo json_encode($list);
    }


    public function worktime(){
        $businessID = input("id");//业务id
        $matterid = input("s_id");//事项id
     	
        $time = time();
        $week = array();
        for ($i=1; $i <=13; $i++) { 
            //循环 获取之后13天的日期
            $date = date('Y-m-d',strtotime("+$i"."day",$time));
            //将日期换成星期 判断在系统设置中是否有效
            $w = date('w',strtotime($date));
            //如果是星期日则换成7
            $w==0?$w=7:$w=$w;
            $where = 'workday_'.$w;

            // 判断是否周末
           $isweek = Db::name('sys_thiscenter')->value($where);
           $holiday = Db::name('sys_holiday')->where("day='$date' and valid=1 and workorholiday=0")->find();
           // 判断是否周末
           if($isweek){
             //判断假日表中有效的数据不等于此日期
             if(!$holiday){
                $week[$i]  = $date;
             }           
           }
           //如果是工作日就添加到数组
           if(Db::name('sys_holiday')->where("day='$date' and valid=1 and workorholiday=1")->find()){
                $week[$i]  = $date;
           }
            //如果日期大于5天退出循环
            if(count($week)>=5){
                break;
            }
        }

        $this->assign('week',$week);
        $this->assign('businessID',$businessID);
        $this->assign('matterid',$matterid);
        return  $this->fetch();
    }
       
    public function workpost(){
     	$id = input("id");
        $matterid = input("matterid");
        $StartTime = input("year")." ".input("starttime");
        $EndTime = input("year")." ".input("endtime");
        $year = input("year");
        $this->assign('id',$id);
        $this->assign('matterid',$matterid);
        $this->assign('StartTime',$StartTime);
        $this->assign('EndTime',$EndTime);
        $this->assign('year',$year);
        return  $this->fetch();
    }
  
    public function workinfo(){
        $openid = Session('openid');// $_SESSION['Openid']  //微信获取session方式
        $name = input('name');//姓名
        $tel = input('tel');//电话
        $idcard = input('idcard');//身份证号
        $id = input('id');//业务id
        $matterid = input('matterid');//事项id
        $StartTime = input('StartTime');//预约开始时间
        $EndTime = input('EndTime');//预约结束时间
        $Number = sofn_generate_num();//算法获取预约编号
        //通过业务id查询义务编号首字母
        $flownums = Db::name('sys_business')->where('id',$id)->value('flownum');
        //查询当天业务预约流水号
        $num = Db::name('wy_orderrecord')->where("businessid='$id' and starttime = '$StartTime'")->order('id desc')->value('flownum');

        if(!$num){
            $num = $flownums.'001';
        }else{
            //替换掉A 业务流水号
            $num = str_replace($flownums, '', $num);
            $sunlen = strlen($num);//字符总长 用于截取
            //排号流水号+1
            $num += '1';

            $len = intval($num);    //转换成整型
            $len = strlen($len);    //整形的长度
            //如果长度小于3位则补0 否则直接加业务流水号
            if($len<3){
                //用于截取的长度
                $len = $sunlen-$len;
                $num = $flownums.substr(strval(1000),1,$len).$num;
            }else{  
                $num = $flownums.$num;
            }
        
        }

        //如果openid为空提交失败
        if(!empty($openid)){
       
            //每个openid每天每个业务只能预约一次
            $mix = input('year')." "."00:00:00";
            $max = input('year')." "."23:59:59";
            $makemun = Db::table('wy_peopleinfo')
            ->alias('a')
            ->join('wy_orderrecord w','a.id = w.peopleid')
            ->where("a.openid = '$openid' and w.starttime > '$mix' and w.starttime < '$max' and w.businessid = '$id'")
            ->count();
            //判断预约人数
            if($makemun<1){
                $people = array('openid' => $openid, 'idcard' => $idcard, 'mobile' => $tel, 'jointime' => date("Y-m-d H:i",time()), 'name' => $name);
                $orderrecordID =  Db::name('wy_peopleinfo')->insertGetId($people);//返回主键id
                 if($orderrecordID){
                    $arrorderrecord = array('peopleid' => $orderrecordID, 'businessid' => $id, 'flownum' => $num, 'number' => $Number, 'starttime' => $StartTime, 'endtime' => $EndTime, 'uptime' => date("Y-m-d H:i",time()), 'status' =>0, 'matterid' => $matterid);
                 }
                 
                 //返回预约成功后id
                $orderrecordid =  Db::name('wy_orderrecord')->insertGetId($arrorderrecord);
                 if(!empty($orderrecordid)){
                   
                    $successinfo =  Db::name('wy_orderrecord')
                    ->alias('a')
                    ->where("a.id = '$orderrecordid'")
                    ->join('wy_peopleinfo w','a.peopleid = w.id')
                    ->find();
                    $successinfo['business_name'] = Db::name('sys_business')->where("id = $id")->value("name");//业务名称
                    // dump($successinfo);
                    $this->assign('successinfo',$successinfo);
                 }
            }
        }else{
            $this->assign('successinfo',false);
        }

        // $data['Id_business'] = $id;
        // $data['StartTime'] = $StartTime;
        // $data['EndTime'] = $EndTime;
        // $data['OrderTime'] = date("Y-m-d H:i",time());
        // $data['Status'] = 1;//0预约取消，1预约成功,2已经取号，3超时未取号
        // //查询编号是否当前时间
        // $num = Db::name('wc_orderrecord')->order('Id desc')->find();
        // if(substr($num['Number'],0,6) == substr(date("Ymd",time()),2)){
        //     $data['Number'] = $num['Number'] + 1;
        // }else{
        //     $data['Number'] = $Number;
        // }

        

        // $info['Name'] = $name;
        // $info['Mobile'] = $tel;
        // // $info['Openid'] = $_SESSION['Openid'];
        // $info['Openid'] = session('Openid');
        // //限制每个openid每天只能提交3次
        // $mix = date("Y-m-d",time())." "."00:00:00";
        // $max = date("Y-m-d",time())." "."23:59:59";
        // // $openid = $_SESSION['Openid'];
        // $openid = session('Openid');
        // $sqlcont = Db::name('wc_orderuser')
        //             ->alias('a')
        //             ->where("a.Openid = '$openid' and b.OrderTime >= '$mix' and b.OrderTime<'$max'")
        //             ->join('wc_orderrecord b','a.Id = b.Id_orderuser')
        //             ->count();
        // //提交之前判断是否重复提交
        // $Arra = array('a.Mobile' => $tel,'b.StartTime' => $StartTime,'b.EndTime' => $EndTime, );
        // $sqlinfo = Db::name('wc_orderuser')
        //             ->alias('a')
        //             ->where($Arra)
        //             ->join('wc_orderrecord b','a.Id = b.Id_orderuser')
        //             ->select();

        // if(empty($sqlinfo) && $sqlcont < 3){
        //     $sal = Db::name('wc_orderuser')->insertGetId($info);
        //     $data['Id_orderuser'] = $sal;//用户id
        //     if(!empty($sal)){
        //        $inf =  Db::name('wc_orderrecord')->insertGetId($data);//返回主键id
        //        if(!empty($inf)){
        //         $successinfo =  Db::name('wc_orderuser')
        //         ->alias('a')
        //         ->where("a.Id = $sal")
        //         ->join('wc_orderrecord w','a.Id = w.Id_orderuser')
        //         ->find();
        //         $successinfo['business_name'] = Db::name('wc_business')->where("Id = $id")->value("Name");//业务名称
        //         // dump($successinfo);
        //         $this->assign('successinfo',$successinfo);
        //        }
        //     }
        // }
        return  $this->fetch();
    }

    //ajax请求数据
     public function ajaxGet(){
        $businessID = input("id");//业务id
        $yer = input("times");//业务时间年月日2017-03-30
         //时间段
        $worktime = DB::name('wy_intervaltime')
        ->where('valid',1)
        ->select();
        foreach ($worktime as $key => $v) {
            $id = $v['id'];
            $start = $yer." ".$v['starttime'];
            $end = $yer." ".$v['endtime'];

            $data['businessid'] = $businessID;
            $data['starttime'] = $start;
            $data['endtime'] = $end;

            $num = Db::name('wy_maxpeople')->where("businessid=$businessID and intervaltimeid=$id")->value('maxpeople');//查询最大预约人数

            $conts =  Db::name('wy_orderrecord')->where($data)->count();//查询已预约人数

            //如果没设置最大人数则选择默认最大人数
            if($num){
                $maxpeople = $num;
            }else{
               $maxpeople =  $v['maxpeopledefault'];//默认最大人数
            }
           
            $worktime[$key]["endnum"] = $maxpeople-$conts;//剩余人数
        }
        echo json_encode($worktime);
     }

}