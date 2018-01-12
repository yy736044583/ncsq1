<?php
namespace app\integration\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
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
        // $card = '513721199309055055';
        //创建存储路径
        $path = $this->createfile('idcard',$card);
        //根据身份证号查询用户信息id和证件照地址
        $info= Db::name('sys_peopleinfo')->where('idcard_IDCardNo',$card)->field('id,idcardData_PhotoFileName')->find();
        
        if(!empty($data['file'])){
            $url = $this->base64up($path,$data['file'],$card);
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
        $arrayName = array('card' => $card, 'name' => $name, 'sex' => $sex,'userid'=>$userid);
        $this->assign('name',$arrayName);
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
        $photo = Db::name('sys_peopleinfo')->where('id',$userid)->value('idcardData_PhotoFileName');
        $url = ROOT_PATH.'public'.$photo;
        //将证件照转换成base64输出
        $photo1 = $this->base64EncodeImage($url);
        $this->assign('photo',$photo1);
        return  $this->fetch();
    }

    //上传人脸比对成功照片
    public function upfacepicture(){
        $userid = input('userid');
        $file = input('file');
        $photo = Db::name('sys_peopleinfo')->where('id',$userid)->field('id,idcard_IDCardNo,picture')->find();
        //拼接要存储的图片位置
        $path = ROOT_PATH.'public/uploads/idcard/'.$photo['idcard_IDCardNo'].'/';
        //将base64转成图片
        $url = $this->base64up($path,$file,$photo['idcard_IDCardNo']);
        //将比对照片上传数据库
        Db::name('sys_peopleinfo')->where('id',$userid)->update(['picture'=>$url]);
        // 如果之前有照片 进行删除
        if(!empty($photo['picture'])){
            //拼接要删除的图片地址
            $dlurl = ROOT_PATH.DS.'/public'.$photo['picture'];
            //删除图片地址
            if(file_exists($dlurl)){
                unlink($dlurl);
            }            
        }

        echo $url;
    }

    public function facedata(Request $request){
        $type = input('type');
        $userid = input('userid');
        $url2 = input('url2');
        $data['type'] = $type;
        $path = ROOT_PATH.'public/uploads/tempfile/';  
        // echo $url2;die;  
        $path1 = $request->domain().DS.dirname($_SERVER['SCRIPT_NAME']);
        $url1 =  $path1.DS.Db::name('sys_peopleinfo')->where('id',$userid)->value('idcardData_PhotoFileName');
        // echo $url1;return;
        $url2 = $path1.DS.$this->base64up($path,$url2,'');
        
        // if($type=='0'){
        //     $data['image_url_1'] = input('url1');
        //     $data['image_url_2'] = input('url2');
        // }else{
        //     $data['content_1'] = input('url1');
        //     $data['content_2'] = input('url2');
        // }
        $data['image_url_1'] = $url1;
        $data['image_url_2'] = $url2;
        $data = json_encode($data);
        $file = $this->faceapp($data);
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
        return  $this->fetch();
    }
    // 选择资料 
    public function file(){
        return  $this->fetch();
    }
    // 上传资料  
    public function fileup(){
        return  $this->fetch();
    }
    // 取件方式  
    public function pickup(){
        return  $this->fetch();
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
    public function base64up($path,$data,$card=''){

        // 创建文件名
        preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result);

        $img = base64_decode($data);

        $type = $result[2];
        // 存放路径
        $new_file = $path."/";
        $url = $new_file.time().".jpg";

        // 拼接存入数据库的路径
        $url1 = '/uploads/idcard/'.$card.'/'.time().".jpg";
     
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
    function createfile($file,$card){
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
        $path2 =  $path.DS.$card; // 接收文件目录
        if (!file_exists($path2)) {
            if(!mkdir($path2)){
                echo '提交失败,自动创建文件夹失败';
            }
        }
        return $path2;
    }  

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

 


    /**
     * [takemessage 取号成功短信提醒]
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



    public function test(){
        $path = 'D:\phpStudy\WWW\ncsq\public\uploads\idcard\513721199309055055';

        $this->base64up($path,$data);
    }  
}