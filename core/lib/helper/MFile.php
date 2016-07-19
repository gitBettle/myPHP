<?php
namespace core\helper;
class MFile{
    private static $logPath;
    public static function rm($fileName){
        if(file_exists($fileName))return unlink($fileName);
        else return false;
    }
    //删除文件夹下的所有文件
    public static function rmDirFiles($dirName){
        self::rmDir($dirName,false);
    }
    //删除文件夹下的所有文件和文件夹
    public static function rmDir($dirName,$delDir=true){
         if(!is_dir($dirName))return false; 
         $fArr = scandir($dirName); 
         foreach($fArr as $file){ 
            if($file != '.' && $file != '..'){ 
                $dir = $dirName.'/'.$file; 
                is_dir($dir)?self::rmDir($dir,$delDir):@unlink($dir); 
            } 
         } 
         if($delDir)rmdir($dirName);
    }
    public static function mkDir($dir){
        return mkdir($dir,0755,true);
    }
    public static function read($filename){
        $r='';
        if(file_exists($filename))$r=file_get_contents($filename);
        return $r;
    }
    public static function readDir($dirName,&$fileList){
        if(!is_dir($dirName))return false;
        $fArr = scandir($dirName);
        foreach($fArr as $file){
            if($file != '.' && $file != '..'){
                $dir = $dirName.'/'.$file;
                is_dir($dir)?self::readDir($dir,$fileList):$fileList[$file]=$dir;
            }
        }
    }
    public static function setLogPath($logPath){
        self::$logPath=$logPath;
    }
    public static function writeLog($data,$name='log'){
        self::writeFile($data,$name,self::$logPath);
    }
    public static function writeCache($data,$name,$path=''){
        self::writeFile($data,$name,$path,false);
    }
    private static function writeFile($data,$name='log',$path='',$append=true){
        $path=!empty($path)?$path.'/':'';
        $name=$path.$name.($append?DATE:'');
        try{
            $path=dirname($name);
            if(!is_dir($path))self::mkDir($path);
            $handle=fopen($name,$append?'a+':'w+');
            fwrite($handle,$data);
            fclose($handle);
        }catch (MException $e){
            throw new MException($name.' not write privilege!');
            exit;
        }
    }
}