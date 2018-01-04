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
    	$list = Db::name('gra_section')->where('top',1)->paginate(18,true);
        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list',$list);
        return  $this->fetch();
    }
    //查询办事指南标题
    public function main4_1(){
        $id = input('id');
        $name = Db::name('gra_section')->where('tid',$id)->value('tname');
        //根据Id查询办事指南
        $list = Db::name('gra_matter')->field('tname,id')->where("deptid",$id)->paginate(16,true,['query'=>array('id'=>input('id'))]);
        if(empty($list)){
            $this->error('该部门暂无数据');
        }
        $this->assign('name',$name);
        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list',$list);
        return $this->fetch();
    }
    public function main4_1_1(){
        $id = input('id');
        if($id!=''){
            $list = Db::name('gra_matter')
            // ->field('id,sectionid,name,workwindow,timelimit,limitday,promisesday,method,character,address')
            ->where("id='$id'")
            ->find();
        }   
        if(empty($list)){
            $this->error('无信息');
        }
 
        //应交材料
        $datlist = Db::name('gra_datum')->where("matterid",$list['id'])->select();
        foreach ($datlist as $k => $v) {
            if($v['paper']==2){
                $datlist[$k]['paper'] = '电子';
            }elseif($v['paper']==1){
                $datlist[$k]['paper'] = '纸质';
            }else{
                $datlist[$k]['paper'] = '';
            }
        }
         
        //审批条件 
        $gscsetlist = Db::name('gra_accept')->where("matterid",$list['id'])->select();
        
        //法定依据
        $warlist = Db::name('gra_warrntset')->where("matterid",$list['id'])->select();
        //办理流程
        
        $flowlimit = Db::name('gra_flowlimit')->where("matterid",$list['id'])->group('flowlimit')->select();
        foreach ($flowlimit as $k => $v) {
            switch ($v['flowlimit']) {
                case '1':
                   $flowlimit[$k]['flowlimit'] = '申请受理';
                    break;
                case '2':
                   $flowlimit[$k]['flowlimit'] = '审核';
                    break;
                case '3':
                   $flowlimit[$k]['flowlimit'] = '办结';
                    break;
                case '4':
                   $flowlimit[$k]['flowlimit'] = '制证';
                    break;
                case '5':
                   $flowlimit[$k]['flowlimit'] = '取件';
                    break;
                case '6':
                   $flowlimit[$k]['flowlimit'] = '办理';
                    break;
                case '7':
                   $flowlimit[$k]['flowlimit'] = '决定';
                    break;
                case '8':
                   $flowlimit[$k]['flowlimit'] = '证明';
                    break;
                case '9':
                   $flowlimit[$k]['flowlimit'] = '核实';
                    break;
                case '10':
                   $flowlimit[$k]['flowlimit'] = '答复';
                    break;
                case '0':
                   $flowlimit[$k]['flowlimit'] = '其他';
                    break;
                default:
                    # code...
                    break;
            }
            
        }
   
        $this->assign('warlist',$warlist); 
        $this->assign('gscsetlist',$gscsetlist);  
        $this->assign('datlist',$datlist);       
        $this->assign('list',$list);
        $this->assign('flowlimit',$flowlimit);
        return $this->fetch();
    }
   
}