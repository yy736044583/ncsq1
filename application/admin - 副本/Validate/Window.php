<?php 
namespace app\admin\validate;

use think\Validate;

class Window extends Validate
{
    protected $rule = [
        'name'  =>  'require|max:100',
        'sectionid'     => 'require',
        // 'pjdeviceid'   => 'require',
        // 'leddeviceid'   => 'require',
        // 'calldeviceid'   => 'require',
    ];

    protected $message = [
    	'name.require' => '名称不能为空',
    	'name.max' => '名称不能超过100位',
    	'sectionid.require' => '部门不能为空',
        // 'pjdeviceid.require' => '评价设备不能为空',
        // 'leddeviceid.require' => 'led设备不能为空',
        // 'calldeviceid.require' => '呼叫设备不能为空',
    ];

}