<?php
namespace core\base\driver;
class SessFile extends \core\base\MSessHandleBase{

    public function __construct($group='sess_'){
        parent::__construct();
        ini_set('session.save_path','/tmp/');
        session_start();
    }
    public static function getInstant($group='sess_'){

        return new self($group);

    }

}