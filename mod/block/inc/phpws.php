<?php

PHPWS_Core::initModClass('block', 'Block_Item.php');
function phpws_block($item, $id)
{
  $block = & new Block_Item((int)$id);
  $template['BLOCK'] = $block->view(FALSE, FALSE);
  return PHPWS_Template::process($template, 'block', 'embedded.tpl');
  
}


?>