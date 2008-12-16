<?php

/**
 * Conversion file for phpWS Bulletin Board module
 *
 * @basecode Article Manager Module Conversion Script
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @version $Id: convert.php 4645 2007-04-13 17:38:26Z matt $
 */

PHPWS_Core::initModClass('phpwsbb', 'BB_Data.php');
PHPWS_Core::initModClass('phpwsbb', 'Forum.php');
PHPWS_Core::initModClass('phpwsbb', 'Topic.php');
PHPWS_Core::initModClass('comments', 'Comments.php');
PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
PHPWS_Core::initModClass('filecabinet', 'Folder.php');
PHPWS_Core::initModClass('filecabinet', 'File_Assoc.php');
PHPWS_Core::requireInc('filecabinet', 'defines.php');

// number of users to convert at a time. lower this number if you are having
// memory or timeout errors
define('BB_USER_BATCH_LIMIT', 30);

// number of threads to convert at a time. lower this number if you are having
// memory or timeout errors
define('BB_THREAD_BATCH_LIMIT', 60);

// If you want to import ip-based bans, set this to true
//Note: Be aware that some ISPs identify all user traffic by 1 ip address.
//Blocking it would prevent *all* of their users from accessing your website!
define('BAN_IPS', false);

function convert()
{
    $mod_list = PHPWS_Core::installModList();
    if (!in_array('phpwsbb', $mod_list))
    return _('phpWS Bulletin Board is not installed.');
    if (Convert::isConverted('phpwsbb'))
    return _('phpWS Bulletin Board has already been converted.');

    if (!isset($_REQUEST['mode'])) {
        $content[] = _('You may convert two different ways.');
        $content[] = sprintf('<a href="%s">%s</a>', 'index.php?command=convert&package=phpwsbb&mode=manual',
        _('Manual mode requires you to click through the conversion process.'));
        $content[] = sprintf('<a href="%s">%s</a>', 'index.php?command=convert&package=phpwsbb&mode=auto',
        _('Automatic mode converts the data without your interaction.'));

        $content[] = ' ';
        $content[] = _('If you encounter problems, you should use manual mode.');
        $content[] = _('Conversion will begin as soon as you make your choice.');

        return implode('<br />', $content);
    }
    if ($_REQUEST['mode'] == 'auto') {
        define('SHOW_WAIT', TRUE);
    } else {
        define('SHOW_WAIT', FALSE);
    }

    // Initialize session reference variables
    if (!isset($_SESSION['phpwsbb_img_ref']))
        $_SESSION['phpwsbb_img_ref'] = array();
    if (!isset($_SESSION['phpwsbb_conversion_map']))
        $_SESSION['phpwsbb_conversion_map'] = array();
        
    if (!Convert::isConverted('phpwsbb_forums')) {
        convert_phpwsbb_forums();
        Convert::addConvert('phpwsbb_forums');
        $content[] = _('All phpwsbb Forums have been converted');
        $content[] = sprintf('<a href="index.php?command=convert&package=phpwsbb&mode=%s">%s</a>',$_REQUEST['mode'] ,_('Continue conversion. . .'));
        return implode('<br />', $content);
    }

    if (!Convert::isConverted('phpwsbb_avatars')) {
        $lib = new PHPWS_IMGLib();
        //    test($lib->_galleries,0,1);
        foreach ($lib->_galleries AS $gallery => $title) {
            $lib->convertGallery($gallery, $title);
            //        echo 'This Gallery is called ""'.$title.'"<br />';
            //        $filelist = $lib->get_files($gallery);
            //        test($filelist,0,1);
        }

        Convert::addConvert('phpwsbb_avatars');
        $content[] = _('All phpwsbb Avatar Images have been converted');
        $content[] = sprintf('<a href="index.php?command=convert&package=phpwsbb&mode=%s">%s</a>',$_REQUEST['mode'] ,_('Continue conversion. . .'));
        return implode('<br />', $content);
    }

    if (!Convert::isConverted('phpwsbb_users')) {
        $content = convertUsers();
        return implode('<br />', $content);
    }

    if (!Convert::isConverted('phpwsbb_banlist')) {
        convertBannedUsers();
        Convert::addConvert('phpwsbb_banlist');
        $content[] = _('All phpwsbb Banned Users have been converted');
        $content[] = sprintf('<a href="index.php?command=convert&package=phpwsbb&mode=%s">%s</a>',$_REQUEST['mode'] ,_('Continue conversion. . .'));
        return implode('<br />', $content);
    }

    if (!Convert::isConverted('phpwsbb')) {
        $content = convert_phpwsbb_topics();
        return implode('<br />', $content);
    }
}

