<?php

function convert()
{
    if (Convert::isConverted('webpage')) {
        return _('Web pages have already been converted.');
    }


    $db = Convert::getSourceDB('mod_pagemaster_pages');
    
    $batch = & new Batches('convert_pagemaster');
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
    }

    if (is_array($result)) {
        $content[] = _('Some errors occurred when trying to convert the following pages:');
        $content[] = '<ul><li>' . implode('</li><li>', $result) . '</li></ul>';
    }

    $content[] = sprintf('%s&#37; done<br>', $batch->percentDone());

    $batch->completeBatch();

    
    if (!$batch->isFinished()) {
        $content[] =  $batch->continueLink();
    } else {
        $batch->clear();
        Convert::addConvert('webpage');
        $content[] =  _('All done!');
        $content[] = '<a href="index.php">' . _('Go back to main menu.') . '</a>';
    }
    
    return implode('<br />', $content);
}

function runBatch(&$db, &$batch)
{
    $start = $batch->getStart();
    $limit = $batch->getLimit();
    $db->setLimit($start, $limit);
    $result = $db->select();
    $db->disconnect();

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
    $db = & new PHPWS_DB('webpage_volume');

    $val['id']           = $page['id'];
    $val['title']        = strip_tags($page['title']);
    $val['date_created'] = strtotime($page['created_date']);
    $val['date_updated'] = strtotime($page['updated_date']);
    $val['created_user'] = $page['created_username'];
    $val['updated_user'] = $page['updated_username'];
    $val['frontpage']    = (int)$page['mainpage'];

    $key = & new Key;
    $key->setItemId($val['id']);
    $key->setModule('webpage');
    $key->setItemName('volume');
    $key->setEditPermission('edit_page');
    $key->setTitle($val['title']);

    $url = 'index.php?module=webpage&amp;id=' . $val['id'];

    $key->setUrl($url);
    $result = $key->save();
    $val['key_id'] = $key->id;


    $db->addValue($val);
    $result = $db->insert(FALSE);
    
    if (PEAR::isError($result)) {
        return FALSE;
    }

    convertSection($page['section_order'], $val['id'], $val['title']);
}

function convertSection($section_order, $volume_id, $title)
{
    $section_order = unserialize($section_order);

    $db = Convert::getSourceDB('mod_pagemaster_sections');
    $db->addWhere('id', $section_order, 'in', 'or');
    $db->setIndexBy('id');
    $sections = $db->select();
    $db->disconnect();

    saveSections($sections, $volume_id, $title);
}

function saveSections($sections, $volume_id, $title)
{
    $db = & new PHPWS_DB('webpage_page');
    $pages = 1;
    foreach ($sections as $sec) {
        $val['id']          = $sec['id'];
        $val['volume_id']   = $volume_id;
        if (empty($sec['title'])) {
            $val['title']   = sprintf(_('%s - Page %s'), $title, $pages);
        } else {
            $val['title']   = $sec['title'];
        }
        $val['content']     = $sec['text'];
        $val['page_number'] = $pages;
        $val['template']    = 'basic.tpl';

        if (!empty($sec['image'])) {
            $image = unserialize($sec['image']);
            if (is_array($image) && isset($image['name'])) {
                $image_link = sprintf('<img src="%s" width="%s" height="%s" alt="%s" title="%s" />',
                                      'images/webpage/' . $image['name'],
                                      $image['width'],
                                      $image['height'],
                                      $image['alt'],
                                      $image['alt']);
                $val['content'] .= $image_link;
            }
        }
        $pages++;
        $db->addValue($val);
        $result = $db->insert(FALSE);
        $db->reset();
    }
    $db->disconnect();
}

?>