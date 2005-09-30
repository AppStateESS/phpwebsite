<?php

class Access_Allow_Deny {
    var $id            = 0;
    var $ip_address    = NULL;
    var $allow_or_deny = 0; // 0 deny | 1 allow
    var $active        = 0;
    var $_db           = NULL;

    function resetDB()
    {
        if (empty($this->_db)) {
            $this->_db = & new PHPWS_DB('access_allow_deny');
        } else {
            $this->_db->reset();
        }
    }

    function setIpAddress($ip_address)
    {
        if (preg_match('/[^\d\.]/', $ip_address)) {
            return FALSE;
        }

        $ip_length = strlen((string)$ip_address);

        if (strstr($ip_address, '.')) {
            $ip_list = explode('.', $ip_address);
        } elseif ($ip_length % 3) {
            return FALSE;
        } else {
            for ($i=0; $i < $ip_length; $i += 3) {
                $sub = (int)substr($ip_address, $i, 3);
                $ip_list[] = $sub;
            }
        }

        if (!$this->allow_or_deny) {
            if ($this->inRange($ip_list, '127.0.0.1')) {
                return FALSE;
            } elseif ($this->inRange($ip_list, Current_User::getIp())) {
                return FALSE;
            }
        }

        foreach ($ip_list as $key => $subset) {

            if ($subset > 255 || $subset == NULL) {
                return FALSE;
            }
        }

        $this->ip_address = implode('.', $ip_list);

        return TRUE;
    }

    function inRange($ip_list, $in_range) {

        $compare = explode('.', $in_range);

        switch (count($ip_list)) {
        case 4:
            if ((int)$ip_list[3] != $compare[3]) {
                break;
            }
        case 3:
            if ((int)$ip_list[2] != $compare[2]) {
                break;
            } 
        case 2:
            if ((int)$ip_list[1] != $compare[1]) {
                break;
            } 
        case 1:
            if ((int)$ip_list[0] != $compare[0]) {
                break;
            }
        default:
            return TRUE;
        }
        
        return FALSE;
    }


    function save()
    {
        $this->resetDB();
        return $this->_db->saveObject($this);
    }

}

?>