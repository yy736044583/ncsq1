<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Wapbespoke extends \think\Controller{

    public function index(){
     	
        return  $this->fetch();
    }

    public function work(){
     	$work = DB::name('wc_business')->select();
        $this->assign('work',$work);
        return  $this->fetch();
    }

    public function worktime(){
        $businessID = input("id");//业务id

     	$time = time();
        $week = array();
        for ($i=1; $i <=13; $i++) { 
            //循环 获取之后13天的日期
            $date = date('Y-m-d',strtotime("+$i"."day",$time));
            //将日期换成星期 判断在系统设置中是否有效
            $w = date('w',strtotime($date));
            //如果是星期日则换成7
            $w==0?$w=7:$w=$w;
            $where = 'Work'.$w;
            
            //判断系统设置表中星期几是否有效
            if(Db::name('wc_setup')->where("$where",1)->select()){
                //判断假日表中有效的数据不等于此日期
                if(Db::name('wc_workday')->where("Date!='$date' and Valid=1")->select()){
                    $week[$i]  = $date;
                }
            }
            //如果日期大于5天退出循环
            if(count($week)>5){
                break;
            }
        }

        //时间段
        $worktime = DB::name('wc_intervaltime')
        // ->join('wc_maxpeople w','a.Id = w.Id_intervaltime')
        ->where('Valid',1)
        ->select();
        foreach ($worktime as $key => $v) {
            $id = $v['Id'];
            $num = Db::name('wc_maxpeople')->where("Id_business=$businessID and Id_intervaltime=$id")->value('Maxpeople'); 
            if($num){
                $worktime[$key]['num'] = $num;
            }else{
               $worktime[$key]['num'] =  Db::name('wc_setup')->value('MaxPeopleDefault');
            }
        }
        dump($worktime);
        $this->assign('week',$week);
        $this->assign('worktime',$worktime);
        return  $this->fetch();
    }

    public function workpost(){
     	
        return  $this->fetch();
    }
    //获取未来5天时间
    public function days(){
        $time = time();
        $week = array();
        for ($i=1; $i <=13; $i++) { 
            //循环 获取之后13天的日期
            $date = date('Y-m-d',strtotime("+$i"."day",$time));
            //将日期换成星期 判断在系统设置中是否有效
            $w = date('w',strtotime($date));
            //如果是星期日则换成7
            $w==0?$w=7:$w=$w;
            $where = 'Work'.$w;
            
            //判断系统设置表中星期几是否有效
            if(Db::name('wc_setup')->where("$where",1)->select()){
                //判断假日表中有效的数据不等于此日期
                if(Db::name('wc_workday')->where("Date!='$date' and Valid=1")->select()){
                    $week[$i]  = $date;
                }
            }
            //如果日期大于5天退出循环
            if(count($week)>5){
                break;
            }
        }
        $this->assign('week',$week);
    }

}