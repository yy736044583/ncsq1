<?php
namespace app\appraise\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Model;
 /* 
        网上办事 
    办事部门    index
    办事标题    main4_1
    办事内容    main4_1_1
 */
class Work extends \think\Controller{

    public function index(){
        $list = Db::name('sys_section')->paginate(18,true);
        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list',$list);
        return  $this->fetch();
    }
    //查询办事指南标题
    public function main4_1(){
        $id = input('id');
        if($id!=''){
            $name = Db::name('sys_section')->where('id',$id)->value('name');
            //根据Id查询办事指南
            $list = Db::name('sys_matter')->field('name,id')->where("sectionid",$id)->paginate(16,true,['query'=>array('id'=>input('id'))]);
            if(empty($list)){
                $this->error('该部门暂无数据');
            }
            $this->assign('name',$name);
        }
        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list',$list);
        return $this->fetch();
    }
    public function main4_1_1(){
        $id = input('id');
        if($id!=''){
            $list = Db::name('sys_matter')
            ->field('id,sectionid,name,workwindow,timelimit,limitday,promisesday,method,character,address')
            ->where("sectionid='$id'")
            ->find();
        }   
        //差：单位名称
        $list['sectionname'] = Db::name('sys_section')->where('id',$list['sectionid'])->value('name');
        if(empty($list)){
            $this->error('无信息');
        }
       
 
         //是否进驻服务中心
        if($list['workwindow']=='1'){
            $list['workwindow'] = '是';
        }else{
            $list['workwindow'] = '';
        }
       

        //应交材料
        $datlist = Db::name('sys_datum')->field('title')->where("matterid",$list['id'])->select();
         
        //审批条件 
        $gscsetlist = Db::name('sys_conditionset')->field('approvecondition')->where("matterid",$list['id'])->select();
        
        //法定依据
        $warlist = Db::name('sys_warrntset')->field('filetitle,govfilecontent,filetype')->where("matterid",$list['id'])->select();
   
        $this->assign('warlist',$warlist); 
        $this->assign('gscsetlist',$gscsetlist);  
        $this->assign('datlist',$datlist);       
        $this->assign('list',$list);
        return $this->fetch();
    }
}