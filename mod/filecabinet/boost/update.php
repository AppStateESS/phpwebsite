<?php

/**
 * @author Matthew McNaney
 * @version $Id$
 */
function filecabinet_update(&$content, $version)
{
    $home_dir = PHPWS_Boost::getHomeDir();
    switch ($version) {
        case version_compare($version, '1.0.1', '<'):
            $content[] = '<pre>File Cabinet versions prior to 1.0.1 are not supported.
Please download version 1.0.2.</pre>';
            break;

        case version_compare($version, '1.0.2', '<'):
            $content[] = '<pre>';
            $db = new PHPWS_DB('folders');
            if (!$db->isTableColumn('key_id')) {
                if (PHPWS_Error::logIfError($db->addTableColumn('key_id',
                                        'int NOT NULL default 0'))) {
                    $content[] = '--- An error occurred when trying to add key_id as a column to the folders table.</pre>';
                    return false;
                }
                $content[] = '--- Successfully added key_id column to folders table.';

                $db2 = new PHPWS_DB('phpws_key');
                $db2->addWhere('module', 'filecabinet');
                $db2->delete();
                $content[] = '--- Deleted false folder keys.';

                $db->reset();
                PHPWS_Core::initModClass('filecabinet', 'Folder.php');
                $result = $db->getObjects('Folder');
                if (!empty($result)) {
                    foreach ($result as $folder) {
                        $folder->saveKey(true);
                    }
                }
            }
            $content[] = '
1.0.2 changes
--------------
+ 1.0.0 update was missing key_id column addition to folders table.
</pre>';


        case version_compare($version, '1.1.0', '<'):
            $content[] = '<pre>';

            if (!checkMultimediaDir($content, $home_dir)) {
                return false;
            }

            if (!is_dir($home_dir . 'files/filecabinet/incoming')) {
                if (is_writable($home_dir . 'files/filecabinet') && @mkdir($home_dir . 'files/filecabinet/incoming')) {
                    $content[] = '--- "files/filecabinet/incoming" directory created.';
                } else {
                    $content[] = 'File Cabinet 1.1.0 is unable to create a "filecabinet/incoming" directory.
It is not required but if you want to classify files you will need to create it yourself.
Example: mkdir phpwebsite/files/filecabinet/incoming/</pre>';
                    return false;
                }
            }

            $source_dir = PHPWS_SOURCE_DIR . 'mod/filecabinet/templates/filters/';
            $dest_dir = $home_dir . 'templates/filecabinet/filters/';

            if (!is_dir($dest_dir)) {
                if (!PHPWS_File::copy_directory($source_dir, $dest_dir)) {
                    $content[] = '--- FAILED copying templates/filters/ directory locally.</pre>';
                    return false;
                }
            }

            $files = array('templates/manager/pick.tpl', 'templates/classify_file.tpl',
                'templates/classify_list.tpl', 'templates/image_edit.tpl',
                'templates/multimedia_edit.tpl', 'templates/multimedia_grid.tpl',
                'templates/style.css', 'templates/settings.tpl', 'conf/config.php');

            if (PHPWS_Boost::updateFiles($files, 'filecabinet')) {
                $content[] = '--- Copied the following files:';
            } else {
                $content[] = '--- FAILED copying the following files:';
            }

            $content[] = "    " . implode("\n    ", $files);

            $db = new PHPWS_DB('images');
            if (!$db->isTableColumn('parent_id')) {
                if (PHPWS_Error::logIfError($db->addTableColumn('parent_id',
                                        'int NOT NULL default 0'))) {
                    $content[] = 'Could not create parent_id column in images table.</pre>';
                    return false;
                }
            }

            if (!$db->isTableColumn('url')) {
                if (PHPWS_Error::logIfError($db->addTableColumn('url',
                                        'varchar(255) NULL'))) {
                    $content[] = 'Could not create url column in images table.</pre>';
                    return false;
                }
            }

            if (!PHPWS_DB::isTable('multimedia')) {
                $result = PHPWS_DB::importFile(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/multimedia.sql');
                if (!PHPWS_Error::logIfError($result)) {
                    $content[] = '--- Multimedia table created successfully.';
                } else {
                    $content[] = '--- Failed to create multimedia table.</pre>';
                    return false;
                }
            }

            $content[] = '
1.1.0 changes
--------------
+ Fixed authorized check when unpinning folders
+ Images can now be linked to other pages.
+ Resized images can now be linked to their parent image.
+ Clip option moved outside edit_folder permissions when viewing images.
+ Added writable directory check before allowing new folders to be
  created.
+ Fixed some error messages in File_Common.
+ Commented out ext variable in File_Common. Doesn\'t appear to be in
  use.
+ Created setDirectory function for File_Common. Assures trailing
  forward slash on directory name.
+ Removed itemname variable from Document_Manager
+ Added ability to classify uploaded files.
+ New folder class - Multimedia
+ Multimedia files can be clipped and pasted via SmartTags.
</pre>
';

        case version_compare($version, '1.2.0', '<'):
            $content[] = '<pre>';
            $files = array('img/no_image.png', 'conf/config.php', 'conf/video_types.php',
                'conf/embedded.php',
                'javascript/folder_contents/head.js',
                'javascript/clear_image/head.js',
                'javascript/clear_image/body.js',
                'javascript/pick_image/head.js',
                'templates/image_folders.tpl', 'templates/settings.tpl',
                'templates/style.css', 'templates/image_view.tpl',
                'templates/multimedia_view.tpl', 'templates/style.css',
                'img/video_generic.png', 'templates/image_edit.tpl', 'conf/error.php');

            fc_updatefiles($files, $content);

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/changes/1_2_0.txt');
            }
            $content[] = '</pre>';

        case version_compare($version, '1.2.1', '<'):
            $content[] = '<pre>';
            if (!PHPWS_DB::isTable('filecabinet_pins')) {
                $db = new PHPWS_DB('filecabinet_pins');
                $db->addValue('key_id', 'int not null default 0');
                $db->addValue('folder_id', 'int not null default 0');
                if (PHPWS_Error::logIfError($db->createTable())) {
                    $content[] = 'Failed to create filecabinet_pins table.</pre>';
                    return false;
                }
                $content[] = '--- Created filecabinet_pins table.';
            }

            $files = array('templates/settings.tpl');

            fc_updatefiles($files, $content);

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/changes/1_2_1.txt');
            }
            $content[] = '</pre>';

        case version_compare($version, '1.2.2', '<'):
            $content[] = '<pre>';
            $files = array('templates/image_edit.tpl');
            fc_updatefiles($files, $content);

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/changes/1_2_2.txt');
            }
            $content[] = '</pre>';

        case version_compare($version, '1.3.0', '<'):
            $content[] = '<pre>';

            $db = new PHPWS_DB('folders');
            if (!$db->isTableColumn('module_created')) {
                if (PHPWS_Error::logIfError($db->addTableColumn('module_created',
                                        'varchar(40) default null'))) {
                    $content[] = '--- Could not create column module_created on folders table.</pre>';
                    return false;
                } else {
                    $content[] = '--- Created module_created column on folders table.';
                }
            }


            $db = new PHPWS_DB('multimedia');
            $result = $db->addTableColumn('thumbnail', 'varchar(255) not null');
            if (PHPWS_Error::logIfError($result)) {
                $content[] = '--- Unable to add thumbnail column to multimedia table.</pre>';
                return false;
            } else {
                $content[] = '--- Added thumbnail column to multimedia table.';
            }

            $s1 = PHPWS_SOURCE_DIR . 'mod/filecabinet/templates/filters/flash/';
            $d1 = $home_dir . 'templates/filecabinet/filters/flash/';

            $s2 = PHPWS_SOURCE_DIR . 'mod/filecabinet/img/icons/';
            $d2 = $home_dir . 'images/mod/filecabinet/icons/';

            if (PHPWS_File::copy_directory($s1, $d1)) {
                $content[] = "--- Successfully copied $s1 to $d1";
            } else {
                $content[] = "--- Failed to copy $s1 to $d1</pre>";
                return false;
            }

            if (PHPWS_File::copy_directory($s2, $d2)) {
                $content[] = "--- Successfully copied $s2 to $d2";
            } else {
                $content[] = "--- Failed to copy $s2 to $d2</pre>";
                return false;
            }
            $content[] = '';
            $files = array('conf/error.php', 'conf/config.php',
                'templates/filters/flash.tpl', 'templates/file_list.tpl',
                'templates/multimedia_edit.tpl', 'templates/settings.tpl',
                'templates/style.css', 'templates/thumbnail.tpl', 'templates/image_edit.tpl',
                'javascript/pick_image/head.js', 'templates/folder_list.tpl',
                'templates/manager/pick.tpl', 'img/delete.png');

            fc_updatefiles($files, $content);
            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/changes/1_3_0.txt');
            }
            $content[] = '</pre>';

        case version_compare($version, '1.4.0', '<'):
            $content[] = '<pre>';
            $files = array('javascript/folder_contents/head.js',
                'javascript/folder_contents/scripts.js',
                'javascript/pick_image/head.js',
                'javascript/pick_image/scripts.js',
                'javascript/clear_image/body.js',
                'javascript/clear_image/head.js',
                'templates/style.css',
                'templates/settings.tpl');
            fc_updatefiles($files, $content);
            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/changes/1_4_0.txt');
            }
            $content[] = '</pre>';

        case version_compare($version, '1.4.1', '<'):
            $content[] = '<pre>';
            $db = new PHPWS_DB('folders');
            if (!$db->isTableColumn('module_created')) {
                if (PHPWS_Error::logIfError($db->addTableColumn('module_created',
                                        'varchar(40) default null'))) {
                    $content[] = '--- Could not create column module_created on folders table.</pre>';
                    return false;
                } else {
                    $content[] = '--- Created module_created column on folders table.';
                }
            }
            $content[] = '1.4.1 changes
