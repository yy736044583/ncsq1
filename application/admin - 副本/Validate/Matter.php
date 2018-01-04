<?php 
namespace app\admin\validate;

use think\Validate;

class Matter extends Validate
{
    protected $rule = [
        'name'             =>  'require|max:300',
        'sectionid'         =>  'require', // 部门
        'character'       =>  'require', // 事项类型
        //'time_limit'        =>  'require', // 法定期限
        //'matter_source'     =>  'require', // 事项来源
        //'acceptance_limit'  =>  'require', // 承诺期限
        //'enacting_organ'    =>  'require', // 实施机关
        'phone'        =>  'require', // 咨询电话
        //'duty_organ'        =>  'require', // 责任处（科）室
        // 'complaint_tel'     =>  'require', // 监督投诉电话
        // 'work_object'       =>  'require', // 办事对象
        //'address_time'      =>  'require', // 办理地点、时间
        // 'number'            =>  'require', // 办事者到现场次数
    ];

    protected $message = [
    	'name.require' => '事项不能为空',
    	'name.max' => '标题不能超过300位',
        'sectionid.require' => '部门不能为空',
        'character.require' => '事项类型不能为空',
        //'time_limit.require' => '法定期限不能为空',
        //'matter_source.require' => '事项来源不能为空',
        //'acceptance_limit.require' => '承诺期限不能为空',
        //'enacting_organ.require' => '实施机关不能为空',
        'phone.require' => '咨询电话不能为空',
        //'duty_organ.require' => '责任处（科）室不能为空',
        // 'complaint_tel.require' => '监督投诉电话不能为空',
        // 'work_object.require' => '办事对象不能为空',
        //'address_time.require' => '办理地点、时间不能为空',
        // 'number.require' => '办事者到现场次数不能为空',
    ];

    //设置场景验证
    // protected $scene = [
    //     'up'  =>  ['phone','password'],
    // ];
}