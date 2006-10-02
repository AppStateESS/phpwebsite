<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function miniadmin_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '0.0.5', '<'):
        $content[] = 'Fixed XHTML incompatibilities.';

    case version_compare($version, '1.0.0', '<'):
        $content[] = '- Changed to allow the addition of multiple yet single link submissions.';
    }
    return true;
}

?>
