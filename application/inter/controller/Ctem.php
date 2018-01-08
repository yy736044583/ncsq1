<?php
namespace app\inter\controller;
use think\Db;
use think\Cache;
use think\Request;  
//清除所有缓存
class Ctem{
	public function index(){
		$action = input('action');
			switch ($action) {
				// 清除缓存
				case 'clearall':
					$this->clearall();
					break;
				default:
					echo json_encode(['data'=>array(),'code'=>'400','message'=>'参数错误']);
					return;
					break;
			}		
	}

	public function clearall(){
		//清除缓存
		Cache::clear();
		//清除数据
		Db::execute("truncate table ph_deviceqid");
        //删除缓存文件
		$path = ROOT_PATH.DS.'public/uploads/tempfile';
		//删除指定路径下的所有文件
        $this->delFileUnderDir($path);
	}

    //循环目录下的所有文件进行删除
    public function delFileUnderDir($dirName){ 
        //打开文件夹
        if ( $handle = opendir( "$dirName" ) ) {
            while ( false !== ( $item = readdir( $handle ) ) ) {
                if ( $item != "." && $item != ".." ) {
                    //如果是文件夹 递归该方法
                    if ( is_dir( "$dirName/$item" ) ) {
                        delFileUnderDir( "$dirName/$item" );
                    } else {
                        $time = time();
                        $filetime = fileatime($dirName.'/'.$item)+60*60;//文件上次打开时间
                        // 删除文件上次打开时间超过半小时的
                        if($time>$filetime){
                            //删除文件
                            unlink( "$dirName/$item" );  
                        }
                    }
                }
            }
         
        //关闭文件夹
        closedir( $handle );
        }
    }
}