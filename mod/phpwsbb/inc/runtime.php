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
Core\Core::initModClass('phpwsbb', 'BB_Data.php');
$forums = PHPWSBB_Data::get_forum_list();
$forum_ids = array_keys($forums);

/**
 * Display block with all active forums
 */
$list = array();
if (!isset($_REQUEST['module']) && Core\Settings::get('phpwsbb', 'showforumsblock')) {
    if (!Current_User::isLogged()) {
        $cachekey = 'bb_forumsblock';
        $list = Core\Cache::get($cachekey);
        if (!empty($list))
        $list = unserialize($list);
    }
    if (empty($list))  {
        $list = array();
        foreach($forums as $rowid => $row)
        $list[]['ITEM'] = Core\Text::rewriteLink(Core\Text::parseOutput($row), 'phpwsbb', array('view'=>'forum', 'id'=>$rowid));
        if (!Current_User::isLogged())
        Core\Cache::save($cachekey, serialize($list), 86400);
    }
    if (!empty($list))  {
        $title = dgettext('phpwsbb', 'Message Boards');
        $finalContent = Core\Template::process(array('TITLE'=>$title, 'listrows'=>$list), 'phpwsbb', 'forum_links.tpl');
        Layout::add($finalContent, 'phpwsbb', 'forumsblock');
    }
}


/**
 * Display block with recently changed threads in it
 */
$list = array();
if (Core\Settings::get('phpwsbb', 'showlatestpostsblock')) {
    // Load all forum records
    if (!Current_User::isLogged()) {
        $cachekey = 'bb_latestpostsblock';
        $list = Core\Cache::get($cachekey);
        if (!empty($list))
        $list = unserialize($list);
    }
    if (empty($list))  {
        Core\Core::initModClass('phpwsbb', 'Topic.php');
        $db = new Core\DB('phpwsbb_topics');
        PHPWSBB_Topic::addColumns($db);
        Core\Key::restrictView($db, 'phpwsbb');
        $db->addOrder('lastpost_date desc');
        $db->setLimit(Core\Settings::get('phpwsbb', 'maxlatesttopics'));
        // What forums can we search in?
        $db->addWhere('fid', $forum_ids, 'IN');
        $result = $db->select();
        if (Core\Error::logIfError($result))
        return;
        if (!empty($result)) {
            $list = array();
            foreach($result as $row) {
                $topic = new PHPWSBB_Topic($row);
                $list[]['ITEM'] = $topic->get_title_link();
            }
        }
        if (!Current_User::isLogged())
        Core\Cache::save($cachekey, serialize($list), 86400);
    }
    if (!empty($list))  {
        $title = dgettext('phpwsbb', 'Latest Forum Posts');
        $finalContent = Core\Template::process(array('TITLE'=>$title, 'listrows'=>$list), 'phpwsbb', 'latest_posts.tpl');
        Layout::add($finalContent, 'phpwsbb', 'latestpostsblock');
    }
}


?>