function convert_phpwsbb_forums()
{
    $db = Convert::getSourceDB('mod_phpwsbb_forums');
    if (empty($db)) {
        return array('phpwsbb is not installed in this database.');
    }
    $db->addWhere('approved', 1);
    $result = $db->select();
    $db->disconnect();

    Convert::siteDB();

    if (empty($result))
    return NULL;

    echo 'Converting Forums...';
    foreach ($result as $oldForum) {
        // Convert forum, keeping the same id#
        $forum = & new PHPWSBB_Forum();
        $forum->id = $oldForum['id'];
        $forum->title = $oldForum['label'];
        $forum->description = $oldForum['description'];
        $forum->sortorder = $oldForum['sortorder'];
        // Create an associated KEY
        $key = & new Key;
        $key->setModule('phpwsbb');
        $key->setItemName('forum');
        $key->setItemId($forum->id);
        $key->setEditPermission('manage_forums');
        $key->setUrl('index.php?module=phpwsbb&amp;var1=forum&amp;var2='.$forum->id);
        $key->setTitle($forum->title);
        $key->setSummary($forum->description);
        $key->active = $forum->active;
        $key->create_date = $oldForum['created'];
        $key->creator = $oldForum['owner'];
        $result = $key->save();
        if (PHPWS_Error::logIfError($result))
            exit(PHPWS_Error::printError($result));
        $forum->key_id = $key->id;
        // Save the new forum info
        $db = new PHPWS_DB('phpwsbb_forums');
        $result = $db->saveObject($forum, false, false);
        if (PHPWS_Error::logIfError($result))
            exit(PHPWS_Error::printError($result));
        // Transfer moderator information
        $mod_arr = explode(',', $oldForum['moderators']);
        if (!empty($mod_arr))
            foreach($mod_arr AS $value) {
                $db = new PHPWS_DB('phpwsbb_moderators');
                $db->addValue('forum_id', $forum->id);
                $db->addValue('user_id', $value);
                $result = $db->insert();
                PHPWS_Error::logIfError($result);
                unset($db);
            }
    }
    // Update the sequence table
    $db = new PHPWS_DB('phpwsbb_forums');
    $db->updateSequenceTable();
    unset($db);
    echo 'Done.';
    return TRUE;
}

class PHPWS_IMGLib {
    function PHPWS_IMGLib () {
        include(PHPWS_HOME_DIR . 'convert/images/phpwsbb/library/config.php');
    }
    function get_files ($gallery) {
        $path = PHPWS_HOME_DIR . 'convert/images/phpwsbb/library/'.$gallery.'/';
        PHPWS_File::appendSlash($path);
        if (!is_dir($path)) {
            echo 'Bad Directory!!!"<br />';
            return false;
        }

        $dir = dir($path);
        $filelist = array();
        while($file = $dir->read()) {
            if (strpos($file, '.') === 0) {
                // skips hidden files, directories and back references
                continue;
            }
            $filelist[] = $file;
        }
        return $filelist;
    }
    function convertGallery ($galleryid, $gallerytitle) {
        // Create the gallery folder
        $folder = new Folder;
        $folder->setTitle($gallerytitle);
        $folder->module_created = 'users';
        $folder->setDescription('User Avatar Images');
        $folder->public_folder = 1;
        $folder->max_image_dimension = COMMENT_MAX_AVATAR_WIDTH;
        if (!$folder->save())
            exit('Something went wrong with folder creation.  Please restore your database to pre-conversion state & check the error log');

        // Get a list of all files in this gallery
        $filelist = $this->get_files($galleryid);

        // If there are files, copy them over
        if (empty($filelist))
            return;
        foreach ($filelist AS $image_name) {
            $src = PHPWS_HOME_DIR . 'convert/images/phpwsbb/library/'.$galleryid.'/' . $image_name;
            $dst = PHPWS_HOME_DIR . $folder->getFullDirectory() . $image_name;
            $err = PHPWS_File::scaleImage($src, $dst, COMMENT_MAX_AVATAR_WIDTH, COMMENT_MAX_AVATAR_HEIGHT);
            if (PHPWS_Error::logIfError($err))
                exit(PHPWS_Error::printError($err));
            if (!$err)
                exit('Something went wrong with the image transfer.  Please restore your database to pre-conversion state & check the error log');

            // Get image specs
            $image_size = filesize($dst);
            $x = getimagesize($dst);
            $image_width  = $x[0];
            $image_height = $x[1];
            $image_type = image_type_to_mime_type($x[2]);

            // Add the file information to FileCabinet
            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $img = new PHPWS_Image();
            $img->file_name = $image_name;
            $img->setDirectory($folder->getFullDirectory());
            $img->setTitle('Avatar Image');
            $img->setAlt('Avatar Image');
            $img->setSize($image_size);
            $img->width = $image_width;
            $img->height = $image_height;
            $img->file_type = $image_type;
            $img->folder_id = $folder->id;
            $result = $img->save(0,0);
            if (PHPWS_Error::logIfError($result)) {
                exit(PHPWS_Error::printError($result));
            }
            // We have to add it to File_Assoc, too
            $file_assoc = new FC_File_Assoc;
            $file_assoc->file_type = FC_IMAGE;
            $file_assoc->file_id = $img->id;
            $result = $file_assoc->save();
            if(PHPWS_Error::logIfError($result)) {
                exit(PHPWS_Error::printError($result));
            }
            
            // add to the reference table
            $_SESSION['phpwsbb_img_ref']['/library/'.$galleryid.'/'.$image_name] = array($file_assoc->id, $img->getPath());
        }
    }
}

