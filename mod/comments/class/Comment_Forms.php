<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Comment_Forms {

    public function form(Comment_Thread $thread, $c_item)
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


        if (!$c_item->id && isset($c_parent) && isset($_REQUEST['type']) && $_REQUEST['type']=='quote') {
            $entry_text = $c_parent->getEntry(FALSE, TRUE) . "\n\n" . $c_item->getEntry(FALSE);
        } else {
            $entry_text = $c_item->getEntry(FALSE);
        }

        $form->addTextArea('cm_entry', $entry_text);
        if (PHPWS_Settings::get('comments', 'use_editor')) {
            $form->useEditor('cm_entry', true, true, 0, 0, 'tinymce');
        }
        $form->setLabel('cm_entry', dgettext('comments', 'Comment'));
        $form->setCols('cm_entry', 50);
        $form->setRows('cm_entry', 10);

        $form->addSubmit(dgettext('comments', 'Post Comment'));

        if (Comments::useCaptcha()) {
            PHPWS_Core::initCoreClass('Captcha.php');
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

    public function settingsForm()
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
        $form->setLabel('anonymous_naming', dgettext('comments', "Get anonymous posters' names"));
        $form->setMatch('anonymous_naming', $settings['anonymous_naming']);

        $default_approval[0] = dgettext('comments', 'All comments preapproved');
        $default_approval[1] = dgettext('comments', 'Anonymous comments require approval');
        $default_approval[2] = dgettext('comments', 'All comments require approval');

        $form->addSelect('default_approval', $default_approval);
        $form->setMatch('default_approval', $settings['default_approval']);
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
        $form->setMatch('order', $settings['default_order']);
        $form->setLabel('order', dgettext('comments', 'Default order'));

        $captcha[0] = dgettext('comments', 'Don\'t use');
        $captcha[1] = dgettext('comments', 'Anonymous users only');
        $captcha[2] = dgettext('comments', 'All users');

        if (extension_loaded('gd')) {
            $form->addSelect('captcha', $captcha);
            $form->setMatch('captcha', $settings['captcha']);
            $form->setLabel('captcha', dgettext('comments', 'CAPTCHA use'));
        }

        // email_subject
        $form->addText('email_subject', $settings['email_subject']);
        $form->setMaxSize('email_subject', '60');
        $form->setWidth('email_subject', '95%');
        $form->setLabel('email_subject', dgettext('comments', 'Subject line of email notifying users of a new comment'));

        // email_text
        $form->addTextArea('email_text', $settings['email_text']);
        $form->setWidth('email_text', '95%');
        $form->setRows('email_text', '5');
        $form->useEditor('email_text', true, true, 0, 0, 'tinymce');
        $form->setLabel('email_text', dgettext('comments', 'Text of email notifying users of a new comment'));

        // monitor_posts
        $form->addCheckBox('monitor_posts');
        $form->setMatch('monitor_posts', $settings['monitor_posts']);
        $form->setLabel('monitor_posts', dgettext('comments', 'Send a copy of each new comment to the Forum Moderator(s)'));

        // allow_user_monitors
        $form->addCheckBox('allow_user_monitors');
        $form->setMatch('allow_user_monitors', $settings['allow_user_monitors']);
        $form->setLabel('allow_user_monitors', dgettext('comments', 'Allow users to monitor threads via email'));

        $form->addSubmit(dgettext('comments', 'Save'));

        $tpl = $form->getTemplate();
        // user ranking system
        $user_ranking = Comments::getUserRanking();

        $groupname = array(0=>dgettext('comments', 'All Members')) + PHPWS_User::getAllGroups();
        $tpl['rank_usergroups'] = array();
        $i = 1;
        // Start constructing the output
        Layout::getModuleJavascript('comments', 'expandCollapse');
        $template = & new PHPWS_Template('comments');
        $status = $template->setFile('settings_form.tpl');

        // Loop through all usergroups in the ranking array
        foreach ($user_ranking as $gkey => $group) {
            // Loop through all ranks in this usergroup
            foreach ($group['user_ranks'] as $rank) {
                // Create form to edit this rank's information
                $template->setCurrentBlock('rank_rows');
                $template->setData(Comment_Forms::editUserRank($i++, $rank));
                $template->parseCurrentBlock();
            }
            $template->setCurrentBlock('rank_usergroups');

            $form1 = new PHPWS_Form('comment-group-settings');
            // allow_local_custom_avatars
            $element = 'user_groups['.$gkey.'][allow_local_custom_avatars]';
            $form1->addCheck($element, 1);
            $form1->setLabel($element, dgettext('comments', "Allow this group's users to upload custom avatars after"));
            $form1->setMatch($element, (int) @$group['allow_local_custom_avatars']);
            $form1->setTag($element, 'ALLOW_LOCAL_CUSTOM_AVATARS');
            // minimum_local_custom_posts
            $element = 'user_groups['.$gkey.'][minimum_local_custom_posts]';
            $form1->addText($element, (int) @$group['minimum_local_custom_posts']);
            $form1->setMaxSize($element, '4');
            $form1->setSize($element, '3');
            $form1->setLabel($element, dgettext('comments', 'posts'));
            $form1->setTag($element, 'MINIMUM_LOCAL_CUSTOM_POSTS');
            // allow_remote_custom_avatars
            $element = 'user_groups['.$gkey.'][allow_remote_custom_avatars]';
            $form1->addCheck($element, 1);
            $form1->setLabel($element, dgettext('comments', "Allow this group's users to use remote custom avatars after"));
            $form1->setMatch($element, (int) @$group['allow_remote_custom_avatars']);
            $form1->setTag($element, 'ALLOW_REMOTE_CUSTOM_AVATARS');
            // minimum_remote_custom_posts
            $element = 'user_groups['.$gkey.'][minimum_remote_custom_posts]';
            $form1->addText($element, (int) @$group['minimum_remote_custom_posts']);
            $form1->setMaxSize($element, '4');
            $form1->setSize($element, '3');
            $form1->setLabel($element, dgettext('comments', 'posts'));
            $form1->setTag($element, 'MINIMUM_REMOTE_CUSTOM_POSTS');

            $group_tpl = $form1->getTemplate();
            $group_tpl['USERGROUP_NAME'] = $groupname[$gkey];
            $group_tpl['GROUP_AVATAR_SETTINGS'] = dgettext('comments', 'Avatar Settings');
            $template->setData($group_tpl);
            $template->parseCurrentBlock();
            unset($form1,$group_tpl);
        }
        // Show form to add a new rank
        $template->setCurrentBlock('add_new_rank');
        $template->setData(Comment_Forms::editUserRank($i++));
        $template->parseCurrentBlock();
        // Rank table text
        $tpl['RANK_TABLE_TEXT']    = dgettext('comments', 'Member Ranks');
        $tpl['RANK_TABLE_HELP']    = dgettext('comments', 'This is the current member ranking system.<br />Don\'t worry about the order - the Rank types will re-order themselves by posting level.<br />To delete a Rank, just leave the name blank.');
        $tpl['RANK_NEW_TITLE']     = dgettext('comments', 'Add a new rank by entering its information here.');
        $tpl['TITLE'] = dgettext('comments', 'Comment settings');

        $template->setData($tpl);
        return $template->get();
    }

    /**
     * Shows a box with an User Rank editing dialog listed within
     */
    public function editUserRank($index, $rank = null)
    {
        $textwidth = '60';
        $form = & new PHPWS_Form('rank_edit');
    	if (empty($rank))
            $rank = array('title'=>'', 'min_posts'=>0, 'usergroup'=>0, 'image'=>'', 'stack'=>1, 'repeat_image'=>0);
        else {
            $form->addTplTag('EDIT_ICON', '[+]');
            $form->addTplTag('EDIT_HELP', dgettext('comments', 'Click here to edit this Rank'));
        }
        // title
        $element = 'user_ranks['.$index.'][title]';
        $form->addText($element, $rank['title']);
        $form->setMaxSize($element, '255');
        $form->setSize($element, $textwidth);
        $form->setLabel($element, dgettext('comments', 'Rank Name'));
        $form->setTag($element, 'RANK_TITLE');
        // min_posts
        $element = 'user_ranks['.$index.'][min_posts]';
        $form->addText($element, $rank['min_posts']);
        $form->setMaxSize($element, '4');
        $form->setSize($element, '3');
        $form->setLabel($element, dgettext('comments', 'Minimum Posts'));
        $form->setTag($element, 'RANK_MIN');
        // usergroup
        $groups = array(0=>dgettext('comments', 'All Members')) + PHPWS_User::getAllGroups();
        $element = 'user_ranks['.$index.'][usergroup]';
        $form->addSelect($element, $groups);
        $form->setMatch($element, $rank['usergroup']);
        $form->setLabel($element, dgettext('article', 'User Group'));
        $form->setTag($element, 'RANK_USERGROUP');
        // image
        $element = 'user_ranks['.$index.'][image]';
        $form->addText($element, $rank['image']);
        $form->setMaxSize($element, '255');
        $form->setSize($element, $textwidth);
        $form->setLabel($element, dgettext('comments', 'Rank Image'));
        $form->setTag($element, 'RANK_IMAGE');
        $form->addTplTag('RANK_IMAGE_HELP', dgettext('comments', 'ex.: images/comments/Member.gif'));
        // stack
        $element = 'user_ranks['.$index.'][stack]';
        $yes_no = array(1,0);
        $yes_no_labels = array(dgettext('comments', 'no'), dgettext('comments', 'yes'));
        $form->addRadio($element, $yes_no);
        $form->setMatch($element, $rank['stack']);
        $form->setLabel($element, $yes_no_labels);
        $form->setTag($element, 'RANK_STACK');
        $form->addTplTag('RANK_STACK_LABEL', dgettext('comments', 'Stack this Rank?'));
        // repeat_image
        $arr = range(0,20);
        $element = 'user_ranks['.$index.'][repeat_image]';
        $form->addSelect($element, $arr);
        $form->setMatch($element, $rank['repeat_image']);
        $form->setLabel($element, dgettext('comments', 'Repeat Image'));
        $form->setTag($element, 'RANK_REPEAT_IMAGE');
        $form->addTplTag('RANK_REPEAT_TIMES', dgettext('comments', 'Times'));
        // extras
        $form->addTplTag('RANK_ID', $index);
        $form->addTplTag('RANK_MIN_TXT_LABEL', dgettext('comments', 'Postlevel'));
        $form->addTplTag('RANK_MIN_TXT', $rank['min_posts']);
        $form->addTplTag('RANK_TITLE_TXT', $rank['title']);
    	if (!empty($rank['image'])) {
            $rank['stack'] = 0;
            $images = $titles = $composites = array();
            Comment_User::getRankImg($rank, $images, $composites, $titles);
            $form->addTplTag('RANK_IMAGE_PIC', $images[0]);
        }
        return $form->getTemplate();
    }

    public function postSettings()
    {
        $settings['default_order'] = $_POST['order'];
        $settings['captcha'] = (int)$_POST['captcha'];
        $settings['allow_signatures'] = (int) !empty($_POST['allow_signatures']);
        $settings['allow_image_signatures'] = (int) !empty($_POST['allow_image_signatures']);
        $settings['allow_avatars'] = (int) !empty($_POST['allow_avatars']);
        $settings['local_avatars'] = (int) !empty($_POST['local_avatars']);
        $settings['anonymous_naming'] = (int) !empty($_POST['anonymous_naming']);

        if (!empty($_POST['email_subject'])) {
            $settings['email_subject'] = PHPWS_Text::parseInput($_POST['email_subject']);
        } else {
            PHPWS_Settings::reset('comments', 'email_subject');
        }

        if (!empty($_POST['email_text'])) {
            $settings['email_text'] = PHPWS_Text::parseInput($_POST['email_text']);
        } else {
            PHPWS_Settings::reset('comments', 'email_text');
        }

        $settings['monitor_posts'] = (int) !empty($_POST['monitor_posts']);
        $settings['allow_user_monitors'] = (int) !empty($_POST['allow_user_monitors']);
        $settings['default_approval'] = (int)$_POST['default_approval'];
        $settings['recent_comments'] = (int)$_POST['recent_comments'];

        // If changes to the user ranking system were posted, save them now.
        if(isset($_POST['user_groups'])) {
            $grouplist = array(0=>dgettext('comments', 'All Members')) + PHPWS_User::getAllGroups();
            foreach($grouplist as $gkey => $group) {
                // Collect all posted user ranks for this group
                $g_arr = $sourcearr = $sortarr = array();
                $i = 0;
                foreach($_POST['user_ranks'] as $rkey => $rankpost) {
                    if (empty($rankpost['title']) || (int) $rankpost['usergroup'] != $gkey)
                        continue;
                    $rank = array('title'=>'', 'min_posts'=>0, 'usergroup'=>0, 'image'=>'', 'stack'=>1, 'repeat_image'=>0);
                    $rank['title'] = PHPWS_Text::parseInput($rankpost['title']);
                    if (!empty($rankpost['min_posts']))
                        $rank['min_posts'] = (int) $rankpost['min_posts'];
                    if (!empty($rankpost['usergroup']))
                        $rank['usergroup'] = (int) $rankpost['usergroup'];
                    if (!empty($rankpost['image']))
                        $rank['image'] = $rankpost['image'];
                    $rank['stack'] = (!empty($rank['stack'])) ? 1 : 0 ;
                    if (!empty($rankpost['repeat_image']))
                        $rank['repeat_image'] = (int) $rankpost['repeat_image'];

                    $sourcearr[$i] = $rank;
                    $sortarr[$i++] = $rank['min_posts'];
                    unset($_POST['user_ranks'][$rkey]);
                }
                $g_arr['user_ranks'] = array();
                if (!empty($sourcearr)) {
                    // Re-sort user ranks in order "min_posts asc"
                    asort($sortarr, SORT_NUMERIC);
                    foreach ($sortarr as $key => $value) {
                        $g_arr['user_ranks'][$value] = $sourcearr[$key];
                        unset($_POST['user_ranks'][$indexarr[$key]]);
                    }
                }
                // Collect all posted settings for this group
                if (isset($_POST['user_groups'][$gkey])) {
                    $g_arr['allow_local_custom_avatars'] = (int) !empty($_POST['user_groups'][$gkey]['allow_local_custom_avatars']);
                    $g_arr['minimum_local_custom_posts'] = (int) $_POST['user_groups'][$gkey]['minimum_local_custom_posts'];
                    $g_arr['allow_remote_custom_avatars'] = (int) !empty($_POST['user_groups'][$gkey]['allow_remote_custom_avatars']);
                    $g_arr['minimum_remote_custom_posts'] = (int) $_POST['user_groups'][$gkey]['minimum_remote_custom_posts'];
                }

                $settings['user_ranking'][$gkey] = $g_arr;
            }
        }

        PHPWS_Settings::set('comments', $settings);
        return PHPWS_Settings::save('comments');
    }

    public function reported()
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

        // If phpwsbb is installed && user is not a SuperModerator...
        if (isset($GLOBALS['Modules']['phpwsbb']) && !Current_User::allow('phpwsbb', 'manage_forums')) {
            //left join to phpwsbb parent topic ON phpwsbb_topics.id = comments_items.thread_id
            $pager->db->addJoin('left', 'comments_items', 'phpwsbb_topics', 'thread_id', 'id');
            PHPWS_Core::initModClass('phpwsbb', 'BB_Data.php');
            PHPWSBB_Data::load_moderators();
            // What forums can user moderate?
            if (!empty($GLOBALS['Moderators_byUser'][Current_User::getId()])) {
                $forums = array_keys($GLOBALS['Moderators_byUser'][Current_User::getId()]);
                $pager->db->addWhere('phpwsbb_topics.fid', $forums, null, null, 'fidgroup');
            }
            $pager->db->addWhere('phpwsbb_topics.fid', null, null, 'or', 'fidgroup');
        }

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

    public function punishForm($comment)
    {
        javascript('modules/comments/admin', array('authkey'=>Current_User::getAuthKey()));

        if ($comment->author_id) {
            $author = Comments::getCommentUser($comment->author_id);
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
                                               $author->user_id, dgettext('comments', 'Ban user from this website'));
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

    public function approvalForm()
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

        // If phpwsbb is installed && user is not a SuperModerator...
        if (isset($GLOBALS['Modules']['phpwsbb']) && !Current_User::allow('phpwsbb', 'manage_forums')) {
            //left join to phpwsbb parent topic ON phpwsbb_topics.id = comments_items.thread_id
            $pager->db->addJoin('left', 'comments_items', 'phpwsbb_topics', 'thread_id', 'id');
            PHPWS_Core::initModClass('phpwsbb', 'BB_Data.php');
            PHPWSBB_Data::load_moderators();
            // What forums can user moderate?
            if (!empty($GLOBALS['Moderators_byUser'][Current_User::getId()])) {
                $forums = array_keys($GLOBALS['Moderators_byUser'][Current_User::getId()]);
                $pager->db->addWhere('phpwsbb_topics.fid', $forums, null, null, 'fidgroup');
            }
            $pager->db->addWhere('phpwsbb_topics.fid', null, null, 'or', 'fidgroup');
        }

        return $pager->get();
    }
}

?>