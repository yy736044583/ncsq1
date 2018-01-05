<?php
namespace app\admin\controller;
use think\Controller;
use think\View;
use think\Db;
use app\admin\Controller\Common;
use think\Loader;
//评价管理  报表查询


class Evaluate extends Common{

	//评价浏览
	public function index(){
		$this->auth();
		//如果有条件则进行查询
		$name = input('name');
		$start = input('start');
		$end = input('end');
		$evaluatelevel = input('evaluatelevel');
		$deviceid = input('deviceid');
		if($name!=''|$start!=''|$end!=''|$evaluatelevel!=''|$deviceid!=''){
			$map = '';
			if($name!=''){//根据姓名查询员工id
				$workmanid = Db::name('sys_workman')->where('name','like',"%$name%")->column('id');
				$map['workmanid'] = array('in',$workmanid);
			}
			//判断时间
			if($start!=''&$end==''){
				$map['evaluatetime'] = array('gt',$start);
			}elseif($start==''&$end!=''){
				$map['evaluatetime'] = array('lt',$end);
			}elseif($start!=''&$end!=''){
				$map['evaluatetime'] = array('between time',array($start,$end));
			}
			//评价
			if($evaluatelevel!=''){
				$map['evaluatelevel'] = $evaluatelevel;
			}
			//设备
			if($deviceid!=''){
				$map['deviceid'] = $deviceid;
			}
			$map['evaluatestatus'] = '1';
			$data = Db::name('pj_evaluate')->where($map)->order('id desc')->paginate(12,false,['query'=>array('name'=>$name,'start'=>$start,'end'=>$end,'evaluatelevel'=>$evaluatelevel,'deviceid'=>$deviceid)]);
		}else{
			$data = Db::name('pj_evaluate')->where("evaluatestatus='1'")->order('id desc')->paginate(12);
		}
		$list = $data->all();
		foreach ($list as $k => $v) {
			$workman = Db::name('sys_workman')->where('id',$v['workmanid'])->field('name,sectionid')->find();
			//员工姓名
			$list[$k]['workmanid']	 = $workman['name'];
			$list[$k]['section']	 = Db::name('gra_section')->where('id',$workman['sectionid'])->value('tname');
			$list[$k]['deviceid']	 = Db::name('pj_device')->where('id',$v['deviceid'])->value('number');
			//评价
			switch ($v['evaluatelevel']) {
				case '0':$list[$k]['evaluatelevel'] = '态度不好';break;
				case '1':$list[$k]['evaluatelevel'] = '业务不熟';break;
				case '2':$list[$k]['evaluatelevel'] = '时间太长';break;
				case '3':$list[$k]['evaluatelevel'] = '有待改进';break;
				case '4':$list[$k]['evaluatelevel'] = '基本满意';break;
				case '5':$list[$k]['evaluatelevel'] = '非常满意';break;	
				default:break;
			}
		}
		$page = $data->render();
		$device = Db::name('pj_device')->where('usestatus',1)->select();

		$this->assign('start',$start);
		$this->assign('end',$end);
		$this->assign('name',$name);
		$this->assign('deviceid',$deviceid);
		$this->assign('evaluatelevel',$evaluatelevel);

		$this->assign('dec',$device);
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}


/* -------------------------------------------------------------------------------------- */	
	//评价统计
	public function count(){
		$this->auth();
		$data = Db::name('sys_workman')->field('id,name,sectionid')->paginate(12);
		$list = $data->all();
		foreach ($list as $k => $v) {
			$map1['workmanid'] = $v['id'];
			$map1['evaluatelevel'] = '0';
			$map1['evaluatestatus'] = '1';
			$list[$k]['evaluatelevel1']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '1';
			$list[$k]['evaluatelevel2']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '2';
			$list[$k]['evaluatelevel3']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '3';
			$list[$k]['evaluatelevel4']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '4';
			$list[$k]['evaluatelevel5']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '5';
			$list[$k]['evaluatelevel6']	= Db::name('pj_evaluate')->where($map1)->count();
			//小计
			$list[$k]['sum'] = 	$list[$k]['evaluatelevel1']+	$list[$k]['evaluatelevel2']+$list[$k]['evaluatelevel3']+$list[$k]['evaluatelevel4']+$list[$k]['evaluatelevel5']+$list[$k]['evaluatelevel6'];
			//满意率
			$many = $list[$k]['evaluatelevel6']+$list[$k]['evaluatelevel5'];
			if($many!=0){
				$list[$k]['many'] = round(($many/$list[$k]['sum'])*100,2);
			}else{
				$list[$k]['many'] = '0.00';
			}

			$list[$k]['section'] = Db::name('gra_section')->where('id',$v['sectionid'])->value('tname');
			
		}		
		$page = $data->render();
		$device = Db::name('pj_device')->where('usestatus',1)->select();

		$this->assign('dec',$device);
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}

