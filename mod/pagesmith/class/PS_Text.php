<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::initModClass('pagesmith', 'PS_Section.php');

class PS_Text extends PS_Section {

    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    public function init()
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


    public function setSaved()
    {
        if (!PageSmith::checkLorum($this->content)) {
            $_SESSION['PS_Page'][$this->pid][$this->secname] = & $this->content;
        }
    }

    public function loadFiller()
    {
        static $lorum = null;

        if ($this->sectype == 'header') {
            $this->content = '<!-- lorem -->Lorem ipsum dolor';
        } else {
            if (empty($lorum)) {
                $lorum = file_get_contents(PHPWS_SOURCE_DIR . 'mod/pagesmith/inc/lorum.txt');
            }
            $this->content =  PHPWS_Text::breaker($lorum);
        }
        $this->setSaved();
    }

    public function loadSaved()
    {
        if (isset($_SESSION['PS_Page'][$this->pid][$this->secname])) {
            $this->content = $_SESSION['PS_Page'][$this->pid][$this->secname];
            return true;
        } else {
            return false;
        }
    }

    public function getContent($view_mode=true)
    {
        if (empty($this->content)) {
            return null;
        }

        if ($view_mode) {
            return PHPWS_Text::parseTag(PHPWS_Text::parseOutput($this->content));
        } else {
            return PHPWS_Text::decodeText($this->content);
            /**
             * Prior to 24 Mar 09, this was what it returned. This prevented anchors
             * and filtered words in edit mode. Although testing the change does not
             * indicate side effects, I am leaving this in just case. -Matt
             */
            //return PHPWS_Text::parseOutput($this->content);
        }
    }


    public function save($key_id)
    {
        $db = new PHPWS_DB('ps_text');
        $result = $db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }
        $search = new Search($key_id);
        $search->addKeywords($this->content);
        return $search->save();
    }

}

?>