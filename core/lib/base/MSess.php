<?php
namespace core\base;
use core\My;
class MSess{
    private static $instant;
    public static function factory(){
        if(!self::$instant){
            $sessHandle=My::get('session_handle','File');//Memcache|File|Mysql
            $className='Sess'.$sessHandle;	
            My::Lib($className,'base/driver',0);
            //存储session值得缓存前缀
            $className='\\core\\base\\driver\\'.$className;
            self::$instant=call_user_func(array($className,'getInstant'),array(My::get('sess_cache_prefix','sess_')));
            //var_dump(self::$instant);
        }
        return self::$instant;
    }
}