--------------
+ module_created column missing from > 1.3.0 install.
</pre>';

        case version_compare($version, '1.4.2', '<'):
            $content[] = '<pre>
1.4.2 changes
--------------
+ Removed test echo 1
+ moved all defines to one file.
</pre>';

        case version_compare($version, '2.0.0', '<'):
            $content[] = '<pre>';

            if (PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'mod/filecabinet/templates/',
                            $home_dir . 'templates/filecabinet/')) {
                $content[] = '--- Copied complete templates directory.';
            } else {
                $content[] = '--- Could not copy complete templates directory. Use revert or copy manually.';
            }

            if (PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'mod/filecabinet/img/',
                            $home_dir . 'images/mod/filecabinet/')) {
                $content[] = '--- Copied complete images directory.';
            } else {
                $content[] = '--- Could not copy complete images directory. Use revert or copy manually.';
            }

            if (PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'mod/filecabinet/conf/',
                            $home_dir . 'config/filecabinet/')) {
                $content[] = '--- Copied complete configuration directory.';
            } else {
                $content[] = '--- Could not copy complete configuration directory. Use revert or copy manually.';
            }

            if (PHPWS_File::copy_directory(PHPWS_SOURCE_DIR . 'mod/filecabinet/javascript/',
                            $home_dir . 'javascript/modules/filecabinet/')) {
                $content[] = '--- Copied complete javascript directory.';
            } else {
                $content[] = '--- Could not copy complete javascript directory. Use revert or copy manually.';
            }

            if (!PHPWS_DB::isTable('fc_convert')) {
                $result = PHPWS_DB::importFile(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/fc_convert.sql');
                if (!PHPWS_Error::logIfError($result)) {
                    $content[] = '--- File conversion table created successfully.';
                } else {
                    $content[] = '--- Failed to create File conversion table.</pre>';
                    return false;
                }
            }

            if (!PHPWS_DB::isTable('fc_file_assoc')) {
                $result = PHPWS_DB::importFile(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/file_assoc.sql');
                if (!PHPWS_Error::logIfError($result)) {
                    $content[] = '--- File assoc table created successfully.';
                } else {
                    $content[] = '--- Failed to create File assoc table.</pre>';
                    return false;
                }
            }

            $db = new PHPWS_DB('multimedia');
            if (!$db->isTableColumn('duration')) {
                if (PHPWS_Error::logIfError($db->addTableColumn('duration',
                                        'int not null default 0'))) {
                    $content[] = '--- Failed to create duration column on multimedia table.</pre>';
                    return false;
                } else {
                    $content[] = '--- Created duration column on multimedia table.';
                }
            }

            if (!$db->isTableColumn('embedded')) {
                if (PHPWS_Error::logIfError($db->addTableColumn('embedded',
                                        'smallint not null default 0'))) {
                    $content[] = 'Failed to create embedded column on multimedia table.</pre>';
                    return false;
                } else {
                    $content[] = '--- Created embedded column on multimedia table.';
                }
            }

            PHPWS_Core::initModClass('filecabinet', 'Multimedia.php');
            $result = $db->getObjects('PHPWS_Multimedia');

            if ($result) {
                foreach ($result as $mm) {
                    $mm->loadDimensions();
                    PHPWS_Error::logIfError($mm->save());
                }
            }

            $content[] = '--- Durations added to multimedia files.';

            fc_update_parent_links();

            if (!checkMultimediaDir($content, $home_dir)) {
                return false;
            }

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/changes/2_0_0.txt');
            }

            $content[] = '</pre>';

        case version_compare($version, '2.0.1', '<'):
            $content[] = '<pre>2.0.1 changes
-------------
+ Updated youTube import.
+ Removed unused code.</pre>';

        case version_compare($version, '2.1.0', '<'):
            $content[] = '<pre>';
            $files = array('templates/image_view.tpl', 'templates/settings.tpl',
                'javascript/pick_file/head.js', 'javascript/pick_file/scripts.js',
                'javascript/update_file/head.js', 'templates/file_manager/placeholder.tpl',
                'templates/document_edit.tpl', 'templates/image_edit.tpl', 'templates/multimedia_edit.tpl',
                'templates/edit_folder.tpl', 'templates/embed_edit.tpl', 'templates/style.css',
                'templates/file_manager/folder_content_view.tpl', 'templates/file_manager/resize.tpl');
            fc_updatefiles($files, $content);

            $db = new PHPWS_DB('folders');
            $db->begin();
            if (PHPWS_Error::logIfError($db->addTableColumn('max_image_dimension',
                                    'smallint  not null default 0'))) {
                $content[] = '--- Unable to add max_image_dimension column to folders table.';
                $db->rollback();
                return false;
            } else {
                $content[] = '--- Added max_image_dimension column to folders table.';
            }

            $db = new PHPWS_DB('fc_file_assoc');
            if (PHPWS_Error::logIfError($db->addTableColumn('width',
                                    'smallint NOT NULL default 0'))) {
                $content[] = '--- Unable to add width column to fc_file_assoc.';
                $db->rollback();
                return false;
            } else {
                $content[] = '--- Added width column to fc_file_assoc table';
            }

            if (PHPWS_Error::logIfError($db->addTableColumn('height',
                                    'smallint NOT NULL default 0'))) {
                $content[] = '--- Unable to add height column to fc_file_assoc.';
                $db->rollback();
                return false;
            } else {
                $content[] = '--- Added height column to fc_file_assoc table';
            }

            if (PHPWS_Error::logIfError($db->addTableColumn('cropped',
                                    'smallint NOT NULL default 0'))) {
                $content[] = '--- Unable to add cropped column to fc_file_assoc.';
                $db->rollback();
                return false;
            } else {
                $content[] = '--- Added cropped column to fc_file_assoc table';
            }

            $db->commit();
            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/changes/2_1_0.txt');
            }
            $content[] = '</pre>';

        case version_compare($version, '2.2.0', '<'):
            $content[] = '<pre>';
            $db = new PHPWS_DB('fc_file_assoc');
            if (PHPWS_Error::logIfError($db->addTableColumn('vertical',
                                    'smallint not null default 0'))) {
                $content[] = 'Unable to create vertical column in fc_file_assoc table.';
                return false;
            }

            if (PHPWS_Error::logIfError($db->addTableColumn('num_visible',
                                    'smallint not null default 3'))) {
                $content[] = 'Unable to create num_visible column in fc_file_assoc table.';
                return false;
            }

            $db->dropTableColumn('cropped');

            $db = new PHPWS_DB('modules');
            $db->addWhere('title', 'filecabinet');
            $db->addValue('unregister', 1);
            PHPWS_Error::logIfError($db->update());
            $content[] = 'Unregister flag set in modules table.';

            $files = array('javascript/jcaro_lite/',
                'javascript/shutter/',
                'javascript/pick_file/',
                'javascript/update_file/head.js',
                'javascript/update_file/default.php',
                'javascript/clear_file/body.js',
                'javascript/clear_file/head.js',
                'javascript/clear_file/default.php',
                'templates/image_view.tpl',
                'templates/carousel_horz.tpl',
                'templates/carousel_vert.tpl',
                'templates/classify_list.tpl',
                'templates/ss_box.tpl',
                'templates/file_manager/carousel_pick.tpl',
                'templates/file_manager/folder_content_view.tpl',
                'templates/settings.tpl',
                'templates/style.css',
                'templates/file_list.tpl',
                'templates/folder_list.tpl',
                'templates/pinned.tpl',
                'img/add.png',
                'img/arrow_left.png',
                'img/arrow_right.png',
                'conf/icons.php',
                'conf/config.php'
            );

            fc_updatefiles($files, $content);

            if (!PHPWS_Boost::inBranch()) {
                $content[] = file_get_contents(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/changes/2_2_0.txt');
            }

        case version_compare($version, '2.2.1', '<'):
            $content[] = '<pre>
2.2.1 changes
-----------------------
+ Fixed folder deletion.
+ Clipped documents and images now have full pathing.
+ Fixed document smarttag</pre>';

        case version_compare($version, '2.2.2', '<'):
            $content[] = '<pre>';
            $files = array('img/mime_types/', 'templates/document_download.tpl', 'templates/file_view.css',
                'templates/multi_doc_download.tpl');
            fc_updatefiles($files, $content);
            $content[] = '2.2.2 changes
-----------------------
+ Fixed edit icon in document view. Needed to be salted.
+ Added error check to prevent possible divide by zero error.
+ Resized mime type icons
+ Changed the document download windows. Simplified.
+ Fixed image edit link.
+ Added pptm to known types.
+ Fixed permission checks on folders.</pre>';

        case version_compare($version, '2.2.3', '<'):
            $content[] = '<pre>2.2.3 changes
-----------------------
+ Fixed document delete link</pre>';

        case version_compare($version, '2.2.4', '<'):
            $content[] = '<pre>';
            $files = array('templates/fckeditor.tpl',
                'templates/fckdocuments.tpl',
                'templates/fck.css',
                'templates/fckimages.tpl',
                'templates/fckfolders.tpl',
                'templates/folder_list.tpl',
                'templates/settings.tpl',
                'img/folder.gif',
                'javascript/fckeditor/');
            fc_updatefiles($files, $content);
            $content[] = '2.2.4 changes
-----------------------
+ Error checking added to document upload.
+ Fixed multimedia folder pager.
+ Added method for fixing document directories.
+ Added file search to folder pager.
+ Added File Cabinet FCKeditor interaction.
+ Cleaned up so interface issues.
</pre>';

        case version_compare($version, '2.2.5', '<'):
            $content[] = '<pre>2.2.5 changes
-----------------------
+ Fixed bug that displayed error message on document upload.
+ Proper error message now on document uploaded to unwritable
  directory
+ Directory check on unwritable directory prevents upload link</pre>';

        case version_compare($version, '2.2.6', '<'):
            $content[] = '<pre>';
            $files = array('javascript/fckeditor/head.js', 'templates/fck.css');
            fc_updatefiles($files, $content);
            $content[] = '2.2.6 changes
-----------------------
+ Fixed bug with editing Multimedia.
+ Fixed bug with File Cabinet in FCKeditor not loading folders properly.
</pre>';

        case version_compare($version, '2.2.7', '<'):
            $content[] = '<pre>';
            $files = array('javascript/fckeditor/head.js', 'templates/filters/media/mediaplayer.swf', 'templates/filters/media/yt.swf');
            fc_updatefiles($files, $content);
            $content[] = '2.2.7 changes
-----------------------
+ Added trim to ffmpeg file directory
+ Added description to search for files
+ Media player updated to latest version.
+ FCKeditor media insertion had problems with any media other than
  YouTube embeds. SmartTag for media is displayed instead.</pre>';

        case version_compare($version, '2.3.0', '<'):
            $content[] = '<pre>2.3.0 changes
---------------------
+ Icon class implemented.
+ Video player changed to Flowplayer.
+ Lightbox option for public folders.
+ Image carousel switched to jcarousel with Lightbox usage.
+ Updated to work with core updates.</pre>';

        case version_compare($version, '2.3.1', '<'):
            $content[] = '<pre>2.3.1 changes
---------------------
+ Setting added to allow direct links to documents
+ Fixed captioned image template</pre>';
        case version_compare($version, '2.3.2', '<'):
            $content[] = '<pre>2.3.2 changes
---------------------
+ Changing public flag to private on document folders creates .htaccess file preventing world access
+ Added mp4/m4v to multimedia class.
</pre>';
        case version_compare($version, '2.4.0', '<'):
            $content[] = '<pre>2.4.0 changes
---------------------
+ Heavy ckeditor modifications.
+ rtmp support added.
+ Removed FCK code where found.
+ Minified some scripts.
+ Failed image upload will now display an error.
+ Can now add Access shortcut to a document.
+ Various bug fixes
</pre>';
        case version_compare($version, '2.4.1', '<'):
            $content[] = '<pre>2.4.1 changes
-----------------------
+ Fixed a possible XSS vulnerability discovered by Jakub Galczyk.
+ Users must have some File Cabinet permission to use the File Manager.
</pre>';
        case version_compare($version, '2.4.2', '<'):
            $content[] = '<pre>2.4.2 changes
-----------------------
+ Added error logging to image resize.
</pre>';
        case version_compare($version, '2.4.3', '<'):
            $content[] = '<pre>2.4.3 changes
-----------------------
+ Added checkbox in Settings to turn off autofloating of images in the ckeditor.
</pre>';
        case version_compare($version, '2.4.4', '<'):
            $content[] = '<pre>2.4.4 changes
-----------------------
+ Removed clipboard functionality. Confused users and was not
+ Bootstrap icons and styles added.
</pre>';

        case version_compare($version, '2.5.0', '<'):
            $content[] = '<pre>2.5.0 changes
-----------------------
+ img-responsive added to inserted images to work with Bootstrap
+ removed image widths and heights
+ Cropping and resizing removed from manager
+ Manager made larger.
+ Manager upload header fixed position
</pre>';
        case version_compare($version, '2.6.0', '<'):
            $content[] = <<<EOF
<pre>2.6.0 changes
----------------
+ Removed call to updateTag as it does not work.
+ Removed calls to Layout Cache (which was removed)
+ Updated filecabinet to work with ckeditor 4.
+ Updated ckeditor 4 filecabinet pop up layout.
+ Thumbnails of uploaded images are not shown.
</pre>
EOF;
            return true;
    }
}

