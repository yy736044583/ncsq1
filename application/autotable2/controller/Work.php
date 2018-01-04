<?php
namespace app\autotable2\controller;
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
    	$list = Db::name('gra_section')->where('top',1)->paginate(12,true);
        $page = $list->render();
        $this->assign('page', $page);
        $this->assign('list',$list);
        return  $this->fetch();
    }
    //查询办事指南标题
    public function matter(){
        $id = input('id');
  
            //根据Id查询办事指南
        $list = Db::name('gra_matter')->field('tname,id,tid')->where("deptid",$id)->paginate(6,true,['query'=>array('id'=>input('id'))]);

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
        $list = Db::name('gra_matter')
        // ->field('id,sectionid,name,workwindow,timelimit,limitday,promisesday,method,character,address')
        ->where("id",$id)
        ->find();
 
        if(empty($list)){
            $this->error('无信息');
        }
       
      
        $this->assign('list',$list);
        return $this->fetch();
    }


    //conditionset 审批条件 warrntset法定依据 flowlimit办理流程
    public function material(){
        $type = input('type');
        $matterid = input('matterid');
        switch ($type) {
            case 'conditionset':
                $table = 'gra_accept';
                break;
            default:break; 
        }
        $mattername = Db::name('gra_matter')->where('id',$matterid)->value('tname');
        $list = Db::name("$table")->where('matterid',$matterid)->select();
        
        $this->assign('mattername',$mattername);
        $this->assign('list',$list);
        $this->assign('type',$type);
        return $this->fetch();
    }

    //办事指南办理材料
    public function materialtable(){
        $matterid = input('matterid');
        $mattername = Db::name('gra_matter')->where('id',$matterid)->value('tname');
        $list = Db::name('gra_datum')->where("matterid",$matterid)->select();
        foreach ($list as $k => $v) {
            if($v['paper']==2){
                $list[$k]['paper'] = '电子';
            }elseif($v['paper']==1){
                $list[$k]['paper'] = '纸质';
            }else{
                $list[$k]['paper'] = '';
            }
            
        }
        $this->assign('list',$list);
        $this->assign('mattername',$mattername);
        return $this->fetch();
    }
    //办事指南法定依据
    public function materialtable2(){
        $matterid = input('matterid');
        $mattername = Db::name('gra_matter')->where('id',$matterid)->value('tname');
        $list = Db::name('gra_warrntset')->where("matterid",$matterid)->select();
        $this->assign('list',$list);
        $this->assign('mattername',$mattername);
        return $this->fetch();
    }
    //办事指南办理流程
    public function materialtable1(){
        $matterid = input('matterid');
        $mattername = Db::name('gra_matter')->where('id',$matterid)->value('tname');
        $list = Db::name('gra_flowlimit')->where("matterid",$matterid)->select();
        foreach ($list as $k => $v) {
            switch ($v['flowlimit']) {
                case '1':
                   $list[$k]['flowlimit'] = '申请受理';
                    break;
                case '2':
                   $list[$k]['flowlimit'] = '审核';
                    break;
                case '3':
                   $list[$k]['flowlimit'] = '办结';
                    break;
                case '4':
                   $list[$k]['flowlimit'] = '制证';
                    break;
                case '5':
                   $list[$k]['flowlimit'] = '取件';
                    break;
                case '6':
                   $list[$k]['flowlimit'] = '办理';
                    break;
                case '7':
                   $list[$k]['flowlimit'] = '决定';
                    break;
                case '8':
                   $list[$k]['flowlimit'] = '证明';
                    break;
                case '9':
                   $list[$k]['flowlimit'] = '核实';
                    break;
                case '10':
                   $list[$k]['flowlimit'] = '答复';
                    break;
                case '0':
                   $list[$k]['flowlimit'] = '其他';
                    break;
                default:
                    # code...
                    break;
            }
        }
        $this->assign('list',$list);
        $this->assign('mattername',$mattername);
        return $this->fetch();
    }
}