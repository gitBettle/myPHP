<?php
namespace core\helper;
class MHtml{
    public static function strip($string){
        return htmlspecialchars($string);
    }
    public static function text($name,$ttype='text',$val='',$must=0,$abs=0,$txt='',$extra=''){
        return '<input type="text" txt="'.($txt?$txt:$name).'" name="'.$name.'" ttype="'.$ttype.'" value="'.$val.'" must="'.$must.'" abs="'.$abs.'" '.$extra.'/>';
    }
    public static function select($name,$fields=array(),$sel='',$must=0,$sel_option='请选择',$txt='',$extra=''){
        $str='<select name="'.$name.'" txt="'.($txt?$txt:$name).'" must="'.$must.'" '.$extra.'>';
        if($must)$str.='<option value="">'.$sel_option.'</option>';
        foreach ($fields as $k=>$v){
            $str.='<option value="'.self::strip($k).'" '.($sel==$k?'selected':'').'>'.self::strip($v).'</option>';
        }
        $str.='</select>';
        return $str;
    }
    public static function areaText($name,$val='',$must=0,$txt='',$extra=''){
        return '<textarea name="'.$name.'" txt="'.($txt?$txt:$name).'" must="'.$must.'" '.$extra.'>'.$val.'</textarea>';
    }
    public static function selectTmp($name,$fields=array(),$sel='',$must=0,$sel_option='请选择',$txt='',$extra=''){
        $str='<select name="'.$name.'" txt="'.($txt?$txt:$name).'" must="'.$must.'" '.$extra.'>';
        if($must)$str.='<option value="">'.$sel_option.'</option>';
        $arrStr='array(';
        foreach ($fields as $k=>$v){
            $arrStr.='\''.MHtml::strip($v).'\',';
        }
        $arrStr=trim($arrStr,',');
        $arrStr.=')';
        $str.='<?php
foreach('.$arrStr.' as $v){
   echo \'<option value="\'.\\core\\helper\\MHtml::strip($v).\'" \'.($v==$'.$name.'?\'selected\':\'\').\'>\'.\\core\\helper\\MHtml::strip($v).\'</option>\';
}
            ?>';
        $str.='</select>';
        return $str;
    }
}