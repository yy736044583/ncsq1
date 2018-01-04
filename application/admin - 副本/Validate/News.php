<?php 
namespace app\admin\validate;

use think\Validate;

class News extends Validate
{
    protected $rule = [
        'title'  =>  'require|max:100',
        'level'     => 'require',
        'content'   => 'require',
    ];

    protected $message = [
    	'title.require' => '标题不能为空',
    	'title.max' => '标题不能超过100位',
    	'level.require' => '部门/中心不能为空',
        'content.require' => '内容不能为空',
    ];

    //设置场景验证
    // protected $scene = [
    //     'up'  =>  ['phone','password'],
    // ];
}