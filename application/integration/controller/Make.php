<?php
namespace app\integration\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Make extends \think\Controller{
	// 选择单位
    public function index(){
        $name = input('name');
        if($name){
            $data =  DB::name('sys_business')->where('canorder',1)->where('level','0')->whereLike('name',"%$name%")->paginate(5,true,['query'=>['name'=>$name]]);
        }else{
            $data =  DB::name('sys_business')->where('canorder',1)->where('level','0')->paginate(5,true);
        }
        $list = $data->all();
        //通过业务id判断事项id
        foreach ($list as $key => $value) {
            $businessid = Db::name('sys_business')->where('parent',$value['id'])->value('id');
            if(!empty($businessid)){
                $list[$key]['levelor'] = '1';
            }else{
                $list[$key]['levelor'] = '0';               
            }
        }
        
        $page = $data->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('name',$name);
        return  $this->fetch();
    }
    public function index1(){
        $name = input('name');
        $id = input('id');
        if($name){
            $data =  DB::name('sys_business')->where('canorder',1)->where('parent',$id)->whereLike('name',"%$name%")->paginate(5,true,['query'=>['name'=>$name,'id'=>$id]]);
        }else{
            $data =  DB::name('sys_business')->where('canorder',1)->where('parent',$id)->paginate(5,true);
        }
        $list = $data->all();
        $page = $data->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('name',$name);
        $this->assign('id',$id);
        return  $this->fetch();        
    }
    // 选择事项
    public function matter(){
        $name = input('name'); 
        $id = input('id');
        $matterid = DB::name('sys_businessmatter')->where('businessid',$id)->value('matterid');
        if($name){
            $data = Db::name('gra_matter')->whereIn('id',$matterid)->whereLike('tname',"%$name%")->field('id,tname,department,content')->paginate(5,true);
        }else{
            $data = Db::name('gra_matter')->whereIn('id',$matterid)->field('id,tname,department,content')->paginate(5,true);
        }
        $list = $data->all();
        $page = $data->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('name',$name);     
        $this->assign('id',$id);     
        return  $this->fetch();
    }
    // 添加预约页面
    public function add(){
        $businessid = input("businessid");//业务id
        $matterid = input("matterid");//事项id  
        $time = time();  
        //选择日期  
        $week = $this->week($time);
        //选择时段
        $worktime = DB::name('wy_intervaltime')->where('valid',1)->select();
        //事项名
        $tname = Db::name('gra_matter')->where('id',$matterid)->value('tname');
        //业务名称
        $name = Db::name('sys_business')->where('id',$businessid)->value('name');
        
       
        $this->assign('week',$week);
        $this->assign('worktime',$worktime);
        $this->assign('businessid',$businessid);
        $this->assign('matterid',$matterid);
        $this->assign('tname',$tname);
        $this->assign('name',$name);
        return  $this->fetch();
    }
    //预约提交
    public function upmake(){
        $data =input('post.');
        // echo json_encode($data);return;
        $info['mobile'] = $data['phone'];unset($data['phone']);
        $info['idcard'] = $data['idcard'];unset($data['idcard']);
        //时段id
        $timeid = $data['timeid'];unset($data['timeid']);
        $valtime = DB::name('wy_intervaltime')->where('id',$timeid)->find();
        //预约日期
        $times = $data['times'];unset($data['times']);
        $time = date('Y-m-d H:i:s',time());
        $info['jointime'] = $time;
 
        Db::startTrans();
        try{
            //查询是否有该手机的信息 如果有就更新
           $id = Db::name('wy_peopleinfo')->where('mobile',$info['mobile'])->value('id');
            if($id){
                Db::name('wy_peopleinfo')->where('id',$id)->update($info); 
            }else{
                //否则添加
               $id = Db::name('wy_peopleinfo')->insertGetId($info); 
            }
            //查询当天是否预约过该事项
            $oldid = Db::name('wy_orderrecord')->where('peopleid',$id)->where('matterid',$data['matterid'])->whereLike('starttime',"%$times%")->value('id');
            if(isset($oldid)){
                echo 3;return;
            }
            $Number = sofn_generate_num();//生成预约编号
            $data['number'] = $Number;//预约编号
            $data['peopleid'] = $id;//预约用户id
            $data['uptime'] = $time;//提交预约时间
            $data['starttime'] = $times.' '.$valtime['starttime'];//预约开始时间
            $data['endtime'] = $times.' '.$valtime['endtime'];//预约结束时间
            
            Db::name('wy_orderrecord')->insert($data);
            Db::commit();
            echo $Number;return;
        }catch (\Exception $e){
            Db::rollback();
            echo 2;return;
        }
    }

