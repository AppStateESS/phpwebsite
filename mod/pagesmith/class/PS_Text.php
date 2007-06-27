<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::initModClass('pagesmith', 'PS_Section.php');

class PS_Text extends PS_Section {

    function PS_Text($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = new PHPWS_DB('ps_text');
        $result = $db->loadObject($this);
        if (PHPWS_Error::logIfError($result)) {
            return $result;
        }
        if (!$result) {
            $this->id = 0;
            return false;
        } else {
            return true;
        }
    }

    function getSaved()
    {
        if (isset($_SESSION['PS_Page'][$this->pid][$this->secname])) {
            return $_SESSION['PS_Page'][$this->pid][$this->secname];
        }
        return null;
    }

    function setSaved()
    {
        $_SESSION['PS_Page'][$this->pid][$this->secname] = & $this->content;
    }

    function loadContent($form_mode=false)
    {
        if ($form_mode) {
            $content = $this->getSaved();
            if ($content) {
                $this->content = & $content;
            } else {
                $this->loadFiller();
                $this->setSaved();
            }
        }
    }

    function getContent()
    {
        return PHPWS_Text::parseOutput($this->content);
    }

    function loadFiller()
    {
        static $lorum = null;

        if ($this->sectype == 'header') {
            $this->content = 'Lorem ipsum dolor';
        } else {
            if (empty($lorum)) {
                $lorum = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/inc/lorum.txt');
            }
            $this->content =  PHPWS_Text::breaker($lorum);
        }
    }

    function save()
    {
        $db = new PHPWS_DB('ps_text');
        $db->saveObject($this);
    }

}

?>