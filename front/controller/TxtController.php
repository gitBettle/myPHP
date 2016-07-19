<?php
namespace app\controller;
use app\core\base\Controller;
use app\helper\Paging;
use app\model\TxtModel;
class TxtController extends Controller{

    private $myObj,$myId;

    public function initAction(){

        $this->myObj=new TxtModel();

        $this->myId=$this->get($this->myObj->getPriKey());

    }

    public function indexAction(){

        $result=$this->myObj->order($this->myObj->getPriKey().' DESC')->findPage($this->get('page','int|abs',1),2);

        $this->result=$result['rs'];

        $mPage=new Paging($result['pageList']);

        $this->pageList=$mPage->getRender();

        $this->render();

    }

    public function editAction(){

        if(!empty($this->myId))$this->bind($this->myObj->findObj($this->myId,1));

        $this->render();

    }

    public function saveAction(){
        $this->myObj->bind($this->bind($this->rulePost($this->myObj->getPostRule())));

        $arr=array();

        if(!empty($this->myId))$arr[$this->myObj->getPriKey()]=$this->myId;

        if($this->myObj->save($arr)===false){

            $this->redirect($this->myObj->getError());

        }

        $this->redirect((!empty($this->myId)?'编辑':'添加').'成功',['@/index']);

    }

    public function delAction(){

        $limit=1;
        $ids=$this->post('ids');

        if(!empty($ids)){
            $condiction=$this->myObj->getPriKey()." IN ('".implode("','",$ids)."')";

            $limit=0;
        }else{
            $condiction=$this->myObj->getPriKey().'="'.$this->myId.'"';

        }
        if(!$this->myObj->del($condiction,0,$limit)){

            $this->redirect($this->myObj->getError());

        }

        $this->redirect('删除成功',['@/index']);

    }

}