    //预约日期
    public function week($time){
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
        return $week;
    }
    //剩余名额
    public function endnum(){
        $yer = input("times");
        $businessid = input("businessid");
        $timeid = input("timeid");

        $busid = Db::name('sys_business')->where('id',$businessid)->value('parent');
        if(!empty($busid)){
            $businessid = $busid ;
            $businessids = Db::name('sys_business')->where('parent',$businessid)->column('id');
            $businessids = implode(',', $businessids);
        }else{
            $businessids = $businessid;
        }
        

        $id = $timeid;
        $worktime = DB::name('wy_intervaltime')->where('id',$id)->find();
        $start = $yer." ".$worktime['starttime'];
        $end = $yer." ".$worktime['endtime'];

        $data['businessid'] = ['in',$businessids];
        $data['starttime'] = ltrim($start);
        $data['endtime'] = ltrim($end);

        $num = Db::name('wy_maxpeople')->where("businessid=$businessid and intervaltimeid=$id")->value('maxpeople');//查询最大预约人数

        $conts =  Db::name('wy_orderrecord')->where($data)->count();//查询已预约人数

        //如果没设置最大人数则选择默认最大人数
        if(!is_null($num)){
            $maxpeople = $num;
        }else{
           $maxpeople =  $worktime['maxpeopledefault'];//默认最大人数
        }

        $endnum = $maxpeople-$conts;//剩余人数
        if($endnum<0){
            $endnum = '0';
        }     
        return $endnum;
    }
    // 我的预约
    public function search(){
        return  $this->fetch();
    }
    // 预约搜索详情
    public function searchshow(){
        $phone = input('phone');
        $code = input('code');

        $people =  Db::name('wy_peopleinfo')->where('mobile',$phone)->find();

        $list = Db::name('wy_orderrecord')->where('peopleid',$people['id'])->where('number',$code)->find();
        if(empty($list)){
            $this->assign('list',$list);
            $this->assign('people',$people); 
            return  $this->fetch();
        }  
        $list['business'] = Db::name('sys_business')->where('id',$list['businessid'])->value('name');
        $list['matter'] = Db::name('gra_matter')->where('id',$list['matterid'])->value('tname');
        $this->assign('list',$list);    
        $this->assign('people',$people);    
        return  $this->fetch();
    }

    // 短信验证码
    public function msgnumin(){
        $phone = input('phone');//电话号码
        $num=rand(1000,9999);//四位随机数
        $time = time();
        $endtime = time()+90;//过期时间
        //插入的数据
        $data = ['phone'=>$phone,'code'=>$num,'endtime'=>$endtime];
        //删除过期了的验证码
        Db::name('wy_ordercode')->where('endtime','lt',$time)->delete(); 

        // 启动事务
        Db::startTrans();
        try{
            //将新的验证码插入数据库
            Db::name('wy_ordercode')->insert($data);
            $set = Db::name('dx_set')->where('id',1)->find();
            $send = $this->takemessage($num,$phone,$set['sign'],$set['username']);
            // 提交事务
            Db::commit();
            echo 1;return;    
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            echo 2;return;  
        }
        
     }

     //验证是否验证码是否过期
     public function codecheck($phone,$code){
        $time = time();
        $map['phone'] = $phone;
        $map['code'] = $code;
        $map['endtime'] = ['gt',$time];
        if(Db::name('wy_ordercode')->where($map)->value('id')){
           return true;
        }else{
           return false;
        }
     }


    /**
     * [takemessage 短信提醒]
     * @param  [type] $code  [验证码]
     * @param  [type] $business [description]
     * @param  [type] $count    [description]
     * @param  [type] $phone    [description]
     * @return [type]           [description]
     */
    public function takemessage($code,$phone,$sign,$username){
        // 模板所需数据
        $json = ['code'=>$code];
        // 短信模板编号
        $code = Db::name('dx_template')->where('type',5)->value('code');

        $data1 = [
            'data'      => $json,
            'template'  => $code,
            'phone'     => $phone,
            'sign'      => $sign,
            'action'    => 'sendSms',
            'username'  => $username,
        ];
        $url = 'http://sms.scsmile.cn/inter/index';
        // url方式提交
        $httpstr = http($url, $data1, 'GET', array("Content-type: text/html; charset=utf-8"));
        return $httpstr;    
    }
}