<?php
namespace core\base;
interface MCacheBase{
    public function set($key,$value,$expire);
    public function get($key);
    public function delete($key);
    public function flush();
    
    public function getTemplateCache($cacheId,$cachePathFile,$fpath,$fLayout='');
    public function writeTemplateFile($cacheId,$cachePathFile,$date,$filedata);
    public function clearCache($cacheId,$path);
    public function clearCacheAll($dir);
}