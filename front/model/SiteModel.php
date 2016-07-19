<?php

namespace app\model;

use app\core\db\Dao;
            
class SiteModel extends Dao{

    public function __construct($tb='',$link='localhost'){

        parent::__construct('site',$link);

        $this->setPriKey('id');

    }

    public function rule(){

        return array (
);

    }

    //['myName','string|((int|abs|format)|float)','default_value']

    public function getPostRule(){

        return array (
  0 => 
  array (
    0 => 'id',
    1 => 'int',
    2 => 0,
  ),
  1 => 
  array (
    0 => 'pid',
    1 => 'int',
    2 => 0,
  ),
);

    }

    //字段

    public function getFields(){

        return array (
  'id' => 
  array (
    'Field' => 'id',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  'pid' => 
  array (
    'Field' => 'pid',
    'Type' => 'int(11)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'sometext' => 
  array (
    'Field' => 'sometext',
    'Type' => 'varchar(45)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
);

    }

}