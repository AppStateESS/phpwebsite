<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Signup_Peep {
    var $id           = 0;
    var $sheet_id     = 0;
    var $slot_id      = 0;
    var $first_name   = null;
    var $last_name    = null;
    var $email        = null;
    var $phone        = null;
    var $organization = null;
    var $hashcheck    = null;
    var $timeout      = 0;
    var $registered   = 0;

    var $_error       = null;


    function Signup_Peep($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function clean($text)
    {
        return preg_replace('/[^\w\'\s\-\.]/', '', strip_tags(trim($text)));
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
        return $this->phone;
    }


    function setFirstName($first_name)
    {
        $this->first_name = $this->clean($first_name);
    }

    function setLastName($last_name)
    {
        $this->last_name = $this->clean($last_name);
    }

    function setPhone($phone)
    {
        $this->phone = preg_replace('/[^\w\-#\s\.]/', '', $phone);

    }

    function setOrganization($organization)
    {
        $this->organization = $this->clean($organization);
    }

    function save()
    {
        $db = new PHPWS_DB('signup_peeps');
        return $db->saveObject($this);
    }

    function delete()
    {
        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('id', $this->id);
        $db->delete();
    }

    function rowtags()
    {
        $tpl['PHONE'] = $this->getPhone();
        $tpl['EMAIL'] = $this->getEmail();
        return $tpl;
    }

}

?>