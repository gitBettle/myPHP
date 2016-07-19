<?php
namespace core\base;
class MAction{
	protected $id;
	protected $controller;

	function __construct($controller, $id){
		$this->controller = $controller;
		$this->id = $id;
	}
	function getController(){
		return $this->controller;
	}
	function getId(){
		return $this->id;
	}
    public function run(){
        $controller=$this->getController();
		$methodName=$this->getId().'Action';
		$controller->$methodName();
    }
}