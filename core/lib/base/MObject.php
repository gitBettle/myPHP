<?php
namespace core\base;
class MObject{
    private  $property = array();
    public function __set($name,$value){
        $this->property[$name] = $value;
    }
    public function __get($name){
        if(isset($this->property[$name])){
            return $this->property[$name];
        }else{
            return null;
        }
    }
    public function __call($name,$params){

    }
    public function __isset($name){
        if(isset($this->property[$name])){
            return true;
        }else{
            return false;
        }
    }
    public function __unset($name){
        if(isset($this->property[$name])){
            unset($this->property[$name]);
            return true;
        }
        else
        {
            return false;
        }
    }
    public function getProperty(){
        return $this->property;
    }
    /**
     * 绑定数组或对象
     * @param array/object $arr
     */
    public function bind($arr){
        if(!empty($arr)){
            if(is_array($arr)){
                foreach ($arr as $k=>$v){
                    $this->$k=$v;
                }//end foreach
            }elseif(is_object($arr)&&$arr instanceof MObject){
                $prop=$arr->getProperty();
                if(!empty($prop)){
                    foreach ($prop as $k=>$v){
                        $this->$k=$v;
                    }
                }
            }
            return $this;
        }else{
            return false;
        }//end if
        
    }//end bind
}