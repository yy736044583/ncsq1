<?php 
namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'username'  =>  'require|max:25',
        'userpass' =>  'require|min:6',
        'phone'     => 'require|length:11',
    ];

    protected $message = [
    	'username.require' => '用户名不能为空',
    	'username.max' => '用户名不能超过10位',
    	'userpass.require' => '密码不能为空',
    	'userpass.min' => '密码不能小于6位',
        'phone.require' => '电话不能为空',
        'phone.length' => '电话号码必须为11位',
    ];

    //设置场景验证
    protected $scene = [
        'up'  =>  ['phone','password'],
    ];
}