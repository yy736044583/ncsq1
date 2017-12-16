<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Model;
/*
**  接收身份证号码 进行办件查询   
 */
class Showinfo extends \think\Controller{

	public function index(){
		//接受身份证参数
        $num = input('IDCard');
        $list = array(array('Code'=>'暂无数据','Name'=>'暂无数据','OrganNameCreator'=>'暂无数据','PubUserNameApplicant'=>'暂无数据','PubUserNameLinkman'=>'暂无数据','FlowStatus'=>'暂无数据'),array('Code'=>'暂无数据','Name'=>'暂无数据','OrganNameCreator'=>'暂无数据','PubUserNameApplicant'=>'暂无数据','PubUserNameLinkman'=>'暂无数据','FlowStatus'=>'暂无数据'));
        $this->assign('list',$list);
        return $this->fetch();
        //根据身份证号查询用户ID
        if($num!=''){     
	        $pro = model('Projects');
	        $userinfo = model('TFrontUserInfo');
            $userid = $userinfo->where("UserCard='$num'")->value('UserId');
            if($userid!=''){
                //根据用户id查询办件进度
                $list = $pro
                ->field('Code,Name,PubUserNameApplicant,PubUserNameLinkman,OrganNameCreator,FlowStatus,PromiseDays,AttributionUserId')
                ->where("AttributionUserId='$userid'")
                ->select();
                //如果查询到相关信息则输出
                if($list){
                     foreach ($list as $k => $v) {
                        $list[$k]['PubUserNameApplicant'] =  mb_convert_encoding($v['PubUserNameApplicant'],'UTF-8', 'gbk');
                        $list[$k]['PubUserNameLinkman'] =  mb_convert_encoding($v['PubUserNameLinkman'],'UTF-8', 'gbk');
                        $list[$k]['OrganNameCreator'] =  mb_convert_encoding($v['OrganNameCreator'],'UTF-8', 'gbk');
                        $list[$k]['Name'] =  mb_convert_encoding($v['Name'],'UTF-8', 'gbk'); 
                        if($v['FlowStatus'] =='finish'){
                            $list[$k]['FlowStatus'] = '办结';
                        }elseif($v['FlowStatus'] =='apply'){
                            $list[$k]['FlowStatus'] = '申请';
                        }elseif($v['FlowStatus'] =='accept'){
                            $list[$k]['FlowStatus'] = '受理';      
                        }elseif($list[$k]['FlowStatus'] = 'prejudication'){
                            $list[$k]['FlowStatus'] = '预审';
                        }elseif($list[$k]['FlowStatus'] = 'draft'){
                            $list[$k]['FlowStatus'] = '草稿';
                        }                
                    }                   
                }else{
                    //未查询到则输出暂无数据
                    $list = array(array('Code'=>'暂无数据','Name'=>'暂无数据','OrganNameCreator'=>'暂无数据','PubUserNameApplicant'=>'暂无数据','PubUserNameLinkman'=>'暂无数据','FlowStatus'=>'暂无数据'),array('Code'=>'暂无数据','Name'=>'暂无数据','OrganNameCreator'=>'暂无数据','PubUserNameApplicant'=>'暂无数据','PubUserNameLinkman'=>'暂无数据','FlowStatus'=>'暂无数据'));
                }
            }else{
                //如果根据身份证未查询到则让其重新输入
                $this->error('未查询到相关数据，请确认是否已办理','index/index');
            }
        }
        $this->assign('list',$list);
        return $this->fetch();
	}
}