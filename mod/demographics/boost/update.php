<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

function demographics_update(&$content, $version)
{
    switch (1) {
    case version_compare($version, '0.0.2', '<'):
        $content[] = '<pre>0.0.2 changes
--------------
+ Added translate functions</pre>';

    case version_compare($version, '0.1.0', '<'):
        $content[] = '<pre>0.1.0 changes
--------------
+ Updated language functions
+ Added German translation</pre>';

    }
    return true;
}

?>