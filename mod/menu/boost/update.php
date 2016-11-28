<?php

/**
 * update file for menu
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
function menu_update(&$content, $currentVersion)
{
    $home_directory = PHPWS_Boost::getHomeDir();

    switch ($currentVersion) {
        case version_compare($currentVersion, '1.2.0', '<'):
            $content[] = '<pre>Menu versions prior to 1.2.0 are not supported for update.
Please download 1.2.1.</pre>';
            break;

        case version_compare($currentVersion, '1.2.1', '<'):
            $content[] = '<pre>1.2.1 changes
-----------------
+ Fixed bug with making home link.
</pre>';

        case version_compare($currentVersion, '1.3.0', '<'):
            $files = array('conf/config.php', 'templates/admin/settings.tpl',
                'templates/links/link.tpl', 'templates/popup_admin.tpl');
            $content[] = '<pre>';
            if (PHPWS_Boost::updateFiles($files, 'menu')) {
                $content[] = '--- Successfully updated the following files:';
            } else {
                $content[] = '--- Was unable to copy the following files:';
            }
            $content[] = '     ' . implode("\n     ", $files);
            $content[] = '
1.3.0 changes
-----------------
+ Admin icon for links is now clickable. Pulls up window of options.
+ Added ability to disable floating admin links.
</pre>';

        case version_compare($currentVersion, '1.3.1', '<'):
            $files = array('templates/site_map.tpl');
            $content[] = '<pre>';
            if (PHPWS_Boost::updateFiles($files, 'menu')) {
                $content[] = '--- Successfully updated the following files:';
            } else {
                $content[] = '--- Was unable to copy the following files:';
            }
            $content[] = '     ' . implode("\n     ", $files);
            $content[] = '
1.3.1 changes
-----------------
+ Bug # 1609737. Fixed site_map.tpl file. Thanks Andy.
</pre>';

        case version_compare($currentVersion, '1.4.0', '<'):
            $content[] = '<pre>';

            $basic_dir = $home_directory . 'templates/menu/menu_layout/basic/';
            $horz_dir = $home_directory . 'templates/menu/menu_layout/horizontal/';

            if (!is_dir($basic_dir)) {
                if (PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'mod/menu/templates/menu_layout/basic/',
                                $basic_dir)) {
                    $content[] = "--- Successfully copied directory: $basic_dir";
                } else {
                    $content[] = "--- Failed to copy directory: $basic_dir</pre>";
                    return false;
                }
            }

            if (!is_dir($horz_dir)) {
                if (PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'mod/menu/templates/menu_layout/horizontal/',
                                $horz_dir)) {
                    $content[] = "--- Successfully copied directory: $horz_dir";
                } else {
                    $content[] = "--- Failed to copy directory: $horz_dir</pre>";
                    return false;
                }
            }

            menuUpdateFiles(array('conf/error.php'), $content);

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/menu/boost/changes/1_4_0.txt');
            }

            $content[] = '</pre>';

        case version_compare($currentVersion, '1.4.1', '<'):
            $content[] = '<pre>';

            $files = array('templates/admin/settings.tpl', 'templates/admin/menu_list.tpl');
            menuUpdateFiles($files, $content);
            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/menu/boost/changes/1_4_1.txt');
            }

            $content[] = '</pre>';

        case version_compare($currentVersion, '1.4.2', '<'):
            $content[] = '<pre>';

            $db = new PHPWS_DB('menus');
            $db->addWhere('template', 'basic.tpl');
            $db->addValue('template', 'basic');
            if (PHPWS_Error::logIfError($db->update())) {
                $content[] = '--- Failed to update menus table.';
            } else {
                $content[] = '--- Updated menu table with correct template directory.';
            }

            $files = array('templates/admin/settings.tpl');
            menuUpdateFiles($files, $content);
            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/menu/boost/changes/1_4_2.txt');
            }

            $content[] = '</pre>';

        case version_compare($currentVersion, '1.4.3', '<'):
            $content[] = '<pre>';
            $files = array('templates/admin/settings.tpl');
            menuUpdateFiles($files, $content);
            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/menu/boost/changes/1_4_3.txt');
            }
            $content[] = '</pre>';

        case version_compare($currentVersion, '1.4.4', '<'):
            $content[] = '<pre>
1.4.4 Changes
--------------
+ Added three new menu functions:
  o quickLink - inserts a new link on any menu pinned on all pages;
                passed a title and url.
  o quickKeyLink - same as above but passed key_id
  o updateKeyLink - causes a link to reset its url, title, and active
                    status based on the condition of the current key
                    it is based on.</pre>';

        case version_compare($currentVersion, '1.4.5', '<'):
            $content[] = '<pre>
1.4.5 Changes
--------------
+ Fixed some submenus not appearing when sibling chosen.</pre>';

        case version_compare($currentVersion, '1.4.6', '<'):
            $content[] = '<pre>';
            menuUpdateFiles(array('templates/admin/menu_list.tpl'), $content);
            $content[] = '1.4.6 Changes
--------------
+ Update to addSortHeaders.
+ Adding missing paging navigation.</pre>';

        case version_compare($currentVersion, '1.5.0', '<'):
            $db = new PHPWS_DB('menu_links');
            PHPWS_Error::logIfError($db->alterColumnType('title',
                            'varchar(255) not null'));
            $files = array('templates/style.css',
                'templates/menu_layout/basic/menu.tpl',
                'templates/menu_layout/basic/link.tpl',
                'templates/menu_layout/horizontal/menu.tpl',
                'templates/admin/settings.tpl',
                'img/icon_indent.png',
                'img/icon_outdent.gif',
                'javascript/admin_link/',
                'conf/config.php'
            );

            $content[] = '<pre>';
            menuUpdateFiles($files, $content);
            $content[] = '1.5.0 Changes
--------------
+ RFE #2060159: Pin page link appears in miniadmin if admin mode is
  set to appear there.
+ Fixed bug #2079194. Deleting menu now removes links as well. Thanks
  Tommy.
+ Added option to expand menus when admin mode is enabled
+ Reworded menu admin link.
+ Added more ajax controls (add, delete, move) to the admin menu.
+ getTitle returns link without decoding it. Needed to prevent
  breakage with quotation marks.
+ Increased some popup window sizes
+ Fixed current link problem with unkeyed items.
+ Increased link title length in database.
</pre>';

        case version_compare($currentVersion, '1.5.1', '<'):
            $content[] = '<pre>1.5.1 changes
-------------------
+ Fixed menu preventing unpinning.
+ Default is now false for menu expansion in admin mode.</pre>';

        case version_compare($currentVersion, '1.5.2', '<'):
            $content[] = '<pre>';
            $files = array('templates/site_map.tpl', 'templates/menu_layout/basic/menu.tpl',
                'templates/menu_layout/horizontal/menu.tpl');
            menuUpdateFiles($files, $content);
            $content[] = '1.5.2 changes
---------------
+ Added Verdon\'s edit full menu sitemap
+ Removed duplicate pin page link in miniadmin
+ Wrapped default menu template in box-content div per patch by Obones
+ Local links created on key pages were not made current.
+ Commented out pin page link in template</pre>';

        case version_compare($currentVersion, '1.6.0', '<'):
            $db = new PHPWS_DB('menus');
            if (PHPWS_Error::logIfError($db->addTableColumn('key_id',
                                    'int not null default 0'))) {
                return false;
            }
            \phpws\PHPWS_Core::initModClass('menu', 'Menu_Item.php');
            $menus = $db->getObjects('Menu_Item');
            if (!empty($menus) && !PHPWS_Error::logIfError($menus)) {
                foreach ($menus as $m) {
                    $m->save();
                }
            }
            $content[] = '<pre>';
            $files = array('img/icon_outdent.gif',
                'conf/config.php',
                'javascript/admin_link/default.php',
                'javascript/admin_link/menu.js',
                'templates/admin/settings.tpl');
            menuUpdateFiles($files, $content);
            $content[] = '1.6.0 changes
---------------
+ Fixed bugs with popup menu.
+ Added "outdent" ability
+ Added ability to set view permissions on menus.
+ Added option to have add links always on.
</pre>';

        case version_compare($currentVersion, '1.6.1', '<'):
            $content[] = '<pre>';
            $files = array('templates/menu_layout/basic/link.tpl',
                'templates/menu_layout/horizontal/link.tpl',
                'templates/style.css',
                'conf/config.php',
                'img/icon_outdent.gif');
            menuUpdateFiles($files, $content);
            $content[] = '1.6.1 changes
---------------
+ Fixed up arrows and indent icons not appearing.
</pre>';

        case version_compare($currentVersion, '1.6.2', '<'):
            $content[] = '<pre>1.6.2 changes
---------------
+ Added file include for missing class.
+ Added missing indent tags to popup_admin.tpl</pre>
';

        case version_compare($currentVersion, '1.6.3', '<'):
            $content[] = '<pre>1.6.3 changes
---------------
+ View permission fix.
+ Icon class used.
+ PHP 5 strict fixes</pre>
';

        case version_compare($currentVersion, '1.6.4', '<'):
            $content[] = '<pre>1.6.4 changes
---------------
+ Bug fix with database initialization
+ Added code from Eloi George reducing DB queries</pre>
';
        case version_compare($currentVersion, '1.6.5', '<'):
            $content[] = '<pre>1.6.5 changes
----------------
+ Patch from Eloi that reduces database calls on menu creation.
+ Fixed notice bugs from link movement
</pre>';

        case version_compare($currentVersion, '1.6.6', '<'):
            $content[] = '<pre>1.6.6 changes
----------------
+ Fixed bug with javascript in menu admin.
+ Changed getUrl to return just the href and not a complete tag.
</pre>';

        case version_compare($currentVersion, '1.6.7', '<'):
            $content[] = '<pre>1.6.7 changes
----------------
+ Fixed bug with add link not appearing on home page
+ Re-ordered administrative options in "mouse hover" menu for menu link
+ Added additional conditional to prevent menu expansion on a blank url
</pre>';

        case version_compare($currentVersion, '1.6.8', '<'):
            $content[] = '<pre>1.6.8 changes
----------------
+ menu-link-href class added to the "a" menu tag.
+ Font Awesome links used in admin functionality.
+ Old graphic files removed.
+ Changed some working to make admin options more clear.
</pre>';

        case version_compare($currentVersion, '2.0.0', '<'):
            $db = \phpws2\Database::newDB();
            $tbl = $db->addTable('menus');
            $tbl->addDataType('queue', 'smallint')->add();

            $tbl->addField('id');
            $count = 0;
            while ($id = $db->selectColumn()) {
                $count++;
                $tbl->addValue('queue', $count);
                $tbl->addFieldConditional('id', $id);
                $db->update();
                $tbl->resetValues();
                $db->clearConditional();
            }

            $content[] = '<pre>2.0.0 changes
----------------
+ Rewrote large parts of administration
+ Category view for menus
</pre>';

        case version_compare($currentVersion, '2.0.1', '<'):
            $db = \phpws2\Database::newDB();
            $tbl = $db->addTable('menus');
            $tbl->addDataType('assoc_key', 'int')->add();
            $content[] = '<pre>2.0.1 changes
----------------
+ Can associate page to a menu
</pre>';

        case version_compare($currentVersion, '2.0.2', '<'):
            $db = \phpws2\Database::newDB();
            $tbl = $db->addTable('menus');
            $dt = $tbl->addDataType('assoc_url', 'varchar');
            $dt->setIsNull(true);
            $dt->add();
            $content[] = '<pre>2.0.2 changes
----------------
+ Can associate url to a menu
</pre>';
        case version_compare($currentVersion, '2.0.3', '<'):
            $new_directory = $home_directory . 'images/menu/';
            if (!is_dir($new_directory)) {
                $status = mkdir($new_directory);
                if (!$status) {
                    $content[] = "Could not create directory $new_directory.";
                    return false;
                }
            }

            $db = \phpws2\Database::newDB();
            $tbl = $db->addTable('menus');
            $dt = $tbl->addDataType('assoc_image', 'varchar');
            $dt->setIsNull(true);
            $dt->add();
            $content[] = "<pre>2.0.3 changes
----------------
+ Created $new_directory
+ Can associate image to a menu
</pre>";
        case version_compare($currentVersion, '2.0.4', '<'):
            $content[] = "<pre>2.0.4 changes
----------------
+ Fixed home icon set as active when menu associated page is chosen.
</pre>";

        case version_compare($currentVersion, '2.0.5', '<'):
            $content[] = "<pre>2.0.5 changes
----------------
+ Reset menu button now sorts all links as well
+ Menu images not loaded if tablet width or below
</pre>";
        case version_compare($currentVersion, '2.1.0', '<'):
            $content[] = <<<EOF
<pre>2.1.0 changes
-----------------
+ Created settings icon for display type and character limit.
+ If PageSmith creates a menu link, an access shortcut will be used if it exists.
+ Removed old icons.
+ Menu assoc_url will pull shortcut if it exists for page.
+ Added button to shortcut all links.
+ Menu can copy carousel images.
+ Added ability for menu links to have external and pdf icons.
+ Fixed: local links changes into bad http urls.
+ Fixed: Menu images will work on classic menus.
+ Fixed: Theme template style sheets not working.
+ Fixed: isDummy check was not set for home page.
+ Added Bootstrap icons
+ Fixed: Menu link no getting saved if PageSmith pages are not available.
+ Fixed: Home link highlighted with other links.
+ Fixed: Drop down options not working in MiniAdmin
</pre>           
EOF;
    }
    return true;
}

function menuUpdateFiles($files, &$content)
{
    $result = PHPWS_Boost::updateFiles($files, 'menu', true);
    if ($result === true) {
        $content[] = '--- Updated the following files:';
        $content[] = "     " . implode("\n     ", $files);
    } elseif (is_array($result)) {
        $content[] = '--- Unable to update the following files:';
        $content[] = "     " . implode("\n     ", $result);
    }
}

