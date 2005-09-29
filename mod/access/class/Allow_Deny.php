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
            $aIPaddress = explode('.', $ip_address);
        } elseif ($ip_length % 3) {
            return FALSE;
        } else {
            for ($i=0; $i < $ip_length; $i += 3) {
                $sub = (int)substr($ip_address, $i, 3);
                $aIPaddress[] = $sub;
            }
        }


        foreach ($aIPaddress as $subset) {
            if ($subset > 255 || $subset == NULL) {
                return FALSE;
            }
        }

        $this->ip_address = implode('.', $aIPaddress);

        return TRUE;
    }

    function save()
    {
        $this->resetDB();
        return $this->_db->saveObject($this);
    }

}

?>