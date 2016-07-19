<?php
namespace core\exception;
class MException extends \Exception{
    private static $debug=false;
    private static $logPath;
    public static function setDebugMode($flag){
        self::$debug=$flag;
    }
//     public static function setLogPath($path){
//         self::$logPath = $path;
//     }
    //异常处理
    //throw new Exception('Uncaught Exception');
    public static function exceptionHandler($e) {
        //if(!($e instanceof MException) && $e instanceof Exception){
            $err='<b>My ERROR</b>:Uncaught exception: '.$e->getMessage().' in line '.$e->getLine().' of '.$e->getFile()."<br/>\n";
            if(self::$debug){
                echo $err;
            }else{
                \core\helper\MFile::writeLog($err);
            }
        //}
    }
    //错误处理
    //trigger_error("Value at position $i is not a number, using 0 (zero)", E_USER_NOTICE);
    public static function myErrorHandler($errno, $errstr, $errfile, $errline){
        $err='';
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_STRICT:
                $err.="<b>My ERROR</b> [$errno] $errstr<br />\n";
                $err.="  Fatal error in line $errline of file $errfile";
                $err.=", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                $err.="Aborting...<br />\n";
                break;
            default:
                //echo "<b>My ERROR</b>:Unkown error type: [$errno] $errstr in line $errline of file $errfile<br />\n";
                break;
        }
        if($err){
            if(self::$debug){
                echo $err;
            }else{
                \core\helper\MFile::writeLog($err);
            }
        }
    }
}//end my_exception