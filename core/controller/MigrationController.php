<?php
namespace my\controller;
use core\helper\MFile;
use core\helper\MFilter;
use core\helper\MHtml;
use core\My;
class MigrationController extends \core\base\MController{
    private $obj,$className,$path;
    public function initAction(){
    }
    //数据库版本创建
    public function createAction() {
        $this->path=$this->app->getMyDbPath();
        $cls=$this->get('cls');
        if(empty($cls)){
           echo 'Error:must input class name！'.PHP_EOL;
           exit(0); 
        }else{
            $className='m'.date('YmdHis')._.rand(10000, 99999).'_'.$cls;
            $tpl="<?php\n
namespace app\\console\\migration;\n
use core\\db\\MyDbInt;\n
class $className extends MyDbInt{\n
    public function __construct(){\n
        parent::__construct();\n
        \$this->tableName='$cls';//表名\n
    }\n
    public function setUp(){\n
        return \$this->createTable(\"...\");//表字段\n
    }\n
    public function setDown(){\n
        return \$this->dropTable();\n
    }\n
}";
            MFile::writeCache($tpl, $className.'.php',$this->path);
            echo 'create myDb class '.$className.' success!';
        }
    }
    //数据库版本上行
    public function upAction(){
        $this->path=$this->app->getMyDbPath();
        $this->handle('up');
    }
    //数据库版本下行
    public function downAction(){
        $this->path=$this->app->getMyDbPath();
        $this->handle('down');
    }
    private function createViewAction($ctrlNamePath,$modelNamePath=''){
        $path=$this->app->getViewPath();
        if(!file_exists($this->app->getControllerPath().'/'.$ctrlNamePath.'Controller.php')){
            exit($ctrlNamePath.' must not be empty!');
        }
        $ctrlNameArr=explode('/',$ctrlNamePath);
        $ctrlName=end($ctrlNameArr);
        $ctrlName{0}=strtolower($ctrlName{0});
        
        $theme=$this->prompt('please input theme:',false);
        empty($theme)&&$theme=$this->theme;
        $path.='/'.$theme;
        
        $usedModel=false;
        $modelClassname=null;
        if(!empty($modelNamePath)){
            $modelNamePath=MFilter::safeCls($modelNamePath);
            $modelNameArr=explode('/',$modelNamePath);
            $modelName=ucfirst(array_pop($modelNameArr));
            $modelNamePath=(!empty($modelNameArr)?implode('/', $modelNameArr).'/':'').$modelName;
            $clsPath=trim(str_replace($modelName,'',$modelNamePath),'/');
            
            //$modelClassname=My::model($modelName,$clsPath);
            $modelName='\\app\\model\\'.(!empty($clsPath)?str_replace('/','\\',MFilter::safedir($clsPath)).'\\':'').$modelName.'Model';
            if(class_exists($modelName)){
                $modelClassname=new $modelName();
                $usedModel=true;
            }else{
                echo 'class model '.$modelName.' not exists!'.PHP_EOL;
            }
        }
        $cssPath=My::$app->getCssPath();
        if($usedModel){
            $fields='';
            $columns=$modelClassname->getColumns();
            $tpl='<?php 
                if($result):?>
                <th><input type="checkbox" id="alls"/></th>
                ';
            foreach ($columns as $k=>$v){
                $tpl.='<th>'.MHtml::strip($k).'</th>';
                $fields.='<td><?=MHtml::strip($v[\''.MHtml::strip($k).'\'])?></td>'."\n";
                if($v['Key']=='PRI'){
                    $myPri=MHtml::strip($k);
                }elseif($v['Extra']!='auto_increment'){
                    $inputText.='<p>';
                    if(strpos($v['Type'],'enum')===false&&strpos($v['Type'],'text')===false){
                        $ttype='text';
                        if(strpos($v['Type'],'int')!==false)$ttype='int';
                        elseif(strpos($v['Type'],'float')!==false||strpos($v['Type'],'decimal')!==false)$ttype='float';
                        elseif(strpos($v['Type'],'datetime')!==false)$ttype='datetime';
                        $inputText.=MHtml::strip($k).':'.MHtml::text(MHtml::strip($k),$ttype,'<?=MHtml::strip($'.MHtml::strip($k).')?>',$v['Null']=='NO'?1:0,strpos($v['Type'],'unsigned')!==false?1:0);
                    }elseif(strpos($v['Type'],'enum')!==false){
                        $Tf=trim(str_replace('enum(','',rtrim($v['Type'],')')),'\'');
                        $arrs=explode("','",$Tf);
                        $inputText.=MHtml::strip($k).':'.MHtml::selectTmp(MHtml::strip($k),$arrs,$v['Null']=='NO'?1:0);
                    }elseif(strpos($v['Type'],'text')!==false){
                        $inputText.=MHtml::strip($k).':'.MHtml::areaText(MHtml::strip($k),'<?=MHtml::strip($'.MHtml::strip($k).')?>',$v['Null']=='NO'?1:0);
                    }
                    $inputText.="</p>\n";
                }
            }
            $tpl='<?php
use core\helper\MHtml;
use core\helper\MUrl;
                ?>
                <div><button id="addRecord">提交</button></div>
                <form action="<?=MUrl::build(\'@/del\')?>" method="post">
                <table with=100% border=1><tr>
                '.$tpl.'<th>操作</th>
                    </tr>
                    <?php foreach ($result as $v):?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?=MHtml::strip($v[\''.$myPri.'\'])?>"/></td>
                        '.$fields.'
                            <td>
                            <a href="<?=MUrl::build(\'@/edit\',[\''.$myPri.'\'=>MHtml::strip($v[\''.$myPri.'\'])])?>">编辑</a>  <a href="<?=MUrl::build(\'@/del\',[\''.$myPri.'\'=>MHtml::strip($v[\''.$myPri.'\'])])?>">删除</a></td></tr>
            <?php endforeach;?>
                    <tr><td colspan="'.(count($columns)+2).'" align="right"><button id="delAll">批量删除</button><?php echo $pageList;?></td></tr>
                    </table></form>
            <?php endif;?>
                        <script language="javascript" src="<?=$myCssPath?>skin/js/jquery.js"></script>
                        <script>var myUrl=\'<?=MUrl::build(\'@/edit\')?>\';</script>
                        <script language="javascript" src="<?=$myCssPath?>skin/js/index.js"></script>';
            
            $tpl2='
                <?php
use core\helper\MHtml;
use core\helper\MUrl;
                ?>
                <form action="<?=MUrl::build(\'@/save\',\'\',true)?>" method="post">'.$inputText;
            $tpl2.='<p><button id="sub">提交</button></p>   
             </form>
              <script language="javascript" src="<?=$myCssPath?>skin/js/jquery.js"></script>
             <script language="javascript" src="<?=$myCssPath?>skin/js/sub.js"></script>';
            if(file_exists($path.'/'.$ctrlName.'/index.php')){
                while(empty($indexFlag)||!in_array($indexFlag,array('yes','no'))){
                    $indexFlag=$this->prompt($ctrlName.'/index.php file was exists,overwrite ?(yes|no):');
                }
                if($indexFlag=='yes')MFile::writeCache($tpl, $ctrlName.'/index.php',$path);
            }else{
                MFile::writeCache($tpl, $ctrlName.'/index.php',$path);
            }
            if(file_exists($path.'/'.$ctrlName.'/edit.php')){
                while(empty($editFlag)||!in_array($editFlag,array('yes','no'))){
                    $editFlag=$this->prompt($ctrlName.'/edit.php file was exists,overwrite ?(yes|no):');
                }
                if($editFlag=='yes')MFile::writeCache($tpl2, $ctrlName.'/edit.php',$path);
            }else{
                MFile::writeCache($tpl2, $ctrlName.'/edit.php',$path);
            }
        }else{
            $tpl='...';
            if(file_exists($path.'/'.$ctrlName.'/index.php')){
                MFile::writeCache($tpl, $ctrlName.'/index.php',$path);
            }else{
                MFile::writeCache($tpl, $ctrlName.'/index.php',$path);
            }
        }
        echo 'create view  success.'.PHP_EOL;
    }
    //创建控制端
    public function createCtrlAction($classNamePath='',$modelNamePath=''){
        $path=$this->app->getControllerPath();
        empty($classNamePath)&&$classNamePath=$this->get('cls');
        empty($classNamePath)&&$classNamePath=$this->prompt('please input controller class name:');
        $classNamePath=MFilter::safeCls($classNamePath);
        $classNameArr=explode('/',$classNamePath);
        $className=ucfirst(array_pop($classNameArr));
        $classNamePath=(!empty($classNameArr)?implode('/', $classNameArr).'/':'').$className;
        $className.='Controller';
        $usedModel=false;
        $defName='';
        if(empty($modelNamePath)){
            
            while(empty($createModelFlag)||!in_array($createModelFlag,array('yes','no'))){
                $createModelFlag=$this->prompt('used model?(yes|no):');
            }
            if($createModelFlag=='yes'){
                $modelNamePath=$this->prompt('please input model class name:');
                $modelNamePath=MFilter::safeCls($modelNamePath);
                $modelNameArr=explode('/',$modelNamePath);
                $modelName=ucfirst(array_pop($modelNameArr));
                $modelNamePath=(!empty($modelNameArr)?implode('/', $modelNameArr).'/':'').$modelName;
                $clsPath=trim(str_replace($modelName,'',$modelNamePath),'/');
                if(!empty($clsPath)){
                    //My::model($modelName,$clsPath);
                    //$tpl_cls='My::model(\''.$modelName.'\',\''.$clsPath.'\');';
                    $defName='\\app\\model\\'.str_replace('/','\\',$clsPath).'\\'.$modelName.'Model';
                }else{
                    $defName='\\app\\model\\'.$modelName.'Model';
                }
                $modelName.='Model';
                if(class_exists($defName)){
                    $usedModel=true;
                }else{
                    echo 'class model '.$defName.' not exists!'.PHP_EOL;
                }
            }
        }else{
            $modelNamePath=MFilter::safeCls($modelNamePath);
            $modelNameArr=explode('/',$modelNamePath);
            $modelName=ucfirst(array_pop($modelNameArr));
            $modelNamePath=(!empty($modelNameArr)?implode('/', $modelNameArr).'/':'').$modelName;
            $clsPath=trim(str_replace($modelName,'',$modelNamePath),'/');
            if(!empty($clsPath)){
                //My::model($modelName,$clsPath);
                //$tpl_cls='My::model(\''.$modelName.'\',\''.$clsPath.'\');';
                $defName='\\app\\model\\'.str_replace('/','\\',$clsPath).'\\'.$modelName.'Model';
            }else{
                $defName='\\app\\model\\'.$modelName.'Model';
            }
            $modelName.='Model';
            if(class_exists($defName)){
                $usedModel=true;
            }else{
                echo 'class model '.$modelName.' not exists!'.PHP_EOL;
            }
        }
        if($usedModel){
            $tpl="<?php\n 
namespace app\\controller;\n
use app\\core\\base\\Controller;\n
use app\\helper\\Paging;\n
use {$defName};\n
class $className extends Controller{\n
    private \$myObj,\$myId;\n
    public function initAction(){\n
        \$this->myObj=new {$modelName}();\n
        \$this->myId=\$this->get(\$this->myObj->getPriKey());\n
    }\n
    public function indexAction(){\n
        \$result=\$this->myObj->order(\$this->myObj->getPriKey().' DESC')->findPage(\$this->get('page','int|abs',1));\n
        \$this->result=\$result['rs'];\n
        \$mPage=new Paging(\$result['pageList']);\n
        \$this->pageList=\$mPage->getRender();\n
        \$this->render();\n
    }\n
    public function editAction(){\n
        if(!empty(\$this->myId))\$this->bind(\$this->myObj->findObj(\$this->myId,1));\n
        \$this->render();\n
    }\n
    public function saveAction(){
        \$this->myObj->bind(\$this->bind(\$this->rulePost(\$this->myObj->getPostRule())));\n
        \$arr=array();\n
        if(!empty(\$this->myId))\$arr[\$this->myObj->getPriKey()]=\$this->myId;\n
        if(\$this->myObj->save(\$arr)===false){\n
            \$this->redirect(\$this->myObj->getError());\n
        }\n
        \$this->redirect((!empty(\$this->myId)?'编辑':'添加').'成功',['@/index']);\n
    }\n
    public function delAction(){\n
        \$limit=1;
        \$ids=\$this->post('ids');\n
        if(!empty(\$ids)){
            \$condiction=\$this->myObj->getPriKey().\" IN ('\".implode(\"','\",\$ids).\"')\";\n
            \$limit=0;
        }else{
            \$condiction=\$this->myObj->getPriKey().'=\"'.\$this->myId.'\"';\n
        }
        if(!\$this->myObj->del(\$condiction,0,\$limit)){\n
            \$this->redirect(\$this->myObj->getError());\n
        }\n
        \$this->redirect('删除成功',['@/index']);\n
    }\n
}";
        }else{
            $tpl="<?php\n
namespace app\\controller;\n
use app\\core\\base\\Controller;\n
class $className extends Controller{\n
    public function initAction(){\n
    }\n
    public function indexAction(){\n
        \$this->render();\n
    }\n
}";            
        }
        $ctrlFile=$classNamePath.'Controller.php';
        if(file_exists($path.'/'.$ctrlFile)){
            while(empty($overCtrlFlag)||!in_array($overCtrlFlag,array('yes','no'))){
                $overCtrlFlag=$this->prompt('controller class '.$className.' was exists, overwrite controller?(yes|no):');
            }
            if($overCtrlFlag=='yes')MFile::writeCache($tpl,$ctrlFile,$path);
        }else{
            MFile::writeCache($tpl,$ctrlFile,$path);
        }
        
      echo 'create controller '.$className.' success.'.PHP_EOL;
      while(empty($createViewFlag)||!in_array($createViewFlag,array('yes','no'))){
          $createViewFlag=$this->prompt('created view?(yes|no):');
      }
      if($createViewFlag=='yes'){
          $this->createViewAction($classNamePath,$modelNamePath);
      }
      
      
    }
    //创建模型端
    public function createMdlAction(){
        $classNamePath=$this->get('cls');
        $path=$this->app->getModelPath();
        //fwrite(STDOUT, 'Enter your name: ');
        empty($classNamePath)&&$classNamePath=$this->prompt('please input model class name:');
        $classNamePath=MFilter::safeCls($classNamePath);
        $classNameArr=explode('/',$classNamePath);
        $className=ucfirst(array_pop($classNameArr));
        $classNamePath=(!empty($classNameArr)?implode('/', $classNameArr).'/':'').$className;
        $className.='Model';
        empty($tableName)&&$tableName=$this->prompt('please input table name:');
        $cls=new \core\db\MModel($tableName);
        $rs=$cls->getColumns();
        if(!empty($rs)){
            $fileds=$rules=$format=array();
            foreach ($rs as $k=>$v){
                if($v['Key']=='PRI')$cls->setPriKey($k);
                if($v['Null']=='NO'&&$v['Extra']!='auto_increment'&&empty($v['Default'])){
                    $rules[]=array($k,'require',$k.'为必填');
                }
                if(strpos($v['Type'],'enum')!==false){
                    $arr=explode("','",trim(rtrim(ltrim($v['Type'],'enum('),')'),"'"));
                    $rules[]=array($k,'in',$k.'值错误',$arr);
                }
                if($v['Key']=='UNI'){//unique
                    $rules[]=array($k,'unique',$k.'已经存在');
                }
                if(strpos($v['Type'],'int')!==false){
                    if(strpos($v['Type'],'unsigned')!==false)$format[]=array($k,'int|abs',$v['Default']?intval($v['Default']):0);
                    else $format[]=array($k,'int',$v['Default']?intval($v['Default']):0);
                }elseif(strpos($v['Type'],'decimal')!==false){
                    if(strpos($v['Type'],'unsigned')!==false)$format[]=array($k,'float|abs|format',$v['Default']?floatval($v['Default']):0.00);
                    else $format[]=array($k,'float|format',$v['Default']?floatval($v['Default']):0.00);
                }else{
                    !empty($v['Default'])&&$format[]=array($k,'string',$v['Default']);
                }
            }
            $tpl="<?php\n
namespace app\\model;\n
use app\\core\\db\\Dao;\n            
class $className extends Dao{\n
    public function __construct(\$tb='',\$link='localhost'){\n
        parent::__construct('$tableName',\$link);\n
        \$this->setPriKey('".$cls->getPriKey()."');\n
    }\n
    public function rule(){\n
        return ".var_export($rules,true).";\n
    }\n
    //['myName','string|((int|abs|format)|float)','default_value']\n
    public function getPostRule(){\n
        return ".var_export($format,true).";\n
    }\n
    //字段\n
    public function getFields(){\n
        return ".var_export($rs,true).";\n
    }\n
}";
            $mdlFile=$classNamePath.'Model.php';
            if(file_exists($path.'/'.$mdlFile)){
                while(empty($overMdlFlag)||!in_array($overMdlFlag,array('yes','no'))){
                    $overMdlFlag=$this->prompt('model class '.$className.' was exists, overwrite model?(yes|no):');
                }
                if($overMdlFlag=='yes')MFile::writeCache($tpl,$mdlFile,$path);
            }else{
                MFile::writeCache($tpl,$mdlFile,$path);
            }
            echo 'create model '.$className.' success.'.PHP_EOL;
            while(empty($createCtrlFlag)||!in_array($createCtrlFlag,array('yes','no'))){
                $createCtrlFlag=$this->prompt('created controller?(yes|no):');
            }
            if($createCtrlFlag=='yes'){
                $this->createCtrlAction($this->prompt('please input controller class name:'),$classNamePath);
            }
            
        }else{
            echo 'get table columns error!'.PHP_EOL;
        }
    }
    private function handle($param='up'){
        $fileList=array();
        MFile::readDir($this->path,$fileList);
        ksort($fileList);
        if(!empty($fileList)){
            $obj=My::model('InitDb');
            $r=\core\helper\MUtil::changeArray($obj->findAll(),'version');
            foreach ($fileList as $k=>$v){
                $ks=str_replace('.php', '', $k);
                //echo $ks;exit;
                if(!empty($r[$ks])&&$r[$ks]['action']==$param)continue;
                include_once $this->path.'/'.$k;
                $ks='\\app\\console\\migration\\'.$ks;
                call_user_func(array(new $ks(),$param));
            }//endforeach $fileList
        }//endif $fileList
    }//end function handle
}