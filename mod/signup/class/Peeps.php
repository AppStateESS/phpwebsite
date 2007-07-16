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
    var $hashcheck  = null;
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

    function clean($text)
    {
        return strip_tags(trim($text));
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
        if (empty($this->phone)) {
            return null;
        }
        $phone_format = SU_PHONE_FORMAT;
        
        $number = & $this->phone;
        
        $no_array = str_split(strrev($number));
        $fmt_array = str_split(strrev($phone_format));
        
        $number_length = strlen($phone_format);
        
        $j=0;
        for ($i=0; $i < $number_length; $i++) {
            if ($fmt_array[$i] == 'x') {
                if (isset($no_array[$j])) {
                    $new_number_array[] = $no_array[$j];
                    $j++;
                } else {
                    break;
        }
            } else {
                if (isset($no_array[$j + 1])) {
                    $new_number_array[] = $fmt_array[$i];
                }
            }
        }
        
        $new_number_string = implode('', $new_number_array);
        $new_number_string = strrev($new_number_string);
        return $new_number_string;
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
        $this->phone = preg_replace('/\D/', '', $phone);

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

}

?>