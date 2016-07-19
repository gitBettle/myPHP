<?php 
use core\helper\MHtml;
if(is_string($mess)):?>
<p><?=MHtml::strip($mess)?></p>
<?php elseif(is_array($mess)):?>
<?php foreach ($mess as $vv):?>
<p><?=MHtml::strip($vv)?></p>
<?php endforeach;?>
<?php endif;?>
<?php if($goUrl):?>
<ul>
<?php foreach($goUrl as $v):?>
<li><a href="<?php echo $v['url'];?>"><?php echo MHtml::strip($v['text']);?></a></li>
<?php endforeach;?>
</ul>
<?php endif;?>
