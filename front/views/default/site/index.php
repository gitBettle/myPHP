<?php 
use core\helper\MHtml;
use core\helper\MUrl;
?>
                <div><button id="addRecord">提交</button></div>
                <form action="<?=MUrl::build('@/del')?>" method="post">
                <table with=100% border=1><tr>
                <?php 
                if($result):?>
                <th><input type="checkbox" id="alls"/></th>
                <th>id</th><th>pid</th><th>sometext</th><th>操作</th>
                    </tr>
                    <?php foreach ($result as $v):?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?=MHtml::strip($v['id'])?>"/></td>
                        <td><?=MHtml::strip($v['id'])?></td>
<td><?=MHtml::strip($v['pid'])?></td>
<td><?=MHtml::strip($v['sometext'])?></td>

                            <td>
                            <a href="<?=MUrl::build('@/edit',['id'=>MHtml::strip($v['id'])])?>">编辑</a>  <a href="<?=MUrl::createMyUrl(array('action'=>'del','id'=>MHtml::strip($v['id'])))?>">删除</a></td></tr>
            <?php endforeach;?>
                    <tr><td colspan="5" align="right"><button id="delAll">批量删除</button><?php echo $pageList;?></td></tr>
                    </table></form>
            <?php endif;?>
                        <script language="javascript" src="<?=$myCssPath?>skin/js/jquery.js"></script>
                        <script>
                        $(function(){
                            if($("#delAll")[0])$("#delAll")[0].disabled=false;
                            $("#delAll").click(function(){
                                $(this)[0].disabled=true;
                                $(this).parents("form").submit();
                            });
                            $("#addRecord").click(function(){
                                window.location.href='<?=MUrl::build('@/edit')?>';
                            });
                            $("#alls").click(function(){
                                $(":checkbox").attr("checked",$(this)[0].checked);
                            });
                        });
                        </script>