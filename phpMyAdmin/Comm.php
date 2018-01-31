<?php 

/**
 * [getTree 递归方法实现无限极分类]
 * @param  [type]  $list  [需要分类的数据]
 * @param  integer $pid   [子集pid]
 * @param  integer $level [表示层级]
 * @return [type]         [排序完成后的数据]
 */
function getTree($list,$pid=0,$level=0) {
    //保存处理后的数据
    static $tree = array();
    foreach($list as $row) {
        if($row['pid'] == $pid) {
            $row['level'] = $level;
            $tree[] = $row;
            getTree($list, $row['id'], $level + 1);
        }
    }
    return $tree;
}

function textlength($str,$lentht=50){
        $len = mb_strlen($str,'utf8');
        if($len>=$lentht){
            $str = mb_substr($str,0,$lentht,'utf8').'...';
        }
        return $str;
    