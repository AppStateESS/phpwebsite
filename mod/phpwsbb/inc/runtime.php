<?php
/**
 * This is the runtime file for the phpwsbb module.
 *
 * Content is cached for the benefit of unregistered users
 *
 * @version $Id: runtime.php,v 1.1 2008/08/23 04:19:16 adarkling Exp $
 *
 * @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
 * @module phpwsBB
 */
if (isset($_REQUEST['module']))
return;
PHPWS_Core::initModClass('phpwsbb', 'BB_Data.php');
$forums = PHPWSBB_Data::get_forum_list();
$forum_ids = array_keys($forums);

/**
 * Display block with all active forums
 */
$list = array();
if (!isset($_REQUEST['module']) && PHPWS_Settings::get('phpwsbb', 'showforumsblock')) {
    if (!Current_User::isLogged()) {
        $cachekey = 'bb_forumsblock';
        $list = PHPWS_Cache::get($cachekey);
        if (!empty($list))
        $list = unserialize($list);
    }
    if (empty($list))  {
        $list = array();
        foreach($forums as $rowid => $row)
        $list[]['ITEM'] = PHPWS_Text::rewriteLink(PHPWS_Text::parseOutput($row), 'phpwsbb', array('view'=>'forum', 'id'=>$rowid));
        if (!Current_User::isLogged())
        PHPWS_Cache::save($cachekey, serialize($list), 86400);
    }
    if (!empty($list))  {
        $title = dgettext('phpwsbb', 'Message Boards');
        $finalContent = PHPWS_Template::process(array('TITLE'=>$title, 'listrows'=>$list), 'phpwsbb', 'forum_links.tpl');
        Layout::add($finalContent, 'phpwsbb', 'forumsblock');
    }
}


/**
 * Display block with recently changed threads in it
 */
$list = array();
if (PHPWS_Settings::get('phpwsbb', 'showlatestpostsblock')) {
    // Load all forum records
    if (!Current_User::isLogged()) {
        $cachekey = 'bb_latestpostsblock';
        $list = PHPWS_Cache::get($cachekey);
        if (!empty($list))
        $list = unserialize($list);
    }
    if (empty($list))  {
        PHPWS_Core::initModClass('phpwsbb', 'Topic.php');
        $db = & new PHPWS_DB('phpwsbb_topics');
        PHPWSBB_Topic::addColumns($db);
        Key::restrictView($db, 'phpwsbb');
        $db->addOrder('lastpost_date desc');
        $db->setLimit(PHPWS_Settings::get('phpwsbb', 'maxlatesttopics'));
        // What forums can we search in?
        $db->addWhere('fid', $forum_ids, 'IN');
        $result = $db->select();
        if (PHPWS_Error::logIfError($result))
        return;
        if (!empty($result)) {
            $list = array();
            foreach($result as $row) {
                $topic = new PHPWSBB_Topic($row);
                $list[]['ITEM'] = $topic->get_title_link();
            }
        }
        if (!Current_User::isLogged())
        PHPWS_Cache::save($cachekey, serialize($list), 86400);
    }
    if (!empty($list))  {
        $title = dgettext('phpwsbb', 'Latest Forum Posts');
        $finalContent = PHPWS_Template::process(array('TITLE'=>$title, 'listrows'=>$list), 'phpwsbb', 'latest_posts.tpl');
        Layout::add($finalContent, 'phpwsbb', 'latestpostsblock');
    }
}


?>