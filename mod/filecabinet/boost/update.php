<?php
  /**
   * @author Matthew McNaney
   * @version $Id$
   */

function filecabinet_update(&$content, $version)
{

    switch ($version) {
    case version_compare($version, '0.1.7', '<'):
        $content[] = 'This package will not update versions under 0.1.7';
        return false;

    case version_compare($version, '0.3.1', '<'):
        $type = PHPWS_DB::getDBType();
        if ($type == 'mysql') {
            $sql = 'ALTER TABLE images MODIFY file_name varchar(255) NOT NULL';
        } else {
            $sql = 'ALTER TABLE images ALTER COLUMN file_name TYPE varchar(255)';
        }

        $result = PHPWS_DB::query($sql);
        if (PEAR::isError($result)) {
            $content[] = 'Failed increasing images.file_name column';
            PHPWS_Error::log($result);
            return false;
        }

        $files = array();
        $files[] = 'javascript/clear_image/head.js';
        $files[] = 'javascript/post_file/body.js';
        $files[] = 'templates/style.css';
        $files[] = 'templates/cookie_directory.tpl';
        $files[] = 'templates/manager/pick.tpl';
        $files[] = 'conf/error.php';
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'filecabinet')) {
            $content[] = 'The following files updated successfully:';
        } else {
            $content[] = 'The following files failed to update successfully:';
        }

        $content[] = '    ' . implode("\n    ", $files);

        $content[] = '
0.3.1 changes
-------------
+ Removed references from object constructors
+ Added missing comment lines
+ Added translate statements
+ Added system upload memory check. Overrides site setting in form.
+ Fixed base directory uploads
+ Document was changing the object id to null when failing to load the
  object. Changed it to a zero.
+ Added more directory error checks.
+ Image manager will try and match the current module to its directory.
+ New error message for bad directory choice
+ Increased file name size in database.
+ Added image directory selection to pick image menu
+ Choosing an image directory only shows images from that directory
+ Removed choice of image directory root. Will always be images/
+ Fixed root document directory. Now actually puts files in said
  directory.
+ Upload windows choose the default directory better
+ Removed [default] tag from directory listing
+ Fixed bug in image manager. Was ignoring width and height upload
  restrictions.
+ Lowercased bools
+ Changed \'x\' to \'by\' in error message.
';

    case version_compare($version, '0.3.2', '<'):
        $files = array('img/nogd.png');
        $content[] = '<pre>';
        if (PHPWS_Boost::updateFiles($files, 'filecabinet')) {
            $content[] = '+ nogd.png image copied successfully.';
        } else {
            $content[] = '! nogd.png failed to copy to images/mod/filecabinet/';
        }

        $content[] = '
0.3.2 changes
-------------
+ Removed test function call.
+ Added "loadDimensions" function to image class
+ Added a gd lib check to image manager. Uses the nogd.png image for a
  thumbnail if fails.
