<?php
PHPWS_Core::initModClass('block', 'Block_Item.php');

class Block {

  function show($key)
  {
    Block::showBlocks($key);

    if (isset($_SESSION['Stored_Blocks'])) {
      Block::viewStoredBlocks($key);
    }
  
  }

  function viewStoredBlocks($key)
  {
    if (!isset($_SESSION['Stored_Blocks'])) {
      return FALSE;
    }

    $block_list = &$_SESSION['Stored_Blocks'];
    if (empty($block_list)) {
      return NULL;
    }

    foreach ($block_list as $block_id => $block) {
      if (isset($GLOBALS['Current_Blocks'][$block_id])) {
	continue;
      }

      $block->setKey($key);
      $content[] = $block->view(TRUE);
    }

    if (empty($content)) {
      return;
    }
    $complete = implode('', $content);
    Layout::add($complete, 'block', 'Block_List', FALSE);
  }

  function showBlocks($key)
  {
    $key->setTable('block_pinned');
    $key->setColumnName('block_id');
    $result = $key->getMatches();

    if (empty($result)) {
      return;
    }

    foreach ($result as $block_id) {
      $block = & new Block_Item($block_id);
      $block->setKey($key);

      Layout::add($block->view(), 'block', $block->getContentVar(), FALSE);
      $GLOBALS['Current_Blocks'][$block_id] = TRUE;
    }

  }

}

?>