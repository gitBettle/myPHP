<?php
namespace my\helper;
use core\helper\MUrl;
class MPage{
    private $allRecords,$allPage,$curPage,$eachPageRecords;
    public function __construct($arr){
        $this->allRecords=$arr['allRecords'];
        $this->curPage=$arr['curPage'];
        $this->allPage=$arr['allPage'];
        $this->eachPageRecords=$arr['eachPageRecords'];
    }
    public function getRender(){
        $str='';
        $url=MUrl::build('',['page'=>'$i']);
        for($i=1;$i<=$this->allPage;$i++){
            $str.='<a href="'.str_replace('$i',$i,$url).'" '.($this->curPage==$i?'class="on"':'').'>['.$i.']</a>';
        }
        return $str;
    }
}