</pre>';

    case version_compare($version, '1.0.0', '<'):
        PHPWS_Core::initModClass('filecabinet', 'Folder.php');
        PHPWS_Core::initModClass('filecabinet', 'Image.php');
        $content[] = '<pre>';
        PHPWS_Boost::registerMyModule('filecabinet', 'users', $content);
        PHPWS_Boost::registerMyModule('filecabinet', 'controlpanel', $content);
        if (!PHPWS_DB::isTable('folders')) {
            if (PHPWS_DB::importFile(PHPWS_SOURCE_DIR . 'mod/filecabinet/boost/folders.sql')) {
                $content[] = '--- Folders table created successfully.';
            } else {
                $content[] = '--- Failed to create folders table.</pre>';
                return false;
            }
        }

        $image_db = new PHPWS_DB('images');

        if ($image_db->isTableColumn('key_id')) {
            $image_db->addColumn('key_id');
            $keys = $image_db->select('col');
            if (!empty($keys)) {
                foreach ($keys as $ky) {
                    $key = new Key($ky);
                    $key->delete();
                }
                $content[] = '--- Removed image keys.';
            }
            
            $image_db->reset();

            $result = $image_db->dropTableColumn('key_id');
            if (PHPWS_Error::logIfError($result)) {
                $content[] = '--- Unable to remove key_id column from images table.';
                return false;
            } else {
                $content[] = '--- Removed key_id column from images table.';
            }
        }

        if ($image_db->isTableColumn('thumbnail_source')) {
            $image_db->addWhere('thumbnail_source', 0, '>');
            $image_db->delete();
            $image_db->reset();

            $result = $image_db->dropTableColumn('thumbnail_source');
            if (PHPWS_Error::logIfError($result)) {
                $content[] = '--- Unable to remove thumbnail_source column from images table.';
                return false;
            } else {
                $content[] = '--- Removed thumbnail_source column from images table.';
            }
        }

        if (!$image_db->isTableColumn('folder_id')) {
            $result = $image_db->addTableColumn('folder_id', 'int NOT NULL default 0');
            if (PHPWS_Error::logIfError($result)) {
                $content[] = '--- Unable to add folder_id column to images table.';
                return false;
            }
        }

        $document_db = new PHPWS_DB('documents');

        if ($document_db->isTableColumn('key_id')) {
            $document_db->addColumn('key_id');
            $keys = $document_db->select('col');
            if (!empty($keys)) {
                foreach ($keys as $ky) {
                    $key = new Key($ky);
                    $key->delete();
                }
                $content[] = '--- Removed document keys.';
            }
            
            $document_db->reset();

            $result = $document_db->dropTableColumn('key_id');
            if (PHPWS_Error::logIfError($result)) {
                $content[] = '--- Unable to remove key_id column from documents table.';
                return false;
            } else {
                $content[] = '--- Removed key_id column from documents table.';
            }
        }

        if (!$document_db->isTableColumn('folder_id')) {
            $result = $document_db->addTableColumn('folder_id', 'int NOT NULL default 0');
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = '--- Unable to add folder_id column to documents table.';
                return false;
            }
        }

        $image_folder = new Folder;
        $image_folder->title = 'Images';
        $image_folder->ftype = IMAGE_FOLDER;
        $image_folder->save();

        $image_db->reset();
        $image_db->addColumn('file_name');
        $image_db->addColumn('file_directory');
        $all_images = $image_db->select();
        $image_folder_dir = $image_folder->getFullDirectory();

        if (!empty($all_images)) {
            foreach ($all_images as $image) {
                if ($image['file_directory'] == $image_folder_dir) {
                    continue;
                }
                $dir = $image['file_directory'] . $image['file_name'];
                copy($dir, $image_folder_dir . $image['file_name']);
            }
        }

        $document_folder = new Folder;
        $document_folder->title = 'Documents';
        $document_folder->ftype = DOCUMENT_FOLDER;
        $document_folder->save();

        $document_db->reset();
        $document_db->addColumn('file_name');
        $document_db->addColumn('file_directory');
        $all_documents = $document_db->select();
        $document_folder_dir = $document_folder->getFullDirectory();

        if (!empty($all_documents)) {
            foreach ($all_documents as $document) {
                if ($document['file_directory'] == $document_folder_dir) {
                    continue;
                }
                $dir = $document['file_directory'] . $document['file_name'];
                @copy($dir, $document_folder_dir . $document['file_name']);
            }
        }

        $image_db->reset();
        $image_db->addValue('folder_id', $image_folder->id);
        $image_db->addValue('file_directory', $image_folder->getFullDirectory());
        $image_db->update();

        $image_db->reset();
        $all_images = $image_db->getObjects('PHPWS_Image');
        if (!empty($all_images)) {
            foreach ($all_images as $img) {
                $img->makeThumbnail();
            }
            $content[] = '--- New thumbnails created.';
        }

        $document_db->reset();
        $document_db->addValue('folder_id', $document_folder->id);
        $document_db->addValue('file_directory', $document_folder->getFullDirectory());
        $document_db->update();

        $files = array('conf/config.php', 'conf/error.php', 'conf/icons.php',
                       'img/icons/audio.png', 'img/icons/document.png',
                       'img/icons/flash_icon.png', 'img/icons/spreadsheet.png',
                       'img/icons/tar.png', 'javascript/pick_image/head.js',
                       'javascript/pick_image/scripts.js', 'templates/document_edit.tpl',
                       'templates/image_edit.tpl', 'templates/settings.tpl',
                       'templates/style.css', 'templates/view.tpl', 'img/folder.png',
                       'img/folder.svg', 'javascript/folder_contents/head.js',
                       'javascript/refresh_manager/head.js', 'templates/edit_folder.tpl',
                       'templates/file_list.tpl', 'templates/folder_list.tpl',
                       'templates/image_folders.tpl', 'templates/image_grid.tpl',
                       'templates/image.xml', 'templates/javascript.tpl', 'templates/plain.tpl',
                       'templates/manager/pick.tpl');
        if (PHPWS_Boost::updateFiles($files, 'filecabinet')) {
            $content[] = '--- Copied the following files:';
        } else {
            $content[] = '--- FAILED copying the following files:';
        }

        $content[] = "\n    " . implode("\n    ", $files);

        $content[] = '
Notice: File Cabinet has been completely rewritten

1.0.0 changes
-----------------------
+ Images and documents are stored in folders.
+ File Cabinet now controls the directories files are stored 
  in so users do not have to.
+ The Image Manager in File Cabinet will now let the user 
  resize their image based on the maximum width and height designated
  by the module.
+ You can pin image and document folders to keyed items.
+ Thumbnails use new resize/cropping logic.
+ Folders now have permissions, not images and documents.
</pre>';

    case version_compare($version, '1.0.1', '<'):
        $content[] = '<pre>';
        $files = array('templates/style.css', 'templates/image_folders.tpl',
                       'templates/javascript.tpl',
                       'templates/manager/javascript.tpl',
                       'javascript/folder_contents/head.js',
                       'javascript/pick_image/body.js',
                       'javascript/pick_image/head.js',
                       'conf/config.php');

        if (PHPWS_Boost::updateFiles($files, 'filecabinet')) {
            $content[] = '--- Copied the following files:';
        } else {
            $content[] = '--- FAILED copying the following files:';
        }

        $content[] = "    " . implode("\n    ", $files);

        $content[] = '
1.0.1 changes
-----------------------
+ Fixed several image manager bugs popping up in IE7
+ Fixed bug caused when an user picked an image that was already resized.
+ Added choice (via config) to make extra resized images instead of previous
+ Added close button to image manager.</pre>';

    case version_compare($version, '1.0.2', '<'):
        $content[] = '<pre>';
        $db = new PHPWS_DB('folders');
        if (!$db->isTableColumn('key_id')) {
            if (PHPWS_Error::logIfError($db->addTableColumn('key_id', 'int NOT NULL default 0'))) {
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

    }

    return true;
}


?>