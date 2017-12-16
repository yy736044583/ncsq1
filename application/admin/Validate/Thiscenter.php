<?php 
namespace app\admin\validate;

use think\Validate;

class Thiscenter extends Validate
{
    protected $rule = [
        'name'  =>  'require|max:100',
        'address' =>  'require|max:100',
        'telephone'     => 'require|length:11',
        'summary' =>  'require',
        'worktime_s_am' =>  'require',
        'worktime_e_am' =>  'require',
        'worktime_s_pm' =>  'require',
        'worktime_e_pm' =>  'require',
        'introduce' =>  'require',
    ];

    protected $message = [
    	'name.require' => '中心名不能为空',
    	'name.max' => '中心名不能超过100位',
    	'address.require' => '地址不能为空',
    	'address.max' => '地址不能大于100位',
        'telephone.require' => '电话不能为空',
        'telephone.length' => '电话号码必须为11位',
        'summary.require' => '中心简介不能为空',
        'worktime_s_am.require' => '上午上班时间不能为空',
        'worktime_e_am.require' => '上午下班时间不能为空',
        'worktime_s_pm.require' => '下午上班时间不能为空',
        'worktime_e_pm.require' => '下午下班时间不能为空',
        'introduce.require' => '中心介绍不能为空',
    ];


}