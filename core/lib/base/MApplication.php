<?php
namespace core\base;
use core\helper;
use app\core\base\Controller;
class MApplication extends MApplicationBase{
    private $defaultController = 'site';
    protected  $argv=array();
    public  $controller;
    public function execRequest(){
        helper\MUrl::beginUrl($this->argv);
        helper\MInterceptor::run('onCreateController',$this);
        $this->controller =  $this->createController();
        $this->controller->run();
        helper\MInterceptor::run('onFinishController',$this);
    }
    public function createController(){
        $controller = helper\MUrl::getInfo('controller');
        if(!$controller) $controller = $this->defaultController;
        $uController=ucfirst($controller);
        $module=helper\MUrl::getInfo('module');
        //\core\My::Controller($uController,$module,false);
        $module=!empty($module)?'\\'.str_replace('_','\\',helper\MFilter::safeCls($module)):'';
        $controllerClassName='\\app\\controller'.$module.'\\'.$uController.'Controller';
        $myControllerClassName=str_replace('\\app\\', '\\my\\', $controllerClassName);
        if(class_exists($controllerClassName))$controllerClass = new $controllerClassName($this,$controller);
        elseif(class_exists($myControllerClassName))$controllerClass = new $myControllerClassName($this,$controller);
        elseif(class_exists('Controller')) $controllerClass = new Controller($this,$controller);
        else $controllerClass = new \core\base\MController($this,$controller);
        $this->controller = $controllerClass;
        return $controllerClass;
    }
    public function getController(){
        return $this->controller;
    }
    public function end($str){
        exit($str);
    }
}