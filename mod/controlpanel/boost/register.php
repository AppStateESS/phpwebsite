<?php
/**
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$ 
 */

function controlpanel_register($module, &$content){
    PHPWS_Core::initModClass('controlpanel', 'Tab.php');
    PHPWS_Core::initModClass('controlpanel', 'Link.php');
    PHPWS_Core::initModClass('controlpanel', 'ControlPanel.php');
    $cpFile = PHPWS_Core::getConfigFile($module, 'controlpanel.php');

    if ($cpFile == FALSE){
        PHPWS_Boost::addLog($module, _('No Control Panel file found.'));
        return NULL;
    }

    include_once($cpFile);

    if (isset($tabs) && is_array($tabs)){
        foreach ($tabs as $info){
            $tab = new PHPWS_Panel_Tab;

            if (!isset($info['id']))
                $tab->setId(strtolower(preg_replace('/\W/', '_', $info['title'])));
            else
                $tab->setId($info['id']);

            if (!isset($info['title'])){
                $content[] = _('Unable to create tab.') . ' ' . _('Missing title.');
                continue;
            }   
            $tab->setTitle($info['title']);

            if (!isset($info['link'])){
                $content[] = _('Unable to create tab.') . ' ' . _('Missing link.');
                continue;
            }   

            $tab->setLink($info['link']);

            if (isset($info['itemname']))
                $tab->setItemname($info['itemname']);
            else
                $tab->setItemname('controlpanel');

            $result = $tab->save();
            if (PEAR::isError($result)){
                $content[] = _('An error occurred when trying to save a controlpanel tab.');
                PHPWS_Error::log($result);
                return FALSE;
            }
        }
        $content[] = sprintf(_('Control Panel tabs created for %s.'), $module);
    } else
        PHPWS_Boost::addLog($module, _('No Control Panel tabs found.'));
    

    if (isset($link) && is_array($link)){
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

            if (isset($info['itemname']))
                $modlink->setItemName($info['itemname']);
            else
                $modlink->setItemName($module);

            $modlink->setDescription($info['description']);
            if (is_string($info['image']))
                $modlink->setImage("images/mod/$module/" . $info['image']);
            elseif(is_array($info['image']))
                $modlink->setImage("images/mod/$module/" . $info['image']['name']);

            $db->addWhere('id', $info['tab']);
            $db->addColumn('id');
            $result = $db->select('one');
            if (PEAR::isError($result)){
                PHPWS_Error::log($result);
                continue;
            }
            elseif (!isset($result)){
                $tab_id = 'unsorted';
                PHPWS_Boost::addLog($module, _('Unable to load a link into a specified tab.'));
            } else
                $tab_id = $info['tab'];

            $modlink->setTab($tab_id);
            $result = $modlink->save();
            if (PEAR::isError($result)){
                PHPWS_Error::log($result);
                $content[] = _('There was a problem trying to save a Control Panel link.');
                return FALSE;
            }
            $db->resetWhere();
        }
        $content[] = sprintf(_('Control Panel links created for %s.'), $module);
    } else
        PHPWS_Boost::addLog($module, _('No Control Panel links found.'));

    PHPWS_ControlPanel::reset();
    return TRUE;
}

?>