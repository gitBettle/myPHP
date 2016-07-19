<?php
namespace core\base;
use core\My;
use core\helper;
use core\exception\MException;
abstract class MApplicationBase{
    private $config=array();
    public function __construct($argv=''){
        $this->argv=$argv;
        define('SYS_TIME',time());
        define('DATE',date('Y-m-d',SYS_TIME));
        define('TIME',date('H:i:s',SYS_TIME));
        define('DATE_TIME',DATE.' '.TIME);
        foreach(array('common','dbase','route') as $v){
            if(file_exists(CORE_PATH.'/config/'.$v.My::SUFFIX)){
                $config=include CORE_PATH.'/config/'.$v.My::SUFFIX;
                $this->config=array_merge($this->config,$config);
            }
            if(file_exists(APP_PATH.'/config/'.$v.My::SUFFIX)){
                $config=include APP_PATH.'/config/'.$v.My::SUFFIX;
                $this->config=array_merge($this->config,$config);
            }
        }//end foreach
        if(!empty($this->config['forbit_ip'])){
             
            if(strpos(';'.str_replace("\n", '',$this->config['forbit_ip']).';', ';'.helper\MClient::getIp().';')!==false){
                exit('IP地址被管理员禁用');
            }
             
        }
        if(isset($this->config['timezone'])){
            date_default_timezone_set($this->config['timezone']);
        }
        //错误处理方法
        $debugMode = (isset($this->config['debug']) && $this->config['debug'] === true ) ? true : false;
        $this->setDebugMode($debugMode);
        //自动加载类
        My::setAutoClassFiles($this->config['auto_load_files']);
        My::setAutoClassDir($this->config['auto_load_dir']);
        //添加魔术方法
        $this->addMagicQuotes();
        My::set($this->config);
        //开始向拦截器里注册类
        if( isset($this->config['interceptor']) && is_array($this->config['interceptor']) ){
            helper\MInterceptor::reg($this->config['interceptor']);
            register_shutdown_function( array('Interceptor','shutDown') );
        }
    }
    private function addMagicQuotes(){
        if(!get_magic_quotes_gpc()){
            $_POST   = helper\MFilter::addSlash($_POST);
            $_GET    = helper\MFilter::addSlash($_GET);
            $_COOKIE = helper\MFilter::addSlash($_COOKIE);
        }
    }
    private function setDebugMode($flag){
        if(function_exists('ini_set'))ini_set('display_errors',$flag?'On':'Off');
        if( $flag === true){
            error_reporting(E_ALL | E_STRICT);
            MException::setDebugMode(true);
        }else{
            error_reporting(0);
            MException::setDebugMode(false);
        }
        set_error_handler("\\core\\exception\\MException::myErrorHandler",E_ALL|E_STRICT);
        set_exception_handler("\\core\\exception\\MException::exceptionHandler");
        helper\MFile::setLogPath($this->getCacheLogPath());//设置日志缓存目录
        //MException::setLogPath($this->getCacheLogPath().'/log');
    }
    public function getCssPath(){
        $scriptName=trim(dirname($_SERVER['SCRIPT_NAME']),'/');
        return !empty($scriptName)?'/'.$scriptName.'/':'';
    }
    public function getBasePath(){
        return APP_PATH;
    }
    //数据库版本目录
    public function getMyDbPath(){
        return $this->getBasePath().'/console/myDb';
    }
    //知道生成控制器目录
    public function getControllerPath(){
        return $this->getBasePath().'/controller';
    }
    //自动生成数据模型目录
    public function getModelPath(){
        return $this->getBasePath().'/model';
    }
    //自动生成模板目录
    public function getViewPath(){
        return $this->getBasePath().'/views';
    }
    //缓存目录
    public function getCachePath(){
        return $this->getBasePath().($this->config['cachePath']?$this->config['cachePath']:'/cache');
    }
    //日志缓存目录
    public function getCacheLogPath(){
        return $this->getCachePath().($this->config['cacheLogPath']?$this->config['cacheLogPath']:'/errorLog');
    }
    //页面模板缓存目录
    public function getCacheTplPath(){
        return $this->getCachePath().($this->config['cacheTplPath']?$this->config['cacheTplPath']:'/template');
    }
    public function getConfig($key,$default=''){
        return $this->config[$key]?$this->config[$key]:$default;
    }
    abstract public function execRequest();
    public function run(){
        helper\MInterceptor::run('onCreateApp',$this);
        $this->execRequest();
        helper\MInterceptor::run('onFinishApp',$this);
    }
}