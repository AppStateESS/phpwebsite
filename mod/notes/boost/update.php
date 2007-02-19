<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function notes_update(&$content, $version) {

    switch ($version) {
    case version_compare($version, '0.1.2', '<'):
        $content[] = 'This package does not update versions under 0.1.2.';
        return false;

    case version_compare($version, '0.1.3', '<'):
        $content[] = '<pre>
0.1.3 changes
--------------
+ Added translate functions.
</pre>
';
    }

    return true;
}

?>