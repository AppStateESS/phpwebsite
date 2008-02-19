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
    var $id	      = 0;

    // Id of thread
    var $thread_id    = 0;

    // Id of comment this comment is a child of
    var $parent	      = 0;

    // Subject of comment
    var $subject      = null;

    // Content of comment
    var $entry	      = null;

    // name of anonymous submitter
    var $anon_name  = null;

    // Author's user id
    var $author_id    = 0;

    // IP address of poster
    var $author_ip    = null;

    // Date comment was created
    var $create_time  = 0;

    // Date comment was edited
    var $edit_time    = 0;

    // Reason comment was edited
    var $edit_reason  = null;

    // Name of person who edited the comment
    var $edit_author  = null;

    // Error encountered when processing object
    var $_error	      = null;

    function Comment_Item($id=null)
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

    function init()
    {
	if (!isset($this->id))
	    return FALSE;

	$db = new PHPWS_DB('comments_items');
	$result = $db->loadObject($this);
	if (PEAR::isError($result))
	    return $result;
    }


    function setId($id)
    {
	$this->id = (int)$id;
    }

    function setThreadId($thread_id)
    {
	$this->thread_id = (int)$thread_id;
    }

    function getThreadId()
    {
	return $this->thread_id;
    }

    function setParent($parent)
    {
	$this->parent = (int)$parent;
    }


    function setSubject($subject)
    {
	$this->subject = strip_tags(trim($subject));
    }

    function setEntry($entry)
    {
        $entry = strip_tags($entry);
	$this->entry = PHPWS_Text::parseInput($entry);
    }

    function getEntry($format=TRUE, $quoted=FALSE)
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

    function setAnonName($name=null)
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

    function stampAuthor()
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

    function stampIP()
    {
	$this->author_ip = $_SERVER['REMOTE_ADDR'];
    }

    function getIP()
    {
	return $this->author_ip;
    }

    function stampCreateTime()
    {
	$this->create_time = mktime();
    }

    function getCreateTime($format=TRUE)
    {
	if ($format) {
	    return strftime(COMMENT_DATE_FORMAT, $this->create_time);
	} else {
	    return $this->create_time;
	}
    }

    function getRelativeTime()
    {
        return PHPWS_Time::relativeTime($this->create_time);
    }

    function stampEditor()
    {
	$this->edit_author = Current_User::getDisplayName();
	$this->edit_time = gmmktime();
    }
  

    function getEditTime($format=TRUE)
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

    function setEditReason($reason)
    {
	$this->edit_reason = strip_tags($reason);
    }

    function getEditReason()
    {
	return $this->edit_reason;
    }


    function getEditAuthor()
    {
	return $this->edit_author;
    }

    function getAuthor()
    {
	if (!empty($GLOBALS['Comment_Users'])) {
            $author = @$GLOBALS['Comment_Users'][$this->author_id];
        }
        
        if (empty($author)) {
            $author = new Comment_User($this->author_id);
        }

        return $author;
    }

    function getAuthorName()
    {
        if (!$this->author_id && $this->anon_name) {
            return $this->anon_name;
        } else {
            $author = $this->getAuthor();
            return $author->display_name;
        }
    }

    function getError()
    {
	return $this->_error;
    }

    function getTpl($allow_anon)
    {
        $author = $this->getAuthor();

        /**
         * If anonymous users are allowed to post or
         * the current user is logged in
         */
        if ($allow_anon || Current_User::isLogged()) {
            $can_post = true;
        } else {
            $can_post = false;
        }

	$author_info = $author->getTpl();

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
            $template['REPORT_LINK'] = $this->reportLink();
        }
	$template['EDIT_LINK']	     = $this->editLink();
	$template['DELETE_LINK']     = $this->deleteLink();
	$template['VIEW_LINK']	     = $this->viewLink();


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

	if (Current_User::allow('comments')) {
	    $template['IP_ADDRESS'] = $this->getIp();
	}
	$template = array_merge($author_info, $template);
	return $template;
    }

    function save()
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
	}

	if ((bool)$this->id) {
	    $this->stampEditor();
	    $increase_count = FALSE;
	} else {
	    $increase_count = TRUE;
	}

	$db = new PHPWS_DB('comments_items');
	$result = $db->saveObject($this);
	if (!PEAR::isError($result) && $increase_count) {
	    $thread = new Comment_Thread($this->thread_id);
	    $thread->increaseCount();
	    $thread->postLastUser($this->author_id);
	    $thread->save();
	}
	return $result;
    }

    function editLink()
    {
	if (Current_User::allow('comments') ||
	    ($this->author_id > 0 && $this->author_id == Current_User::getId())
	    ) {
	    $vars['user_action']   = 'post_comment';
	    $vars['thread_id']	   = $this->thread_id;
	    $vars['cm_id']	   = $this->id;
	    return PHPWS_Text::moduleLink(dgettext('comments', 'Edit'), 'comments', $vars);
	} else {
	    return null;
	}
    }

    function deleteLink()
    {
	if (Current_User::allow('comments', 'delete_comments')) {
	    $vars['QUESTION'] = dgettext('comments', 'Are you sure you want to delete this comment?');
	    $vars['ADDRESS'] = 'index.php?module=comments&amp;cm_id=' . $this->id . '&amp;admin_action=delete_comment&amp;authkey='
		. Current_User::getAuthKey();
	    $vars['LINK'] = dgettext('comments', 'Delete');
	    return Layout::getJavascript('confirm', $vars);
	} else {
	    return null;
	}

    }

    function quoteLink()
    {
	$vars['user_action']   = 'post_comment';
	$vars['thread_id']     = $this->thread_id;
	$vars['cm_parent']     = $this->id;
	return PHPWS_Text::moduleLink(dgettext('comments', 'Quote'), 'comments', $vars);
    }

    function replyLink()
    {
	$vars['user_action']   = 'post_comment';
	$vars['thread_id']     = $this->thread_id;
	return PHPWS_Text::moduleLink(dgettext('comments', 'Reply'), 'comments', $vars);
    }

    function reportLink()
    {
        $link = PHPWS_Text::linkAddress('comments', array('user_action'=>'report_comment',
                                                          'cm_id' => $this->id));
        return sprintf('<a href="#" onclick="loadRequester(\'%s\', \'%s\', \'void(0)\'); return false">%s</a>',
                       $link,
                       sprintf('alert(\\\'%s\\\')', addslashes(dgettext('comments', 'Comment reported'))),
                       dgettext('comments', 'Report'));
    }

    function viewLink()
    {
	$vars['user_action']   = 'view_comment';
	$vars['cm_id']	   = $this->id;

	return PHPWS_Text::moduleLink($this->subject, 'comments', $vars);
    }

    /**
     * Removes a comment from the database
     */
    function delete()
    {
        // physical removal
        $thread = new Comment_Thread($this->thread_id);
        $db = new PHPWS_DB('comments_items');
        $db->addWhere('id', $this->id);
        $db->delete();

        // clear replies to this comment
        $this->clearChildren();

        // decrease thread count
        $thread->decreaseCount();
        $thread->save();
    }

    /**
     * Sets the replies to this comment to zero
     */
    function clearChildren()
    {
        PHPWS_DB::query('update comments_items set parent=0 where parent=' . $this->id);
    }

    function responseNumber()
    {
	$vars['user_action'] = 'view_comment';
	$vars['cm_id']	     = $this->parent;

	return PHPWS_Text::moduleLink($this->parent, 'comments', $vars);
    }

    function responseAuthor()
    {
        $comment = new Comment_Item($this->parent);
	$vars['user_action']   = 'view_comment';
	$vars['cm_id']	       = $comment->id;
	return PHPWS_Text::moduleLink($comment->getAuthorName(), 'comments', $vars);
    }

}

?>