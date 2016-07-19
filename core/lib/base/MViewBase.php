<?php
namespace core\base;
interface MViewBase{
     public function display();
     public function getCache();
     public function setTemplate($tpl=null);
     public function setCache($cacheId=null,$cacheTime=0,$cache=true);
     public function clearCache($cacheId=null);
     public function clearCacheAll();
}