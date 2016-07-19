<?php
class XBController extends XBControllerBase
{
    public $theme                = 'default';          //主题方案
    public $skin                 = 'default';          //风格方案
    public $layout               = 'main';             //布局文件
    public $extend               = '.html';            //模板扩展名

    protected $checkRight        = array();            //需要校验的action动作
    protected $lang              = 'zh_sc';            //语言包方案
    protected $module            = null;               //隶属于模块的对象
    protected $ctrlId            = null;               //控制器ID标识符
    protected $title             = null;               //控制器标题

    protected $defaultViewPath   = 'views';            //默认视图目录
    protected $defaultLayoutPath = 'layouts';          //默认布局目录
    protected $defaultLangPath   = 'language';         //默认语言目录
    protected $defaultSkinPath   = 'skin';             //默认皮肤目录
    protected $defaultExecuteExt = '.php';             //默认编译后文件扩展名

    private $action;                                   //当前action对象
    private $CURD = array('del','add','update','get'); //系统CURD
    private $defaultAction = 'index';                  //默认执行的action动作
    private $renderData    = array();                  //渲染的数据

    public function __construct($module,$controllerId){
        $this->module = $module;
        $this->ctrlId = $controllerId;
        //初始化theme方案
        if(isset($this->module->config['theme']) && $this->module->config['theme'] != null){
            $this->theme = $this->module->config['theme'];
        }
        //初始化skin方案
        if(isset($this->module->config['skin']) && $this->module->config['skin'] != null){
            $this->skin = $this->module->config['skin'];
        }
        //初始化lang方案
        if(isset($this->module->config['lang']) && $this->module->config['lang'] != null){
            $this->lang = $this->module->config['lang'];
        }
    }
    public function checkRight($ownRight){
        $actionId = BFilter::safedir(BReq::get('action'));

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
    public function getId(){
        return $this->ctrlId;
    }
    public function init(){

    }
    public function filters(){
        return array();
    }
    public function getAction(){
        return $this->action;
    }
    public function setAction($actionObj){
        $this->action = $actionObj;
    }
    public function run(){
        //开启缓冲区
        ob_start();
        ob_implicit_flush(false);

        header("content-type:text/html;charset=".$this->module->getCharset());
        //初始化控制器
        $this->init();

        //创建action对象
        BInterceptor::run("onCreateAction");
        $actionObj = $this->createAction();
        $actionObj->run();
        BInterceptor::run("onFinishAction");
        flush();
        Core::$app->end(0);
    }
    public function createAction(){
        //获取action的标识符
        $actionId = BUrl::getInfo('action');

        //设置默认的action动作
        if($actionId===null) $actionId = $this->defaultAction;

        /*创建action对象流程
         *1,控制器内部动作
         *2,CURD系统动作
         *3,配置动作
         *4,视图动作*/

        //1,控制器内部动作
        if(method_exists($this,$actionId))
            $this->action = new IInlineAction($this,$actionId);

        //2,CURD系统动作
        else if(method_exists($this,'curd') && in_array($actionId,$this->CURD))
            $this->action = new ICURDAction($this,$actionId);

        //3,配置动作
        else if(($actions = $this->actions()) && isset($actions[$actionId]))
        {
            //自定义类名
            $className = $actions[$actionId]['class'];
            $this->action = new $className($this,$actionId);
        }

        //4,视图动作
        else
            $this->action = new IViewAction($this,$actionId);

        return $this->action;
    }

    /**
     * @brief 预定义的action动作
     * @return array 动作信息
     */
    public function actions()
    {
        return array();
    }

    /**
     * @brief 渲染
     * @param string $view 要渲染的视图文件
     * @param string or array 要渲染的数据
     * @param bool $return 渲染类型
     * @return 渲染出来的数据
     */
    public function render($view,$data=null,$return=false)
    {
        $output = $this->renderView($view,$data);
        if($return)
            return $output;
        else
            echo $output;
    }

    /**
     * @brief 渲染出静态文字
     * @param string $text 要渲染的静态数据
     * @param bool $return 输出方式 值: true:返回; false:直接输出;
     * @return string 静态数据
     */
    public function renderText($text,$return=false)
    {
        $text = $this->tagResolve($text);
        if($return)
            return $text;
        else
            echo $text;
    }

    /**
     * @brief 获取当前主题下的视图路径
     * @return string 视图路径
     */
    public function getViewPath()
    {
        if(!isset($this->_viewPath))
        {
            $viewPath        = isset($this->module->config['viewPath']) ? $this->module->config['viewPath'] : $this->defaultViewPath;
            $this->_viewPath = $this->module->getBasePath().$viewPath.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR;
        }
        return $this->_viewPath;
    }

    /**
     * @brief 获取当前主题下的皮肤路径
     * @return string 皮肤路径
     */
    public function getSkinPath()
    {
        if(!isset($this->_skinPath))
        {
            $skinPath        = isset($this->module->config['skinPath']) ? $this->module->config['skinPath'] : $this->defaultSkinPath;
            $this->_skinPath = $this->getViewPath().$skinPath.DIRECTORY_SEPARATOR.$this->skin.DIRECTORY_SEPARATOR;
        }
        return $this->_skinPath;
    }

    /**
     * @brief 获取当前语言包方案的路径
     * @return string 语言包路径
     */
    public function getLangPath()
    {
        if(!isset($this->_langPath))
        {
            $langPath        = isset($this->module->config['langPath']) ? $this->module->config['langPath'] : $this->defaultLangPath;
            $this->_langPath = $this->module->getBasePath().$langPath.DIRECTORY_SEPARATOR.$this->lang.DIRECTORY_SEPARATOR;
        }
        return $this->_langPath;
    }

    /**
     * @brief 获取layout文件路径(无扩展名)
     * @return string layout路径
     */
    public function getLayoutFile()
    {
        if($this->layout == null)
            return false;

        return $this->getViewPath().$this->defaultLayoutPath.DIRECTORY_SEPARATOR.$this->layout;
    }

    /**
     * @brief 取得视图文件路径(无扩展名)
     * @param string $viewName 视图文件名
     * @return string 视图文件路径
     */
    public function getViewFile($viewName)
    {
        $path = $this->getViewPath().strtolower($this->ctrlId).DIRECTORY_SEPARATOR.$viewName;
        return $path;
    }

    /**
     * @brief 设置页面标题
     * @param string $value 标题值
     */
    public function setTitle($value)
    {
        $this->title = $value;
    }

    /**
     * @brief 获取页面标题
     * @return string 页面标题
     */
    public function getTitle()
    {
        if($this->title !==null)
        {
            return $this->title;
        }
        else
        {
            return $this->ctrlId;
        }
    }

    /**
     * @brief 获取要渲染的数据
     * @return array 渲染的数据
     */
    public function getRenderData()
    {
        return $this->renderData;
    }

    /**
     * @brief 设置要渲染的数据
     * @param array $data 渲染的数据数组
     */
    public function setRenderData($data)
    {
        if(is_array($data))
            $this->renderData = array_merge($this->renderData,$data);
    }

    /**
     * @brief 视图重定位
     * @param string $next     下一步要执行的动作或者路径名,注:当首字符为'/'时，则支持跨控制器操作
     * @param bool   $location 是否重定位 true:是 false:否
     * @param mix $data 传入的数据
     * @param mix $cacheId 开启缓存，缓存的id
     * @param int $cacheTime 缓存时间
     */
    public function redirect($nextUrl, $location = true, $data = null,$cacheId=0,$cacheTime=0)
    {
        //获取当前的action动作
        $actionId = IReq::get('action')!=null?IFilter::safedir(IReq::get('action')):null;
        if($actionId === null)
        {
            $actionId = $this->defaultAction;
        }

        //分析$nextAction 支持跨控制器跳转
        $nextUrl = strtr($nextUrl,'\\','/');

        if($nextUrl[0] != '/')
        {
            //重定跳转定向
            if($actionId!=$nextUrl && $location == true)
            {
                $locationUrl = IUrl::creatUrl('/'.$this->ctrlId.'/'.$nextUrl);
                header('location: '.$locationUrl);
                IWeb::$app->end(0);
            }
            //非重定向
            else
            {
                if(!empty($cacheId)){
                    $mec=new IMemCache();
                    $record=$mec->get($cacheId);
                    if(empty($record)){
                        ob_start();
                        $this->action = new IViewAction($this,$nextUrl);
                        $this->action->run();
                        $record=ob_get_contents();
                        $mec->set($cacheId,$record);
                    }else{
                        print $record;
                    }
                }else{
                    $this->action = new IViewAction($this,$nextUrl);
                    $this->action->run();
                }
            }
        }
        else
        {
            $urlArray   = explode('/',$nextUrl,4);
            $ctrlId     = isset($urlArray[1]) ? $urlArray[1] : '';
            $nextAction = isset($urlArray[2]) ? $urlArray[2] : '';

            //重定跳转定向
            if($location == true)
            {
                //url参数
                if(isset($urlArray[3]))
                {
                    $nextAction .= '/'.$urlArray[3];
                }
                $locationUrl = IUrl::creatUrl('/'.$ctrlId.'/'.$nextAction);
                header('location: '.$locationUrl);
                IWeb::$app->end(0);
            }
            //非重定向
            else
            {
                $nextCtrlObj = new $ctrlId($this->module,$ctrlId);

                //跨控制器渲染数据
                if($data != null)
                {
                    $nextCtrlObj->setRenderData($data);
                }
                $nextCtrlObj->init();

                if(!empty($cacheId)){
                    $mec=new IMemCache();
                    $record=$mec->get($cacheId);
                    if(empty($record)){
                        ob_start();
                        $nextViewObj = new IViewAction($nextCtrlObj,$nextAction);
                        $nextViewObj->run();
                        $record=ob_get_contents();
                        $mec->set($cacheId,$record);
                    }
                }else{
                    $nextViewObj = new IViewAction($nextCtrlObj,$nextAction);
                    $nextViewObj->run();
                }


            }
        }
    }
}