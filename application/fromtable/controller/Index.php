<?php
namespace app\fromtable\controller;
use think\Db;
use think\Request;
//样表展示接口
class Index{

	/**
	 * 根据方法名跳转
	 * @param [string] $[action] [方法名]
	 * @param [string] $[devicenum] [设备编号]
	 */
    public function index(){ 
		$action = input('action');
		$devicenum = input('devicenum');
		//如果设备编号为空则返回
		if(empty($devicenum)){
			echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
			return;
		}
		//根据方法名跳转到各个方法
		switch ($action) {
			//查询部门
			case 'section':
				$this->section();
				break;
			//查询事项	
			case'matter':
				$this->matter(input('sectionid'));
				break;
			//查询文件	
			case'showfile':
				$this->showfile(input('matterid'));
				break;
			//搜索	
			case'sousuo':
				//按事项名称搜索
				$this->sousuo(input('name'));
				break;
			default:
				echo json_encode(['data'=>array(),'code'=>'404','message'=>'未找到'],JSON_UNESCAPED_UNICODE);
				return;
				break;
		}
    }

    //部门查询
    public function section(){
    	$list = Db::name('gra_section')->where('top',1)->field('id,tname,tid')->select();
        
    	if(!empty($list)){
			echo json_encode(['data'=>$list,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
    	}else{
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'失败'],JSON_UNESCAPED_UNICODE);
    	}
    	return;
    }

    /**
     * [matter 查询事项]
     * @param  [int] $sectionid [部门id]
     * @return [type]            [id事项id name事项名称]
     */
    public function matter($sectionid){
    	if(empty($sectionid)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'部门不能为空']);
    		return;
    	}
    	$list = Db::name('gra_matter')->where('deptid',$sectionid)->field('id,tname,deptid')->select();
    	if(!empty($list)){
			echo json_encode(['data'=>$list,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
    	}else{
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'无数据'],JSON_UNESCAPED_UNICODE);
    	}
    	return;
    }

    /**
     * [showfile 查询文件]
     * @param  [type] $matterid [事项id]
     * @return [type]           [file[sort 排序 url地址 type类型] id文件id title 文件标题]
     */
    public function showfile($matterid){
    	if(empty($matterid)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'事项不能为空'],JSON_UNESCAPED_UNICODE);
    		return;
    	}
    	//根据事项id查询所有的文件标题
    	$list = Db::name('sys_showfile')->where('matterid',$matterid)->field('id,title')->select();
        $fileid = Db::name('sys_showfile')->where('matterid',$matterid)->column('id');

        // $list = Db::name('sys_showfileup')->whereIn('showfileid',$fileid)->field('id,title,url,thumburl')->select();

    	if(empty($list)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'该事项没有相关资料'],JSON_UNESCAPED_UNICODE);
    		return;
    	}
    	$request = Request::instance();
        //遍历文件标题  查询下面的所有文件
        foreach ($list as $k => $v) {
             $file = Db::name('sys_showfileup')->where('showfileid',$v['id'])->order('sort desc')->field('sort,type,title,url,thumburl')->select();
            foreach ($file as $key => $val) {
                $file[$key]['url'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public/'.$val['url'];
                $file[$key]['thumburl'] = $request->domain().dirname($_SERVER['SCRIPT_NAME']).'/public/'.$val['thumburl'];
            }
            $list[$k]['file'] = $file;
        }

    	echo json_encode(['data'=>$list,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
    	return;
    }

    /**
     * [sousuo 搜索事项]
     * 可以根据事项名和排号编号查询
     * @param  [type] $name [事项名称]
     * @return [type]       [id 事项id name事项名]
     */
    public function sousuo($name){
    	if(empty($name)){
    		echo json_encode(['data'=>array(),'code'=>'400','message'=>'事项不能为空'],JSON_UNESCAPED_UNICODE);
    		return;
    	}

    	$list = Db::name('sys_matter')->whereLike('name',"%$name%")->field('id,name,sectionid')->select();
        $today = date('Ymd',time());
        //如果在事项表中未查询到相关数据 则根据排号编号查询事项id  再根据事项id查询
        if(empty($list)){
            $name = strtoupper($name);
            $matterid = Db::name('ph_queue')->where('flownum',$name)->where('today',$today)->value('matterid');
            if($matterid){
                $list = Db::name('gra_matter')->where('id',$matterid)->find();
            }
        }
        
		echo json_encode(['data'=>$list,'code'=>'200','message'=>'成功'],JSON_UNESCAPED_UNICODE);
    	
    	return;
    }

}