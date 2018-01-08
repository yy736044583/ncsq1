<?php
namespace app\integration\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Work extends \think\Controller{
	// 选择事项
    public function index(){
        $nid = input('nid');//个人服务
        $legal = input('legal');//法人服务
        $themename = input('themename');//主题
        $deptid = input('deptid');//部门
        $name = input('name');//事项关键字
        $map = array();//查询主题条件
        $map1 = array();//查询部门条件
        $map2 = array();//查询事项条件
        if(!empty($nid)){
            $map['nid'] = 1;
            $map1['nid'] = 1;
            $map2['nid'] = 1;
        }
        if(!empty($legal)){
            $map['nid'] = 2;
            $map1['legal'] = 1;
            $map2['legal'] = 1;
        }
        if(!empty($themename)){
            $map2['theme'] = $themename;
        }
        if(!empty($deptid)){
            $map2['deptid'] = $deptid;
            //部门名称
            $tname = Db::name('gra_section')->where('tid',$deptid)->value('tname');
            $this->assign('tname',$tname);
        }
        if(!empty($name)){
            $map2['tname'] = ['like',"%$name%"];

        }
        // dump($map2);die;
        //事项列表
        $list = Db::name('gra_matter')->where($map2)->field('id,tname,department')->paginate(6,true);
        //根据个人服务还是法人服务查询主题
        $theme = Db::name('gra_theme')->where($map)->field('id,tname,tid')->select();
        //根据个人服务还是法人服务查询部门
        $section = Db::name('gra_section')->where($map1)->field('id,tid,tname')->select();
        $page = $list->render();
        $this->assign('nid',$nid);
        $this->assign('legal',$legal);
        $this->assign('theme',$theme);
        $this->assign('section',$section);
        $this->assign('themename',$themename);
        $this->assign('deptid',$deptid);
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('name',$name);
        return  $this->fetch();
    }


    //热门事项
    public function index1(){
        $nid = input('nid');//个人服务
        $legal = input('legal');//法人服务
        $map = array();
        $map1 = array();
        $map2 = array();
        if(!empty($nid)){
            $map['nid'] = 1;
            $map1['nid'] = 1; 
        }else{
            $nid= 1;
        }
        
        if(!empty($legal)){
            $map['nid'] = 2;
            $map1['legal'] = 1;
            $map2['legal'] = 1;
            $nid = '';
        }
        $map2['nid'] = $nid;
        //根据个人服务还是法人服务查询主题
        $theme = Db::name('gra_theme')->where($map)->field('id,tname,tid')->select();
        //根据个人服务还是法人服务查询部门
        $section = Db::name('gra_section')->where($map1)->field('id,tid,tname')->select();
        $list = Db::name('gra_matter')->where($map2)->order('sort desc')->field('id,tname,department')->paginate(6,true);
        $page = $list->render();
        $this->assign('nid',$nid);
        $this->assign('legal',$legal);
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('theme',$theme);
        $this->assign('section',$section);
        return  $this->fetch();
    }
    // 事项详情
    public function matter(){
        $id = input('id');
        $list = Db::name('gra_matter')->where('id',$id)->find();
        $this->assign('list',$list);
        return  $this->fetch();
    }
    // 扫描身份证  
    public function idcard(){
        return  $this->fetch();
    }
    // 手机验证  
    public function phone(){
        return  $this->fetch();
    }
    // 人脸识别  
    public function face(){
        return  $this->fetch();
    }
    // 指纹识别  
    public function fingerprint(){
        return  $this->fetch();
    }
    // 承诺书  
    public function letter(){
        return  $this->fetch();
    }
    // 选择资料 
    public function file(){
        return  $this->fetch();
    }
    // 上传资料  
    public function fileup(){
        return  $this->fetch();
    }
    // 取件方式  
    public function pickup(){
        return  $this->fetch();
    }
    // 打印小票  
    public function prin(){
        return  $this->fetch();
    }
}