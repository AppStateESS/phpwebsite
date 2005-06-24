<?php
/**
 * Tab class
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */

class PHPWS_Panel_Tab {
    var $id           = NULL;
    var $title        = NULL;
    var $link         = NULL;
    var $tab_order    = NULL;
    var $itemname     = NULL;
    var $_secure      = TRUE;

    function PHPWS_Panel_Tab($id=NULL) {

        if(isset($id)) {
            $this->setId($id);
            $this->init();
        }
    }

    function setId($id){
        $this->id = $id;
    }

    function init(){
        $DB = new PHPWS_DB('controlpanel_tab');
        $DB->loadObject($this);
    }

    function getId(){
        return $this->id;
    }

    function setTitle($title){
        $this->title = strip_tags($title);
    }

    function getTitle($noBreak=TRUE){
        if ($noBreak)
            return str_replace(' ', '&nbsp;', $this->title);
        else
            return $this->title;
    }

    function setLink($link){
        $this->link = $link;
    }

    function getLink($addTitle=TRUE){
        if ($addTitle){
            $title = $this->getTitle();
            $link = $this->getLink(FALSE);
            if ($this->_secure) {
                $authkey = Current_User::getAuthKey();
                return sprintf('<a href="%s&amp;tab=%s&amp;authkey=%s">%s</a>',
                               $link, $this->getId(), $authkey, $title);
            } else {
                return sprintf('<a href="%s&amp;tab=%s">%s</a>',
                               $link, $this->getId(), $title);
            }
        } else {
            return $this->link;
        }
    }


    function setOrder($order){
        $this->tab_order = $order;
    }

    function getOrder(){
        if (isset($this->tab_order))
            return $this->tab_order;

        $DB = & new PHPWS_DB('controlpanel_tab');
        $DB->addColumn('tab_order', NULL, 'max');
        $max = $DB->select('one');
    
        if (PEAR::isError($max))
            exit($max->getMessage());

        if (isset($max))
            return $max + 1;
        else
            return 1;
    }

    function setItemname($itemname){
        $this->itemname = $itemname;
    }

    function getItemname(){
        return $this->itemname;
    }

    function disableSecure()
    {
        $this->_secure = FALSE;
    }

    function enableSecure()
    {
        $this->_secure = TRUE;
    }

    function save(){
        $db = & new PHPWS_DB('controlpanel_tab');
        $db->addWhere('id', $this->id);
        $db->delete();
        $db->resetWhere();
        $this->tab_order = $this->getOrder();
        return $db->saveObject($this, FALSE, FALSE);
    }

    function nextBox(){
        $db = & new PHPWS_DB('controlpanel_tab');
        $db->addWhere('theme', $this->getTheme());
        $db->addWhere('theme_var', $this->getThemeVar());
        $db->addColumn('box_order', NULL, 'max');
        $max = $db->select('one');
        if (isset($max))
            return $max + 1;
        else
            return 1;
    }


    /**
     * Moves the tab 'up' the order, which is actually a lower
     * order number
     */ 
    function moveUp(){
        $db = & new PHPWS_DB('controlpanel_tab');
        $db->setIndexBy('tab_order');
        $db->addOrder('tab_order');
        $allTabs = $db->getObjects('PHPWS_Panel_Tab');

        $current_order = $this->getOrder();
        if ($current_order == 1){
            unset($allTabs[1]);
            $allTabs[] = $this;
        } else {
            $tempObj = $allTabs[$current_order - 1];
            $allTabs[$current_order] = $tempObj;
            $allTabs[$current_order - 1] = $this;
        }


        $count = 1;
        foreach ($allTabs as $tab){
            $tab->setOrder($count);
            $tab->save();
            $count++;
        }
    }

    function moveDown(){
        $db = & new PHPWS_DB('controlpanel_tab');
        $db->setIndexBy('tab_order');
        $db->addOrder('tab_order');
        $allTabs = $db->getObjects('PHPWS_Panel_Tab');
        $number_of_tabs = count($allTabs);

        $current_order = $this->getOrder();
        if ($current_order == $number_of_tabs){
            unset($allTabs[$current_order]);
            array_unshift($allTabs, $this);
        } else {
            $tempObj = $allTabs[$current_order + 1];
            $allTabs[$current_order] = $tempObj;
            $allTabs[$current_order + 1] = $this;
        }

        $count = 1;
        foreach ($allTabs as $tab){
            $tab->setOrder($count);
            $tab->save();
            $count++;
        }

    }

}

?>