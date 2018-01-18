<?php
namespace app\admin\controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
use think\Validate;
use think\Request; 
use think\Loader;
use first\PHPExcel; 
/*
**样表管理 
 */
class Fromtable extends Common{

	//事项列表
	public function index(){
		$this->auth();
		//判断是否有部门id查询
		$sectionid = input('sectionid');
		$name = input('name');
		$map = array();
		if(!empty($sectionid)){
			$map['deptid'] = $sectionid;
		}
		if($name){
			$map['tname'] = ['like',"%$name%"];
		}
		$data = Db::name('gra_matter')->where($map)
			->paginate(12,false,['query'=>array('sectionid'=>$sectionid)]);
		//显示全部部门列表	
		$sec = Db::name('gra_section')->field('id,tname,tid')->where('valid',1)->select();
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['section'] = Db::name('gra_section')->where('tid',$v['deptid'])->value('tname');
		}
		$page = $data->render();

		$this->assign('sec',$sec);
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('sectionid',$sectionid);
		$this->assign('name',$name);
		return $this->fetch();
	}

	//添加事项
	public function addfrom(){
		if(request()->isPost()){
			$data = input('post.');
			$data['department'] = Db::name('gra_section')->where('tid',$data['deptid'])->value('tname');
			$data['mattertype'] = 1;
			if(Db::name('gra_matter')->insert($data)){
				$this->success('添加成功','fromtable/index');	
			}else{
				$this->error('添加失败,请重试');
			}
		}
		//显示全部部门列表	
		$sec = Db::name('gra_section')->field('id,tname,tid')->where('valid',1)->select();
		$this->assign('sec',$sec);
		return $this->fetch();
	}

	/**
	 * 更新数据
	 */
	public function upfrom(){
		if(request()->isPost()){
			$data = input('post.');
			$mid = $data['id'];
			unset($data['id']);
				
			if(Db::name('gra_matter')->where('id',$mid)->update($data)){
				$this->success('修改成功','fromtable/index');	
			}else{
				$this->error('修改失败,请重试');
			}
		}
		$id = input('id');
		$list = DB::name('gra_matter')->where('id',$id)->find();
		//显示全部部门列表	
		$sec = Db::name('gra_section')->field('id,tname,tid')->where('valid',1)->select();
		$this->assign('sec',$sec);
		$this->assign('list',$list);
		return $this->fetch();
	}

	/**
	 * 删除事项
	 */
	public function dlfrom(){
		$id = input('id');
		$list = Db::name('sys_showfile')->where('matterid',$id)->find();
		if($list){
			return '该事项下有文件无法直接删除';
		}
		if(Db::name('gra_matter')->where('id',$id)->delete()){
			echo '删除成功';
		}else{
			echo '删除失败';
		}
	}


	/*-------------------------------------------------------------------------------------------------------*/

	//文件管理
	public function filelist(){
		//事项id
		$mid = input('mid');
		// $m_name = session('m_name');

		//查询事项名
		$m_name = Db::name('gra_matter')->where('id',$mid)->value('tname');
		session('m_name',$m_name);			



		//根据事项id查询对应的文件标题
		$list = Db::name('sys_showfile')->field('id,summary,title')->where('matterid',$mid)->select();
		//dump($list);die;
		$this->assign('list',$list);
		$this->assign('m_name',$m_name);
		$this->assign('mid',$mid);
		return $this->fetch();
	}

	//添加文件标题
	public function addfile(){
		$mid = input('mid');
		if(request()->isPost()){
			$data = input('post.');
			if(Db::name('sys_showfile')->insert($data)){
				$this->success('添加成功','fromtable/filelist?mid='.$data['matterid']);	
			}else{
				$this->error('添加失败,请重试','fromtable/filelist?mid='.$data['matterid']);
			}
		}
		//查询事项名
		//$m_name = Db::name('sys_matter')->where('id',$mid)->value('name');
		$m_name = session('m_name');
		$this->assign('m_name',$m_name);
		$this->assign('mid',$mid);
		return $this->fetch();
	}

	//查看文件
	public function showfile(){
		$fid = input('fid');
		$mid = input('mid');
		$m_name = session('m_name');
		$f_name =  Db::name('sys_showfile')->where('id',$fid)->value('title');
		$list = Db::name('sys_showfileup')->where('showfileid',$fid)->order('sort desc')->select();
		foreach ($list as $k => $v) {
			$list[$k]['filetitle'] = Db::name('sys_showfile')->where('id',$v['showfileid'])->value('title');
		}
		$this->assign('list',$list);
		$this->assign('mid',$mid);
		$this->assign('fid',$fid);
		$this->assign('m_name',$m_name);
		$this->assign('f_name',$f_name);
		return $this->fetch();
	}

	//升序
	public function orderasc(){
		$id = input('id');
		$type = input('type');
		if($type==1){
			Db::name('sys_showfileup')->where('id',$id)->setInc('sort');
		}else{
			Db::name('sys_showfileup')->where('id',$id)->setDec('sort');
		}
		
	}


	/**
	 * 删除事项
	 */
	public function dlfiles(){
		$id = input('id');
		$list = Db::name('sys_showfileup')->where('showfileid',$id)->find();
		if($list){
			return '该事项下有文件无法直接删除';
		}
		if(Db::name('sys_showfile')->where('id',$id)->delete()){
			echo '删除成功';
		}else{
			echo '删除失败';
		}
	}

	//上传文件页面
	public function upfile(){
		$fid = input('fid');
		$mid = input('mid');
		$m_name = session('m_name');
		if(request()->isPost()){
			$data = input('post.');
			// dump($data);
		}
		//查询文件名
		$f_name = Db::name('sys_showfile')->where('id',$fid)->value('title');		

		$this->assign('m_name',$m_name);
		$this->assign('f_name',$f_name);
		$this->assign('fid',$fid);
		$this->assign('mid',$mid);
		return $this->fetch();
	}

	// 编辑上传文件页面
	public function savefile(){
		$id = input('id');
		$fid = input('fid');
		$mid = input('mid');
		$m_name = session('m_name');
		$f_name =  Db::name('sys_showfile')->where('id',$fid)->value('title');
		$list = Db::name('sys_showfileup')->where('id',$id)->find();
		$this->assign('fid',$fid);
		$this->assign('mid',$mid);
		$this->assign('list',$list);
		$this->assign('m_name',$m_name);
		$this->assign('f_name',$f_name);
		return $this->fetch();
	}

	// 上传样表文件
	public function upfromload(Request $request){
		// 建立上传路径 uploads下面的datum文件夹 
		// 调用common的文件上传方法
		$path = createfile('matter');
		// 上传文件类型限制
		$type = 'pdf,jpg,jpeg,png';
		// 获取上传文件路径 
		// 调用common的文件上传方法
		$url = uploadfile('file',$type,$path);
		if($url==0){
			return 0;
		}
		// $today = date('Ymd',time());
		$url = '/uploads/matter/'.$url;
		return $url;
	}

		// 上传填表文件
	public function upfromload1(Request $request){
		// 建立上传路径 uploads下面的datum文件夹 
		// 调用common的文件上传方法
		$path = createfile('matter');
		// 上传文件类型限制
		$type = 'xls,xlsx';
		// 获取上传文件路径 
		// 调用common的文件上传方法
		$url = uploadfile('file',$type,$path);
		if($url==0){
			return 0;
		}
		$url = '/uploads/matter/'.$url;
		return $url;
	}

	//多图文件上传
	public function upload(Request $request){
        //文件列表id
    	$fid = input('fid');
    	$mid = input('mid');
    	$data['url'] = input('url');
    	$data['nullurl'] = input('nullurl');
    	$data['title'] = input('title');
    	$data['showfileid'] = input('fid');
 
    	//查看文件类型
    	$suff = get_extension($data['url']);
    	$data['type'] = $suff;
    	//如果是图片才转换缩略图
    	if($suff=='jpg'||$suff=='jpeg'||$suff=='png'){
	    	$data['thumburl'] = $this->savethumbimg($data['url']);		
    	}

 		if(Db::name('sys_showfileup')->insert($data)){
 			$id = Db::name('sys_showfileup')->getLastInsID();

	    	if(!empty($data['nullurl'])){
	    		$path =  ROOT_PATH.'/public/'.$data['nullurl'];
	    		//返回填表需要替换的单元格位置
				$rest = $this->readexcel($path);
				if(!empty($rest)){
					//如果位置不为空则更新填表替换的位置
					Db::name('sys_showfileup')->where('id',$id)->update($rest);
				}
	    	}
    		$this->success('上传成功','fromtable/showfile?mid='.$mid.'&fid='.$fid);
        }else{
        	$this->error('上传失败','fromtable/showfile?mid='.$mid.'&fid='.$fid);
        }    
	}

	public function saveload(Request $request){
        //文件列表id
    	$fid = input('fid');
    	$mid = input('mid');
    	$id = input('id'); //文件id
    	$data['url'] = input('url');
    	$data['nullurl'] = input('nullurl');
    	$data['title'] = input('title');
    	$data['showfileid'] = input('fid');
    	$path = ROOT_PATH.'/public/';
    	$list = Db::name('sys_showfileup')->where('id',$id)->find();

    	// 如果上传的样表文件有更改再重新生产缩略图
    	if($list['url']!=$data['url']){
    		//查看文件类型
    		$suff = get_extension($data['url']);
    		$data['type'] = $suff;
    		//如果是图片才转换缩略图
	    	if($suff=='jpg'||$suff=='jpeg'||$suff=='png'){
		    	$data['thumburl'] = $this->savethumbimg($data['url']);		
	    	}
	    	if($list['url']){
		    	// 删除之前的样表文件
		    	if(file_exists($path.$list['url'])){
		    		unlink($path.$list['url']);
		    	}	    		
	    	}
	    	if($list['thumburl']){
		    	// 删除之前的缩略图文件
		    	if(file_exists($path.$list['thumburl'])){
		    		unlink($path.$list['thumburl']);
		    	}	    		
	    	}
    	}
    	//如果有上传填表文件 获取单元格位置并清空
    	if(!empty($data['nullurl'])){
    		$path =  ROOT_PATH.'/public/'.$data['nullurl'];
    		//返回填表需要替换的单元格位置
			$rest = $this->readexcel($path);
			
			if(!empty($rest)){
				//如果位置不为空则更新填表替换的位置
				Db::name('sys_showfileup')->where('id',$id)->update($rest);
			}
	    }
    	if($data['nullurl']!=$list['nullurl']&&$list['nullurl']){
    		// 删除之前的缩略图文件
	    	if(file_exists($path.$list['nullurl'])){
	    		unlink($path.$list['nullurl']);
	    	}
    	}
		// dump($data);die;
    	if(Db::name('sys_showfileup')->where('id',$id)->update($data)){
    		$this->success('修改成功','fromtable/showfile?mid='.$mid.'&fid='.$fid);
        }else{
        	$this->error('修改失败','fromtable/showfile?mid='.$mid.'&fid='.$fid);
        }
	}

	/**
	 * [savethumbimg 如果是图片文件则生产缩略图]
	 * @param  [type] $url1 [description]
	 * @return [type]       [description]
	 */
	public function savethumbimg($url1){
    	//缩略图文件地址
	 	$filepath = '/uploads/matter/';
	 	// 截取路径获取文件名称
	 	$url = explode('/',$url1);
	 	$count = count($url);

	 	$count1 = $count-2;
	 	$count2 = $count-1;
	 	$name = $url["$count1"].'/'.$url["$count2"];
	 	
	 	$path = ROOT_PATH . 'public' . DS . 'uploads'.DS.'matter';
	 	// 调用生成缩略图方法
 		$pathname = $this->imagethumb($path,$name);
 		$thumburl = $filepath.$pathname;  
 		return $thumburl;
	}

	//删除文件
	public function dlfile(Request $request){
		$id = input('id');
		$file = Db::name('sys_showfileup')->where('id',$id)->find();
		$domain = $request->domain().dirname($_SERVER['SCRIPT_NAME']);
		$path = ROOT_PATH.'/public/';
		
		if(Db::name('sys_showfileup')->where('id',$id)->delete()){
			if(!empty($file['url'])){
				$url = $path.$file['url'];
				if(file_exists($url)){
					unlink($url);
				}
			}
			if(!empty($file['nullurl'])){
				$nullurl = $path.$file['nullurl'];
				if(file_exists($nullurl)){
					unlink($nullurl);
				}
			}
			if(!empty($file['thumburl'])){
				$thumburl = $path.$file['thumburl'];
				if(file_exists($thumburl)){
					unlink($thumburl);
				}
			}			
			echo '删除成功';
		}else{
			echo '删除失败';
		}
	}

	//显示编辑内容
	public function showtitle(){
		$id = input('id');
		$list = Db::name('sys_showfile')->where('id',$id)->find();
		return $list;
	}

	//提交编辑内容
	public function upfilename(){
		$data = input('post.');
		$id = $data['id'];
		unset($data['id']);
		if(Db::name('sys_showfile')->where('id',$id)->update($data)){
			echo '提交成功';
		}else{
			echo '提交失败,请重试';
		}
	}

	/**
	 * [imagethumb 图片文件生产缩略图]
	 * @param  [string] $path [路径]
	 * @param  [string] $name [文件名]
	 * @return [string]  [缩略图文件名]
	 */
	public function imagethumb($path,$name){
		// 拼接要打开文件地址
		$path1 = $path.'/'.$name;
		// echo $path1;die;
		$image = \think\Image::open($path1);

		// 取文件名不包括后缀
		$name = explode('.', $name);
		$type = $name['1'];
		if($type=='pdf'){
			return false;
		}
		$name = $name[0].'thumb.png';
		// 拼接要生成的文件名全路径
		$paththumb = $path.DS.$name;
		// 如果生成缩略图成功则返回文件名
		if($image->thumb(480, 400)->save($paththumb)){
			return $name;
		}
	}

	// 修改别名
	public function uptitle(){
		$id = intval(input('id'));
		$title = input('title');
		$aotuwrite = input('aotuwrite');
		if($title){
			$data['title'] = $title;
		}
		if($aotuwrite){
			$data['aotuwrite'] = $aotuwrite;
		}
		if(empty($data)){
			echo '数据错误';
			return;
		}
		if(Db::name('sys_showfileup')->where('id',$id)->update($data)){
			echo '修改成功';
		}else{
			echo '修改失败';
		}

	}
