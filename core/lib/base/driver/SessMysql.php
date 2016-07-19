<?php
namespace core\base\driver;
use core\My;
class SessMysql extends \core\base\MSessHandleBase{
    var $lifetime = 1800;
    var $db;
    var $table;
    /**
     * 构造函数
     *
     */
    public function __construct($group='') {
        parent::__construct();
        $this->db = My::model('session');
        $this->lifetime =My::get('session_ttl');
        session_set_save_handler(array(&$this,'open'), array(&$this,'close'), array(&$this,'read'), array(&$this,'write'), array(&$this,'destroy'), array(&$this,'gc'));
        session_start();
    }
    public static function getInstant($group=''){

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
        return $this->gc($this->lifetime);
    }
    /**
     * 读取session_id
     * session_set_save_handler  read方法
     * @return string 读取session_id
     */
    public function read($id) {
        $r = $this->db->find($id, 'data');
        return $r ? $r['data'] : '';
    }
    /**
     * 写入session_id 的值
     *
     * @param $id session
     * @param $data 值
     * @return mixed query 执行结果
     */
    public function write($id, $data) {
        if(strlen($data) > 255) $data = '';
        $ip = \core\helper\MClient::getIp();
        $sessiondata = array(
            'sessionid'=>$id,
            'ip'=>$ip,
            'lastvisit'=>SYS_TIME,
            'data'=>$data,
        );
        return $this->db->edit($sessiondata,$id,1);
    }
    /**
     * 删除指定的session_id
     *
     * @param $id session
     * @return bool
     */
    public function destroy($id) {
        return $this->db->del($id);
    }
    /**
     * 删除过期的 session
     *
     * @param $maxlifetime 存活期时间
     * @return bool
     */
    public function gc($maxlifetime) {
        $expiretime = SYS_TIME - $maxlifetime;
        return $this->db->del("`lastvisit`<$expiretime");
    }
}