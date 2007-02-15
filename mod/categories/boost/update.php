<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */


function categories_update(&$content, $currentVersion)
{

    switch ($currentVersion) {

    case version_compare($currentVersion, '2.1.5', '<'):
        $content[] = 'This package will not update versions under 2.1.5.';
        return false;

    case version_compare($currentVersion, '2.1.6', '<'):
        $content[] = 'Fix - Key unregisteration works properly.';

    case version_compare($currentVersion, '2.1.7', '<'):
        $files = array('templates/forms/edit.tpl', 'templates/menu_bar.tpl',
                       'templates/popup_menu.tpl');
        $content[] = '<pre>2.1.7 Changes';
        if (PHPWS_Boost::updateFiles($files, 'categories')) {
            $content[] = '+ Successfully updated the following files:';
        } else {
            $content[] = '+ Was unsuccessful updating the following files:';
        }
        $content[] = '    ' . implode("\n    ", $files);
        $content[] = '+ Removed table format from edit form
+ With quick creation of categories in place, no need for old warning
  message. Altered pop up box.
+ Added ability to add categories quickly on the assignment form
+ Category parent defaulted to zero instead of null
+ Updated files: templates/menu_bar.tpl, templates/popup_menu.tpl
+ Set the default listing to create date descending
+ Added translate functions.
+ Updated message translation files.</pre>';
    }
    return true;
}



?>