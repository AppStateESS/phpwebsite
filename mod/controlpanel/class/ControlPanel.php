<?php
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('controlpanel', 'Panel.php');

class PHPWS_ControlPanel {

	public static function display($content=null, $current_tab=null)
	{
		Layout::addStyle('controlpanel');

		$panel = new PHPWS_Panel('controlpanel');
		$panel->disableSecure();
		$current_mod = PHPWS_Core::getCurrentModule();

		$checkTabs = PHPWS_ControlPanel::loadTabs();

		$panel->setTabs($checkTabs);

		$allLinks = PHPWS_ControlPanel::getAllLinks();

		if (empty($checkTabs)){
			PHPWS_Error::log(CP_NO_TABS, 'controlpanel', 'display');
			PHPWS_ControlPanel::makeDefaultTabs();
			PHPWS_ControlPanel::reset();
			PHPWS_Core::errorPage();
			exit();
		}

		$defaultTabs = PHPWS_ControlPanel::getDefaultTabs();

		foreach ($defaultTabs as $tempTab) {
			$tabList[] = $tempTab['id'];
		}


		if (!empty($allLinks)) {
			$links = array_keys($allLinks);
			if ($current_mod != 'controlpanel' && !$current_tab) {
				foreach ($allLinks as $key => $tablinks) {
					foreach($tablinks as $link) {
						if ($link->itemname == $current_mod) {
							$current_tab = $key;
							break 2;
						}
					}
				}
			}
		}

		foreach ($checkTabs as $tab) {
			if ($tab->getItemname() == 'controlpanel' &&
			in_array($tab->id, $tabList) &&
			(!isset($links) || !in_array($tab->id, $links))
			) {
				$panel->dropTab($tab->id);
			}
		}

		if (empty($panel->tabs)) {
			return dgettext('controlpanel', 'No tabs available in the Control Panel.');
		}

		if (!isset($content) && $current_mod == 'controlpanel') {
			if (isset($allLinks[$panel->getCurrentTab()])) {
				foreach ($allLinks[$panel->getCurrentTab()] as $id => $link) {
					$link_content[] = $link->view();
				}

				$link_content = PHPWS_Template::process(array('LINKS' => implode('', $link_content)), 'controlpanel', 'links.tpl');
				$panel->setContent($link_content);
			}
		} else {
			$panel->setContent($content);
		}

		if (isset($current_tab)) {
			$panel->setCurrentTab($current_tab);
		}

		if (!isset($panel->tabs[$panel->getCurrentTab()])) {
			return dgettext('controlpanel', 'An error occurred while accessing the Control Panel.');
		}
		$tab = $panel->tabs[$panel->getCurrentTab()];
		$link = str_replace('&amp;', '&', $tab->getLink(false)) . '&tab=' . $tab->id;
		$current_link = str_replace($_SERVER['PHP_SELF'] . '\?', '', $_SERVER['REQUEST_URI']);

		// Headers to the tab's link if it is not a control panel
		// link tab.
		if (isset($_REQUEST['command']) &&
		$_REQUEST['command'] == 'panel_view' &&
		!preg_match('/controlpanel/', $link) &&
		$link != $current_link
		){
			PHPWS_Core::reroute($link);
		}

		return $panel->display();
	}

	public static function loadTabs()
	{
		$tabs = PHPWS_ControlPanel::getAllTabs();
		if (PEAR::isError($tabs)){
			PHPWS_Error::log($tabs);
			PHPWS_Core::errorPage();
		}

		return $tabs;
	}

	public static function getAllTabs()
	{
		$db = new PHPWS_DB('controlpanel_tab');
		$db->setIndexBy('id');
		$db->addOrder('tab_order');
		return $db->getObjects('PHPWS_Panel_Tab');
	}

	public static function getAllLinks($alpha_order=false)
	{
		PHPWS_Core::initModClass('controlpanel', 'Link.php');
		$allLinks = null;

		// This session prevents the DB query and link
		// creation from being repeated.

		$idx = $alpha_order ? 'normal' : 'alpha';

		if (isset($_SESSION['CP_All_links'][$idx])) {
			return $_SESSION['CP_All_links'][$idx];
		}

		$DB = new PHPWS_DB('controlpanel_link');

		if ($alpha_order) {
			$DB->addOrder('label');
		} else {
			$DB->addOrder('tab');
			$DB->addOrder('link_order');
		}
		$DB->setIndexBy('id');
		$result = $DB->getObjects('PHPWS_Panel_Link');

		if (empty($result)) {
			return null;
		}

		foreach ($result as $link){
			if (!$link->isRestricted() || Current_User::allow($link->itemname)) {
				$allLinks[$link->tab][] = $link;
			}
		}

		$_SESSION['CP_All_links'][$idx] = $allLinks;
		return $_SESSION['CP_All_links'][$idx];
	}

