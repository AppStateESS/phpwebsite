<?php

  /**
   * Blog conversion file
   *
   * Transfers announcement modules items to blog
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

  // number of blogs to convert at a time. lower this number if you are having
  // memory or timeout errors
define('BLOG_BATCH', 15);

// Must be in YYYY-MM-DD format.
// If you want to convert all your announcements, leave this line commented out.
//define('IGNORE_BEFORE', '2006-01-01');

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

    if (!isset($_REQUEST['mode'])) {
        $content[] = _('You may convert to different ways.');
        $content[] = sprintf('<a href="%s">%s</a>', 'index.php?command=convert&package=blog&mode=manual',
                             _('Manual mode requires you to click through the conversion process.'));
        $content[] = sprintf('<a href="%s">%s</a>', 'index.php?command=convert&package=blog&mode=auto',
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
        $db = Convert::getSourceDB('mod_announce');
        $db->addWhere('approved', 1);

        if (defined('IGNORE_BEFORE')) {
            $db->addWhere('dateCreated', IGNORE_BEFORE, '>=');
        }

        $batch = & new Batches('convert_blog');

        $total_entries = $db->count();
        if ($total_entries < 1) {
            return _('No announcements to convert.');
        }

        $batch->setTotalItems($total_entries);
        $batch->setBatchSet(BLOG_BATCH);

        if (isset($_REQUEST['reset_batch'])) {
            $batch->clear();
        }

        if (!$batch->load()) {
            $content[] = _('Batch previously run.');
        } else {
            $result = runBatch($db, $batch);
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
            createSeqTable();
            $batch->clear();
            Convert::addConvert('blog');
            $content[] =  _('All done!');
            $content[] = '<a href="index.php">' . _('Go back to main menu.') . '</a>';
        }
    
        return implode('<br />', $content);
    }
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
    if (!$entry['approved']) {
        continue;
    }


    $val['id']      = $entry['id'];
    $val['title']   = strip_tags($entry['subject']);
    $val['entry']   = $entry['summary'];

    if (!empty($entry['body'])) {
        $val['entry'] .= '<br /><br />' . $entry['body'];
    }

    $val['author']  = $entry['userCreated'];
    $val['create_date']    = strtotime($entry['dateCreated']);
    $val['approved']       = $entry['approved'];

    if (!empty($entry['image']) && $entry['image'] != 'Array') {
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
    return $db->updateSequenceTable();
}
?>