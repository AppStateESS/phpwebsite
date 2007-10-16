<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Alert_Item {
    var $id               = 0;
    var $title            = null;
    var $description      = null;
    var $create_date      = 0;
    var $update_date      = 0;
    var $created_by_id    = 0;
    var $created_name     = null;
    var $updated_by_id    = 0;
    var $updated_name     = null;
    var $type_id          = 0;
    var $contact_complete = false;
    var $active           = true;

    function Alert_Item($id=0)
    {
        if (!$id) {
            return true;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = new PHPWS_DB('alert_item');
        $db->loadObject($this);
    }

    function setTitle($title)
    {
        $this->title = trim(strip_tags($title));
    }


    function setDescription($desc)
    {
        $this->description = PHPWS_Text::parseInput($desc);
    }

    function getDescription()
    {
        return PHPWS_Text::parseOutput($this->description);
    }

    function rowTags()
    {
        $tpl = array();

        $links[] = PHPWS_Text::secureLink(dgettext('alert', 'Edit'), 'alert', array('aop' => 'edit_item',
                                                                                    'id'  => $this->id));
        if (Current_User::allow('alert', 'delete_items')) {
            $js['question'] = dgettext('alert', 'Are you sure you want to delete this alert?');
            $js['link']     = dgettext('alert', 'Delete');
            $js['address']  = PHPWS_Text::linkAddress('alert', array('aop'=>'delete_item', 'id'=>$this->id), true);
            $links[] = javascript('confirm', $js);
        }

        $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }
    
    function save()
    {
        if (!$this->id) {
            $this->create_date = mktime();
            $this->created_by_id = Current_User::getId();
            $this->created_name = Current_User::getUsername();
        }

        $this->update_date   = mktime();
        $this->updated_by_id = Current_User::getId();
        $this->updated_name  = Current_User::getUsername();

        $db = new PHPWS_DB('alert_item');
        return $db->saveObject($this);
    }
}

?>