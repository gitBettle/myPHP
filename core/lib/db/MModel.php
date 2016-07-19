<?php
namespace core\db;
class MModel extends MDao{
    public function __construct($tb,$link='localhost'){
        parent::__construct($link);
        $this->setTableName($tb);
    }
}//end model