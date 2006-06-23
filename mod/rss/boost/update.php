<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function rss_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '0.1.0', '<'):
        $content[] = '- Changed to binary safe file pull.';
        $content[] = '- Added system error checks and warnings.';
    }

    return true;
}

?>