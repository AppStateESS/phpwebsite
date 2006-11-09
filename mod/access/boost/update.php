<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function access_update(&$content, $version)
{
    switch (1) {
    case version_compare($version, '0.1.0', '<'):
        if (PHPWS_Boost::updateFiles(array('conf/config.php'), 'access')) {
            $content[] = '- Copied config.php locally.';
        } else {
            $content[] = '- Unable to copy config.php locally.';
        }
        $content[] = '- Added rewrite conditionals to .htaccess write.';
    }

    return true;
}

?>