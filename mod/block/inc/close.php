<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
Block::show();

if (Current_User::allow('block')) {

    $key = Key::getCurrent();
    if (Key::checkKey($key) && javascriptEnabled()) {
        javascript('editors/ckeditor');
        $js_address = PHPWS_SOURCE_HTTP . 'mod/block/javascript/addblock/script.js';
        javascript('jquery');
        javascript('jquery_ui');
        Layout::addJSHeader('<script src="'.$js_address.'" type="text/javascript"></script>', 'addblock');

        Layout::add('<div id="block-form-dialog" style="display : none"></div>');
        MiniAdmin::add('block', '<a style="cursor:pointer" data-auth-key="' . Current_User::getAuthKey() .
                '" data-key-id="'.$key->id.'" id="add-block">Add block here</a>');
    }
}
?>
