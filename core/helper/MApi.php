<?php
namespace my\helper;
class MApi{
    private static $redis=null;
    private static $appId='wx630a1869766b11ed';
    private static $appSecret='cf1bf6611e11818039ce3cafc21054ee';
    private static $accessKey='accessToken-afa';
    public static function get_fans_info($openid){
        $key='Info-'.$openid;
        $redis=self::init_redis();
        $arr=$redis->get($key);
        if(!empty($arr))return unserialize($arr);
        $usrinfo=self::get_wx_api('https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid='.$openid.'&lang=zh_CN');//��ȡ��Ա��Ϣ
        self::write_logs(var_export($usrinfo,true));
        if(!empty($usrinfo['openid'])){
            $arr['openid']=$usrinfo['openid'];
            $arr['nickname']=$usrinfo['nickname'];
            $arr['sex']=$usrinfo['sex'];
            $arr['city']=$usrinfo['city'];
            $arr['province']=$usrinfo['province'];
            $arr['country']=$usrinfo['country'];
            $arr['headimgurl']=$usrinfo['headimgurl'];
            $arr['addTime']=date('Y-m-d H:i:s',$usrinfo['subscribe_time']);
            $arr['isSub']=$usrinfo['subscribe'];
            $rs=array_map(array('func','new_addslashes'),$arr);
            $redis->set($key,serialize($rs));
            return $rs;
        }
        return false;
    }
    //微信推送json格式内容
    private static function put_json($url, $jsonData,$type='json'){
        $ch = curl_init($url) ;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($type=='json'){
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        }else{
            curl_setopt($ch, CURLOPT_HTTPHEADER, 0);
        }
        $result = curl_exec($ch) ;
    
        curl_close($ch) ;
        return $result;
    }
    //获得微信远程API(直接访问url)
    public static function get_wx_api($url,$reset=0){
        static $nums=0;
        $json=file_get_contents(str_replace('{$token}',self::get_token($reset),$url));
        if(!empty($json)){
    
            $data=json_decode($json,true);
            if(empty($data)||!is_array($data)||!empty($data['errcode'])){
                if(++$nums<=3&&$data['errcode']==40001){//$accessToken 过期
                    return self::get_wx_api($url,1);
                }
                return false;
            }
            $nums=0;
            return $data;
        }
        return false;
    
    }
    //获得推送给微信的API返回值(post)
    function get_wx_post_api($jsonData,$url,$reset=0){
        static $nums=0;
        $url=str_replace('{$token}',self::get_token($reset),$url);
        $json=putJson($url, $jsonData);
        if(!empty($json)){
    
            $datas=json_decode($json,true);
            if(empty($datas)||!is_array($datas)||!empty($datas['errcode'])){
                if(++$nums<=3&&$datas['errcode']==40001){//$accessToken 过期
                    return self::get_wx_post_api($jsonData, $url,1);
                }
                return false;
            }
            $nums=0;
            return $datas;
        }
        return false;
    
    }
    public static function write_logs($string,$fn='work'){
        $f=fopen(BASEPATH.'/caches/log/'.$fn.date('Y-m-d'),'a+');
        fwrite($f, $string."\n");
        fclose($f);
    }
    public static function get_token($flag=0){
        static $nums=0;
        $redis=self::init_redis();
        $data=$redis->get(self::$accessKey);
        $access_token=false;
        if ($flag==1||empty($data)) {
            if(DOMAIN=='http://wx.51afa.com/'){//阿发
                self::$appId='wx8b41b423f796cfbe';
                self::$accessKey='6e84fb89ec45a3c89503d196c5c99c83';
            }
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.self::$appId.'&secret='.self::$appSecret;
            $res = json_decode(file_get_contents($url),true);
            if (isset($res['access_token'])) {
                $access_token = $res['access_token'];
                $redis->set(self::$accessKey,$access_token);
                $redis->expire(self::$accessKey,6000);
                $nums=0;
            }else{
                if(isset($res['errmsg'])){
                    if(++$nums<=3){
                        $redis->del(self::$accessKey);
                        $access_token = self::get_token(1);
                        return $access_token;
                    }
                }
                return $res;
            }
        } else {
            $nums=0;
            $access_token = $data;
        }
        return $access_token;
    }//end getToken
}//end api