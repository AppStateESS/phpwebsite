<?php
/**
 * Class for individual pages within volumes
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class Webpage_Page {
    var $id          = 0;
    // Id of volume page belongs to
    var $volume_id   = 0;
    var $content     = 0;
    var $page_number = 1;
    var $template    = NULL;
    var $_error      = NULL;
    var $_db         = NULL;

    function Webpage_Page($id=NULL)
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