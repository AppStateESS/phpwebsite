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
define('BLOG_BATCH', 10);

// Must be in YYYY-MM-DD format.
// If you want to convert all your announcements, leave this line commented out.
//define('IGNORE_BEFORE', '2006-01-01');


// If you do not want to convert comments, set this to false
define('CONVERT_COMMENTS', true);

PHPWS_Core::initModClass('search', 'Search.php');

function htmlallspecialchars_decode($string)
{
    return html_entity_decode(preg_replace("/&#([0-9]+);/e", "chr($1)", $string));
}

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
        $content[] = _('You may convert two different ways.');
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
        if (empty($db)) {
            return _('Announcements is not installed in this database.');
        }
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
            unset($_SESSION['Authors']);
        }
    
        return implode('<br />', $content);
    }
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
    $db = new PHPWS_DB('blog_entries');
    if (!$entry['approved']) {
        continue;
    }

    $val['id']      = $entry['id'];
    $val['title']   = PHPWS_Text::parseInput(utf8_encode(htmlallspecialchars_decode(strip_tags($entry['subject']))));
    $val['summary'] = PHPWS_Text::parseInput(PHPWS_Text::breaker(utf8_encode(htmlallspecialchars_decode($entry['summary']))));

    if (!empty($entry['body'])) {
        $val['entry'] = PHPWS_Text::parseInput(PHPWS_Text::breaker(utf8_encode(htmlallspecialchars_decode($entry['body']))));
    }

    $val['author']  = $entry['userCreated'];
    $val['create_date']    = strtotime($entry['dateCreated']);
    $val['publish_date']   = $val['create_date'];
    $val['approved']       = $entry['approved'];
    $val['allow_comments'] = $entry['comments'];

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

    if (MOD_REWRITE_ENABLED) {
        $url = 'blog/' . $val['id'];
    } else {
        $url = 'index.php?module=blog&id=' . $val['id'];
    }

    $key = new Key;
    $key->create_date = $val['create_date'];
    $key->setItemId($val['id']);
    $key->setModule('blog');
    $key->setItemName('entry');
    $key->setEditPermission('edit_blog');
    $key->setUrl($url);
    $key->setTitle($val['title']);
    $key->setSummary($val['summary']);
    $key->save();
    $val['key_id'] = $key->id;

    $db->addValue($val);
    $result = $db->insert(FALSE);

    $search = & new Search($key->id);
    $search->addKeywords($val['summary']);
    if (!empty($val['entry'])) {
        $search->addKeywords($val['entry']);
    }
    $search->addKeywords($val['title']);
    $search->save();

    if (PEAR::isError($result)) {
        PHPWS_Error::log($result);
    }

    if (CONVERT_COMMENTS && $val['allow_comments']) {
        $cm_db = Convert::getSourceDB('mod_comments_data');
        $cm_db->addWhere('module', 'announce');
        $cm_db->addWhere('itemId', $val['id']);
        $comment_list = $cm_db->select();

        $cm_db->disconnect();
        Convert::siteDB();
        if (!empty($comment_list)) {
            convertComments($comment_list, $val['key_id']);
        }
    }

}

function createSeqTable()
{
    $db = new PHPWS_DB('blog_entries');
    return $db->updateSequenceTable();
}

function convertComments($comments, $key_id)
{
    $db = & new PHPWS_DB('comments_threads');
    $db->addValue('key_id', $key_id);
    $thread_id = $db->insert();

    if (PEAR::isError($thread_id)) {
        PHPWS_Error::log($thread_id);
        return;
    } elseif (!$thread_id) {
        return;
    }

    $db2 = & new PHPWS_DB('comments_items');
    $count = 0;
    
    foreach ($comments as $comment) {
        $author_id = buildAuthor($comment['author']);

        $count++;
        $val = array();
        $val['id']          = &$comment['cid'];
        $val['thread_id']   = &$thread_id;
        $val['parent']      = (int)$comment['pid'];
        $val['author_ip']   = &$comment['authorIp'];
        $val['author_id']   = $author_id;
        $val['subject']     = &$comment['subject'];
        $val['entry']       = &$comment['comment'];
        $val['edit_author'] = &$comment['editor'];
        $val['create_time'] = strtotime($comment['postDate']);
        if ($comment['editDate'] != '0000-00-00 00:00:00') {
            $val['edit_time']   = strtotime($comment['editDate']);
            $val['edit_reason'] = &$comment['editReason'];
        }
        $db2->addValue($val);

        $result = $db2->insert(false);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
        }
        $db2->reset();
    }

    $db2->updateSequenceTable();

    $db->reset();
    $db->addWhere('id', $thread_id);
    $db->addValue('total_comments', $count);
    $db->update();
}

function buildAuthor($username)
{
    $db = Convert::getSourceDB('mod_users');
    $db->addWhere('username', $username);
    $db->addColumn('user_id');
    $user_id = $db->select('one');
    $db->disconnect();
    Convert::siteDB();

    if ($user_id == 1) {
        return FALSE;
    }

    if (!$user_id || PEAR::isError($user_id)) {
        return FALSE;
    }

    $db2 = & new PHPWS_DB('comments_users');
    if (isset($_SESSION['Authors']) && in_array($user_id, $_SESSION['Authors'])) {
        $db2->addWhere('user_id', $user_id);
        $db2->incrementColumn('comments_made');
        return $user_id;
    }


    $db2->addValue('user_id', $user_id);
    $db2->addValue('display_name', $username);
    $db2->addValue('comments_made', 1);
    $db2->addValue('joined_date', mktime());
    $db2->insert();

    $db3 = & new PHPWS_DB('demographics');
    $db3->addValue('user_id', $user_id);
    $db3->insert();

    $_SESSION['Authors'][] = $user_id;
    return $user_id;
}

?>