	public function reset()
	{
		unset($_SESSION['CP_All_links']);
	}

	public function unregisterModule($module, &$content)
	{
		PHPWS_Core::initModClass('controlpanel', 'Tab.php');
		PHPWS_Core::initModClass('controlpanel', 'Link.php');

		$itemnameList = array();
		$cpFile = sprintf('%smod/%s/boost/controlpanel.php', PHPWS_SOURCE_DIR, $module);

		if (!is_file($cpFile)){
			PHPWS_Boost::addLog($module, dgettext('controlpanel', 'Control Panel unregisteration file not implemented.'));

			return FALSE;
		}

		include $cpFile;

		/*** Get all the links associated with a module ***/
		if (isset($link) && is_array($link)) {
			foreach ($link as $info) {
				if (isset($info['itemname'])) {
					$itemname = $info['itemname'];
				}
				else {
					$itemname = $module;
				}

				if (!in_array($itemname, $itemnameList)) {
					$itemnameList[] = $itemname;
				}
			}

			$db = new PHPWS_DB('controlpanel_link');
			foreach ($itemnameList as $itemname) {
				$db->addWhere('itemname', $itemname);
				$result = $db->getObjects('PHPWS_Panel_Link');
				if (PEAR::isError($result)) {
					PHPWS_Error::log($result);

					return $result;
				} elseif (!$result) {
					continue;
				}

				foreach ($result as $link) {
					$link->kill();
				}
			}
		}

		$itemname = $info = NULL;
		$labelList = array();

		/** Get all the tabs associated with a module **/
		if (isset($tabs) && is_array($tabs)) {
			foreach ($tabs as $info) {
				if (isset($info['label'])) {
					$label = $info['label'];
				}
				else {
					$label = strtolower(preg_replace('/\W/', '_', $info['title']));
				}

				if (!in_array($label, $labelList)) {
					$labelList[] = $label;
				}
			}

			$db = new PHPWS_DB('controlpanel_tab');
			foreach ($labelList as $label){
				$db->addWhere('label', $label);
				$result = $db->getObjects('PHPWS_Panel_Tab');

				if (PEAR::isError($result)) {

					PHPWS_Error::log($result);
					return $result;
				} elseif (empty($result)) {
					continue;
				}

				foreach ($result as $tab) {
					$tab->kill();
				}
			}
		}

		$content[] = dgettext('controlpanel', 'Control Panel links and tabs have been removed.');
		PHPWS_ControlPanel::reset();
		return true;

	}

	public function registerModule($module, &$content)
	{
		PHPWS_Core::initModClass('controlpanel', 'Tab.php');
		PHPWS_Core::initModClass('controlpanel', 'Link.php');

		$cpFile = sprintf('%smod/%s/boost/controlpanel.php', PHPWS_SOURCE_DIR, $module);

		if (!is_file($cpFile)) {
			PHPWS_Boost::addLog($module, dgettext('controlpanel', 'Control Panel file not implemented.'));
			return false;
		}

		include $cpFile;
		// insure cp file does not change translation directory

		if (isset($tabs) && is_array($tabs)) {
			foreach ($tabs as $info){
				$tab = new PHPWS_Panel_Tab;

				if (!isset($info['id'])) {
					$tab->setId(strtolower(preg_replace('/\W/', '_', $info['title'])));
				} else {
					$tab->setId($info['id']);
				}

				if (!isset($info['title'])) {
					$content[] = dgettext('controlpanel', 'Unable to create tab.') . ' ' . dgettext('controlpanel', 'Missing title.');
					continue;
				}
				$tab->setTitle($info['title']);

				if (!isset($info['link'])) {
					$content[] = dgettext('controlpanel', 'Unable to create tab.') . ' ' . dgettext('controlpanel', 'Missing link.');
					continue;
				}

				$tab->setLink($info['link']);

				if (isset($info['itemname'])) {
					$tab->setItemname($info['itemname']);
				}
				else {
					$tab->setItemname('controlpanel');
				}

				$result = $tab->save();
				if (PEAR::isError($result)) {
					$content[] = dgettext('controlpanel', 'An error occurred when trying to save a controlpanel tab.');
					PHPWS_Error::log($result);
					return false;
				}
			}
			$content[] = sprintf(dgettext('controlpanel', 'Control Panel tabs created for %s.'), $module);
		} else {
			PHPWS_Boost::addLog($module, dgettext('controlpanel', 'Control Panel tabs not implemented.'));
		}

		if (isset($link) && is_array($link)) {
			$db = new PHPWS_DB('controlpanel_tab');
			foreach ($link as $info){
				$modlink = new PHPWS_Panel_Link;

				if (isset($info['label'])) {
					$modlink->setLabel($info['label']);
				}

				if (isset($info['restricted'])) {
					$modlink->setRestricted($info['restricted']);
				} elseif (isset($info['admin'])) {
					$modlink->setRestricted($info['admin']);
				}

				$modlink->setUrl($info['url']);
				$modlink->setActive(1);

				if (isset($info['itemname'])) {
					$modlink->setItemName($info['itemname']);
				}
				else {
					$modlink->setItemName($module);
				}

				$modlink->setDescription($info['description']);

				if (is_string($info['image'])) {
					$modlink->setImage($info['image']);
				} elseif(is_array($info['image'])) {
					$modlink->setImage($info['image']['name']);
				}

				$db->addWhere('id', $info['tab']);
				$db->addColumn('id');
				$result = $db->select('one');
				if (PEAR::isError($result)) {
					PHPWS_Error::log($result);
					continue;
				}
				elseif (!isset($result)) {
					$tab_id = 'unsorted';
					PHPWS_Boost::addLog($module, dgettext('controlpanel', 'Unable to load a link into a specified tab.'));
				} else {
					$tab_id = $info['tab'];
				}

				$modlink->setTab($tab_id);
				$result = $modlink->save();
				if (PEAR::isError($result)) {
					PHPWS_Error::log($result);
					$content[] = dgettext('controlpanel', 'There was a problem trying to save a Control Panel link.');
					return false;
				}
				$db->resetWhere();
			}
			$content[] = sprintf(dgettext('controlpanel', 'Control Panel links created for %s.'), $module);
		} else {
			PHPWS_Boost::addLog($module, dgettext('controlpanel', 'No Control Panel links found.'));
		}

		PHPWS_ControlPanel::reset();
		return true;
	}

