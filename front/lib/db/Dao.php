<?php
namespace app\core\db;
class Dao extends \core\db\MDao{
    public function __construct($tb='',$link='localhost'){
        parent::__construct($link);
        if(!empty($tb))$this->setTableName($tb);
    }
}