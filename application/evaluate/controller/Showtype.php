<?php  
namespace app\evaluate\controller;
use think\Request;  
use think\Controller;
use think\Db;
//查询评价 截图状态 看是否已处理完
class Showtype extends \think\Controller{

	public function index(){
		$action = input('action');
		switch ($action) {
			case 'showtype':
				$this->showtype(input('id'));
				break;
			case 'dlevaluate':
				$this->dlevaluate(input('dlid'));
				break;
			default:
				# code...
				break;
		}
	}
		
	//查询评价
	public function showtype($id){
		$type = Db::name('pj_evaluate')->where('id',$id)->value('evaluatestatus');
		$data = ['type'=>"$type",'id'=>"$id"];
		echo json_encode($data);
	}

	//如果是如果未确认截图或者是未评价则删除该评价
	public function dlevaluate($dlid){
		$type = Db::name('pj_evaluate')->where('id',$dlid)->value('evaluatestatus');
		if($type=='0'){//如果是评价则直接删除
			Db::name('pj_evaluate')->where('id',$dlid)->delete();
		}else{//如果是截图则删除该条数据后删除截图文件
			$url = Db::name('pj_evaluate')->where('d',$dlid)->value('photobefor');
			if(Db::name('pj_evaluate')->where('id',$dlid)->delete()){
				//删除之前的图片
				$path = ROOT_PATH . 'public' . DS . 'uploads'.DS.$url;
				if(file_exists($path)&$url!=''){
					unlink($path);
				}
			}
		}
	}
	

}