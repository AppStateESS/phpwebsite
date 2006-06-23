<?php

  /**
   * @author Matthew McNaney
   * @version $Id$
   */

function related_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '0.1.0', '<'):
        $content[] = 'Made links XHTML compatible.';
    }

    return TRUE;
}

?>