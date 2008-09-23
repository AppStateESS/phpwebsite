<?php

  /**
   * Block conversion file
   *
   * Converts old blocks to new module
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('block', 'Block_Item.php');

function convert()
{
    if (Convert::isConverted('block')) {
        return _('Block is already converted.');
    }

    $mod_list = PHPWS_Core::installModList();
    
    if (!in_array('block', $mod_list)) {
        return _('Block is not installed.');
    }

    $db = Convert::getSourceDB('mod_blockmaker_data');
    if (!$db) {
        return _('An error occurred while accessing your mod_blockmaker_data table.'); 
    }
    $all_blocks = $db->select();
    $db->disconnect();
    Convert::siteDB();

    if (empty($all_blocks)) {
        return _('No blocks found.');
    } elseif (PEAR::isError($all_blocks)) {
        PHPWS_Error::log($all_blocks);
        return _('An error occurred while accessing your mod_blockmaker_data table.');
    }
   
    $values['key_id'] = 0;
    foreach ($all_blocks as $old_block) {
        $new_block = & new Block_Item;
        $new_block->setTitle(utf8_encode($old_block['block_title']));
        $new_block->setContent(PHPWS_Text::breaker(utf8_encode($old_block['block_content'] . $old_block['block_footer'])));
        $result = $new_block->save();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($all_blocks);
            return _('An error occurred while converting your old blocks.');
        }
        $values['block_id'] = $new_block->id;
        $db = new PHPWS_DB('block_pinned');
        $db->addValue($values);
        $db->insert();
    }
    Convert::addConvert('block');
    return _('Blocks converted.');

}


?>