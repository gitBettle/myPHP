<?php
class XBController extends XBControllerBase
{
    public $theme                = 'default';          //���ⷽ��
    public $skin                 = 'default';          //��񷽰�
    public $layout               = 'main';             //�����ļ�
    public $extend               = '.html';            //ģ����չ��

    protected $checkRight        = array();            //��ҪУ���action����
    protected $lang              = 'zh_sc';            //���԰�����
    protected $module            = null;               //������ģ��Ķ���
    protected $ctrlId            = null;               //������ID��ʶ��
    protected $title             = null;               //����������

    protected $defaultViewPath   = 'views';            //Ĭ����ͼĿ¼
    protected $defaultLayoutPath = 'layouts';          //Ĭ�ϲ���Ŀ¼
    protected $defaultLangPath   = 'language';         //Ĭ������Ŀ¼
    protected $defaultSkinPath   = 'skin';             //Ĭ��Ƥ��Ŀ¼
    protected $defaultExecuteExt = '.php';             //Ĭ�ϱ�����ļ���չ��

    private $action;                                   //��ǰaction����
    private $CURD = array('del','add','update','get'); //ϵͳCURD
    private $defaultAction = 'index';                  //Ĭ��ִ�е�action����
    private $renderData    = array();                  //��Ⱦ������

    public function __construct($module,$controllerId){
        $this->module = $module;
        $this->ctrlId = $controllerId;
        //��ʼ��theme����
        if(isset($this->module->config['theme']) && $this->module->config['theme'] != null){
            $this->theme = $this->module->config['theme'];
        }
        //��ʼ��skin����
        if(isset($this->module->config['skin']) && $this->module->config['skin'] != null){
            $this->skin = $this->module->config['skin'];
        }
        //��ʼ��lang����
        if(isset($this->module->config['lang']) && $this->module->config['lang'] != null){
            $this->lang = $this->module->config['lang'];
        }
    }
    public function checkRight($ownRight){
        $actionId = BFilter::safedir(BReq::get('action'));

        //�Ƿ���ҪȨ��У�� true:��Ҫ; false:����Ҫ
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

        //��ҪУ��Ȩ��
        if($isCheckRight == true){
            $rightCode = $this->ctrlId.'@'.$actionId; //ƴ�ӵ�Ȩ��У����
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
        //����������
        ob_start();
        ob_implicit_flush(false);

        header("content-type:text/html;charset=".$this->module->getCharset());
        //��ʼ��������
        $this->init();

        //����action����
        BInterceptor::run("onCreateAction");
        $actionObj = $this->createAction();
        $actionObj->run();
        BInterceptor::run("onFinishAction");
        flush();
        Core::$app->end(0);
    }
    public function createAction(){
        //��ȡaction�ı�ʶ��
        $actionId = BUrl::getInfo('action');

        //����Ĭ�ϵ�action����
        if($actionId===null) $actionId = $this->defaultAction;

        /*����action��������
         *1,�������ڲ�����
         *2,CURDϵͳ����
         *3,���ö���
         *4,��ͼ����*/

        //1,�������ڲ�����
        if(method_exists($this,$actionId))
            $this->action = new IInlineAction($this,$actionId);

        //2,CURDϵͳ����
        else if(method_exists($this,'curd') && in_array($actionId,$this->CURD))
            $this->action = new ICURDAction($this,$actionId);

        //3,���ö���
        else if(($actions = $this->actions()) && isset($actions[$actionId]))
        {
            //�Զ�������
            $className = $actions[$actionId]['class'];
            $this->action = new $className($this,$actionId);
        }

        //4,��ͼ����
        else
            $this->action = new IViewAction($this,$actionId);

        return $this->action;
    }

    /**
     * @brief Ԥ�����action����
     * @return array ������Ϣ
     */
    public function actions()
    {
        return array();
    }

    /**
     * @brief ��Ⱦ
     * @param string $view Ҫ��Ⱦ����ͼ�ļ�
     * @param string or array Ҫ��Ⱦ������
     * @param bool $return ��Ⱦ����
     * @return ��Ⱦ����������
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
     * @brief ��Ⱦ����̬����
     * @param string $text Ҫ��Ⱦ�ľ�̬����
     * @param bool $return �����ʽ ֵ: true:����; false:ֱ�����;
     * @return string ��̬����
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
     * @brief ��ȡ��ǰ�����µ���ͼ·��
     * @return string ��ͼ·��
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
     * @brief ��ȡ��ǰ�����µ�Ƥ��·��
     * @return string Ƥ��·��
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
     * @brief ��ȡ��ǰ���԰�������·��
     * @return string ���԰�·��
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
     * @brief ��ȡlayout�ļ�·��(����չ��)
     * @return string layout·��
     */
    public function getLayoutFile()
    {
        if($this->layout == null)
            return false;

        return $this->getViewPath().$this->defaultLayoutPath.DIRECTORY_SEPARATOR.$this->layout;
    }

    /**
     * @brief ȡ����ͼ�ļ�·��(����չ��)
     * @param string $viewName ��ͼ�ļ���
     * @return string ��ͼ�ļ�·��
     */
    public function getViewFile($viewName)
    {
        $path = $this->getViewPath().strtolower($this->ctrlId).DIRECTORY_SEPARATOR.$viewName;
        return $path;
    }

    /**
     * @brief ����ҳ�����
     * @param string $value ����ֵ
     */
    public function setTitle($value)
    {
        $this->title = $value;
    }

    /**
     * @brief ��ȡҳ�����
     * @return string ҳ�����
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
     * @brief ��ȡҪ��Ⱦ������
     * @return array ��Ⱦ������
     */
    public function getRenderData()
    {
        return $this->renderData;
    }

    /**
     * @brief ����Ҫ��Ⱦ������
     * @param array $data ��Ⱦ����������
     */
    public function setRenderData($data)
    {
        if(is_array($data))
            $this->renderData = array_merge($this->renderData,$data);
    }

    /**
     * @brief ��ͼ�ض�λ
     * @param string $next     ��һ��Ҫִ�еĶ�������·����,ע:�����ַ�Ϊ'/'ʱ����֧�ֿ����������
     * @param bool   $location �Ƿ��ض�λ true:�� false:��
     * @param mix $data ���������
     * @param mix $cacheId �������棬�����id
     * @param int $cacheTime ����ʱ��
     */
    public function redirect($nextUrl, $location = true, $data = null,$cacheId=0,$cacheTime=0)
    {
        //��ȡ��ǰ��action����
        $actionId = IReq::get('action')!=null?IFilter::safedir(IReq::get('action')):null;
        if($actionId === null)
        {
            $actionId = $this->defaultAction;
        }

        //����$nextAction ֧�ֿ��������ת
        $nextUrl = strtr($nextUrl,'\\','/');

        if($nextUrl[0] != '/')
        {
            //�ض���ת����
            if($actionId!=$nextUrl && $location == true)
            {
                $locationUrl = IUrl::creatUrl('/'.$this->ctrlId.'/'.$nextUrl);
                header('location: '.$locationUrl);
                IWeb::$app->end(0);
            }
            //���ض���
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

            //�ض���ת����
            if($location == true)
            {
                //url����
                if(isset($urlArray[3]))
                {
                    $nextAction .= '/'.$urlArray[3];
                }
                $locationUrl = IUrl::creatUrl('/'.$ctrlId.'/'.$nextAction);
                header('location: '.$locationUrl);
                IWeb::$app->end(0);
            }
            //���ض���
            else
            {
                $nextCtrlObj = new $ctrlId($this->module,$ctrlId);

                //���������Ⱦ����
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