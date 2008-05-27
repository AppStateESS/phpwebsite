<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Comment_Forms {

    function form($thread, $c_item)
    {
        $form = new PHPWS_Form;
    
        if (isset($_REQUEST['cm_parent'])) {
            $c_parent = new Comment_Item($_REQUEST['cm_parent']);
            $form->addHidden('cm_parent', $c_parent->id);
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
        $form->addHidden('uop', 'save_comment');
        $form->addHidden('thread_id',    $thread->id);

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

    function settingsForm()
    {
        $settings = PHPWS_Settings::get('comments');

        $form = new PHPWS_Form('comments');
        $form->useRowRepeat();
        $form->addHidden('module', 'comments');
        $form->addHidden('aop', 'post_settings');

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
        
        $default_approval[0] = dgettext('comments', 'All comments preapproved');
        $default_approval[1] = dgettext('comments', 'Anonymous comments require approval');
        $default_approval[2] = dgettext('comments', 'All comments require approval');

        $form->addSelect('default_approval', $default_approval);
        $form->setMatch('default_approval', PHPWS_Settings::get('comments', 'default_approval'));
        $form->setLabel('default_approval', dgettext('comments', 'Default approval'));

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

    function reported()
    {
        javascript('jsquery');
        javascript('modules/comments/admin');
        javascript('modules/comments/quick_view');
        Layout::addStyle('comments', 'admin.css');
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('comments', 'Comment_Item.php');
        $pager = new DBPager('comments_items', 'Comment_Item');
        $pager->setModule('comments');
        $pager->setTemplate('reported.tpl');
        $pager->addWhere('reported', 0, '>');
        $pager->addRowTags('reportTags');
        $pager->setEmptyMessage(dgettext('comments', 'No comments reported'));

        $form = new PHPWS_Form('reported');
        $form->addHidden('module', 'comments');
        $form->addSelect('aop', array(''=>'',
                                      'clear_report'=>dgettext('comments', 'Clear checked'),
                                      'delete_comment' => dgettext('comments', 'Delete checked')));
        $form->addButton('go', dgettext('comments', 'Go'));
        $form->setExtra('go', 'onclick="ignore()"');

        $tags = $form->getTemplate();

        $tags['SUBJECT_LABEL']  = dgettext('comments', 'Subject');
        $tags['ENTRY_LABEL']    = dgettext('comments', 'Entry');
        $tags['REPORTED_LABEL'] = sprintf('<abbr title="%s">%s</abbr>',
                                          dgettext('comments', 'Times reported'),
                                          dgettext('comments', 'T.R.'));
        $tags['CHECK_ALL']      = javascript('check_all', array('checkbox_name'=>'cm_id', 'type'=>'checkbox'));

        $pager->addPageTags($tags);
        return $pager->get();
    }

    function punishForm($comment)
    {
        javascript('modules/comments/admin', array('authkey'=>Current_User::getAuthKey()));

        if ($comment->author_id) {
            $author = new Comment_User($comment->author_id);
            $user = new PHPWS_User($comment->author_id);

            if ($user->id && $user->allow('comments')) {
                $links[] = dgettext('comments', 'A site admin wrote this comment and may not be punished.');
                $links[] = dgettext('comments', 'Remove their admin privileges and return.');
            } else {
                if ($author->user_id) {
                    if ($author->locked) {
                        $links[] = sprintf('<a href="#" onclick="punish_user(%s, this, \'unlock_user\'); return false;">%s</a>',
                                           $author->user_id, dgettext('comments', 'Unlock user'));

                    } else {
                        $links[] = sprintf('<a href="#" onclick="punish_user(%s, this, \'lock_user\'); return false;">%s</a>',
                                           $author->user_id, dgettext('comments', 'Lock user'));
                    }

                    // User may only be banned if admin has user:ban_users permissions and the
                    // commentor does not have user permissions.
                    if (Current_User::allow('users', 'ban_users') && !$user->allow('users')) {
                        if ($user->active) {
                            $links[] = sprintf('<a href="#" onclick="punish_user(%s, this, \'ban_user\'); return false;">%s</a>',
                                               $author->user_id, dgettext('comments', 'Ban user'));
                        } else {
                            $links[] = sprintf('<a href="#" onclick="punish_user(%s, this, \'unban_user\'); return false;">%s</a>',
                                               $author->user_id, dgettext('comments', 'Remove ban'));
                        }
                    }
                }


            }
        }
        
        if (Current_User::allow('access') && $comment->author_ip != '127.0.0.1') {
            if (!isset($user) || !$user->allow('access')) {
                PHPWS_Core::initModClass('access', 'Access.php');
                if (!Access::isDenied($comment->author_ip)) {
                    $links[] = sprintf('<a href="#" onclick="punish_user(\'%s\', this, \'deny_ip\'); return false;">%s</a>',
                                       $comment->author_ip, dgettext('comments', 'Deny IP address'));
                    
                } else {
                    $links[] = sprintf('<a href="#" onclick="punish_user(\'%s\', this, \'remove_deny_ip\'); return false;">%s</a>',
                                       $comment->author_ip, dgettext('comments', 'Remove IP denial'));
                    
                }
            }
        } 

        if (isset($links)) {
            $tpl['LINKS'] = implode('<br />', $links);
        } else {
            $tpl['LINKS'] = dgettext('comments', 'Either your permissions do not allow you to punish users or this user posted from an unblockable IP address.');
        }


        $tpl['CLOSE'] = javascript('close_window');
        return PHPWS_Template::process($tpl, 'comments', 'punish_pop.tpl');
    }

    function approvalForm()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        Layout::addStyle('comments', 'admin.css');
        javascript('jquery');
        javascript('modules/comments/quick_view');
        $form = new PHPWS_Form('approval');
        $form->addHidden('module', 'comments');
        $form->addSelect('aop', array('approval'=>'', 'approve_all'=>dgettext('comments', 'Approve checked'),
                                          'remove_all'=>dgettext('comments', 'Remove checked')));
        $form->addSubmit(dgettext('comments', 'Go'));

        $tpl = $form->getTemplate();
        $tpl['CHECK_ALL'] = javascript('check_all', array('checkbox_name'=>'cm_id', 'type'=>'checkbox'));
        

        $pager = new DBPager('comments_items', 'Comment_Item');
        $pager->setModule('comments');
        $pager->setTemplate('approval.tpl');
        $pager->addWhere('approved', 0);
        $pager->joinResult('author_id', 'users', 'id', 'username', 'author');
        $pager->addPageTags($tpl);
        $pager->addRowTags('approvalTags');
        $pager->addSortHeader('subject', dgettext('comments', 'Subject/Comment'));
        $pager->addSortHeader('author', dgettext('comments', 'Author'));
        $pager->addSortHeader('create_time', dgettext('comments', 'Created on'));
        $pager->convertDate('create_time');
        return $pager->get();
    }
}

?>