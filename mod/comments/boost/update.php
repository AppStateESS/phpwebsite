<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function comments_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.2', '<'):
        $result = comments_update_002($content);
        if (PEAR::isError($result)) {
            return $result;
        } elseif (!$result) {
            return false;
        } else {
            $content[] = '+ Added a shortcut icon.';
        }

    case version_compare($currentVersion, '0.0.3', '<'):
        $result = comments_update_003($content);
        if (PEAR::isError($result)) {
            return $result;
        }

    case version_compare($currentVersion, '0.1.0', '<'):
        $content[] = '+ Changing over to Demographics module.';
        $result = comments_update_010($content);

    case version_compare($currentVersion, '0.2.0', '<'):
        $content[] = '+ Added anonymous tag.';
        $result = comments_update_020($content);
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


?>