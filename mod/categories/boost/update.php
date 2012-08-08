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
            PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
            $db = new PHPWS_DB('categories');
            $result = $db->select();

            if (PHPWS_Error::logIfError($result)) {
                $content[] = 'An error occurred when accessing your categories table.';
                return false;
            }

            $db->dropTable('categories');
            if (!$db->importFile(PHPWS_SOURCE_DIR . 'mod/categories/boost/categories.sql')) {
                $content[] = 'Could not import updated categories table.';
                return false;
            }

            if (!empty($result)) {
                foreach ($result as $cat) {
                    $cat['icon'] = (int) $cat['icon'];
                    $db->reset();
                    $db->addValue($cat);
                    PHPWS_Error::logIfError($db->insert(false));
                    $db->reset();
                }
            }
            $db->updateSequenceTable();
            $content[] = 'Updated categories.icon table column.';

            if (Cabinet::convertImagesToFileAssoc('categories', 'icon')) {
                $content[] = '--- Converted images to new File Cabinet format.';
            } else {
                $content[] = '--- Could not convert images to new File Cabinet format.</pre>';
                return false;
            }

            $content[] = '<pre>2.2.1 changes
----------------
+ Added getForm function to Categories.
+ Rewrote portions to work with File Cabinet.
+ Fixed two notices with error checks.</pre>';

        case version_compare($currentVersion, '2.2.2', '<'):
            $content[] = '<pre>';
            categoryUpdateFiles(array('templates/menu_bar.tpl', 'templates/style.css'), $content);
            $content[] = '2.2.2 changes
-----------------
+ Bug #1777242 - Shared bug with default themes. Made more
  presentable.
+ Altered default category tabs to fix some listing problems</pre>';

        case version_compare($currentVersion, '2.3.0', '<'):
            $content[] = '<pre>2.3.0 changes
-----------------
+ php 5 formatted.
+ Error notice fixed.
</pre>';

        case version_compare($currentVersion, '2.3.1', '<'):
            $content[] = '<pre>2.3.1 changes
-----------------
+ Icon class used.
+ PHP 5 Strict changes.</pre>';

        case version_compare($currentVersion, '2.3.2', '<'):
            $content[] = '<pre>2.3.2 changes
-----------------
+ Increased category list view to 100
+ Static call fixes.
</pre>';
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