/*	
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
        foreach ($AllSheet as $sheet) {
        	// 获取excel里的内容 转为数组
            $rest[$sheet->getTitle()] = $sheet->toArray();
           
            //循环数组 将每个单元格拿出来
            foreach ($rest[$sheet->getTitle()] as $k => $v) {
            	foreach ($v as $key => $val) {
            		//判断是否有@@符号,如果有获取坐标
            		if($val){
            			if(strstr($val,'#@')){
	            			$row = $k + 1; //横坐标
	            			$col = chr($key + 65); //纵坐标
	            			$zb = $col.$row;
	            			// 组合坐标变量和坐标 以;分隔
	            			$str .= str_replace('#@','',$val).','.$zb.';';
	            			// 将@@坐标的内容替换为空
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
    	$aotuwrite = trim($str,';');

        return $aotuwrite;
    }
  */ 
 	//获取变量坐标 替换变量为空 上传表单模板
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
        $html = '<form class="layui-form" action="" method="post">';
        foreach ($AllSheet as $sheet) {
        	// 获取excel里的内容 转为数组
            $rest[$sheet->getTitle()] = $sheet->toArray();
           
            //循环数组 将每个单元格拿出来
            foreach ($rest[$sheet->getTitle()] as $k => $v) {
            	foreach ($v as $key => $val) {
            		//判断是否有@@符号,如果有获取坐标
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
            				$str1 .= str_replace('##','',$val).',name='.$zb.';';
            				
            				// 将##坐标的内容替换为空
	            			$objSheet->setActiveSheetIndex(0)->setCellValue($zb,'');
	            			$tempstr1 = str_replace('##','',$val).',name='.$zb.';';
	            			//将该文本的内容转成数组
	            			$tempstr = explode(',',$tempstr1);
	            			
	            			// 将数组的 title=标题 转换成数组形式 ["value"] =>  "姓名"
	            			foreach ($tempstr as $kk => $vv) {
	            				$title = strstr($vv,'=',true);//截取=前面的字符
	            				$value = strstr($vv,'=');//截取=后面的字符
	            				$value = ltrim($value,'=');//去掉=
	            				$data1[$title] = $value;//组成一个一维数组
	            			}

	            			//如果某个值没有定义则赋值为空
	            			$data1['value'] = empty($data1['value'])?'':$data1['value']; //标题
	            			$data1['name'] = empty($data1['name'])?'':rtrim($data1['name'],';');//坐标
	            			$data1['len'] = empty($data1['len'])?'':$data1['len']; //长度
	            			$data1['check'] = empty($data1['check'])?'':$data1['check']; //验证方式
	            			// dump($data);
	            			$html .= '<div class="layui-form-item inputrow">
										<label class="layui-form-label">'.$data1['value'].'</label>
										<div class="layui-input-inline">';
							if($data1['check']=='date'){
								$date = date('Y年m月d日',time());
								$date1 = date('Y-m-d',time());
								$html .=	'<input type="text" name="'.$data1['name'].'" class="layui-input datepicker" autofocuss value="'.$date.'" length="'.$data1['len'].'" data-name="'.$data1['check'].'" placeholder="请输入'.$data1['value'].'" data-valuee="'.$date1.'">';
							}else{
								$html .=	'<input type="text" name="'.$data1['name'].'" class="layui-input" value="" length="'.$data1['len'].'" data-name="'.$data1['check'].'" placeholder="请输入'.$data1['value'].'">';
							}			

							
							$html .=	'</div></div>';
            			}	
            		}
        		}
        	}
        }
        $html .= '</form>';

        //执行写入操作
        header('Cache-Control: max-age=0');
        // header("Content-type:application/vnd.ms-excel");
        $objSheet->setActiveSheetIndex(0);
       	$objWriter = \PHPExcel_IOFactory::createWriter($objSheet, 'excel2007');
       	$objWriter->save($path);
       	//将要替换的单元格位置末尾符号去掉  返回
    	$data['aotuwrite'] = trim($str,';');
    	$data['aotuwrite1'] = trim($str1,';');
    	$data['html'] = $html;
        return $data;
    }
  


    // public function test(){
	   // 	$path = 'D:\phpStudy\WWW\sbxt\public\uploads\tempfile\123.xls';
	   // 	$this->readexcel($path);
    // }


}