	public function makeDefaultTabs()
	{
		$tabs = PHPWS_ControlPanel::getDefaultTabs();

		foreach ($tabs as $tab){
			$newTab = new PHPWS_Panel_Tab;
			$newTab->setId($tab['id']);
			$newTab->setTitle($tab['title']);
			$newTab->setLink($tab['link']);
			$newTab->setItemname('controlpanel');
			$newTab->save();

			if ($tab['id'] == 'unsorted') {
				$defaultId = $newTab->id;
			}
		}

		$db = new PHPWS_DB('controlpanel_link');
		$result = $db->getObjects('PHPWS_Panel_Link');

		$count = 1;

		if (empty($result)) {
			return null;
		}
		foreach ($result as $link){
			$link->setTab($defaultId);
			$link->setLinkOrder($count);
			$link->save();
			$count++;
		}
	}

	public static function getDefaultTabs()
	{
		include PHPWS_SOURCE_DIR . 'mod/controlpanel/boost/controlpanel.php';
		return $tabs;
	}

	public static function panelLink($fly_out=false)
	{
		Layout::addStyle('controlpanel', 'panel_link.css');
		$reg_link = PHPWS_Text::quickLink(dgettext('users', 'Control Panel'), 'controlpanel',
		array('command'=>'panel_view'));

		if (!$fly_out) {
			return $reg_link->get();
		}

		javascript('jquery');
		javascriptMod('controlpanel', 'subpanel');

		$reg_link->setId('cp-panel-link');

		$all_tabs = PHPWS_ControlPanel::loadTabs();

		$all_links = PHPWS_ControlPanel::getAllLinks(true);

		$tpl = new PHPWS_Template('controlpanel');
		$tpl->setFile('subpanel.tpl');

		$authkey = Current_User::getAuthKey();
		if (!empty($all_links)) {
			foreach ($all_links as $tab => $links) {
				foreach($links as $link) {
					$tpl->setCurrentBlock('links');
					$tpl->setData(array('LINK'=> sprintf('<a href="%s&amp;authkey=%s">%s</a>',
					$link->url, $authkey, str_replace(' ', '&#160;', $link->label))));
					$tpl->parseCurrentBlock();
				}

				$tab_link = $all_tabs[$tab]->link . '&amp;tab=' . $all_tabs[$tab]->id;
				$tpl->setCurrentBlock('tab');
				$tpl->setData(array('TAB_TITLE'=> sprintf('<a href="%s">%s</a>', $tab_link, $all_tabs[$tab]->title)));
				$tpl->parseCurrentBlock();
			}
		}

		$tpl->setCurrentBlock();
		$tpl->setData(array('CP_LINK' => $reg_link->get()));
		$tpl->parseCurrentBlock();
		$submenu = $tpl->get();
		return $submenu;
	}

}

?>
