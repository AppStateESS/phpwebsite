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

function convert()
{
    if (Convert::isConverted('webpage')) {
        return _('Web pages have already been converted.');
    }

    $mod_list = PHPWS_Core::installModList();

    if (!in_array('webpage', $mod_list)) {
        return _('Web Page is not installed.');
    }

    if (!isset($_SESSION['Webpage_Method'])) {
        if (isset($_GET['webpage_method'])) {
            $_SESSION['Webpage_Method'] = $_GET['webpage_method'];
        } else {
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
    //    $content[] = sprintf('%s&#37; done<br>', $batch->percentDone());

    $batch->completeBatch();
    
    if (!$batch->isFinished()) {
        Convert::forward($batch->getAddress());
        //        $content[] =  $batch->continueLink();
    } else {
        createSeqTables();
        $batch->clear();
        Convert::addConvert('webpage');
        Convert::addConvert('pagesmith');
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
    
    if (PEAR::isError($result)) {
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
                $page->title = null;
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

        $page_content[] = utf8_encode($sec['text']);

        if (!empty($sec['image'])) {
            $image = @unserialize($sec['image']);
            if (is_array($image) && isset($image['name'])) {
                $image_link = sprintf('<img src="%s" width="%s" height="%s" alt="%s" title="%s" />',
                                      'images/webpage/' . utf8_encode($image['name']),
                                      $image['width'],
                                      $image['height'],
                                      $image['alt'],
                                      $image['alt']);
                $page_content[] = $image_link;
            }
        }

        $pages++;

        if ($method == 'sep') {
            $page->setContent(implode("\n", $page_content));
            $result = $page->save();
            if (PEAR::isError($result)) {
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
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
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