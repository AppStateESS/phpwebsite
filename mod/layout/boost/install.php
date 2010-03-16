<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function layout_install(&$content, $branchInstall=FALSE)
{
    // Removed response install
    /*
    if (isset($_POST['process_layout'])) {
    if (empty($_POST['page_title'])) {
    $error  = dgettext('layout', 'Please enter a page title.');
    } else {
    $page_title = strip_tags($_POST['page_title']);
    $default_theme = $_POST['theme'];
    }
    */
    $page_title = 'My phpWebSite';
    $default_theme = 'default';

    if (!isset($error)) {
        $db = new PHPWS_DB('layout_config');
        $db->addValue('default_theme', trim($default_theme));
        $db->addValue('page_title', $page_title);
        $db->update();
        $content[] = dgettext('layout', 'Layout settings updated.');
        return true;
    } else {
        return $error;
    }
}
