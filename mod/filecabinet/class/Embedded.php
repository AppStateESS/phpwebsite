<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class FC_Embedded {
    var $id     = 0;
    var $title  = null;
    var $url    = null;
    var $etype  = null;
    var $_error = null;

    function FC_Embedded($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        if (!$this->id) {
            return;
        }

        $db = new PHPWS_DB('fc_embedded');
        $result = $db->loadObject($this);
        if (PHPWS_Error::logIfError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        }
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title, '<b><i><strong><em>');
    }

    function setWidth($width)
    {
        $this->width = (int)$width;
    }

    function setHeight($height)
    {
        $this->height = (int)$height;
    }

    function setUrl($url)
    {
        $this->url = trim(strip_tags($url));
    }

    function get()
    {
        static $filters = null;

        if (empty($filters)) {
            include 'config/filecabinet/embedded.php';
        }

        $f_info = @$filters[$this->etype];

        if (empty($f_info) || !isset($f_info['template'])) {
            return null;
        }
        $embedded_tpl = 'embedded/' . $f_info['template'];
        
        $tpl['WIDTH']  = $this->width;
        $tpl['HEIGHT'] = $this->height;
        $tpl['URL']    = $this->url;
        
        return PHPWS_Template::process($tpl, 'filecabinet', $embedded_tpl);
    }

    function save()
    {
        $db = new PHPWS_DB('fc_embedded');
        return $db->saveObject($this);
    }

}

?>