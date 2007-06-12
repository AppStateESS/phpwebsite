<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class Signup_Sheet {
    var $id          = 0;
    var $key_id      = 0;
    var $title       = null;
    var $description = null;
    var $start_time  = 0;
    var $end_time    = 0;
    var $_error      = null;

    function Signup_Sheet($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = new PHPWS_DB('signup_sheet');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    function getDescription()
    {
        return PHPWS_Text::parseOutput($this->description);
    }

    function getStartTime()
    {
        if (!$this->start_time) {
            return strftime('%Y%m%d %H:00', mktime());
        } else {
            return strftime('%Y%m%d %H:00', $this->start_time);
        }
    }

    function getEndTime()
    {
        if (!$this->end_time) {
            return strftime('%Y%m%d %H:00', mktime() + (86400 * 7));
        } else {
            return strftime('%Y%m%d %H:00', $this->end_time);
        }
    }

    function defaultStart()
    {
        $this->start_time = mktime() - 86400;
    }

    function defaultEnd()
    {
        $this->end_time = mktime(0,0,0,1,1,2020);
    }

    function save()
    {
        $db = new PHPWS_DB('signup_sheet');
        return $db->saveObject($this);
    }

    function rowTag()
    {
        $vars['s_id'] = $this->id;
        $vars['aop']  = 'edit_sheet';
        $tpl['ACTION'] = PHPWS_Text::secureLink(dgettext('signup', 'Edit'), 'signup', $vars);
        return $tpl;
    }
}

?>