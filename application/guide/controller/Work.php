<?php
namespace app\guide\controller;
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
    // public function main4_1(){
    //     $gsc = model('GscOrgan');
    //     $app = model('AppProject');
    //     $name = input('name');
    //     $this->assign('name',$name);
    //     if($name!=''){
    //         $name = '邻水县'.$name;
 
    //         $name = mb_convert_encoding($name,'gbk', 'UTF-8');
    //         //根据部门名称查询部门Id
    //         $id = $gsc->where("GscOrganName='$name'")->value('GscOrganCodeId');
    //         if(!$id){
    //             $this->error('该部门暂无数据');
    //         }
    //         //根据Id查询办事指南
    //         $list = $app->field('AppProjectName,AppProjectCodeID')->where("AppObjectOrganCodeID='$id' and Cancel='1' and StatusCodeID='OK'")->paginate(16,true,['query'=>array('name'=>input('name'))]);
    //         foreach ($list as $k => $v) {
    //             $list[$k]['AppProjectName'] = mb_convert_encoding($v['AppProjectName'],'UTF-8', 'gbk');
    //         }
    //     }
    //     $page = $list->render();
    //     $this->assign('page', $page);
    //     $this->assign('list',$list);
    //     return $this->fetch();
    // }

    // public function main4_1_1(){
    //     $id = input('id');
    //     $app = model('AppProject');
    //     $dat = model('Datum');
    //     $gscset = model('GscApproveConditionSet');
    //     $war = model('WarrntSet');
    //     if($id!=''){
    //         $list = $app
    //         ->field('ID,AppProjectCodeID,AppObjectOrganCodeID,AppProjectName,AppMianObject,AppIsAccreditWindows,timelimit,AppLimitDay,AppPromisesLimitDay,AppMethod,character,WorkWindow,AppAddress')
    //         ->where("AppProjectCodeID='$id'")
    //         ->find();
    //     }
        
    //     $list['AppProjectName'] = mb_convert_encoding($list['AppProjectName'],'UTF-8', 'gbk');
    //     if(!$list){
    //         $this->error('无信息');
    //     }
    //     //审批性质
    //     if($list['character']=='pubApprove'){
    //         $list['character'] = '行政许可';
    //     }elseif($list['character']=='GovApprove'){
    //         $list['character'] = '公共服务';
    //     }elseif ($list['character']=='OtherApprove') {
    //         $list['character'] = '其他';
    //     }  
    //     // 审批方式
    //     if($list['AppMethod']=='Auditing'){
    //         $list['AppMethod'] = '审批';
    //     }elseif($list['AppMethod']=='transitdtelegram'){
    //         $list['AppMethod'] = '转报';
    //     }elseif ($list['AppMethod']=='InspectReport') {
    //         $list['AppMethod'] = '上报';
    //     }elseif($list['AppMethod']=='KeepArchive'){
    //         $list['AppMethod'] = '备案';
    //     }elseif($list['AppMethod']=='Ratify'){
    //         $list['AppMethod'] = '转报';
    //     }  
    //     //审批时限
    //     if($list['timelimit']=='promises'){
    //         $list['timelimit'] = '承诺件';
    //     }elseif($list['timelimit']=='instants'){
    //         $list['timelimit'] = '即办件';
    //     } 
    //      //是否进驻服务中心
    //     if($list['AppIsAccreditWindows']=='1'){
    //         $list['AppIsAccreditWindows'] = '是';
    //     }else{
    //         $list['AppIsAccreditWindows'] = '';
    //     }
    //      //办理地点
    //     if($list['WorkWindow']=='1'){
    //         $list['WorkWindow'] = '邻水县政务服务中心';
    //     }elseif($list['WorkWindow']=='0'){
    //         $list['WorkWindow'] ='';
    //     }

    //     //应交材料
    //     $datlist = $dat->field('ContentTitle')->where("ApproveCodeID='$id' and StatusCodeID='OK'")->select();
    //     foreach ($datlist as $k => $v) {
    //         $datlist[$k]['ContentTitle'] = mb_convert_encoding($v['ContentTitle'],'UTF-8', 'gbk');
    //     } 
    //     //审批条件 
    //     $gscsetlist = $gscset->field('GscApproveCondition')->where("AppProjectCodeID='$id' and StatusCodeID='OK'")->select();
    //     foreach ($gscsetlist as $k => $v) {
    //         $gscsetlist[$k]['GscApproveCondition'] = mb_convert_encoding($v['GscApproveCondition'],'UTF-8', 'gbk');
    //     }
    //     //法定依据
    //     $warlist = $war->field('FileTitle,GovFileContent,FileType')->where("AppProjectCodeID='$id'")->select();
    //     foreach ($warlist as $k => $v) {
    //         $warlist[$k]['FileTitle'] = mb_convert_encoding($v['FileTitle'],'UTF-8', 'gbk');
    //         $warlist[$k]['GovFileContent'] = mb_convert_encoding($v['GovFileContent'],'UTF-8', 'gbk');
    //         $warlist[$k]['FileType'] = mb_convert_encoding($v['FileType'],'UTF-8', 'gbk');
    //     }        
    //     $this->assign('warlist',$warlist); 
    //     $this->assign('gscsetlist',$gscsetlist);  
    //     $this->assign('datlist',$datlist);       
    //     $this->assign('list',$list);
    //     return $this->fetch();
    // }
}