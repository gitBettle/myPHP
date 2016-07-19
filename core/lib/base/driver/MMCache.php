<?php
namespace core\base\driver;
use core\My;
class MMCache implements \core\base\MCacheBase{   
        private $mmc = null,$pre='';
        private function __construct($pre='',$hosts){
            $this->pre=$pre;
            //        if(!class_exists('mmcache')){
            //            $this->mmc = false;
            //            return;
            //        }
            //     	$servers=array(
            //         	//array('192.168.1.2',11211),
            //         	array('192.168.1.3',11211),
            //         	//array('192.168.1.4',11211),
            //         	array('192.168.1.5',11211),
            //         	//array('192.168.1.6',11211),
            //         	//array('192.168.1.7',11211),
            //         );
            //        $this->mmc = new Memcached(MCACHE_TAG);//长链接标示
            //        $this->mmc->setOption(Memcached::OPT_DISTRIBUTION,Memcached::DISTRIBUTION_CONSISTENT);
            // 	   $this->mmc->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE,true);
            //        if (!count($this->mmc->getServerList())) {
            //     		$this->mmc->addServers($servers);
            // 		}
        
            $this->mmc = new \Memcached();
            if(is_string($hosts)){
                $this->mmc->addServer($hosts, 11222);
            }elseif(is_array($hosts)){//[['192.168.1.4','11211'],['192.168.1.3',11211]]
                foreach ($hosts as $v){
                    $this->mmc->addServer($v[0], $v[1]);
                }
            }
        
        }
        public static function getInstant($pre='',$hosts=''){
            if(empty($hosts)){
                $hosts=My::get('cache_memcache_server','','192.168.1.4');
            }
            return new self($pre,$hosts);
        
        }
        function set($key, $var, $expire=3600){
            if(!$this->mmc)return;
            return $this->mmc->set($this->pre.$key, $var, $expire);
        }
        function get($key){
            if(!$this->mmc)return;
            return $this->mmc->get($this->pre.$key);
        }
        function delete($key){
            if(!$this->mmc)return;
            return $this->mmc->delete($this->pre.$key);
        }
        function flush(){
            if(!$this->mmc)return;
            return $this->mmc->flush();
        }
        //文件模板
        public function getTemplateCache($cacheId,$cachePathFile,$fpath,$fLayout=''){
            $value=$this->get($cacheId);
            return !empty($value)?$value:false;
        }
        public static function writeTemplateFile($cacheId,$name,$date,$prefix,$data){
            $this->set($cacheId,$data,$date);
        }
        public function clearCache($cacheId, $path){
            return $this->delete($cacheId);
        }
        public function clearCacheAll($dir){
            return $this->flush();
        }

}