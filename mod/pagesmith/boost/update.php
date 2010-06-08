<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */


function pagesmith_update(&$content, $currentVersion)
{
    $home_dir = PHPWS_Boost::getHomeDir();

    switch ($currentVersion) {
        case version_compare($currentVersion, '1.0.1', '<'):
            $content[] = '<pre>';

            $db = new \core\DB('ps_page');
            $result = $db->addTableColumn('front_page', 'smallint NOT NULL default 0');
            if (core\Error::logIfError($result)) {
                $content[] = "--- Unable to create table column 'front_page' on ps_page table.</pre>";
                return false;
            } else {
                $content[] = "--- Created 'front_page' column on ps_page table.";
            }
            $files = array('templates/page_list.tpl', 'javascript/update/head.js', 'conf/error.php',
                       'templates/page_templates/simple/page.tpl', 'templates/page_templates/twocolumns/page.tpl');
            pagesmithUpdateFiles($files, $content);

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/boost/changes/1_0_1.txt');
            }
            $content[] = '</pre>';

        case version_compare($currentVersion, '1.0.2', '<'):
            $content[] = '<pre>';
            $dest_dir   = $home_dir . 'javascript/modules/pagesmith/passinfo/';

            if (!is_dir($dest_dir)) {
                if (is_writable($home_dir . 'javascript/modules/pagesmith/') && @mkdir($dest_dir)) {
                    $content[] = '--- SUCCEEDED creating "javascript/modules/passinfo/" directory.';
                } else {
                    $content[] = 'PageSmith 1.0.2 requires the javascript/modules/pagesmith/ directory be writable.</pre>';
                    return false;
                }
            } elseif (!is_writable($dest_dir)) {
                $content[] = 'PageSmith 1.0.2 requires the javascript/modules/pagesmith/passinfo/ directory be writable.</pre>';
                return false;
            }

            $source_dir = PHPWS_SOURCE_DIR . 'mod/pagesmith/javascript/passinfo/';
            if (!core\File::copy_directory($source_dir, $dest_dir)) {
                $content[] = "--- FAILED copying to $dest_dir.</pre>";
                return false;
            } else {
                $content[] = "--- SUCCEEDED copying to $dest_dir directory.";
            }

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/boost/changes/1_0_2.txt');
            }
            $content[] = '</pre>';

        case version_compare($currentVersion, '1.0.3', '<'):
            $content[] = '<pre>';

            $source_dir = PHPWS_SOURCE_DIR . 'mod/pagesmith/templates/page_templates/text_only/';
            $dest_dir   = $home_dir . 'templates/pagesmith/page_templates/text_only/';

            if (core\File::copy_directory($source_dir, $dest_dir)) {
                $content[] = "--- Successfully copied $source_dir\n    to $dest_dir\n";
            } else {
                $content[] = "--- Failed to copy $source_dir to $dest_dir</pre>";
                return false;
            }
            $files = array('conf/config.php');
            pagesmithUpdateFiles($files, $content);

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/boost/changes/1_0_3.txt');
            }
            $content[] = '</pre>';

        case version_compare($currentVersion, '1.0.4', '<'):
            $content[] = '<pre>';
            $db = new \core\DB('phpws_key');
            $db->addWhere('module', 'pagesmith');
            $db->addValue('edit_permission', 'edit_page');
            if (core\Error::logIfError($db->update())) {
                $content[] = 'Unable to update phpws_key table.</pre>';
                return false;
            } else {
                $content[] = 'Updated phpws_key table.';
            }
            $content[] = '1.0.4 changes
------------------
+ Fixed pagesmith edit permission.
+ PageSmith home pages were missing edit link.</pre>';

        case version_compare($currentVersion, '1.0.5', '<'):
            $content[] = '<pre>';
            pagesmithUpdateFiles(array('templates/page_templates/text_only/page.tpl'), $content);
            $content[] ='1.0.5 changes
----------------
+ Changed wording on move to front functionality
+ Added move to front to miniadmin
+ Fixed text_only template. Missing closing div tag.
</pre>';

        case version_compare($currentVersion, '1.0.6', '<'):
            $content[] = '<pre>
1.0.6 changes
-------------
+ Small fix to allow linkable images on cached pages.
</pre>';

        case version_compare($currentVersion, '1.0.7', '<'):
            $content[] = '<pre>';
            pagesmithUpdateFiles(array('templates/settings.tpl'), $content);

            $content[] = '1.0.7 changes
-------------
+ PageSmith can be set to automatically create a link when a new page
  is created.
