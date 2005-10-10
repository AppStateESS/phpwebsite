<?php

function categories_update(&$content, $currentVersion)
{

    switch ($currentVersion) {
    case version_compare($currentVersion, '2.1.0', '<'):
        $result = cat_update_210();
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ changed "link" column to "url"';
        break;
    }
    return TRUE;
}

function cat_update_210()
{
    $db = & new PHPWS_DB;
    return $db->importFile(PHPWS_SOURCE_DIR . 'mod/categories/boost/update_2_1_0.sql');
}


?>