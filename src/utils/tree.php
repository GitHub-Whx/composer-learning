<?php
namespace whx\treeutils;


class Tree{


    public static function test() {
        return "It's working!";
    }

    /**
     * [listToTree 列表生成树结构[父类禁用后，子类不会生成在树里面]]
     * @Author   Whx
     * @DateTime 2020-11-17
     * @param    [type]     $list           [列表数据]
     * @param    integer    $pid            [父级ID]
     * @param    string     $node_name      [子节点名称]
     * @param    string     $pid_name       [父ID名称]
     * @param    string     $self_id_name   [本身ID名称]
     * @return   [type]                     [description]
     */
    public static function listToTree($list, $pid = 0, $node_name = 'child', $pid_name = 'pid', $self_id_name = 'menu_id') {
        $tree = [];
        foreach($list as $k => $v){
            if($v[$pid_name] == $pid) {
                unset($list[$k]); // 减少递归次数
                $v[$node_name] = self::listToTree($list, $v[$self_id_name], $node_name, $pid_name, $self_id_name);
                if (!$v[$node_name]) {
                    unset($v[$node_name]); // 最后一个节点，去除child字段
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * [treeToList 树结构转为列表]
     * @Author   Whx
     * @DateTime 2020-11-17
     * @param    [type]     $tree       [原始树数据]
     * @param    string     $node_name  [子节点名称]
     * @param    array      &$list      [过渡用的中间数组]
     * @return   [type]                 [description]
     */
    public static function treeToList($tree, $node_name = 'child', &$list = []) {
        if(is_array($tree)) {
            foreach ($tree as $k => $v) {
                $reffer = $v;
                if(!empty($reffer[$node_name])){
                    unset($reffer[$node_name]);
                    self::treeToList($v[$node_name], $node_name, $list);
                }
                $list[] = $reffer;
            }
        }
        return $list;
    }

    /**
     * [searchListSon 获取某个节点所有子类【包含本身-非树状结构】]
     * @Author   Whx
     * @DateTime 2020-11-17
     * @param    [type]     $list         [原始列表数据]
     * @param    [type]     $target_id    [目标ID]
     * @param    string     $pid_name     [父ID名称]
     * @param    string     $self_id_name [本身ID名称]
     * @return   [type]                   [description]
     */
    public static function searchListSon($list, $target_id, $pid_name = 'pid',$self_id_name = 'menu_id') {
         $ret = [];
        static $mark = false;
        if($target_id == 0 || !is_array($list) || !$list) {
            return $ret;
        }

        foreach ($list as $k => $v) {

            if ($v[$self_id_name] == $target_id) { // 获取本身数据
                $mark = true;
                $ret[] = $v;
                unset($list[$k]);
                // self::searchListSon($list, $v[$self_id_name], $pid_name, $self_id_name);
                $ret = array_merge($ret,self::searchListSon($list,$v[$self_id_name], $pid_name, $self_id_name));
            }

            if ($mark) {
                if($v[$pid_name] == $target_id){  // 获取子级数据
                    $ret[] = $v;
                    unset($list[$k]);
                    $ret = array_merge($ret,self::searchListSon($list,$v[$self_id_name], $pid_name, $self_id_name));
                }
            }
            
        }

        return $ret;
    }


    /**
     * [searchListSon 获取某个节点所有子类【不包含本身-非树状结构】]
     * @Author   Whx
     * @DateTime 2020-11-17
     * @param    [type]     $list         [原始列表数据]
     * @param    [type]     $target_id    [目标ID]
     * @param    string     $pid_name     [父ID名称]
     * @param    string     $self_id_name [本身ID名称]
     * @return   [type]                   [description]
     */
    public static function searchListSonPlus($list, $target_id, $pid_name = 'pid',$self_id_name = 'menu_id') {
        $ret = [];
        if($target_id == 0 || !is_array($list) || !$list) {
            return $ret;
        }

        foreach ($list as $k => $v) {
            if($v[$pid_name] == $target_id){  // 获取子级数据
                $ret[] = $v;
                unset($list[$k]);
                $ret = array_merge($ret,self::searchListSon($list,$v[$self_id_name], $pid_name, $self_id_name));
            }
            
        }

        return $ret;
    }



    /**
     * [searchListParent 获取某个节点所有父类【包含本身-非树状结构】]
     * @Author   Whx
     * @DateTime 2020-11-17
     * @param    [type]     $list         [原始列表数据]
     * @param    [type]     $target_id    [目标ID]
     * @param    string     $pid_name     [父ID名称]
     * @param    string     $self_id_name [本身ID名称]
     * @return   [type]                   [description]
     */
    public static function searchListParent($list, $target_id, $pid_name = 'pid',$self_id_name = 'menu_id') {
        $ret = [];
        if($target_id == 0 || !is_array($list)) {
            return $ret;
        }

        foreach ($list as $k => $v) {
            if($v[$self_id_name] == $target_id){
                $ret[] = $v;
                $ret = array_merge($ret,self::searchListParent($list, $v[$pid_name], $pid_name, $self_id_name));
            }
        }

        return $ret;

    }



    /**
     * [searchTreeSon 获取某个节点所有子类【包含本身-树状结构】]]
     * @Author   Whx
     * @DateTime 2020-11-17
     * @param    [type]     $tree         [原始树结构数据]
     * @param    [type]     $target_id    [目标ID]
     * @param    string     $node_name    [节点名称]
     * @param    string     $self_id_name [本身ID名称]
     * @return   [type]                   [description]
     */
    public static function searchTreeSon($tree, $target_id, $node_name = 'child', $self_id_name = 'menu_id') {
        static $ret = [];
        foreach ($tree as $k => $v) {
            if ($v[$self_id_name] == $target_id) {
                $ret[] = $v;
            }else{
                if ($v[$node_name]) {
                    self::searchTreeSon($v[$node_name],$target_id, $node_name, $self_id_name);
                }
            }
        }
        return $ret;
    }


    /**
     * [copyTree 复制树结构]
     * @Author   Whx
     * @DateTime 2021-02-05
     * @param    [type]     $model        [模型类，eg:app\modules\common\models\AdminRule]
     * @param    [type]     $tree         [原始树结构]
     * @param    integer    $pid          [父类ID]
     * @param    array      &$pool        [树结构关系池]
     * @param    string     $node_name    [节点名称]
     * @param    string     $pid_name     [父ID名称]
     * @param    string     $self_id_name [本身ID名称]
     * @return   [type]                   [description]
     */
    public static function copyTree($model,$tree, $pid = 0, &$pool = [], $node_name = 'child', $pid_name = 'pid', $self_id_name = 'menu_id') {
        foreach ($tree as $k => $v) {
            $pid = self::getPid($v,$pool,$pid,$pid_name,$self_id_name);
            $menu_id = self::saveData($model,$v,$pid, $node_name, $pid_name, $self_id_name);
            array_push($pool, ['old_'.$self_id_name => $v[$self_id_name],$self_id_name => $menu_id]);
            if (isset($v[$node_name])) {
                self::copyTree($model,$v[$node_name],$pid,$pool);
            }
        }
        return true;
    }



    public static function getPid($source_data,$pool,$pid, $pid_name = 'pid', $self_id_name = 'menu_id') {
        foreach ($pool as $k => $v) {
            if ($source_data[$pid_name] == $v['old_'.$self_id_name]) {
                $pid = $v[$self_id_name ];
                break;
            }
        }
        return $pid;
    }

    public static function saveData($model,$source_data,$pid, $node_name = 'child', $pid_name = 'pid', $self_id_name = 'menu_id') {
        unset($source_data[$node_name]);
        unset($source_data[$self_id_name]);
        $source_data[$pid_name] = $pid;
        $obj = new $model();
        return $obj->add_one($source_data);
    }




}
