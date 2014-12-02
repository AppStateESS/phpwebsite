<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
Block::show();

if (Current_User::allow('block')) {

    $key = Key::getCurrent();
    if (Key::checkKey($key) && javascriptEnabled()) {
        javascript('jquery');
        javascript('ckeditor');
        $js_address = PHPWS_SOURCE_HTTP . 'mod/block/javascript/addblock/script.js';
        Layout::addJSHeader('<script src="'.$js_address.'" type="text/javascript"></script>', 'addblock');

        $modal = new \Modal('block-form-modal', '<div id="block-form-dialog"></div>', 'Add block here');
        $modal->sizeLarge();
        $save_button = '<button class="btn btn-success" id="save-block">Save</button>';
        $modal->addButton($save_button);
        Layout::add((string)$modal);

        MiniAdmin::add('block', '<a style="cursor:pointer" data-auth-key="' . Current_User::getAuthKey() .
                '" data-key-id="'.$key->id.'" id="add-block"><i class="fa fa-plus"></i> Add block here</a>');
    }
}
?>
