<?php
namespace core\base\driver;
use core\My;
class SessMemcache extends \core\base\MSessHandleBase{
    private $lifetime;
    private $mmc = null;
    private $group = null;
    /**
     * 构造函数
     *
     */
    public function __construct($group='sess_') {
        parent::__construct();
        $this->lifetime =My::get('session_ttl',3600);
        $this->mmc=MMCache::getInstant($group,My::get('sess_memcache_server','','192.168.1.4'));
        session_set_save_handler(array(&$this,'open'), array(&$this,'close'), array(&$this,'read'), array(&$this,'write'), array(&$this,'destroy'), array(&$this,'gc'));
        session_start($group);
    }
    static function getInstant($group='sess_'){

        return new self($group);

    }
    /**
     * session_set_save_handler  open方法
     * @param $save_path
     * @param $session_name
     * @return true
     */
    public function open($save_path, $session_name) {

        return true;
    }
    /**
     * session_set_save_handler  close方法
     * @return bool
     */
    public function close() {
    }
    /**
     * 读取session_id
     * session_set_save_handler  read方法
     * @return string 读取session_id
     */
    public function read($id) {
        return $this->mmc->get($id);
    }
    /**
     * 写入session_id 的值
     *
     * @param $id session
     * @param $data 值
     * @return mixed query 执行结果
     */
    public function write($id, $data) {
        return $this->mmc->set($id,$data,$this->lifetime);
    }
    /**
     * 删除指定的session_id
     *
     * @param $id session
     * @return bool
     */
    public function destroy($id) {
        return $this->mmc->delete($id);
    }
    /**
     * 删除过期的 session
     *
     * @param $maxlifetime 存活期时间
     * @return bool
     */
    public function gc($maxlifetime) {
    }
}