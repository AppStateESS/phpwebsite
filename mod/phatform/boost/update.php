<?php

  /**
   * @version $Id: update.php 28 2006-11-17 17:02:42Z matt $
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function phatform_update(&$content, $version)
{
    switch ($version) {
    case version_compare($version, '3.0.2', '<'):
        $content[] = '- Fixed compatibility issues.';

    case version_compare($version, '3.0.3', '<'):
        $content[] = '- Fixed element move bug.';

    case version_compare($version, '3.0.4', '<'):
        $content[] = '<pre>
3.0.4 changes
-------------
+ Simplified install.sql
+ Fixed some incompatible errorMessage function calls
</pre>';
    }
    return true;
}

?>