function fc_updatefiles($files, &$content)
{
    $result = PHPWS_Boost::updateFiles($files, 'filecabinet', true);
    if (!is_array($result)) {
        $content[] = '--- Copied the following files:';
        $content[] = '    ' . implode("\n    ", $files);
    } else {
        $content[] = '--- FAILED copying the following files:';
        $content[] = '    ' . implode("\n    ", $result);
    }

    $content[] = '';
}

function checkMultimediaDir(&$content, $home_dir)
{
    if (!is_dir($home_dir . 'files/multimedia')) {
        if (is_writable($home_dir . 'files/') && @mkdir($home_dir . 'files/multimedia')) {
            $content[] = '--- "files/multimedia" directory created.';
        } else {
            $content[] = 'File Cabinet 1.1.0 requires the creation of a "multimedia" directory.
Please place it in the files/ directory.
Example: mkdir phpwebsite/files/multimedia/</pre>';
            return false;
        }
    } elseif (!is_writable($home_dir . 'files/multimedia')) {
        $content[] = 'Your files/multimedia directory is not writable by the web server.
 Please change its permissions and return.</pre>';
        return false;
    }
    return true;
}

function fc_update_parent_links()
{
    // Nulling the url column for images with 'parent' set as url
    $db = new PHPWS_DB('images');
    $db->addWhere('url', 'parent');
    $db->addValue('url', NULL);
    PHPWS_Error::logIfError($db->update());

    // remove superfluous column
    PHPWS_Error::logIfError($db->dropTableColumn('parent_id'));
}

?>