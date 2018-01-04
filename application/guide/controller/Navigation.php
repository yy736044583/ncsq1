<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Model;

// 楼层分布
class Navigation extends \think\Controller{
    public function index(){
        //楼层触屏坐标
       $zbiao = Db::name('ds_device')->where('usestatus',1)->column('address');
        foreach ($zbiao as $k => $v) {
            if($v){
                $zb = explode(',', $v);         
                if($zb['2']==1){
                    $zb1[$k]= $zb;
                    $this->assign('zb1',$zb1);
                }elseif($zb['2']==2){
                    $zb2[$k]= $zb;
                    $this->assign('zb2',$zb2);
                }elseif($zb['2']==3){
                    $zb3[$k]= $zb;
                    $this->assign('zb3',$zb3);
                }elseif($zb['2']==4){
                    $zb4[$k]= $zb;
                    $this->assign('zb4',$zb4);
                }
            }
        }
        $info = Db::name('gra_section')->select();
        // $this->assign('info',$info);
        // 输出一楼数据
        $sid = Db::name('ds_maps')->where('z',1)->column('sectionid');
        $map1['id'] = ['in',$sid]; 
        $info1 = Db::name('gra_section')->field('tname,id,tid')->where($map1)->select();
        $this->assign('info1',$info1);
        // 输出二楼数据
        $sid = Db::name('ds_maps')->where('z',2)->column('sectionid');
        $map2['id'] = ['in',$sid]; 
        $info2 = Db::name('gra_section')->field('tname,id,tid')->where($map2)->select(); 
        $this->assign('info2',$info2);
        // 输出三楼数据
        $sid = Db::name('ds_maps')->where('z',3)->column('sectionid');
        $map3['id'] = ['in',$sid]; 
        $info3 = Db::name('gra_section')->field('tname,id,tid')->where($map3)->select();
        $this->assign('info3',$info3);
        // 输四楼数据
        $sid = Db::name('ds_maps')->where('z',4)->column('sectionid');
        $map4 = '';
        if($sid){
          $map4['id'] = ['in',$sid];  
        }
        
        $infofous = Db::name('gra_section')->field('tname,id,tid')->where($map4)->select();
        $this->assign('infofous',$infofous);
        // 办事指南默认显示数据
        $info4 = Db::name('gra_matter')->field('tname,id')->limit(6)->select();
        
        // $info4 = Db::name('articles')->order('ar_id desc')->limit(6)->select();
        $this->assign('info4',$info4);

        return  $this->fetch();
    }

    // 通过部门id获得办事指南数据
    public function branch(){
        $id = input('id');
        if($id!=''){
            //根据部门名称查询部门Id
            $list = Db::name('gra_matter')->where("deptid",$id)->field('tname,id')->limit(6)->select();
            echo json_encode($list);
        }
    }
    // 通过部门id获得坐标
    public function maps(){
        $id = input('id');
        $info = Db::name('ds_maps')->where('id',$id)->select();
        echo json_encode($info);
    }
}