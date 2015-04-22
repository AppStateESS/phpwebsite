<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
if (Current_User::isLogged()) {
    $key = Key::getCurrent(true);
    if ($key) {

        $qr_func = function($key_id, $size) {
            $qr = new QR($key_id);
            $qr->setSize($size);
            $image = $qr->get();
            return '<a download="QR-image.png" href="' . $qr->image. '">' . $qr->get() . '</a>';
        };
        
        $tpl_vars['small'] = $qr_func($key->id, 5);
        $tpl_vars['medium'] = $qr_func($key->id, 6);
        $tpl_vars['large'] = $qr_func($key->id, 8);
        $tpl_vars['xlarge'] = $qr_func($key->id, 12);

        $tpl = new \Template($tpl_vars);
        $tpl->setModuleTemplate('qr', 'modal.html');
        $content = $tpl->get();
        $modal = new Modal('qr-modal', $content, 'QR Codes (click to download)');
        $modal->sizeLarge();
        Layout::add($modal->get());

        MiniAdmin::add('qr', '<a data-toggle="modal" data-target="#qr-modal" class="pointer">Show QR codes</a>');
    }
}
?>
