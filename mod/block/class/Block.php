<?php

/**
 * Command class for block
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::initModClass('block', 'Block_Item.php');

class Block {

    function show()
    {
        $key = Key::getCurrent();

        if (empty($key)) {
            return;
        }
        Block::showBlocks($key);

        if (isset($_SESSION['Pinned_Blocks'])) {
            Block::viewPinnedBlocks($key);
        }
  
    }

    function viewPinnedBlocks($key)
    {
        if (!isset($_SESSION['Pinned_Blocks'])) {
            return FALSE;
        }

        $block_list = &$_SESSION['Pinned_Blocks'];
        if (empty($block_list)) {
            return NULL;
        }

        foreach ($block_list as $block_id => $block) {
            if (isset($GLOBALS['Current_Blocks'][$block_id])) {
                continue;
            }

            $block->setPinKey($key);
            $content[] = $block->view(TRUE);
        }

        if (empty($content)) {
            return;
        }

        $complete = implode('', $content);
        Layout::add($complete, 'block', 'Block_List');
    }

    function showBlocks($key)
    {
        $db = & new PHPWS_DB('block_pinned');
        $db->addColumn ('block_id');
        $db->addWhere('key_id', $key->id);
        $result = $db->select('col');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return NULL;
        }

        if (empty($result)) {
            return NULL;
        }

        foreach ($result as $block_id) {
            $block = & new Block_Item($block_id);
            $block->setPinKey($key);
            Layout::add($block->view(), 'block', $block->getContentVar());
            $GLOBALS['Current_Blocks'][$block_id] = TRUE;
        }

    }

}

?>