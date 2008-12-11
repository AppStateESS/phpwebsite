<?php

/**
 * Developer class for accessing comments
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

// This will be set by config and cookie later
define('CURRENT_VIEW_MODE', 3);

define('COMMENTS_MISSING_DEFAULT_RANK', 2);

PHPWS_Core::initModClass('comments', 'Comment_Thread.php');
PHPWS_Core::initModClass('comments', 'Comment_User.php');

class Comments {

    /**
     * Returns the comment thread object associated with a specific key
     */
    public function getThread($key=NULL)
    {
        if (empty($key)) {
            $key = Key::getCurrent();
        }

        if (!Key::isKey($key)) {
            if (is_numeric($key)) {
                $key = new Key((int)$key);
            } else {
                return NULL;
            }
        }

        if ( empty($key) || $key->isDummy() || PEAR::isError($key->_error) ) {
            return NULL;
        }

        $thread = new Comment_Thread;

        $thread->key_id = $key->id;
        $thread->_key = $key;
        $thread->buildThread();

        return $thread;
    }

    public function getCommentUser($user_id)
    {
        if (isset($GLOBALS['Comment_Users'][$user_id])) {
            return $GLOBALS['Comment_Users'][$user_id];
        }

        $user = new Comment_User($user_id);

        if (!$user_id) {
            return $user;
        }

        // If we're loading the current user, make sure that the cached userdata is up to date
        if ($user_id == Current_User::getId()) {
            $user->setCachedItems();
        } 

        if ($user->isNew()) {
            $result = $user->saveUser();
        }

        $GLOBALS['Comment_Users'][$user_id] = $user;
        return $GLOBALS['Comment_Users'][$user_id];
    }

    public function updateCommentUser($user_id)
    {
        if (empty($user_id)) {
            return;
        }

        $user = Comments::getCommentUser($user_id);

        if (!empty($user->user_id)) {
            $user->bumpCommentsMade();
        }
    }


    public function panel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        $tabs['settings'] = array('title'=>dgettext('comments', 'Settings'), 'link'=>'index.php?module=comments');

        $tabs['ranks'] = array('title'=>dgettext('comments', 'Member ranks'), 'link'=>'index.php?module=comments');

        $db = new PHPWS_DB('comments_items');
        $db->addColumn('id');
        $db->addWhere('reported', 0, '>');
        $count = $db->count();

        $tabs['report'] = array('title'=> sprintf(dgettext('comments', 'Reported (%s)'), $count),
                                'link'=>'index.php?module=comments');
        $db->resetWhere();
        $db->addWhere('approved', 0);
        $count = $db->count();
        $tabs['approval'] = array('title'=> sprintf(dgettext('comments', 'Approval (%s)'), $count),
                                  'link'=>'index.php?module=comments');


        $panel = new PHPWS_Panel('comments');
        $panel->quickSetTabs($tabs);
        $panel->enableSecure();
        return $panel;
    }

    /**
     * Authorization checked in index.php
     */
    public function adminAction($command)
    {
        $panel = Comments::panel();
        $content = NULL;
        if (!empty($_REQUEST['cm_id'])) {
            if (is_array($_REQUEST['cm_id']))
                $comments = $_REQUEST['cm_id'];
            else
                $comments = array($_REQUEST['cm_id']);
        }

        switch ($command) {
        case 'delete_comment':
            foreach ($comments AS $cm_id) {
                $comment = new Comment_Item((int) $cm_id);
                $comment->delete();
            }
            PHPWS_Core::goBack();
            return;
            break;

        case 'approve':
            // Admin approved a comment
            $comment = new Comment_Item($_REQUEST['cm_id']);
            $comment->approve();
            PHPWS_Core::goBack();
            break;

        case 'remove':
            $comment = new Comment_Item($_REQUEST['cm_id']);
            $comment->delete(false);
            PHPWS_Core::goBack();
            break;

        case 'report':
            $panel->setCurrentTab('report');
            PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
            $content = Comment_Forms::reported();
            break;

        case 'move_comments':
            // If phpwsbb is installed...
            if (isset($GLOBALS['Modules']['phpwsbb'])) {
                PHPWS_Core::initModClass('phpwsbb', 'BB_Forms.php');
                $content = PHPWSBB_Forms::move_comments($comments);
            } else
                $content = dgettext('comments', 'Sorry, module phpwsBB is not installed.');
            break;

        case 'ranks':
            $panel->setCurrentTab('ranks');
            if (Current_User::allow('comments', 'settings')) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                $content = Comment_Forms::ranksForm();
            } else {
                $content = dgettext('comments', 'Sorry, but you do not have rights to alter ranks.');
            }

            break;

        case 'split_comments':
            // If phpwsbb is installed...
            if (isset($GLOBALS['Modules']['phpwsbb'])) {
                PHPWS_Core::initModClass('phpwsbb', 'BB_Forms.php');
                $content = PHPWSBB_Forms::split_comments($comments);
            } else
                $content = dgettext('comments', 'Sorry, module phpwsBB is not installed.');
            break;

        case 'settings':
            $panel->setCurrentTab('settings');
            if (Current_User::allow('comments', 'settings')) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                $content = Comment_Forms::settingsForm();
            } else {
                $content = dgettext('comments', 'Sorry, but you do not have rights to alter settings.');
            }
            break;

        case 'approve_all':
            if (!empty($_POST['cm_id'])) {
                Comments::multipleApprove($_POST['cm_id']);
            }
            PHPWS_Core::reroute('index.php?module=comments&tab=approval&authkey=' . Current_User::getAuthKey());
            break;

        case 'remove_all':
            if (!empty($_POST['cm_id'])) {
                Comments::multipleRemove($_POST['cm_id']);
            }
            PHPWS_Core::reroute('index.php?module=comments&tab=approval&authkey=' . Current_User::getAuthKey());
            break;

        case 'approval':
            // Basic comment permissions allow approval
            PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
            $panel->setCurrentTab('approval');
            $content = Comment_Forms::approvalForm();
            break;

        case 'post_rank':
            if (Current_User::authorized('comments', 'settings')) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                Comment_Forms::postRank(Comments::loadRank());
                PHPWS_Core::goBack();
            } else {
                $content = dgettext('comments', 'Sorry, but you do not have rights to alter settings.');
            }
            break;

        case 'delete_rank':
            if (Current_User::authorized('comments', 'settings')) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                $rank = Comments::loadRank();
                if ($rank->group_id) {
                    $rank->delete();
                }
                PHPWS_Core::goBack();
            } else {
                $content = dgettext('comments', 'Sorry, but you do not have rights to alter settings.');
            }
            break;

        case 'create_rank':
            if (Current_User::authorized('comments', 'settings')) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                Comment_Forms::postRank(Comments::loadRank(), true);
                PHPWS_Core::goBack();
            } else {
                $content = dgettext('comments', 'Sorry, but you do not have rights to alter settings.');
            }
            break;

        case 'post_user_rank':
            if (Current_User::authorized('comments', 'settings')) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                Comment_Forms::postUserRank();
                PHPWS_Core::goBack();
            } else {
                $content = dgettext('comments', 'Sorry, but you do not have rights to alter settings.');
            }
            break;

        case 'drop_user_rank':
            if (Current_User::authorized('comments', 'settings')) {
                PHPWS_Core::initModClass('comments', 'User_Rank.php');
                $user_rank = new Comment_User_Rank($_GET['user_rank_id']);
                $user_rank->delete();
                PHPWS_Core::goBack();
            } else {
                Current_User::disallow();
            }
            break;

        case 'post_settings':
            if (Current_User::authorized('comments', 'settings')) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                Comment_Forms::postSettings();
                PHPWS_Core::goBack();
            } else {
                $content = dgettext('comments', 'Sorry, but you do not have rights to alter settings.');
            }
            break;

        case 'set_anon_posting':
            $thread =  new Comment_Thread((int) @$_REQUEST['thread_id']);
            if ($thread->userCan()) {
                $thread->setAnonPosting((int) @$_REQUEST['allow']);
            }
            PHPWS_Core::goBack();
            break;

        case 'unlock_user':
        case 'lock_user':
            if (Current_User::authorized('comments', 'punish_users')) {
                $user = new PHPWS_User($_GET['id']);
                if ($user->id && $user->allow('comments')) {
                    exit();
                }

                $cuser = Comments::getCommentUser($_GET['id']);

                if ($cuser->user_id) {
                    if ($_GET['aop'] == 'lock_user') {
                        $cuser->locked = 1;
                        printf('<a href="#" onclick="punish_user(%s, this, \'unlock_user\'); return false;">%s</a>',
                               $cuser->user_id, dgettext('comments', 'Unlock user'));
                    } else {
                        $cuser->locked = 0;
                        printf('<a href="#" onclick="punish_user(%s, this, \'lock_user\'); return false;">%s</a>',
                               $cuser->user_id, dgettext('comments', 'Lock user'));
                    }
                    $cuser->save();
                }
            }
            exit();
            break;

        case 'clear_report':
            if (Current_User::authorized('comments', 'punish_users')) {
                foreach  ($comments as $cm_id) {
                    $comment = new Comment_Item((int) $cm_id);
                    $comment->reported = 0;
                    PHPWS_Error::logIfError($comment->save());
                }
            }
            PHPWS_Core::goBack();
            break;

        case 'unban_user':
        case 'ban_user':
            if (Current_User::authorized('users', 'ban_users')) {
                $user = new PHPWS_User($_GET['id']);
                if ($user->id && $user->allow('users')) {
                    exit();
                }
                if ($user->id) {
                    if ($_GET['aop'] == 'ban_user') {
                        $user->active = 0;
                        printf('<a href="#" onclick="punish_user(%s, this, \'unban_user\'); return false;">%s</a>',
                               $user->id, dgettext('comments', 'Unban user'));

                    } else {
                        $user->active = 1;
                        printf('<a href="#" onclick="punish_user(%s, this, \'ban_user\'); return false;">%s</a>',
                               $user->id, dgettext('comments', 'Ban user'));
                    }
                    $user->save();
                }
            }
            exit();
            break;

        case 'deny_ip':
            if (Current_User::authorized('access')) {
                PHPWS_Core::initModClass('access', 'Access.php');
                Access::addIp($_GET['id'], false);
                echo sprintf('<a href="#" onclick="punish_user(\'%s\', this, \'remove_deny_ip\'); return false;">%s</a>',
                             $_GET['id'], dgettext('comments', 'Remove IP denial'));
            }
            exit();
            break;

        case 'remove_deny_ip':
            if (Current_User::authorized('access')) {
                PHPWS_Core::initModClass('access', 'Access.php');

                Access::removeIp($_GET['id'], false);
                echo sprintf('<a href="#" onclick="punish_user(\'%s\', this, \'deny_ip\'); return false;">%s</a>',
                             $_GET['id'], dgettext('comments', 'Deny IP address'));
            }
            exit();
            break;


        case 'punish_user':
            if (!Current_User::authorized('comments', 'punish_users')) {
                Current_User::disallow();
            }
            $comment = new Comment_Item($_REQUEST['cm_id']);
            if ($comment->id) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                $content = Comment_Forms::punishForm($comment);
            }
            Layout::nakedDisplay($content);
            break;

        case 'lock_thread':
            $thread = & new Comment_Thread((int) @$_REQUEST['thread_id']);
            if ($thread->userCan())
                $thread->setLock((int) @$_REQUEST['lock']);
            PHPWS_Core::reroute($thread->_key->url);
            break;

        case 'recalc_userposts':
            $where = '';
            if (!empty($_REQUEST['user']))
                $where = ' WHERE user_id = '. (int) $_REQUEST['user'];
            $db = new PHPWS_DB('comments_threads');
            $sql = 'UPDATE comments_users SET comments_made = (SELECT COUNT(ID) FROM comments_items WHERE comments_items.author_id = comments_users.user_id)'.$where;
            $result = $db->query($sql);
            PHPWS_Error::logIfError($result);
            $content = dgettext('comments', 'All user postcounts have been recalculated');
            break;

        case 'delete_all_user_comments':
            if (Current_User::authorized('comments', 'delete_comments') && !empty($_GET['aid'])) {
                Comments::deleteAllUserComments($_GET['aid']);
            }
            PHPWS_Core::goBack();
            break;

        case 'delete_all_ip_comments':
            if (Current_User::authorized('comments', 'delete_comments') && !empty($_GET['aip'])) {
                Comments::deleteAllUserComments(0, $_GET['aip']);
            }
            PHPWS_Core::goBack();
            break;
            

        default:
            PHPWS_Core::errorPage('404');
        }
        $panel->setContent($content);
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    public function userAction($command)
    {
        $title = NULL;
        if (isset($_REQUEST['thread_id'])) {
            $thread = new Comment_Thread($_REQUEST['thread_id']);
        } else {
            $thread = new Comment_Thread;
        }

        if (isset($_REQUEST['cm_id'])) {
            $c_item = new Comment_Item($_REQUEST['cm_id']);
        } else {
            $c_item = new Comment_Item;
        }

        switch ($command) {
        case 'post_comment':
            if ($thread->canComment()) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                $title = dgettext('comments', 'Post Comment');
                $content[] = Comment_Forms::form($thread, $c_item);
            } else {
                PHPWS_Core::errorPage('404');
            }
            break;

        case 'report_comment':
            if (isset($_REQUEST['cm_id'])) {
                $cm_id = (int)$_REQUEST['cm_id'];
                if (!$_SESSION['Users_Reported_Comments'][$cm_id]) {
                    $db = new PHPWS_DB('comments_items');
                    $db->addWhere('id', $cm_id);
                    $db->incrementColumn('reported');
                    $_SESSION['Users_Reported_Comments'][$cm_id] = true;
                }
                Comments::update_reported_comments();
            }
            exit();
            break;

        case 'cm_history':
            $comment_user = new Comment_User($_GET['uid']);
            $title = sprintf(dgettext('comment', 'Comment history for %s'), $comment_user->display_name);
            $content[] = Comments::showHistory($comment_user);
            break;

        case 'change_view':
            Comments::changeView();
            break;

        case 'save_comment':
            if (empty($_POST['cm_subject']) && empty($_POST['cm_entry'])) {
                PHPWS_Core::reroute($thread->_key->url);
            }

            if (PHPWS_Core::isPosted()) {
                PHPWS_Core::reroute($thread->_key->url);
            }

            if (!isset($thread)) {
                $title = dgettext('comments', 'Error');
                $content[] = dgettext('comments', 'Missing thread information.');
                break;
            }

            if (Comments::postComment($thread, $c_item)) {
                $result = $c_item->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $title = dgettext('comments', 'Sorry');
                    $content[] = dgettext('comments', 'A problem occurred when trying to save your comment.');
                    $content[] = dgettext('comments', 'Please try again later.');
                    break;
                } else {
                    if (!$c_item->approved) {
                        $content[] = dgettext('comments', 'We are holding your comment for approval');
                        $content[] = sprintf('<a href="%s">%s</a>', $thread->getSourceUrl(false, $c_item->id),
                                             dgettext('comments', 'Return to the thread...'));
                    } else {
                        PHPWS_Core::reroute($thread->getSourceUrl(false, $c_item->id));
                        exit();
                    }
                }

            } else {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                $title = dgettext('comments', 'Post Comment');
                $content[] = Comment_Forms::form($thread, $c_item);
            }

            break;

        case 'view_comment':
            $thread = new Comment_Thread($c_item->thread_id);
            if (!$thread->id) {
                PHPWS_Core::errorPage('404');
                break;
            }

            if (!$thread->_key->allowView()) {
                Current_User::requireLogin();
            }

            if ($c_item->approved || Current_User::allow('comments')) {
                $title = sprintf(dgettext('comments', 'Comment from: %s'), $thread->_key->getUrl());
                $content[] = Comments::viewComment($c_item, $thread);
            } else {
                PHPWS_Core::errorPage('404');
            }
            break;

        case 'user_posts':
            $userid = Current_User::getId();
            if (!empty($_REQUEST['userid']))
                $userid = (int) $_REQUEST['userid'];
            $user = Comments::getCommentUser($userid);
            $title = sprintf(dgettext('comments', 'Comments Made by %s'), $user->display_name);
            $content[] = $user->list_posts();
            break;

        case 'set_monitor':
            Comment_User::subscribe(Current_User::getId(), $thread->id);
            PHPWS_Core::reroute($thread->_key->url);
            break;

        case 'unset_monitor':
            Comment_User::unsubscribe(Current_User::getId(), $thread->id);
            PHPWS_Core::reroute($thread->_key->url);
            break;
        }

        if (empty($content)) {
            PHPWS_Core::errorPage('404');
        }

        $template['TITLE'] = $title;
        $template['CONTENT'] = implode('<br />', $content);

        Layout::add(PHPWS_Template::process($template, 'comments', 'main.tpl'));
    }

    public function changeView()
    {
        $getValues = PHPWS_Text::getGetValues();

        if (isset($_GET['referer'])) {
            $referer = PHPWS_Text::getGetValues(urldecode($_GET['referer']));
        } else {
            $referer = PHPWS_Text::getGetValues($_SERVER['HTTP_REFERER']);
        }

        $referer['time_period'] = $getValues['time_period'];
        $referer['order'] = $getValues['order'];

        foreach ($referer as $key=>$value) {
            $url[] = $key . '=' . $value;
        }

        $link = 'index.php?' . implode('&', $url);
        PHPWS_Core::reroute($link);
        return;
    }

    public function postComment($thread, Comment_Item $cm_item)
    {
        if (!$thread->id) {
            $cm_item->_error = dgettext('comments', 'Unable to post to this thread.');
            return false;
        }
        if (empty($_POST['cm_subject']) && empty($_POST['cm_entry'])) {
            $cm_item->_error = dgettext('comments', 'You must include a subject or comment.');
            return false;
        }

        $cm_item->setThreadId($thread->id);
        $cm_item->setSubject($_POST['cm_subject']);
        $cm_item->setEntry($_POST['cm_entry']);

        /**
         * If the user doesn't have permissions to edit the key
         * AND the user does not have permissions to admin comments
         * AND the comment is new (no id)
         * AND the thread approval requires approval from everyone
         * OR the user is not logged in and the comment requires
         * anonymous comments to be approved,
         * THEN set the approval to 0
         */
        if ( !$thread->_key->allowEdit()      &&
             !Current_User::allow('comments') &&
             !$cm_item->id                    &&
             ( $thread->approval == 2 ||
               ( $thread->approval == 1 && !Current_User::isLogged() ) )
             ) {
            $cm_item->approved = 0;
        }

        if (isset($_POST['cm_parent'])) {
            $cm_item->setParent($_POST['cm_parent']);
        }

        if ($cm_item->id) {
            if (!empty($_POST['edit_reason'])) {
                $cm_item->setEditReason($_POST['edit_reason']);
            } else {
                $cm_item->edit_reason = NULL;
            }
        }

        if (!Current_User::isLogged() &&
            PHPWS_Settings::get('comments', 'anonymous_naming')) {
            $name = trim(strip_tags($_POST['anon_name']));
            if (empty($name) || strlen($name) < 2) {
                $cm_item->_error = dgettext('comments', 'Your name cannot be shorter than 2 characters.');
                return false;
            }
            if (!$cm_item->setAnonName($name)) {
                $cm_item->_error = dgettext('comments', 'That name is not allowed. Try another.');
                return false;
            }
        }

        if ( Comments::useCaptcha() ) {
            PHPWS_Core::initCoreClass('Captcha.php');
            if (!Captcha::verify()) {
                $cm_item->_error =  dgettext('comments', 'You failed verification. Try again.');
                return false;
            }
        }
        return true;
    }


    /**
     * Determines if captcha should be used
     */
    public function useCaptcha()
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        if (Current_User::allow('comments')) {
            return false;
        }

        $captcha = PHPWS_Settings::get('comments', 'captcha');

        // if captcha is enabled (1 or 2)
        // and everyone has to use it (option 2) or
        // the only anonymous and user is not logged in
        // return true
        if ($captcha && ($captcha == 2 || ($captcha == 1 && !Current_User::isLogged()))) {
            return true;
        }

        return false;
    }


    public function unregister($module)
    {
        $ids = Key::getAllIds($module);
        if (PEAR::isError($ids)) {
            PHPWS_Error::log($ids);
            return FALSE;
        }

        if (empty($ids)) {
            return TRUE;
        }

        $db = new PHPWS_DB('comments_threads');
        $db->addWhere('key_id', $ids, 'in');
        $db->addColumn('id');
        $id_list = $db->select('col');
        if (empty($id_list)) {
            return TRUE;
        } elseif (PEAR::isError($id_list)) {
            PHPWS_Error::log($id_list);
            return FALSE;
        }

        $db2 = new PHPWS_DB('comments_items');
        $db2->addWhere('thread_id', $id_list, 'in');
        $result = $db2->delete();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($id_list);
            return FALSE;
        } else {
            $db->reset();
            $db->addWhere('key_id', $ids, 'in');
            $result = $db->delete();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($id_list);
                return FALSE;
            }
            return TRUE;
        }
    }

    public function viewComment(& $comment, & $thread)
    {
        $tpl = $comment->getTpl($thread);
        $tpl['RESPONSES'] = dgettext('comments', 'Replies to this comment');
        $tpl['CHILDREN'] = $thread->view($comment->id);
        $content = PHPWS_Template::process($tpl, 'comments', COMMENT_VIEW_ONE_TPL);

        return $content;
    }

    /**
     * Shows a box with recent comments listed within
     */
    public function showRecentComments($limit)
    {
        $db = new PHPWS_DB('comments_items');
        $db->setLimit($limit);
        $db->addOrder('create_time desc');
        $db->addColumn('comments_items.id');
        $db->addColumn('comments_items.subject');
        $db->addColumn('comments_items.create_time');
        $db->addColumn('comments_threads.key_id');
        $db->addColumn('users.display_name', 'comments_items.author_id');
        $db->addColumn('phpws_key.url');
        $db->addWhere('comments_items.author_id', 'comments_users.user_id');
        $db->addWhere('comments_items.thread_id', 'comments_threads.id');
        $db->addWhere('comments_threads.key_id', 'phpws_key.id');
        $db->addWhere('phpws_key.active', 1);

        if (!Current_User::isLogged()) {
            $db->addWhere('phpws_key.restricted', 0);
        }
        
        $result = $db->select();

        if (empty($result)) {
            return;
        }

        foreach ($result as $comment) {
            $date = PHPWS_Time::relativeTime($comment['create_time'], '%e %b');
            $from = sprintf(dgettext('comments', '%1$s - by %2$s from %3$s'),
                            $comment['subject'],
                            $comment['display_name'],
                            $date);

            $subtpl['TITLE']  = sprintf('<a href="%s#cm_%s" title="%s">%s</a>',
                                        $comment['url'],
                                        $comment['id'],
                                        $from,
                                        $comment['subject']);

            $subtpl['AUTHOR'] = $comment['display_name'];
            $subtpl['DATE']   = $date;
            $tpl['comment-row'][] = $subtpl;
        }

        $tpl['BOX_TITLE'] = dgettext('comments', 'Recent comments');

        $content = PHPWS_Template::process($tpl, 'comments', 'recent.tpl');
        Layout::add($content, 'comments', 'recent');
    }

    public function multipleApprove($comment_ids)
    {
        $all_approved = false;
        foreach ($comment_ids as $id) {
            $comment = new Comment_Item($id);
            if ($comment->id) {
                $comment->approve();
                $all_approved = true;
            }
        }
        return $all_approved;
    }

    public function multipleRemove($comment_ids)
    {
        $all_removed = false;
        foreach ($comment_ids as $id) {
            $comment = new Comment_Item($id);
            if ($comment->id) {
                $comment->delete(false);
                $all_removed = true;
            }
        }
        return $all_removed;
    }

    /*
     * Sends "New Post" notices to all users monitoring the topic
     */
    public function sendUpdateNotice(&$thread, &$cm_item)
    {
        if(!PHPWS_Settings::get('comments', 'allow_user_monitors'))
            return;

        // look for all users that are monitoring this thread
        $db = & new PHPWS_DB('comments_monitors');
        $db->addColumn('comments_monitors.user_id');
        $db->addColumn('users.id');
        $db->addColumn('users.username');
        $db->addColumn('users.email');
        $db->addWhere('thread_id', $thread->id);
        $db->addWhere('send_notice', 1);
        $db->addWhere('suspended', 0);
        $db->addWhere('users.id', 'comments_monitors.user_id');
        $db->addWhere('users.active', 1);
        $result = $db->select();
        if (PHPWS_Error::logIfError($result) || empty($result))
            return;

        // Send all email notices (not current user)
        //xxxxNOTE: Need to create a core_based Mail_Queue to pop these into for Comments & Newsletter modules
        PHPWS_Core::initCoreClass('Mail.php');
        $mail = new PHPWS_Mail;
        foreach ($result AS $to) {
            if ($to['id'] != Current_User::getId())
                $mail->addSendTo($to['email']);
        }
        $find = array('::username::', '::postername::'
                      , '::thread_title::', '::thread_url::'
                      , '::reply_msg::', '::unsubscribeall_url::');
        $replace = array($to['username']
                         , $cm_item->getAuthorName()
                         , $thread->_key->title
                         , PHPWS_Core::getHomeHttp().$thread->_key->url
                         , $cm_item->getEntry()
                         , PHPWS_Core::getHomeHttp().'index.php?module=users&action=user&tab=comments'
                         );
        $body = str_replace($find, $replace, PHPWS_Settings::get('comments', 'email_text'));
        $mail->setSubject(str_replace($find, $replace, PHPWS_Settings::get('comments', 'email_subject')));
        $mail->setFrom(PHPWS_User::getUserSetting('site_contact'));
        $mail->setMessageBody($body);
        $mail->sendIndividually();
        $mail->send();
    }

    /*
     * Update the 'reported_comments' count cache to make admin screens load faster
     */
    public function update_reported_comments()
    {
        PHPWS_Settings::get('comments', 'reported_comments');
        $db = new PHPWS_DB('comments_items');
        $db->addColumn('id', null, null, true);
        $db->addWhere('reported', 0, '>');
        $count = $db->select('one');
        if (PHPWS_Error::logIfError($count))
            $count = 0;
        PHPWS_Settings::set('comments', 'reported_comments', $count);
        PHPWS_Settings::save('comments');
    }

    /*
     * Update the 'unapproved_comments' count cache to make admin screens load faster
     */
    public function update_unapproved_comments()
    {
        PHPWS_Settings::get('comments', 'unapproved_comments');
        $db = new PHPWS_DB('comments_items');
        $db->addColumn('id', null, null, true);
        $db->addWhere('approved', 0);
        $count = $db->select('one');
        if (PHPWS_Error::logIfError($count))
            $count = 0;
        PHPWS_Settings::set('comments', 'unapproved_comments', $count);
        PHPWS_Settings::save('comments');
    }


    public function showHistory($comment_user)
    {
        Layout::addStyle('comments', 'admin.css');

        javascript('jsquery');
        javascript('modules/comments/admin');
        javascript('modules/comments/quick_view');

        PHPWS_Core::initCoreClass('DBPager.php');
        if (empty($comment_user->user_id)) {
            return dgettext('comments', 'No comments made');
        }
        $pager = new DBPager('comments_items');
        $pager->setModule('comments');
        $pager->setTemplate('history.tpl');
        $pager->addWhere('author_id', $comment_user->user_id);
        $pager->addRowFunction(array('Comments', 'getCommentTpl'));
        $pager->addRowTags('historyTags');
        $pager->setEmptyMessage(dgettext('comments', 'No comments made'));
        $pager->setDefaultOrder('create_time', 'desc');
        $pager->setDefaultLimit(30);
        $pager->setLimitList(array(30,60,90));
        $pager->addToggle(' toggle1"');
        $pager->addToggle(' toggle2"');
        $pager->setSearch('title');

        $pager->db->addColumn('comments_items.*');
        $pager->db->addColumn('comments_threads.total_comments', null, 'total_comments');
        $pager->db->addColumn('phpws_key.module', null, 'topic_module');
        $pager->db->addColumn('phpws_key.item_name', null, 'topic_item_name');
        $pager->db->addColumn('phpws_key.item_id', null, 'topic_item_id');
        $pager->db->addColumn('phpws_key.title', null, 'topic_title');
        $pager->db->addColumn('phpws_key.url', null, 'topic_url');
        $pager->db->addWhere('comments_threads.id', 'comments_items.thread_id');
        $pager->db->addWhere('phpws_key.id', 'comments_threads.key_id');

        $pager->addWhere('approved', 1);

        if(!Current_User::isDeity()) {
            Key::restrictView($pager->db, 'comments', false, 'comments_threads');
        }

        return $pager->get();
    }

    public function getCommentTpl($data) {
        $thread = new Comment_Thread;
        $comment = new Comment_Item;
        PHPWS_Core::plugObject($comment, $data);
        $tpl = $comment->historyTags();

        $tpl['TOPIC_ID'] = $data['thread_id'];
        $tpl['TOPIC_TITLE'] = $data['topic_title'];
        $tpl['TOPIC_LBL'] = sprintf(dgettext('phpwsbb', 'In %s'), $data['topic_item_name']);
        $tpl['TOPIC_LINK'] = '<a href="'.$data['topic_url'].'">'.$data['topic_title'].'</a>';
        $tpl['REPLY_LBL'] = sprintf(dgettext('comments', 'Total comments in %s'), $data['topic_item_name']);
        $tpl['REPLIES'] = $data['total_comments'];
        return $tpl;
    }

    public function getUserRanking($simple=false)
    {
        static $all_ranks = null;
        static $simple_ranks = null;

        if ($simple && !empty($simple_ranks)) {
            return $simple_ranks;
        } elseif (!$simple && !empty($all_ranks)) {
            return $all_ranks;
        }

        $db = new PHPWS_DB('comments_ranks');
        $db->addColumn('users_groups.name', null, 'group_name');
        $db->addJoin('left', 'comments_ranks', 'users_groups', 'group_id', 'id');
        $db->addOrder('users_groups.name');
        $db->setIndexBy('id');
        $default_rank = PHPWS_Settings::get('comments', 'default_rank');

        PHPWS_Core::initModClass('comments', 'Rank.php');
        $db->addColumn('comments_ranks.*');
        $result = $db->getObjects('Comment_Rank', true);
        
        if (PHPWS_Error::logIfError($result)) {
            return null;
        }
        $result[$default_rank]->group_name = dgettext('comments', 'All Members');
        $all_ranks = $result;

        foreach ($all_ranks as $rank) {
            $simple_ranks[$rank->id] = $rank->group_name;
        }

        if ($simple) {
            return $simple_ranks;
        } else {
            return $all_ranks;
        }
    }

    public function getDefaultRank()
    {
        $rank = new Comment_Rank(PHPWS_Settings::get('comments', 'default_rank'));
        return $rank;
    }

    public function loadRank()
    {
        PHPWS_Core::initModClass('comments', 'Rank.php');
        if (isset($_REQUEST['rank_id'])) {
            $rank = new Comment_Rank($_REQUEST['rank_id']);
        } else {
            $rank = new Comment_Rank;
        }
        return $rank;
    }

    public function deleteAllUserComments($author_id=0, $author_ip=null)
    {
        $author_id = (int)$author_id;
        if (!$author_id && empty($author_ip)) {
            return;
        }

        $db = new PHPWS_DB('comments_items');
        if ($author_id) {
            $db->addWhere('author_id', $author_id);
        } else {
            $db->addWhere('author_ip', $author_ip);
        }

        // first get threads so we can update them
        $db->addColumn('thread_id', null, null, false, true);

        $threads = $db->select('col');
        if (PHPWS_Error::logIfError($threads) || empty($threads)) {
            return;
        }

        // now delete all the comments
        $db->resetColumns();
        PHPWS_Error::logIfError($db->delete());

        // go through threads and update their comment count
        foreach ($threads as $thread_id) {
            $thread = new Comment_Thread($thread_id);
            $thread->updateCount();
            $thread->updateLastPoster();
        }

        if ($author_id) {
            $db = new PHPWS_DB('comments_users');
            $db->addWhere('user_id', $author_id);
            $db->setValue('comments_made', 0);
            PHPWS_Error::logIfError($db->update());
        }

    }
}

?>