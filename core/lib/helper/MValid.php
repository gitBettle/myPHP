<?php
namespace core\helper;
class MValid{
    /**
     * 检查时间
     */
    public static function isDate($date){
        if(!preg_match('/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{1,2})$/i',$date,$match))return false;
        if(isset($match[1])&&isset($match[2])&&isset($match[3])){
            return checkdate($match[2],$match[3],$match[1]);
        }else{
            return false;
        }
    }
    /**
     * 验证手机号码
     * @param string $phonenumber
     * @return boolean
     */
    public static function isPhone($phonenumber){
        if(preg_match("/^1[1-9][0-9]\d{8}$/",$phonenumber))return true;
        else return false;
    }
    /**
     * 检查用户名是否符合规定
     *
     * @param STRING $username
     * @return 	TRUE or FALSE
     */
    public static function isUserName($username) {
        $strlen = strlen($username);
        if($this->is_badword($username) || !preg_match("/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/", $username)){
            return false;
        } elseif ( 20 <= $strlen || $strlen < 2 ) {
            return false;
        }
        return true;
    }
    //email
    public static function isEmail($email) {
        return preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
    }
}