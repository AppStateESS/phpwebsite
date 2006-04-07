<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Branch {
    var $id          = NULL;
    var $branch_name = NULL;
    var $directory   = NULL;
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
    
    
}

?>