<?php
namespace core\base;
class MView implements MViewBase{
 private $tpl;//模板
    private $ctlr;
    private $app;
    private $cache=false,$cacheTime=0,$cacheId=null;
    const SUFFIX='.html';
    public function __construct($ctlr){
        $this->ctlr=$ctlr;
        $this->app=$this->ctlr->getModule();
    }
    public function display(){
        $path=$this->app->getBasePath();
        $fpath=$path.'/'.$this->ctlr->getViewPath().'/'.$this->ctlr->theme.'/'.$this->tpl.$this->ctlr->getExecuteExt();
        $fLayout='';
        if(!empty($this->ctlr->layout)){
            $fLayout=$path.'/'.$this->ctlr->getViewPath().'/'.$this->ctlr->theme.'/'.$this->ctlr->getLayoutPath().'/'.$this->ctlr->layout.$this->ctlr->getExecuteExt();
        }
        

        if(file_exists($fpath)){
            extract($this->ctlr->getProperty(),EXTR_OVERWRITE);
            $myCssPath=$this->app->getCssPath();
            $this->header();
            include($fpath);
            if(!empty($this->ctlr->layout)){
                if(file_exists($fLayout)){
                    $content=ob_get_contents();
                    ob_clean();
                    include($fLayout);
                }
                //                     else{
                //                         throw new MException($fLayout.' not exist!');
                //                         exit;
                //                     }
            }
            if($this->cache===true){
                $cachePathFile=$this->app->getCacheTplPath().'/'.$this->cacheId{0}.'/'.$this->cacheId{1}.'/'.$this->cacheId.self::SUFFIX;
                $filedata=ob_get_contents();
                $date=SYS_TIME+$this->cacheTime;//缓存时间
                $Cache=MCache::factory();
                $Cache->writeTemplateFile($this->cacheId,$cachePathFile,$date,$filedata);
            }
            $this->footer();
        }else{
            throw new \core\exception\MException($fpath.' not exist!');
            exit;
        }//end file_exists
    }
    /**
     * 获取模板缓存
     */
    public function getCache(){
        if($this->cache===true){
            $path=$this->app->getBasePath();
            $fpath=$path.'/'.$this->ctlr->getViewPath().'/'.$this->ctlr->theme.'/'.$this->tpl.$this->ctlr->getExecuteExt();
            $fLayout='';
            if(!empty($this->ctlr->layout)){
                $fLayout=$path.'/'.$this->ctlr->getViewPath().'/'.$this->ctlr->theme.'/'.$this->ctlr->getLayoutPath().'/'.$this->ctlr->layout.$this->ctlr->getExecuteExt();
            }
            $cachePathFile=$this->app->getCacheTplPath().'/'.$this->cacheId{0}.'/'.$this->cacheId{1}.'/'.$this->cacheId.self::SUFFIX;
            $cache=MCache::factory();
            $ct=$cache->getTemplateCache($this->cacheId,$cachePathFile,$fpath,$fLayout);
            if(!empty($ct)){
                echo '<!--<hit cache>-->';
                echo $ct;
                exit;
            }
        }//end $ct
        return false;
    }
    public function setTemplate($tpl=null){
        if(!empty($tpl)){
            $this->tpl=\core\helper\MFilter::safedir($tpl);
        }else{
            $this->tpl=$this->ctlr->getId().'/'.$this->ctlr->getAction()->getId();
        }
    }
    public function setCache($cacheId=null,$cacheTime=0,$cache=true){
        $cacheId==null?$this->ctlr->getId().'-'.$this->ctlr->getAction()->getId():$cacheId;
        $this->cacheId=md5($cacheId);
        $this->cache=$cache;
        $this->cacheTime=$cacheTime;
    }
    public function header(){
        ob_start();
        ob_implicit_flush(false);
    }
    public function footer(){
        flush();
    }
    public function redirect($mess=array(),$goUrl=array(),$tpl='message'){
        $path=$this->app->getBasePath().'/'.$this->ctlr->getViewPath().'/'.$this->ctlr->theme.'/'.$this->ctlr->getLayoutPath().'/';
        $this->header();
        include_once $path.$tpl.$this->ctlr->getExecuteExt();
        if(!empty($this->ctlr->layout)){
            $fLayout=$path.$this->ctlr->layout.$this->ctlr->getExecuteExt();
            if(file_exists($fLayout)){
                $content=ob_get_contents();
                ob_clean();
                include_once $fLayout;
            }
        }
        $this->footer();
        exit(0);
    }
    public function ajax_redirect($mess){
        echo json_encode($mess);
        exit(0);
    }
    //删除缓存
    public function clearCache($cacheId=null){
        $cacheId==null?$this->ctlr->getId().'-'.$this->ctlr->getAction()->getId():$cacheId;
        $cacheId=md5($cacheId);
        $cache=MCache::factory();
        return $cache->clearCache($cacheId,$this->app->getCacheTplPath().'/'.$cacheId{0}.'/'.$cacheId{1}.'/'.$cacheId.self::SUFFIX);
    }
    public function clearCacheAll(){
        $cache=MCache::factory();
        return $cache->clearCacheAll($this->app->getCacheTplPath());
    }
}