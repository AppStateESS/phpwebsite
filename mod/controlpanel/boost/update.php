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
        if (PHPWS_Boost::updateFiles($files, 'controlpanel')) {
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

    case version_compare($currentVersion, '2.1.1', '<'):
        $files = array('templates/link_form.tpl','templates/panelList.tpl','templates/tab_form.tpl', 'img/controlpanel.png', 'templates/style.css');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'controlpanel')) {
            $content[] = '-- Successfully updated the following files:';
        } else {
            $content[] = '-- Unable to update the following files:';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '
2.1.1 Changes
--------------
+ Put Link.php and Tab.php back in to the inc/init.php. They are
  needed for one of the sessions.
+ Bug #1665174 - Added coding for control panel to try and use the correct master tab
  depending on which module you are using. Thanks Shaun.
+ Removed panel width from style.css. Caused problems in panels.
+ Corrected file copy from previous version update. They were labeled as calendar files.
</pre>';
    }
    return true;
}
