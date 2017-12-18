<?php
namespace app\autotable\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Model;
 /* 
    网上办事 
    办事部门    index
    事项标题    matter
    办事内容    matterinfo
 */
class Work extends \think\Controller{

    public function index(){
    	$list = Db::name('sys_section')->paginate(12,true);
        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list',$list);
        return  $this->fetch();
    }
    //查询办事指南标题
    public function matter(){
        $id = input('id');
  
            //根据Id查询办事指南
        $list = Db::name('sys_matter')->field('name,id')->where("sectionid",$id)->paginate(6,true,['query'=>array('id'=>input('id'))]);
        if(empty($list)){
            $this->error('该部门暂无数据');
        }

        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list',$list);
        return $this->fetch();
    }
    //查询办事指南详情

    public function mattershow(){
        $id = input('id');
        if($id!=''){
            $list = Db::name('sys_matter')
            ->field('id,sectionid,name,workwindow,timelimit,limitday,promisesday,method,character,address')
            ->where("id",$id)
            ->find();
        }   
        //差：单位名称
        $list['sectionname'] = Db::name('sys_section')->where('id',$list['sectionid'])->value('name');
        if(empty($list)){
            $this->error('无信息');
        }
       
        switch ($list['method']) {
                case '1':
                    $list['method'] = '审批';
                    break;
                case '2':
                    $list['method'] = '转报';
                    break;
                case '3':
                    $list['method'] = '上报';
                    break;
                case '4':
                    $list['method'] = '备案';
                    break;
                default:
                    $list['method'] = '无';
                    break;
            }    
         //是否进驻服务中心
        if($list['workwindow']=='1'){
            $list['workwindow'] = '是';
        }else{
            $list['workwindow'] = '';
        }
       

        //应交材料
        // $datlist = Db::name('sys_datum')->field('title')->where("matterid",$list['id'])->select();
         
        // //审批条件 
        // $gscsetlist = Db::name('sys_conditionset')->field('approvecondition')->where("matterid",$list['id'])->select();
        
        // //法定依据
        // $warlist = Db::name('sys_warrntset')->field('filetitle,govfilecontent,filetype')->where("matterid",$list['id'])->select();
   
        // $this->assign('warlist',$warlist); 
        // $this->assign('gscsetlist',$gscsetlist);  
        // $this->assign('datlist',$datlist);       
        $this->assign('list',$list);
        return $this->fetch();
    }


    //conditionset 审批条件 warrntset法定依据 flowlimit办理流程
    public function material(){
        $type = input('type');
        $matterid = input('matterid');
        switch ($type) {
            case 'conditionset':
                $table = 'sys_conditionset';
                break;
             case 'warrntset':
                $table = 'sys_warrntset';
                break;
             case 'flowlimit':
                $table = 'sys_flowlimit';
                break;
            default:break; 
        }
        $mattername = Db::name('sys_matter')->where('id',$matterid)->value('name');
        $list = Db::name("$table")->where('matterid',$matterid)->find();
        $this->assign('mattername',$mattername);
        $this->assign('list',$list);
        $this->assign('type',$type);
        return $this->fetch();
    }

    //办事指南办理材料
    public function materialtable(){
        $matterid = input('matterid');
        $mattername = Db::name('sys_matter')->where('id',$matterid)->value('name');
        $list = Db::name('sys_datum')->field('title')->where("matterid",$matterid)->select();
        $this->assign('list',$list);
        $this->assign('mattername',$mattername);
        return $this->fetch();
    }
}