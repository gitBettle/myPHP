<?php
namespace core\helper;
class MUrl{
    const UrlNative		= 1; //原生的Url形式,指从index.php，比如index.php?controller=blog&action=read&id=100
    const UrlPathinfo	= 2; //pathinfo格式的Url,指的是：/blog/read/id/100
    const UrlDiy		= 3; //经过urlRoute后的Url,指的是:/blog-100.html

    const UrlCtrlName	= 'controller';
    const UrlActionName	= 'action';
    const UrlModuleName	= 'module';

    const Anchor = "/#&"; //urlArray中表示锚点的索引

    const QuestionMarkKey = "?";// /site/abc/?callback=/site/login callback=/site/login部分在UrlArray里的key
    
    private static $arrM = array(
        self::UrlCtrlName,
        self::UrlActionName,
        self::UrlModuleName
    );
    private static $currentRoute=[];//目前的路由
    private static $urlRoute = []; //路由规则的缓存
//urlRoute=>   
//     'shichang' => 'simple/market',
//     'article.html' => 'site/article',
//     'article-<id:\\d+>.html' => 'site/article_detail',
//     'items/<id:\\d+>.html' => 'site/products',
//     'items-<cat:\\d+>.html' => 'site/pro_list',
//     'tuan.html' => 'site/groupon',
//     'brand.html' => 'site/brand',
//     'pinpai/<id:\\d+>.html' => 'site/brand_zone',
//     'help-<id:\\d+>.html' => 'simple/help',
//     'help-list-<id:\\d+>.html' => 'simple/help_list',
//     'help.html' => 'simple/help_list',
//     'notice.html' => 'site/notice',
//     'tuan-list.html' => 'site/groupon_list',
//     'search.html' => 'site/search_list',
//     'market_serach.html' => 'simple/market_serach',
//     'sitemap.html' => 'site/sitemap',
//     'link.html' => 'site/link',
//     'error.html' => 'site/error',
//     'notice-<id:\\d+>.html' => 'site/notice_detail',
//     'tuan-<id:\\w+>-<regiment_id:\\w+>.html' => 'site/products/promo/groupon',

