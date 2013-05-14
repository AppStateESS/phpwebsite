<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function layout_install(&$content, $branchInstall=FALSE)
{
    $page_title = 'My phpWebSite';
    $default_theme = 'bootstrap';

    if (!isset($error)) {
        $db = new PHPWS_DB('layout_config');
        $db->addValue('default_theme', $default_theme);
        $db->addValue('page_title', $page_title);
        $db->update();
        $content[] = dgettext('layout', 'Layout settings updated.');
        return true;
    } else {
        return $error;
    }
}
