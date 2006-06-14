<?php
/**
 * Contains information for an individual comment
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

define('COMMENTS_MISSING_THREAD', 1);

PHPWS_Core::configRequireOnce('comments', 'config.php');

class Comment_Item {
    // Id number of comment
    var $id	      = 0;

    // Id of thread
    var $thread_id    = 0;

    // Id of comment this comment is a child of
    var $parent	      = 0;

    // Subject of comment
    var $subject      = NULL;

    // Content of comment
    var $entry	      = NULL;

    // Author's user id
    var $author_id    = 0;

    // IP address of poster
    var $author_ip    = NULL;

    // Date comment was created
    var $create_time  = 0;

    // Date comment was edited
    var $edit_time    = 0;

    // Reason comment was edited
    var $edit_reason  = NULL;

    // Name of person who edited the comment
    var $edit_author  = NULL;

    // Error encountered when processing object
    var $_error	      = NULL;

    function Comment_Item($id=NULL)
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

	$db = & new PHPWS_DB('comments_items');
	$result = $db->loadObject($this);
	if (PEAR::isError($result))
	    return $result;
    }


    function setId($id)
    {
	$this->id = (int)$id;
    }

    function getId()
    {
	return $this->id;
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

    function getSubject($format=TRUE)
    {
	if ($format) {
	    return PHPWS_Text::parseOutput($this->subject);
	} else {
	    return $this->subject;
	}
    }

    function setEntry($entry)
    {
	$this->entry = PHPWS_Text::parseInput($entry);
    }

    function getEntry($format=TRUE, $quoted=FALSE)
    {
	if ($format) {
	    $entry =  PHPWS_Text::parseOutput($this->entry);
	} else {
	    $entry =  $this->entry;
	}

        if ($quoted) {
            return sprintf('[quote="%s"]%s[/quote]', $this->getAuthorName(), trim($entry));
        } else {
            return $entry;
        }
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
	$this->create_time = gmmktime();
    }

    function getCreateTime($format=TRUE)
    {
	if ($format) {
	    return gmstrftime(COMMENT_DATE_FORMAT, $this->create_time);
	} else {
	    return $this->create_time;
	}
    }

    function getRelativeTime()
    {
        return PHPWS_Time::relativeTime($this->create_time, COMMENT_DATE_FORMAT);
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
		return NULL;
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

    function getAuthorName()
    {
        static $author_list;
        $author_id = &$this->author_id;
        if (!$author_id) {
            return _('Anonymous');
        } else {
            if (!isset($author_list[$author_id])) {
                $user = & new PHPWS_User($author_id);
                if (empty($user->display_name)) {
                    $author_list[$author_id] = _('Unknown');
                    return _('Unknown');

                }
                $author_list[$author_id] = $user->display_name;
                return $user->display_name;
            } else {
                return $author_list[$author_id];
            }
        }
    }

    function getError()
    {
	return $this->_error;
    }

    function getTpl()
    {
        translate('comments');

	if (!empty($GLOBALS['Comment_Users'])) {
            $author = @$GLOBALS['Comment_Users'][$this->author_id];
        }
        
        if (empty($author)) {
            $author = & new Comment_User($this->author_id);
        }

	$author_info = $author->getTpl();

	$template['SUBJECT_LABEL'] = _('Subject');
	$template['ENTRY_LABEL']   = _('Comment');
	$template['AUTHOR_LABEL']  = _('Author');
	$template['POSTED_BY']	   = _('Posted by');
	$template['POSTED_ON']	   = _('Posted on');

	$template['SUBJECT']	     = $this->getSubject(TRUE);
	$template['ENTRY']	     = $this->getEntry(TRUE);
	$template['CREATE_TIME']     = $this->getCreateTime();
        $template['RELATIVE_CREATE'] = $this->getRelativeTime(TRUE);
	$template['REPLY_LINK']	     = $this->replyLink();
	$template['EDIT_LINK']	     = $this->editLink();
	$template['DELETE_LINK']     = $this->deleteLink();
	$template['VIEW_LINK']	     = $this->viewLink();

        if ($this->parent) {
            $template['RESPONSE_LABEL']  = _('In response to');
            $template['RESPONSE_NUMBER'] = $this->responseNumber();
            $template['RESPONSE_NAME']   = $this->responseAuthor();
        }

	if ($this->edit_time) {
	    $template['EDIT_LABEL']	   = _('Edited');
	    $template['EDIT_AUTHOR']	   = $this->getEditAuthor();
	    $template['EDIT_AUTHOR_LABEL'] = _('Edited by');
	    $template['EDIT_TIME_LABEL']   = _('Edited on');
	    $template['EDIT_TIME']	   = $this->getEditTime();
	    if (!empty($this->edit_reason)) {
		$template['EDIT_REASON']       = $this->getEditReason();
		$template['EDIT_REASON_LABEL'] = _('Reason');
	    } else {
                $template['EDIT_REASON'] = NULL;
            }
	} else {
            $template['EDIT_TIME'] = NULL;
            $template['EDIT_REASON'] = NULL;
            $template['EDIT_AUTHOR'] = NULL;
        }

	if (Current_User::allow('comments')) {
	    $template['IP_ADDRESS'] = $this->getIp();
	}
	$template = array_merge($author_info, $template);
        translate();
	return $template;
    }

    function save()
    {
	if (empty($this->thread_id) ||
	    empty($this->subject)   ||
	    empty($this->entry)) {
	    return PHPWS_Error::get(COMMENTS_MISSING_THREAD, 'comments', 'Comment_Item::save');
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

	$db = & new PHPWS_DB('comments_items');
	$result = $db->saveObject($this);
	if (!PEAR::isError($result) && $increase_count) {
	    $thread = & new Comment_Thread($this->thread_id);
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
	    $vars['cm_id']	   = $this->getId();
	    return PHPWS_Text::moduleLink(_('Edit'), 'comments', $vars);
	} else {
	    return NULL;
	}
    }

    function deleteLink()
    {
	if (Current_User::allow('comments', 'delete_comments')) {
	    $vars['QUESTION'] = _('Are you sure you want to delete this comment?');
	    $vars['ADDRESS'] = 'index.php?module=comments&amp;cm_id=' . $this->getId() . '&amp;admin_action=delete_comment&amp;authkey='
		. Current_User::getAuthKey();
	    $vars['LINK'] = _('Delete');
	    return Layout::getJavascript('confirm', $vars);
	} else {
	    return NULL;
	}

    }

    function replyLink()
    {
	$vars['user_action']   = 'post_comment';
	$vars['thread_id']     = $this->thread_id;
	$vars['cm_parent']     = $this->getId();

	return PHPWS_Text::moduleLink(_('Reply'), 'comments', $vars);
    }

    function viewLink()
    {
	$vars['user_action']   = 'view_comment';
	$vars['cm_id']	   = $this->id;

	return PHPWS_Text::moduleLink($this->id, 'comments', $vars);
    }

    function delete()
    {
        $thread = & new Comment_Thread($this->thread_id);
        $db = & new PHPWS_DB('comments_items');
        $db->addWhere('id', $this->id);
        $db->delete();

        $thread->decreaseCount();
        $thread->save();
    }

    function responseNumber()
    {
	$vars['user_action']   = 'view_comment';
	$vars['cm_id']	   = $this->parent;

	return PHPWS_Text::moduleLink($this->parent, 'comments', $vars);
    }

    function responseAuthor()
    {
        $comment = & new Comment_Item($this->parent);
	$vars['user_action']   = 'view_comment';
	$vars['cm_id']	       = $comment->id;
	return PHPWS_Text::moduleLink($comment->getAuthorName(), 'comments', $vars);
    }

}

?>