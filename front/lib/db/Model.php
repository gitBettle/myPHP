<?php
namespace app\core\db;
class Model extends Dao{
    public function __construct($tb='',$link='localhost'){
        parent::__construct($link);
        if(!empty($tb))$this->setTableName($tb);
    }
}