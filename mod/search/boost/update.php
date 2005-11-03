<?php

function search_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.2', '<'):
        $content[] = _('Add permissions to search.');
        search_update_002($content);
        return true;
    }
}

function search_update_002(&$content)
{
    PHPWS_Boost::registerMyModule('search', 'users', $content);
}

?>