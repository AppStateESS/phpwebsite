<?php

if (Current_User::allow('pagesmith', 'edit_page')) {
    $vars = array(
        'aop' => 'pick_template',
        'tpl' => 'text_only',
        'pid' => 0);
    MiniAdmin::add('pagesmith',
            \phpws\PHPWS_Text::secureLink('<i class="fa fa-file-text-o"></i> ' . dgettext('pagesmith',
                            'Create New Web Page'), 'pagesmith', $vars));
    $key = \Canopy\Key::getCurrent();
    if (!empty($key) && !$key->isDummy() && $key->module == 'pagesmith') {
        $vars['aop'] = 'edit_page';
        $vars['id'] = $key->item_id;
        unset($vars['tpl']);
        unset($vars['pid']);
        MiniAdmin::add('pagesmith',
                \phpws\PHPWS_Text::secureLink('<i class="fa fa-pencil-square-o"></i> ' . dgettext('pagesmith',
                                'Edit current page'), 'pagesmith', $vars));
    }
}