    /**
     * @brief 获取当前Controller、action、module的信息
     * @param string $key controller或者action或者module
     * @return string|null
    */
    public static function getInfo($key){
        $arr = array(
            'controller'=>self::UrlCtrlName,
            'action'=>self::UrlActionName,
            'module'=>self::UrlModuleName
        );
        if($arr[$key]!==null){

            if(MReq::get( $arr[$key] )!==null)return MFilter::safedir(MReq::get( $arr[$key] ));

        }
        return '';
    }
    //         echo $_SERVER['PATH_INFO'];///wws-htm.html
    //         echo $_SERVER['QUERY_STRING'].'<br/>';//dd&kk
    //         echo $_SERVER['SCRIPT_NAME'].'<br/>';///front/public/index.php
    //         echo $_SERVER['PHP_SELF'].'<br/>';///front/public/index.php
    //         echo $_SERVER['REQUEST_URI'].'<br/>';///front/public/index.php/wws-htm.html?dd&kk
    public static function beginUrl($argv=''){
        if(IS_CLI===true){
            if(!empty($argv[1])){
                $urlArray=explode('/',$argv[1]);
                if(!empty($urlArray[0])){
                    $tmp = explode('-',$urlArray[0]);
                    if( count($tmp) == 2 ){
                        MReq::set(self::UrlModuleName,$tmp[0]);
                        MReq::set(self::UrlCtrlName , $tmp[1]);
                    }else{
                        MReq::set(self::UrlCtrlName , $urlArray[0] );
                    }
                }
                !empty($urlArray[1])&&MReq::set(self::UrlActionName,$urlArray[1]);
                if(MUrl::getInfo(self::UrlCtrlName)=='migration'){//迁移数据库
                    MReq::set('cls',$argv[2]);
                    return;
                }
            }
        
            if(!empty($argv[2])){
                parse_str($argv[2], $urlArray);
                foreach($urlArray as $key=>$value){
                    MReq::set($key,$value);
                }
            }
            return;
        }
        
        $myGet=$_GET;
        
        $urlRoute=\core\My::$app->getConfig('urlRoute');
        //$urlRoute=['notice-<id:\\d+>'=>'site/index/'];
        if($_SERVER['PATH_INFO']!==null){
            $url=MFilter::safeCls($_SERVER['PATH_INFO']);
            if(!empty($urlRoute)){//路由重新
                foreach ($urlRoute as $regPattern=>$value){
                    //notice-(\d+)-(\w+)
                    $regPatternReplace = preg_replace('%<\w+?:(.*?)>%',"($1)",$regPattern);
                    if(strpos($regPatternReplace,'%') !== false){
                        $regPatternReplace = str_replace('%','\%',$regPatternReplace);
                    }
                    if(preg_match("%$regPatternReplace%",$url,$matchValue)){
                        //是否完全匹配整个完整url
                        $matchAll = array_shift($matchValue);
                        
                        if($matchAll != $url)continue;
                        //如果url存在动态参数，则获取到$urlArray
                        if($matchValue){
                            preg_match_all('%<\w+?:.*?>%',$regPattern,$matchReg);
                            foreach($matchReg[0] as $key => $val){
                                $val                     = trim($val,'<>');
                                $tempArray               = explode(':',$val,2);
                                $urlArray[$tempArray[0]] = $matchValue[$key]!==null ? $matchValue[$key] : '';
                            }//endforeach $matchReg[0]
                        
                        }//end $matchValue
                        $re=explode('/',trim($value,'/'));
                        $ct=count($re);
                        if($ct<1)continue;
                        $ctrlName=array_shift($re);
                        if(strpos($ctrlName,'-')){
                            $ctrlArr=explode('-',$ctrlName,2);
                            MReq::set(self::UrlModuleName,$ctrlArr[0]);
                            MReq::set(self::UrlCtrlName,$ctrlArr[1]);
                        }else{
                            MReq::set(self::UrlCtrlName,$ctrlName);
                        }
                        if($ct>=2){
                            $actName=array_shift($re);
                            MReq::set(self::UrlActionName,$actName);
                        }
                        if(!empty($re)){
                            $tmp=null;
                            $tmpArr=[];
                            foreach ($re as $vv){
                                if($tmp===null){
                                    if(in_array($vv,self::$arrM))continue;
                                    $tmpArr[$vv]='';
                                    $tmp=$vv;
                                }else{
                                    if(strpos($vv,'>')){//<id>
                                        $vv=trim($vv,'<>');
                                        $tmpArr[$tmp]=$urlArray[$vv]!==null?$urlArray[$vv]:'';
                                    }else{
                                        $tmpArr[$tmp]=$vv;
                                    }
                                    $tmp=null;
                                }
                            }
                            $re=$tmpArr;
                        }
                        if(!empty($urlArray))$re=$re+$urlArray;
                        $get=[];
                        foreach ($re as $kk=>$vv){
                            if(in_array($kk,self::$arrM))continue;
                            MReq::set($kk,$vv);
                            $get[$kk]=$vv;
                        }
                        self::$currentRoute=[1,$get+$myGet,$regPattern,$urlArray];
                        return true;
                    }//end preg_match
                    
                }//endforeach $urlRoute
                
            }//endif $urlRoute
            
            $re=explode('/',$url);
            $ct=count($re);
            if($ct<1){
                self::$currentRoute=[0,$myGet];
                return false;
            }
            $ctrlName=array_shift($re);
            if(strpos($ctrlName,'-')){
                $ctrlArr=explode('-',$ctrlName,2);
                MReq::set(self::UrlModuleName,$ctrlArr[0]);
                MReq::set(self::UrlCtrlName,$ctrlArr[1]);
            }else{
                MReq::set(self::UrlCtrlName,$ctrlName);
            }
            if($ct>=2){
                $actName=array_shift($re);
                MReq::set(self::UrlActionName,$actName);
            }
            if(!empty($re)){
                $tmp=null;
                $tmpArr=[];
                foreach ($re as $vv){
                    if($tmp===null){
                        if(in_array($vv,self::$arrM))continue;
                        $tmpArr[$vv]='';
                        $tmp=$vv;
                    }else{
                        $tmpArr[$tmp]=$vv;
                        $tmp=null;
                    }
                }
                $re=$tmpArr;
            }
            foreach ($re as $kk=>$vv) MReq::set($kk,$vv);
            self::$currentRoute=[0,$re+$myGet];
            return true;
            
        }else{
            self::$currentRoute=[0,$myGet];
           
        }//end $_SERVER['PATH_INFO']
    }
    /**
     * 
     * @param string $url site/index|@/index 为空保留原来的链接记录
     * @param array/string $queryString 
     * @param boolean $reset true|false 复用$_GET参数
     * @return string url
     */
    public static function build($url='',$queryString=array(),$reset=false){
        $baseUrl=$_SERVER['SCRIPT_NAME']?$_SERVER['SCRIPT_NAME']:$_SERVER['PHP_SELF'];
        $baseUrl=trim($baseUrl,'/');
        if(is_array($queryString)){
            $re=$queryString;
        }elseif(is_string($queryString)){
            parse_str(trim($queryString,'?&'),$re);
        }
        if(!empty(self::$currentRoute)&&empty($url)){
            
            if(self::$currentRoute[0]==1){//使用自定义路由
                //self::$currentRoute=[1,$get,$regPattern,$urlArray]; 
                $regPattern=self::$currentRoute[2];
                $urlArray=self::$currentRoute[3];
                $getUrlArray=[];
                if(!empty($urlArray)){
                    foreach ($urlArray as $qk=>$qv){
                        if(!empty($re[$qk])){
                            $qvv=$re[$qk];
                            unset($re[$qk]);
                        }else{
                            $qvv=$qv;
                        }
                        $getUrlArray[$qk]=$qvv;
                    }
                }
                foreach ($getUrlArray as $qk=>$qv){
                    $regPattern=preg_replace('%<'.MFilter::safe($qk).':.*?>%',$qv,$regPattern);
                }
            }else{//默认pathinfo
                //self::$currentRoute=[0,$re];
                if($reset){
                    $get=self::$currentRoute[1];
                    $re=array_merge($get,$re);
                }
                $regPattern=(MUrl::getInfo('module')?MUrl::getInfo('module').'-':'').MUrl::getInfo('controller').'/'.MUrl::getInfo('action');
            }
        }elseif(!empty(self::$currentRoute)){
            if($reset){
                $get=self::$currentRoute[1];
                //print_r(self::$currentRoute);
                $re=array_merge($get,$re);
            }
            $regPattern=MFilter::safeCls($url);
            $patterArr=explode('/',$regPattern);
            if($patterArr[0]=='@'){
                $patterArr[0]=(MUrl::getInfo('module')?MUrl::getInfo('module').'-':'').MUrl::getInfo('controller');
                $regPattern=implode('/',$patterArr);
            }
            
        }else{
            return $baseUrl;
        }
        
        $url=$baseUrl.'/'.$regPattern;
        $suffix='';
        foreach ($re as $k=>$v){
            $suffix.='&'.$k.'='.$v;
        }
        $suffix=$suffix?'?'.ltrim($suffix,'&'):'';
        $baseUrl='/'.trim($url,'/').$suffix;
        return '/'.ltrim($baseUrl,'/');
    }
}//end class