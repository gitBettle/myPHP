<?php
namespace core\base;
use core\helper;
class MController extends MControllerBase{
    public $theme                = 'default';          //主题方案
    public $layout               = 'main';             //布局文件
    public $extend               = '.html';            //模板扩展名

    protected $checkRight        = array();            //需要校验的action动作
    protected $lang              = 'zh_sc';            //语言包方案
    protected $app            = null;               //隶属于模块的对象
    protected $ctrlId            = null;               //控制器ID标识符

    protected $defaultViewPath   = 'views';            //默认视图目录
    protected $defaultLayoutPath = 'layouts';          //默认布局目录
    protected $defaultLangPath   = 'language';         //默认语言目录
    protected $defaultSkinPath   = 'skin';             //默认css目录
    protected $defaultExecuteExt = '.php';             //默认编译后文件扩展名

    private $action;                                   //当前action对象
    private $CURD = array('del','add','update','get'); //系统CURD
    private $defaultAction = 'index';                  //默认执行的action动作
    private  $render    = null;                  //渲染器
    private $messTpl='message';//信息提示模板

    public function __construct($application,$controllerId){
        $this->app = $application;
        $this->ctrlId = $controllerId;
        //初始化theme方案
        if(isset($this->app->config['theme']) && $this->app->config['theme'] != null){
            $this->theme = $this->app->config['theme'];
        }
    }
    public function getModule(){
        return $this->app;
    }
    public function getViewPath(){
        return $this->defaultViewPath;
    }
    public function getLayoutPath(){
        return $this->defaultLayoutPath;
    }
    public function getLangPath(){
        return $this->defaultLangPath;
    }
    public function getSkinPath(){
        return $this->defaultSkinPath;
    }
    public function getExecuteExt(){
        return $this->defaultExecuteExt;
    }
    public function getId(){
        return $this->ctrlId;
    }
    public function setAction($actionObj){
        $this->action = $actionObj;
    }
    public function getAction(){
        return $this->action;
    }
    public function checkRight($ownRight){
        $actionId = helper\MFilter::safedir(helper\MReq::get('action'));

        //是否需要权限校验 true:需要; false:不需要
        $isCheckRight = false;
        if($this->checkRight == 'all'){
            $isCheckRight = true;
        }else if(is_array($this->checkRight)){
            if(isset($this->checkRight['check']) && ( ($this->checkRight['check'] == 'all') || ( is_array($this->checkRight['check']) && in_array($actionId,$this->checkRight['check']) ) ) ){
                $isCheckRight = true;
            }

            if(isset($this->checkRight['uncheck']) && is_array($this->checkRight['uncheck']) && in_array($actionId,$this->checkRight['uncheck'])){
                $isCheckRight = false;
            }
        }

        //需要校验权限
        if($isCheckRight == true){
            $rightCode = $this->ctrlId.'@'.$actionId; //拼接的权限校验码
            $ownRight  = ','.trim($ownRight,',').',';

            if(stripos($ownRight,','.$rightCode.',') === false)
                return false;
            else
                return true;
        }
        else
            return true;
    }
    public function setRender($render){
        $this->render=$render;
    }
    public function initAction(){

    }
    public function filters(){
        return array();
    }
    /**
     * 
     * @param unknown $param
     * @param string $type string|int[|abs]|float[|abs|format]
     * @param string $default
     */
    public function post($param=null,$type='string',$default=''){
        if($param==null)return $_POST;
        $param=helper\MReq::get($param,'post');
        return $this->handle_params($param,$type,$default);
    }
    public function get($param=null,$type='string',$default=''){
        if($param==null)return $_GET;
        $param=helper\MReq::get($param,'get');
        return $this->handle_params($param,$type,$default);
    }
    //get|post
    public function args($param,$type='string',$default=''){
        $param=helper\MReq::get($param);
        return $this->handle_params($param,$type,$default);
    }
    public function prompt($mess='',$while=true){
        if($while){
            while(!preg_match('/^[a-zA-Z]+?$/',$tableName)){
                echo $mess;
                $tableName = trim(fgets(STDIN));
            }
        }else{
            echo $mess;
            $tableName = trim(fgets(STDIN));
            !preg_match('/^[a-zA-Z]+?$/',$tableName)&&$tableName='';
        }
        return $tableName;
    }
    /**
     * 
     * @param unknown $param
     * @param string $type|abs|format
     * @param string $default
     * @return Ambigous <string, number>
     */
    private function handle_params($param,$type='string',$default=''){
        if(empty($param)||is_array($param))return $param;
        if(!is_array($param))$param=trim($param);
        $typeArr=explode('|',$type);
        switch($typeArr[0]){
            case 'int':
                $param=intval($param);
                break;
            case 'float':
                $param=floatval($param);
                if(!empty($typeArr[2])&&$typeArr[2]=='format')$param=number_format($param,2, '.','');
                break;
        }
        if(!empty($typeArr[1]))$param=call_user_func($typeArr[1],$param);
        return !empty($param)?$param:$default;
    }
    public function run(){
        
        header('content-type:text/html;charset='.$this->app->getConfig('charset'));
        //初始化控制器
        $this->initAction();
        //创建action对象
        helper\MInterceptor::run('onCreateAction',$this);
        if(IS_CLI===true)$actionObj = self::createAction();
        else $actionObj = $this->createAction();
        $actionObj->run();
        helper\MInterceptor::run('onFinishAction',$this);
        \core\My::$app->end(0);
    }
    public function createAction(){
        //获取action的标识符
        $actionId = helper\MUrl::getInfo('action');
        //设置默认的action动作
        if(!$actionId) $actionId = $this->defaultAction;
        if(method_exists($this,$actionId.'Action')){
            if(class_exists('Action'))$this->action = new \app\core\base\Action($this,$actionId);
            else $this->action = new MAction($this,$actionId);
        }else{
            throw new \core\exception\MException('action not exists!');exit;
        }
        //elseif(($actions = $this->actions()) && isset($actions[$actionId])){
            //自定义类名
            //$className = $actions[$actionId]['class'];
            //$this->action = new $className($this,$actionId);
        //}else{
            //视图
            //$this->action = new MViewAction($this,$actionId);
       //}
        return $this->action;
    }
    public function actions(){
        return array();
    }
    public function ruleGet($rule=array()){
        if(!empty($rule)&&is_array($rule)){
            //['myName','string|((int|abs|format)|float)','default_value']
            foreach ($rule as $v){
                $_GET[$v[0]]=$this->get($v[0],$v[1],$v[2]);
            }//end foreach
        }//end $rule
        return $_GET;
    }
    public function rulePost($rule=array()){
        if(!empty($rule)&&is_array($rule)){
            //['myName','string|((int|abs|format)|float)','default_value']
            foreach ($rule as $v){
               $_POST[$v[0]]=$this->post($v[0],$v[1],$v[2]); 
            }//end foreach
        }//end $rule
        return $_POST;
    }
    /**
     * //直接跳转
     * @param array()/string $arr
     * @param string $append
     */
    public function goUrl($url='',$querySting=array(),$append=false){
        header('Location:'.helper\MUrl::build($url,$querySting,$append));
        exit(0);
    }
    //设置信息提示模板
    public function setMessageTpl($tpl){
        $this->messTpl=$tpl;
    }
    //[[array(),append,'text']]
    public function redirect($mess=array(),$goUrl='goback',$append=false,$rword='返回'){
        $errorType=1;
        $go_Url=[];
        if($goUrl=='goback'){
            $errorType=2;
            $go_Url[0]=['text'=>$rword,'url'=>'javascript:window.history.back();'];
        }elseif(!empty($goUrl)&&is_array($goUrl)){
           $go_Url[0]=['text'=>$rword,'url'=>helper\MUrl::build($goUrl[0],$goUrl[1]!==null?$goUrl[1]:'',$goUrl[2]!==null?$goUrl[2]:false)];
        }
        $this->render->redirect($mess,$go_Url,$this->messTpl);
    }
    public function ajax_redirect($mess){
        $this->render->ajax_redirect($mess);
    }
    //render
    public function setCache($cacheId=null,$cacheTime=600,$cache=true){
        $this->render->setCache($cacheId,$cacheTime,$cache);
    }
    public function getRenderCache($tpl=null){
        $this->render->setTemplate($tpl);
        return $this->render->getCache();
    }
    public function render($tpl=null){
        $this->render->setTemplate($tpl);
        $this->render->display();
    }
    //clear render cache
    public function clearCache($cacheId=null){
        $this->render->clearCache($cacheId);
    }
    public function clearCacheAll(){
        $this->render->clearCacheAll();
    }

}?>