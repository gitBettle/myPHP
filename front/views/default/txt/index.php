
                <div><button id="addRecord">提交</button></div>
                <form action="<?=MUrl::createMyUrl(array('action'=>'del'))?>" method="post">
                <table with=100% border=1><tr>
                <?php if($result):?>
                <th><input type="checkbox" id="alls"/></th>
                <th>id</th><th>uId</th><th>title</th><th>appId</th><th>appSecret</th><th>createTime</th><th>remark</th><th>mchid</th><th>keys</th><th>isDelivery</th><th>isPay</th><th>qcode</th><th>role_id</th><th>openId</th><th>push_music</th><th>push_music_enable</th><th>isBaimaShop</th><th>isBaimaRecommand</th><th>level</th><th>follower</th><th>printerCode</th><th>distributed</th>
                    </tr>
                    <?php foreach ($result as $v):?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?=MHtml::strip($v['id'])?>"/></td>
                        <td><?=MHtml::strip($v['id'])?></td>
<td><?=MHtml::strip($v['uId'])?></td>
<td><?=MHtml::strip($v['title'])?></td>
<td><?=MHtml::strip($v['appId'])?></td>
<td><?=MHtml::strip($v['appSecret'])?></td>
<td><?=MHtml::strip($v['createTime'])?></td>
<td><?=MHtml::strip($v['remark'])?></td>
<td><?=MHtml::strip($v['mchid'])?></td>
<td><?=MHtml::strip($v['keys'])?></td>
<td><?=MHtml::strip($v['isDelivery'])?></td>
<td><?=MHtml::strip($v['isPay'])?></td>
<td><?=MHtml::strip($v['qcode'])?></td>
<td><?=MHtml::strip($v['role_id'])?></td>
<td><?=MHtml::strip($v['openId'])?></td>
<td><?=MHtml::strip($v['push_music'])?></td>
<td><?=MHtml::strip($v['push_music_enable'])?></td>
<td><?=MHtml::strip($v['isBaimaShop'])?></td>
<td><?=MHtml::strip($v['isBaimaRecommand'])?></td>
<td><?=MHtml::strip($v['level'])?></td>
<td><?=MHtml::strip($v['follower'])?></td>
<td><?=MHtml::strip($v['printerCode'])?></td>
<td><?=MHtml::strip($v['distributed'])?></td>

                            <td>
                            <a href="<?=MUrl::createMyUrl(array('action'=>'edit','id'=>MHtml::strip($v['id'])))?>">编辑</a>  <a href="<?=MUrl::createMyUrl(array('action'=>'del','id'=>MHtml::strip($v['id'])))?>">删除</a></td></tr>
            <?php endforeach;?>
                    <tr><td columns="23"><button id="delAll">批量删除</button><?php echo $pageList;?></td></tr>
                    </table></form>
            <?php endif;?>
                        <script language="javascript" src="./skin/js/jquery.js"></script>
                        <script>
                        $(function(){
                            if($("#delAll")[0])$("#delAll")[0].disabled=false;
                            $("#delAll").click(function(){
                                $(this)[0].disabled=true;
                                $(this).parents("form").submit();
                            });
                            $("#addRecord").click(function(){
                                window.location.href='<?=MUrl::createMyUrl(array('action'=>'edit'))?>';
                            });
                            $("#alls").click(function(){
                                $(":checkbox").attr("checked",$(this)[0].checked);
                            });
                        });
                        </script>