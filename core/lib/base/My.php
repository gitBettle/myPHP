<?php
namespace core;
class My{
    private static $_cofing=array();
    private static $_includeClass=array();
    public static $app;
    const SUFFIX='.php';
    public static function createApp($className, $config){
        $app = new $className($config);
        return $app;
    }
    public static function createWebApp($config = null){
        self::$app = self::createApp('\core\base\MApplication',$config);
        return self::$app;
    }
    public static function autoLoad($className){
        if(!empty(self::$_includeClass[$className]))return true;
        if(isset(self::$_core[$className])){
            include_once(CORE_PATH.self::$_core[$className]);
            self::$_includeClass[$className]=true;
        }else{
            $clsArr=explode('\\',$className);
            if(count($clsArr)>1&&$clsArr[0]=='my'){
                unset($clsArr[0]);
                $className=implode('/',$clsArr);
                if(file_exists(CORE_PATH.'/'.$className.self::SUFFIX)){
                    include_once(CORE_PATH.'/'.$className.self::SUFFIX);
                    self::$_includeClass[$className]=true;
                }
            }
        }
        return true;
    }
    public static function autoLoadWebApp($className){
        if(!empty(self::$_includeClass[$className]))return true;
        if(isset(self::$_coreWebApp[$className])){
            include_once(APP_PATH.self::$_coreWebApp[$className]);
            self::$_includeClass[$className]=true;
        }else{
            $clsArr=explode('\\',$className);
            if(count($clsArr)>1&&$clsArr[0]=='app'){
                unset($clsArr[0]);
                $classNameA=implode('/',$clsArr);
                if(file_exists(APP_PATH.'/'.$classNameA.self::SUFFIX)){
                    include_once(APP_PATH.'/'.$classNameA.self::SUFFIX);
                    self::$_includeClass[$className]=true;
                    return true;
                }
            }
            if(isset(self::$_selfCoreFile[$className])){//自定义核心文件
                include_once(APP_PATH.'/'.trim(self::$_selfCoreFile[$className],'/'));
                self::$_includeClass[$className]=true;
            }elseif(!empty(self::$_selfCoreDir)){//自定义核心文件夹
                foreach (self::$_selfCoreDir as $va){
                    $va=trim($va,'/');
                    if(file_exists(APP_PATH.'/'.$va.'/'.$className.self::SUFFIX)){
                        include_once(APP_PATH.'/'.$va.'/'.$className.self::SUFFIX);
                        self::$_includeClass[$className]=true;
                    }
                }
            }
            
        }
        return true;
    }
    public static function setAutoClassFiles($arr){
        self::$_selfCoreFile=$arr;
    }
    public static function setAutoClassDir($arr){
        self::$_selfCoreDir=$arr;
    }
    private static $_selfCoreFile=array();
    private static $_selfCoreDir=array();
    private static $_coreWebApp=array(
        'app\\core\\base\\Controller'=>'/lib/base/Controller.php',
        'app\\core\\base\\Action'=>'/lib/base/Action.php',
        'app\\core\\base\\View'=>'/lib/base/View.php',
        'app\\core\\db\\Model'=>'/lib/db/Model.php',
        'app\\core\\db\\Dao'=>'/lib/db/Dao.php',
        'app\\core\\helper\\Valid'=>'lib/helper/Valid.php'
    );
    private static $_core = array(
        'core\\base\\MObject'=>'/lib/base/MObject.php',
        'core\\base\\MApplicationBase'=>'/lib/base/MApplicationBase.php',
        'core\\base\\MApplication'=>'/lib/base/MApplication.php',
        'core\\base\\MControllerBase'=>'/lib/base/MControllerBase.php',
        'core\\base\\MController'=>'/lib/base/MController.php',
        'core\\base\\MAction'=>'/lib/base/MAction.php',
        'core\\base\\MViewBase'=>'/lib/base/MViewBase.php',
        'core\\base\\MView'=>'/lib/base/MView.php',
        'core\\base\\MCache'=>'/lib/base/MCache.php',
        'core\\base\\MSessHandleBase'=>'/lib/base/MSessHandleBase.php',
        'core\\base\\MSess'=>'/lib/base/MSess.php',
        'core\\base\\MCacheBase'=>'/lib/base/MCacheBase.php',
        'core\\db\\MDao'=>'/lib/db/MDao.php',
        'core\\db\\MDb'=>'/lib/db/MDb.php',
        'core\\db\\MModel'=>'/lib/db/MModel.php',
        'core\\db\\MyDbInt'=>'/lib/db/MyDbInt.php',
        'core\\db\\driver\\MysqlClass'=>'/lib/db/driver/MysqlClass.php',
        'core\\exception\\MException'=>'/lib/exception/MException.php',
        'core\\helper\\MHtml'=>'/lib/helper/MHtml.php',
        'core\\helper\\MUtil'=>'/lib/helper/MUtil.php',
        'core\\helper\\MClient'=>'/lib/helper/MClient.php',
        'core\\helper\\MFilter'=>'/lib/helper/MFilter.php',
        'core\\helper\\MInterceptor'=>'/lib/helper/MInterceptor.php',
        'core\\helper\\MValid'=>'/lib/helper/MValid.php',
        'core\\helper\\MReq'=>'/lib/helper/MReq.php',
        'core\\helper\\MTag'=>'/lib/helper/MTag.php',
        'core\\helper\\MUrl'=>'/lib/helper/MUrl.php',
        'core\\helper\\MUtil'=>'/lib/helper/MUtil.php',
        'core\\helper\\MFile'=>'/lib/helper/MFile.php',
    );
    public static function model($class,$dir_path=''){
        return self::_loadClass('/model/',$class,$dir_path,'Model',true);
    }
    public static function helper($class,$dir_path='',$initial=true){
        $r=self::_loadClass('/lib/helper/',$class,$dir_path,'',$initial);
        if(!$r){
            $r=self::_loadClass('/helper/',$class,$dir_path,'',$initial);
        }
        return $r;
    }
//     public static function controller($class,$dir_path='',$initial=true){
//         return self::_loadClass('/controller/',$class,$dir_path,'Controller',$initial);
//     }
    public static function lib($class,$dir_path='',$initial=true){
        return self::_loadClass('/lib/',$class,$dir_path,'',$initial);
    }
    private static function _loadClass($prefix,$class,$dir_path='',$suffix='',$initial=true){
        static $cls=array();
        $namespace='';
        if(!empty($dir_path)){
            $dir_path=trim($dir_path,'/').'/';
        }
        $class=ucfirst($class);
        $key=md5($prefix.$dir_path.$class.$suffix);
        if(empty($cls[$key])){
            $flag=false;
            $className=$class.$suffix;
            if(file_exists(APP_PATH.$prefix.$dir_path.$className.self::SUFFIX)){
                require_once APP_PATH.$prefix.$dir_path.$className.self::SUFFIX;
                $flag=true;
                switch ($prefix){
                    case '/model/':
                        $namespace='\\app\\model\\'.str_replace('/', '\\', $dir_path);
                        break;
                    case '/lib/':
                        $namespace='\\app\\core\\'.str_replace('/', '\\', $dir_path);
                        break;
                    case '/lib/helper/':
                        $namespace='\\app\\core\\helper\\'.str_replace('/', '\\', $dir_path);
                        break;
                }
                $namespace=!empty($namespace)?rtrim($namespace,'\\').'\\':'';
            }elseif(file_exists(CORE_PATH.$prefix.$dir_path.$className.self::SUFFIX)){
                include_once(CORE_PATH.$prefix.$dir_path.$className.self::SUFFIX);
                $flag=true;
                switch ($prefix){
                    case '/model/':
                        $namespace='\\my\\model\\'.str_replace('/', '\\', $dir_path);
                        break;
                    case '/lib/':
                        $namespace='\\core\\'.str_replace('/', '\\', $dir_path);
                        break;
                    case '/lib/helper/':
                        $namespace='\\core\\helper\\'.str_replace('/', '\\', $dir_path);
                        break;
                }
                $namespace=!empty($namespace)?rtrim($namespace,'\\').'\\':'';
            }
            if($flag){
                $ncls=$namespace.$className;
                if($initial){
                    $cls[$key]=new $ncls();
                }
                else $cls[$key]=true;
            }else{
                if($suffix=='Model'){
                    if(class_exists('Model'))$cls[$key]=new \app\core\db\Model($class);
                    else $cls[$key]=new \core\db\MModel($class);
                }else{
                    $cls[$key]=false;
                }
            }
        }
        return $cls[$key];
    }
    public static function set($key,$value=null){
        if(is_array($key)&&$value==null){
            self::$_cofing=$key;
        }elseif(strpos($key,'.')){
            $keyArr=explode('.',$key);
            $ct=count($keyArr);
            if($ct==2)self::$_cofing[$keyArr[0]][$keyArr[1]]=$value;
            elseif($ct==3)self::$_cofing[$keyArr[0]][$keyArr[1]][$keyArr[2]]=$value;
        }else{
            self::$_cofing[$key]=$value;
        }
    }
    public static function get($key,$default=''){
        if(strpos($key,'.')){
            $keyArr=explode('.',$key);
            $ct=count($keyArr);
            if($ct==2)return self::$_cofing[$keyArr[0]][$keyArr[1]]?self::$_cofing[$keyArr[0]][$keyArr[1]]:$default;
            elseif($ct==3)return self::$_cofing[$keyArr[0]][$keyArr[1]][$keyArr[2]]?self::$_cofing[$keyArr[0]][$keyArr[1]][$keyArr[2]]:$default;
            else return $default;
        }else{
            return self::$_cofing[$key]?self::$_cofing[$key]:$default;
        }
    }
}//end class Core
\spl_autoload_register('\\core\\My::autoLoad');
\spl_autoload_register('\\core\\My::autoLoadWebApp');
?>