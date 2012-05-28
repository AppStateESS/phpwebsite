<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Signup_Peep {
    public $id           = 0;
    public $sheet_id     = 0;
    public $slot_id      = 0;
    public $first_name   = null;
    public $last_name    = null;
    public $email        = null;
    public $phone        = null;
    /**
     * No longer used
     */
    public $hashcheck    = null;
    public $timeout      = 0;
    public $registered   = 0;
    public $extra1       = null;
    public $extra2       = null;
    public $extra3       = null;

    public $_error       = null;


    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    public function clean($text)
    {
        return preg_replace('/[^\w\'\s\-\.]/', '', strip_tags(trim($text)));
    }


    public function init()
    {
        $db = new PHPWS_DB('signup_peeps');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            $this->_error = $result;
        } elseif (!$result) {
            $this->id = 0;
        }
    }

    public function getEmail()
    {
        return sprintf('<a href="mailto:%s">%s</a>', $this->email, $this->email);
    }

    public function getPhone()
    {
        return $this->phone;
    }


    public function setFirstName($first_name)
    {
        $this->first_name = $this->clean($first_name);
    }

    public function setLastName($last_name)
    {
        $this->last_name = $this->clean($last_name);
    }

    public function setPhone($phone)
    {
        $this->phone = trim(preg_replace('/(\d{3})?[ .-]?(\d{3})[ .-]?(\d{4})/', '\1 \2-\3 ', $phone));
    }

    public function save()
    {
        $db = new PHPWS_DB('signup_peeps');
        return $db->saveObject($this);
    }

    public function delete()
    {
        $db = new PHPWS_DB('signup_peeps');
        $db->addWhere('id', $this->id);
        $db->delete();
    }

    public function rowtags()
    {
        $tpl['PHONE'] = $this->getPhone();
        $tpl['EMAIL'] = $this->getEmail();
        $tpl['EXTRA1'] = $this->getExtra1();
        $tpl['EXTRA2'] = $this->getExtra2();
        $tpl['EXTRA3'] = $this->getExtra3();
        return $tpl;
    }


    public function getExtra1()
    {
        return PHPWS_Text::parseOutput($this->extra1);
    }

    public function getExtra2()
    {
        return PHPWS_Text::parseOutput($this->extra2);
    }

    public function getExtra3()
    {
        return PHPWS_Text::parseOutput($this->extra3);
    }

    public function setExtra1($extra)
    {
        $this->setExtraX($extra, $this->extra1);
    }

    public function setExtra2($extra)
    {
        $this->setExtraX($extra, $this->extra2);
    }

    public function setExtra3($extra)
    {
        $this->setExtraX($extra, $this->extra3);
    }

    public function setExtraX($extra, &$key)
    {
        $key = PHPWS_Text::parseInput(trim(strip_tags($extra)));
    }
}

?>
