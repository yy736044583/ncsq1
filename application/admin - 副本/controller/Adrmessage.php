<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Loader;
use first\PHPExcel;
use app\admin\Controller\Common;
/*
**错误日志导出
 */
class Adrmessage extends Common{

	public function index(){
		$this->auth();
		return $this->fetch();
	}

	public function exceldown(){
		$time = input('time');
		$map = array();
		if($time){
			$map['createtime'] = ['EGT',$time];
		}
    	// 实例化phpexcel
    	Loader::import('first.PHPExcel');
		$excel = new \PHPExcel();
		// 根据事项id查询所有数据
		$list = Db::name('sys_adrmessage')->where($map)->select();

		 $excel->getProperties()->setCreator('a')
            ->setLastModifiedBy('b')
            ->setTitle('设备编号')
            ->setSubject('appname')
            ->setDescription('生成时间')
            ->setKeywords('错误日志')
            ->setCategory('备注');  //不知道的就用英文字母测试一下

		//设置第一行的标题   可以把execl表打开看一下
        $excel->setActiveSheetIndex(0)
            ->setCellValue('A1','设备编号')
            ->setCellValue('B1','appname')
            ->setCellValue('C1','生成时间')
            ->setCellValue('D1','错误日志');
            $i = 2;

            // 设置行高
            $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(22); 

            // 设置宽度
            $excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
            $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20); 
            $excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);  
            $excel->getActiveSheet()->getColumnDimension('D')->setWidth(80);   

            // 遍历数据库查询的数据 放入excel
            foreach ($list as $k => $v) {
            	$excel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i,$v['devicenum'])
                ->setCellValue('B'.$i,$v['appname'])
                ->setCellValue('C'.$i,$v['createtime'])
                ->setCellValue('D'.$i,$v['message']);
                $i++;
            }
        // 底部名称    
  		$excel->getActiveSheet()->setTitle('错误日志');
        $excel->setActiveSheetIndex(0);
        $filename=urlencode('错误日志').'_'.date('Y-m-dHis');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');  
        $objWriter->save('php://output');
        exit;		
	}
}