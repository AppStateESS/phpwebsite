<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
Block::show();

if (Current_User::allow('block')) {

    $key = Key::getCurrent();
    if (Key::checkKey($key) && javascriptEnabled()) {
        Layout::addJSHeader('<script type="text/javascript" src="' .
                PHPWS_SOURCE_HTTP . 'mod/block/javascript/popup/script.js"></script>',
                'blockpopupk');
        /*
          $val['address'] = sprintf('index.php?module=block&action=js_block_edit&key_id=%s&authkey=%s',
          $key->id, Current_User::getAuthkey());
          $val['label'] = dgettext('block', 'Add block here');
          $val['width'] = 750;
          $val['height'] = 650;
          MiniAdmin::add('block', javascript('open_window', $val));
         *
         */
        $content = '<a style="cursor:pointer" class="add-block">Add block here</a>';
        MiniAdmin::add('block', $content);
    }
}
?>
