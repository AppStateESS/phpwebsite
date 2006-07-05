<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function comments_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.2.0', '<'):
        $content[] = '+ Added anonymous tag.';
        if(!comments_update_020($content)) {
            return FALSE;
        }

    case version_compare($currentVersion, '0.2.5', '<'):
        $content[] = '+ Added quote next to reply tags. Template update.';
        if (!comments_update_025($content)) {
            return FALSE;
        }

    case version_compare($currentVersion, '0.2.6', '<'):
        $content[] = '+ Fixed occurrences where anonymous users could post on restricted threads.';

    case version_compare($currentVersion, '0.2.7', '<'):
        $content[] = '+ Comments without subject will use COMMENT_NO_SUBJECT define.';
        if (PHPWS_Boost::updateFiles(array('conf/config.php'), 'comments')) {
            $content[] = 'New config.php file copied locally.';
        } else {
            $content[] = 'New config.php file failed to copy locally.';
        }
    }
            
    return TRUE;
}


function comments_update_002(&$content)
{
    PHPWS_Core::initModClass('controlpanel', 'ControlPanel.php');
    if (!@mkdir('images/mod/comments')) {
        $content[] = 'Unable to create image directory.';
        return FALSE;
    }
    return PHPWS_ControlPanel::registerModule('comments', $content);
}

function comments_update_003(&$content) {
    $content[] = 'Update control panel link.';
    $db = & new PHPWS_DB('controlpanel_link');
    $db->addWhere('itemname', 'comments');
    $db->addValue('url', 'index.php?module=comments&admin_action=admin_menu');
    return $db->update();
}

function comments_update_010(&$content)
{
    $db = & new PHPWS_DB('comments_users');
    
    $result = $db->dropTableColumn('signature');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
    }
    $result = $db->dropTableColumn('picture');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
    }

    $result = $db->dropTableColumn('contact_email');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
    }

    $result = $db->dropTableColumn('website');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
    }

    $result = $db->dropTableColumn('location');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
    }
    return TRUE;
}

function comments_update_020(&$content) {
    $db = & new PHPWS_DB('comments_threads');
    $result = $db->addTableColumn('allow_anon', 'smallint NOT NULL default \'0\'');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = 'There was a problem adding the allow_anon column.';
        return false;
    }
    return true;
}

function comments_update_025(&$content) {
    $files[] = 'templates/alt_view.tpl';
    $files[] = 'templates/alt_view_one.tpl';
    $files[] = 'templates/view.tpl';
    $files[] = 'templates/view_one.tpl';
    $files[] = 'templates/style.css';
    $result = PHPWS_Boost::updateFiles($files, 'comments');
    if (!$result) {
        $content[] = 'Failed to copy template files locally.';
        return false;
    }
    
    return true;
}


?>