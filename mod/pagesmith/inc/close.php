<?php

if(Current_User::allow('pagesmith', 'edit_page')) {
    $vars = array(
        'aop' => 'pick_template',
        'tpl' => 'text_only',
        'pid' => 0);
    MiniAdmin::add('pagesmith', PHPWS_Text::secureLink(dgettext('pagesmith', 'Create New Web Page'), 'pagesmith', $vars));
}
?>
