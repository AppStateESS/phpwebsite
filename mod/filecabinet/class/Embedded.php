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
    var $width  = 0;
    var $height = 0;
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

    function getFilterInfo()
    {
        static $filters = null;

        if (empty($filters)) {
            include 'config/filecabinet/embedded.php';
        }

        return @$filters[$this->etype];
    }

    function view()
    {
        $f_info = $this->getFilterInfo();

        if (empty($f_info) || !isset($f_info['template'])) {
            return null;
        }
        $embedded_tpl = 'embedded/' . $f_info['template'];
        
        $tpl['WIDTH']  = $this->width;
        $tpl['HEIGHT'] = $this->height;
        $tpl['URL']    = $this->url;
        
        return PHPWS_Template::process($tpl, 'filecabinet', $embedded_tpl);
    }

    function viewLink()
    {
        $vars['uop'] = 'view_embedded';
        $vars['embed_id'] = $this->id;

        $jsvars['width'] = $this->width + 50;
        $jsvars['height'] = $this->height + 50;
        $jsvars['address'] = PHPWS_Text::linkAddress('filecabinet', $vars);
        $jsvars['label'] = $this->title;

        return javascript('open_window', $jsvars);
    }


    function rowTags()
    {
        $links[] = 'Clip';

        $links[] = 'Edit';

        $links[] = 'Delete';

        $tpl['ACTION'] = implode(' | ', $links);
        $tpl['TITLE'] = $this->viewLink();

        return $tpl;
    }

    function save()
    {
        if ($this->width < 100 || $this->height < 100) {
            $filter = $this->getFilterInfo();
            $this->width = $filter['width'];
            $this->height = $filter['height'];
        }

        $db = new PHPWS_DB('fc_embedded');
        return $db->saveObject($this);
    }

}

?>