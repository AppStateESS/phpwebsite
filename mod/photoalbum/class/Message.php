<?php

/**
 * This is a crutch file for 0.x series of phpwebsite.
 * If you are not running any 0.x modules, it may be safe to remove this file.
 * Make sure all your mods are 1.x before removing it.
 *
 * @author Steven Levin
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id: Message.php 5472 2007-12-11 16:13:40Z jtickle $
 */

class PHPWS_Message {

    private $_title = NULL;
    private $_content = NULL;
    private $_contentVar = NULL;

    public function PHPWS_Message($content, $contentVar, $title=NULL) {
        $this->_content = $content;
        $this->_contentVar = $contentVar;
        $this->_title = $title;
    }

    public function display() {
        $messageTags = array();
        $messageTags['CONTENT'] = $this->_content;

        if(isset($this->_title)) {
            $messageTags['TITLE'] = $this->_title;
        }

        Layout::add(PHPWS_Template::process($messageTags, 'core', 'message.tpl'));
    }

    public function isMessage($value) {
        return (is_object($value) && (strcasecmp(get_class($value), 'PHPWS_Message') == 0) || is_subclass_of($value, 'PHPWS_Message'));
    }
} // END CLASS PHPWS_Message


function Old_Error($module, $funcName, $message)
{
    $this->crutch_info['module']  = $module;
    $this->crutch_info['func']    = $funcName;
    $this->crutch_info['message'] = $message;
}

?>