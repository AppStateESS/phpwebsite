<?php

/**
 * Command class for block
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('block', 'Block_Item.php');

class Block {

    public static function show()
    {
        Block::showAllBlocks();

        $key = Key::getCurrent();

        if (empty($key) || $key->isDummy(true)) {
            return;
        }
        Block::showBlocks($key);

    }

    public static function showAllBlocks()
    {
        $key = new Key;
        $key->id = -1;
        Block::showBlocks($key);
    }

    public static function showBlocks($key)
    {
        $db = new PHPWS_DB('block');
        $db->addWhere('block_pinned.key_id', $key->id);
        $db->addWhere('id', 'block_pinned.block_id');
        Key::restrictView($db, 'block');
        $result = $db->getObjects('Block_Item');

        if (PHPWS_Error::isError($result)) {
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