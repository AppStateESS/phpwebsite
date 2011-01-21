<?php
/**
 * This is the index file for the phpwsbb module.
 *
 * @version $Id: index.php,v 1.2 2008/10/08 17:11:19 adarkling Exp $
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @module Bulletin Board
 */
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

Layout::addStyle('phpwsbb');

// When was this user last on?
if (Current_User::isLogged()) {
    $db = new PHPWS_DB('phpwsbb_users');
    // If this is the first phpwsbb session activity...

    if (empty($_SESSION['phpwsbb_last_on'])) {
        // load last_on information
        $db->addWhere('user_id', Current_User::getId());
        $result = $db->select('row');
        if (PHPWS_Error::logIfError($result)) {
            Layout::add(PHPWS_Error::printError($result));
            return;
        }
        // if User doesn't have an activity entry yet.  Create one.
        if (empty($result)) {
            $db->reset();
            $db->addValue('user_id', Current_User::getId());
            $db->addValue('last_on', '0');
            $db->addValue('last_activity', time());
            $db->insert();
            $result['last_on'] = 0;
            $result['last_activity'] = time();
        }
        $db->reset();
        $session_lifetime = ini_get('session.gc_maxlifetime');
        $since = time() - $session_lifetime;
        // if the last activity was in a previous session, update last_on

        if ($result['last_activity'] < $since) {
            $db->addValue('last_on', $result['last_activity']);
            $_SESSION['phpwsbb_last_on'] = $result['last_activity'];
        }
        else {
            $_SESSION['phpwsbb_last_on'] = $result['last_on'];
        }
    }
    // otherwise, just update the last_activity
    $db->addValue('last_activity', time());
    $db->addWhere('user_id', Current_User::getId());
    $result = $db->select('row');
    $db->update();
}

PHPWS_Core::initModClass('phpwsbb', 'BB_Data.php');
PHPWSBB_Data::load_moderators();
PHPWS_Core::initModClass('phpwsbb', 'Forum.php');
PHPWS_Core::initModClass('phpwsbb', 'Topic.php');
PHPWS_Core::initModClass('comments', 'Comments.php');
$msg_noauth = dgettext('phpwsbb', "You're not allowed to do this!");

/* Process any form button submissions */
/* Button format is BB_vars[tab:command<::key:value><::key:value>...] */
if (isset($_REQUEST['BB_vars']))
{
    $key = array_keys($_REQUEST['BB_vars']);
    $arr = explode('::', $key[0]);
    foreach($arr AS $value) {
        $req = explode(':', $value);
        $_REQUEST[$req[0]] = $req[1];
    }
    unset($_REQUEST['BB_vars']);
}

// Basic view public function request
if (!empty($_GET['view'])) {
    switch ($_GET['view']) {
        case 'topic':
            $topic = new PHPWSBB_Topic((int) $_GET['id']);
            if ($topic->id) {
                $title = $topic->get_title();
                $content = $topic->view();
            }
            else {
                $message = dgettext('phpwsbb', "This topic doesn't exist.  Please check the address you entered.");
            }
            break;

        case 'forum':
            $forum = new PHPWSBB_Forum((int) $_GET['id']);
            if ($forum->id) {
                $title = $forum->get_title();
                $content = $forum->view();
            }
            else {
                $message = dgettext('phpwsbb', "This forum doesn't exist.  Please check the address you entered.");
                $content = '<br />';
            }
            break;
    }
}