function convertUsers ()
{
    // If not known yet, find the first posting dates of all the users
    if (empty($_SESSION['phpwsbb_convert_firstpostdate'])) {
        $db = Convert::getSourceDB('mod_phpwsbb_messages');
        $db->addColumn('owner_id', null, null, false, true);
        $db->addColumn('created', 'min', 'firstpostdate');
        $db->setIndexBy('owner_id');
        $result = $db->select();
        $db->disconnect();
        if (PHPWS_Error::logIfError($result))
            exit(PHPWS_Error::printError($result));
        if (!empty($result))
            $_SESSION['phpwsbb_convert_firstpostdate'] = $result;
        else
            $_SESSION['phpwsbb_convert_firstpostdate'] = array();
    }

    $db = Convert::getSourceDB('mod_phpwsbb_user_info');
    if (empty($db)) {
        return array('phpwsbb is not installed in this database.');
    }

    $db->addOrder('user_id asc');

    $batch = & new Batches('convert_phpwsbb');

    $total_entries = $db->count();
    if ($total_entries < 1) {
        return array('No users to update.');
    }

    $batch->setTotalItems($total_entries);
    $batch->setBatchSet(BB_USER_BATCH_LIMIT);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }

    if (!$batch->load()) {
        $content[] = _('Batch previously run.');
    } else {
        // Run the Batch
        $start = $batch->getStart();
        $limit = $batch->getLimit();
        $db->setLimit($limit, $start);
        $result = $db->select();
        $db->disconnect();
        Convert::siteDB();

        PHPWS_Core::initModClass('access', 'Access.php');
        $db = new PHPWS_DB('phpwsbb_users');
        if (!empty($result)) {
            foreach ($result as $old_user) {
                if ($old_user['user_id']==1)
                    continue;
                convertUser($old_user);
                // Save logging information
                $db->addValue('user_id', $old_user['user_id']);
                $db->addValue('last_on', $old_user['last_on']);
                $db->addValue('last_activity', $old_user['session_start']);
                $result = $db->insert();
                if (PHPWS_Error::logIfError($result))
                exit(PHPWS_Error::printError($result));
                $db->reset();
            }
        }
    }

    $percent = $batch->percentDone();
    $content[] = Convert::getGraph($percent, SHOW_WAIT);
    $batch->completeBatch();

    if (!$batch->isFinished()) {
        $content[] = _('Converting phpwsbb Extended User Information...');
        if ($_REQUEST['mode'] == 'manual') {
            $content[] =  $batch->continueLink();
        } else {
            Convert::forward($batch->getAddress());
        }
    } else { // This was the last batch...
        $batch->clear();
        Convert::addConvert('phpwsbb_users');
        $content[] = _('All phpwsbb Extended User Information has been converted');
        $content[] = sprintf('<a href="index.php?command=convert&package=phpwsbb&mode=%s">%s</a>',$_REQUEST['mode'] ,_('Continue conversion. . .'));
    }
    return $content;
}

