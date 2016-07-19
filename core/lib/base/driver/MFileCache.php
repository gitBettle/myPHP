<?php
namespace core\base\driver;
use core\helper\MFile;
use core\My;
class MFileCache implements \core\base\MCacheBase{
    private $path;
    private function __construct(){
        $this->path=trim(My::get('cache_file'),'/');//文件缓存的路径
    }
    public static function getInstant(){
        return new self();
    }
    function set($key,$value,$expire=3600){
        $filename=$this->getFilename($key);
        if($expire>0)$arr['expire']=SYS_TIME+$expire;
        else $arr['expire']=false;
        $arr['tmp']=$value;
        return MFile::writeCache(serialize($arr),$filename);

    }
    function get($key,$default=false){
        $filename=$this->getFilename($key);
        $data=MFile::read($filename);
        if(!$data){
            return $default;
        }else{
            $arr=unserialize($data);
            if($arr['expire']==false||SYS_TIME<=$arr['expire']){
                return $arr['tmp'];
            }else{
                return $default;
            }
        }
    }
    function delete($key){
        $filename=$this->getFilename($key);
        MFile::rm($filename);
    }
    //清空
    function flush(){
        MFile::rmDirFiles($this->path);
    }
    private function getFilename($key){
        $k=md5($key);
        return $this->path.'/'.$k{0}.'/'.$k{1}.'/'.$k;
    }
    //缓存网页模板
    public function writeTemplateFile($cacheId,$name,$date,$data){
        $prefix=$this->crc($data);
        MFile::writeCache($date.'_'.$prefix.$data,$name);
    }
    public function getTemplateCache($cacheId,$cachePathFile,$fpath,$fLayout=''){
        if(!file_exists($cachePathFile))return false;
        $handle=fopen($cachePathFile,'rb');
        $contents = '';
        $ftm=filemtime($cachePathFile);
        if($handle&&(filemtime($fpath)<=$ftm&&(empty($fLayout)||filemtime($fLayout)<=$ftm))){
            $j=0;
            while(!feof($handle)) {
                if($j==0){ 
                    $tmp= fread($handle,43);
                    if(empty($tmp)||strlen($tmp)!=43)break;
                    $tmp_arr=explode('_',$tmp);
                    if(empty($tmp_arr)||count($tmp_arr)!=2)break;
                    if(SYS_TIME>$tmp_arr[0])break;
                }else{
                    $contents.=fread($handle,8192);
                }
                ++$j;
            }//end while
            fclose($handle);
        }//end handle
        if(!empty($contents)){//读缓存
            if($tmp_arr[1]==$this->crc($contents)){
                return $contents;
            }else{
                return false;
            }
        }
        return false;
    }
    public function clearCache($cacheId,$path){
        return MFile::rm($path);
    }
    public function clearCacheAll($dir){
        return MFile::rmDirFiles($dir);
    }
    /**
     * crc32加密，不够32位补齐
     */
    private function crc($str){
        $cnum=sprintf("%u",crc32($str.My::get('auth_key')));
        $len=strlen($cnum);
        for($i=1;$i<=32-$len;$i++)$cnum.=0;
        return $cnum;
    }
}