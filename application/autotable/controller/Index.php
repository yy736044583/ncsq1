<?php 
namespace app\autotable\controller;
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
		if($name){
    		$map['name'] = ['like',"%$name%"];
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
        
        if(empty($list)){
            $this->error('该部门暂无数据');
        }

        $this->assign('page', $page);
        $this->assign('list',$list);
        $this->assign('id',$id);
        $this->assign('name',$name);
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
		$list = Db::name('sys_showfileup')->where('showfileid',$id)->field('id,title')->order('sort desc')->paginate(10,true,['query'=>array('showfileid'=>$id)]);
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
		$list['url'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'public/'.$list['url'];
		$this->assign('list',$list);
		return $this->fetch();
	}
	//图片展示
	public function pictureshow(){
		$id = input('id');
		$list = Db::name('sys_showfileup')->field('id,title,url')->where('id',$id)->find();
		$list['url'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'public/'.$list['url'];
		$this->assign('list',$list);
		return $this->fetch();
	}
	//填单详情
	public function tableinfo(){
		$id = input('id');
		$url = Db::name('sys_showfileup')->where('id',$id)->value('nullurl');
		$tempid = input('tempid');
		if(!empty($url)){
			if(!$tempid){
				//填表临时文件
				$tempid = $this->excelcopy($url,$id);	
			}
			
			// 根据临时文件id 查询填表临时文件表中的 临时文件地址
			$resturl = Db::name('sys_table_temp')->where('id',$tempid)->value('tempurl');
		}else{
			$resturl = '';
		}
		$request = Request::instance();
		$domain = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public';
		$this->assign('resturl',$resturl);	
		$this->assign('domain',$domain);	
		$this->assign('tempid',$tempid);	
		$this->assign('id',$id);	
		return $this->fetch();	
	}

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
		//返回填表需要替换的单元格位置
		$rest = $this->readexcel($path1);
		if(!empty($rest)){
			//如果位置不为空则更新填表替换的位置
			Db::name('sys_showfileup')->where('id',$id)->update(['aotuwrite'=>$rest]);
		}
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
        $objReader->setReadDataOnly(true);
        $AllSheets = $objReader->load($path);
        $AllSheet = $AllSheets->getAllSheets();
        //实例化写入excel
       	$objSheet = \PHPExcel_IOFactory::load($path);      	
        $str = '';
        foreach ($AllSheet as $sheet) {
        	// 获取excel里的内容 转为数组
            $rest[$sheet->getTitle()] = $sheet->toArray();
            //循环数组 将每个单元格拿出来
            foreach ($rest[$sheet->getTitle()] as $k => $v) {
            	foreach ($v as $key => $val) {
            		//判断是否有@@符号,如果有获取坐标
            		if($val){
            			if(strstr($val,'@@')){
	            			$row = $k + 1; //横坐标
	            			$col = chr($key + 65); //纵坐标
	            			$zb = $col.$row;
	            			// 组合坐标变量和坐标 以;分隔
	            			$str .= str_replace('@@','',$val).','.$zb.';';
	            			// 将@@坐标的内容替换为空
	            			$objSheet->setActiveSheetIndex(0)->setCellValue($zb,'');
            			}	
            		}
        		}
        	}
        }
        //执行写入操作
        header('Cache-Control: max-age=0');
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
    	$data = input('post.');
    	$tempid = $data['tempid'];//填表临时文件id
    	$table_temp = Db::name('sys_table_temp')->where('id',$tempid)->find();
    	$tableid = $table_temp['tableid'];//模板表id

    	//将身份证照片存储
    	$idpath = createfile('idcard');
    	$url = uploadfile('idcardData_PhotoFileName','xls,xlsx',$idpath);
    	$data['idcardData_PhotoFileName'] = ROOT_PATH.'/public/idcard/'.$url;

    	//根据模板表id查询自动写入的坐标
    	$autowrite = Db::name('sys_showfileup')->where('id',$tableid)->value('autowrite');

    	unset($data['tempid']);
    	//查询身份证号是否存在 如果存在则更新
    	if($id = Db::name('sys_peopleinfo')->where('idcard_IDCardNo',$data['idcard_IDCardNo'])->value('id')){
    		Db::name('sys_peopleinfo')->where('idcard_IDCardNo',$data['idcard_IDCardNo'])->update($data);
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
        $AllSheets = $objReader->load($path);
        $AllSheet = $AllSheets->getSheet(0);//读取第一张表格数据
		$objPHPExcel->setActiveSheetIndex(0);
		$objActSheet = $objPHPExcel->getActiveSheet();


		/*写入图片*/
		$objDrawing = new \PHPExcel_Worksheet_Drawing();
		$objDrawing->setPath($data['idcardData_PhotoFileName']);//写入图片路径
		$objDrawing->setHeight(100);//写入图片宽度
		$objDrawing->setWidth(100);//写入图片高度
		$objDrawing->setCoordinates('A1');//设置图片所在表格位置
		$objDrawing->setWorksheet($AllSheets->getActiveSheet());//把图片写到当前的表格中

		
		//将自动填入坐标转换成数组
		$autowrite = explode(';',$autowrite);
		foreach ($autowrite as $k => $v) {
			$autowrite[$k] = explode(',',$v);
			foreach ($data as $key => $val){
				if(in_array($val,$v)){
					//插入文字
					$AllSheets->setActiveSheetIndex(0)->setCellValue($v[1],$val);
				}
			}
		}			
	
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($AllSheets,'excel2007');//创建写文件生成器
		$objWriter->save($path);//生成文件
		Db::name('sys_table_temp')->where('id',$tempid)->update(['type'=>1]);
    }


    //扫描身份证失败 更新状态
    public function typeup($tempid){
    	Db::name('sys_table_temp')->where('id',$tempid)->update(['type'=>'2']);
    }
}
