<?php
/**
* This is the index file for the phpwsbb module.
*
* @version $Id: index_admin.php,v 1.1 2008/08/23 04:19:25 adarkling Exp $
*
* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
* @module Bulletin Board
*/
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}


// Admin Forum Actions
if (Current_User::authorized('phpwsbb'))
switch ($_REQUEST['op']) {
	case 'create_forum': 
		$forum = & new PHPWSBB_Forum();
		$title = dgettext('phpwsbb', 'Create a New Forum');
		$content = $forum->edit();
	break;

	case 'edit_forum': 
		if (empty($forum))
			$forum = & new PHPWSBB_Forum();
		$forum->update();
		$forum->update_forum();
		$forum->save();
		if (isset($GLOBALS['BB_message']))
			$message = $GLOBALS['BB_message'];
		$title = sprintf(dgettext('phpwsbb', 'Editing Forum "%s"'), $forum->get_title());
		$content = $forum->edit();
	break;
	
	case 'show_forum': 
	    $forum->active = 1;
	    if ($forum->save())
	       $message = sprintf(dgettext('phpwsbb', 'Forum "%s" is now visible'), $forum->get_title());
        $title = $forum->get_title();
        $content = $forum->view();
   break;
	
	case 'hide_forum': 
        $forum->active = 0;
        if ($forum->save())
           $message = sprintf(dgettext('phpwsbb', 'Forum "%s" is now hidden'), $forum->get_title());
        $title = $forum->get_title();
        $content = $forum->view();
   break;
	
	case 'delete_forum': 
        if(!Current_User::allow('phpwsbb', 'manage_forums')) 
        	break;
        if(isset($_REQUEST['yes'])) {
			// Call up a list of all topics in this forum.
			$db = new PHPWS_DB('phpwsbb_topics');
			$db->addColumn('id');
			$db->addWhere('fid', $_REQUEST['forum']);
			$ids = $db->select('col');
		    if (PHPWS_Error::logIfError($ids)) 
			    $message = PHPWS_Error::printError($id_list);
			// If there is a list...
		    if (!empty($ids)) 
			    foreach ($ids AS $recid) {
					$obj = & new PHPWSBB_Topic($recid);
				    $obj->delete();
			    }
			// Delete the Forum
		    if (empty($message)) {
				// Delete the forum record & Key id
				$db = & new PHPWS_DB('phpwsbb_forums');
				$db->addWhere('id', $_REQUEST['forum']);
		        $result = $db->delete();
		        if (PHPWS_Error::logIfError($result)) 
		            $msg[] = PHPWS_Error::printError($result);
		    	$message = dgettext('phpwsbb', 'Forum %s and all attatched topics have been deleted');
		    }
        } elseif(isset($_REQUEST['no'])) {
	 		$title = $forum->get_title();
			$content = $forum->view();
        } else {
        	$address = 'index.php?module=phpwsbb&amp;op=delete_forum&amp;forum='.$forum->id;
			$title = dgettext('phpwsbb', 'Delete Confirmation') .' -- '. $forum->get_title();
        	$question = dgettext('phpwsbb', 'This will delete the forum and all topics under it!  Are you sure you want to delete this?');
			PHPWS_Core::initModClass('phpwsbb', 'BB_Forms.php');
        	$content = PHPWSBB_Forms::show_dialog($address, $title, $question);
        }
	break;
	
	
	// Admin Thread Operations
	case 'stick_topic':
	   	$topic->sticky = 1;
		$message = $topic->set_thread('sticky_threads', dgettext('phpwsbb', 'The topic will now stick to the top of lists'));
		$title = $topic->get_title();
		$content = $topic->view();
	break;

	case 'unstick_topic':
	   	$topic->sticky = 0;
		$message = $topic->set_thread('sticky_threads', dgettext('phpwsbb', 'The topic is now unstuck from the top of lists'));
		$title = $topic->get_title();
		$content = $topic->view();
	break;

	case 'move_topic':
	    if (!empty($forum) && !$forum->userCan('phpwsbb', 'move_threads')) {
			$message = $msg_noauth;
		 	break;
		}
		if (empty($topic)) {
    		if (!empty($_REQUEST['key_id'])) {
    	    	$key = new Key((int) $_REQUEST['key_id']);
    	    }
    	    else {
    	    	$content = dgettext('phpwsbb', 'phpwsBB::assign_forum is missing some required parameters');
    	    	break;
    	    }
	    }
		$is_popup = !empty($_REQUEST['popup']);

	   	// Show dialog if destination forum hasn't been picked yet
	   	if (!isset($_REQUEST['new_forum'])) {
            // Show the assignment form
            PHPWS_Core::initModClass('phpwsbb', 'BB_Forms.php');
            if (!empty($topic)) 
                $content = PHPWSBB_Forms::assign_forum($topic, $is_popup);
            else
                $content = PHPWSBB_Forms::assign_forum($key, $is_popup);
            if ($is_popup) 
                Layout::nakedDisplay($content);
            break;
	   	}

        $newforum = new PHPWSBB_Forum((int) $_REQUEST['new_forum']);
	   	// If user doesn't have access to the receiving forum, exit
	   	if (!$newforum->id) {
			$message = dgettext('phpwsbb', "You can't move a topic to a forum that you don't have access to!");
	   	}
		// If we're moving a topic...
		if (!empty($topic)) {
		    $topic->fid = $newforum->id;
		    $topic->commit(false);
			$message = sprintf(dgettext('phpwsbb', 'You have just moved the topic to forum "%s"'), $newforum->get_title());
		}
		// otherwise, we're importing a key...
		else {
		    // get thread's id#
            $db = & new PHPWS_DB('comments_threads');
            $db->addColumn('id');
            $db->addWhere('key_id', $key->id);
            $result = $db->select('one');
            $db->reset();
            if (!$result) 
                $message = dgettext('phpwsbb', 'ERROR: Thread not found');
            elseif (PHPWS_Error::logIfError($result)) 
                $message = PHPWS_Error::printError($result);
            else {
                // insert the new topic
                $topic = & new PHPWSBB_Topic();
                $topic->id = $result;
                $topic->fid = $newforum->id;
                $topic->key_id = $key->id;
                $topic->is_phpwsbb = 0;
                $topic->title = $key->title;
                $topic->update_topic();
                if (!$topic->commit(true))
                   $message = $topic->_error;
                else
                   $message = dgettext('phpwsbb', 'Import Complete');
                unset($key);
            }
		}
		// Update the old forum's lastpost summaries
		if (!empty($forum)) 
			$forum->update_forum(true);
		$forum = $newforum;
		unset($newforum);
		$title = $topic->get_title();
		if ($is_popup) {
			$template['MESSAGE'] = $message;
			$url = 'index.php?module=phpwsbb&amp;view=topic&amp;id='.$topic->id;
	        $template['CONTENT'] = sprintf('<input type="button" value="%s" onclick="opener.location.href=\'%s\'; window.close();" style="text-align: center" />',
                                     dgettext('categories', 'Close Window'), $url);
	        Layout::nakedDisplay(PHPWS_Template::process($template, 'phpwsbb', 'main.tpl')); // auto-exit
		}
		$content = $topic->view();
		$_SESSION['DBPager_Last_View']['comments_items'] = 'index.php?module=phpwsbb&amp;view=topic&amp;id='.$topic->id;
	break;
	
	case 'delete_topic':
	   	if (!$forum->userCan('phpwsbb', 'delete_threads')) {
			$message = $msg_noauth;
		 	break;
		}
        if(isset($_REQUEST['yes'])) {
			$result = $topic->delete();
			if (PHPWS_Error::logIfError($result)) {
				$message = dgettext('phpwsbb', 'The topic could not be deleted.');
				$title = $topic->get_title();
				$content = $topic->view();
				$_SESSION['DBPager_Last_View']['comments_items'] = 'index.php?module=phpwsbb&amp;view=topic&amp;id='.$topic->id;
			}
			else {
				unset($topic);
				$forum->update_forum(true);
				$title = $forum->get_title();
				$message = dgettext('phpwsbb', 'The topic was sucessfully deleted.');
				$content = $forum->view();
			}
        } elseif(isset($_REQUEST['no'])) {
	 		$title = $topic->get_title();
			$content = $topic->view();
        } else {
        	$address = 'index.php?module=phpwsbb&amp;op=delete_topic&amp;topic='.$topic->id;
			$title = dgettext('phpwsbb', 'Delete Confirmation') .' -- '. $topic->get_title();
        	$question = dgettext('phpwsbb', 'This will delete the topic and all messages under it!  Are you sure you want to delete this?');
			PHPWS_Core::initModClass('phpwsbb', 'BB_Forms.php');
        	$content = PHPWSBB_Forms::show_dialog($address, $title, $question);
        }
	break;

	case 'drop_topic':
	   	if (!$forum->userCan('phpwsbb', 'delete_threads')) {
			$message = $msg_noauth;
		 	break;
		}
	   	if (!$topic->drop()) {
			$message = dgettext('phpwsbb', 'The requested operation did not work');
	 		$title = $topic->get_title();
			$content = $topic->view();
			$_SESSION['DBPager_Last_View']['comments_items'] = 'index.php?module=phpwsbb&amp;view=topic&amp;id='.$topic->id;
			break;
		}
		$forum->update_forum(true);
		$message = dgettext('phpwsbb', 'The topic has been dropped from this forum');
		$title = $forum->get_title();
		$content = $forum->view();
	break;

	case 'show_topic':
		$topic->active = 1;
		$message = $topic->set_thread('hide_threads', sprintf(dgettext('phpwsbb', 'Topic "%s" is now visible'), $topic->title), true);
		$title = $topic->get_title();
		$content = $topic->view();
	break;

	case 'hide_topic':
		$topic->active = 0;
		$message = $topic->set_thread('hide_threads', sprintf(dgettext('phpwsbb', 'Topic "%s" is now hidden'), $topic->title), true);
        $title = $topic->get_title();
        $content = $topic->view();
	break;

	case 'split_topic':
		// Check for required variables
		if (empty($_REQUEST['topic']) || empty($_REQUEST['split_point'])) {
			 $content = dgettext('phpwsbb', 'Some required information is missing.');
			 break;
		}
		// Get list of all affected comment ids
        $db = new PHPWS_DB('comments_items');
        $db->addColumn('id');
        $db->addWhere('thread_id', (int) $_REQUEST['topic']);
        $db->addWhere('create_time', (int) $_REQUEST['split_point'], '>=');
        $id_list = $db->select('col');
		if (PHPWS_Error::logIfError($id_list)) 
		 	break;
		// if is_phpwsbb & this would get rid of all comments, abort
		if (!empty($topic->id) && $topic->total_posts <= count($id_list)) {
			$message = dgettext('phpwsbb', 'You cannot erase all comments in this topic.');
			$title = $topic->get_title();
			$content = $topic->view();
		}
		// otherwise, show PHPWSBB_Forms::split_comments() 
		else {
			PHPWS_Core::initModClass('phpwsbb', 'BB_Forms.php');
			$content = PHPWSBB_Forms::split_comments($id_list);
		}
	break;


	// Manager Operations
	case 'update_all_threads':
		if (!Current_User::allow('phpwsbb', 'manage_forums')) {
			$message = $msg_noauth;
			break;
		}
		// Call up update information on all threads.
		$sql = '
SELECT phpwsbb_topics.id AS topic_id, 
	comments_items.id, 
	comments_items.create_time, 
	comments_items.author_id, 
	comments_items.anon_name, 
	comments_threads.total_comments, 
	users.username 
FROM phpwsbb_topics
	LEFT JOIN comments_threads ON comments_threads.id = phpwsbb_topics.id
	LEFT JOIN comments_items ON comments_items.id = 
		(SELECT MAX(id) FROM comments_items WHERE comments_items.thread_id = comments_threads.id)
	LEFT JOIN users ON users.id = comments_items.author_id
GROUP BY phpwsbb_topics.id
ORDER BY comments_items.create_time desc
';
		$db = & new PHPWS_DB('phpwsbb_topics');
		$result = $db->select(null, $sql);
	    if (PHPWS_Error::logIfError($result)) 
		    $message = PHPWS_Error::printError($result);
		elseif (empty($result))
			$message = dgettext('phpwsbb', 'No threads were found!');
		else {
			foreach ($result AS $row) {
				$db->reset();
				$db->addValue('total_posts', $row['total_comments']);
				if ($row['total_comments'] > 0) {
					$db->addValue('lastpost_post_id', $row['id']);
					$db->addValue('lastpost_date', $row['create_time']);
					if ($row['author_id']) {
						$db->addValue('lastpost_author_id', $row['author_id']);
						$db->addValue('lastpost_author', $row['username']);
					}
					else {
						$db->addValue('lastpost_author_id', 0);
						$db->addValue('lastpost_author', $row['anon_name']);
					}
				}
				else {
					$db->addValue('lastpost_post_id', 0);
					$db->addValue('lastpost_date', 0);
					$db->addValue('lastpost_author_id', 0);
					$db->addValue('lastpost_author', '');
				}
				$db->addWhere('id', $row['topic_id']);
				$db->update();
			}
			$message = dgettext('phpwsbb', 'All threads have been updated');
		}
		$title = dgettext('phpwsbb', 'Updating All Topics');

	case 'update_all_forums':
		if (!Current_User::allow('phpwsbb', 'manage_forums')) {
			$message = $msg_noauth;
			break;
		}
		$db = & new PHPWS_DB('phpwsbb_topics');
		$sql = 'SELECT fid,COUNT(total_posts) AS total_topics, SUM(total_posts) AS total_topic_posts FROM phpwsbb_topics GROUP BY fid';
		$count_info = $db->select(null, $sql);
	    if (PHPWS_Error::logIfError($count_info)) 
			$message = PHPWS_Error::printError($count_info);
		elseif (empty($count_info))
			$message = dgettext('phpwsbb', 'Not all thread summary information was found!');
		else {
			foreach ($count_info AS $key=>$row) {
				$db->reset();
				$db->setTable('phpwsbb_forums');
				$db->addValue('topics', $row['total_topics']);
				$db->addValue('posts', $row['total_topic_posts']);
				$db->addWhere('id', $row['fid']);
				$db->update();
			}
			$message = dgettext('phpwsbb', 'All forums have been updated');
		}
		$title = dgettext('phpwsbb', 'Updating All Forums');
	break;
	
	case 'config':
        if (!Current_User::allow('phpwsbb', 'manage_forums')) {
            $message = $msg_noauth;
            break;
        }
	    PHPWS_Core::initModClass('phpwsbb', 'BB_Forms.php');
	    $template['TITLE'] = dgettext('phpwsbb', 'PHPWSBB Settings');
	    if (!empty($_REQUEST['reset'])) {
            include(PHPWS_SOURCE_DIR . 'mod/phpwsbb/inc/settings.php');
            PHPWS_Settings::set('phpwsbb', $settings);
            PHPWS_Settings::save('phpwsbb');
            $template['MESSAGE'] = dgettext('phpwsbb', 'Your settings have been reset to the factory defaults.');
	    }
	    elseif (!empty($_REQUEST['save'])) {
		    $settings = array();
			$settings['allow_anon_posts'] = (bool) !empty($_POST['allow_anon_posts']);
			$settings['showforumsblock'] = (bool) !empty($_POST['showforumsblock']);
			$settings['showlatestpostsblock'] = (bool) !empty($_POST['showlatestpostsblock']);
			$settings['maxlatesttopics'] = (int) $_POST['maxlatesttopics'];
			$settings['use_views'] = (bool) !empty($_POST['use_views']);
			$settings['long_date_format'] = $_POST['long_date_format'];
			$settings['short_date_format'] = $_POST['short_date_format'];
			PHPWS_Settings::set('phpwsbb', $settings);
			PHPWS_Settings::save('phpwsbb');
			$template['MESSAGE'] = dgettext('phpwsbb', 'Your settings have been saved.');
	    }
        $template['CONTENT'] = PHPWSBB_Forms::edit_configuration();
        $content = PHPWS_ControlPanel::display(PHPWS_Template::process($template, 'phpwsbb', 'main.tpl'), 'admin');
        $title = $message = '';
        unset($template);
        return;
    break;


	case 'split_comments':
		// Check incoming vars
		if (empty($_REQUEST['new_forum']) || empty($_POST['cm_subject'])) {
			 $content = dgettext('phpwsbb', 'split_comments: Some required information is missing.');
			 break;
		}
		$make_new_topic = true;
	case 'move_comments':
		// Check incoming vars & topic existence
		if (empty($_REQUEST['oldthread']) || empty($_REQUEST['comment_ids'])) {
			 $content = dgettext('phpwsbb', 'move_comments: Some required information is missing.');
			 break;
		}
		// Check permission to move/split comments
        $oldthread = new Comment_Thread($_REQUEST['oldthread']);
	   	if (!$oldthread->userCan('delete_comments')) {
			$content = dgettext('phpwsbb', "You're not authorized to do this!");
		 	break;
		}
		// If we have to create a new topic, do it now
		if (isset($make_new_topic)) {
			// Check receiving Forum permission
			$forum = new PHPWSBB_Forum((int) $_REQUEST['new_forum']);
		   	if (!$forum->can_post()) {
				$content = dgettext('phpwsbb', "You can't create a topic in a forum that you don't have access to!");
			 	break;
			}
			// Create a new topic 
			$topic = & new PHPWSBB_Topic();
			$topic->is_phpwsbb = $oldthread->id;
		    $topic->title = strip_tags(trim($_POST['cm_subject']));
            $topic->summary = $oldthread->_key->summary;
			// If there's a problem, show the split_comments form
			if (!$topic->create($forum->id)) {
				$message = $topic->_error;
				PHPWS_Core::initModClass('phpwsbb', 'BB_Forms.php');
				$content = PHPWSBB_Forms::split_comments($comments);
				break;
			}
		}
		// otherwise, make sure that a topic & forum were specified
		elseif (empty($topic) || empty($forum)) {
            $content = dgettext('phpwsbb', "You don't have permission to post to this topic!");
            break;
		}
        // otherwise, check Comment Thread permission
		else {
            PHPWS_Core::initModClass('comments', 'Comments.php');
            $thread = new Comment_Thread($topic->id); 
            if (!$thread->canComment()) {
                $content = dgettext('phpwsbb', "You don't have permission to post to this topic!");
                break;
            }
            unset($thread);
        }
		// Move comments
        $db = & new PHPWS_DB('comments_items');
		$db->addValue('thread_id', $topic->id);
		$db->addWhere('id', explode(',', $_REQUEST['comment_ids']));
		$db->addWhere('thread_id', $oldthread->id);
		if (PHPWS_Error::logIfError($db->update())) {
			$content = dgettext('phpwsbb', 'Could not move comments to the new topic');
		 	break;
		}
		// Update the new thread's stats
		$db = & new PHPWS_DB('comments_threads');
		$sql = 'UPDATE comments_threads SET total_comments = (SELECT COUNT(id) FROM comments_items WHERE thread_id = comments_threads.id) WHERE id = '.$topic->id;
		if (PHPWS_Error::logIfError($db->query($sql))) {
			$content = dgettext('phpwsbb', 'Could not update comment count in table "comments_threads"');
		 	break;
		}
		$topic->update_topic();
		$topic->commit();
		$oldthread_id = (int) $_REQUEST['oldthread'];
		// If requested, create a notification comment in the old thread
		if (isset($_REQUEST['leave_notice'])) {
			$c_item = new Comment_Item;
			$c_item->setThreadId($oldthread_id);
			$c_item->setSubject('This thread has been modified');
			$str = dgettext('phpwsbb', 'Some comments have been moved to a related topic.  You can view it by <a href="./index.php?module=phpwsbb&amp;view=topic&amp;id='.$topic->id.'">clicking here.</a>');
			$c_item->entry = PHPWS_Text::parseInput($str);
			$c_item->save();
		}
		// Update the old thread's stats
		$sql = 'UPDATE comments_threads SET total_comments = (SELECT COUNT(id) FROM comments_items WHERE thread_id = comments_threads.id) WHERE id = '.$oldthread_id;
		if (PHPWS_Error::logIfError($db->query($sql))) {
			$content = dgettext('phpwsbb', 'Could not update comment count in table "comments_threads"');
		 	break;
		}
		// If there's an old topic, update its stats
		$oldtopic = & new PHPWSBB_Topic($oldthread_id);
		if ($oldtopic->id) {
            $oldtopic->update_topic();
            $oldtopic->commit();
		}
        $_SESSION['DBPager_Last_View']['comments_items'] = 'index.php?module=phpwsbb&amp;view=topic&amp;id='.$topic->id;
		PHPWS_Core::reroute('index.php?module=phpwsbb&amp;view=topic&amp;id='.$topic->id);
	break;
}

?>