<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
if (PHPWS_Settings::get('blog', 'home_page_display')) {
    if (!isset($_REQUEST['module'])) {
        $content = Blog_User::show();
        Layout::add($content, 'blog', 'view', TRUE);
    }
} else {
    Blog_User::showSide();
}


if (Current_User::allow('blog')) {
    Controlpanel::getToolbar()->addSiteOption('blog',
            PHPWS_Text::secureLink(dgettext('blog', 'Blog list'), 'blog',
                    array('action' => 'admin', 'tab' => 'list')));

    Controlpanel::getToolbar()->addCreateOption('blog',
            PHPWS_Text::secureLink(dgettext('blog', 'Create blog entry'),
                    'blog', array('action' => 'admin', 'tab' => 'new')));
}
?>