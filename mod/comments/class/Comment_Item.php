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

    // Number of times this comment has been reported
    // as needing review
    var $reported     = 0;

    // Approval status
    var $approved     = 1;

    var $author = null;

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

    function getTpl($allow_anon, $can_post=true)
    {
        $author = $this->getAuthor();
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
            if (!isset($_SESSION['Users_Reported_Comments'][$this->id])) {
                $template['REPORT_LINK'] = $this->reportLink();
            } else {
                $template['REPORT_LINK'] = dgettext('comments', 'Reported!');
            }
        }

        if ($can_post) {
            $template['EDIT_LINK']    = $this->editLink();
        }
        $template['DELETE_LINK']  = $this->deleteLink();
        $template['VIEW_LINK']    = $this->viewLink();
        $template['PUNISH_LINK']     = $this->punishUserLink();

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

	if (!$this->id) {
	    $this->stampIP();
	    $this->stampAuthor();
	    $increase_count = TRUE;
	} else {
	    $this->stampEditor();
	    $increase_count = FALSE;
        }

	$db = new PHPWS_DB('comments_items');
	$result = $db->saveObject($this);
	if (!PEAR::isError($result) && $increase_count && $this->approved) {
            PHPWS_Error::logIfError($this->stampThread());
	}
	return $result;
    }

    function stampThread()
    {
        $thread = new Comment_Thread($this->thread_id);
        $thread->increaseCount();
        $thread->postLastUser($this->author_id);
        return $thread->save();
    }

    function editLink()
    {
	if (Current_User::allow('comments') ||
	    ($this->author_id > 0 && $this->author_id == Current_User::getId())
	    ) {
	    $vars['uop']   = 'post_comment';
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
	    $vars['ADDRESS'] = 'index.php?module=comments&amp;cm_id=' . $this->id . '&amp;aop=delete_comment&amp;authkey='
		. Current_User::getAuthKey();
	    $vars['LINK'] = dgettext('comments', 'Delete');
	    return Layout::getJavascript('confirm', $vars);
	} else {
	    return null;
	}

    }

    function clearReportLink()
    {
        return PHPWS_Text::secureLink(dgettext('comments', 'Clear'), 'comments',
                                      array('aop'=>'clear_report', 'cm_id'=>$this->id));
    }

    function punishUserLink($graphic=false)
    {
        if (Current_User::allow('comments', 'punish_users')) {
            $vars['address'] = PHPWS_Text::linkAddress('comments', array('aop'=>'punish_user',
                                                                         'cm_id'=>$this->id), true);
            if ($graphic) {
                $vars['label'] = sprintf('<img src="images/mod/comments/noentry.png" width="20" height="20" title="%s" alt="%s"/>',
                                         dgettext('comments', 'Punish poster'), dgettext('comments', 'Punish icon'));
            } else {
                $vars['label'] = dgettext('comments', 'Punish');
            }
            $vars['width'] = 240;
            $vars['height'] = 180;
            return javascript('open_window', $vars);
        } else {
            return null;
        }
    }

    function quoteLink()
    {
	$vars['uop']   = 'post_comment';
	$vars['thread_id']     = $this->thread_id;
	$vars['cm_parent']     = $this->id;
	return PHPWS_Text::moduleLink(dgettext('comments', 'Quote'), 'comments', $vars);
    }

    function replyLink()
    {
	$vars['uop']   = 'post_comment';
	$vars['thread_id']     = $this->thread_id;
	return PHPWS_Text::moduleLink(dgettext('comments', 'Reply'), 'comments', $vars);
    }

    function reportLink()
    {
        return sprintf('<a href="#" onclick="report(%s, this); return false">%s</a>',
                       $this->id,
                       dgettext('comments', 'Report'));
    }

    function viewLink()
    {
	$vars['uop']   = 'view_comment';
	$vars['cm_id']	   = $this->id;

	return PHPWS_Text::moduleLink($this->subject, 'comments', $vars);
    }

    /**
     * Removes a comment from the database
     */
    function delete($reduce_count=true)
    {
        // physical removal
        $thread = new Comment_Thread($this->thread_id);
        $db = new PHPWS_DB('comments_items');
        $db->addWhere('id', $this->id);
        $db->delete();

        // clear replies to this comment
        $this->clearChildren();

        // decrease thread count
        if ($reduce_count) {
            $thread->decreaseCount();
            $thread->save();
        }
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
	$vars['uop'] = 'view_comment';
	$vars['cm_id']	     = $this->parent;

	return PHPWS_Text::moduleLink($this->parent, 'comments', $vars);
    }

    function responseAuthor()
    {
        $comment = new Comment_Item($this->parent);
	$vars['uop']   = 'view_comment';
	$vars['cm_id']	       = $comment->id;
	return PHPWS_Text::moduleLink($comment->getAuthorName(), 'comments', $vars);
    }

    function reportTags()
    {
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

    function approvalTags()
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

        $tpl['ACTION'] = implode('', $links);
        return $tpl;
    }

    function approve()
    {
        $this->approved = 1;
        $this->save();

        // Thread is not increased on save
        $this->stampThread();
    }

}

?>