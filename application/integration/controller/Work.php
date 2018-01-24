<?php
namespace app\integration\controller;
//use think\Controller;
use think\View;
use think\Db;
use think\Request;


class Work extends \think\Controller{
	// 选择事项
    public function index(){
        $nid = input('nid');//个人服务
        $legal = input('legal');//法人服务
        $themename = input('themename');//主题
        $deptid = input('deptid');//部门
        $name = input('name');//事项关键字
        $map = array();//查询主题条件
        $map1 = array();//查询部门条件
        $map2 = array();//查询事项条件
        if(!empty($nid)){
            $map['nid'] = 1;
            $map1['nid'] = 1;
            $map2['nid'] = 1;
        }
        if(!empty($legal)){
            $map['nid'] = 2;
            $map1['legal'] = 1;
            $map2['legal'] = 1;
        }
        if(!empty($themename)){
            $map2['theme'] = $themename;
        }
        if(!empty($deptid)){
            $map2['deptid'] = $deptid;
            //部门名称
            $tname = Db::name('gra_section')->where('tid',$deptid)->value('tname');
            $this->assign('tname',$tname);
        }
        if(!empty($name)){
            $map2['tname'] = ['like',"%$name%"];

        }
        // dump($map2);die;
        //事项列表
        $list = Db::name('gra_matter')->where($map2)->field('id,tname,department')->paginate(6,true);
        //根据个人服务还是法人服务查询主题
        $theme = Db::name('gra_theme')->where($map)->field('id,tname,tid')->select();
        //根据个人服务还是法人服务查询部门
        $section = Db::name('gra_section')->where($map1)->field('id,tid,tname')->select();
        $page = $list->render();
        $this->assign('nid',$nid);
        $this->assign('legal',$legal);
        $this->assign('theme',$theme);
        $this->assign('section',$section);
        $this->assign('themename',$themename);
        $this->assign('deptid',$deptid);
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('name',$name);
        return  $this->fetch();
    }


