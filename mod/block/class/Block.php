<?php
PHPWS_Core::initModClass('block', 'Block_Item.php');

class Block {

  function show($module, $id, $itemname=NULL)
  {
    if (empty($itemname)) {
      $itemname = $module;
    }

    Block::showBlocks($module, $id, $itemname);

    if (isset($_SESSION['Stored_Blocks'])) {
      Block::viewStoredBlocks($module, $id, $itemname);
    }
  
  }

  function viewStoredBlocks($module, $id, $itemname)
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
      $block->_module   = $module;
      $block->_item_id  = $id;
      $block->_itemname = $itemname;
      $content[] = $block->view(TRUE);
    }

    if (empty($content)) {
      return;
    }
    $complete = implode('', $content);
    Layout::add($complete, 'block', 'Block_List', FALSE);
  }

  function showBlocks($module, $id, $itemname)
  {
    $db = & new PHPWS_DB('block_pinned');
    $db->addWhere('module',   $module);
    $db->addWhere('item_id',  $id);
    $db->addWhere('itemname', $itemname);
    $db->addColumn('block_id');
    $result = $db->select('col');

    if (empty($result)) {
      return;
    }

    foreach ($result as $block_id) {
      $block = & new Block_Item($block_id);
      $block->_module   = $module;
      $block->_item_id  = $id;
      $block->_itemname = $itemname;

      Layout::add($block->view(), 'block', $block->getContentVar(), FALSE);
      $GLOBALS['Current_Blocks'][$block_id] = TRUE;
    }

  }

}

?>