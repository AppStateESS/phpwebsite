<?php
/**
 * Contains information for an individual comment
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

define('COMMENTS_MISSING_THREAD', 1);

PHPWS_Core::requireConfig('comments', 'config.php');

class Comment_Item {
    // Id number of comment
    public $id	      = 0;

    // Id of thread
    public $thread_id    = 0;

    // Id of comment this comment is a child of
    public $parent	      = 0;

    // Subject of comment
    public $subject      = null;

    // Content of comment
    public $entry	      = null;

    // name of anonymous submitter
    public $anon_name  = null;

    // Author's user id
    public $author_id    = 0;

    // IP address of poster
    public $author_ip    = null;

    // Date comment was created
    public $create_time  = 0;

    // Date comment was edited
    public $edit_time    = 0;

    // Reason comment was edited
    public $edit_reason  = null;

    // Name of person who edited the comment
    public $edit_author  = null;

    // Number of times this comment has been reported
    // as needing review
    public $reported     = 0;

    // Approval status
    public $approved     = 1;

    public $author = null;

    // Error encountered when processing object
    public $_error       = null;

    // Userid of author of comment this comment is a child of
    public $parent_author_id = 0;

    // UnregName of author of comment this comment is a child of
    public $parent_anon_name = null;

    // If set, this comment is locked to this thread.  It cannot be moved or deleted.
    public $protected      = 0;

    public function __construct($id=null)
    {
	if (empty($id)) {
	    return;
	}

	$this->setId($id);
	$result = $this->init();
	if (PEAR::isError($result)) {
	    $this->_error = $result;
	}
    }

    public function init()
    {
	if (!isset($this->id))
	    return FALSE;

	$db = new PHPWS_DB('comments_items');
	$result = $db->loadObject($this);
	if (PEAR::isError($result)) {
	    return $result;
        } elseif (!$result) {
            $this->id = 0;
        }
    }

    public function setId($id)
    {
	$this->id = (int)$id;
    }

    public function setThreadId($thread_id)
    {
	$this->thread_id = (int)$thread_id;
    }

    public function getThreadId()
    {
	return $this->thread_id;
    }

    public function setParent($parent)
    {
	$this->parent = (int)$parent;
    }

    public function setSubject($subject)
    {
	$this->subject = strip_tags(trim($subject));
    }

    public function setEntry($entry)
    {
        $entry = strip_tags($entry);
	$this->entry = PHPWS_Text::parseInput($entry);
    }

    public function getEntry($format=TRUE, $quoted=FALSE)
    {
	if ($format) {
            $entry = PHPWS_Text::parseOutput($this->entry, true, true);
        } else {
            $entry =  $this->entry;
        }

        if ($quoted) {
            return sprintf('[quote="%s"]%s[/quote]', $this->getAuthorName(), trim($entry));
        } else {
            return $entry;
        }
    }

    public function setAnonName($name=null)
    {
        $name = strip_tags($name);

        if (empty($name) || strlen($name) < 2) {
            $this->anon_name = DEFAULT_ANONYMOUS_TITLE;
        } else {
            include PHPWS_Core::getConfigFile('comments', 'forbidden.php');
            foreach ($forbidden_names as $fn) {
                if (preg_match('/' . $fn . '/i', $name)) {
                    return false;
                }
            }

            $this->anon_name = & $name;
        }
        return true;
    }

    public function stampAuthor()
    {
	if (Current_User::isLogged()) {
	    $this->author_id = Current_User::getId();
	    $result = Comments::updateCommentUser($this->author_id);

	    if (PEAR::isError($result)) {
		PHPWS_Error::log($result);
		return FALSE;
	    }
	} else {
	    $this->author_id = 0;
	}
    }

    public function stampIP()
    {
	$this->author_ip = $_SERVER['REMOTE_ADDR'];
    }

    public function getIP()
    {
	return $this->author_ip;
    }

    public function stampCreateTime()
    {
	$this->create_time = mktime();
    }

    public function getCreateTime($format=TRUE)
    {
	if ($format) {
	    return strftime(COMMENT_DATE_FORMAT, $this->create_time);
	} else {
	    return $this->create_time;
	}
    }

    public function getRelativeTime()
    {
        return PHPWS_Time::relativeTime($this->create_time);
    }

    public function stampEditor()
    {
	$this->edit_author = Current_User::getDisplayName();
	$this->edit_time = gmmktime();
    }


    public function getEditTime($format=TRUE)
    {
	if ($format) {
	    if (empty($this->edit_time)) {
		return null;
	    } else {
		return gmstrftime(COMMENT_DATE_FORMAT, $this->edit_time);
	    }
	} else {
	    return $this->edit_time;
	}
    }

    public function setEditReason($reason)
    {
	$this->edit_reason = strip_tags($reason);
    }

    public function getEditReason()
    {
	return $this->edit_reason;
    }


    public function getEditAuthor()
    {
	return $this->edit_author;
    }

    public function getAuthor()
    {
	if (!empty($GLOBALS['Comment_Users'])) {
            $author = @$GLOBALS['Comment_Users'][$this->author_id];
        }

        if (empty($author)) {
            $author = new Comment_User($this->author_id);
        }

        return $author;
    }

    public function getAuthorName()
    {
        if (!$this->author_id && $this->anon_name) {
            return $this->anon_name;
        } else {
            $author = $this->getAuthor();
            return $author->display_name;
        }
    }

    public function getError()
    {
	return $this->_error;
    }

    public function getTpl(&$thread)
    {
        $author = $this->getAuthor();

        // If anonymous users are allowed to post or
        // the current user is logged in and not banned from posting
        $can_post = $thread->canComment();

        $is_moderator = empty($thread->phpwsbb_topic) || PHPWSBB_Forum::isModerator($author->user_id, $thread->phpwsbb_topic->fid);
        $author_info = $author->getTpl($is_moderator);

        if (!$this->author_id && $this->anon_name) {
            $author_info['AUTHOR_NAME'] = & $this->anon_name;
            $author_info['ANONYMOUS_TAG'] = COMMENT_ANONYMOUS_TAG;
        }

	$template['SUBJECT_LABEL'] = dgettext('comments', 'Subject');
	$template['ENTRY_LABEL']   = dgettext('comments', 'Comment');
	$template['AUTHOR_LABEL']  = dgettext('comments', 'Author');
	$template['POSTED_BY']	   = dgettext('comments', 'Posted by');
	$template['POSTED_ON']	   = dgettext('comments', 'Posted on');

	$template['SUBJECT']	     = $this->subject;
	$template['ENTRY']	     = $this->getEntry(TRUE);
	$template['CREATE_TIME']     = $this->getCreateTime();
        $template['RELATIVE_CREATE'] = $this->getRelativeTime();
        if ($can_post) {
            $template['QUOTE_LINK']  = $this->quoteLink();
            $template['REPLY_LINK']  = $this->replyLink();
            if (Current_User::isLogged()) {
                if (empty($_SESSION['Users_Reported_Comments'][$this->id]))
                    $template['REPORT_LINK'] = $this->reportLink();
                else
                    $template['REPORT_LINK'] = dgettext('comments', 'Reported!');
            }
            if ($thread->userCan() || ($this->author_id > 0 && $this->author_id == Current_User::getId()))
                $template['EDIT_LINK']       = $this->editLink();
            if (!$this->protected && $thread->userCan('delete_comments'))
                $template['DELETE_LINK']     = $this->deleteLink();
            // If phpwsbb is installed && we have moderation & forking privileges
            if (isset($GLOBALS['Modules']['phpwsbb']) && $thread->userCan('fork_messages', 'phpwsbb')) {
                $vars = array();
                $vars['op'] = 'split_topic';
                $vars['split_point'] = $this->create_time;
                $vars['topic'] = $this->thread_id;
                $str = dgettext('comments', 'Split topic from this position');
                $str = '<span>' . dgettext('comments', 'Split Topic') . '</span>';
                $title = dgettext('comments', 'Split topic from this position');
                $template['FORK_THIS'] = PHPWS_Text::secureLink($str, 'phpwsbb', $vars, null, $title, 'comment_fork_link');
            }
        }
        $template['VIEW_LINK']       = $this->viewLink();
        if ($thread->userCan('punish_users'))
            $template['PUNISH_LINK']     = $this->punishUserLink(true);

        if ($this->parent) {
            $template['RESPONSE_LABEL']  = dgettext('comments', 'In response to');
            $template['RESPONSE_NUMBER'] = $this->responseNumber();
            $template['RESPONSE_NAME']   = $this->responseAuthor();
        }

	if ($this->edit_time) {
	    $template['EDIT_LABEL']	   = dgettext('comments', 'Edited');
	    $template['EDIT_AUTHOR']	   = $this->getEditAuthor();
	    $template['EDIT_AUTHOR_LABEL'] = dgettext('comments', 'Edited by');
	    $template['EDIT_TIME_LABEL']   = dgettext('comments', 'Edited on');
	    $template['EDIT_TIME']	   = $this->getEditTime();
	    if (!empty($this->edit_reason)) {
		$template['EDIT_REASON']       = $this->getEditReason();
		$template['EDIT_REASON_LABEL'] = dgettext('comments', 'Reason');
	    } else {
                $template['EDIT_REASON'] = null;
            }
	} else {
            $template['EDIT_TIME'] = null;
            $template['EDIT_REASON'] = null;
            $template['EDIT_AUTHOR'] = null;
        }

        $template['ANCHOR'] = sprintf('<a name="cm_%s"></a>', $this->id);
        $str = dgettext('comments', 'Back to Top');
        $template['TO_TOP'] = sprintf('<a href="%s#comments"><img src="./images/mod/comments/back_to_top.png" title="%s" border="0" height="16" width="16"> %s</a>', $_SERVER['REQUEST_URI'],$str, $str);
        $template['COMMENT_ID'] = $this->id;

        if ($thread->userCan('delete_comments')) {
            $template['IP_ADDRESS_LABEL'] = dgettext('comments', 'IP');
            $template['IP_ADDRESS'] = $this->getIp();
            if (!$this->protected)
                $template['SELECT_THIS'] = '<input type="checkbox" name="cm_id[]" value="'.$this->id.'" title="'.dgettext('comments', 'Select this Comment').'">';
        }
        $template = array_merge($author_info, $template);
        return $template;
    }

    public function save($stamp_update=true)
    {
	if (empty($this->thread_id)) {
	    return PHPWS_Error::get(COMMENTS_MISSING_THREAD, 'comments', 'Comment_Item::save');
	}

        if (empty($this->subject)) {
            $this->subject = COMMENT_NO_SUBJECT;
        }

	if (empty($this->create_time)) {
	    $this->stampCreateTime();
	}

        if (empty($this->id)) {
    	    $this->stampIP();
    	    $this->stampAuthor();
    	    $increase_count = TRUE;
    	} else {
            if ($stamp_update) {
                $this->stampEditor();
            }
	    $increase_count = FALSE;
        }

	$db = new PHPWS_DB('comments_items');
	$result = $db->saveObject($this);
	if (!PEAR::isError($result) && $increase_count && $this->approved) {
            PHPWS_Error::logIfError($this->stampThread());
	}
	return $result;
    }

    public function stampThread()
    {
        $thread = new Comment_Thread($this->thread_id);
        $thread->increaseCount();
        $thread->postLastUser($this->author_id);
        $result = $thread->save();
        if (!PHPWS_Error::logIfError($result)) {
            // Update any associated phpwsbb topic's lastpost information
            if (!empty($thread->phpwsbb_topic)) {
                $thread->phpwsbb_topic->update_topic();
                $thread->phpwsbb_topic->commit();
            }
            // Start subscription?
            $user = Comments::getCommentUser(Current_User::getId());
            if (!$thread->monitored && $user->monitordefault)
                Comment_User::subscribe(Current_User::getId(), $thread->id);
            // Send notices to all subscribed users
            Comments::sendUpdateNotice($thread, $this);
        }
        return $result;
    }

    public function editLink()
    {
        $vars['uop']   = 'post_comment';
        $vars['cm_id'] = $this->id;
        $vars['thread_id'] = $this->thread_id;
        $str = '<span>' . dgettext('comments', 'Edit') . '</span>';
        $title = dgettext('comments', 'Edit this comment');
        return PHPWS_Text::secureLink($str, 'comments', $vars, null, $title, 'comment_edit_link');
    }

    public function deleteLink()
    {
        $vars['QUESTION'] = dgettext('comments', 'Are you sure you want to delete this comment?');
        $vars['ADDRESS'] = 'index.php?module=comments&amp;cm_id=' . $this->id . '&amp;aop=delete_comment&amp;authkey='
            . Current_User::getAuthKey();
        $vars['LINK'] = '<span>' . dgettext('comments', 'Delete') . '</span>';
        $vars['CLASS'] = 'comment_delete_link';
        $vars['TITLE'] = dgettext('comments', 'Delete this comment');
        return Layout::getJavascript('confirm', $vars);
    }

    public function clearReportLink()
    {
        return PHPWS_Text::secureLink(dgettext('comments', 'Clear'), 'comments',
                                      array('aop'=>'clear_report', 'cm_id'=>$this->id),
                                      NULL, dgettext('comments', 'Clear this report'));
    }

    public function punishUserLink($graphic=false)
    {
        $vars['address'] = PHPWS_Text::linkAddress('comments', array('aop'=>'punish_user',
                                                                     'cm_id'=>$this->id, 'authkey'=>Current_User::getAuthKey()), true);
        $vars['link_title'] = dgettext('comments', 'Punish this user');
        if ($graphic) {
            $vars['class'] = 'comment_punish_link';
            $vars['label'] = '<span>' . dgettext('comments', 'Punish this user') . '</span>';
        } else {
            $vars['label'] = dgettext('comments', 'Punish this user');
        }

        $vars['width'] = 240;
        $vars['height'] = 180;
        return javascript('open_window', $vars);
    }

    public function quoteLink()
    {
        $vars['uop']   = 'post_comment';
        $vars['thread_id'] = $this->thread_id;
        $vars['cm_parent'] = $this->id;
        $vars['type']      = 'quote';
        $str = '<span>' . dgettext('comments', 'Quote') . '</span>';
        $title = dgettext('comments', 'Quote this comment');
        return PHPWS_Text::moduleLink($str, 'comments', $vars, null, $title, 'comment_quote_link');
    }

    public function replyLink()
    {
        $vars['uop']   = 'post_comment';
        $vars['thread_id'] = $this->thread_id;
        $vars['cm_parent'] = $this->id;
        $str = '<span>' . dgettext('comments', 'Reply') . '</span>';
        $title = dgettext('comments', 'Reply to this comment');
        return PHPWS_Text::secureLink($str, 'comments', $vars, null, $title, 'comment_reply_link');
    }

    public function reportLink()
    {
        $str = '<span>' . dgettext('comments', 'Report') . '</span>';
        $title = dgettext('comments', 'Report this comment to an administrator');
        return sprintf('<a href="#" class="%s" onclick="report(%s, this); return false" title="%s">%s</a>',
                       'comment_report_link', $this->id, $title, $str);
    }

    public function viewLink()
    {
	$vars['uop']   = 'view_comment';
	$vars['cm_id']	   = $this->id;

	return PHPWS_Text::moduleLink($this->subject, 'comments', $vars);
    }

    /**
     * Removes a comment from the database
     */
    public function delete($reduce_count=true)
    {
        // Protected comments cannot be deleted
        if ($this->protected)
            return false;

        // physical removal
        $db = new PHPWS_DB('comments_items');
        $db->addWhere('id', $this->id);
        if (PHPWS_Error::logIfError($db->delete())) {
            return false;
        }

        // orphan replies to this comment
        $this->clearChildren();

        $thread = new Comment_Thread($this->thread_id);
        // decrease thread count
        if ($reduce_count) {
            $thread->decreaseCount();

            // Update parent thread's lastposter info
            // Update any associated phpwsbb topic's lastpost information
            if (!empty($thread->phpwsbb_topic)) {
                $thread->phpwsbb_topic->update_topic();
                $thread->phpwsbb_topic->commit();
                $thread->last_poster = $thread->phpwsbb_topic->lastpost_author;
            }
            else {
                $db = new PHPWS_DB('comments_items');
                $db->addColumn('comments_items.author_id');
                $db->addColumn('comments_items.anon_name');
                $db->addColumn('users.display_name');
                $db->addWhere('users.id', 'comments_items.author_id');
                $db->addWhere('thread_id', $this->thread_id);
                $db->addWhere('comments_items.approved', 1);
                $db->addOrder('create_time desc');
                $result = $db->select('row');
                if (PHPWS_Error::logIfError($result))
                    return;
                if (empty($result))
                    $thread->last_poster = dgettext('comments', 'None');
                elseif ($row['display_name'])
                    $thread->last_poster = $row['display_name'];
                else
                    $thread->last_poster = trim($row['anon_name'] . ' '.COMMENT_ANONYMOUS_TAG);
            }

            $thread->save();
        }
        return true;
    }

    /**
     * Sets the replies to this comment to zero
     */
    public function clearChildren()
    {
        PHPWS_DB::query('update comments_items set parent=0 where parent=' . $this->id);
    }

    public function responseNumber()
    {
	$vars['uop'] = 'view_comment';
	$vars['cm_id']	     = $this->parent;

	return PHPWS_Text::moduleLink($this->parent, 'comments', $vars);
    }

    public function responseAuthor()
    {
        if (!empty($this->parent_author_id)) {
            $author = Comments::getCommentUser($this->parent_author_id);
            $name = $author->display_name;
        }
        else if (!empty($this->parent_anon_name))
            $name = $this->parent_anon_name.' '.COMMENT_ANONYMOUS_TAG;
        else { // backward compatibility - cache data for future use
            $comment = new Comment_Item($this->parent);
            $name = $comment->getAuthorName();
            $this->parent_author_id = $comment->author_id;
            $this->parent_anon_name = $comment->anon_name;
            $db = new PHPWS_DB('comments_items');
            PHPWS_Error::logIfError($db->saveObject($this));
        }
        $vars['uop']   = 'view_comment';
        $vars['cm_id'] = $this->parent;
        return PHPWS_Text::moduleLink($name, 'comments', $vars);
    }

    public function reportTags()
    {
        $tpl['CHECK'] = sprintf('<input type="checkbox" name="cm_id[]" value="%s" />', $this->id);
        $tpl['SUBJECT'] = $this->viewLink();

        $tpl['ENTRY']   = sprintf('<span class="pointer" onmouseout="quick_view(\'#cm%s\'); return false" onmouseover="quick_view(\'#cm%s\'); return false">%s</span>',
                                  $this->id, $this->id,
                                  substr($this->entry, 0, 50));
        $tpl['FULL'] = sprintf('<div class="full-view" id="cm%s">%s</div>', $this->id, $this->getEntry());

        $links[] = $this->clearReportLink();
        $links[] = $this->deleteLink();
        $links[] = $this->punishUserLink();
        $tpl['ACTION']  = implode(' | ', $links);
        return $tpl;
    }

    public function historyTags()
    {
        $tpl['VIEW_LINK'] =  $this->viewLink();

        $tpl['ENTRY']   = sprintf('<span class="pointer" onmouseout="quick_view(\'#cm%s\'); return false" onmouseover="quick_view(\'#cm%s\'); return false">%s</span>',
                                  $this->id, $this->id,
                                  substr($this->entry, 0, 50));
        $tpl['FULL'] = sprintf('<div class="full-view" id="cm%s">%s</div>', $this->id, $this->getEntry());

	$tpl['POSTED_ON']	   = dgettext('comments', 'Posted on');
	$tpl['CREATE_TIME']   = $this->getCreateTime();

        if (Current_User::allow('comments')) {
            $tpl['CHECK'] = sprintf('<input type="checkbox" name="cm_id[]" value="%s" />', $this->id);
            $links[] = $this->deleteLink();
            $links[] = $this->punishUserLink();
            $tpl['ACTION']  = implode(' | ', $links);
        }
        return $tpl;
    }

    public function approvalTags()
    {
        if (!$this->author_id) {
            if (!empty($this->anon_name)) {
                $tpl['AUTHOR'] = sprintf('%s (%s)', $this->anon_name, DEFAULT_ANONYMOUS_TITLE);
            } else {
                $tpl['AUTHOR'] = DEFAULT_ANONYMOUS_TITLE;
            }
        }

        $tpl['CHECKBOX'] = sprintf('<input type="checkbox" name="cm_id[]" value="%s" />', $this->id);

        $approve = sprintf('<img src="images/mod/comments/ok.png" width="20" height="20" title="%s" alt="%s" />',
                           dgettext('comments', 'Approve this comment'),
                           dgettext('comments', 'Approval icon'));

        $remove = sprintf('<img src="images/mod/comments/cancel.png" width="20" height="20" title="%s" alt="%s" />',
                          dgettext('comments', 'Remove this comment'),
                          dgettext('comments', 'Removal icon'));


        $links[] = PHPWS_Text::secureLink($approve, 'comments', array('aop'=>'approve',
                                                                      'cm_id'=>$this->id));
        $links[] = PHPWS_Text::secureLink($remove, 'comments', array('aop'=>'remove',
                                                                     'cm_id'=>$this->id));
        $links[] = $this->punishUserLink(true);

        $tpl['ENTRY']   = sprintf('<span class="pointer" onmouseout="quick_view(\'#cm%s\'); return false" onmouseover="quick_view(\'#cm%s\'); return false">%s</span>',
                                  $this->id, $this->id,
                                  substr($this->entry, 0, 50));
        $tpl['FULL'] = sprintf('<div class="full-view" id="cm%s">%s</div>', $this->id, $this->getEntry());
        $tpl['SUBJECT'] = $this->viewLink();
        $tpl['ACTION'] = implode('', $links);

        return $tpl;
    }

    public function approve()
    {
        $this->approved = 1;
        $this->save(false);

        // Thread is not increased on save
        $this->stampThread();
    }

}

?>
