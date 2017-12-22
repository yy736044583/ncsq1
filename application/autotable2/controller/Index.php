<?php 
namespace app\autotable2\controller;
use think\Db;
use think\View;
use think\Loader;
use first\PHPExcel;
use think\Request;

/**
* 自主填单系统
*/
class Index extends \think\Controller{
	
	//进入系统页面
	public function insys(){
		$time = time();
		$this->assign('time',$time);
		return $this->fetch();
	}

	//首页
	public function indexfrom(){
		$time = time();
		$this->assign('time',$time);
		return $this->fetch();
	}
	//部门首页 ifrom
	//查询所有部门
	public function index(){
		$list = Db::name('sys_section')->paginate(12,true);
        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list',$list);
		return $this->fetch();
	}


	//办事指南列表
	//根据部门id查询事项
	public function table_matter(){
		$id = input('id');//部门id
		$name = input('name');
		$map = array();
		$search = '';
		if($name){
    		$map['name'] = ['like',"%$name%"];
    		$search = '1';
    	}
    	if($id){
    		$map['sectionid'] = $id;
    	}
        //根据Id查询办事指南
        $list = Db::name('sys_matter')->field('name,id')->where($map)->paginate(15,true,['query'=>array('id'=>$id,'name'=>$name)]);
        $page = $list->render();
        $list = $list->all();
        $today = date('Ymd',time());
        //如果在事项表中未查询到相关数据 则根据排号编号查询事项id  再根据事项id查询
        if(empty($list)){
            $name = strtoupper($name);
            $matterid = Db::name('ph_queue')->where('flownum',$name)->where('today',$today)->value('matterid');
            if($matterid){
                $list = Db::name('sys_matter')->where('id',$matterid)->field('name,id')->select();
            }
        }
        

        $this->assign('page', $page);
        $this->assign('list',$list);
        $this->assign('id',$id);
        $this->assign('name',$name);
        $this->assign('search',$search);
		return $this->fetch();
	}
  
	//事项目录
	public function table_showfile(){
		$matterid = input('matterid');
		$secid = input('secid');
		//根据事项id查询所有的文件标题
    	$list = Db::name('sys_showfile')->where('matterid',$matterid)->field('id,title')->paginate(10,true,['query'=>array('matterid'=>$matterid)]);
    	$page = $list->render();
        $this->assign('page', $page);
        $this->assign('secid', $secid);
        $this->assign('matterid', $matterid);
    	$this->assign('list',$list);
    	return $this->fetch();
	}

	//填表文件
	public function table_showfileup(){
		$id = input('showfileid');
		$matterid = input('matterid');
		$secid = input('secid');
		$list = Db::name('sys_showfileup')->where('showfileid',$id)->field('id,title,aotuwrite')->order('sort desc')->paginate(10,true,['query'=>array('showfileid'=>$id)]);
		$page = $list->render();
        $this->assign('page', $page);
        $this->assign('matterid', $matterid);
        $this->assign('secid', $secid);
    	$this->assign('list',$list);
		return $this->fetch();
	}
	
