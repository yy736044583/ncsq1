<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
	take模块
	str：字符串
*/
use think\Db;	


	/**
 * 发送HTTP请求方法
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
	function http($url, $params, $method = 'GET', $header = array(), $multi = false){
	    $opts = array(
	            CURLOPT_TIMEOUT        => 30,
	            CURLOPT_RETURNTRANSFER => 1,
	            CURLOPT_SSL_VERIFYPEER => false,
	            CURLOPT_SSL_VERIFYHOST => false,
	            CURLOPT_HTTPHEADER     => $header
	    );
	    /* 根据请求类型设置特定参数 */
	    switch(strtoupper($method)){
	        case 'GET':
	            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
	            break;
	        case 'POST':
	            //判断是否传输文件
	            $params = $multi ? $params : http_build_query($params);
	            $opts[CURLOPT_URL] = $url;
	            $opts[CURLOPT_POST] = 1;
	            $opts[CURLOPT_POSTFIELDS] = $params;
	            break;
	        default:
	            throw new Exception('不支持的请求方式！');
	    }
	    /* 初始化并执行curl请求 */
	    $ch = curl_init();
	    curl_setopt_array($ch, $opts);
	    $data  = curl_exec($ch);
	    $error = curl_error($ch);
	    curl_close($ch);
	    if($error) throw new Exception('请求发生错误：' . $error);
	    return  $data;
	}

/**
	微信预约编号算法
*/
function sofn_generate_num($num_no='') {
    
    //根据时间生成字符串
    $time = date('y-m-d-H-i-s');
    $atime = explode('-', $time);
    foreach ($atime as $stime) {
        $itime = $stime * 1;
        if ($itime < 26) {
            // 65(A)-90(Z)
            $num_no.=chr(65 + $itime);
            continue;
        }
        // 48(0)-57(9)
        if ($itime >= 48 && $itime <= 57) {
            $num_no.=chr($stime);
            continue;
        }
        $num_no.=$stime;
    }
    
    //加上随机数
    $rand_s=mt_rand(1,35);
    if ($rand_s<10)
        $num_no.=chr($rand_s+48);
    else
        $num_no.=chr(65 + $rand_s-10);
    
    return $num_no;
}

/**
 * [cachequeue 叫号插入队列]
 * @param  [type] $wid        [窗口id]
 * @param  [type] $qid        [description]
 * @param  [type] $flownum    [description]
 * @param  string $businessid [description]
 * @param  string $oldqid     [description]
 * @return [type]             [description]
 */
