<?php 
namespace app\guide\model;

use think\Model;

class Projects extends Model{

	// 设置当前模型对应的完整数据表名称
  	protected $table = 'Projects';
  	// 设置当前模型的数据库连接
    protected $connection = [
        // 数据库类型
        'type'        => 'sqlsrv',
        // 服务器地址
        'hostname'    => '10.150.5.13',
        // 数据库名
        'database'    => 'egh',
        // 数据库用户名
        'username'    => 'egh',
          // 端口
        // 数据库密码
        'password'    => 'egh',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        // 'prefix'      => '',
        // 数据库调试模式
        'debug'       => true,
        // 连接dsn,驱动、服务器地址和端口、数据库名称
        'dsn'         => 'odbc:Driver={SQL Server};Server=10.150.5.13;Database=egh',
        // 
        
    ];

}
