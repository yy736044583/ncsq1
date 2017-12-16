<?php 
namespace app\admin\validate;

use think\Validate;

class Workman extends Validate
{
    protected $rule = [
        'name'  =>  'require|max:25',
        'number' =>  'require|max:25',
        'phone'     => 'require|length:11',
        'posttitle' =>  'require',
        'sectionid' =>  'require',
       // 'summary' =>  'require',
        'duty' =>  'require',
        'promise' =>  'require',
        'loginname' => 'require',
        'loginpass' => 'require|min:5',
        'business' =>'require',

    ];

    protected $message = [
    	'name.require' => '姓名不能为空',
    	'name.max' => '姓名不能超过25位',
    	'number.require' => '工号不能为空',
    	'number.max' => '工号不能大于25位',
        'phone.require' => '手机不能为空',
        'phone.length' => '手机必须为11位',
        'posttitle.require' => '职称不能为空',
        'sectionid.require' => '部门不能为空',
       // 'summary.require' => '个人简介不能为空',
        'duty.require' => '岗位职责不能为空',
        'promise.require' => '服务承诺不能为空',
        'loginname.require' => '用户名不能为空',
        'loginpass.require' => '密码不能为空',
        'loginpass.min' => '密码不能小于5位',
        'business.require' => '办理事项不能为空',
    ];

    //设置场景验证
    protected $scene = [
        'up'  =>  ['name','number','phone','posttitle','sectionid','summary','duty','promise','loginname'],
    ];
}