function cachequeue($wid,$qid,$flownum,$businessid=''){

	//根据窗口id查询窗口编号
	$windowflownum= Db::name('sys_window')->where('id',$wid)->value('fromnum');
	$time = date('Y-m-d H:i:s',time());

	// 集中屏队列所需数据  窗口编号 排队编号 集中屏编号
	//根据窗口id查询集中显示屏的设备id集
	$clid = Db::name('ph_cledwindow')->where('windowid','like',"%,$wid,%")->whereor('windowid','like',"%,$wid")->whereor('windowid','like',"$wid,%")->column('cledid');

	//根据id查询集中屏编号
	foreach ($clid as $k => $v) {

		$cled[$k]['clednumber'] = Db::name('ph_cled')->where('id',$v)->value('number');
		$cled[$k]['qid'] = $qid;
		$cled[$k]['flownum'] = $flownum;
		$cled[$k]['windowflownum'] = $windowflownum;
		$cled[$k]['time'] = $time;
	}
	// 如果集中屏数据不为空就插入数据库
	empty($cled)?'':Db::name('ph_cachequeue')->insertAll($cled);

	$led = array();
	$call = array();
	// 如果有业务id则查询该业务相关的所有窗口
	// 有业务id的情况下判断为叫号  置后 选叫
	// 如果没有业务id判断为重呼
	if(!empty($businessid)){
		$wids = Db::name('sys_winbusiness')->where('businessid','like',"%,$businessid,%")->whereor('businessid','like',"%,$businessid")->whereor('businessid','like',"$businessid,%")->field('windowid,businessid')->select();

		foreach ($wids as $k => $v) {
			//根据窗口id查询窗口屏/呼叫器/评价器编号
			$lednumber = Db::name('ph_led')->where('windowid',$v['windowid'])->where('usestatus',1)->value('number');
			// echo Db::name('ph_led')->getlastsql();die;
			$callnumber = Db::name('ph_call')->where('windowid',$v['windowid'])->where('usestatus',1)->value('number');

			// 计算排队人数
			$count = 0;
			$day = date('d',time());
			$v['businessid'] = explode(',',$v['businessid']);

			//计算该窗口下所有业务的总排队人数
			foreach ($v['businessid'] as $key => $val) {
				$count += Db::name('sys_business')->where('id',$val)->where('day',$day)->value('waitcount');
			}			
			//如果窗口屏/呼叫器编号不为空就赋值给数组  并且计算当前排队人数
			if(!empty($lednumber)){
				$led['lednumber'] = $lednumber;
				$led['count'] = $count;
				$led['time'] = $time;
				if($wid==$v['windowid']){
					$led['qid'] = $qid;
					$led['flownum'] = $flownum;
				}else{
					$led['qid'] = '';
					$led['flownum'] = '';
				}
			}

			if(!empty($callnumber)){
				$call['callnumber'] = $callnumber;
				$call['count'] = $count;
				$call['time'] = $time;
				if($wid==$v['windowid']){
					$call['qid'] = $qid;
					$call['flownum'] = $flownum;
				}else{
					$call['qid'] = '';
					$call['flownum'] = '';
				}
			}
			// dump($led);
			empty($led)?'':Db::name('ph_cachequeue')->insert($led);
			empty($call)?'':Db::name('ph_cachequeue')->insert($call);
		}

	}

}

/**
 * [upevaluation 将评价插入队列]
 * @param  [type] $eid    [评价id]
 * @param  [type] $number [评价器编号]
 */
function upevaluation($eid,$number){
	$wid = Db::name('ph_call')->where('number',$number)->value('windowid');
	$devicenum = Db::name('pj_device')->where('windowid',$wid)->value('number');
	if($devicenum){
		$device['time'] = date('Y-m-d H:i:s',time());
		$device['eid'] = $eid;
		$device['online'] = 2;
		$device['devicenumber'] = $devicenum;
		Db::name('ph_cachequeue')->insert($device);		
	}

}

/**
 * [uponline 将设备在线状态提交到队列]
 * @param  [type] $lid    [窗口屏id]
 * @param  [type] $did    [评价器id]
 * @param  [type] $online [在线状态  3点击暂离 4回归 5登陆 0 离线]
 */
function uponline($lid,$did,$online){
	//窗口屏在线状态插入
	$time = date('Y-m-d H:i:s',time());
	if($lid){
		$led['lednumber'] = Db::name('ph_led')->where('id',$lid)->value('number');
		$led['online'] = $online;
		$led['time'] = $time;
		Db::name('ph_cachequeue')->insert($led);		
	}

	if($did){
		//评价器在线状态插入
		$device['devicenumber'] = Db::name('pj_device')->where('id',$did)->value('number');
		$device['online'] = $online;
		$device['time'] = $time;
		Db::name('ph_cachequeue')->insert($device);
	}

}

// 员工上线
function onfine($wid){

	$time = date('Y-m-d H:i:s',time());
	$led['lednumber'] = Db::name('ph_led')->where('windowid',$wid)->value('number');
	$led['online'] = 5;
	$led['time'] = $time;
	//如果该窗口有设备则添加到队列
	if($led['lednumber']){
		Db::name('ph_cachequeue')->insert($led);
	}

	//评价器在线状态插入
	$device['devicenumber'] = Db::name('pj_device')->where('windowid',$wid)->value('number');
	$device['online'] = 5;
	$device['time'] = $time;
	//如果该窗口有设备则添加到队列
	if($device['devicenumber']){
		Db::name('ph_cachequeue')->insert($device);
	}
}

