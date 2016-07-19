<form action="<?=\core\helper\MUrl::build('@/save','',true)?>" method="post"><p>uId:<input type="text" txt="uId" name="uId" ttype="int" value="<?=\core\helper\MHtml::strip($uId)?>" must="1" abs="1" /></p>
<p>title:<input type="text" txt="title" name="title" ttype="text" value="<?=\core\helper\MHtml::strip($title)?>" must="1" abs="0" /></p>
<p>appId:<input type="text" txt="appId" name="appId" ttype="text" value="<?=\core\helper\MHtml::strip($appId)?>" must="0" abs="0" /></p>
<p>appSecret:<input type="text" txt="appSecret" name="appSecret" ttype="text" value="<?=\core\helper\MHtml::strip($appSecret)?>" must="0" abs="0" /></p>
<p>createTime:<input type="text" txt="createTime" name="createTime" ttype="datetime" value="<?=\core\helper\MHtml::strip($createTime)?>" must="0" abs="0" /></p>
<p>remark:<input type="text" txt="remark" name="remark" ttype="text" value="<?=\core\helper\MHtml::strip($remark)?>" must="0" abs="0" /></p>
<p>mchid:<input type="text" txt="mchid" name="mchid" ttype="text" value="<?=\core\helper\MHtml::strip($mchid)?>" must="0" abs="0" /></p>
<p>keys:<input type="text" txt="keys" name="keys" ttype="text" value="<?=\core\helper\MHtml::strip($keys)?>" must="0" abs="0" /></p>
<p>isDelivery:<select name="isDelivery" txt="isDelivery" must="0" ><?php
foreach(array('1','0') as $v){
   echo '<option value="'.\core\helper\MHtml::strip($v).'" '.($v==$isDelivery?'selected':'').'>'.\core\helper\MHtml::strip($v).'</option>';
}
            ?></select></p>
<p>isPay:<select name="isPay" txt="isPay" must="0" ><?php
foreach(array('1','-1','0') as $v){
   echo '<option value="'.\core\helper\MHtml::strip($v).'" '.($v==$isPay?'selected':'').'>'.\core\helper\MHtml::strip($v).'</option>';
}
            ?></select></p>
<p>qcode:<input type="text" txt="qcode" name="qcode" ttype="text" value="<?=\core\helper\MHtml::strip($qcode)?>" must="0" abs="0" /></p>
<p>role_id:<input type="text" txt="role_id" name="role_id" ttype="int" value="<?=\core\helper\MHtml::strip($role_id)?>" must="1" abs="1" /></p>
<p>openId:<input type="text" txt="openId" name="openId" ttype="text" value="<?=\core\helper\MHtml::strip($openId)?>" must="0" abs="0" /></p>
<p>push_music:<input type="text" txt="push_music" name="push_music" ttype="text" value="<?=\core\helper\MHtml::strip($push_music)?>" must="1" abs="0" /></p>
<p>push_music_enable:<select name="push_music_enable" txt="push_music_enable" must="0" ><?php
foreach(array('1','0') as $v){
   echo '<option value="'.MHtml::strip($v).'" '.($v==$push_music_enable?'selected':'').'>'.\core\helper\MHtml::strip($v).'</option>';
}
            ?></select></p>
<p>isBaimaShop:<input type="text" txt="isBaimaShop" name="isBaimaShop" ttype="int" value="<?=\core\helper\MHtml::strip($isBaimaShop)?>" must="1" abs="0" /></p>
<p>isBaimaRecommand:<input type="text" txt="isBaimaRecommand" name="isBaimaRecommand" ttype="int" value="<?=\core\helper\MHtml::strip($isBaimaRecommand)?>" must="1" abs="0" /></p>
<p>level:<input type="text" txt="level" name="level" ttype="int" value="<?=\core\helper\MHtml::strip($level)?>" must="1" abs="0" /></p>
<p>follower:<input type="text" txt="follower" name="follower" ttype="text" value="<?=\core\helper\MHtml::strip($follower)?>" must="1" abs="0" /></p>
<p>printerCode:<input type="text" txt="printerCode" name="printerCode" ttype="text" value="<?=\core\helper\MHtml::strip($printerCode)?>" must="1" abs="0" /></p>
<p>distributed:<select name="distributed" txt="distributed" must="0" ><?php
foreach(array('nopass','pass','applying','noapply') as $v){
   echo '<option value="'.\core\helper\MHtml::strip($v).'" '.($v==$distributed?'selected':'').'>'.\core\helper\MHtml::strip($v).'</option>';
}
            ?></select></p>
<p>extras:<textarea name="extras" txt="extras" must="0" ><?=\core\helper\MHtml::strip($extras)?></textarea></p>
<p><button id="sub">提交</button></p>   
             </form>
              <script language="javascript" src="<?=$myCssPaths?>skin/js/jquery.js"></script>
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