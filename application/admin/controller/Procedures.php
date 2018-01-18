<?php 
namespace app\admin\controller;
use think\Db;
use think\Request;
use app\admin\Controller\Common;

/**
* 资料审批
*/
class Procedures extends Common{

	public function index(){
		$status = input('status');
		$tname = input('tname');
		$getnum = input('getnum');
		$map = array();
		if($status){
			$map['a.status'] = $status;
		}
		if($getnum){
			$map['a.getnum'] = ['like',"%$getnum%"];
		}
		if($tname){
			$matterid = Db::name('gra_matter')->whereLike('tname',"%$tname%")->column('id');
			$matterid = implode(',',$matterid);
			$map['a.matterid'] = ['in',$matterid];
		}
		$map['a.type'] = 2;//1提交  2确认提交
		// dump($map);
		$data = Db::name('gra_matterdatum')->alias('a')
		->join('sys_peopleinfo b','a.userid=b.id')
		->where($map)
		->field('a.id,a.matterid,a.userid,a.status,b.idcard_Name,b.phone,a.manner,a.createtime,a.getnum')
		->order('a.createtime desc,a.status')
		->paginate(12);

		$page = $data->render();
		$list = $data->all();
		foreach ($list as $k => $v) {
			$list[$k]['tname'] = Db::name('gra_matter')->where('id',$v['matterid'])->value('tname');
			switch ($v['status']) {
				case '0':
					$list[$k]['status'] = '未审核';
					break;
				case '1':
					$list[$k]['status'] = '审核成功';
					break;
				case '2':
					$list[$k]['status'] = '审核失败';
					break;
				default:break;	
			}
			switch ($v['manner']) {
				case '0':
					$list[$k]['mannername'] = '自主取件';
					break;
				case '1':
					$list[$k]['mannername'] = '邮寄';
					break;
				case '2':
					$list[$k]['mannername'] = '已邮寄';
					break;
				case '3':
					$list[$k]['mannername'] = '已取件';
					break;
				default:break;	
			}
			
		}
		$this->assign('list',$list);
		$this->assign('status',$status);
		$this->assign('tname',$tname);
		$this->assign('getnum',$getnum);
		$this->assign('page',$page);
		return $this->fetch();
	}

	//审核
	public function showres(){
		if(request()->isPost()){
			$data = input('post.');
			$id = $data['id'];
			unset($data['id']);
			if(Db::name('gra_matterdatum')->where('id',$id)->update($data)){
				$this->success('成功','procedures/index');
			}else{
				$this->error('失败');
			}
		}

		$id = input('id');
		$list = Db::name('gra_matterdatum')->where('id',$id)->where('type',2)->field('id,matterid,userid,summary,status')->find();
		$list['tname'] = Db::name('gra_matter')->where('id',$list['matterid'])->value('tname');
		 //应交材料
        $datlist = Db::name('gra_datum')->where("matterid",$list['matterid'])->order('sort')->select();
        $count = 0;//统计应提交的资料数
        foreach ($datlist as $k => $v) {
            if($v['nullurl']){
                $count +=1;
            }
            $datlist[$k]['picture'] = Db::name('gra_datumfile')->where('datumid',$v['id'])->where('fdatumid',$id)->field('file,id')->select();
        }
		$this->assign('list',$list);
		$this->assign('datlist',$datlist);
		return $this->fetch();
	}

	//邮寄信息 并提交
	public function showaddress(){
		if(request()->isPost()){
			$id =  input('id');
			$status = Db::name('gra_matterdatum')->where('id',$id)->value('status');
			if($status!=1){
				$this->error('请先审核');
			}
			$manner =  input('manner');
			$number =  input('number');
			if(Db::name('gra_matterdatum')->where('id',$id)->update(['manner'=>$manner,'number'=>$number])){
				$this->success('成功','procedures/index');
			}else{
				$this->error('失败');
			}
		}
		$id = input('id');
		$list = Db::name('gra_matterdatum')->where('id',$id)->where('type',2)->field('id,address,linkman,phone,manner,number')->find();
		switch ($list['manner']) {
				case '0':
					$list['mannername'] = '自主取件';
					break;
				case '1':
					$list['mannername'] = '邮寄';
					break;
				case '2':
					$list['mannername'] = '已邮寄';
					break;
				case '3':
					$list['mannername'] = '已取件';
					break;
				default:break;	
			}
		$this->assign('list',$list);
		return $this->fetch();
	}

}