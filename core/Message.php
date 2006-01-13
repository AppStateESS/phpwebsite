<?php

  /**
   * This is a crutch file for 0.x series of phpwebsite.
   * If you are not running any 0.x modules, it may be safe to remove this file.
   * Make sure all your mods are 1.x before removing it.
   *
   * @author Steven Levin
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class PHPWS_Message {

    var $_title = NULL;
    var $_content = NULL;
    var $_contentVar = NULL;

    function PHPWS_Message($content, $contentVar, $title=NULL) {
        $this->_content = $content;
        $this->_contentVar = $contentVar;
        $this->_title = $title;
    }

    function display() {
        $messageTags = array();
        $messageTags['CONTENT'] = $this->_content;

        if(isset($this->_title)) {
            $messageTags['TITLE'] = $this->_title;
        }

        Layout::add(PHPWS_Template::process($messageTags, 'core', 'message.tpl'));
    }
  
    function isMessage($value) {
        return (is_object($value) && (strcasecmp(get_class($value), 'PHPWS_Message') == 0) || is_subclass_of($value, 'PHPWS_Message'));
    }
} // END CLASS PHPWS_Message

?>