<?php
PHPWS_Core::initModClass('block', 'Block_Item.php');

class Block {

  function show($module, $id, $itemname=NULL)
  {
    if (empty($itemname)) {
      $itemname = $module;
    }

    if (isset($_SESSION['Stored_Blocks'])) {
      Block::viewStoredBlocks($module, $id, $itemname);
    }
  
  }

  function viewStoredBlocks($module, $id, $itemname)
  {
    if (!isset($_SESSION['Stored_Blocks'])) {
      return FALSE;
    }
    
    $block_list = & $_SESSION['Stored_Blocks'];

    $link['action']   = 'pin';
    $link['pinmod']   = $module;
    $link['item_id']   = $id;
    $link['itemname'] = $itemname;

    $pin = '<img src="./images/block/pin.png" />';
    foreach ($block_list as $block_id => $block) {
      $link['block_id'] = $block_id;
      $template = array('TITLE'   => $block->getTitle(),
			'CONTENT' => $block->getContent(),
			'PIN'     => PHPWS_Text::secureLink($pin, 'block', $link)
			);
      
      $content[] = PHPWS_Template::process($template, 'block', 'sample.tpl');
    }

    $complete = implode('', $content);
    Layout::add($complete, 'block', 'Block_List', FALSE);
  }

}

?>