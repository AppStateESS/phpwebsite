<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Signup_Peep {
    var $id         = 0;
    var $sheet_id   = 0;
    var $slot_id    = 0;
    var $first_name = null;
    var $last_name  = null;
    var $email      = null;
    var $phone      = null;
    var $hash       = null;
    var $timeout    = 0;
    var $registered = 0;

    var $_error     = null;


    function Signup_Peep($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = new PHPWS_DB('signup_peeps');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            $this->_error = $result;
        } elseif (!$result) {
            $this->id = 0;
        }
    }

    function getEmail()
    {
        return sprintf('<a href="mailto:%s">%s</a>', $this->email, $this->email);
    }

    function getPhone()
    {
        return sprintf('%s-%s-%s', substr($this->phone, 0, 3),
                       substr($this->phone, 3, 3),
                       substr($this->phone, 6, 9));
    }

}

?>