	//评价统计查询页面
	 public function count1(){
	 	$this->auth();
		$name = input('name');
		$start = input('start');
		$end = input('end');
		$deviceid = input('deviceid');
		//判断是否为空并作为条件
		$map = '';
		if($name!=''){//根据姓名查询员工id
			$map['name'] = array('like',"%$name%");
		}
		$data = Db::name('sys_workman')->field('id,name,sectionid')->where($map)->paginate(12);
		$list = $data->all();
		foreach ($list as $k => $v) {
			//判断时间
			if($start!=''&$end==''){
				$map1['evaluatetime'] = array('gt',$start);
			}elseif($start==''&$end!=''){
				$map1['evaluatetime'] = array('lt',$end);
			}elseif($start!=''&$end!=''){
				$map1['evaluatetime'] = array('between time',array($start,$end));
			}
			//设备id
			if($deviceid!=''){
				$map1['deviceid'] = $deviceid;
			}
			$map1['evaluatestatus'] = '1';
			// dump($map1);die;
			$map1['workmanid'] = $v['id'];
			$map1['evaluatelevel'] = '0';
			$list[$k]['evaluatelevel1']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '1';
			$list[$k]['evaluatelevel2']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '2';
			$list[$k]['evaluatelevel3']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '3';
			$list[$k]['evaluatelevel4']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '4';
			$list[$k]['evaluatelevel5']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '5';
			$list[$k]['evaluatelevel6']	= Db::name('pj_evaluate')->where($map1)->count();
			//小计
			$list[$k]['sum'] = 	$list[$k]['evaluatelevel1']+$list[$k]['evaluatelevel2']+$list[$k]['evaluatelevel3']+$list[$k]['evaluatelevel4']+$list[$k]['evaluatelevel5']+$list[$k]['evaluatelevel6'];
			//满意率
			$many = $list[$k]['evaluatelevel6']+$list[$k]['evaluatelevel5'];
			if($many!=0){
				$list[$k]['many'] = round(($many/$list[$k]['sum']),2);
			}else{
				$list[$k]['many'] = '0.00';
			}

			$list[$k]['section'] = Db::name('gra_section')->where('id',$v['sectionid'])->value('tname');
			
		}		
		$page = $data->render();
		$device = Db::name('pj_device')->where('usestatus',1)->select();

		$this->assign('start',$start);
		$this->assign('end',$end);
		$this->assign('name',$name);
		$this->assign('deviceid',$deviceid);

		$this->assign('dec',$device);
		$this->assign('list',$list);
		$this->assign('page',$page);
		return $this->fetch();

	 }


	//评价截图
	public function picture(){
		$this->auth();
		$sectionid = input('sectionid');
		$name = input('name');
		$start = input('start');
		$end = input('end');

		$section = Db::name('gra_section')->where('valid',1)->select();
		if($sectionid||$name||$start||$end){
			$data = Db::name('pj_evaluate')->where("evaluatetype='1' and evaluatestatus='1'")->order('id desc')->paginate(8,false,['query'=>['sectionid'=>$sectionid,'name'=>$name,'start'=>$start,'end'=>$end]]);
		}else{
			$data = Db::name('pj_evaluate')->where("evaluatetype='1' and evaluatestatus='1'")->order('id desc')->paginate(8);
		}
		
		$list = $data->all();
		foreach ($list as $k => $v) {
			//员工姓名
			$list[$k]['workmanid']	 = Db::name('sys_workman')->where('id',$v['workmanid'])->value('name');
		}
		$page = $data->render();
		$this->assign('sec',$section);
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('sectionid',$sectionid);
		$this->assign('name',$name);
		$this->assign('start',$start);
		$this->assign('end',$end);
		return $this->fetch();
	}

	public function downindex(){
		return $this->fetch();
	}


	public function download(){
		$start = input('start');
		$end = input('end');
		$map = array();
		if($start!=''&$end==''){
			$map1['evaluatetime'] = array('egt',$start);
		}elseif($start==''&$end!=''){
			$map1['evaluatetime'] = array('elt',$end);
		}elseif($start!=''&$end!=''){
			$map1['evaluatetime'] = array('between time',array($start,$end));
		}
    	// 实例化phpexcel
    	Loader::import('first.PHPExcel');
		$excel = new \PHPExcel();
		// 根据事项id查询所有数据
		$list = Db::name('pj_evaluate')->where($map)->select();
		foreach ($list as $k => $v) {
			$list[$k]['device'] = Db::name('pj_device')->where('id',$v['deviceid'])->value('number');
			$workman = Db::name('sys_workman')->where('id',$v['workmanid'])->field('name,sectionid')->find();
			//员工姓名
			$list[$k]['workman']	 = $workman['name'];
			$list[$k]['section'] = Db::name('gra_section')->where('id',$workman['sectionid'])->value('tname');
			switch ($v['evaluatelevel']) {
				case '0':$list[$k]['evaluatelevel'] = '态度不好';break;
				case '1':$list[$k]['evaluatelevel'] = '业务不熟';break;
				case '2':$list[$k]['evaluatelevel'] = '时间太长';break;
				case '3':$list[$k]['evaluatelevel'] = '有待改进';break;
				case '4':$list[$k]['evaluatelevel'] = '基本满意';break;
				case '5':$list[$k]['evaluatelevel'] = '非常满意';break;	
				default:break;
			}
		}


		//设置第一行的标题   可以把execl表打开看一下
        $excel->setActiveSheetIndex(0)
            ->setCellValue('A1','员工')
            ->setCellValue('B1','部门')
            ->setCellValue('C1','设备')
            ->setCellValue('D1','评价')
            ->setCellValue('E1','评价时间');
            $i = 2;

            // 设置行高
            $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(22); 
            // 设置宽度
            $excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);  

