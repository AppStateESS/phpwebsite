<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Alert_Item {
    var $id               = 0;
    var $title            = 0;
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

    function getDescription()
    {
        return PHPWS_Text::parseOutput($this->description);
    }

}

?>