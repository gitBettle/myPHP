<?php
namespace core\db;
class MyDbInt{
    protected $tableName,$obj;
    protected $engine='ENGINE=InnoDB DEFAULT CHARSET=utf8';
    public function __construct(){
        $this->obj=\core\My::model('InitDb');
    }
    public function up(){
        $this->obj->start();
        if(!$this->setUp()){
            $this->obj->rollback();
        }
        $this->obj->commit();
        echo 'exec success!';
    }
    public function down(){
        $this->obj->start();
        if(!$this->setDown()){
            $this->obj->rollback();
        }
        $this->obj->commit();  
        echo 'exec success!';
    }
    protected function setUp(){
        return true;
    }
    protected function setDown(){
        return true;
    }
    protected function createTable($sql_tmp){
        $sql='CREATE TABLE `'.$this->obj->getDbPrefix().$this->tableName.'`('.$sql_tmp.')'.$this->engine;
        if(!$this->obj->query($sql)){
            echo 'create table `'.$this->tableName.'` error'.PHP_EOL;
            return false;
        }else{
            //get_called_class()
            $this->obj->version=get_class($this);
            $this->obj->addTime=DATE_TIME;
            $this->obj->action='up';
            if(!$this->obj->save()){
                echo 'add table `'.$this->tableName.'` log error'.PHP_EOL;
                return false;
            }
            if($this->obj->del('`version`="'.$this->obj->version.'" AND `action`="down"')===false){
                echo 'delete  `'.$this->obj->version.'` up error'.PHP_EOL;
                return false;
            }
            return true;
        }
    }
    protected function dropTable(){
        $sql='DROP TABLE '.$this->obj->getDbPrefix().$this->tableName;
        if(!$this->obj->query($sql)){
            echo 'delete table `'.$this->tableName.'` error'.PHP_EOL;
            return false;
        }else{
            $this->obj->version=get_class($this);
            $this->obj->addTime=DATE_TIME;
            $this->obj->action='down';
            if(!$this->obj->save()){
                echo 'add table `'.$this->tableName.'` log error'.PHP_EOL;
                return false;
            }
            if($this->obj->del('`version`="'.$this->obj->version.'" AND `action`="up"')===false){
                echo 'delete  `'.$this->obj->version.'` down error'.PHP_EOL;
                return false;
            }
            return true;
        }
    }
}