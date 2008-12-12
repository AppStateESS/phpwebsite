<?php

/**
 * Command class for block
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::initModClass('block', 'Block_Item.php');

class Block {

    public function show()
    {
        Block::showAllBlocks();

        $key = Key::getCurrent();

        if (empty($key) || $key->isDummy(true)) {
            return;
        }
        Block::showBlocks($key);

        if (isset($_SESSION['Pinned_Blocks'])) {
            Block::viewPinnedBlocks($key);
        }

    }

    public function showAllBlocks()
    {
        $key = new Key;
        $key->id = -1;
        Block::showBlocks($key);
    }

    public function viewPinnedBlocks($key)
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

    public function showBlocks($key)
    {
        $db = new PHPWS_DB('block');
        $db->addWhere('block_pinned.key_id', $key->id);
        $db->addWhere('id', 'block_pinned.block_id');
        Key::restrictView($db, 'block');
        $result = $db->getObjects('Block_Item');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return NULL;
        }

        if (empty($result)) {
            return NULL;
        }

        foreach ($result as $block) {
            $block->setPinKey($key);
            Layout::add($block->view(), 'block', $block->getContentVar());
            $GLOBALS['Current_Blocks'][$block->id] = TRUE;
        }

    }

}

?>