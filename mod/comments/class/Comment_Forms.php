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
        $form->setLabel('recent_comments', dgettext('comments', 'Recent comments'));
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


        //avatar selection
        $folders = Cabinet::listFolders(IMAGE_FOLDER, true);
        if (empty($folders)) {
            $folders[0] = dgettext('comments', 'No image folders found');
        } else {
            $folders = array_reverse($folders, true);
            $folders[0] = dgettext('comments', '-- Choose a folder --');
            $folders = array_reverse($folders, true);
        }
        $form->addSelect('avatar_folder_id', $folders);
        $form->setLabel('avatar_folder_id', dgettext('comments', 'Enable avatar selection'));
        $form->setMatch('avatar_folder_id', PHPWS_Settings::get('comments', 'avatar_folder_id'));

        $form->addSubmit(dgettext('comments', 'Save'));

        $tpl = $form->getTemplate();
        $tpl['TITLE']        = dgettext('comments', 'Comment settings');
        $tpl['USER_PROFILE'] = dgettext('comments', 'User profile');
        $tpl['DISPLAY']      = dgettext('comments', 'Display');
        $tpl['MODERATE']     = dgettext('comments', 'Moderation');
        $tpl['MONITOR']      = dgettext('comments', 'Thread Monitor');
        
        return PHPWS_Template::process($tpl, 'comments', 'settings_form.tpl');
    }


    public function ranksForm()
    {
        // user ranking system
        $user_ranking = Comments::getUserRanking();
        $default_rank = Comments::getDefaultRank();
        $all_groups   = PHPWS_User::getAllGroups();

        // Start constructing the output
        javascript('jquery');
        javascript('modules/comments/expandCollapse');

        $template = new PHPWS_Template('comments');
        $status = $template->setFile('ranks.tpl');

        // Loop through all usergroups in the ranking array
        foreach ($user_ranking as $id => $rank) {
            unset($all_groups[$rank->group_id]);
            $form = new PHPWS_Form('form_' . $id);
            $form->addHidden('module', 'comments');
            $form->addHidden('aop', 'post_rank');
            $form->addHidden('rank_id', $id);

            // allow_local_avatars
            $form->addCheck('allow_local_avatars', 1);
            $form->setMatch('allow_local_avatars', $rank->allow_local_avatars);
            $form->setLabel('allow_local_avatars', dgettext('comments', 'Allow this groups\'s users to upload custom avatars after'));

            // minimum_local_posts
            $form->addText('minimum_local_posts', (int) $rank->minimum_local_posts);
            $form->setMaxSize('minimum_local_posts', '4');
            $form->setSize('minimum_local_posts', '3');
            $form->setLabel('minimum_local_posts', dgettext('comments', 'posts'));

            // allow_remote_avatars
            $form->addCheck('allow_remote_avatars', 1);
            $form->setLabel('allow_remote_avatars', dgettext('comments', 'Allow this group\'s users to use remote custom avatars after'));
            $form->setMatch('allow_remote_avatars', (int) $rank->allow_remote_avatars);

            // minimum_remote_posts
            $form->addText('minimum_remote_posts', (int) $rank->minimum_remote_posts);
            $form->setMaxSize('minimum_remote_posts', '4');
            $form->setSize('minimum_remote_posts', '3');
            $form->setLabel('minimum_remote_posts', dgettext('comments', 'posts'));

            $form->addSubmit('save_rank', dgettext('comments', 'Save member'));

            $rank_tpl = $form->getTemplate();

            $rank_tpl['GROUP_NAME'] = $rank->group_name;
            $rank_tpl['GROUP_AVATAR_SETTINGS'] = dgettext('comments', 'Avatar Settings');
            if ($rank->group_id) {
                $rank_tpl['DELETE'] = javascript('confirm', array('question'=>dgettext('comments', 'Are you sure you want to remove this rank?'),
                                                                  'address'=>PHPWS_Text::linkAddress('comments', array('aop'=>'delete_rank',
                                                                                                                       'rank_id'=>$id), true),
                                                                  'link'=>dgettext('comments', 'Delete rank'),
                                                                  'title'=>dgettext('comments', 'Delete rank'),
                                                                  'type'=>'button'));
            }
                                                              
            if (!empty($rank->user_ranks)) {
                $rows = array();
                foreach  ($rank->user_ranks as $rank) {
                    $rows[] = Comment_Forms::editUserRank($rank, $rank->id);
                }
                $rank_tpl['USER_RANKS'] = implode('', $rows);
            }

            $tpl['rank_rows'][] = $rank_tpl;
            unset($rank_tpl, $form);
        }

        $new_user_rank = new Comment_User_Rank;
        $tpl['NEW_USER_RANK']   = Comment_Forms::editUserRank($new_user_rank);

        if (!empty($all_groups)) {
            $quick = new PHPWS_Form('add_group');
            $quick->addHidden('module', 'comments');
            $quick->addHidden('aop', 'create_rank');
            $quick->addSelect('group_id', $all_groups);
            $quick->addSubmit('submit', dgettext('comments', 'Create member'));
            $quick_rank = $quick->getTemplate();
            $tpl['NEW_RANK'] = implode('', $quick_rank);
        }

        $tpl['RANK_TABLE_TEXT'] = dgettext('comments', 'Member Ranks');
        $tpl['RANK_TABLE_HELP'] = dgettext('comments', 'This is the current member ranking system.<br />Don\'t worry about the order - the Rank types will re-order themselves by posting level.');
        $tpl['RANK_NEW_TITLE']  = dgettext('comments', 'Create new member rank');
        return PHPWS_Template::process($tpl, 'comments', 'ranks.tpl');
    }

    public function editUserRank($user_rank)
    {
        $default_rank = Comments::getDefaultRank();
        $all_ranks = Comments::getUserRanking(true);

        $textwidth = 60;

        $form = new PHPWS_Form('user-rank-' . $user_rank->id);
        $form->addHidden('module', 'comments');
        $form->addHidden('aop', 'post_user_rank');

        if ($user_rank->id) {
            $form->addHidden('user_rank_id', $user_rank->id);
            $js['question'] = dgettext('comments', 'Are you sure you want to delete this user rank?');
            $js['address']  = PHPWS_Text::linkAddress('comments', array('aop'=>'drop_user_rank', 'user_rank_id'=>$user_rank->id), true);
            $js['link']     = dgettext('comments', 'Delete user rank');
            $js['title']    = dgettext('comments', 'Delete user rank');
            $form->addTplTag('DELETE', javascript('confirm', $js));
        }

        $form->addSubmit('submit', dgettext('comments', 'Save rank'));

        $form->addText('title', $user_rank->title);
        $form->setMaxSize('title', '255');
        $form->setSize('title', $textwidth);
        $form->setLabel('title', dgettext('comments', 'Rank Name'));

        // min_posts
        $form->addText('min_posts', $user_rank->min_posts);
        $form->setMaxSize('min_posts', '4');
        $form->setSize('min_posts', '3');
        $form->setLabel('min_posts', dgettext('comments', 'Minimum Posts'));

        // rank id

        $form->addSelect('rank_id', $all_ranks);
        $form->setMatch('rank_id', $user_rank->rank_id);
        $form->setLabel('rank_id', dgettext('article', 'User Group'));

        // image
        $form->addText('image', $user_rank->image);
        $form->setMaxSize('image', '255');
        $form->setSize('image', $textwidth);
        $form->setLabel('image', dgettext('comments', 'Rank Image'));
        $form->addTplTag('IMAGE_HELP', dgettext('comments', 'ex.: images/comments/Member.gif'));

        // stack
        $yes_no = array(1,0);
        $yes_no_labels = array(dgettext('comments', 'No'), dgettext('comments', 'Yes'));
        $form->addRadio('stack', $yes_no);
        $form->setMatch('stack', $user_rank->stack);
        $form->setLabel('stack', $yes_no_labels);
        $form->addTplTag('STACK_LABEL', dgettext('comments', 'Stack this Rank?'));

        // repeat_image
        $arr = range(0,20);
        $form->addSelect('repeat_image', $arr);
        $form->setMatch('repeat_image', $user_rank->repeat_image);
        $form->setLabel('repeat_image', dgettext('comments', 'Repeat Image'));
        $form->addTplTag('RANK_REPEAT_IMAGE', dgettext('comments', 'Image'));

        $ftpl = $form->getTemplate();
        if (!empty($user_rank->image)) {
            $ftpl['RANK_IMAGE_PIC'] = $user_rank->getImage();
        }

        if ($user_rank->id) {
            $ftpl['EDIT_ICON'] = sprintf('<a style="cursor : pointer" class="expander" id="t-%s">[+]</a>', $user_rank->id);
            $ftpl['RANK_MIN_TXT_LABEL'] = dgettext('comments', 'Post level');
            $ftpl['RANK_MIN_TXT']       = $user_rank->min_posts;
            $ftpl['RANK_TITLE_LABEL']   = dgettext('comments', 'Rank title');
            $ftpl['RANK_TITLE_TXT']     = $user_rank->title;
            $ftpl['HIDE']               = 'display : none;';
        }

        $ftpl['TAG_RANK_ID'] = $user_rank->id;

        return PHPWS_Template::process($ftpl, 'comments', 'user_rank.tpl');
    }


    public function postSettings()
    {
        $settings['default_order']          = $_POST['order'];
        $settings['captcha']                = (int)$_POST['captcha'];
        $settings['allow_signatures']       = (int) !empty($_POST['allow_signatures']);
        $settings['allow_image_signatures'] = (int) !empty($_POST['allow_image_signatures']);
        $settings['allow_avatars']          = (int) !empty($_POST['allow_avatars']);
        $settings['local_avatars']          = (int) !empty($_POST['local_avatars']);
        $settings['anonymous_naming']       = (int) !empty($_POST['anonymous_naming']);
        $settings['avatar_folder_id']       = (int) $_POST['avatar_folder_id'];

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
        
        PHPWS_Settings::set('comments', $settings);
        return PHPWS_Settings::save('comments');
    }

    public function postUserRank()
    {
        $default_rank_id = PHPWS_Settings::get('comments', 'default_rank');
        PHPWS_Core::initModClass('comments', 'User_Rank.php');

        if (isset($_POST['user_rank_id'])) {
            $user_rank = new Comment_User_Rank($_POST['user_rank_id']);            
        } else {
            $user_rank = new Comment_User_Rank;
        }

        $user_rank->setTitle($_POST['title']);
        if (empty($user_rank->title)) {
            return false;
        }

        $user_rank->setImage($_POST['image']);
        $user_rank->setMinPosts($_POST['min_posts']);
        $user_rank->setRepeatImage($_POST['repeat_image']);
        $user_rank->rank_id = (int)$_POST['rank_id'];
        $user_rank->stack = (bool)$_POST['stack'];

        $user_rank->save();
    }


    public function postRank($rank)
    {
        if ($rank->id) {
            $rank->allow_local_avatars = isset($_POST['allow_local_avatars']);
            $rank->minimum_local_posts = (int)$_POST['minimum_local_posts'];
            
            $rank->allow_remote_avatars = isset($_POST['allow_remote_avatars']);
            $rank->minimum_remote_posts = (int)$_POST['minimum_remote_posts'];
        } else {
            $rank->group_id = (int)$_POST['group_id'];
        }
        return $rank->save();
    }

    public function reported()
    {
        javascript('jsquery');
        javascript('modules/comments/admin');
        javascript('modules/comments/quick_view');
        Layout::addStyle('comments');
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
        Layout::addStyle('comments');
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