// 在其他窗口登陆 该窗口设备下线
function downnumber($wid){
	$time = date('Y-m-d H:i:s',time());
	$call['callnumber'] = Db::name('ph_call')->where('windowid',$wid)->value('number');
	$call['online'] = 6;
	$call['time'] = $time;

	//如果该窗口有设备则添加到队列
	if($call['callnumber']){
		Db::name('ph_cachequeue')->insert($call);
	}

}

// 判断设备运行状态
// 根据当前时间和最后登陆时间 如果大于5分钟则返回false
function judgerun($lastlogin){
	$nowtime = time();
	$intime = strtotime($v['lastlogin']);
	$new = intval(date('i',$nowtime-$intime));
	if($new>=5){
		return false;
	}else{
		return true;
	}
}

	/**
	 * [createfile 创建上传文件夹]
	 * @param  [string] $file [要创建的文件夹]
	 * @return [string]       [文件夹路径地址]
	 */
	function createfile($file){
	  	//文件夹是否存在   不存在创建
    	$path1 =  ROOT_PATH . 'public' . DS . 'uploads'; // 接收文件目录
        if (!file_exists($path1)) {
            if(!mkdir($path1)){
                echo '提交失败,自动创建文件夹失败';
            }
        }
    	$path =  ROOT_PATH . 'public' . DS . 'uploads'.DS.$file; // 接收文件目录
        if (!file_exists($path)) {
            if(!mkdir($path)){
                echo '提交失败,自动创建文件夹失败';
            }
        }

        return $path;
	}

	/**
	 * [uploadfile 文件上传]
	 * @param  [string] $name [html上传name]
	 * @param  [string] $type [允许上传类型]
	 * @param  [string] $path [上传路径]
	 * @return [string]       [上传名称]
	 */
	function uploadfile($name,$type,$path){
 		// 获取表单上传文件 例如上传了001.jpg
	    $file = request()->file($name);
	    
	    $info = $file->validate(['ext'=>$type])->move($path);
	    if($info){
	    	//获取文件名称
        	$url = $info->getSaveName();
        	//转义反斜杠
        	$url = str_replace("\\","/",$url);
        	return $url;
	    }else{
	    	//返回0表示错误信息
	    	return 0;
	    } 
	}

/**
 * [get_extension 获取文件目录]
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
function get_extension($file){
	$suff = substr(strrchr($file, '.'), 1);
	return $suff;
}

/**
 * [weekday 将时间转换成星期]
 * @param  [type] $time [时间戳]
 * @return [type]       [星期]
 */
function weekday($time){
	if(is_numeric($time)){
		$weekday = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
		return $weekday[date('w',$time)];
	}
	return false;
}

//显示字符长度限制
function textlength($str,$lentht=50){
	$len = mb_strlen($str,'utf8');
	if($len>=$lentht){
		$str = mb_substr($str,0,$lentht,'utf8').'...';
	}
	return $str;
}

/**
 * [timereturn 将时间转换成 日 时分]
 * @param  [type] $time [时间]
 * @return [type]       [description]
 */
function timereturn($time){
	$time1 = date('Y-m-d H:i',strtotime($time));
	return $time1;
}

function timereturn1($time){
	$time1 = date('H:i',strtotime($time));
	return $time1;
}
function time_ago($agoTime)
{
	$agoTime = (int)$agoTime;

	// 计算出当前日期时间到之前的日期时间的毫秒数，以便进行下一步的计算
	$time = time() - $agoTime;

	if ($time >= 31104000) { // N年前
		$num = (int)($time / 31104000);
		return $num.'年前';
	}
	if ($time >= 2592000) { // N月前
		$num = (int)($time / 2592000);
		return $num.'月前';
	}
	if ($time >= 86400) { // N天前
		$num = (int)($time / 86400);
		return $num.'天前';
	}
	if ($time >= 3600) { // N小时前
		$num = (int)($time / 3600);
		return $num.'小时前';
	}
	if ($time > 60) { // N分钟前
		$num = (int)($time / 60);
		return $num.'分钟前';
	}
	return '1分钟前';
}