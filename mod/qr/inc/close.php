<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
if (Current_User::isLogged()) {
    $key = Key::getCurrent(true);
    if ($key) {
        $form = new PHPWS_Form('create-qr');
        $form->setMethod('get');
        $form->addHidden('module', 'qr');
        $form->addHidden('id', $key->id);

        $sizes[5] = 'Small';
        $sizes[6] = 'Medium';
        $sizes[8] = 'Large';
        $sizes[12] = 'X-Large';
        $form->addSelect('size', $sizes);
        $form->setLabel('size', 'Size');

        $form->addSubmit('Show QR');

        $tpl = $form->getTemplate();
        $content = PHPWS_Template::process($tpl, 'qr', 'form.tpl');
        Layout::add($content, 'qr', 'qr');
    }
}
?>