            // 遍历数据库查询的数据 放入excel
            foreach ($list as $k => $v) {
            	$excel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i,$v['workman'])
                ->setCellValue('B'.$i,$v['section'])
                ->setCellValue('C'.$i,$v['device'])
                ->setCellValue('D'.$i,$v['evaluatelevel'])
                ->setCellValue('E'.$i,$v['evaluatetime']);
                $i++;
            }
        // 底部名称    
  		$excel->getActiveSheet()->setTitle('评价记录');
        $excel->setActiveSheetIndex(0);
        $filename=urlencode('评价记录').'_'.date('Y-m-dHi');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');  
        $objWriter->save('php://output');
        exit;
	}

	public function downindex1(){
		return $this->fetch();
	}

	public function download1(){
		$start = input('start');
		$end = input('end');
		$map = array();

		$list = Db::name('sys_workman')->field('name,id,sectionid')->select();
		foreach ($list as $k => $v) {
			//判断时间
			if($start!=''&$end==''){
				$map1['evaluatetime'] = array('gt',$start);
			}elseif($start==''&$end!=''){
				$map1['evaluatetime'] = array('lt',$end);
			}elseif($start!=''&$end!=''){
				$map1['evaluatetime'] = array('between time',array($start,$end));
			}

			$map1['evaluatestatus'] = '1';
			$map1['workmanid'] = $v['id'];
			$map1['evaluatelevel'] = '0';
			$list[$k]['evaluatelevel1']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '1';
			$list[$k]['evaluatelevel2']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '2';
			$list[$k]['evaluatelevel3']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '3';
			$list[$k]['evaluatelevel4']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '4';
			$list[$k]['evaluatelevel5']	= Db::name('pj_evaluate')->where($map1)->count();
			$map1['evaluatelevel'] = '5';
			$list[$k]['evaluatelevel6']	= Db::name('pj_evaluate')->where($map1)->count();
			//小计
			$list[$k]['sum'] = 	$list[$k]['evaluatelevel1']+$list[$k]['evaluatelevel2']+$list[$k]['evaluatelevel3']+$list[$k]['evaluatelevel4']+$list[$k]['evaluatelevel5']+$list[$k]['evaluatelevel6'];
			//满意率
			$many = $list[$k]['evaluatelevel6']+$list[$k]['evaluatelevel5'];
			if($many!=0){
				$list[$k]['many'] = round(($many/$list[$k]['sum']),2);
			}else{
				$list[$k]['many'] = '0.00';
			}
			$list[$k]['section'] = Db::name('gra_section')->where('id',$v['sectionid'])->value('tname');
			
		}
    	// 实例化phpexcel
    	Loader::import('first.PHPExcel');
		$excel = new \PHPExcel();

		//设置第一行的标题   可以把execl表打开看一下
        $excel->setActiveSheetIndex(0)
            ->setCellValue('A1','员工')
            ->setCellValue('B1','非常满意')
            ->setCellValue('C1','基本满意')
            ->setCellValue('D1','有待改进')
            ->setCellValue('E1','时间太长')
            ->setCellValue('F1','业务不熟')
            ->setCellValue('G1','态度不好')
            ->setCellValue('H1','满意率')
            ->setCellValue('I1','小计')
            ->setCellValue('J1','部门');
            $i = 2;

            // 设置行高
            $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);   

            // 遍历数据库查询的数据 放入excel
            foreach ($list as $k => $v) {
            	$excel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i,$v['name'])
                ->setCellValue('B'.$i,$v['evaluatelevel6'])
                ->setCellValue('C'.$i,$v['evaluatelevel5'])
                ->setCellValue('D'.$i,$v['evaluatelevel4'])
                ->setCellValue('E'.$i,$v['evaluatelevel3'])
                ->setCellValue('F'.$i,$v['evaluatelevel2'])
                ->setCellValue('G'.$i,$v['evaluatelevel1'])
                ->setCellValue('H'.$i,$v['many'].'%')
                ->setCellValue('I'.$i,$v['sum'])
                ->setCellValue('J'.$i,$v['section']);
                $i++;
            }
        // 底部名称    
  		$excel->getActiveSheet()->setTitle('评价统计');
        $excel->setActiveSheetIndex(0);
        $filename=urlencode('评价统计').'_'.date('Y-m-dHi');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');  
        $objWriter->save('php://output');
        exit;
	}
}