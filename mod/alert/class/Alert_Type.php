<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Alert_Type {
    var $id = 0;
    var $title = null;
    var $email = false;
    var $xssfeed = false;
    var $post_type = 0;
    var $default_alert = null;

    function Alert_Type($id=0)
    {
        if (!$id) {
            return true;
        }

        $this->id = (int)$id;
        $result = $this->init();
        if (!$result || PHPWS_Error::logIfError($result)) {
            $this->id = 0;
        }
    }

    function init()
    {
        $db = new PHPWS_DB('alert_item');
        $result = $db->loadObject($this);
        if (!$result || PHPWS_Error::isError($result)) {
            return $result;
        }
        return true;
    }

    function getDefaultAlert()
    {
        return PHPWS_Text::parseOutput($this->default_alert);
    }

    function setDefaultAlert($text)
    {
        $this->default_alert = PHPWS_Text::parseInput($text);
    }

}

?>