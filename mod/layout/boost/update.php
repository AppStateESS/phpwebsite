<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function layout_update(&$content, $currentVersion)
{
    switch ($currentVersion) {

        case version_compare($currentVersion, '2.2.0', '<'):
            $content[] = 'This package will not update versions under 2.2.0.';
            return false;

        case version_compare($currentVersion, '2.2.1', '<'):
            $content[] = '+ Fixed improper sql call in update_220.sql file.';

        case version_compare($currentVersion, '2.3.0', '<'):
            $content[] = '<pre>';
            if (PHPWS_Boost::updateFiles(array('conf/config.php', 'conf/error.php'), 'layout')) {
                $content[] = 'Updated conf/config.php and conf/error.php file.';
            } else {
                $content[] = 'Unable to update conf/config.php and conf/error.php file.';
            }
            $content[] = '
2.3.0 changes
-------------
+ Removed references from object constructors.
+ Added the plug function to allow users to inject content directly
  into a theme.
+ Added translate functions.
+ Layout now looks for and includes a theme\'s theme.php file.
+ Fixed unauthorized access.
+ Added XML mode to config.php file. Puts Layout in XHTML+XML content mode.
+ Added missing media parameters to XML mode.
</pre>';

        case version_compare($currentVersion, '2.4.0', '<'):
            $files = array('img/layout.png', 'templates/no_cookie.tpl');
            $content[] = '<pre>';
            if (PHPWS_Boost::updateFiles($files, 'layout')) {
                $content[] = '--- Successfully updated the following files:';
            } else {
                $content[] = '--- Was unable to copy the following files:';
            }
            $content[] = '     ' . implode("\n     ", $files);
            $content[] = '
2.4.0 changes
-------------
+ Layout now checks and forces a user to enable cookies on their
  browser.
+ Rewrote Javascript detection. Was buggy before as session
  destruction could disrupt it.
+ Added German translations
+ Updated language functions
+ Fixed: bug in Layout confused a user\'s style sheet settings after
  the theme was changed.
+ Rewrote theme change code.
+ Added ability to force theme on layout settings construction.
+ Changed Control Panel icon
</pre>';

        case version_compare($currentVersion, '2.4.1', '<'):
            $files = array('conf/config.php');
            $content[] = '<pre>';
            if (PHPWS_Boost::updateFiles($files, 'layout')) {
                $content[] = '--- Successfully updated the following files:';
            } else {
                $content[] = '--- Was unable to copy the following files:';
            }
            $content[] = '     ' . implode("\n     ", $files);
            $content[] = '
2.4.1 changes
-------------
+ Bug #1741111 - Fixed moving a top box up and a bottom box down.
+ The cookie check is not determined by a define in the config file.
+ The cookie check was interfering with the rss feed. Cut off the page
  too quickly. Moved cookie check to the close.php file.
</pre>';

        case version_compare($currentVersion, '2.4.2', '<'):
            $content[] = '<pre>';
            $files = array('templates/arrange.tpl', 'conf/error.php', 'templates/move_box_select.tpl');
            layoutUpdateFiles($files, $content);

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/layout/boost/changes/2_4_2.txt');
            }
            $content[] = '</pre>';

        case version_compare($currentVersion, '2.4.3', '<'):
            $content[] = '<pre>2.4.3 changes
-----------------
+ nakedDisplay now allows a choice whether to use the blank template
  or not when it wraps. Default is to not.
+ Fixed noCache.
</pre>';

        case version_compare($currentVersion, '2.4.4', '<'):
            $content[] = '<pre>';
            $db = new PHPWS_DB('layout_config');
            if (PHPWS_Error::logIfError($db->dropTableColumn('userAllow'))) {
                $content[] = '--- An error occurred when trying to drop the userAllow column from the layout_config table.';
            } else {
                $content[] = '--- Dropped the userAllow column from the layout_config table.';
            }

            layoutUpdateFiles(array('templates/user_form.tpl'), $content);
            $content[] = '2.4.4 changes
-------------------
+ Dropped unused column from config table.
+ Added collapse function. Adds id="layout-collapse" to theme template
  under the {COLLAPSE} tag.
+ Changed method of checking for javascript status. Less chance for
  error.
+ Fixed notice.</pre>';

        case version_compare($currentVersion, '2.4.5', '<'):
            $content[] = '<pre>';
            $db = new PHPWS_DB('layout_config');
            if (PHPWS_Error::logIfError($db->addTableColumn('deity_reload', 'smallint not null default 0'))) {
                $content[] = 'Could not create layout_config.deity_reload column.';
                return false;
            } else {
                $content[] = 'Added layout_config.deity_reload column.';
            }

            layoutUpdateFiles(array('templates/metatags.tpl', 'conf/config.php'), $content);
            $content[] = '2.4.5 changes
--------------------
+ Added option to use a Key\'s summary or title to fill in the meta
  description.
+ Added new cacheHeader function to retain javascript and css
  information should a module return cached content before the above
  can be established.
+ Deities can now move a box to a theme locked area.
+ Added LAYOUT_IGNORE_JS_CHECK to force javascript use.
+ PHP 5 formatted';

        case version_compare($currentVersion, '2.4.6', '<'):
            $content[] = '<pre>2.4.6 changes
---------------------
+ Fix to cache headers
</pre>';

        case version_compare($currentVersion, '2.4.7', '<'):
            $content[] = '<pre>';
            layoutUpdateFiles(array('templates/themes.tpl'), $content);
            $content[] = '2.4.7 changes
---------------------
+ Added option to layout theme tab to disable or order module style
  sheet inclusion.
+ Can enable box move from mini admin
</pre>';

        case version_compare($currentVersion, '2.4.8', '<'):
            $content[] = '<pre>2.4.8 changes
---------------------
+ Bug#2424256 - Removed browser check to use @import on style sheets.
</pre>';

        case version_compare($currentVersion, '2.5.0', '<'):
            $content[] = '<pre>2.5.0 changes
---------------------
+ Icon class used.
+ Change of template directories to conform with core hub/branch change.
+ PHP 5 strict fixes.
+ New javascript detection method.
+ Default theme is now simple.
+ Allow admin to use hub or local themes.
</pre>';

        case version_compare($currentVersion, '2.5.1', '<'):
            $content[] = '<pre>2.5.1 changes
---------------------
+ Eloi George javascript patch applied
</pre>';
        case version_compare($currentVersion, '2.5.2', '<'):
            $content[] = '<pre>2.5.2 changes
---------------------
+ Eloi George templating patch added.
+ Fixed silent javascript failure.
</pre>';

        case version_compare($currentVersion, '2.5.3', '<'):
            $content[] = '<pre>2.5.3 changes
---------------------
+ Added HTTP tag for theming. Assists with http vs https
+ HOME_URL also added for theming.
</pre>';


    }
    return true;
}

function layoutUpdateFiles($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'layout')) {
        $content[] = '--- Updated the following files:';
    } else {
        $content[] = '--- Unable to update the following files:';
    }
    $content[] = "     " . implode("\n     ", $files);
}
?>