	//填单页面
	public function nulltable(){
		$id = input('id');
		$tempid = input('tempid');
		$list = Db::name('sys_showfileup')->where('id',$id)->find();
		$urlsuff = get_extension($list['url']);
		$this->assign('list',$list);
		$this->assign('urlsuff',$urlsuff);
		$this->assign('tempid',$tempid);	
		return $this->fetch();
	}
	//pdf展示
	public function pdfshow(Request $request){
		$id = input('id');
		$list = Db::name('sys_showfileup')->field('id,title,url')->where('id',$id)->find();
		$list['url'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public'.$list['url'];
		$this->assign('list',$list);
		return $this->fetch();
	}
	//图片展示
	public function pictureshow(Request $request){
		$id = input('id');
		$list = Db::name('sys_showfileup')->field('id,title,url')->where('id',$id)->find();
		$list['url'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public'.$list['url'];
		$this->assign('list',$list);
		return $this->fetch();
	}
	//填单详情
	public function tableinfo(Request $request){
		$id = input('id');
		$tempid = input('tempid');
		$fileup = Db::name('sys_showfileup')->where('id',$id)->find();
		$fileup['nullurl'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public'.$fileup['nullurl'];	
		$this->assign('tempid',$tempid);	
		$this->assign('fileup',$fileup);	
		$this->assign('id',$id);	
		return $this->fetch();	
	}
/*
	//上传excel
	public function upexcel(){
		$tempid = input('tempid');
		if(!empty($_FILES['ExcelFile']['tmp_name'])){
			$path = createfile('autotable');
			$url = uploadfile('ExcelFile','xls,xlsx',$path);
			$url = 'uploads/'.$url;
		}
	}

	//临时文件再次保存
	public function save_excel(){
		$tempid = input('tempid');
		$path1 = Db::name('sys_table_temp')->where('id',$tempid)->value('tempurl');
		$path = ROOT_PATH.DS.'public/'.$this->suburl($path1);
		if(!empty($_FILES['ExcelFile']['tmp_name'])){
			//将文件保存到临时文件
			copy ($_FILES['ExcelFile']['tmp_name'],$path )  or die ("Could not copy file"); 
			echo 1;
		}
	}	
*/
	//前端ajax请求 查询是否有身份证信息需要读写
	//生成临时文件 并传回临时文件存储的表id
	public function ajaxview(){
		$id = input('id');

		$fileup = Db::name('sys_showfileup')->where('id',$id)->field('id,nullurl,aotuwrite')->find();
		if(!empty($fileup['nullurl'])){
			//填表临时文件
			$tempid = $this->excelcopy($fileup['nullurl'],$id);			
		}else{
			$tempid = '0';
		}

		// type  1表示需要刷身份证  0表示不刷身份证
		$data['type'] = empty($fileup['aotuwrite'])?'0':'1';
		$data['tempid'] = $tempid;
		echo json_encode($data);return;	
	}


	//上传用户填写内容
	public function loadinfo(){
		$arr = input('post.');
		$data = $arr['data'];

		//根据临时模板id查询临时模板路径
		$tempurl = Db::name('sys_table_temp')->where('id',$arr['tempid'])->value('tempurl');
		//删除数组中的临时文件id

		$name = $this->suburl($tempurl);
		// 临时文件地址生产绝对路径
		$path = ROOT_PATH.'/public/'.$name;
		//实例化phpexcel
    	Loader::import('first.PHPExcel');
    	$excel = new \PHPExcel();

        //实例化写入excel
       	$objSheet = \PHPExcel_IOFactory::load($path); 

       	// 将传过来的数据遍历到excel中
       	foreach ($data as $key => $val) {
    		$objSheet->setActiveSheetIndex(0)->setCellValue($val['name'],$val['val']);
        }

         //执行写入操作
        header('Cache-Control: max-age=0');
        $objSheet->setActiveSheetIndex(0);
       	$objWriter = \PHPExcel_IOFactory::createWriter($objSheet, 'excel2007');
       	$objWriter->save($path);

       	$tempurl = Db::name('sys_table_temp')->where('id',$arr['tempid'])->update(['type'=>'2']);
       	echo 1;
       	
       	// dump($str);die;
       	//将临时文件存储到正式文件中去
       	// $path1 = createfile('autotable');
       	// $path1 = $path1.DS.'public'.DS.$name;
       	// copy ($path,$path1)  or die ("Could not copy file"); 

	}

	// //再次上传用户填写内容页面
	// public function loadinfoagin(){
	// 	$data = input('post.');
	// }

	//将excel模板拷贝成临时文件 返回路径 
	public function excelcopy($excelurl,$id){

		$request = Request::instance();
		//截取路径 取uploads后的路径
		$excelurl = $this->suburl($excelurl);
		//填表地址
		$path = ROOT_PATH.DS.'public/'.$excelurl;
		//填表临时文件地址
		$path1 = $this->tempfile($path);

		//将绝对路径替换成服务器路径
		$path2 = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public'.$this->suburl($path1);
		$path2 = str_replace("\\","/",$path2);//去掉反斜杠

		copy($path,$path1);
		//将模板id和临时文件地址存入 模板临时文件表中
		$tempid = Db::name('sys_table_temp')->insertGetId(['tableid'=>$id,'tempurl'=>$path2]);

		//在上传时替换
		//返回填表需要替换的单元格位置
		// $rest = $this->readexcel($path1);
		// if(!empty($rest)){
		// 	//如果位置不为空则更新填表替换的位置
		// 	Db::name('sys_showfileup')->where('id',$id)->update(['aotuwrite'=>$rest]);
		// }
		return $tempid;
	}

	/**
	 * [suburl 截取服务器地址 只保留uploads后的地址]
	 * @param  [type] $url [服务器ip地址]
	 * @return [type]      [uploads后面的地址]
	 */
	public function suburl($url){
		$len = strpos($url,'/uploads');
		if(!$len){
			$len = strpos($url,'\uploads');
		}
		$url = substr($url,$len);
		return $url;
	}

	//填表临时文件地址
	public function tempfile($path){
		//临时文件目录
		$path1 = createfile('tempfile');
		//临时文件名
		$name = time().rand(100,999);
		//文件名后缀
		$suff = get_extension($path);
		$path2 = $path1.DS.$name.'.'.$suff;
		return $path2;
	}

	//获取变量坐标 替换变量为空
	public function readexcel($path){
		//实例化phpexcel
    	Loader::import('first.PHPExcel');
    	$excel = new \PHPExcel();
        $rest = [];
        // 读取excel
        $objReader = \PHPExcel_IOFactory::createReader('excel2007');
        if(!$objReader->canRead($path)) {
			$objReader = \PHPExcel_IOFactory::createReader('Excel5');
		if(!$objReader->canRead($path)) 
			return;
		}
        $objReader->setReadDataOnly(true);
        $AllSheets = $objReader->load($path);
        $AllSheet = $AllSheets->getAllSheets();
        //实例化写入excel
       	$objSheet = \PHPExcel_IOFactory::load($path);   

        $str = '';
        $str1 = '';
        foreach ($AllSheet as $sheet) {
        	// 获取excel里的内容 转为数组
            $rest[$sheet->getTitle()] = $sheet->toArray();
           
            //循环数组 将每个单元格拿出来
            foreach ($rest[$sheet->getTitle()] as $k => $v) {
            	foreach ($v as $key => $val) {
            		//判断是否有#@符号,如果有获取坐标
            		if($val){
            			$row = $k + 1; //横坐标
            			$col = chr($key + 65); //纵坐标
            			$zb = $col.$row;
            			if(strstr($val,'#@')){
	            			// 组合坐标变量和坐标 以;分隔
	            			$str .= str_replace('#@','',$val).','.$zb.';';
	            			// 将#@坐标的内容替换为空
	            			$objSheet->setActiveSheetIndex(0)->setCellValue($zb,'');
            			}
            			if(strstr($val,'##')){
            				$str1 .= str_replace('##','',$val).',zb='.$zb.';';
            				// 将##坐标的内容替换为空
	            			$objSheet->setActiveSheetIndex(0)->setCellValue($zb,'');
            			}	
            		}
        		}
        	}
        }
        //执行写入操作
        header('Cache-Control: max-age=0');
        // header("Content-type:application/vnd.ms-excel");
        $objSheet->setActiveSheetIndex(0);
       	$objWriter = \PHPExcel_IOFactory::createWriter($objSheet, 'excel2007');
       	$objWriter->save($path);
       	//将要替换的单元格位置末尾符号去掉  返回
    	$str = trim($str,';');
        return $str;
    }




    // excel存入图片
    public function excelimg(){
    	// 发送的身份证信息
    	$data = input('get.');
    	
    	$tempid = $data['tempid'];//填表临时文件id
    	$table_temp = Db::name('sys_table_temp')->where('id',$tempid)->find();

    	$tableid = $table_temp['tableid'];//模板表id

    	if($_FILES['file']['tmp_name']){
	    	// //将身份证照片存储
	    	$idpath = createfile('idcard');

	    	$url = uploadfile('file','jpg,jpeg,png',$idpath);
	    	
	    	$data['idcardData_PhotoFileName'] = ROOT_PATH.'/public/uploads/idcard/'.$url;    		
    	}


    	//根据模板表id查询自动写入的坐标
    	$autowrite = Db::name('sys_showfileup')->where('id',$tableid)->value('aotuwrite');

    	unset($data['tempid']);
    	unset($data['tableid']);
    	unset($data['rand']);

    	//查询身份证号是否存在 如果存在则更新
    	if($id = Db::name('sys_peopleinfo')->where('idcard_IDCardNo',$data['idcard_IDCardNo'])->value('id')){

    		Db::name('sys_peopleinfo')->where('id',$id)->update($data);
    	}else{
    		//如果身份证号不存在 则添加 再将用户信息id更新到临时表中
    		$peopleid = Db::name('sys_peopleinfo')->insertGetId($data);
    		Db::name('sys_table_temp')->where('id',$tempid)->update(['peopleid'=>$peopleid]);
    	}

    	//查询临时填表地址 并转换成绝对路径
    	$path = $table_temp['tempurl'];
    	$path = $this->suburl($path);
    	$path = ROOT_PATH.DS.'public/'.$path;



    	//引用excel
    	Loader::import('first.PHPExcel');
    	$objPHPExcel = new \PHPExcel();
    	//读取excel
    	$objReader = \PHPExcel_IOFactory::createReader('excel2007');
    	if(!$objReader->canRead($path)) {
			$objReader = \PHPExcel_IOFactory::createReader('Excel5');
		if(!$objReader->canRead($path)) 
			return;
		}
        $AllSheets = $objReader->load($path);
        $AllSheet = $AllSheets->getSheet(0);//读取第一张表格数据
		$objPHPExcel->setActiveSheetIndex(0);
		$objActSheet = $objPHPExcel->getActiveSheet();

		//将自动填入坐标转换成数组
		$autowrite = explode(';',$autowrite);

		// 启动事务
		Db::startTrans();
		try{
			foreach ($autowrite as $k => $v) {
				$v = explode(',',$v);
				foreach ($data as $key => $val){
					if(in_array($key,$v)){
						if($key=='idcardData_PhotoFileName'&&!empty($data['idcardData_PhotoFileName'])){
							//插入图片
							$objDrawing = new \PHPExcel_Worksheet_Drawing();
							$objDrawing->setPath($data['idcardData_PhotoFileName']);//写入图片路径
							$objDrawing->setHeight(100);//写入图片宽度
							$objDrawing->setWidth(100);//写入图片高度
							$objDrawing->setCoordinates($v[1]);//设置图片所在表格位置
							$objDrawing->setWorksheet($AllSheets->getActiveSheet());//把图片写到当前的表格中
						}else{
							//插入文字
							$AllSheets->setActiveSheetIndex(0)->setCellValue($v[1],$val);	
						}
					}
				}
			}		    // 提交事务
		
	        header('Cache-Control: max-age=0');
	        $objWriter = \PHPExcel_IOFactory::createWriter($AllSheets,'excel2007');//创建写文件生成器

			$objWriter->save($path);//生成文件

			Db::name('sys_table_temp')->where('id',$tempid)->update(['type'=>1]);
			Db::commit();
			// echo '1';  
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    // echo 2;
		}
    }


    //查询
    public function typeshow($tempid){
    	$data = Db::name('sys_table_temp')->where('id',$tempid)->field('type,tempurl')->find();
    	echo json_encode($data);return;
    }

    public function test(){
    	// $data = ['data'=>['G15'=>'XXX公司1','G14'=>'双流东升1','G16'=>'13625658745'],'tempid'=>382];
    	// $url = '192.168.0.10:8076/sbxt/index.php/autotable2/index/loadinfo';
    	// $http = $this->postData($url,$data);
    	// dump($http);
    	return $this->fetch();

    }

    public function postData($url, $data){

	    $ch = curl_init();        
	    $timeout = 300;
	    // $data = http_build_query($data);          
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

