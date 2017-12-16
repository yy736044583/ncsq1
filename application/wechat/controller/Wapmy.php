<?php
namespace app\wechat\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use app\wechat\Controller\Common;

class Wapmy extends Common{

    public function index(){
     	//获取opid
     	// $Openid = $_SESSION['Openid'];
        $Openid = Session('openid');  
        if(!empty($Openid)){
            //判断是否超时
            $nowtime = date('Y-m-d H:i:s',time());
            $sqltime = Db::name('wy_peopleinfo')
            ->alias('a')
            ->field('w.id,w.endtime')
            ->join('wy_orderrecord w','a.id = w.peopleid')
            ->where("a.openid = '$Openid' and w.endtime < '$nowtime'")
            ->order('w.id desc')
            ->select();
            foreach ($sqltime as $key => $value) {
               $dataid['status'] = 2;
               Db::table('wy_orderrecord')->where('id', $sqltime[$key]['id'])->update($dataid);
            }
            $sql = Db::name('wy_peopleinfo')
            ->alias('a')
            ->where("a.openid = '$Openid'")
            ->field('w.id,w.businessid,w.number,w.status')
            ->join('wy_orderrecord w','a.id = w.peopleid')
            ->order('w.id desc')
            ->select();
            foreach ($sql as $key => $value) {
                $sql[$key]["busName"] = Db::name('sys_business')->where("id", $sql[$key]['businessid'])->value('name');
                //状态，0预约取消，1预约成功,2已经取号，3超时未取号
                if($sql[$key]['status'] == 0){
                    $sql[$key]["type"] = "预约中";
                }elseif ($sql[$key]['status'] == 1) {
                    $sql[$key]["type"] = "成功取号";
                }elseif ($sql[$key]['status'] == 2) {
                    $sql[$key]["type"] = "超时未取号";
                }else{
                    $sql[$key]["type"] = "预约已经取消";
                }
            }
        }else{
            $sql = null;
        }
     	
     	// dump($sql);
     	$this->assign('sql',$sql);
        return  $this->fetch();
    }

    public function show(){
     	$id = input("id");//预约记录id
     	$info = Db::name('wy_orderrecord')
     	->alias('a')
     	->where("a.id = $id")
     	->join('wy_peopleinfo w','a.peopleid = w.id')
     	->find();
     	$info["busName"] = Db::name('sys_business')->where("id", $info['businessid'])->value('name');
     	$info["orderID"] = $id;
     	// dump($info);
     	$this->assign('info',$info);
        return  $this->fetch();
    }

     public function delete(){
     	$id = input("id");//预约id
     	echo $id;
     	$info = Db::name('wy_orderrecord')->where("id = $id")->update(['status' => '3','canceltime' => date("Y-m-d H:i:s",time())]);
     	if ($info) {
     		echo 1;//取消成功
     	}else{
     		echo 0;//取消失败
     	}
     }

}