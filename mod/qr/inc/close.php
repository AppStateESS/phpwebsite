<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
if (Current_User::isLogged()) {
    $key = Key::getCurrent(true);
    if ($key) {
        $links = array();
        $vars  = array();
        $windowVars = array();
        $vars['id'] = $key->id;

        $windowVars['width']  = 500;
        $windowVars['height'] = 500;

        $vars['size'] = 5;
        $windowVars['label'] = dgettext('qr', 'Small');
        $windowVars['address'] = PHPWS_Text::linkAddress('qr', $vars);
        $links[] = javascript('open_window', $windowVars);

        $vars['size'] = 6;
        $windowVars['label'] = dgettext('qr', 'Medium');
        $windowVars['address'] = PHPWS_Text::linkAddress('qr', $vars);
        $links[] = javascript('open_window', $windowVars);

        $vars['size'] = 8;
        $windowVars['label'] = dgettext('qr', 'Large');
        $windowVars['address'] = PHPWS_Text::linkAddress('qr', $vars);
        $links[] = javascript('open_window', $windowVars);

        $vars['size'] = 12;
        $windowVars['label'] = dgettext('qr', 'X-Large');
        $windowVars['address'] = PHPWS_Text::linkAddress('qr', $vars);
        $links[] = javascript('open_window', $windowVars);

        MiniAdmin::add('qr', $links);
    }
}
?>