function convertUser ($old_user)
{
    if ($old_user['user_id']==1)
        return;
    $user = Comments::getCommentUser($old_user['user_id']);
    $user->comments_made = $old_user['posts'];
    $user->location = $old_user['location'];
    $user->signature = $old_user['signature'];
    $user->suspendmonitors = $old_user['suspendmonitors'];
    $user->monitordefault = $old_user['monitordefault'];
    if (!empty($_SESSION['phpwsbb_convert_firstpostdate'][$old_user['user_id']]['firstpostdate']))
        $user->joined_date = $_SESSION['phpwsbb_convert_firstpostdate'][$old_user['user_id']]['firstpostdate'];
    // Transfer avatar information
    if (!empty($old_user['avatar_file'])) {
        $errors = '';
        if (empty($old_user['avatar_dir'])) { // offsite image
            $remotefile = 'http://'.trim($old_user['avatar_file']);
            if($user->testAvatar($remotefile, $errors)) { // invalid remote images get thrown away
                $dim = @getimagesize($remotefile);
                $user->setAvatar($remotefile.'" width="'.$dim[0].'" height="'.$dim[1]);
                unset($dim);
            }
        }
        else { // library image
            if (!empty($_SESSION['phpwsbb_img_ref']['/'.$old_user['avatar_dir'].$old_user['avatar_file']])) {
                $arr = $_SESSION['phpwsbb_img_ref']['/'.$old_user['avatar_dir'].$old_user['avatar_file']];
                $user->avatar_id = $arr[0];
                $user->avatar = $arr[1];
            }
        }
    }

    if (!$user->saveUser())
    exit('Something went wrong while saving user#'.$old_user['user_id'].' ('.$user->display_name.').  Please restore your database to pre-conversion state & check the error log');
}

function convertBannedUsers ()
{
    $db = Convert::getSourceDB('mod_phpwsbb_banned');
    $list = $db->select();
    $db->disconnect();
    Convert::siteDB();
    if (empty($list))
    return;

    PHPWS_Core::initModClass('access', 'Access.php');
    $db = new PHPWS_DB('comments_users');

    foreach ($list AS $value) {
        if (!empty($list['username'])) {
            $db->addValue('locked', 1);
            $db->addWhere('display_name', $list['username']);
            $result = $db->update();
            if (PHPWS_Error::logIfError($result))
            exit(PHPWS_Error::printError($result));
            $db->reset();
        }
        if (BAN_IPS && !empty($list['ip'])) {
            Access::addIp($list['ip'], false);
        }
    }

}

function convert_phpwsbb_topics()
{
    $db = Convert::getSourceDB('mod_phpwsbb_threads');
    if (empty($db)) {
        return array('phpwsbb is not installed in this database.');
    }
    $db->addWhere('approved', 1);
    // The descending order is so that all free ids will be taken up first.
    $db->addOrder('id desc');

    $batch = & new Batches('convert_phpwsbb');

    $total_entries = $db->count();
    if ($total_entries < 1) {
        return array('No topics to convert.');
    }

    $batch->setTotalItems($total_entries);
    $batch->setBatchSet(BB_THREAD_BATCH_LIMIT);

    if (isset($_REQUEST['reset_batch'])) {
        $batch->clear();
    }

    if (!$batch->load()) {
        $content[] = _('Batch previously run.');
    } else {
        // Run the Batch
        $start = $batch->getStart();
        $limit = $batch->getLimit();
        $db->setLimit($limit, $start);
        $result = $db->select();
        $db->disconnect();
        Convert::siteDB();

        if (!empty($result)) {
            foreach ($result as $oldEntry) {
                convert_phpwsbb_topic($oldEntry);
            }
        }
    }

    $percent = $batch->percentDone();
    $content[] = Convert::getGraph($percent, SHOW_WAIT);
    $batch->completeBatch();

    if (!$batch->isFinished()) {
        $content[] = _('Converting phpwsbb Topics');
        if ($_REQUEST['mode'] == 'manual') {
//if (true) {
            $content[] =  $batch->continueLink();
        } else {
            Convert::forward($batch->getAddress());
        }
    } else { // This was the last batch...
        // Save the topicId cross-reference array to file
        if (!empty($_SESSION['phpwsbb_conversion_map']) 
                && !file_put_contents(PHPWS_HOME_DIR . 'config/phpwsbb/cross-reference.inc', serialize($_SESSION['phpwsbb_conversion_map'])))
            exit('Could not save TopicId cross-reference array to file');
        // Make sure that the _seq table is correct
        $db = new PHPWS_DB('comments_threads');
        $db->updateSequenceTable();
        $db = new PHPWS_DB('comments_items');
        $db->updateSequenceTable();
        $batch->clear();
        Convert::addConvert('phpwsbb');
        $content[] =  _('All done!');
        $content[] = '<a href="index.php">' . _('Go back to main menu.') . '</a>';
    }
    return $content;
}

