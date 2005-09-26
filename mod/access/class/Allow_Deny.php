<?php

class Access_Allow_Deny {
    var $id         = 0;
    var $ip_address = NULL;
    var $allow      = 0;
    var $deny       = 0;
    var $accepted   = 0;
    var $_db        = NULL;

    function resetDB()
    {
        if (empty($this->_db)) {
            $this->_db = & new PHPWS_DB('access_allow_deny');
        } else {
            $this->_db->reset();
        }
    }

}

?>