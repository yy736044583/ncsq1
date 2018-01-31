<?php
namespace app\wechat\controller;

use think\Db;
use think\Request;
use app\wechat\Controller\Common;

class Business extends Common{
	// 首页
    public function index(){
        $nid = input('nid');
        $legal = input('legal');
        $section = !empty($section)?$section:1;
        $nid = !empty($nid)?$nid:1;
        $nid = $legal==1?0:$nid;
        $map = array();
        $map1 = array();
        // 根据前端的值查询部门表或者主题表
        //个人办事
	    if($nid)$map['nid'] = 1;
        //法人办事
	    if($legal)$map['nid'] = 2;
        $themelist = Db::name('gra_theme')->where($map)->select();

        //个人办事
	    if($nid)$map1['nid'] = 1;
        //法人办事
        if($legal)$map1['legal'] = 1;
        $sectionlist = Db::name('gra_section')->where($map1)->select();

        $this->assign('nid',$nid);
        $this->assign('legal',$legal);
        $this->assign('sectionlist',$sectionlist);
        $this->assign('themelist',$themelist);
        return  $this->fetch();

    }
    //选择事项
    public function matter(){
	    $theme = input('theme');//主题名称
	    $deptid = input('deptid');//部门id
	    $name = input('name');//搜索事项标题
	    $map = array();
	    if($theme){
	    	$map['theme'] = $theme;
	    }
	    if($deptid){
	    	$map['deptid'] = $deptid;
	    }
	    if($name){
	    	$map['tname'] = ['like',"%$name%"];
	    }
	    $data = Db::name('gra_matter')->where($map)->field('id,tname,telephone')->paginate(12);
	    $list = $data->all();
	    $page = $data->render();
	    $this->assign('list',$list);
	    $this->assign('page',$page);
	    $this->assign('theme',$theme);
	    $this->assign('deptid',$deptid);
        return  $this->fetch();
    }
    //ajax请求数据 进行分页
    public  function  matterlist(){
	    $theme = input('theme');//主题名称
	    $deptid = input('deptid');//部门id
	    $pageindex = input('pageindex');
	    $pagesize = input('pagesize');
	    $map = array();
	    if($theme){
		    $map['theme'] = $theme;
	    }
	    if($deptid){
		    $map['deptid'] = $deptid;
	    }
	    $data = Db::name('gra_matter')->where($map)->field('id,tname,telephone')->page($pageindex,$pagesize)->select();
	    echo json_encode($data);
    }
    //事项详情
    public function mattershow(Request $request){
     	$id = input('id');
     	$list =  Db::name('gra_matter')->where('id',$id)->find();
     	//应交材料
        $datlist = Db::name('gra_datum')->where("matterid",$list['id'])->order('sort')->select();
        foreach ($datlist as $k => $v) {
            if($v['nullurl']){
                 $datlist[$k]['nullurl'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public'.$v['nullurl'];
            }
        }
        
        //审批条件 
        $gscsetlist = Db::name('gra_accept')->where("matterid",$list['id'])->select();
        
        //法定依据
        $warlist = Db::name('gra_warrntset')->where("matterid",$list['id'])->select();
        //办理流程
        
        $flowlimit = Db::name('gra_flowlimit')->where("matterid",$list['id'])->group('flowlimit')->select();
        foreach ($flowlimit as $k => $v) {
            switch ($v['flowlimit']) {
                case '1':
                   $flowlimit[$k]['flowlimit'] = '申请受理';
                    break;
                case '2':
                   $flowlimit[$k]['flowlimit'] = '审核';
                    break;
                case '3':
                   $flowlimit[$k]['flowlimit'] = '办结';
                    break;
                case '4':
                   $flowlimit[$k]['flowlimit'] = '制证';
                    break;
                case '5':
                   $flowlimit[$k]['flowlimit'] = '取件';
                    break;
                case '6':
                   $flowlimit[$k]['flowlimit'] = '办理';
                    break;
                case '7':
                   $flowlimit[$k]['flowlimit'] = '决定';
                    break;
                case '8':
                   $flowlimit[$k]['flowlimit'] = '证明';
                    break;
                case '9':
                   $flowlimit[$k]['flowlimit'] = '核实';
                    break;
                case '10':
                   $flowlimit[$k]['flowlimit'] = '答复';
                    break;
                case '0':
                   $flowlimit[$k]['flowlimit'] = '其他';
                    break;
                default:
                    # code...
                    break;
            }
        }

	    $this->assign('warlist',$warlist); 
        $this->assign('gscsetlist',$gscsetlist);  
        $this->assign('datlist',$datlist);       
        $this->assign('flowlimit',$flowlimit);
        $this->assign('list',$list);
        return  $this->fetch();

    }
    
    // 乡镇政务中心
    public function govlist(){
    	$name = input('name');
    	$map = array();
    	if($name){
    		$map['name'] = ['like',"%$name%"];
	    }
     	$list = Db::name('gra_towns')->where($map)->select();
     	$this->assign('list',$list);
     	$this->assign('name',$name);
        return  $this->fetch();

    }
    // 乡镇政务中心
    public function govshow(){
        $id = input('id');
        $list = Db::name('gra_towns')->where('id',$id)->find();
	    $this->assign('list',$list);
        // 调用微信公众号
        $jssdk = $this->weisdk();
        dupm($jssdk);
        return  $this->fetch();

    }
    // 调用微信公众号
    public function weisdk(){
        $appId = 'wx71c245013bbb9567';
        $appsecret = '0de31bc94bd9560fa8bf9b66ac58eb10';
        $timestamp = time();
        $jsapi_ticket = $this->make_ticket($appId,$appsecret);
        $nonceStr = $this->make_nonceStr();
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
         // $url = $_GET['url'];
        $signature = $this->make_signature($nonceStr,$timestamp,$jsapi_ticket,$url);
        
        

        $weidata = array('appId' => $appId, 'timestamp' => $timestamp, 'nonceStr'=> $nonceStr, 'signature' => $signature, 'url' => $url);
        return $weidata;
    }

    public function make_ticket($appId,$appsecret) {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents("http://".$_SERVER['HTTP_HOST']."/ncsq/access_token.json"));
        if ($data->expire_time < time()) {
            $TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appsecret;
            $json = file_get_contents($TOKEN_URL);
            $result = json_decode($json,true);
            $access_token = $result['access_token'];
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $fp = fopen("http://".$_SERVER['HTTP_HOST']."/ncsq/access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        }else{
            $access_token = $data->access_token;
        }
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents("http://".$_SERVER['HTTP_HOST']."/ncsq/jsapi_ticket.json"));
        if ($data->expire_time < time()) {
            $ticket_URL="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
            $json = file_get_contents($ticket_URL);
            $result = json_decode($json,true);
            $ticket = $result['ticket'];
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $fp = fopen("http://".$_SERVER['HTTP_HOST']."/ncsq/jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        }else{
            $ticket = $data->jsapi_ticket;
        }
        return $ticket;
    }
    public function make_nonceStr()
    {
        $codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i<16; $i++) {
            $codes[$i] = $codeSet[mt_rand(0, strlen($codeSet)-1)];
        }
        $nonceStr = implode($codes);
        return $nonceStr;
    }
    public function make_signature($nonceStr,$timestamp,$jsapi_ticket,$url)
    {
        $tmpArr = array(
        'noncestr' => $nonceStr,
        'timestamp' => $timestamp,
        'jsapi_ticket' => $jsapi_ticket,
        'url' => $url
        );
        ksort($tmpArr, SORT_STRING);
        $string1 = http_build_query( $tmpArr );
        $string1 = urldecode( $string1 );
        $signature = sha1( $string1 );
        return $signature;
    }
}