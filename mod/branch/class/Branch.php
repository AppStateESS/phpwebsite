<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Branch {
    var $id          = NULL;
    var $branch_name = NULL;
    var $directory   = NULL; // saved WITHOUT final forward slash (/)
    var $url         = NULL;
    var $hash        = NULL;

    function Branch($id=0)
    {
        $this->hash = md5(rand());
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = & new PHPWS_DB('branch_sites');
        $db->loadObject($this);
    }
   
    
    function save()
    {
        $this->directory = preg_replace('/\/$/', '', $this->directory);
        $db = & new PHPWS_DB('branch_sites');
        return $db->saveObject($this);
    }

    function createDirectories()
    {
        if (!mkdir($this->directory . '/config/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/config/core/')) {
            return FALSE;
        }
        
        if (!mkdir($this->directory . '/files/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/images/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/images/core/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/javascript/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/javascript/modules')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/templates/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/themes/')) {
            return FALSE;
        }

        if (!mkdir($this->directory . '/logs/')) {
            return FALSE;
        }

        return TRUE;

    }

}

?>