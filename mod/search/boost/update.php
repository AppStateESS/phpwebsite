<?php
  /**
   * update file for search
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function search_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.2', '<'):
        $content[] = _('Add permissions to search.');
        search_update_002($content);
        return true;
        break;

    case version_compare($currentVersion, '0.0.3', '<'):
        $content[] = _('Register Search to Key.');
        search_update_003($content);
        return true;

    }
}

function search_update_002(&$content)
{
    PHPWS_Boost::registerMyModule('search', 'users', $content);
}

function search_update_003(&$content)
{
    $result = Key::registerModule('search');
    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = _('A problem occurred during the update.');
    }
}

?>