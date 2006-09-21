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

    case version_compare($version, '0.1.1', '<'):
        Key::registerModule('related');
        $content[] = 'Register module to key.';
    }

    return TRUE;
}

?>