<?php
namespace core\base;
use core\My;
abstract class MSessHandleBase{
    private $preFix;
    public function __construct(){
        session_name('PHPSESSID');
        ini_set('session.use_cookies', 1);
        session_set_cookie_params(0,'/',My::get('cookie_domain'));
        $this->preFix=My::get('sess_prefix');//session前缀
    }
    public function get($str){
        return $_SESSION[$this->preFix.$str]!==null?$_SESSION[$this->preFix.$str]:'';
    }
    public function set($str,$value=''){
        $_SESSION[$this->preFix.$str]=$value;
    }
    public function delete($str){
        unset($_SESSION[$this->preFix.$str]);
    }
    public function flush(){
        session_destroy();
    }
}