<?php
/**
 * Class to control the link icons in the Control Panel
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class PHPWS_Panel_Link {
    public $id          = 0;
    public $label       = null;
    public $active      = 1;
    public $itemname    = null;
    public $restricted  = true;
    public $tab         = null;
    public $url         = null;
    public $description = null;
    public $image       = null;
    public $link_order  = null;

    public function __construct($id=null)
    {
        if (!isset($id))
        return;

        $this->setId($id);
        $result = $this->init();
        if (PEAR::isError($result))
        PHPWS_Error::log($result);
    }

    public function init()
    {
        $db = new PHPWS_DB('controlpanel_link');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function setActive($active)
    {
        $this->active = (bool)$active;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setLabel($label)
    {
        $this->label = strip_tags($label);
    }

    public function getLabel()
    {
        return dgettext($this->itemname, $this->label);
    }


    public function getDescription()
    {
        return dgettext($this->itemname, $this->description);
    }

    public function setDescription($description)
    {
        $this->description = strip_tags($description);
    }


    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage($tag=false, $linkable=false)
    {
        if ($tag == false) {
            return $this->image;
        }

        if ($this->restricted) {
            $authkey = '&amp;authkey=' . Current_User::getAuthKey();
        } else {
            $authkey = null;
        }

        $image_path = sprintf('%smod/%s/img/%s', PHPWS_SOURCE_HTTP, $this->itemname, $this->image);
            $image = sprintf('<img src="%s" title="%s" alt="%s" />', $image_path, $this->getLabel(),
                sprintf(dgettext('controlpanel', '%s module icon'), $this->getLabel()));

        if ($linkable == true) {
            $image = sprintf('<a href="%s%s">%s</a>', $this->url, $authkey, $image);
        }
        return $image;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl($tag=false)
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

    public function setLinkOrder($order)
    {
        $this->link_order = (int)$order;
    }

    public function getLinkOrder()
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

    public function setItemName($itemname)
    {
        $this->itemname = $itemname;
    }

    public function getItemName()
    {
        return $this->itemname;
    }

    public function isRestricted()
    {
        return (bool)$this->restricted;
    }

    public function setRestricted($restrict)
    {
        $this->restricted = $restrict;
    }

    public function save()
    {
        $db = new PHPWS_DB('controlpanel_link');
        $this->link_order = $this->getLinkOrder();
        $result = $db->saveObject($this);
        return $result;
    }

    public function view()
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
    public function moveUp()
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

    public function moveDown()
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

    public function kill()
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