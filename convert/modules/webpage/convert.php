<?php

/**
 * Convertion file for Webpage module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('search', 'Search.php');
PHPWS_Core::initModClass('webpage', 'Volume.php');
PHPWS_Core::initModClass('webpage', 'Page.php');
PHPWS_Core::initModClass('filecabinet', 'Folder.php');
PHPWS_Core::initModClass('filecabinet', 'Image.php');


function convert()
{
    if (Convert::isConverted('webpage')) {
        return _('Web Pages have already been converted.');
    }

    $home_dir = Convert::getHomeDir();

    if (!is_dir($home_dir . 'images/pagemaster')) {
        return sprintf(_('Please create a directory in %simages/ named "pagemaster". Copy all images from the old Web Pages image directory into it.'),
        $home_dir);
    }

    $mod_list = PHPWS_Core::installModList();

    if (!in_array('webpage', $mod_list)) {
        return _('Web Page is not installed.');
    }

    if (!isset($_SESSION['Webpage_Method'])) {
        if (isset($_GET['webpage_method'])) {
            $_SESSION['Webpage_Method'] = $_GET['webpage_method'];
        } else {
            if (Convert::isConverted('pagesmith')) {
                $content[] = _('PageSmith has been converted as well. Make sure to check the defines in the menu convert.php file before running.');
            }

            $content[] = _('There are two methods for converting your webpages:');
            $content[] = sprintf('<a href="index.php?command=convert&amp;package=webpage&amp;webpage_method=sep">%s</a>',
            _('Method 1 - Each section is placed into a separate page.'));
            $content[] = sprintf('<a href="index.php?command=convert&amp;package=webpage&amp;webpage_method=col">%s</a>',
            _('Method 2 - Each section is collected into ONE page.'));
            $content[] = _('Click on the method you wish to use.');
            return implode('<br /><br />', $content);
        }
    }


    $db = Convert::getSourceDB('mod_pagemaster_pages');

    $batch = new Batches('convert_pagemaster');
    $total_pages = $db->count();
    if ($total_pages < 1) {
        return _('No pages to convert.');
    }
    $batch->setTotalItems($total_pages);
    $batch->setBatchSet(5);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }
     
    if (!$batch->load()) {
        $content[] = _('Batch previously run.');
    } else {
        $result = runBatch($db, $batch);
        if (is_array($result)) {
            $content[] = _('Some errors occurred when trying to convert the following pages:');
            $content[] = '<ul><li>' . implode('</li><li>', $result) . '</li></ul>';
            return implode('<br />', $content);
        }
    }

    $percent = $batch->percentDone();
    $content[] = Convert::getGraph($percent);

    $batch->completeBatch();

    if (!$batch->isFinished()) {
        Convert::forward($batch->getAddress());
    } else {
        createSeqTables();
        $batch->clear();
        Convert::addConvert('webpage');
        PHPWS_Core::killSession('Folder_Id');
        unset($_SESSION['Webpage_Method']);
        $content[] =  _('All done!');
        $content[] = '<a href="index.php">' . _('Go back to main menu.') . '</a>';
    }

    return implode('<br />', $content);
}

function runBatch(&$db, &$batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($limit, $start);
    $result = $db->select();
    $db->disconnect();
    Convert::siteDB();

    if (empty($result)) {
        return NULL;
    } else {
        foreach ($result as $oldPage) {
            $result = convertPage($oldPage);
            if ($result) {
                $errors[] = $oldPage['title'];
            }
        }
    }
    if (isset($errors)) {
        return $errors;
    } else {
        return TRUE;
    }
}

function convertPage($page)
{
    $db = new PHPWS_DB('webpage_volume');
    $user_id = Current_User::getId();
    $val['id']             = $page['id'];
    $val['title']          = PHPWS_Text::parseInput(strip_tags(utf8_encode($page['title'])));
    $val['date_created']   = strtotime($page['created_date']);
    $val['date_updated']   = strtotime($page['updated_date']);
    $val['created_user']   = $page['created_username'];
    $val['updated_user']   = $page['updated_username'];
    $val['create_user_id'] = $user_id;
    $val['frontpage']      = (int)$page['mainpage'];
    $val['approved']       = 1;
    $val['active']         = $page['active'];

    $key = new Key;
    $key->setItemId($val['id']);
    $key->setModule('webpage');
    $key->setItemName('volume');
    $key->setEditPermission('edit_page');
    $key->setTitle(utf8_encode($val['title']));

    $url = 'index.php?module=webpage&amp;id=' . $val['id'];

    $key->setUrl($url);
    $result = $key->save();
    $val['key_id'] = $key->id;


    $db->addValue($val);
    $result = $db->insert(FALSE);

    if (PHPWS_Error::isError($result)) {
        return FALSE;
    }

    convertSection($page['section_order'], $val['id'], $val['title'], $key->id);
}

function convertSection($section_order, $volume_id, $title, $key_id)
{
    $section_order = @unserialize($section_order);

    if (!is_array($section_order)) {
        return;
    }

    $db = Convert::getSourceDB('mod_pagemaster_sections');
    $db->addWhere('id', $section_order, 'in', 'or');
    $db->setIndexBy('id');
    $sections = $db->select();
    $db->disconnect();
    Convert::siteDB();

    saveSections($sections, $volume_id, $title, $key_id);
}

function saveSections($sections, $volume_id, $title, $key_id)
{
    // sep or col
    $method = & $_SESSION['Webpage_Method'];
    $title_set = false;
    $pages = 1;

    if ($method == 'col') {
        $page = new Webpage_Page;
    }

    foreach ($sections as $sec) {
        if ($method == 'sep') {
            $page = new Webpage_Page;
            $page_content = array();
        }

        if ($method == 'sep') {
            $page->volume_id = $volume_id;
            $page->approved  = 1;

            if (!empty($sec['title'])) {
                $page->title = PHPWS_Text::parseInput(strip_tags(utf8_encode($sec['title'])));
            } else {
                $page->title = _('Untitled');
            }
            $page->page_number = $pages;
            $page->template = 'basic.tpl';
        } else {
            if (!empty($sec['title'])) {
                if (!$title_set) {
                    $page->title = PHPWS_Text::parseInput(strip_tags(utf8_encode($sec['title'])));
                    $title_set = true;
                } else {
                    $page_content[] = '<h2>' . utf8_encode($sec['title']) . '</h2>';
                }
            }
        }

        if (!empty($sec['image'])) {
            $image = @unserialize($sec['image']);
            $image_obj = convertImage($image);
            if ($image_obj && $image_obj->id) {
                switch ($sec['template']) {
                    case 'image_left.tpl':
                    case 'image_top_left.tpl':
                    case 'image_float_left.tpl':
                        $page_content[] = sprintf('<div style="float: left; display : inline; margin : 0px 10px 10px 0px">%s</div>', $image_obj->getTag());
                        break;

                    default:
                        $page_content[] = sprintf('<div style="float : right; display : inline; margin : 0px 0px 10px 10px">%s</div>', $image_obj->getTag());
                }
            }
        }

        $content = preg_replace('/module=pagemaster(&|&amp;)page_user_op=view_page(&|&amp;)page_id=/i', 'module=webpage&id=', $sec['text']);
        $content = preg_replace('/&MMN_position=\d+:\d+/', '', $content);

        $page_content[] = PHPWS_Text::breaker(utf8_encode($content));

        $pages++;

        if ($method == 'sep') {
            $page->setContent(implode("\n", $page_content));
            $result = $page->save();
            if (PHPWS_Error::isError($result)) {
                PHPWS_Error::log($result);
            }
        }
    }

    if ($method == 'col') {
        $page->setContent(implode("\n", $page_content));
        $page->volume_id   = $volume_id;
        $page->approved    = 1;
        $page->page_number = 1;
        $page->template    = 'basic.tpl';
        $result = $page->save();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
        }
    }
}

function convertImage($data)
{
    if (empty($data['name'])) {
        return false;
    }

    $home_dir = Convert::getHomeDir();

    if (!isset($_SESSION['Folder_Id'])) {
        $folder = new Folder;
        $folder->title = _('Web Page conversion');
        $folder->description = _('Images copied during a 0.10.x conversion.');
        if (PHPWS_Error::logIfError($folder->save())) {
            PHPWS_Core::log("Error creating saving conversion folder.", 'conversion.log');
            return false;
        } else {
            $_SESSION['Folder_Id'] = $folder->id;
        }
    } else {
        $folder = new Folder($_SESSION['Folder_Id']);
        if (!$folder->id) {
            PHPWS_Core::log("Unable to load folder.", 'conversion.log');
            return false;
        }
    }

    $image = new PHPWS_Image;
    $image->folder_id = $folder->id;
    $image->file_name = $data['name'];
    $image->file_directory = $folder->getFullDirectory();

    $image_dir = $home_dir . $image->getPath();

    $source_image = $home_dir . 'images/pagemaster/' . $image->file_name;

    if (!is_file($source_image)) {
        PHPWS_Core::log("Missing source image: $source_image.", 'conversion.log');
        return false;
    } else {
        if (!@copy($source_image, $image_dir)) {
            PHPWS_Core::log("Failed to copy $source_image to $image_dir", 'conversion.log');
            return false;
        }
    }

    $size = @getimagesize($image_dir);

    if (!$size) {
        return false;
    }

    $image->file_type = $size['mime'];
    $image->size = filesize($image_dir);
    $image->width = $size[0];
    $image->height = $size[1];
    $image->alt = $data['alt'];
    $image->title = $data['alt'];

    if (PHPWS_Error::logIfError($image->save(true, false, true))) {
        PHPWS_Core::log("Failed to save Image object.", 'conversion.log');
        return false;
    } else {
        return $image;
    }
}


function createSeqTables()
{
    $db1 = new PHPWS_DB('webpage_volume');
    $result = $db1->updateSequenceTable();

    $db2 = new PHPWS_DB('webpage_page');
    $result = $db2->updateSequenceTable();
}

?>