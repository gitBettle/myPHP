<?php
namespace core\db;
use core\My;
use core\base\MCache;
class MDao extends \core\base\MObject{
    const INI='int';
    const FLOAT='float';
    const ENUM='enum';
    const VARCHAR='varchar';
    const TEXT='text';
    const DECIMAL='decimal';
    const DATETIME='datetime';
    
    const MUST=0;//不管是否为空都验证
    const ISVAL=1;//不为空再验证
    
    const ADD=1;
    const UPDATE=2;
    const MULTI=3;
    const MODEL='Model';
    private $db,$wdb;
    protected $tableName;//表名
    protected $priKey;//主键
    private $sqlLists=array(),$cachePrefix='sql_';//记录的sql语句
    private $_errors=array(),$_rules=array();//验证错误信息,验证规则
    private $tmpSql,$mySql,$append=true;
    private $my_condiction=array('{$fields}','{$table}','{$bk}','{$join}','{$where}','{$groupby}','{$having}','{$order}','{$limit}');
    function __construct($links='localhost'){
        if(My::get('dbase.master_slave')===true){
            $this->db=MDb::getInstant($links.'_read');
            $this->wdb=MDb::getInstant($links.'_write');
        }else{
            $this->wdb=$this->db=MDb::getInstant($links.'_read');
        }
        $this->mySql='SELECT '.$this->my_condiction[0].' FROM `'.$this->my_condiction[1].'` '.$this->my_condiction[2].' '.$this->my_condiction[3].' '.$this->my_condiction[4].' '.$this->my_condiction[5].' '.$this->my_condiction[6].' '.$this->my_condiction[7].' '.$this->my_condiction[8];
        $this->tmpSql=$this->mySql;
    }
    //设置表名称
    public function setTableName($tb){
        $this->tableName=$this->db->prefix.$tb;
    }
    public function getDbPrefix(){
        return $this->db->prefix;
    }
    public function getTableName(){
        return $this->tableName;
    }
    //设置关键字
    public function setPriKey($key){
        $this->priKey=$key;
    }
    public function getPriKey(){
        return $this->priKey;
    }
    //获取验证错误信息
    public function getError(){
        return $this->_errors;
    }
    //重置$tmpSql
    private function sqlReset(){
        $this->tmpSql=$this->mySql;
    }
    //获取执行的sql记录
    public function getSqlLists(){
        return $this->sqlLists;
    }
    public function escape($char){
        return $this->db->escape_string($char);
    }
    //ORM start
    public function fields($field='*'){
        $field=$this->get_select_item($field);
        $this->tmpSql=str_replace('{$fields}',$field,$this->tmpSql);
        return $this;
    }
    public function where($condiction='',$usePriKey=0){
        $where=$this->get_where_item($condiction);
        $this->tmpSql=str_replace('{$where}',$where,$this->tmpSql);
        return $this;
    }
    public function groupby($groupby){
        $groupby=empty($groupby)?'':'GROUP BY '.$groupby;
        $this->tmpSql=str_replace('{$groupby}',$groupby,$this->tmpSql);
        return $this;
    }
    public function having($having){
        $having=empty($having)?'':'HAVING '.$having;
        $this->tmpSql=str_replace('{$having}',$having,$this->tmpSql);
        return $this;
    }
    public function order($order){
        $order=$this->get_order_item($order);
        $this->tmpSql=str_replace('{$order}',$order,$this->tmpSql);
        return $this;
    }
    public function limit($limit){
        $limit=$this->get_limit_item($limit);
        $this->tmpSql=str_replace('{$limit}',$limit,$this->tmpSql);
        return $this;
    }
    public function setAlias($alias=''){
        $this->tmpSql=str_replace('{$bk}',$alias,$this->tmpSql);
        return $this;
    }
    /**
     *
     * @param unknown $condiction 链接条件
     * @param unknown $tbl 链接表名称
     * @param string $alias 链接表别名，为空就使用表名
     * @param string $cdt 链接方式join|left join|inner join
     */
    public function join($condiction,$tbl,$alias='',$cdt='JOIN'){
        $newAlias=My::model($tbl);
        $newTable=$newAlias->getTableName();
        $this->tmpSql=str_replace('{$join}',' '.$cdt.' '.$newTable.' AS '.(!empty($alias)?$alias:$tbl).' ON('.$condiction.') {$join} ',$this->tmpSql);
        return $this;
    }
    //counts必须在find或findall前执行
    function counts($id=0,$clear=0){
        $sql=str_replace('{$fields}','COUNT(*) AS cont',$this->tmpSql);
        $sql=str_replace('{$table}',$this->tableName,$sql);
        $sql=str_replace($this->my_condiction,'',$sql);
        $r=$this->db->get_one($sql);
        if($clear)$this->sqlReset();
        if(!empty($r))return $r['cont'];
        else return false;
    }
    /**
     *
     * @param number $id
     * @param number $usePriKey 使用主建为查询条件
     * @param number $addSqlLog 记录sql语句
     */
    public function find($id=0,$usePriKey=0,$cache=0,$cacheTime=600,$addSqlLog=1){
        $sql=str_replace('{$fields}','*',$this->tmpSql);
        $sql=str_replace('{$table}',$this->tableName,$sql);
        if(!empty($id)){
            $where=$this->get_where_item($id,$usePriKey);
            $sql=str_replace('{$where}',$where,$sql);
        }
        $sql=str_replace($this->my_condiction,'',$sql);
        if($addSqlLog)$this->sqlLists[$addSqlLog]=$sql;
        $this->sqlReset();
        if($cache){
            $ch=MCache::factory($this->cachePrefix);
            $r=$ch->get($sql);
            if(empty($r)){
                $r=$this->db->get_one($sql);
                $ch->set($sql,$r,$cacheTime);
            }
        }else{
            $r=$this->db->get_one($sql);
        }
        return $r;
    }
    //返回对象
    public function findObj($id=0,$usePriKey=0,$cache=0,$cacheTime=600,$addSqlLog=1){
        $r=$this->find($id,$usePriKey,$cache,$cacheTime,$addSqlLog);
        if(!empty($r)){
            return $this->bind($r);
        }
        return false;
    }
    public function findAll($id=0,$usePriKey=0,$cache=0,$cacheTime=600,$addSqlLog=1){
        $sql=str_replace('{$fields}','*',$this->tmpSql);
        $sql=str_replace('{$table}',$this->tableName,$sql);
        if(!empty($id)){
            $where=$this->get_where_item($id,$usePriKey);
            $sql=str_replace('{$where}',$where,$sql);
        }
        $sql=str_replace($this->my_condiction,'',$sql);
        if($addSqlLog)$this->sqlLists[$addSqlLog]=$sql;
        $this->sqlReset();
        if($cache){
            $ch=MCache::factory($this->cachePrefix);
            $r=$ch->get($sql);
            if(empty($r)){
                $r=$this->db->get_all($sql);
                $ch->set($sql,$r,$cacheTime);
            }
        }else{
            $r=$this->db->get_all($sql);
        }
        return $r;
    }
    /**
     * 
     * @param number $page 单前页
     * @param number $eachPageRecords 每页几条记录 20
     * @param number $addSqlLog
     * @return Ambigous <boolean, multitype:unknown >
     */
    public function findPage($page=1,$eachPageRecords=20,$cache=0,$cacheTime=600,$addSqlLog=1){
        $counts=$this->counts();
        $allPage=ceil($counts/$eachPageRecords);
        if($page>$allPage)$page=$allPage;
        if($page<1)$page=1;
        $this->limit(array(($page-1)*$eachPageRecords,$eachPageRecords));
        $rs=$this->findAll(0,0,$cache,$cacheTime,$addSqlLog);
        return array('rs'=>$rs,'pageList'=>array('allRecords'=>$counts,'allPage'=>$allPage,'page'=>$page,'eachPageRecords'=>$eachPageRecords));
    }
    //ROM end
    //操作执行数据
    public function query($sql,$addSqlLog=0){
        if($addSqlLog)$this->sqlLists[$addSqlLog]=$sql;
        return $this->wdb->query($sql);
    }
    public function getColumns(){
        return $this->db->list_fields($this->tableName);
    }
    /* 事务 */
    public function start(){
        $this->wdb->start();
    }
    public function commit(){
        $this->wdb->commit();
    }
    public function rollback(){
        $this->wdb->rollback();
    }
    //去除字段
    public function move($arr){
        foreach ($arr as $v){
            unset($this->$v);
        }
    }
    public function insert_id(){
        return $this->wdb->insert_id();
    }
    /**
     * require
     * regex	正则验证，定义的验证规则是一个正则表达式（默认）
     callback	方法验证，定义的验证规则是当前模型类的一个方法
     confirm	验证表单中的两个字段是否相同，定义的验证规则是一个字段名
     equal	验证是否等于某个值，该值由前面的验证规则定义
     notequal	验证是否不等于某个值，该值由前面的验证规则定义
     in	验证是否在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串
     notin	验证是否不在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串
     length	验证长度，定义的验证规则可以是一个数字（表示固定长度）或者数字范围（例如3,12 表示长度从3到12的范围）
     between	验证范围，定义的验证规则表示范围，可以使用字符串或者数组，例如1,31或者array(1,31)
     notbetween	验证不在某个范围，定义的验证规则表示范围，可以使用字符串或者数组
     unique	验证是否唯一，系统会根据字段目前的值查询数据库来判断是否存在相同的值，当表单数据中包含主键字段时unique不可用于判断主键字段本身
     */
    /**
     * 
     * @param unknown $arr
     * @param string $append
     */
    public function setRule($arr=array(),$append=true){
        $this->append=$append;
        $this->_rules=$arr;
    }
    public function rule(){
        return array();
    }
    //['myName','string|((int|abs|format)|float)','default_value']
    public function getPostRule(){
        return array();
    }
    //字段
    public function getFields(){
        return array();
    }
    /**
     * 
     * @param unknown $arr
     * @param string $cond self::ADD新增时认证，self::UPDATE编辑时认证,为空或self::MULTI新增或修改都认证
     */
    public function checkRule($arr,$cond=null){
        $this->_errors=array();
        if($this->append){
            $rule=array_merge($this->rule(),$this->_rules);
        }else{
            $rule=$this->_rules;
        }
        $this->_rules=array();
        $this->append=true;
        foreach ($rule as $v){
            if(!is_array($v[0]))$childs=array($v[0]);
            else $childs=$v[0];
            foreach ($childs as $cn){
                $name=$cn;
                $express=$v[1];
                $confirmStr=$v[2];
                $condiction=$v[3];
                $isVal=$v[5]?$v[5]:self::MUST;
                
                if(($cond==null&&$v[4]==self::UPDATE)||($cond!=null&&$v[4]==self::ADD))continue;
                
                $_v=isset($arr[$name])?trim($arr[$name]):false;
                if($_v===false){
                    $this->_errors[]=$confirmStr;  
                    continue;
                }else{
                    switch($express){
                        case 'require'://['name','require','words..','',self::ADD,self::MUST]
                            if(empty($arr[$name]))$this->_errors[]=$confirmStr;
                            break;
                        case 'confirm'://['字段1','confirm','words..','字段2',self::MULTI,self::ISVAL]
                            if($isVal){
                                if(!empty($arr[$name])&&$arr[$name]!=$arr[$condiction])$this->_errors[]=$confirmStr;
                            }else{
                                if($arr[$name]!=$arr[$condiction])$this->_errors[]=$confirmStr;
                            }
                            break;
                        case 'equal'://['字段1','equal','words..','值',self::UPDATE]
                            if($isVal){
                                if(!empty($arr[$name])&&$arr[$name]!=$condiction)$this->_errors[]=$confirmStr;
                            }else{
                                if($arr[$name]!=$condiction)$this->_errors[]=$confirmStr;
                            }
                            break;
                        case 'notequal'://['字段1','notequal','words..','值']
                            if($isVal){
                                if(!empty($arr[$name])&&$arr[$name]==$condiction)$this->_errors[]=$confirmStr;
                            }else{
                                if($arr[$name]==$condiction)$this->_errors[]=$confirmStr;
                            }
                            break;
                        case 'in'://['字段1','in','words..',[1,2,3]]
                            if($isVal){
                                if(!empty($arr[$name])&&!in_array($arr[$name],$condiction))$this->_errors[]=$confirmStr;
                            }else{
                                if(!in_array($arr[$name],$condiction))$this->_errors[]=$confirmStr;
                            }
                            break;
                        case 'notin'://['字段1','notin','words..',[1,2,3]]
                            if($isVal){
                                if(!empty($arr[$name])&&!in_array($arr[$name],$condiction))$this->_errors[]=$confirmStr;
                            }else{
                                if(!in_array($arr[$name],$condiction))$this->_errors[]=$confirmStr;
                            }
                            break;
                        case 'length'://['字段1','length','words..',5]
                            if($isVal){
                                if(!empty($arr[$name])&&strlen($arr[$name])!=$condiction)$this->_errors[]=$confirmStr;
                            }else{
                                if(strlen($arr[$name])!=$condiction)$this->_errors[]=$confirmStr;
                            }
                            break;
                        case 'between'://['字段1','between','words..',[3,20]]
                            if($isVal){
                                if(!empty($arr[$name])&&$arr[$name]<$condiction[0]||$arr[$name]>$condiction[1])$this->_errors[]=$confirmStr;
                            }else{
                                if($arr[$name]<$condiction[0]||$arr[$name]>$condiction[1])$this->_errors[]=$confirmStr;
                            }
                            break;
                        case 'notbetween'://['字段1','notbetween','words..',[3,20]]
                            if($isVal){
                                if(!empty($arr[$name])&&$arr[$name]>=$condiction[0]&&$arr[$name]<=$condiction[1])$this->_errors[]=$confirmStr;
                            }else{
                                if($arr[$name]>=$condiction[0]&&$arr[$name]<=$condiction[1])$this->_errors[]=$confirmStr;
                            }
                            break;
                        case 'regex'://['字段1','regex','words..','正则表达式']
                            if($isVal){
                                if(!empty($arr[$name])&&!preg_match('/'.$condiction.'/',$arr[$name]))$this->_errors[]=$confirmStr;
                            }else{
                                if(!preg_match('/'.$condiction.'/',$arr[$name]))$this->_errors[]=$confirmStr;
                            }
                            break;
                            //['字段1','callback','words..','isEmail|isPhone|isDate|isUserName']
                        case 'callback'://['字段1','callback','words..',['class','method']]
                            if($isVal){
                                if(empty($arr[$name]))continue;
                            }
                            if(is_array($condiction)){
                                $callback=$condiction;
                            }elseif(class_exists('Valid')){
                                $callback=array('Valid',$condiction);
                            }else{
                                $callback=array('MValid',$condiction);
                            }
                            if(!call_user_func($callback,$arr[$name]))$this->_errors[]=$confirmStr;
                            break;
                        case 'unique'://['字段1','unique','words..','条件表达式']
                            if(!empty($arr[$name])&&$this->checkUnique($name.'="'.$arr[$name].'"',$condiction,1))$this->_errors[]=$confirmStr;
                            break;
                    }
                    
                }//end $__v
            }//end $cn
            
        }//end $rule
        
    }//checkRule
    public function checkUnique($conf,$condiction='',$append=0){
        if(empty($conf))return true;
        if($append){
            $myId=\core\helper\MReq::get($this->priKey);
            if(!empty($myId))$conf.=' AND '.$this->priKey.'<>"'.$myId.'"';
        }
        return $this->find($conf.$condiction);
    }
    public function add($arr,$replace=0){
        $re=false;
        $id = 0;
        $set=$this->get_insert_item($arr);
        if(empty($set))return false;
        if($replace==1)$sql="REPLACE INTO `$this->tableName` $set ";
        else $sql="INSERT INTO `$this->tableName` $set ";
        if($flag=$this->wdb->query($sql)){
            $id=$this->wdb->insert_id();
        }
        $re=$id > 0 ? $id : $flag;
        if(!$re)$this->_errors[]='插入数据错误';
        return $re;
    }
    public function edit($arr,$condiction=NULL){
        $re=false;
        $where=$this->get_where_item($condiction);
        if(empty($where))return $re;
        $update=$this->get_update_item($arr);
        $sql="UPDATE `$this->tableName` SET $update $where";
        if($this->wdb->query($sql)){
            $re = $this->wdb->affected_rows();
        }else{
            $this->_errors[]='更新数据错误';
        }
        return $re;
    }
    /* 修改|添加数据 */
    public function save($arr=array(),$condiction=NULL,$replace=0){
        $arr=array_merge($this->getProperty(),$arr);
        $re=false;
        $this->checkRule($arr,$condiction);
        if(!empty($this->_errors))return false;
        if(empty($condiction)&&empty($arr[$this->priKey])){//插入
            $id = 0;
            $set=$this->get_insert_item($arr);
            if($replace==1)$sql="REPLACE INTO `$this->tableName` $set ";
            else $sql="INSERT INTO `$this->tableName` $set ";
            if($flag=$this->wdb->query($sql)){
                $id=$this->wdb->insert_id();
            }
            $re=$id > 0 ? $id : $flag;
            if(!$re)$this->_errors[]='插入数据错误';
        }else{//编辑
            if(empty($condiction)&&!empty($arr[$this->priKey]))$condiction=$this->priKey."='".$arr[$this->priKey]."'";
            $where=$this->get_where_item($condiction);
            if(empty($where))return $re;
            $update=$this->get_update_item($arr);
            $sql="UPDATE `$this->tableName` SET $update $where";
            if($this->wdb->query($sql)){
                $re = $this->wdb->affected_rows();
            }else{
                $this->_errors[]='更新数据错误';
            }
        }
        return $re;

    }
    public function del($condiction=NULL,$userPriKey=0,$limit=1){
        $re=false;
        $where=$this->get_where_item($condiction,$userPriKey);
        if(empty($where))return $re;
        $sql="DELETE FROM `$this->tableName` $where".($limit>0?' LIMIT '.$limit:'');
        if($this->wdb->query($sql)){
            $re = $this->wdb->affected_rows();
        }else{
            $this->_errors[]='删除数据错误';
        }
        return $re;
    }
    //==========================================================
    private function get_where_item($select_items,$usePriKey=0){
        $sql_part = '';
        if(is_numeric($select_items)||!empty($select_items)){
             
            if(is_array($select_items)) {
                $sql_part =implode(' AND ',$select_items);
            }elseif($usePriKey&&!empty($this->priKey)){
                $sql_part = '`'.$this->priKey.'`='.(is_numeric($select_items)?intval($select_items):'\''.$this->db->escape_string(trim($select_items)).'\'');

            } else {
                $sql_part = $select_items;
            }
             
            $sql_part=' WHERE '.$sql_part;
             
        }
        return $sql_part;
    }
    function get_order_item($select_items){
        $sql_part = '';
        if(!empty($select_items)){
             
            if(is_array($select_items)) {
                foreach($select_items as $k=>$v){
                    $sql_part.=$k.' '.$v.',';
                }
                $sql_part=substr($sql_part,0,-1);
            } else {
                $sql_part = $select_items;
            }
             
            $sql_part=' ORDER BY '.$sql_part;
             
        }
        return $sql_part;
    }
    function get_limit_item($select_items){
        $sql_part = '';
        if(!empty($select_items)){
             
            if(is_array($select_items)) {
                $sql_part =implode(',',$select_items);
            } else {
                $sql_part = $select_items;
            }
             
            $sql_part=' LIMIT '. $sql_part;
             
        }
        return $sql_part;
    }

    function get_select_item($select_items){
        $sql_part = '';
        if($select_items!='*' and $select_items!=''){
            if(is_array($select_items)) {
                $sql_part = implode(",",$select_items);
            } else {
                $sql_part = " $select_items ";
            }
        } else {
            $sql_part = '*';
        }
        return $sql_part;
    }
    function get_update_item($update_items){
        $sql_part = '';
        if(is_array($update_items)){

            foreach($update_items as $k=>$v) {
                $sql_part .= "`$k` = '$v',";
            }
            return substr($sql_part,0,-1);
        }elseif(is_string($update_items)){
            return $update_items;
        } else {
            exit('update_items error');
            return false;
        }
    }

    function get_insert_item($insert_items){
        $sql_part = '';
        if(is_array($insert_items)){
            $set = $value = '';
            foreach($insert_items as $k=>$v) {
                $set .="`$k`,";
                $value .="'$v',";
            }
            $sql_part = '('.substr($set,0,-1).') VALUES ('.substr($value,0,-1).')';
            return $sql_part;
        } else {
            exit(var_dump($insert_items).'insert_items not array');
            return false;
        }
    }
}//end dao