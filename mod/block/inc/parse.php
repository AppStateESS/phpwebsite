<?php

PHPWS_Core::initModClass('block', 'Block_Item.php');

function viewBlock($values) {
  $block = new Block_Item($values[0]);

  $template['BLOCK'] = $block->view(FALSE, FALSE);
  return PHPWS_Template::process($template, 'block', 'embedded.tpl');
}

?>