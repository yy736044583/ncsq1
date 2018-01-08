<?php
namespace app\integration\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;

class Make extends \think\Controller{
	// 选择单位
    public function index(){
        
        return  $this->fetch();
    }
    // 选择事项
    public function matter(){      
        return  $this->fetch();
    }
    // 添加预约
    public function add(){      
        return  $this->fetch();
    }
    // 我的预约
    public function search(){      
        return  $this->fetch();
    }
    // 预约搜索详情
    public function searchshow(){      
        return  $this->fetch();
    }
}