/* User actions */
elseif (!empty($_REQUEST['op'])) {
    //If Topic is requested, pre-load it
    if (!empty($_REQUEST['topic'])) {
        $topic = new PHPWSBB_Topic((int) $_REQUEST['topic']);
        $forum = $topic->get_forum();
    }
    //If Forum is requested, pre-load it
    elseif (!empty($_REQUEST['forum'])) {
        // Make a reference to this $GLOBAL
        $GLOBALS['BBForums'][(int) $_REQUEST['forum']] = new PHPWSBB_Forum((int) $_REQUEST['forum']);
        $forum = & $GLOBALS['BBForums'][(int) $_REQUEST['forum']];
    }

    switch ($_REQUEST['op']) {
        case 'create_topic':
            $topic = new PHPWSBB_Topic();
            $topic->fid = $forum->id;
            $title = sprintf(dgettext('phpwsbb', 'Create a New Topic in Forum "%s"'), $forum->get_title());
            $content = $topic->edit();
            break;

        case 'save_topic':
            // Make sure that we can save this topic
            if (empty($forum) || !$forum->can_post()) {
                $message = dgettext('phpwsbb', 'You are not authorized to save topics in this forum.');
                Security::log($GLOBALS['BB_message']);
                break;
            }
            $topic = new PHPWSBB_Topic();
            if ($topic->create($forum->id, @$_POST['cm_subject'], @$_POST['cm_entry']) !== true) {
                $message = $topic->_error;
                if ($topic->id) {
                $title = sprintf(dgettext('phpwsbb', 'Editing Topic "%s"'), $topic->get_title());
                }
                else {
                	$title = sprintf(dgettext('phpwsbb', 'Create a New Topic in Forum "%s"'), $forum->get_title());
                }
                $content = $topic->edit();
                break;
            }
            $title = $topic->get_title();
            $content = $topic->view();
            $_SESSION['DBPager_Last_View']['comments_items'] = 'index.php?module=phpwsbb&amp;view=topic&amp;id='.$topic->id;
            unset($message);
            break;

        case 'getnew':
            if (!empty($_SESSION['phpwsbb_last_on'])) {
                $since = $_SESSION['phpwsbb_last_on'];
            } else {
                $since = time();
            }

            $title = sprintf(dgettext('phpwsbb', 'New Posts Since My Last Visit (%s)'), PHPWSBB_Data::get_long_date($since));
            PHPWS_Core::initModClass('phpwsbb', 'BB_Lists.php');
            $content = PHPWSBB_Lists::search_threads('since', $since);
            Layout::addPageTitle($title);
            break;

        case 'viewtoday':
            $since = strtotime('00:00 today');
            $title = dgettext('phpwsbb', 'Today\'s Posts');
            PHPWS_Core::initModClass('phpwsbb', 'BB_Lists.php');
            $content = PHPWSBB_Lists::search_threads('since', $since);
            Layout::addPageTitle($title);
            break;

        case 'viewweek':
            $since = strtotime('last monday');
            $title = dgettext('phpwsbb', 'This Week\'s Posts');
            PHPWS_Core::initModClass('phpwsbb', 'BB_Lists.php');
            $content = PHPWSBB_Lists::search_threads('since', $since);
            Layout::addPageTitle($title);
            break;

        case 'viewzerothreads':
            $title = dgettext('phpwsbb', 'Threads with no replies');
            PHPWS_Core::initModClass('phpwsbb', 'BB_Lists.php');
            $content = PHPWSBB_Lists::search_threads('zerothreads');
            Layout::addPageTitle($title);
            break;

        case 'viewuserthreads':
            PHPWS_Core::initModClass('phpwsbb', 'BB_Lists.php');
            if (isset($_REQUEST['user'])) {
                $title = sprintf(dgettext('phpwsbb', 'Topics started by %s'), $_REQUEST['username']);
                $content = PHPWSBB_Lists::search_threads('userthreads', (int) $_REQUEST['user']);
            } else {
                $title = dgettext('phpwsbb', 'My Topics');
                $content = PHPWSBB_Lists::search_threads('userthreads', Current_User::getId());
            }
            Layout::addPageTitle($title);
            break;

        case 'viewlockedthreads':
            $title = dgettext('phpwsbb', 'Locked Threads');
            PHPWS_Core::initModClass('phpwsbb', 'BB_Lists.php');
            $content = PHPWSBB_Lists::search_threads('lockedthreads');
            Layout::addPageTitle($title);
            break;

            /*
             case 'fill_topic':  // For diagnostic use only! Creates test comments in a topic
             // Make sure that we can save this topic
             if (empty($topic) || empty($forum) || !$forum->can_post()) {
             $message = dgettext('phpwsbb', 'You are not authorized to post here.');
             Security::log($GLOBALS['BB_message']);
             break;
             }
             // Load the topic's comment list
             include(PHPWS_SOURCE_DIR . 'mod/phpwsbb/inc/'. $_REQUEST['template'] .'.php');
             if (empty($comment_list)) {
             $message = 'Invalid Template File.';
             break;
             }
             // Load a list of all user ids
             $db = new PHPWS_DB('users');
             $db->addColumn('id');
             $list = $db->select('col');
             PHPWS_Error::logIfError($list);
             $listsize = count($list) - 1;
             // Create all requested comments
             foreach ($comment_list AS $comment) {
             // If non is specified, pick a random user as author
             if (!$comment['author_id'] && empty($comment['anon_name']))
             $comment['author_id'] = $list[rand(0, $listsize)];
             PHPWSBB_Data::create_comment($topic->id, $comment['subject'], $comment['entry'], $comment['author_id'], $comment['anon_name']);
             sleep(1);
             }
             // reload & show the topic
             $topic = new PHPWSBB_Topic($topic->id);
             $title = $topic->get_title();
             $content = $topic->view();
             break;
             */
        default:
            // If none of these actions were requested & user is an admin..
            if (Current_User::authorized('comments')) {
                include(PHPWS_SOURCE_DIR . 'mod/phpwsbb/index_admin.php');
            }
    }
}


/* If nothing else, show the top menu */
if (empty($content)) {
    $title = dgettext('phpwsbb', 'Bulletin Board Forums');
    PHPWS_Core::initModClass('phpwsbb', 'BB_Lists.php');
    $content = PHPWSBB_Lists::list_forums();
}

/* Show the MiniAdmin */
PHPWSBB_Data::MiniAdmin();
if (!empty($topic))
$topic->MiniAdmin();
if (!empty($forum))
$forum->MiniAdmin();

/* Show generated content */
if (!empty($title))
$template['TITLE']   = $title;
if (!empty($message))
$template['MESSAGE'] = $message;
if (!empty($content))
$template['CONTENT'] = $content;
$content = PHPWS_Template::process($template, 'phpwsbb', 'main.tpl');
// Release module vars
unset($topic, $forum, $title, $message, $template,$GLOBALS['Moderators_byForum'], $GLOBALS['Moderators_byUser'],
$GLOBALS['BBForumTags'], $GLOBALS['BB_errors'], $GLOBALS['BB_message'], $GLOBALS['BBForums']);
// Release Comment-based GLOBALS
unset($thread,$GLOBALS['Comment_Users'],$GLOBALS['Comment_UsersGroups'],$GLOBALS['cm_threads']);

Layout::add($content);
unset($content);

?>
