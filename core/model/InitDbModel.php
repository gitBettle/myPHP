<?php
namespace my\model;
use core\db\MModel;
class InitDbModel extends MModel{
    public function __construct($tb='',$link='localhost'){
        parent::__construct('init_db',$link);
    }
    
}