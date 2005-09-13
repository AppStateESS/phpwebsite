<?php
/**
 * Class that holds individual pages
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Webpage_Volume {
    var $id            = 0;
    var $title         = 0;
    var $summary       = NULL;
    var $date_created  = 0;
    var $date_updated  = 0;
    var $created_user  = 0;
    var $updated_user  = 0;
    var $template      = NULL;
    var $frontpage     = FALSE;
    var $_current_page = 1;
    var $_error        = NULL;
    var $_db           = NULL;

    function Webpage_Volume($id=NULL)
    {
        if (empty($id)) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function resetDB()
    {
        if (empty($this->_db)) {
            $this->_db = & new PHPWS_DB('webpage_volume');
        } else {
            $this->_db->reset;
        }
    }

    function init()
    {
        $result = $this->_db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return;
        }
    }

}

?>