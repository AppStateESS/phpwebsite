<?php

/**
 * Category conversion file
 *
 * Transfers fatcat stuff to categories
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @modified Eloi George <adarkling at users dot sourceforge dot net>
 * @version $Id$
 */

PHPWS_Core::initModClass('filecabinet', 'File_Assoc.php');
PHPWS_Core::requireInc('filecabinet', 'defines.php');

function convert()
{
    if ( (!Convert::isConverted('webpage') || !Convert::isConverted('blog')) && !isset($_GET['ignore'])) {
        $content[] = _('Any content modules using FatCat should be converted BEFORE continuing.');
        $content[] = sprintf('<a href="index.php?command=convert&amp;package=categories&amp;ignore=1">%s</a>', _('Click to continue anyway.'));
        $content[] = _('Otherwise, click on the "Main page" link above.');
        return implode('<br />', $content);
    }

    if (!Convert::isConverted('categories')) {
        return convertCategories();
    } elseif (!Convert::isConverted('category_items')) {
        return convertItems();
    } else {
        return _('Categories has already been converted.');
    }
}

/**
 * @modifier Eloi George
 */
function convertCategories()
{
    $db = Convert::getSourceDB('mod_fatcat_categories');
    $batch = new Batches('convert_categories');
    $total_categories = $db->count();
    $batch->setTotalItems($total_categories);
    $batch->setBatchSet(50);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }

    if (!$batch->load()) {
        $content[] = 'Batch previously run.';
    } else {
        runCatBatch($db, $batch);
    }

    $content[] = sprintf('%s&#37; done<br>', $batch->percentDone());

    $batch->completeBatch();

    if (!$batch->isFinished()) {
        $content[] =  $batch->continueLink();
    } else {
        $batch->clear();
        createSeqTable();

        // Fix the FileCabinet URL column
        Convert::siteDB();
        $home_dir = Convert::getHomeDir();
        $fixdir = $_SESSION['Category Folder_Dir'];
        $db = new PHPWS_DB('images');
        $db->addWhere('file_directory', $fixdir);
        $db->addValue('file_directory', str_replace($home_dir, './', $_SESSION['Category Folder_Dir']));
        $db->update();

        Convert::addConvert('categories');
        $content[] =  _('Finished converting categories!');
        $content[] = '<a href="index.php?command=convert&amp;package=categories">' . _('Continue to convert category elements.') . '</a>';
    }

    return implode('<br />', $content);
}

function convertItems()
{
    if (!isset($_REQUEST['mode'])) {
        $content[] = _('You may convert two different ways.');
        $content[] = sprintf('<a href="%s">%s</a>', 'index.php?command=convert&package=categories&ignore=1&mode=manual',
        _('Manual mode requires you to click through the conversion process.'));
        $content[] = sprintf('<a href="%s">%s</a>', 'index.php?command=convert&package=categories&ignore=1&mode=auto',
        _('Automatic mode converts the data without your interaction.'));

        $content[] = ' ';
        $content[] = _('If you encounter problems, you should use manual mode.');
        $content[] = _('Conversion will begin as soon as you make your choice.');

        return implode('<br />', $content);
    } else {
        if ($_REQUEST['mode'] == 'auto') {
            $show_wait = TRUE;
        } else {
            $show_wait = FALSE;
        }

        $db = Convert::getSourceDB('mod_fatcat_elements');

        $batch = new Batches('convert_category_items');
        $total_items = $db->count();

        $batch->setTotalItems($total_items);
        $batch->setBatchSet(30);

        if (isset($_REQUEST['reset_batch'])) {
            $batch->clear();
        }

        if (!$batch->load()) {
            $content[] = 'Batch previously run.';
        } else {
            runCatItemBatch($db, $batch);
        }

        $percent = $batch->percentDone();
        $content[] = Convert::getGraph($percent, $show_wait);
        $batch->completeBatch();

        if (!$batch->isFinished()) {
            if ($_REQUEST['mode'] == 'manual') {
                $content[] =  $batch->continueLink();
            } else {
                Convert::forward($batch->getAddress());
            }
        } else {
            $batch->clear();
            Convert::addConvert('categories_items');
            $content[] =  _('Finished converting category items!');
            $content[] = '<a href="index.php">' . _('Return to the main page.') . '</a>';
        }

        return implode('<br />', $content);
    }
}

function runCatBatch(&$db, &$batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($limit, $start);
    $result = $db->select();
    $db->disconnect();
    Convert::siteDB();

    $newdb = new PHPWS_DB('categories');

    if (empty($result)) {
        return NULL;
    } else {
        foreach ($result as $oldCat) {
            if ($oldCat['cat_id']) {
                $val['id']          = $oldCat['cat_id'];
            }
            $val['title']       = utf8_encode($oldCat['title']);
            $val['description'] = PHPWS_Text::breaker(utf8_encode($oldCat['description']));
            $val['parent']      = $oldCat['parent'];
            $val['icon'] = convertImage($oldCat['image'], 'Category: ' . $oldCat['title']);
            $newdb->addValue($val);
            $result = $newdb->insert(FALSE);
            $newdb->reset();
        }
    }

}

