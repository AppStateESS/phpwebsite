<?php

/**
 * Developer class for accessing comments
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

// This will be set by config and cookie later
define('CURRENT_VIEW_MODE', 3);

PHPWS_Core::initModClass('comments', 'Comment_Thread.php');
PHPWS_Core::initModClass('comments', 'Comment_User.php');

class Comments {

    function getThread($key=NULL)
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

    function &getCommentUser($user_id)
    {
        if (isset($GLOBALS['Comment_Users'][$user_id])) {
            return $GLOBALS['Comment_Users'][$user_id];
        }

        $user = new Comment_User($user_id);
        if ($user->isNew()) {
            $result = $user->saveUser();
        }

        $GLOBALS['Comment_Users'][$user_id] = & $user;
        return $GLOBALS['Comment_Users'][$user_id];
    }

    function updateCommentUser($user_id)
    {
        if (empty($user_id)) {
            return;
        }

        $user = Comments::getCommentUser($user_id);

        if (!empty($user->user_id)) {
            $user->bumpCommentsMade();
        }
    }


    function panel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        $tabs['settings'] = array('title'=>dgettext('comments', 'Settings'), 'link'=>'index.php?module=comments');
        $tabs['report']   = array('title'=>dgettext('comments', 'Reported'), 'link'=>'index.php?module=comments');

        $panel = new PHPWS_Panel('comments');
        $panel->quickSetTabs($tabs);
        $panel->enableSecure();
        return $panel;
    }

    /**
     * Authorization checked in index.php
     */
    function adminAction($command)
    {
        $panel = Comments::panel();
        $content = NULL;
        switch ($command) {
        case 'delete_comment':
            $comment = new Comment_Item($_REQUEST['cm_id']);
            $comment->delete();
            PHPWS_Core::goBack();
            return;
            break;
            
        case 'report':
            PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
            $content = Comment_Forms::reported();
            break;

        case 'settings':
            if (Current_User::allow('comments', 'settings')) {
                PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
                $content = Comment_Forms::settingsForm();
            } else {
                $content = dgettext('comments', 'Sorry, but you do not have rights to alter settings.');
            }
            break;

        case 'post_settings':
            $content = Comments::postSettings();
            break;

        case 'disable_anon_posting':
            $db = new PHPWS_DB('comments_threads');
            $db->addWhere('id', (int)$_REQUEST['thread_id']);
            $db->addValue('allow_anon', 0);
            $result = $db->update();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            PHPWS_Core::goBack();
            break;

        case 'enable_anon_posting':
            $db = new PHPWS_DB('comments_threads');
            $db->addWhere('id', (int)$_REQUEST['thread_id']);
            $db->addValue('allow_anon', 1);
            $result = $db->update();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
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

                $cuser = new Comment_User($_GET['id']);

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

        }
        $panel->setContent($content);
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    function userAction($command)
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
                $title = dgettext('comments', 'Post Comment');
                $content[] = Comments::form($thread, $c_item);
            } else {
                PHPWS_Core::errorPage('404');
            }
            break;

        case 'report_comment':
            if (isset($_GET['cm_id'])) {
                $cm_id = (int)$_GET['cm_id'];
                if (!$_SESSION['Users_Reported_Comments'][$cm_id]) {
                    $db = new PHPWS_DB('comments_items');
                    $db->addWhere('id', $cm_id);
                    $db->incrementColumn('reported');
                    $_SESSION['Users_Reported_Comments'][$cm_id] = true;
                }
            }
            exit();
            break;

        case 'change_view':
            Comments::changeView();
            break;

        case 'save_comment':
            if (empty($_REQUEST['cm_subject']) && empty($_REQUEST['cm_entry'])) {
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
                    PHPWS_Core::reroute($thread->getSourceUrl(false, $c_item->id));
                    exit();
                }

            } else {
                $title = dgettext('comments', 'Post Comment');
                $content[] = Comments::form($thread, $c_item);
            }

            break;

        case 'view_comment':
            $comment = new Comment_Item($_REQUEST['cm_id']);
            $thread = new Comment_Thread($comment->thread_id);
            $key = new Key($thread->key_id);

            if (!$key->allowView()) {
                Current_User::requireLogin();
            }
            $title = sprintf(dgettext('comments', 'Comment from: %s'), $key->getUrl());
            $content[] = Comments::viewComment($comment);
            break;
        }


        $template['TITLE'] = $title;
        $template['CONTENT'] = implode('<br />', $content);

        Layout::add(PHPWS_Template::process($template, 'comments', 'main.tpl'));
    }

    function changeView()
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
  
    function postComment(&$thread, &$cm_item)
    {
        if (empty($_POST['cm_subject']) && empty($_POST['cm_entry'])) {
            $cm_item->_error = dgettext('comments', 'You must include a subject or comment.');
            return false;
        }

        $cm_item->setThreadId($thread->id);
        $cm_item->setSubject($_POST['cm_subject']);
        $cm_item->setEntry($_POST['cm_entry']);

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
            if (!$cm_item->setAnonName($_POST['anon_name'])) {
                $cm_item->_error = dgettext('comments', 'That name is not allowed. Try another.');
                return false;
            }
        }


        if ( Comments::useCaptcha() ) {
            PHPWS_Core::initCoreClass('Captcha.php');
            if (!Captcha::verify($_POST['captcha'])) {
                $cm_item->_error =  dgettext('comments', 'You failed verification. Try again.');
                return false;
            }
        }
        return true;
    }


    /**
     * Determines if captcha should be used
     */
    function useCaptcha()
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


    function unregister($module)
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

    function viewComment($comment)
    {
        
        $thread = new Comment_Thread($comment->getThreadId());
        $tpl = $comment->getTpl($thread->allow_anon);
        $tpl['CHILDREN'] = $thread->view($comment->id);
        $content = PHPWS_Template::process($tpl, 'comments', COMMENT_VIEW_ONE_TPL);
        
        return $content;
    }

    function postSettings()
    {
        $settings['default_order'] = $_POST['order'];
        $settings['captcha'] = (int)$_POST['captcha'];

        if (@$_POST['allow_signatures']) {
            $settings['allow_signatures'] = 1;
        } else {
            $settings['allow_signatures'] = 0;
        }

        if (@$_POST['allow_image_signatures']) {
            $settings['allow_image_signatures'] = 1;
        } else {
            $settings['allow_image_signatures'] = 0;
        }
 
        if (@$_POST['allow_avatars']) {
            $settings['allow_avatars'] = 1;
        } else {
            $settings['allow_avatars'] = 0;
        }

        if (@$_POST['local_avatars']) {
            $settings['local_avatars'] = 1;
        } else {
            $settings['local_avatars'] = 0;
        }

        if (@$_POST['anonymous_naming']) {
            $settings['anonymous_naming'] = 1;
        } else {
            $settings['anonymous_naming'] = 0;
        }

        $settings['recent_comments'] = (int)$_POST['recent_comments'];

        PHPWS_Settings::set('comments', $settings);
        PHPWS_Settings::save('comments');

        $content[] = dgettext('comments', 'Settings saved.');
        $vars['aop'] = 'admin_menu';
        $content[] = PHPWS_Text::secureLink(dgettext('comments', 'Go back to settings...'), 'comments', $vars);
        return implode('<br /><br />', $content);
    }
    
    function form(&$thread, $c_item)
    {
        PHPWS_Core::initModClass('comments', 'Comment_Forms.php');
        return Comment_Forms::form($thread, $c_item);
    }


    /**
     * Shows a box with recent comments listed within
     */
    function showRecentComments($limit)
    {
        $db = new PHPWS_DB('comments_items');
        $db->setLimit($limit);
        $db->addOrder('create_time desc');
        $db->addColumn('comments_items.id');
        $db->addColumn('comments_users.display_name');
        $db->addColumn('comments_items.subject');
        $db->addColumn('comments_items.create_time');
        $db->addColumn('comments_threads.key_id');
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
}

?>