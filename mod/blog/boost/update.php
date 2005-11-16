<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function blog_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.0.3', '<'):
        $result = blog_update_003();
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ added the author column';
        break;

    case version_compare($currentVersion, '0.0.4', '<'):
        $result = blog_update_004($content);
        if (PEAR::isError($result)) {
            return $result;
        }
        $content[] = '+ registered to rss';
        break;

    }
    return TRUE;
}


function blog_update_003()
{
    $filename = PHPWS_SOURCE_DIR . 'mod/blog/boost/update_0_0_3.sql';
    $db = & new PHPWS_DB;
    return $db->importFile($filename);
}


function blog_update_004(&$content)
{
    PHPWS_Core::initModClass('rss', 'RSS.php');
    return RSS::registerModule('blog', $content);
}


?>