function runCatItemBatch(&$db, &$batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($limit, $start);
    $result = $db->select();
    $db->disconnect();
    Convert::siteDB();

    $item_db = new PHPWS_DB('category_items');
    $key_db = new PHPWS_DB('phpws_key');

    if (empty($result)) {
        return NULL;
    } else {
        foreach ($result as $item) {
            // ignores 0 id category
            if (!$item['cat_id']) {
                continue;
            }
            $module = $item_name = $item_id = NULL;

            switch ($item['module_title']) {
                case 'announce':
                    $module = 'blog';
                    $item_name = 'entry';
                    break;

                case 'calendar':
                    $module = 'calendar';
                    $item_name = 'event';
                    break;

                case 'photoalbum':

                    break;

                case 'pagemaster':
                    $module = 'webpage';
                    $item_name = 'volume';
                    break;

                case 'documents':
                    $module = 'filecabinet';
                    $item_name = 'document';
                    break;

                case 'phatform':
                    $module = 'phatform';
                    $item_name = 'form';
                    break;
            } // end item switch

            if (empty($module)) {
                continue;
            }

            $item_id = $item['module_id'];
            $key_db->addWhere('module', $module);
            $key_db->addWhere('item_name', $item_name);
            $key_db->addWhere('item_id', $item_id);
            $key_db->addColumn('id');
            $key_id = $key_db->select('one');
            $key_db->reset();
            if (empty($key_id)) {
                continue;
            } else {
                $item_db->addValue('key_id', $key_id);
                $item_db->addValue('cat_id', $item['cat_id']);
                $item_db->addValue('module', $module);
                $item_db->insert();
                $item_db->reset();
            }
        }
    }

}

function createSeqTable()
{
    $db = new PHPWS_DB('categories');
    return $db->updateSequenceTable();
}

function convertImage ($image_name, $image_alt)
{
    $home_dir = Convert::getHomeDir();

    // If there's no image, return
    if (empty($image_name))
    return 0;

    // Extract the image name out of FatCat's format
    $arr = explode(':', $image_name);
    $image_name = $arr[0];

    // Create an "Category" folder
    if (!isset($_SESSION['Category Folder'])) {
        Convert::siteDB();
        PHPWS_Core::initModClass('filecabinet', 'Folder.php');
        $folder = new Folder;
        $folder->setTitle('Categories');
        $folder->setDescription('Category images');
        $folder->_base_directory = $home_dir . 'images/filecabinet/';
        $result = $folder->save();
        $_SESSION['Category Folder'] = $folder->id;
        $_SESSION['Category Folder_Dir'] = $folder->getFullDirectory();
    }

    $old_file = $home_dir . 'images/fatcat/images/' . $image_name;
    $new_directory = $_SESSION['Category Folder_Dir'];

    $err = PHPWS_File::fileCopy($old_file, $new_directory, $image_name, 1, 1);
    if (PHPWS_Error::isError($err))
    exit(PHPWS_Error::printError($err));
    if (!$err)
    exit('Something went wrong with the image transfer');

    // Get image specs
    $image_size = filesize($new_directory . $image_name);
    if (empty($image_width)) {
        $x = getimagesize($new_directory . $image_name);
        $image_width  = $x[0];
        $image_height = $x[1];
        $image_type = image_type_to_mime_type($x[2]);
    }

    // Add the file information to FileCabinet
    PHPWS_Core::initModClass('filecabinet', 'Image.php');
    $img = new PHPWS_Image();
    $img->file_name = utf8_encode($image_name);
    $img->file_directory = $_SESSION['Category Folder_Dir'];
    $img->setTitle(utf8_encode($image_alt));
    $img->setAlt(utf8_encode($image_alt));
    $img->setSize(utf8_encode($image_size));
    $img->width = $image_width;
    $img->height = $image_height;
    $img->file_type = $image_type;
    $img->folder_id = $_SESSION['Category Folder'];
    $result = $img->save(true, false, false);
    if (PHPWS_Error::isError($result)) {
        exit(PHPWS_Error::printError($result));
    }
    $hold = $img->file_directory;
    $img->file_directory = $home_dir . $hold;
    $img->makeThumbnail();

    $file_assoc = new FC_File_Assoc;
    $file_assoc->file_type = FC_IMAGE;
    $file_assoc->file_id = $img->id;

    if(PHPWS_Error::logIfError($file_assoc->save())) {
        continue;
    }

    // return the new FileCabinet id
    return $file_assoc->id;
}



?>