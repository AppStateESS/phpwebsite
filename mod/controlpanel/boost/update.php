<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function controlpanel_update(&$content, $currentVersion)
{
    switch (1) {
    case version_compare($currentVersion, '2.0.3', '<'):
        $content[] = '<pre>2.0.3 Changes
------------';
        $files = array('templates/style.css');
        $content[] = '+ Updated style.css to work better with IE/Safari (thanks singletrack).';
        if (!PHPWS_Boost::updateFiles($files, 'controlpanel')) {
            $content[] = 'Warning: style.css could not be copied locally to templates/controlpanel.';
        }
        $content[] = '+ Fixed problem with unregister function.
+ Added translate functions.
</pre>';

    case version_compare($currentVersion, '2.1.0', '<'):
        $files = array('templates/link_form.tpl','templates/panelList.tpl','templates/tab_form.tpl', 'img/controlpanel.png');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'calendar')) {
            $content[] = '-- Successfully updated the following files:';
        } else {
            $content[] = '-- Unable to update the following files:';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '
2.1.0 Changes
--------------
+ RFE 1665181 - Can now edit control panel tabs and links.
+ Updated language functions.
+ Added German translation.
+ Removed border from icons. not xhtml compliant
+ changed style sheet to work under IE7 again
+ Changed control panel icon
</pre>';
    }
    return true;
}
