<?php 
use core\helper\MHtml;
use core\helper\MUrl;
?>
<form action="<?=MUrl::build('@/save','',true)?>" method="post"><p>pid:<input type="text" txt="pid" name="pid" ttype="int" value="<?=MHtml::strip($pid)?>" must="0" abs="0" /></p>
<p>sometext:<input type="text" txt="sometext" name="sometext" ttype="text" value="<?=MHtml::strip($sometext)?>" must="0" abs="0" /></p>
<p><button id="sub">提交</button></p>   
             </form>
              <script language="javascript" src="<?=$myCssPath?>skin/js/jquery.js"></script>
             <script>
                $(function(){
                    if($("#sub")[0])$("#sub")[0].disabled=false;
                    $("#sub").click(function(){
                        $(this)[0].disabled=true;
                        var Thef=$(this);
                        var errs=[];
                        $(this).parents("form").find("[must=\"1\"]").each(function(){
                            var val=$.trim($(this).val());
                            if(val==""){
                                errs.push($(this).attr("txt")+"不能为空!");
                            }else if($(this).attr("ttype")=="int"){
                                if($(this).attr("abs")=="1"&&!/^[1-9][0-9]*$/.test(val)){
                                    errs.push($(this).attr("txt")+"数字格式错误!");
                                }else if($(this).attr("abs")=="0"&&!/^[\-]{0,1}[1-9][0-9]*$/.test(val)){
                                    errs.push($(this).attr("txt")+"数字格式错误!");
                                }
                            }else if($(this).attr("ttype")=="float"||$(this).attr("ttype")=="decimal"){
                                if(isNaN(val)||val==0){
                                    errs.push($(this).attr("txt")+"数字格式错误!");
                                }else if($(this).attr("abs")=="1"&&!isNaN(val)&&val<=0){
                                    errs.push($(this).attr("txt")+"数字格式错误!");
                                }
                            }else if($(this).attr("ttype")=="datetime"){
                                var _reTimeReg = /^(?:19|20)[0-9][0-9]-(?:(?:0[1-9])|(?:1[0-2]))-(?:(?:[0-2][1-9])|(?:[1-3][0-1])) (?:(?:[0-2][0-3])|(?:[0-1][0-9])):[0-5][0-9]:[0-5][0-9]$/;
                                if(!_reTimeReg.test(val)){
                                    errs.push($(this).attr("txt")+"日期格式错误!");
                                }
                            }
                        });
                        if(errs.length>0){
                            alert(errs.join("\n"));
                            Thef[0].disabled=false;
                            return false;
                        }
                        $(this).parents("form").submit();
                    });
                });
             </script>