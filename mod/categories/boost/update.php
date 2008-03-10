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
+ Updated message translation files.
+ Increase category list size limits.</pre>';

   case version_compare($currentVersion, '2.1.8', '<'):
       PHPWS_Boost::updateFiles(array('img/categories.png'), 'categories');
       $content[] = '<pre>
2.1.8 Changes
-------------------
+ Updated language format.
+ New icon.
</pre>';

    case version_compare($currentVersion, '2.1.9', '<'):
        $content[] = '<pre>';
        $files = array('templates/list.tpl', 'templates/style.css');
        categoryUpdateFiles($files, $content);
        $content[] = '2.1.9 changes
----------------
+ Rewrote and simplified category horizontal menu.</pre>';

    case version_compare($currentVersion, '2.1.10', '<'):
        $content[] = '<pre>';
        PHPWS_Boost::registerMyModule('categories', 'users', $content);
        $content[] = '2.1.10 changes
----------------
+ Fixed: Permissions were not being used.
+ Added Vietnamese translations.
</pre>';

    case version_compare($currentVersion, '2.2.0', '<'):
        $content[] = '<pre>';
        $files = array('templates/style.css');
        categoryUpdateFiles($files, $content);
        $content[] = '2.2.0 changes
----------------
+ Added error check to category icon search.
+ Added getIcons function.</pre>';

    case version_compare($currentVersion, '2.2.1', '<'):
        $content[] = '<pre>2.2.1 changes
----------------
+ Added getForm function to Categories.
+ Rewrote portions to work with File Cabinet.
+ Fixed two notices with error checks.</pre>';
    }
    return true;
}

function categoryUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'categories')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "     " . implode("\n     ", $files);
}


?>