<?php
namespace core\base;
use core\My;
//缓存处理
class MCache{
    private static $instant;
    public static function factory($prefix=''){
        if(!self::$instant){
            $sessHandle=My::get('cache_handle','File');//M=(Memcache)|File
            $className='M'.$sessHandle.'Cache';
            My::Lib($className,'base/driver',0);
            //存储session值得缓存前缀
            $className='\\core\\base\\driver\\'.$className;
            self::$instant=call_user_func(array($className,'getInstant'),array($prefix?$prefix:My::get('cache_prefix','','cache_')));
        }
        return self::$instant;
    }
}