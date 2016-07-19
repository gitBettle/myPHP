<?php
namespace core\db;
use core\My;
class MDb{
    private static $_instatnt=array();
    private function __construct(){
    }
    public static function getInstant($key){
        if(empty(self::$_instatnt[$key])){
            self::$_instatnt[$key]=new driver\MysqlClass(My::get('dbase.'.$key));
        }
        return self::$_instatnt[$key];
    }
}//end db