function convert_phpwsbb_topic($entry)
{
    // Load all replies to this thread, just to make sure we have some
    $db = Convert::getSourceDB('mod_phpwsbb_messages');
    $db->addWhere('tid', $entry['id']);
    $db->addOrder('id asc');
    $old_comments = $db->select();
    $db->disconnect();
    Convert::siteDB();
    if (empty($old_comments))
        return;

    // If this Comment_Thread id is already taken....
    $db = new PHPWS_DB('comments_threads');
    $db->addColumn('id');
    $db->addWhere('id', $entry['id']);
    $result = $db->select('one');
    if (PHPWS_Error::logIfError($result))
    exit(PHPWS_Error::printError($result));
    if (!empty($result)) {
        // find the next free id#.
        $db->reset();
        $db->addColumn('id', 'max');
        $max_id = $db->select('one');
        if (PHPWS_Error::logIfError($max_id))
        exit(PHPWS_Error::printError($max_id));
        if ($max_id < 1)
        $max_id = 0;
        // Assign its new id to a translation table
        $newid = $_SESSION['phpwsbb_conversion_map'][$entry['id']] = ++$max_id;
    }
    else
        $newid = $entry['id'];

    // Import replies to this topic
    // this is kinda backwards, but we'll need the lastpost information to save a db query & update
    importComments($old_comments, $newid);

    // Create the topic now that we have an id
    $topic = new PHPWSBB_Topic();
    $topic->id = $newid;
    $topic->fid = $entry['fid'];
    $topic->sticky = $entry['sticky'];
    $topic->locked = $entry['locked'];
    $topic->title = $entry['label'];
    $topic->summary = $old_comments[0]['body'];
    $topic->active = !(bool) $entry['hidden'];
    $topic->create_date = $entry['created'];
    $topic->creator = $entry['owner'];
    $topic->creator_id = $old_comments[0]['owner_id']; //No need to query. Its the first message author
//    $topic->times_viewed = $entry['views'];
    if (!$topic->commit(true))
        exit('There was an error when saving this topic to the database!  Please restore your database to pre-conversion state & check the error log');
    // Create attached Comment_Thread
    $thread = new Comment_Thread;
    $thread->id = $newid;
    $thread->key_id = $topic->key_id;
    $thread->total_comments = count($old_comments);
    $thread->last_poster = $old_comments[count($old_comments) - 1]['owner'];
    $forum = $topic->get_forum();
    $thread->allowAnonymous($forum->allow_anon);
    $thread->setApproval($forum->default_approval);
    $db = new PHPWS_DB('comments_threads');
    $result = $db->saveObject($thread, false, false);
    if (PHPWS_Error::logIfError($result))
        exit(PHPWS_Error::printError($result));

    // Update Lastpost stats
    $topic->update_topic();
    $topic->commit();

    // Convert Monitors
    $db = Convert::getSourceDB('mod_phpwsbb_monitors');
    $db->addColumn('user_id');
    $db->addWhere('thread_id', $entry['id']);
    $result = $db->select('col');
    $db->disconnect();
    Convert::siteDB();
    if (PHPWS_Error::logIfError($result))
    exit(PHPWS_Error::printError($result));
    if (!empty($result)) {
        $db = & new PHPWS_DB('comments_monitors');
        foreach ($result AS $user_id) {
            $db->addValue('thread_id', $newid);
            $db->addValue('user_id', $user_id);
            $result = $db->insert();
            if (PHPWS_Error::logIfError($result))
            exit(PHPWS_Error::printError($result));
            $db->reset();
        }
    }

}

function importComments(&$replies, $thread_id)
{
    $db = new PHPWS_DB('comments_items');

    foreach ($replies as $reply) {
        $val = array();
        $val['thread_id']   = $thread_id;
        $val['subject']     = $reply['label'];
        $val['entry']       = $reply['body'];
        $val['anon_name']   = $reply['guestname'];
        $val['author_id']   = $reply['owner_id'];
        $val['author_ip']   = $reply['ip'];
        $val['create_time'] = $reply['created'];
        $val['edit_time']   = $reply['updated'];
        $val['edit_author'] = $reply['editor'];
        $val['approved']    = $reply['approved'] || $reply['hidden'];
        $db->addValue($val);
        $result = $db->insert();
        if (PHPWS_Error::logIfError($result))
        exit(PHPWS_Error::printError($result));
        $db->reset();
    }
}

?>
