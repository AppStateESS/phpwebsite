<?php

/**
 * Class for comment threads. Threads hold all the comments for
 * a specific item.
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::initModClass('comments', 'Comment_Item.php');

define('NO_COMMENTS_FOUND', 'none');

class Comment_Thread {
    public $id             = 0;
    public $key_id         = null;
    public $total_comments = 0;
    public $last_poster    = null;
    public $allow_anon     = 0;
    /**
     * Default approval type:
     * 0 - no approval necessary
     * 1 - anonymous must approve
     * 2 - all user comments must be approved
     */
    public $approval       = 0;
    public $_key           = null;
    public $_comments      = null;
    public $_error         = null;
    public $_return_url    = null;
    public $locked         = 0;
    public $monitored      = 0;
    public $send_notice    = 1;


    public function __construct($id=0)
    {
        if (empty($id)) {
            return;
        }

        $this->setId($id);
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('comments_threads');
        $db->addColumn('comments_threads.*');
        $db->addColumn('comments_monitors.thread_id', null, 'monitored');
        $db->addColumn('comments_monitors.send_notice', null, 'send_notice');
        $db->addJoin('left', 'comments_threads', 'comments_monitors', 'id', 'thread_id');
        $db->addWhere('comments_monitors.user_id', Current_User::getId());
        $db->addWhere('id', $this->id);

        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $this->_error = $result->getMessage();
        }

        $this->loadKey();
        $this->loadTopic();
    }

    public function setApproval($approval)
    {
        $this->approval = (int)$approval;
        if ($this->approval < 0 || $this->approval > 2) {
            $this->approval = 0;
        }
    }

    public function allowAnonymous($anon)
    {
        $this->allow_anon = (int)(bool)$anon;
    }

    public function countComments($formatted=FALSE)
    {
        if ($formatted) {
            if (empty($this->total_comments)) {
                return dgettext('comments', 'No comments');
            } else {
                return sprintf(dngettext('comments', '%d comment', '%d comments', $this->total_comments), $this->total_comments);
            }
        } else {
            return $this->total_comments;
        }
    }

    public function loadKey()
    {
        $this->_key = new Key($this->key_id);
    }

    public function getLastPoster()
    {
        return $this->last_poster;
    }

    /**
     * Creates a new thread
     *
     * If there is a thread in the database, it is loaded.
     * If there is NOT then one is created.
     */
    public function buildThread()
    {
        $db = new PHPWS_DB('comments_threads');
        $db->addColumn('comments_threads.*');
        $db->addColumn('comments_monitors.thread_id', null, 'monitored');
        $db->addColumn('comments_monitors.send_notice', null, 'send_notice');
        $join_on_2 = 'thread_id  AND comments_monitors.user_id = '.Current_User::getId();
        $db->addJoin('left', 'comments_threads', 'comments_monitors', 'id', $join_on_2);
        $db->addWhere('key_id', $this->key_id);
        $result = $db->loadObject($this);

        if (PEAR::isError($result)) {
            $this->_error = $result->getMessage();
            return $result;
        } elseif (empty($result)) {
            $result = $this->save();
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);

                $this->_error = dgettext('comments', 'Error occurred trying to create new thread.');

            }
            return TRUE;
        } else {
            $this->loadTopic();
            return TRUE;
        }
    }


    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function setSourceUrl($link)
    {
        $link = str_replace('&amp;', '&', $link);
        $this->source_url = stristr($link, 'index.php?');
    }

    public function getSourceUrl($full=FALSE, $comment_id=0)
    {

        PHPWS_Core::initCoreClass('DBPager.php');
        $url = DBPager::getLastView('comments_items');

        if ($comment_id) {
            $url .= "#cm_$comment_id";
        }

        if ($full==TRUE) {
            $url = sprintf('<a href="%s">%s</a>', $url, dgettext('comments', 'Go back'));
        }


        return $url;
    }

    public function setKey($key)
    {
        $this->_key = $key;
    }

    public function postLink()
    {
        $vars['uop']   = 'post_comment';
        $vars['thread_id']     = $this->id;
        $str = dgettext('comments', 'Post New Comment');
        return PHPWS_Text::moduleLink('<span>'.$str.'</span>', 'comments', $vars, null, $str, 'comment_postnew_link');
    }

    public function save()
    {
        $db = new PHPWS_DB('comments_threads');
        return $db->saveObject($this);
    }

    public function delete()
    {
        $db = new PHPWS_DB('comments_items');
        $db->addWhere('thread_id', $this->id);
        $item_result = $db->delete();

        if (PEAR::isError($item_result)) {
            return $item_result;
        }

        $db = new PHPWS_DB('comments_threads');
        $db->addWhere('id', $this->id);
        $thread_result = $db->delete();

        if (PEAR::isError($thread_result)) {
            return $thread_result;
        }
        return TRUE;
    }

    public function _getTimePeriod()
    {
        switch ($_GET['time_period']) {
        case 'today':
            return gmmktime(0,0,0);
            break;

        case 'yd':
            return gmmktime(0,0,0, gmdate('m'), gmdate('d')-1);
            break;

        case 'week':
            return gmmktime(0,0,0, gmdate('m'), gmdate('d')-7);
            break;

        case 'month':
            return gmmktime(0,0,0, gmdate('m')-1);
            break;
        }

    }

    public function priorReport()
    {
        return sprintf('<img src="images/mod/comments/reported.png" title="%s" />', dgettext('comments', 'Reported!'));
    }

    public function setReturnUrl($url)
    {
        $this->_return_url = $url;
    }

    public function view($parent_id=0)
    {
        Layout::addStyle('comments');

        javascript('modules/comments/report', array('reported'=>$this->priorReport()));

        if (Current_User::isLogged()) {
            $this->miniAdmin();
        }

        PHPWS_Core::initCoreClass('DBPager.php');

        $time_period = array('all'    => dgettext('comments', 'All'),
                             'today'  => dgettext('comments', 'Today'),
                             'yd'     => dgettext('comments', 'Since yesterday'),
                             'week'   => dgettext('comments', 'This week'),
                             'month'  => dgettext('comments', 'This month')
                             );

        $order_list = array('old_all'  => dgettext('comments', 'Oldest first'),
                            'new_all'  => dgettext('comments', 'Newest first'));


        $pager = new DBPager('comments_items', 'Comment_Item');
        $pager->addWhere('approved', 1);
        $pager->setAnchor('comments');
        $pager->saveLastView();
        $form = new PHPWS_Form;

        if (!$this->_return_url) {
            $getVals = PHPWS_Text::getGetValues();

            if (!empty($getVals)) {
                $referer[] = 'index.php?';
                foreach ($getVals as $key=>$val) {
                    $referer[] = "$key=$val";
                }
                $form->addHidden('referer', urlencode(implode('&', $referer)));
            }
        } else {
            $form->addHidden('referer', urlencode($this->_return_url));
        }

        $form->addHidden('module', 'comments');
        $form->addHidden('uop', 'change_view');
        $form->addSelect('time_period', $time_period);
        $form->addSelect('order', $order_list);

        // set where clauses
        if (isset($_GET['time_period']) && $_GET['time_period'] != 'all') {
            $form->setMatch('time_period', $_GET['time_period']);
            $time_period = $this->_getTimePeriod();
            $pager->addWhere('create_time', $time_period, '>=');
        }

        if (!empty($parent_id)) {
            $pager->addWhere('parent', (int)$parent_id);
        }
        $pager->addWhere('thread_id', $this->id);

        $user_order = PHPWS_Cookie::read('cm_order_pref');

        if (isset($_GET['order'])) {
            $default_order = &$_GET['order'];
        } elseif ($user_order) {
            if ($user_order == 1) {
                $default_order = 'old_all';
            } else {
                $default_order = 'new_all';
            }
        } else {
            $default_order = PHPWS_Settings::get('comments', 'default_order');
        }

        switch ($default_order) {
        case 'new_all':
            $pager->setOrder('create_time', 'desc');
            break;

        case 'old_all':
            $pager->setOrder('create_time', 'asc');
            break;
        }
        $form->setMatch('order', $default_order);
        $form->addSubmit(dgettext('comments', 'Go'));
        $form->setMethod('get');

        $page_tags = $form->getTemplate();

        // BULK_ACTIONS
        $actions = '';
        // If we can delete comments
        if ($this->total_comments && $this->userCan('delete_comments')) {
            $actions .= '<option value="">'.dgettext('comments', 'Choose an action')."</option>\n";
            $actions .= '<option value="delete_comment">'.dgettext('comments', 'Delete')."</option>\n";
            $js_vars = array();
            $js_vars['value']        = dgettext('comments', 'Go');
            $js_vars['select_id']    = 'list_actions_edit'; // the name of your select input
            $js_vars['action_match'] = 'delete_comment';
            $js_vars['message']      = dgettext('comments', 'Are you sure you want to delete the checked items?');
            $page_tags['BULK_ACTION_BUTTON'] = javascript('select_confirm', $js_vars);
        }
        // If phpwsbb is installed && we have moderation & forking privileges
        if (isset($GLOBALS['Modules']['phpwsbb']) && $this->userCan('fork_messages', 'phpwsbb')) {
            $actions .= '<option value="move_comments">'.dgettext('comments', 'Move to another Topic')."</option>\n";
            $actions .= '<option value="split_comments">'.dgettext('comments', 'Split to a new topic')."</option>\n";
        }
        if (!empty($actions)) {
            $page_tags['BULK_ACTION'] = '<select id="list_actions_edit" name="aop" title="'
                . dgettext('comments', 'Select the desired action for the checked comments').'">'
                . $actions."</select>\n ";
            $page_tags['MOD_FORM_START'] = '<form class="phpws-form" id="phpws_form" action="index.php?module=comments" method="post">'
                . '<input type="hidden" name="authkey" value="'.Current_User::getAuthKey().'">';
            $page_tags['MOD_FORM_END'] = '</form>';
        }

        if ($this->canComment())
            $page_tags['NEW_POST_LINK'] = $this->postLink();
        elseif ($this->locked)
            $page_tags['NEW_POST_LINK'] = dgettext('comments', 'This topic is locked.  No more comments');

        $pager->setModule('comments');
        $pager->setTemplate(COMMENT_VIEW_TEMPLATE);
        $pager->addPageTags(array_merge($this->getStatusTags(), $page_tags));
        $pager->addRowTags('getTpl', $this);
        $pager->setLimitList(array(25,50,100));
        $pager->setDefaultLimit(COMMENT_DEFAULT_LIMIT);
        $pager->setEmptyMessage(dgettext('comments', 'No comments'));
        $pager->initialize();
        $rows = $pager->getRows();
        if (!empty($rows)) {
            $this->_createUserList($rows);
        }

        $content = $pager->get();
        if (PHPWS_Error::logIfError($content)) {
            return null;
        }
        $GLOBALS['comments_viewed'] = true;

        // If phpwsbb is installed...
        if (isset($GLOBALS['Modules']['phpwsbb'])) {
            PHPWS_Core::initModClass('phpwsbb', 'BB_Data.php');
            // If this is already in a forum, offer to disassociate it
            if (!empty($this->phpwsbb_topic) && !$this->phpwsbb_topic->is_phpwsbb)
                PHPWSBB_Data::drop_item_link($this->id, $this->_key->module, $this->_key->item_name);
            elseif (empty($this->phpwsbb_topic))
                // otherwise, add an "Attach to Forum" link to the MiniAdmin
                PHPWSBB_Data::move_item_link($this->_key);
        }
        // If this thread is being monitored and the "send_notice" flag is not set, set it
        if ($this->monitored && !$this->send_notice) {
            $db = new PHPWS_DB('comments_monitors');
            $db->addWhere('thread_id', $this->id);
            $db->addWhere('user_id', Current_User::getId());
            $db->addValue('send_notice', 1);
            $result = $db->update();
        }

        return $content;
    }

    public function canComment()
    {
        if (isset($GLOBALS['Perms']['canComment'][$this->id]))
            return $GLOBALS['Perms']['canComment'][$this->id];

        $user = Comments::getCommentUser(Current_User::getId());
        $result =  (!$this->locked || $this->userCan()) && !$user->locked && (Current_User::isLogged() || $this->allow_anon);
        $GLOBALS['Perms']['canComment'][$this->id] = $result;
        return $GLOBALS['Perms']['canComment'][$this->id];
    }

    /**
     * Creates $GLOBAL lists of comment author information
     *   $GLOBALS['Comment_Users'] is a list of all relevant Comment_User objects
     *   $GLOBALS['Comment_UsersGroups'] is a list of group memberships (for user ranking)
     */
    public function _createUserList(&$comment_list)
    {
        $author_list = array();
        foreach ($comment_list as $comment) {
            $author_id = & $comment->author_id;
            if ($author_id == 0 || in_array($author_id, $author_list)) {
                continue;
            }

            $author_list[] = $author_id;
        }

        // Load  all relevant Comment_User objects
        $result = Demographics::getList($author_list, 'comments_users', 'Comment_User');

        if (PHPWS_Error::logIfError($result)) {
            return;
        }
        
        $GLOBALS['Comment_Users'] = $result;

        // Load all groups that these authors belong to (kept separate to save query time)
        $db = new PHPWS_DB('users_members');
        $db->addWhere('member_id', $author_list);
        $db->addColumn('group_id');
        $db->addColumn('member_id');
        $result = $db->select('col');
        if (!PHPWS_Error::logIfError($result) && !empty($result)) {
            foreach ($result as $value) {
                $GLOBALS['Comment_UsersGroups'][$value['member_id']][] = $value['group_id'];
            }
        }

        return TRUE;
    }

    public function increaseCount()
    {
        $this->total_comments++;
    }

    public function decreaseCount()
    {
        if ($this->total_comments > 0) {
            $this->total_comments--;
        }
    }

    public function updateLastPoster()
    {
        $db = new PHPWS_DB('comments_items');
        $db->addWhere('thread_id', $this->id);
        $db->addOrder('create_time desc');
        $db->setLimit(1);
        $db->addColumn('author_id');
        $last_poster = $db->select('one');
        if (PHPWS_Error::logIfError($last_poster)) {
            $last_author = 0;
        }
        $this->postLastUser($last_poster);
        return $this->save();
    }

    public function updateCount()
    {
        $db = new PHPWS_DB('comments_items');
        $db->addWhere('thread_id', $this->id);
        $db->addColumn('id', null, null, true);
        $comment_count = $db->select('one');
        if (PHPWS_Error::logIfError($comment_count)) {
            return false;
        }
        $this->total_comments = $comment_count;
        return $this->save();
    }

    public function postLastUser($author_id)
    {
        if ($author_id) {
            $author = Comments::getCommentUser($author_id);
            $this->last_poster = $author->display_name;
        } elseif ($author_id == 0) {
            $this->last_poster = DEFAULT_ANONYMOUS_TITLE;
        } else {
            $this->last_poster = null;
        }
    }

    public function miniAdmin()
    {
        $vars['thread_id'] = $this->id;
        if ($this->monitored) {
            $vars['user_action'] = 'unset_monitor';
            $link[] = PHPWS_Text::secureLink(dgettext('comments', 'Stop Monitoring'), 'comments', $vars);
        } else {
            $vars['user_action'] = 'set_monitor';
            $link[] = PHPWS_Text::secureLink(dgettext('comments', 'Monitor this Thread'), 'comments', $vars);
        }
        unset($vars['user_action']);
        if ($this->userCan()) {
            $vars['aop'] = 'set_anon_posting';
            if ($this->allow_anon) {
                $vars['allow'] = '0';
                $link[] = PHPWS_Text::secureLink(dgettext('comments', 'Disable anonymous posting'), 'comments', $vars);
            } else {
                $vars['allow'] = '1';
                $link[] = PHPWS_Text::secureLink(dgettext('comments', 'Enable anonymous posting'), 'comments', $vars);
            }
            unset($vars['allow']);
            $vars['aop'] = 'lock_thread';
            if ($this->locked) {
                $vars['lock'] = '0';
                $link[] = PHPWS_Text::secureLink(dgettext('comments', 'Unlock this Thread'), 'comments', $vars);
            } else {
                $vars['lock'] = '1';
                $link[] = PHPWS_Text::secureLink(dgettext('comments', 'Lock this Thread'), 'comments', $vars);
            }
            unset($vars['lock']);
        }

        MiniAdmin::add('comments', $link);
    }

    public function setLock($status)
    {
        $this->locked = (int) (bool) $status;
        // If the changes were saved & phpwsbb is installed...
        if ($this->save() && !empty($this->phpwsbb_topic)) {
            $this->phpwsbb_topic->locked = $this->locked;
            $this->phpwsbb_topic->commit();
        }
    }

    /**
     * Extension of Current_User::allow() that also checks to see if this thread
     * belongs to a phpwsbb forum to be sure the user is a moderator.
     *
     * @param string $function : subpermission that we're checking for
     * @param string $module : permission module that we're checking for. Defaults to 'comments'
     * @return bool : Success or faliure
     */
    public function userCan($function = null, $module = 'comments')
    {
        if (isset($GLOBALS['Perms'][$module][$this->id][$function]))
            return $GLOBALS['Perms'][$module][$this->id][$function];
        $is_moderator = empty($this->phpwsbb_topic) || PHPWSBB_Forum::canModerate(Current_User::getId(), $this->phpwsbb_topic->fid);
        $GLOBALS['Perms'][$module][$this->id][$function] = $this->id && $is_moderator && Current_User::allow($module, $function);
        return $GLOBALS['Perms'][$module][$this->id][$function];
    }

    /**
     * If phpwsbb is installed this will load any associated topic into $this->phpwsbb_topic
     *
     * @param none
     * @return none
     */
    public function loadTopic()
    {
        if (!isset($GLOBALS['Modules']['phpwsbb']))
            return;
        PHPWS_Core::initModClass('phpwsbb', 'Topic.php');
        PHPWS_Core::initModClass('phpwsbb', 'Forum.php');
        $topic = & new PHPWSBB_Topic($this->id);
        if ($topic->id)
            $this->phpwsbb_topic = $topic;
    }

    /**
     * Sets the 'Allow Anonymous Posting' status for this thread
     *
     * @param int $status : 0 off or 1 on
     * @return mixed : success or error object
     */
    public function setAnonPosting($status)
    {
        $db = new PHPWS_DB('comments_threads');
        $db->addWhere('id', $this->id);
        $db->addValue('allow_anon', (int) $status);
        $result = $db->update();
        if (!PHPWS_Error::logIfError($result))
            return true;
    }

    /**
     * Shows the current user's authorizations
     *
     * It's pretty much just here for testing purposes.
     * You can get rid of it if you want to.
     *
     * @param none
     * @return string : success or error object
     */
    public function getStatusTags()
    {
        if (!empty($this->phpwsbb_topic)) {
            $forum = $this->phpwsbb_topic->get_forum();
            if ($forum->active)
                $tags = $forum->getStatusTags();
        }

        PHPWS_Text::filterText('some text');
        $filters = explode(',', TEXT_FILTERS);
        if (ALLOW_TEXT_FILTERS && in_array('bb', $filters)) {
            $list[] = dgettext('comments', 'cbparser BB code is <b>on</b>');
            if (ALLOW_BB_SMILIES)
                $list[] = dgettext('comments', 'Smilies are <b>on</b>');
            else
                $list[] = dgettext('comments', 'Smilies are <b>off</b>');
            if (ALLOW_BB_IMAGES)
                $list[] = dgettext('comments', '[IMG] is <b>allowed</b>');
            else
                $list[] = dgettext('comments', '[IMG] is <b>not allowed</b>');
        }

        if (ALLOW_TEXT_FILTERS && in_array('pear', $filters)) {
            $list[] = dgettext('comments', 'Pear BB code is <b>on</b>');
            $list[] = dgettext('comments', 'The following filters are <b>on</b>');
            $list[] = '&nbsp;&nbsp;&nbsp;'.PEAR_BB_FILTERS;
        }

        if (PHPWS_ALLOWED_TAGS)
            $list[] = dgettext('comments', 'HTML tags are <b>on</b>');
        else
            $list[] = dgettext('comments', 'HTML tags are <b>off</b>');

        if (ALLOW_PROFANITY)
            $list[] = dgettext('comments', 'Profanity is <b>allowed</b>');
        else
            $list[] = dgettext('comments', 'Profanity is <b>not allowed</b>');

        $tags['STATUS_FLAGS'] = implode("<br />\n", $list);
        $tags['STATUS_TITLE'] = dgettext('comments', 'Feature Summary');

        return $tags;
    }

}

?>