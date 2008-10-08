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
if (isset($_REQUEST['module']) && $_REQUEST['module'] == 'controlpanel') 
	return;

/**
* Display block with all active forums
*/
$content = '';
if (PHPWS_Settings::get('phpwsbb', 'showforumsblock')) {
	if (!Current_User::isLogged()) {
		$cachekey = 'bb_forumsblock';
		$content = PHPWS_Cache::get($cachekey);
	}
	if (empty($content))  {
		PHPWS_Core::initModClass('phpwsbb', 'BB_Data.php');
		$forums = PHPWSBB_Data::get_forum_list();
	    if (!empty($forums)) {
		    $s = '';
		    foreach($forums as $rowid => $row) 
		        $s .= '<li>' . PHPWS_Text::rewriteLink(PHPWS_Text::parseOutput($row), 'phpwsbb', array('view'=>'forum', 'id'=>$rowid)) . "</li>\n";
			if (!empty($s))  
		    	$content = '<ul >'.$s.'</ul>';
		}
		if (!Current_User::isLogged()) 
			PHPWS_Cache::save($cachekey, $content, 86400);
	}
	if (!empty($content))  {
		$title = dgettext('phpwsbb', 'Message Boards');
		$finalContent = PHPWS_Template::process(array('TITLE'=>$title, 'CONTENT'=>$content), 'layout', 'box.tpl');
		Layout::add($finalContent, 'phpwsbb', 'forumsblock');
	}
}


/**
* Display block with recently changed threads in it
*/
$content = '';
if (PHPWS_Settings::get('phpwsbb', 'showlatestpostsblock')) {
	// Load all forum records
	if (!Current_User::isLogged()) {
		$cachekey = 'bb_latestpostsblock';
		$content = PHPWS_Cache::get($cachekey);
	}
	if (empty($content))  {
		PHPWS_Core::initModClass('phpwsbb', 'Topic.php');
		$db = & new PHPWS_DB('phpwsbb_topics');
		PHPWSBB_Topic::addColumns($db);
		Key::restrictView($db, 'phpwsbb');
		$db->addOrder('lastpost_date desc');
		$db->setLimit(PHPWS_Settings::get('phpwsbb', 'maxlatesttopics'));
		$result = $db->select();
	    if (PHPWS_Error::logIfError($result))
	    	return;
	    if (!empty($result)) {
	        $s = '';
	        foreach($result as $row) {
				$topic = new PHPWSBB_Topic($row);
				$s .= '<li>' . $topic->get_title_link() . "</li>\n";
	        }
			if (!empty($s))  
		    	$content = '<ul>'.$s.'</ul>';
	    }
		if (!Current_User::isLogged()) 
			PHPWS_Cache::save($cachekey, $content, 86400);
	}
	if (!empty($content))  {
		$title = dgettext('phpwsbb', 'Latest Forum Posts');
		$finalContent = PHPWS_Template::process(array('TITLE'=>$title, 'CONTENT'=>$content), 'layout', 'box.tpl');
		Layout::add($finalContent, 'phpwsbb', 'latestpostsblock');
	}
}

/**
* Display block with phpwsbb links in it
*/
if (PHPWS_Settings::get('phpwsbb', 'showlinksblock')) {
	$link = array();
    $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'List Forums'), 'phpwsbb', array());
    $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'New Posts'), 'phpwsbb', array('op'=>'getnew'));
    $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'Today\'s Posts'), 'phpwsbb', array('op'=>'viewtoday'));
    $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'This Week\'s Posts'), 'phpwsbb', array('op'=>'viewweek'));
    $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'Locked Topics'), 'phpwsbb', array('op'=>'viewlockedthreads'));
    $link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'Empty Topics'), 'phpwsbb', array('op'=>'viewzerothreads'));
    if (Current_User::isLogged()) 
    	$link[] = PHPWS_Text::moduleLink(dgettext('phpwsbb', 'My Topics'), 'phpwsbb', array('op'=>'viewuserthreads')); 
	$content = '<ul class="no-bullet"><li>'.implode('</li><li>', $link).'</li></ul>';
	$title = dgettext('phpwsbb', 'Forum Links');
	$finalContent = PHPWS_Template::process(array('TITLE'=>$title, 'CONTENT'=>$content), 'layout', 'box.tpl');
	Layout::add($finalContent, 'phpwsbb', 'linkblock');
}


?>