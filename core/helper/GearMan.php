<?php
namespace my\helper;
class GearMan{
    private $client=null;
    private static $obj=array();
    private function __construct($host='127.0.0.1',$port=4730){
        if(DOMAIN=='http://wx.51afa.com/'){
            $host='192.168.1.24';
            $port=4770;
        }
        $this->client = new \GearmanClient();
        $this->client->addServer($host, $port);
    }
    public static function single($key='127.0.0.1',$port=4730){
        if(empty(self::$obj[$key])){
            self::$obj[$key]=new self($key,$port);
        }
        return self::$obj[$key];
    }
    public function callBack($classname,$method){//回调函数
        //emthod = completeCallBack
        $this->client->setCompleteCallback(call_user_func(array($classname, $method)));//先绑定才有效
    }
    public function asyncMethod($method,$params=''){//异步
        return $this->client->doBackground($method, $params);
    }
    public function syncMethod($method,$params=''){//同步
        return $this->client->do($method, $params);
    }
    public function syncTask($method,$params=''){//同步队列
        return $this->client->addTask($method, $params);
    }
    public function asyncTask($method,$params=''){//异步队列
        return $this->client->addTaskBackground($method,$params);
    }
    public function runTasks(){//运行队列中的任务，只是do系列不需要runTask
        $this->client->runTasks();
    }
    //绑定回调函数，只对addTask有效
    //     function completeCallBack($task)
    //     {
    //         echo 'CompleteCallback！handle result:'.$task->data().'<br/>';
    //     }
}
?>