<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
/*
*   投诉建议
 */
class Complain extends \think\Controller{


   //意见 建议
    public function index(){
        $data = Db::name('ds_complain')->where('valid',1)->order('uptime desc')->paginate(8);
        $list = $data->all();
        $page = $data->render();
        $this->assign('page', $page);
        $this->assign('list',$list);
        return $this->fetch();
    }

   //意见详细查询
    public function main6_1(){
        $id = input('id');
        $list = Db::name('ds_complain')->where('id',$id)->find();
        $this->assign('list',$list);
        return $this->fetch();
    }


    //意见提交
    public function main6_2(){
        $data = input('post.');
        if($data){
            $data['uptime'] = date('Y-m-d H:i:s',time());

            if(Db::name('ds_complain')->insert($data)){
                $this->success('提交成功','complain/index');
            }else{
                $this->error('提交失败');
            }
         
        }
        return $this->fetch();


        
    }
}