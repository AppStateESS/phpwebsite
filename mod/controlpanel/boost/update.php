<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function controlpanel_update(&$content, $currentVersion)
{
    switch (1) {
    case version_compare($currentVersion, '2.0.3', '<'):
        $content[] = '<pre>';
        $files = array('templates/style.css');
        cpFileUpdate($files, $content);

        $content[] = '2.0.3 Changes
------------
+ Updated style.css to work better with IE/Safari (thanks singletrack).
+ Fixed problem with unregister function.
+ Added translate functions.
</pre>';

    case version_compare($currentVersion, '2.1.0', '<'):
        $files = array('templates/link_form.tpl','templates/panelList.tpl','templates/tab_form.tpl', 'img/controlpanel.png');
        $content[] = '<pre>';
        cpFileUpdate($files, $content);
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
        cpFileUpdate($files, $content);
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

    case version_compare($currentVersion, '2.1.2', '<'):
        $content[] = '<pre>';
        cpFileUpdate(array('templates/style.css'), $content);
        $content[] = '2.1.2 changes
----------------
+ Control panel will now direct a user to login if their session times
  out during panel view.
+ Changed icon image layout a touch to try and make more space.
+ A tab link can be now be sent in strict mode to prevent appending.
</pre>';

    case version_compare($currentVersion, '2.1.3', '<'):
        $content[] = '<pre>';
        $content[] = '2.1.3 changes
----------------
+ Developers can now add a link_title to quickSetTabs. This allows the
  user to mouse over tabs to get extended information.
</pre>';

    case version_compare($currentVersion, '2.1.4', '<'):
        $content[] = '<pre>';
        cpFileUpdate(array('templates/default.tpl'), $content);
        $content[] = '2.1.4 changes
----------------
+ Panel::display now includes the options to send content, title, and
  message information to be put in a default template.
</pre>';

    case version_compare($currentVersion, '2.1.5', '<'):
        $content[] = '<pre>';
        cpFileUpdate(array('templates/default.tpl'), $content);
        $content[] = '2.1.5 changes
----------------
+ Commented out title in default template.
+ A module can force a control panel tab using the second parameter in
  the display function.
</pre>';

    case version_compare($currentVersion, '2.1.6', '<'):
        $content[] = '<pre>';
        cpFileUpdate(array('templates/style.css'), $content);
        $content[] = '2.1.6 changes
----------------
+ Changed tab formatting. The a tag is padded to make clickable area
  larger.</pre>';


    }
    return true;
}

function cpFileUpdate($files, &$content) {
    if (PHPWS_Boost::updateFiles($files, 'controlpanel')) {
        $content[] = '-- Successfully updated the following files:';
    } else {
        $content[] = '-- Unable to update the following files:';
    }
    $content[] = '    ' . implode("\n    ", $files);
}

?>