<?php

/**
 * Convertion file for PageSmith module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('search', 'Search.php');
PHPWS_Core::initModClass('pagesmith', 'PageSmith.php');
PHPWS_Core::initModClass('pagesmith', 'PS_Page.php');
PHPWS_Core::initModClass('pagesmith', 'PS_Text.php');
PHPWS_Core::initModClass('pagesmith', 'PS_Block.php');
PHPWS_Core::initModClass('filecabinet', 'Folder.php');
PHPWS_Core::initModClass('filecabinet', 'Image.php');
PHPWS_Core::initModClass('filecabinet', 'File_Assoc.php');
PHPWS_Core::requireInc('filecabinet', 'defines.php');

function convert()
{
    if (Convert::isConverted('pagesmith')) {
        return _('PageSmith has already been converted.');
    }
    
    $home_dir = Convert::getHomeDir();

    if (!is_dir($home_dir . 'images/pagemaster')) {
        return sprintf(_('Please create a directory in %simages/ named "pagemaster". Copy all images from the old Web Pages image directory into it.'),
                       $home_dir);
    }

    $mod_list = PHPWS_Core::installModList();

    if (!in_array('pagesmith', $mod_list)) {
        return _('PageSmith is not installed.');
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
        Convert::addConvert('pagesmith');
        PHPWS_Core::killSession('Folder_Id');
        $content[] = _('All done!');
        $content[] = _('You may delete your images/pagemaster/ directory if you wish.');
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
    $user_id = Current_User::getId();

    $db = new PHPWS_DB('ps_page');
    $val['id']            = $page['id'];
    $val['title']         = PHPWS_Text::parseInput(strip_tags(utf8_encode($page['title'])));
    $val['template']      = 'simple';
    $val['create_date']   = strtotime($page['created_date']);
    $val['last_updated']  = strtotime($page['updated_date']);    
    $val['front_page']      = (int)$page['mainpage'];

    $key = new Key;
    $key->setItemId($val['id']);
    $key->setModule('pagesmith');
    $key->setItemName('page');
    $key->setEditPermission('edit_page');
    $key->setTitle($val['title']);
    $url = 'index.php?module=pagesmith&amp;id=' . $val['id'];
    $key->setUrl($url);
    $result = $key->save();

    $val['key_id'] = $key->id;
    $db->addValue($val);
    $result = $db->insert(FALSE);

    if (PHPWS_Error::logIfError($result)) {
        return FALSE;
    }

    $search = new Search($key->id);
    $search->addKeywords($val['title']);
    $search->save();

    /**
     * Keeping web page conversion stuff just in case extra data is added.
     *
     * $val['created_user']   = $page['created_username'];
     * $val['updated_user']   = $page['updated_username'];
     * $val['create_user_id'] = $user_id;
     * $val['approved']       = 1;
     * $val['active']         = $page['active'];
     **/

    convertSection($page['section_order'], $val['id'], $val['title'], $key->id);
}

function convertSection($section_order, $id, $title, $key_id)
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

    saveSections($sections, $id, $title, $key_id);
}

function saveSections($sections, $id, $title, $key_id)
{
    $header_sec['pid']     = $id;
    $header_sec['secname'] = 'header1';
    $header_sec['sectype'] = 'text';
    
    $text_sec['pid']       = $id;
    $text_sec['secname']   = 'text1';
    $text_sec['sectype']   = 'text';

    $image_sec['pid']       = $id;
    $image_sec['btype']     = 'image';
    $image_sec['secname']   = 'image1';
    $image_sec['sectype']   = 'image';

    $image_set = false;

    foreach ($sections as $sec) {
        if (!empty($sec['title'])) {
            if (!$title_set) {
                $header_sec['content'] = PHPWS_Text::parseInput(strip_tags(utf8_encode($sec['title'])));
                $db = new PHPWS_DB('ps_text');
                $db->addValue($header_sec);
                PHPWS_Error::logIfError($db->insert());
                $title_set = true;
            } else {
                $page_content[] = '<h2>' . utf8_encode($sec['title']) . '</h2>';
            }
        }

        if (!empty($sec['image']) && preg_match('/^a:4:/', $sec['image'])) {
            $image = @unserialize($sec['image']);
            $image_obj = convertImage($image);

            if ($image_obj && $image_obj->id) {
                if ($image_set) {
                    switch ($sec['template']) {
                    case 'image_left.tpl':
                    case 'image_top_left.tpl':
                    case 'image_float_left.tpl':
                        $page_content[] = sprintf('<div style="float: left; display : inline; margin : 0px 10px 10px 0px">%s</div>', $image_obj->getTag());
                        break;

                    default:
                        $page_content[] = sprintf('<div style="float : right; display : inline; margin : 0px 0px 10px 10px">%s</div>', $image_obj->getTag());
                    }
                } else {
                    $file_assoc = new FC_File_Assoc;
                    $file_assoc->file_type = FC_IMAGE;
                    $file_assoc->file_id = $image_obj->id;

                    if(PHPWS_Error::logIfError($file_assoc->save())) {
                        continue;
                    }
                    $image_sec['type_id'] = $file_assoc->id;
                    $image_sec['width']   = $image_obj->width;
                    $image_sec['height']  = $image_obj->height;
                    $db = new PHPWS_DB('ps_block');
                    $db->addValue($image_sec);
                    if (PHPWS_Error::logIfError($db->insert())) {
                        PHPWS_Core::log("Failed to save page block.", 'conversion.log');
                        continue;
                    }
                    $image_set = true;
                }
            }
        }

        $content = preg_replace('/module=pagemaster(&|&amp;)page_user_op=view_page(&|&amp;)page_id=/i', 'module=pagesmith&id=', $sec['text']);
        $content = preg_replace('/&MMN_position=\d+:\d+/', '', $content);

        $page_content[] = PHPWS_Text::parseInput(utf8_encode($content));
    }

    $text_sec['content'] = implode("\n", $page_content);
    
    $search = new Search($key_id);

    $search->addKeywords($text_sec['content']);
    $search->addKeywords($header_sec['content']);
    $search->save();

    $db = new PHPWS_DB('ps_text');
    $db->addValue($text_sec);
    PHPWS_Error::logIfError($db->insert());
}

function convertImage($data)
{
    if (empty($data['name'])) {
        return false;
    }

    $home_dir = Convert::getHomeDir();

    if (!isset($_SESSION['Folder_Id'])) {
        $folder = new Folder;
        $folder->title = _('PageSmith conversion');
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

    $image_dir = $image->getPath();

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
    $db = new PHPWS_DB('ps_page');
    $result = $db->updateSequenceTable();

    $db = new PHPWS_DB('ps_block');
    $result = $db->updateSequenceTable();

    $db = new PHPWS_DB('ps_text');
    $result = $db->updateSequenceTable();

}

?>