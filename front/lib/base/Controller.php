<?php
namespace app\core\base;
use core\base;
class Controller extends base\MController{
    public function __construct($module, $controllerId){
        parent::__construct($module, $controllerId);
        $this->setRender(new View($this));
    }
}