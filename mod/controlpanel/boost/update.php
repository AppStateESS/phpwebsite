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

    case version_compare($currentVersion, '2.0.2', '<'):
        $content[] = '<pre>2.0.2 Changes
------------
+ Fixed problem with unregister function.</pre>';

    case version_compare($currentVersion, '2.0.3', '<'):
        $content[] = '<pre>2.0.3 Changes
------------';
        $files = array('templates/style.css');
        $content[] = '+ Updated style.css to work better with IE/Safari (thanks singletrack).';
        if (!PHPWS_Boost::updateFiles($files, 'controlpanel')) {
            $content[] = 'Warning: style.css could not be copied locally to templates/controlpanel.';
        }
        $content[] = '+ Fixed problem with unregister function.</pre>';

    }
    return true;
}
