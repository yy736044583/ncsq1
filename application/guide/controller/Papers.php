<?php
namespace app\guide\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Model;
/*
*   办件查询
 */
class Papers extends \think\Controller{

    public function index(){

        return  $this->fetch();
    }
    //办件查询
    public function query_5(){
        $num = input('num');
        $pro = model('Projects');
        $userinfo = model('TFrontUserInfo');
        if(strlen($num)>18){
            $list = $pro
            ->field('Code,Name,PubUserNameApplicant,PubUserNameLinkman,OrganNameCreator,FlowStatus,PromiseDays,AttributionUserId')
            ->where("code='$num'")->select();
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
            echo  json_encode($list);
        }else{
            $userid = $userinfo->where("UserCard='$num'")->value('UserId');
            if($userid!=''){
                $list = $pro
                ->field('Code,Name,PubUserNameApplicant,PubUserNameLinkman,OrganNameCreator,FlowStatus,PromiseDays,AttributionUserId')
                ->where("AttributionUserId='$userid'")->select();
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
                echo json_encode($list);
            }  
        }
    }

    

}