    //热门事项
    public function index1(){
        $nid = input('nid');//个人服务
        $legal = input('legal');//法人服务
        $map = array();
        $map1 = array();
        $map2 = array();
        if(!empty($nid)){
            $map['nid'] = 1;
            $map1['nid'] = 1; 
        }else{
            $nid= 1;
        }
        
        if(!empty($legal)){
            $map['nid'] = 2;
            $map1['legal'] = 1;
            $map2['legal'] = 1;
            $nid = '';
        }
        $map2['nid'] = $nid;
        //根据个人服务还是法人服务查询主题
        $theme = Db::name('gra_theme')->where($map)->field('id,tname,tid')->select();
        //根据个人服务还是法人服务查询部门
        $section = Db::name('gra_section')->where($map1)->field('id,tid,tname')->select();
        $list = Db::name('gra_matter')->where($map2)->order('sort desc')->field('id,tname,department')->paginate(6,true);
        $page = $list->render();

        $this->assign('nid',$nid);
        $this->assign('legal',$legal);
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('theme',$theme);
        $this->assign('section',$section);
        return  $this->fetch();
    }
    // 事项详情
    public function matter(Request $request){
        $id = input('id');
        $list = Db::name('gra_matter')->where('id',$id)->find();
 
        //应交材料
        $datlist = Db::name('gra_datum')->where("matterid",$list['id'])->order('sort')->select();
        foreach ($datlist as $k => $v) {
            if($v['nullurl']){
                 $datlist[$k]['nullurl'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public'.$v['nullurl'];
            }
        }
        $count =   Db::name('gra_datum')->where("matterid",$list['id'])->count();
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
        $this->assign('count',$count);
        return  $this->fetch();
    }
    // 扫描身份证  
    public function idcard(){
        $id = input('id');
        $this->assign('id',$id);
        return  $this->fetch();
    }
    //上传身份证照片
    public function upidcard_picture(){
        $data = input('post.');
        $card = $data['idcard'];
        // $card = '513721199309055040';
        //创建存储路径
        $path = $this->createfile('idcard',$card);
        //根据身份证号查询用户信息id和证件照地址
        $info = Db::name('sys_peopleinfo')->where('idcard_IDCardNo',$card)->field('id,idcardData_PhotoFileName')->find();
        
        if(!empty($data['file'])){
            $url = $this->base64up($path,$data['file']);
            $url = '/uploads/idcard/'.$card.'/'.$url;
        }
        //用户信息表中有该用户数据则更新
        if(!empty($info)){
            if(!empty($url)){
                //将新的证件照更新到用户信息表中
                Db::name('sys_peopleinfo')->where('id',$info['id'])->update(['idcardData_PhotoFileName'=>$url]);

                //拼接要删除的图片地址
                $dlurl = ROOT_PATH.DS.'/public'.$info['idcardData_PhotoFileName'];
                
                //删除图片地址
                if(file_exists($dlurl)){
                    unlink($dlurl);
                }
            }
            echo json_encode(['code'=>200,'data'=>['id'=>$info['id']]]);
        }else{
            //如果用户信息表中没有该身份证信息增添加
            if(!empty($url)){
                $data1['idcardData_PhotoFileName'] = $url;
            }
            $data1['idcard_IDCardNo'] = $card;
            $data1['idcard_Name'] = $data['idcard_Name'];
            $data1['idcard_Sex'] = $data['idcard_Sex'];
            $data1['idcard_Nation'] = $data['idcard_Nation'];
            $data1['idcard_Born'] = $data['idcard_Born'];
            $data1['idcard_Address'] = $data['idcard_Address'];
            $data1['idcard_GrantDept'] = $data['idcard_GrantDept'];
            $data1['idcard_UserLifeBegin'] = $data['idcard_UserLifeBegin'];
            $data1['idcard_UserLifeEnd'] = $data['idcard_UserLifeEnd'];
            $id = Db::name('sys_peopleinfo')->insert($data1);
            echo json_encode(['code'=>200,'data'=>['id'=>$id]]);
        }
    }
    // 手机验证  
    public function phone(){
        $card = input('card');//身份证号
        $name = input('name');//姓名
        $sex = input('sex');//性别
        $userid = input('userid');//用户id
        $matterid =  input('matterid');//事项id
        $arrayName = array('card' => $card, 'name' => $name, 'sex' => $sex,'userid'=>$userid);
        $this->assign('name',$arrayName);
        $this->assign('matterid',$matterid);
        return  $this->fetch();
    }

    //验证是否验证码是否过期
    public function codecheck(){
        $data = input('post.');
        $code = $data['code'];
        $phone = $data['phone'];
        unset($data['code']);
        
        $time = time();
        $map['phone'] = $phone;
        $map['code'] = $code;
        $map['endtime'] = ['gt',$time];
        if(Db::name('wy_ordercode')->where($map)->value('id')){
            //将用户信息提交到数据库保存
            if($this->upphone($data)){
                return true;
            }else{
                return false;
            }  
        }else{
            return false;
        }
    }
    //提交用户信息 或者公司信息
    public function upphone($data){
        $id = $data['id'];
        unset($data['id']);
        if(Db::name('sys_peopleinfo')->where('id',$id)->update($data)){
            return 1;
        }else{
            return 2;
        }
    }

    // 人脸识别页面 
    public function face(){
        $userid = input('userid');
        $matterid = input('matterid');
        $photo = Db::name('sys_peopleinfo')->where('id',$userid)->value('idcardData_PhotoFileName');
        $url = ROOT_PATH.'public'.$photo;
        //将证件照转换成base64输出
        $photo1 = $this->base64EncodeImage($url);
        $this->assign('photo',$photo1);
        $this->assign('userid',$userid);
        $this->assign('matterid',$matterid);
        return  $this->fetch();
    }

    //上传人脸比对成功照片
    public function upfacepicture(){
        $userid = input('userid');
        $file = input('file');
        $photo = Db::name('sys_peopleinfo')->where('id',$userid)->field('id,idcard_IDCardNo,picture,idcard_IDCardNo')->find();
        //拼接要存储的图片位置
        $path = ROOT_PATH.'public/uploads/idcard/'.$photo['idcard_IDCardNo'].'/';
        //将base64转成图片
        $url = $this->base64up($path,$file,$photo['idcard_IDCardNo']);
        if(isset($url)){
            $url = '/uploads/idcard/'.$photo['idcard_IDCardNo'].'/'.$url;
        }
        //将比对照片上传数据库
        if(Db::name('sys_peopleinfo')->where('id',$userid)->update(['picture'=>$url])){
            // 如果之前有照片 进行删除
            if(!empty($photo['picture'])){
                //拼接要删除的图片地址
                $dlurl = ROOT_PATH.DS.'/public'.$photo['picture'];
                //删除图片地址
                if(file_exists($dlurl)){
                    unlink($dlurl);
                }            
            } 
            echo 1;           
        }       
    }

    //人脸识别接口
    public function facedata(Request $request){
        $type = input('type');
        $userid = input('userid');
        $url2 = input('url2');
        $data['type'] = $type;
        $path = ROOT_PATH.'public/uploads/tempfile/';  
        // echo $url2;die;  
        $path1 = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public';
         
        $url1 = Db::name('sys_peopleinfo')->where('id',$userid)->value('idcardData_PhotoFileName');
        $url1 = $path1.$url1;
        $url = '/uploads/tempfile/'.$this->base64up($path,$url2,'');
        $url2 = $path1.$url;
        
        $data['image_url_1'] = $url1;
        $data['image_url_2'] = $url2;
        $data = json_encode($data);
        $file = $this->faceapp($data);
        //删除临时文件
        $dlurl = ROOT_PATH.'/public'.$url;
        if(file_exists($dlurl)){
            unlink($dlurl);
        }
        echo $file;
    }
    //人脸识别接口
    public function faceapp($data){
        
        // echo $data;return;
        $akId = "LTAIs9LpbiK62pgL";
        $akSecret = "oQTLUnOycwSiN04Wmqq5w50B8nJ9ec";
        //更新api信息
        $url = "https://dtplus-cn-shanghai.data.aliyuncs.com/face/verify";
        $options = array(
            'http' => array(
                'header' => array(
                    'accept'=> "application/json",
                    'content-type'=> "application/json",
                    'date'=> gmdate("D, d M Y H:i:s \G\M\T"),
                    'authorization' => ''
                ),
                'method' => "POST", //可以是 GET, POST, DELETE, PUT
                'content' => $data //如有数据，请用json_encode()进行编码
            )
        );
        $http = $options['http'];
        $header = $http['header'];
        $urlObj = parse_url($url);
        if(empty($urlObj["query"]))
            $path = $urlObj["path"];
        else
            $path = $urlObj["path"]."?".$urlObj["query"];
        $body = $http['content'];
        if(empty($body))
            $bodymd5 = $body;
        else
            $bodymd5 = base64_encode(md5($body,true));
        $stringToSign = $http['method']."\n".$header['accept']."\n".$bodymd5."\n".$header['content-type']."\n".$header['date']."\n".$path;
        $signature = base64_encode(
            hash_hmac(
                "sha1",
                $stringToSign,
                $akSecret, true));
        $authHeader = "Dataplus "."$akId".":"."$signature";
        $options['http']['header']['authorization'] = $authHeader;
        $options['http']['header'] = implode(
            array_map(
                function($key, $val){
                    return $key.":".$val."\r\n";
                },
                array_keys($options['http']['header']),
                $options['http']['header']));
        $context = stream_context_create($options);
        $file = file_get_contents($url, false, $context );
        return $file;
        // echo($file);
    }

    // 指纹识别  
    public function fingerprint(){
        return  $this->fetch();
    }
    // 承诺书  
    public function letter(){
        $userid = input('userid');
        $matterid = input('matterid');
        $time = time();
        $data['userid'] = $userid;
        $data['matterid'] = $matterid;
        $data['createtime'] = $time;
        $people = Db::name('sys_peopleinfo')->where('id',$userid)->field('id,picture')->find();
        //删除1小时前未提交的数据
        $this->dlmatterdatum($time);
        //添加数据
        $id = Db::name('gra_matterdatum')->insertGetId($data);
        $this->assign('userid',$userid);
        $this->assign('matterid',$matterid);
        $this->assign('fdatumid',$id);
        $this->assign('people',$people);
        return  $this->fetch();
    }

    /**
     * 上传签名照片
     * @param userid 用户id
     * @param id 事项材料表id
     * @return array
     */
    public function upsign(){
        $userid = input('userid');//用户id
        $id = input('fdatumid');//事项材料表id
        $file = input('file');
        //用户信息
        $people = Db::name('sys_peopleinfo')->where('id',$userid)->field('id,idcard_IDCardNo')->find();
        //签名照片  如果有的话需要删除
        $photo = Db::name('gra_matterdatum')->where('id',$id)->value('sign');
        //拼接要存储的图片位置
        $path = $this->createfile('datumfile',$people['idcard_IDCardNo']);
        //将base64转成图片
        $url = $this->base64up($path,$file);
//        echo json_encode($url);die;
        if(isset($url)){
            $url = '/uploads/datumfile/'.$people['idcard_IDCardNo'].'/'.$url;
        }
        //将比对照片上传数据库
        if(Db::name('gra_matterdatum')->where('id',$id)->update(['sign'=>$url])){
            // 如果之前有照片 进行删除
            if(!empty($photo)){
                //拼接要删除的图片地址
                $dlurl = ROOT_PATH.DS.'/public'.$photo;
                //删除图片地址
                if(file_exists($dlurl)){
                    unlink($dlurl);
                }
            }
            echo 1;
        }
    }
    // 选择资料 
    public function file(Request $request){
        $userid = input('userid');
        $matterid = input('matterid');
        $fdatumid = input('fdatumid');
        //事项信息
        $list = Db::name('gra_matter')->where('id',$matterid)->field('id,tname')->find();
        //应交材料
        $datlist = Db::name('gra_datum')->where("matterid",$matterid)->order('sort')->select();
        $count = 0;//统计应提交的资料数
        foreach ($datlist as $k => $v) {
            if($v['nullurl']){
                $datlist[$k]['nullurl'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public'.$v['nullurl'];
                $count +=1;
            }
            $datlist[$k]['picture'] = Db::name('gra_datumfile')->where('datumid',$v['id'])->where('fdatumid',$fdatumid)->field('file,id')->select();
        }
        // dump($datlist);die;
        $this->assign('userid',$userid);
        $this->assign('list',$list);
        $this->assign('count',$count);
        $this->assign('datlist',$datlist);
        $this->assign('fdatumid',$fdatumid);
        $this->assign('matterid',$matterid);
        return  $this->fetch();
    }
    /**
     * [dlmatterdatum 删除1小时前未提交的资料]
     * @param  [time] $start [时间]
     * @return [type]        [description]
     */
    public function dlmatterdatum($time){
        $start = strtotime(date('Y-m-d',$time)); //开始时间 当天零点
        $end = $time-60*60; //结束时间 60分钟前
        $path1 = ROOT_PATH.'public';
        //删除1小时前未提交的数据
        $dlid = Db::name('gra_matterdatum')->where('createtime','>',$start)->where('createtime','<',$end)->where('type',1)->column('id');
        //删除用户事项表下所有上传的资料
        foreach ($dlid as $k => $v) {
            //查询全部上传信息 并删除
            $dlfile = Db::name('gra_datumfile')->where('fdatumid',$v)->field('file,id')->find();
            //删除数据
            Db::name('gra_datumfile')->where('id',$dlfile['id'])->delete();
            //删除文件
            if($dlfile['file']){
                $dlurl = $path1.$dlfile['file'];
                if(file_exists($dlurl)){
                    unlink($dlurl);
                }
            } 
        }
    }
    //资料拍照上传
    public function fileload(){

        $time = time();
        $start = strtotime(date('Y-m-d',$time)); //开始时间 当天零点
        $end = $time-60*60; //结束时间 60分钟前
        $path1 = ROOT_PATH.'public';
        //查询所有在这个时间范围内需要删除的数据
        $fileurl = Db::name('gra_datumfile')->where('createtime','>',$start)->where('createtime','<',$end)->where("type",1)->column('file');
        //删除这个时间范围内没有提交的文件
        foreach ($fileurl as $k => $v) {
            if($v){
                $dlurl = $path1.$v;
                if(file_exists($dlurl)){
                    unlink($dlurl);
                }
            }
                    }
        $data1 = input('post.');
        $userid = $data1['userid'];
        $datumid = $data1['datumid'];
        $fdatumid = $data1['fdatumid'];//用户事项材料表id
        $file = $data1['file'];

        $card = Db::name('sys_peopleinfo')->where('id',$userid)->value('idcard_IDCardNo');
        $path = $this->createfile('datumfile',$card);//创建资料上传文件夹
        foreach ($file as $k => $v) {
            $url = '';
            //拼接需要返回的url
            $url = $this->base64up($path,$v);
            if($url){
               $url = '/uploads/datumfile/'.$card.'/'.$url; 
            }
            $data[$k]['fdatumid'] = $fdatumid;
            $data[$k]['datumid'] = $datumid;
            $data[$k]['file'] = $url;
            $data[$k]['createtime'] = $time;
            $data[$k]['type'] = 1; //1资料提交  2确认提交       
        }
      
        Db::name('gra_datumfile')->insertAll($data);
        echo 1;return;
        
    }
    //资料确认提交
    public function submitfile(){
        $fdatumid = input('fdatumid');
        Db::startTrans();
        try{
            Db::name('gra_matterdatum')->where('id',$fdatumid)->update(['type'=>2]);
            Db::name('gra_datumfile')->where('fdatumid',$fdatumid)->update(['type'=>2]);
            // 提交事务
            Db::commit();
            echo 1;    
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            echo 2;
        }
    }
    //删除图片
    public function dlfile(){
        $id = input('id');
        $path1 = ROOT_PATH.'public';
        $url =  Db::name('gra_datumfile')->where('id',$id)->value('file');
        // echo $id.'-'.$url;die;
        //删除数据库后删除文件
        if(Db::name('gra_datumfile')->where('id',$id)->delete()){
            if($url){
                $dlurl = $path1.$url;
                if(file_exists($dlurl)){
                    unlink($dlurl);
                } 
            }
            echo 1;            
        }     
    }

    // 上传资料  
    public function fileup(){
        $userid = input('userid');
        $fdatumid = input('fdatumid');
        $datumid = input('datumid');
        $matterid = input('matterid');
        $this->assign('userid',$userid);
        $this->assign('fdatumid',$fdatumid);
        $this->assign('datumid',$datumid);
        $this->assign('matterid',$matterid);
        return  $this->fetch();
    }
    // 取件方式  
    public function pickup(){
        $userid = input('userid');
        $fdatumid = input('fdatumid');
        $phone = Db::name('sys_peopleinfo')->where('id',$userid)->value('phone');
        $this->assign('phone',$phone);
        $this->assign('fdatumid',$fdatumid);
        return  $this->fetch();
    }
    //提交邮寄地址 联系电话 联系人
    public function upaddress(){
        $data = input('post.');
        $fdatumid = $data['fdatumid'];
        unset($data['fdatumid']);
        if(Db::name('gra_matterdatum')->where('id',$fdatumid)->update($data)){
            echo 1;return;
        }else{
            echo 2;return;
        }
        
    }
    // 打印小票  
    public function prin(){
        return  $this->fetch();
    }

    /**
     * [base64up base64图片上传解码]
     * @param  [string] $path [存储路径]
     * @param  [array] $data [base64文件 数组]
     * @return [array]       [身份证照片 url ]
     */
    public function base64up($path,$data){
        $num = rand('1','99');
        // 创建文件名
        preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result);

//        $img = base64_decode($data);

//        $type = $result[2];
        // 存放路径
        $new_file = $path."/";
        $url = $new_file.time().$num.".jpg";

        // 拼接存入数据库的路径
        $url1 = '/'.time().$num.".jpg";
     
        // 解码base64 存放文件
        file_put_contents($url, base64_decode(str_replace($result[1], '', $data)));
        return $url1; 
    }


    //将图片装成base64
    public function base64EncodeImage ($image_file) {
      $base64_image = '';
      $image_info = getimagesize($image_file);
      $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
      $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
      return $base64_image;
    }

    /**
     * [createfile 创建上传文件夹]
     * @param  [string] $file [要创建的文件夹]
     * @return [string]       [文件夹路径地址]
     */
    function createfile($file,$card=''){
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
        if(!empty($card)){
            $path2 =  $path.DS.$card; // 接收文件目录
            if (!file_exists($path2)) {
                if(!mkdir($path2)){
                    echo '提交失败,自动创建文件夹失败';
                }
            }
            return $path2;   
        }else{
            return $path;
        }
 
    }  
    //身份验证
    // 短信验证码
     public function msgnumin(){
        $phone = input('phone');//电话号码
        $num=rand(100000,999999);//六位随机数
        $time = time();
        $endtime = time()+60;//过期时间
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

    //取件码
    // 短信验证码
     public function msggetnum(){
        $phone = input('phone');//电话号码
        $fdatumid = input('fdatumid');//用户事项材料表id
        $num=$this->sofn_generate_num(4);//10+4位随机数

        // 启动事务
        Db::startTrans();
        try{
            //将新的验证码插入数据库
            Db::name('gra_matterdatum')->where('id',$fdatumid)->update(['manner'=>1,'getnum'=>$num]);
            $set = Db::name('dx_set')->where('id',1)->find();
            $send = $this->getmessage($num,$phone,$set['sign'],$set['username']);
            // 提交事务
            Db::commit();
            echo 1;return;    
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            echo 2;return;  
        } 
     }


    /**
     * [takemessage 身份验证短信验证码]
     * @param  [type] $code  [验证码]
     * @param  [type] $sign [签名]
     * @param  [type] $username    [用户名]
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

    /**
     * [getmessage 取件短信验证码]
     * @param  [type] $code  [验证码]
     * @param  [type] $sign [签名]
     * @param  [type] $username    [用户名]
     * @param  [type] $phone    [description]
     * @return [type]           [description]
     */
    public function getmessage($code,$phone,$sign,$username){
        // 模板所需数据
        $json = ['code'=>$code];
        // 短信模板编号
        $code = Db::name('dx_template')->where('type',6)->value('code');
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

    public function sofn_generate_num($len='') {
        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $num = rand('10000','99999');//生成一个5位数的随机数
        $string=time()-$num;//时间戳减去随机数 增加一层变量
        for($len=$len;$len>=1;$len--){
            $position=rand()%strlen($chars);
            $position2=rand()%strlen($string);
            //随机添加一个chars里面的字符到时间戳随机位置上
            $string=substr_replace($string,substr($chars,$position,1),$position2,0);
        }
        return $string;
    }



    public function test(){

       
    } 

    public function postData($url, $data){        
        $ch = curl_init();        
        $timeout = 300;
        $data = http_build_query($data);
        curl_setopt ($ch, CURLOPT_HEADER, 0 );  
        $header = array ();  
        $header [] = 'Host:www.XXXX.co';  
        $header [] = 'Connection: keep-alive';  
        $header [] = 'User-Agent: ozilla/5.0 (X11; Linux i686) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1';  
        $header [] = 'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';  
        $header [] = 'Accept-Language: zh-CN,zh;q=0.8';  
        $header [] = 'Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3';  
        $header [] = 'Cache-Control:max-age=0';  
        $header [] = 'Cookie:t_skey=p5gdu1nrke856futitemkld661; t__CkCkey_=29f7d98';  
        $header [] = 'Content-Type:application/x-www-form-urlencoded';  
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header );           
        curl_setopt($ch, CURLOPT_URL, $url);   //请求地址      
        curl_setopt($ch, CURLOPT_POST, true);  //post请求     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);      //数据  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //当CURLOPT_RETURNTRANSFER设置为1时 $head 有请求的返回值      
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);    //设置请求超时时间    
        $handles = curl_exec($ch);        
        curl_close($ch);          
        return $handles;  
    }     
}