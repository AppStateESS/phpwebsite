<?php

/**
 * Command class for block
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

core\Core::initModClass('block', 'Block_Item.php');

class Block {

    public static function show()
    {
        Block::showAllBlocks();

        $key = \core\Key::getCurrent();

        if (empty($key) || $key->isDummy(true)) {
            return;
        }
        Block::showBlocks($key);

        if (isset($_SESSION['Pinned_Blocks'])) {
            Block::viewPinnedBlocks($key);
        }

    }

    public static function showAllBlocks()
    {
        $key = new \core\Key;
        $key->id = -1;
        Block::showBlocks($key);
    }

    public static function viewPinnedBlocks($key)
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

    public static function showBlocks($key)
    {
        $db = new \core\DB('block');
        $db->addWhere('block_pinned.key_id', $key->id);
        $db->addWhere('id', 'block_pinned.block_id');
        \core\Key::restrictView($db, 'block');
        $result = $db->getObjects('Block_Item');

        if (core\Error::isError($result)) {
            \core\Error::log($result);
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