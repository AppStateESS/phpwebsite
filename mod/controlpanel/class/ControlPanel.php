<?php
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$ 
 */

PHPWS_Core::initModClass('controlpanel', 'Panel.php');

class PHPWS_ControlPanel {

    function display($content=NULL)
    {
        Layout::addStyle('controlpanel');

        $panel = new PHPWS_Panel('controlpanel');
        $panel->disableSecure();

        if (1 || !isset($_SESSION['Control_Panel_Tabs'])){
            PHPWS_ControlPanel::loadTabs($panel);
        }
        else {
            $panel->setTabs($_SESSION['Control_Panel_Tabs']);
        }

        $allLinks = PHPWS_ControlPanel::getAllLinks();

        $checkTabs = $panel->getTabs();

        if (empty($checkTabs)){
            PHPWS_Error::log(CP_NO_TABS, 'controlpanel', 'display');
            PHPWS_ControlPanel::makeDefaultTabs();
            ;
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
        }

        foreach ($checkTabs as $tab){
            if ($tab->getItemname() == 'controlpanel' &&
                in_array($tab->id, $tabList) &&
                (!isset($links) || !in_array($tab->id, $links))
                ) {
                $panel->dropTab($tab->id);
            }
        }

        if (empty($panel->tabs)) {
            return _('No tabs available in the Control Panel.');
        }

        if (!isset($content)){
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

        if (!isset($panel->tabs[$panel->getCurrentTab()])) {
            return _('An error occurred while accessing the Control Panel.');
        }
        $tab = $panel->tabs[$panel->getCurrentTab()];
        $link = str_replace('&amp;', '&', $tab->getLink(FALSE)) . '&tab=' . $tab->id;
        $current_link = ereg_replace($_SERVER['PHP_SELF'] . '\?', '', $_SERVER['REQUEST_URI']);

        // Headers to the tab's link if it is not a control panel
        // link tab. 
        if (isset($_REQUEST['command']) &&
            $_REQUEST['command'] == 'panel_view' &&
            !preg_match('/controlpanel/', $link) &&
            $link != $current_link
            ){
            PHPWS_Core::reroute($link);
        }


        $_SESSION['Control_Panel_Tabs'] = $panel->getTabs();
        return $panel->display();
    }

    function loadTabs(&$panel)
    {
        $DB = new PHPWS_DB('controlpanel_tab');
        $DB->addOrder('tab_order');
        $DB->setIndexBy('id');
        $result = $DB->getObjects('PHPWS_Panel_Tab');

        if (PEAR::isError($result)){
            PHPWS_Error::log($result);
            PHPWS_Core::errorPage();
        }

        $panel->setTabs($result);
    }

    function getAllTabs()
    {
        $db = & new PHPWS_DB('controlpanel_tab');
        $db->setIndexBy('id');
        $db->addOrder('tab_order');
        return $db->getObjects('PHPWS_Panel_Tab');
    }

    function getAllLinks()
    {
        PHPWS_Core::initModClass('controlpanel', 'Link.php');
        $allLinks = NULL;

        // This session prevents the DB query and link
        // creation from being repeated.

        if (isset($_SESSION['CP_All_links'])) {
            return $_SESSION['CP_All_links'];
        }

        $DB = new PHPWS_DB('controlpanel_link');
        $DB->addOrder('tab');
        $DB->addOrder('link_order');
        $DB->setIndexBy('id');
        $result = $DB->getObjects('PHPWS_Panel_Link');

        foreach ($result as $link){
            if (!$link->isRestricted() || Current_User::allow($link->itemname)) {
                $allLinks[$link->tab][] = $link;
            }
        }

        $_SESSION['CP_All_links'] = $allLinks;
        return $_SESSION['CP_All_links'];
    }

    function reset()
    {
        unset($_SESSION['Control_Panel_Tabs']);
        unset($_SESSION['CP_All_links']);
    }

    function registerModule($module, &$content)
    {
        PHPWS_Core::initModClass('controlpanel', 'Tab.php');
        PHPWS_Core::initModClass('controlpanel', 'Link.php');

        $cpFile = PHPWS_Core::getConfigFile($module, 'controlpanel.php');

        if ($cpFile == FALSE){
            PHPWS_Boost::addLog($module, _('No Control Panel file found.'));
            return NULL;
        }

        $modSource = PHPWS_SOURCE_DIR . 'mod/' . $module . '/img';
        $modImage = PHPWS_HOME_DIR . 'images/mod/' . $module;
        if (is_dir($modSource) && !is_dir($modImage)) {
            PHPWS_Core::initCoreClass('File.php');
            $content[] = _('Copying source image directory for module.');
            
            $result = PHPWS_File::recursiveFileCopy($modSource, $modImage);
            if ($result) {
                $content[] = _('Source image directory copied successfully.');
            } else {
                $content[] = _('Source image directory failed to copy.');
            }
        }

        include $cpFile;

        if (isset($tabs) && is_array($tabs)) {
            foreach ($tabs as $info){
                $tab = new PHPWS_Panel_Tab;

                if (!isset($info['id'])) {
                    $tab->setId(strtolower(preg_replace('/\W/', '_', $info['title'])));
                } else {
                    $tab->setId($info['id']);
                }

                if (!isset($info['title'])) {
                    $content[] = _('Unable to create tab.') . ' ' . _('Missing title.');
                    continue;
                }   
                $tab->setTitle($info['title']);

                if (!isset($info['link'])) {
                    $content[] = _('Unable to create tab.') . ' ' . _('Missing link.');
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
                    $content[] = _('An error occurred when trying to save a controlpanel tab.');
                    PHPWS_Error::log($result);
                    return FALSE;
                }
            }
            $content[] = sprintf(_('Control Panel tabs created for %s.'), $module);
        } else {
            PHPWS_Boost::addLog($module, _('No Control Panel tabs found.'));
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
                    $modlink->setImage(sprintf('images/mod/%s/%s', $module, $info['image']));
                } elseif(is_array($info['image'])) {
                    $modlink->setImage(sprintf('images/mod/%s/%s', $module, $info['image']['name']));
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
                    PHPWS_Boost::addLog($module, _('Unable to load a link into a specified tab.'));
                } else {
                    $tab_id = $info['tab'];
                }

                $modlink->setTab($tab_id);
                $result = $modlink->save();
                if (PEAR::isError($result)) {
                    PHPWS_Error::log($result);
                    $content[] = _('There was a problem trying to save a Control Panel link.');
                    return FALSE;
                }
                $db->resetWhere();
            }
            $content[] = sprintf(_('Control Panel links created for %s.'), $module);
        } else {
            PHPWS_Boost::addLog($module, _('No Control Panel links found.'));
        }

        PHPWS_ControlPanel::reset();
        return TRUE;

    }

    function makeDefaultTabs()
    {
        $tabs = PHPWS_ControlPanel::getDefaultTabs();

        foreach ($tabs as $tab){
            $newTab = & new PHPWS_Panel_Tab;
            $newTab->setId($tab['id']);
            $newTab->setTitle($tab['title']);
            $newTab->setLink($tab['link']);
            $newTab->setItemname('controlpanel');
            $newTab->save();

            if ($tab['id'] == 'unsorted') {
                $defaultId = $newTab->id;
            }
        }

        $db = & new PHPWS_DB('controlpanel_link');
        $result = $db->getObjects('PHPWS_Panel_Link');

        $count = 1;

        foreach ($result as $link){
            $link->setTab($defaultId);
            $link->setLinkOrder($count);
            $link->save();
            $count++;
        }
    }

    function getDefaultTabs()
    {
        include PHPWS_Core::getConfigFile('controlpanel', 'controlpanel.php');
        return $tabs;
    }

}

?>