+ Changing a page title now updates the menu link.
</pre>';

        case version_compare($currentVersion, '1.1.0', '<'):
            \core\Core::initModClass('filecabinet', 'Cabinet.php');
            $content[] = '<pre>';
            Cabinet::convertImagesToFileAssoc('ps_block', 'type_id');
            $content[] = '--- Images converted for File Cabinet 2.0.0.';
            pagesmithUpdateFiles(array('javascript/passinfo/head.js',
                                   'templates/page_templates/threesec/page.css',
                                   'templates/page_templates/threesec/page.tpl',
                                   'templates/page_templates/threesec/structure.xml',
                                   'templates/page_templates/threesec/threesec.png'), $content);

            $content[] = '1.1.0 changes (unreleased)
-------------
+ PageSmith conforms to new File Cabinet update.
+ Added url parser to passinfo script to allow images to work with fck better.
</pre>';

        case version_compare($currentVersion, '1.2.1', '<'):
            $content[] = '<pre>';

            $source_tpl = PHPWS_SOURCE_DIR . 'mod/pagesmith/templates/page_templates/';
            $local_tpl  = $home_dir . 'templates/pagesmith/page_templates/';
            $backup     = $home_dir . 'templates/pagesmith/_page_templates/';
            $source_img = PHPWS_SOURCE_DIR . 'mod/pagesmith/img/folder_icons/';
            $local_img  = $home_dir . 'images/mod/pagesmith/folder_icons/';

            if (is_dir($backup) || @core\File::copy_directory($local_tpl, $backup)) {
                $content[] = '--- Local page templates backed up to: ' . $backup;
            } else {
                $content[] = sprintf('--- Could not backup directory "%s" to "%s"</pre>', $local_tpl, $backup);
                return false;
            }

            if (@core\File::copy_directory($source_tpl, $local_tpl)) {
                $content[] = '--- Local page templates updated.';
            } else {
                $content[] = sprintf('--- Could not copy directory "%s" to "%s"</pre>', $source_tpl, $local_tpl);
                return false;
            }

            if (@core\File::copy_directory($source_img, $local_img)) {
                $content[] = '--- New page template icons copied locally.';
            } else {
                $content[] = sprintf('--- Could not copy directory "%s" to "%s"</pre>', $source_img, $local_img);
                return false;
            }

            if (!pagesmithSearchIndex()) {
                $content[] = '--- Unable to index pages in search. Check your error log.</pre>';
                return false;
            } else {
                $content[] = '--- Pages added to search';
            }

            $files = array('templates/pick_folder.tpl', 'templates/pick_template.tpl',
                       'templates/style.css', 'conf/folder_icons.php');
            pagesmithUpdateFiles($files, $content);


            $content[] = '1.2.1 changes
----------------
+ PageSmith now allows the sorting of templates
+ Page titles now added to search.
+ Wrong page ids don\'t 404. Send to message page.
+ Search indexing added to update and version raised.
+ Added search to pagesmith.
+ Changed to new url rewriting method.</pre>';

        case version_compare($currentVersion, '1.2.2', '<'):
            $content[] = '<pre>';
            $files = array('templates/page_list.tpl');
            pagesmithUpdateFiles($files, $content);
            $content[] = '
1.2.2 changes
---------------
+ Updated pagers to addSortHeaders.
+ Fixed direct access to page allowing view.
+ Front page does not alter page title.
+ Fixed some notices and a caching bug.
+ Changed wording on edit text windows.</pre>';

        case version_compare($currentVersion, '1.3.0', '<'):
            $db = new \core\DB('ps_block');
            $db->dropTableColumn('btype');

            $db = new \core\DB('ps_page');
            if (core\Error::logIfError($db->addTableColumn('parent_page', 'int NOT NULL default 0'))) {
                $content[] = 'Could not create ps_page.parent_page column.';
                return false;
            }

            if (core\Error::logIfError($db->addTableColumn('page_order', 'smallint NOT NULL default 0'))) {
                $content[] = 'Could not create ps_page.page_order column.';
                return false;
            }

            $db = new \core\DB('ps_text');

            if (core\DB::getDBType() == 'mysql' ||
            \core\DB::getDBType() == 'mysqli') {
                if (core\Error::logIfError($db->alterColumnType('content', 'longtext NOT NULL'))) {
                    $content[] = 'Could not alter ps_text.content column.';
                }
            }

            $content[] = '<pre>';
            $files = array('javascript/passinfo/head.js', 'templates/page_form.tpl',
                       'javascript/delete_orphan/',
                       'javascript/confirm_delete/',
                       'javascript/update/head.js',
                       'templates/page_templates/threesec-tbl/',
                       'templates/orphans.tpl',
                       'templates/page_form.tpl',
                       'templates/page_frame.tpl',
                       'templates/page_list.tpl',
                       'templates/style.css',
                       'templates/sublist.tpl',
                       'templates/upload_template.tpl',
                       'img/add.png', 'img/delete.png', 
                       'img/back.png', 'img/front.png'
                       );
                       pagesmithUpdateFiles($files, $content);

                       if (!PHPWS_Boost::inBranch()) {
                           $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/boost/changes/1_3_0.txt');
                       }
                       $content[] = '</pre>';

        case version_compare($currentVersion, '1.3.1', '<'):
            $content[] = '<pre>';
            $files = array('templates/page_templates/threesec/page.tpl',
                       'templates/page_templates/threesec-tbl/page.tpl',
                       'templates/settings.tpl');
            pagesmithUpdateFiles($files, $content);
            $content[] = '1.3.1 changes
