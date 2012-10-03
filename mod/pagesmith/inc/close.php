<?php

if(Current_User::allow('pagesmith', 'edit_page')) {
    $vars = array(
        'aop' => 'menu');
    MiniAdmin::add('pagesmith', PHPWS_Text::secureLink(dgettext('pagesmith', 'Create New Web Page'), 'pagesmith', $vars));
}

?>
