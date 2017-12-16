<?php 
namespace app\admin\validate;

use think\Validate;

class Device extends Validate
{
    protected $rule = [
        'number'  =>  'require|max:25',
        'address'     => 'require',
        'windowid'   => 'require',
        'usestatus' =>  'require',
        'summary' =>  'require',
    ];

    protected $message = [
    	'number.require' => '设备编号不能为空',
    	'number.max' => '设备编号不能超过25位',
    	'address.require' => '设备位置不能为空',
        'windowid.require' => '窗口不能为空',
        'usestatus.require' => '使用状态不能为空',
        'summary.require' => '备注不能为空',
    ];

    //设置场景验证
    protected $scene = [
        'take'  =>  ['number','address','usestatus','summary'],
    ];
}