---------------
+ Page cache refreshed on page save.
+ Updated threesec templates to conform with norm box-title,
  box-content layout
+ Added ability to lengthen or shorten pagesmith links.
+ Added fix so edit mode does not parse smarttags.</pre>';

        case version_compare($currentVersion, '1.3.2', '<'):
            $content[] = '<pre>';
            Users_Permission::registerPermissions('pagesmith', $content);
            pagesmithUpdateFiles(array('templates/page_templates/'), $content);

            $content[] = '1.3.2 changes
----------------
+ Update was missing a permission update
+ Wrong permission getting called on settings
+ All page templates now have a class called pagesmith-page
+ Removed padding from page templates</pre>';

        case version_compare($currentVersion, '1.3.3', '<'):
            $db = new \core\DB('ps_text');
            if (core\Error::logIfError($db->alterColumnType('content', 'longtext'))) {
                $content[] = 'Could not alter ps_text.content column.';
                return false;
            } else {
                $content[] = 'Updated ps_text.content column';
            }
            $content[] = '<pre>';
            pagesmithUpdateFiles(array('javascript/disable_links/',
                                   'javascript/update/head.js'), $content);
            $content[] = 'Version 1.3.3
--------------------------------------------------------------------
+ Made ps_text.content null instead of not null
+ Made a change in page editing. Text spaces receive text without
  formatting. Prior to this change the parseOutput was run before
  sending the data to the editor. This stripped data that may need
  editing.
  Now the text is sent to the editor without processing. After post
  the text IS processed. This fixed the filters. Anchors will be
  busted AFTER the edit post but I don\'t think they really need to
  work in edit mode.
+ Added javascript to prevent accidental link clicks in edit mode.
+ change_link was an id, changed to a class since there were several
  on a page.</pre>';

        case version_compare($currentVersion, '1.3.4', '<'):
            $content[] = '<pre>1.3.4 changes
-------------
+ Fixed link shortening
+ Restored missing placeholder width and height
+ Fixed lost text bug.
</pre>';

        case version_compare($currentVersion, '1.4.0', '<'):
            $content[] = '<pre>1.4.0 changes
-------------
+ Icon class implemented.
+ Fixed on disableLinks script.
+ Added black page check on saving. Prohibits empty content from being saved to the database.
+ Added option to turn off "Back to top" links.
+ Added default installation page.
+ PHP 5 strict fixes.</pre>';

    } // end switch

    return true;
}

function pagesmithUpdateFiles($files, &$content)
{
    $result = PHPWS_Boost::updateFiles($files, 'pagesmith', true);

    $content[] = '--- Updated the following files:';
    $content[] = "    " . implode("\n    ", $files);

    if (is_array($result)) {
        $content[] = '--- Unable to update the following files:';
        $content[] = "    " . implode("\n    ", $result);
    }

    $content[] = '';
}

/**
 * Versions prior to 1.1.0 didn't have search. This function
 * plugs in values for all current text sections.
 */
function pagesmithSearchIndex()
{
    \core\Core::initModClass('search', 'Search.php');
    $db = new \core\DB('ps_text');
    $db->addColumn('id');
    $db->addColumn('content');
    $db->addColumn('ps_page.key_id');
    $db->addColumn('ps_page.title');
    $db->addWhere('ps_text.pid', 'ps_page.id');
    $db->addOrder('pid');
    $db->addOrder('secname');
    $result = $db->select();

    if (!empty($result)) {
        if (core\Error::logIfError($result)) {
            return false;
        }
        foreach ($result as $pg) {
            $search = new Search($pg['key_id']);
            $search->addKeywords($pg['content']);
            $search->addKeywords($pg['title']);
            \core\Error::logIfError($search->save());
        }
    }

    return true;
}

?>
