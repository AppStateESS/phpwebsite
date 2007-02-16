<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function controlpanel_update(&$content, $currentVersion)
{
    switch (1) {
    case version_compare($currentVersion, '2.0.1', '<'):
        $content[] = '<pre>2.0.1 Changes
------------
+ Added translate functions.</pre>';
    }
    return true;
}
