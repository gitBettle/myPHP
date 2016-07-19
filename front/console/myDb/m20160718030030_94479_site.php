<?php

namespace app\console\migration;

use core\db\MyDbInt;

class m20160718030030_94479_site extends MyDbInt{

    public function __construct(){

        parent::__construct();

        $this->tableName='site';//表名

    }

    public function setUp(){

        return $this->createTable("
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `sometext` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
");//表字段

    }

    public function setDown(){

        return $this->dropTable();

    }

}