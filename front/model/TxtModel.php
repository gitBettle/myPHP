<?php
namespace app\model;
use app\core\db\Dao;
class TxtModel extends Dao{

    public function __construct($tb='',$link='localhost'){

        parent::__construct('txt',$link);

        $this->setPriKey('id');

    }

    public function rule(){

        return array (
  0 => 
  array (
    0 => 'uId',
    1 => 'require',
    2 => 'uId为必填',
  ),
  1 => 
  array (
    0 => 'title',
    1 => 'require',
    2 => 'title为必填',
  ),
  2 => 
  array (
    0 => 'isDelivery',
    1 => 'require',
    2 => 'isDelivery为必填',
  ),
  3 => 
  array (
    0 => 'isDelivery',
    1 => 'in',
    2 => 'isDelivery值错误',
    3 => 
    array (
      0 => '1',
      1 => '0',
    ),
  ),
  4 => 
  array (
    0 => 'isPay',
    1 => 'require',
    2 => 'isPay为必填',
  ),
  5 => 
  array (
    0 => 'isPay',
    1 => 'in',
    2 => 'isPay值错误',
    3 => 
    array (
      0 => '1',
      1 => '-1',
      2 => '0',
    ),
  ),
  6 => 
  array (
    0 => 'role_id',
    1 => 'require',
    2 => 'role_id为必填',
  ),
  7 => 
  array (
    0 => 'push_music',
    1 => 'require',
    2 => 'push_music为必填',
  ),
  8 => 
  array (
    0 => 'push_music_enable',
    1 => 'in',
    2 => 'push_music_enable值错误',
    3 => 
    array (
      0 => '1',
      1 => '0',
    ),
  ),
  9 => 
  array (
    0 => 'isBaimaShop',
    1 => 'require',
    2 => 'isBaimaShop为必填',
  ),
  10 => 
  array (
    0 => 'isBaimaRecommand',
    1 => 'require',
    2 => 'isBaimaRecommand为必填',
  ),
  11 => 
  array (
    0 => 'level',
    1 => 'require',
    2 => 'level为必填',
  ),
  12 => 
  array (
    0 => 'follower',
    1 => 'require',
    2 => 'follower为必填',
  ),
  13 => 
  array (
    0 => 'printerCode',
    1 => 'require',
    2 => 'printerCode为必填',
  ),
  14 => 
  array (
    0 => 'distributed',
    1 => 'in',
    2 => 'distributed值错误',
    3 => 
    array (
      0 => 'nopass',
      1 => 'pass',
      2 => 'applying',
      3 => 'noapply',
    ),
  ),
);

    }

    //['myName','string|((int|abs|format)|float)','default_value']

    public function getPostRule(){

        return array (
  0 => 
  array (
    0 => 'id',
    1 => 'int|abs',
    2 => 0,
  ),
  1 => 
  array (
    0 => 'uId',
    1 => 'int|abs',
    2 => 0,
  ),
  2 => 
  array (
    0 => 'role_id',
    1 => 'int|abs',
    2 => 0,
  ),
  3 => 
  array (
    0 => 'push_music_enable',
    1 => 'string',
    2 => '1',
  ),
  4 => 
  array (
    0 => 'isBaimaShop',
    1 => 'int',
    2 => 0,
  ),
  5 => 
  array (
    0 => 'isBaimaRecommand',
    1 => 'int',
    2 => 0,
  ),
  6 => 
  array (
    0 => 'level',
    1 => 'int',
    2 => 0,
  ),
  7 => 
  array (
    0 => 'distributed',
    1 => 'string',
    2 => 'noapply',
  ),
);

    }

    //字段

    public function getFields(){

        return array (
  'id' => 
  array (
    'Field' => 'id',
    'Type' => 'int(10) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  'uId' => 
  array (
    'Field' => 'uId',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0',
    'Extra' => '',
  ),
  'title' => 
  array (
    'Field' => 'title',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'appId' => 
  array (
    'Field' => 'appId',
    'Type' => 'varchar(255)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'appSecret' => 
  array (
    'Field' => 'appSecret',
    'Type' => 'varchar(255)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'createTime' => 
  array (
    'Field' => 'createTime',
    'Type' => 'datetime',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'remark' => 
  array (
    'Field' => 'remark',
    'Type' => 'varchar(255)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  'mchid' => 
  array (
    'Field' => 'mchid',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'keys' => 
  array (
    'Field' => 'keys',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'isDelivery' => 
  array (
    'Field' => 'isDelivery',
    'Type' => 'enum(\'1\',\'0\')',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  'isPay' => 
  array (
    'Field' => 'isPay',
    'Type' => 'enum(\'1\',\'-1\',\'0\')',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  'qcode' => 
  array (
    'Field' => 'qcode',
    'Type' => 'char(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'role_id' => 
  array (
    'Field' => 'role_id',
    'Type' => 'smallint(4) unsigned',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  'openId' => 
  array (
    'Field' => 'openId',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'push_music' => 
  array (
    'Field' => 'push_music',
    'Type' => 'varchar(30)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  'push_music_enable' => 
  array (
    'Field' => 'push_music_enable',
    'Type' => 'enum(\'1\',\'0\')',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '1',
    'Extra' => '',
  ),
  'isBaimaShop' => 
  array (
    'Field' => 'isBaimaShop',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  'isBaimaRecommand' => 
  array (
    'Field' => 'isBaimaRecommand',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  'level' => 
  array (
    'Field' => 'level',
    'Type' => 'smallint(6)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  'follower' => 
  array (
    'Field' => 'follower',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  'printerCode' => 
  array (
    'Field' => 'printerCode',
    'Type' => 'varchar(8)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  'distributed' => 
  array (
    'Field' => 'distributed',
    'Type' => 'enum(\'nopass\',\'pass\',\'applying\',\'noapply\')',
    'Null' => 'YES',
    'Key' => '',
    'Default' => 'noapply',
    'Extra' => '',
  ),
);

    }

}