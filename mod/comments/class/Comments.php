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

    function &getThread($key=NULL)
    {
        if (empty($key)) {
            $key = Key::getCurrent();
        }

        if (!Key::isKey($key)) {
            if (is_numeric($key)) {
                $key = & new Key((int)$key);
            } else {
                return NULL;
            }
        }

        if ( empty($key) || $key->isDummy() || PEAR::isError($key->_error) ) {
            return NULL;
        }

        $thread = & new Comment_Thread;

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

        $user = & new Comment_User($user_id);
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
            $comment = & new Comment_Item($_REQUEST['cm_id']);
            $comment->delete();
            PHPWS_Core::goBack();
            return;
            break;
            
        case 'admin_menu':
            $content = Comments::settingsForm();
            break;

        case 'post_settings':
            $content = Comments::postSettings();
            break;

        case 'disable_anon_posting':
            $db = & new PHPWS_DB('comments_threads');
            $db->addWhere('id', (int)$_REQUEST['thread_id']);
            $db->addValue('allow_anon', 0);
            $result = $db->update();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            }
            PHPWS_Core::goBack();
            break;

        case 'enable_anon_posting':
            $db = & new PHPWS_DB('comments_threads');
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
            $thread = & new Comment_Thread($_REQUEST['thread_id']);
        } else {
            $thread = & new Comment_Thread;
        }
    
        switch ($command) {
        case 'post_comment':
            if ($thread->canComment()) {
                $title = _('Post Comment');
                $content[] = Comments::form($thread);
            } else {
                PHPWS_Core::errorPage('404');
            }
            break;

        case 'change_view':
            Comments::changeView();
            break;

        case 'save_comment':
            if (empty($_POST['cm_subject']) || empty($_POST['cm_entry'])) {
                $title = _('Sorry');
                $content[] = _('Your comment must contain a subject and comment.');
                $content[] = PHPWS_Text::backLink();
                break;
            }

            if (PHPWS_Core::isPosted()) {
                PHPWS_Core::reroute($thread->_key->url);
                exit();
            }

            if (!isset($thread)) {
                $title = _('Error');
                $content[] = _('Missing thread information.');
                break;
            }

            $result = Comments::saveComment($thread);

            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $title = _('Sorry');
                $content[] = _('A problem occurred when trying to save your comment.');
                $content[] = _('Please try again later.');
            } else {
                PHPWS_Core::reroute($thread->_key->url);
            }
            break;

        case 'view_comment':
            $comment = & new Comment_Item($_REQUEST['cm_id']);
            $thread = & new Comment_Thread($comment->thread_id);
            $key = & new Key($thread->key_id);
            $title = sprintf(_('Comment from: %s'), $key->getUrl());
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
        $referer = PHPWS_Text::getGetValues($_SERVER['HTTP_REFERER']);

        $referer['time_period'] = $getValues['time_period'];
        $referer['order'] = $getValues['order'];

        foreach ($referer as $key=>$value) {
            $url[] = $key . '=' . $value;
        }
        
        $link = 'index.php?' . implode('&', $url);
        PHPWS_Core::reroute($link);
        
        return;
    }
  
    function saveComment(&$thread)
    {
        if (isset($_POST['cm_id'])) {
            $cm_item = & new Comment_Item($_POST['cm_id']);
        } else {
            $cm_item = & new Comment_Item;
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

        return $cm_item->save();
    }

    function form(&$thread)
    {
        if (isset($_REQUEST['cm_id'])) {
            $c_item = & new Comment_Item($_REQUEST['cm_id']);
        } else {
            $c_item = & new Comment_Item;
        }

        $form = & new PHPWS_Form;
    
        if (isset($_REQUEST['cm_parent'])) {
            $c_parent = & new Comment_Item($_REQUEST['cm_parent']);
            $form->addHidden('cm_parent', $c_parent->getId());
            $form->addTplTag('PARENT_SUBJECT', $c_parent->getSubject());
            $form->addTplTag('PARENT_ENTRY', $c_parent->getEntry());
        }
    
        if (!empty($c_item->id)) {
            $form->addHidden('cm_id', $c_item->id);
            $form->addText('edit_reason', $c_item->getEditReason());
            $form->setLabel('edit_reason', _('Reason for edit'));
            $form->setSize('edit_reason', 50);
        }

        $form->addHidden('module', 'comments');
        $form->addHidden('user_action', 'save_comment');
        $form->addHidden('thread_id',    $thread->getId());

        $form->addText('cm_subject');
        $form->setLabel('cm_subject', _('Subject'));
        $form->setSize('cm_subject', 50);

        if (isset($c_parent) && empty($c_item->subject)) {
            $form->setValue('cm_subject', _('Re:') . $c_parent->getSubject());
        } else {
            $form->setValue('cm_subject', $c_item->getSubject());
        }


        if (!$c_item->id && isset($c_parent)) {
            $entry_text = $c_parent->getEntry(FALSE, TRUE) . "\n\n" . $c_item->getEntry(FALSE);
        } else {
            $entry_text = $c_item->getEntry(FALSE);
        }

        $form->addTextArea('cm_entry', $entry_text);
        $form->setLabel('cm_entry', _('Comment'));
        $form->setCols('cm_entry', 50);
        $form->setRows('cm_entry', 10);
        $form->addSubmit(_('Post Comment'));
        $template = $form->getTemplate();
        $template['BACK_LINK'] = $thread->getSourceUrl(TRUE);

        $content = PHPWS_Template::process($template, 'comments', 'edit.tpl');
        return $content;
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

        $db = & new PHPWS_DB('comments_threads');
        $db->addWhere('key_id', $ids, 'in');
        $db->addColumn('id');
        $id_list = $db->select('col');
        if (empty($id_list)) {
            return TRUE;
        } elseif (PEAR::isError($id_list)) {
            PHPWS_Error::log($id_list);
            return FALSE;
        }

        $db2 = & new PHPWS_DB('comments_items');
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
        $thread = & new Comment_Thread($comment->getThreadId());
        $tpl = $comment->getTpl($thread->allow_anon);
        $tpl['CHILDREN'] = $thread->view($comment->getId());
        $content = PHPWS_Template::process($tpl, 'comments', COMMENT_VIEW_ONE_TPL);
        return $content;
    }

    function postSettings()
    {
        $settings['default_order'] = $_POST['order'];

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

        PHPWS_Settings::set('comments', $settings);
        PHPWS_Settings::save('comments');

        $content[] = _('Settings saved.');
        $vars['admin_action'] = 'admin_menu';
        $content[] = PHPWS_Text::secureLink(_('Go back to settings...'), 'comments', $vars);
        return implode('<br /><br />', $content);
    }

    function settingsForm()
    {
        $settings = PHPWS_Settings::get('comments');

        $form = & new PHPWS_Form('comments');
        $form->addHidden('module', 'comments');
        $form->addHidden('admin_action', 'post_settings');

        $form->addCheck('allow_signatures', 1);
        $form->setLabel('allow_signatures', _('Allow user signatures'));
        $form->setMatch('allow_signatures', $settings['allow_signatures']);
                        

        $form->addCheck('allow_image_signatures', 1);
        $form->setLabel('allow_image_signatures', _('Allow images in signatures'));
        $form->setMatch('allow_image_signatures', $settings['allow_image_signatures']);

        $form->addCheck('allow_avatars', 1);
        $form->setLabel('allow_avatars', _('Allow user avatars'));
        $form->setMatch('allow_avatars', $settings['allow_avatars']);

        $form->addCheck('local_avatars', 1);
        $form->setLabel('local_avatars', _('Save avatars locally'));
        $form->setMatch('local_avatars', $settings['local_avatars']);

        $order_list = array('old_all'  => _('Oldest first'),
                            'new_all'  => _('Newest first'));

        $form->addSelect('order', $order_list);
        $form->setMatch('order', PHPWS_Settings::get('comments', 'default_order'));
        $form->setLabel('order', _('Default order'));

        $form->addSubmit(_('Save'));

        $tpl = $form->getTemplate();

        $tpl['TITLE'] = _('Comment settings');
        
        return PHPWS_Template::process($tpl, 'comments', 'settings_form.tpl');
    }

}

?>