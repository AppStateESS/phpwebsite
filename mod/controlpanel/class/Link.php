<?php
/**
 * Class to control the link icons in the Control Panel
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class PHPWS_Panel_Link {
    var $id          = 0;
    var $label       = null;
    var $active      = 1;
    var $itemname    = null;
    var $restricted  = true;
    var $tab         = null;
    var $url         = null;
    var $description = null;
    var $image       = null;
    var $link_order  = null;

    function PHPWS_Panel_Link($id=null)
    {
        if (!isset($id))
            return;

        $this->setId($id);
        $result = $this->init();
        if (PEAR::isError($result))
            PHPWS_Error::log($result);
    }

    function init()
    {
        $db = new PHPWS_DB('controlpanel_link');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function setTab($tab)
    {
        $this->tab = $tab;
    }

    function setActive($active)
    {
        $this->active = (bool)$active;
    }

    function getActive()
    {
        return $this->active;
    }

    function setLabel($label)
    {
        $this->label = strip_tags($label);
    }

    function getLabel()
    {
        return $this->label;
    }


    function getDescription()
    {
        return $this->description;
    }

    function setDescription($description)
    {
        $this->description = strip_tags($description);
    }


    function setImage($image)
    {
        $this->image = $image;
    }

    function getImage($tag=false, $linkable=false)
    {
        if ($tag == false) {
            return $this->image;
        }

        if ($this->restricted) {
            $authkey = '&amp;authkey=' . Current_User::getAuthKey();
        } else {
            $authkey = null;
        }

        if (is_file($this->image)) {
            $image = sprintf('<img src="%s" title="%s" alt="%s" />',
                             $this->image, $this->getLabel(),
                             sprintf(dgettext('controlpanel', '%s module icon'), $this->getLabel()));
        } else {
            return null;
        }

        if ($linkable == true) {
            $image = sprintf('<a href="%s%s">%s</a>', $this->url, $authkey, $image);
        }

        return $image;
    }

    function setUrl($url)
    {
        $this->url = $url;
    }
  
    function getUrl($tag=false)
    {
        if ($this->restricted) {
            $authkey = '&amp;authkey=' . Current_User::getAuthKey();
        } else {
            $authkey = null;
        }


        if ($tag) {
            return sprintf('<a href="%s%s">%s</a>', $this->url, $authkey, $this->getLabel());
        }
        else
            return $this->url;
    }

    function setLinkOrder($order)
    {
        $this->link_order = (int)$order;
    }

    function getLinkOrder()
    {
        if (isset($this->link_order)) {
            return $this->link_order;
        }

        $db = new PHPWS_DB('controlpanel_link');
        $db->addWhere('tab', $this->tab);
        $db->addColumn('link_order', 'max');
        $max = $db->select('one');
    
        if (PEAR::isError($max)) {
            return $max;
        }

        if (isset($max)) {
            return $max + 1;
        }
        else {
            return 1;
        }
    }

    function setItemName($itemname)
    {
        $this->itemname = $itemname;
    }

    function getItemName()
    {
        return $this->itemname;
    }

    function isRestricted()
    {
        return (bool)$this->restricted;
    }

    function setRestricted($restrict)
    {
        $this->restricted = $restrict;
    }

    function save()
    {
        $db = new PHPWS_DB('controlpanel_link');
        $this->link_order = $this->getLinkOrder();
        $result = $db->saveObject($this);
        return $result;
    }

    function view()
    {
        $tpl['IMAGE']       = $this->getImage(true, true);
        $tpl['NAME']        = $this->getUrl(true);
        $tpl['DESCRIPTION'] = $this->getDescription();

        return PHPWS_Template::process($tpl, 'controlpanel', 'link.tpl');
    }

    /**
     * Moves the tab 'up' the order, which is actually a lower
     * order number
     */ 
    function moveUp()
    {
        $db = new PHPWS_DB('controlpanel_link');
        $db->setIndexBy('link_order');
        $db->addWhere('tab', $this->tab);
        $db->addOrder('link_order');
        $allLinks = $db->getObjects('PHPWS_Panel_Link');

        $current_order = $this->getLinkOrder();
        if ($current_order == 1){
            unset($allLinks[1]);
            $allLinks[] = $this;
        } else {
            $tempObj = $allLinks[$current_order - 1];
            $allLinks[$current_order] = $tempObj;
            $allLinks[$current_order - 1] = $this;
        }


        $count = 1;
        foreach ($allLinks as $link){
            $link->setLinkOrder($count);
            $link->save();
            $count++;
        }
    }

    function moveDown()
    {
        $db = new PHPWS_DB('controlpanel_link');
        $db->setIndexBy('link_order');
        $db->addWhere('tab', $this->tab);
        $db->addOrder('link_order');
        $allLinks = $db->getObjects('PHPWS_Panel_Link');

        $number_of_links = count($allLinks);

        $current_order = $this->getLinkOrder();
        if ($current_order == $number_of_links){
            unset($allLinks[$current_order]);
            array_unshift($allLinks, $this);
        } else {
            $tempObj = $allLinks[$current_order + 1];
            $allLinks[$current_order] = $tempObj;
            $allLinks[$current_order + 1] = $this;
        }

        $count = 1;
        foreach ($allLinks as $link){
            $link->setLinkOrder($count);
            $link->save();
            $count++;
        }
    }

  

    function kill()
    {
        $db = new PHPWS_DB('controlpanel_link');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (PEAR::isError($result))
            return $result;

        $db->reset();
        $db->addWhere('tab', $this->tab);
        $db->addOrder('link_order');
        $result = $db->getObjects('PHPWS_Panel_Link');

        if (PEAR::isError($result))
            return $result;

        if (empty($result))
            return true;

        $count = 1;
        foreach ($result as $link){
            $link->setLinkOrder($count);
            $link->save();
            $count++;
        }
    }

}
?>