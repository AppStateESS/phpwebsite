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

        $GLOBALS['Comment_Users'][$user_id] = &$user;
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


    /**
     * Authorization checked in index.php
     */
    function adminAction($command)
    {
        $content = NULL;
        switch ($command) {
        case 'delete_comment':
            $comment = new Comment_Item($_REQUEST['cm_id']);
            $comment->delete();
            PHPWS_Core::goBack();
            return;
            break;
            
        case 'admin_menu':
            if (Current_User::allow('comments', 'settings')) {
                $content = Comments::settingsForm();
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
        }
        Layout::add(PHPWS_ControlPanel::display($content));
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

    function form(&$thread, $c_item)
    {
        $form = new PHPWS_Form;
    
        if (isset($_REQUEST['cm_parent'])) {
            $c_parent = new Comment_Item($_REQUEST['cm_parent']);
            $form->addHidden('cm_parent', $c_parent->getId());
            $form->addTplTag('PARENT_SUBJECT', $c_parent->subject);
            $form->addTplTag('PARENT_ENTRY', $c_parent->getEntry());
        }
    
        if (!empty($c_item->id)) {
            $form->addHidden('cm_id', $c_item->id);
            $form->addText('edit_reason', $c_item->getEditReason());
            $form->setLabel('edit_reason', dgettext('comments', 'Reason for edit'));
            $form->setSize('edit_reason', 50);
        }

        if (!Current_User::isLogged() && PHPWS_Settings::get('comments', 'anonymous_naming')) {
            $form->addText('anon_name', $c_item->getEditReason());
            $form->setLabel('anon_name', dgettext('comments', 'Name'));
            $form->setSize('anon_name', 20, 20);
        }

        $form->addHidden('module', 'comments');
        $form->addHidden('user_action', 'save_comment');
        $form->addHidden('thread_id',    $thread->getId());

        $form->addText('cm_subject');
        $form->setLabel('cm_subject', dgettext('comments', 'Subject'));
        $form->setSize('cm_subject', 50);

        if (isset($c_parent) && empty($c_item->subject)) {
            $form->setValue('cm_subject', dgettext('comments', 'Re:') . $c_parent->subject);
        } else {
            $form->setValue('cm_subject', $c_item->subject);
        }


        if (!$c_item->id && isset($c_parent)) {
            $entry_text = $c_parent->getEntry(FALSE, TRUE) . "\n\n" . $c_item->getEntry(FALSE);
        } else {
            $entry_text = $c_item->getEntry(FALSE);
        }

        $form->addTextArea('cm_entry', $entry_text);
        $form->setLabel('cm_entry', dgettext('comments', 'Comment'));
        $form->setCols('cm_entry', 50);
        $form->setRows('cm_entry', 10);
        $form->addSubmit(dgettext('comments', 'Post Comment'));

        if (Comments::useCaptcha()) {
            PHPWS_Core::initCoreClass('Captcha.php');
            $form->addText('captcha');
            $form->setLabel('captcha', dgettext('comments', 'Please copy the word in the above image.'));
            $form->addTplTag('CAPTCHA_IMAGE', Captcha::get());
        }

        $template = $form->getTemplate();
        if (isset($c_parent)) {
            $template['BACK_LINK'] = $thread->getSourceUrl(TRUE, $c_parent->id);
        } else {
            $template['BACK_LINK'] = $thread->getSourceUrl(TRUE);
        }

        if ($c_item->_error) {
            $template['ERROR'] = & $c_item->_error;
        }


        $content = PHPWS_Template::process($template, 'comments', 'edit.tpl');
        return $content;
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
        $tpl['CHILDREN'] = $thread->view($comment->getId());
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
        $vars['admin_action'] = 'admin_menu';
        $content[] = PHPWS_Text::secureLink(dgettext('comments', 'Go back to settings...'), 'comments', $vars);
        return implode('<br /><br />', $content);
    }

    function settingsForm()
    {
        $settings = PHPWS_Settings::get('comments');

        $form = new PHPWS_Form('comments');
        $form->addHidden('module', 'comments');
        $form->addHidden('admin_action', 'post_settings');

        $form->addCheck('allow_signatures', 1);
        $form->setLabel('allow_signatures', dgettext('comments', 'Allow user signatures'));
        $form->setMatch('allow_signatures', $settings['allow_signatures']);
                        

        $form->addCheck('allow_image_signatures', 1);
        $form->setLabel('allow_image_signatures', dgettext('comments', 'Allow images in signatures'));
        $form->setMatch('allow_image_signatures', $settings['allow_image_signatures']);

        $form->addCheck('allow_avatars', 1);
        $form->setLabel('allow_avatars', dgettext('comments', 'Allow user avatars'));
        $form->setMatch('allow_avatars', $settings['allow_avatars']);

        $form->addCheck('local_avatars', 1);
        $form->setLabel('local_avatars', dgettext('comments', 'Save avatars locally'));
        $form->setMatch('local_avatars', $settings['local_avatars']);

        $form->addCheck('anonymous_naming', 1);
        $form->setLabel('anonymous_naming', dgettext('comments', 'Allow anonymous naming'));
        $form->setMatch('anonymous_naming', $settings['anonymous_naming']);

        $cmt_count[0]  = dgettext('comments', 'Do not show');
        $cmt_count[5]  = sprintf(dgettext('comments', 'Show last %d'), 5);
        $cmt_count[10] = sprintf(dgettext('comments', 'Show last %d'), 10);
        $cmt_count[15] = sprintf(dgettext('comments', 'Show last %d'), 15);

        $form->addSelect('recent_comments', $cmt_count);
        $form->setLabel('recent_comments', dgettext('comments', 'Recent comment'));
        $form->setMatch ('recent_comments', $settings['recent_comments']);

        $order_list = array('old_all'  => dgettext('comments', 'Oldest first'),
                            'new_all'  => dgettext('comments', 'Newest first'));

        $form->addSelect('order', $order_list);
        $form->setMatch('order', PHPWS_Settings::get('comments', 'default_order'));
        $form->setLabel('order', dgettext('comments', 'Default order'));

        $captcha[0] = dgettext('comments', 'Don\'t use');
        $captcha[1] = dgettext('comments', 'Anonymous users only');
        $captcha[2] = dgettext('comments', 'All users');

        if (extension_loaded('gd')) {
            $form->addSelect('captcha', $captcha);
            $form->setMatch('captcha', PHPWS_Settings::get('comments', 'captcha'));
            $form->setLabel('captcha', dgettext('comments', 'CAPTCHA use'));
        }

        $form->addSubmit(dgettext('comments', 'Save'));

        $tpl = $form->getTemplate();

        $tpl['TITLE'] = dgettext('comments', 'Comment settings');
        return PHPWS_Template::process($tpl, 'comments', 'settings_form.tpl');
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