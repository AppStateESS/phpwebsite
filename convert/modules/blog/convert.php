<?php

  /**
   * Blog conversion file
   *
   * Transfers announcement modules items to blog
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('search', 'Search.php');

function convert()
{
    if (Convert::isConverted('blog')) {
        return _('Blog has already been converted.');
    }

    $mod_list = PHPWS_Core::installModList();

    if (!in_array('blog', $mod_list)) {
        return _('Blog is not installed.');
    }

    $db = Convert::getSourceDB('mod_announce');
    $db->addWhere('approved', 1);

    $batch = & new Batches('convert_blog');

    $total_entries = $db->count();
    if ($total_entries < 1) {
        return _('No announcements to convert.');
    }

    $batch->setTotalItems($total_entries);
    $batch->setBatchSet(20);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }
 

    if (!$batch->load()) {
        $content[] = _('Batch previously run.');
    } else {
        $result = runBatch($db, $batch);
    }

    $content[] = sprintf('%s&#37; done<br>', $batch->percentDone());

    $batch->completeBatch();

    
    if (!$batch->isFinished()) {
        $content[] =  $batch->continueLink();
    } else {
        createSeqTable();
        $batch->clear();
        Convert::addConvert('blog');
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
        foreach ($result as $oldEntry) {
            $result = convertAnnouncement($oldEntry);
            if ($result) {
                $errors[] = $oldEntry['title'];
            }
        }
    }

    if (isset($errors)) {
        return $errors;
    } else {
        return TRUE;
    }
}

function convertAnnouncement($entry)
{
    $db = & new PHPWS_DB('blog_entries');

    $val['id']      = $entry['id'];
    $val['title']   = strip_tags($entry['subject']);
    $val['entry']   = $entry['summary'];
    if (!empty($entry['body'])) {
        $val['entry'] .= '<br /><br />' . $entry['body'];
    }

    $val['author']  = $entry['userCreated'];
    $val['date']    = strtotime($entry['dateCreated']);

    if (!empty($entry['image'])) {
        $image = unserialize($entry['image']);
        if (is_array($image) && isset($image['name'])) {
            $image_link = sprintf('<img src="%s" width="%s" height="%s" alt="%s" title="%s" />',
                                  'images/blog/' . $image['name'],
                                  $image['width'],
                                  $image['height'],
                                  $image['alt'],
                                  $image['alt']);
            $val['entry'] .= $image_link;
        }
    }

    $key = & new Key;
    $key->setItemId($val['id']);
    $key->setModule('blog');
    $key->setItemName('entry');
    $key->setEditPermission('edit_blog');
    $key->setUrl('index.php?module=blog&action=view_comments&id=' . $val['id']);
    $key->setTitle($val['title']);
    $key->save();
    $val['key_id'] = $key->id;

    $db->addValue($val);
    $result = $db->insert(FALSE);

    $search = & new Search($key->id);
    $search->addKeywords($val['entry']);
    $search->addKeywords($val['title']);
    $search->save();


    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
    }

}

function createSeqTable()
{
    $db = new PHPWS_DB('blog_entries');
    $result = $db->updateSequenceTable();
}


?>