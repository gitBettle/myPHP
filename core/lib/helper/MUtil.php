<?php
namespace core\helper;
class MUtil{
    /**
     * 转换数组
     * @param array $arr 转换的数组
     * @param string $ke 关键字
     * @param string $ve 值
     * @param int $multi =(0|1) 是否使用多层数组
     * @return array
     */
    public static function changeArray($arr=array(),$ke,$ve=NULL,$multi=0){
        $newArr=array();
        foreach($arr as $v){
            if($multi==1)$newArr[$v[$ke]][]=is_null($ve)?$v:$v[$ve];
            else $newArr[$v[$ke]]=is_null($ve)?$v:$v[$ve];
    
        }
        return $newArr;
    
    }
    public static function init_redis($host='192.168.1.4',$port='6379'){
        if(self::$redis==null){
            self::$redis = new \Redis();
            self::$redis->connect($host,$port);
        }
        return self::$redis;
    }
}//end func class