<?php

if (Current_User::allow('pagesmith', 'edit_page')) {
    $vars = array(
        'aop' => 'pick_template',
        'tpl' => 'text_only',
        'pid' => 0);
    MiniAdmin::add('pagesmith',
            PHPWS_Text::secureLink(dgettext('pagesmith', '<span class="fa fa-file-text-o"></span> Create New Web Page'),
                    'pagesmith', $vars));
    $key = \Key::getCurrent();
    if (!empty($key) && !$key->isDummy() && $key->module == 'pagesmith') {
        $vars['aop'] = 'edit_page';
        $vars['id'] = $key->item_id;
        unset($vars['tpl']);
        unset($vars['pid']);
        MiniAdmin::add('pagesmith',
                PHPWS_Text::secureLink(dgettext('pagesmith',
                                '<span class="fa fa-pencil-square-o"></span> Edit current page'), 'pagesmith', $vars));
    }
}
?>
