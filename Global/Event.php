<?php

/**
 * Event is a class that is used to add Javascript events to Tags
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Event {

    public function __construct($type, $command)
    {
        $this->setType($type);
        $this->setCommand($command);
    }

    public function setType($type)
    {
        static $allowed = array('onclick', 'ondblclick', 'onmousedown', 'onmousemove',
    'onmouseover', 'onmouseout', 'onmouseup', 'onkeydown', 'onkeypress',
    'onkeyup', 'onabort', 'onload', 'onresize', 'onunload', 'onblur',
    'onchange', 'onfocus', 'onreset', 'onselect', 'onsubmit');

        if (!in_array($type, $allowed)) {
            throw new \Exception('Unknown event type');
        }

        $this->type = $type;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getType()
    {
        return $this->type;
    }

    public function __toString()
    {
        return $this->type . '="' . $this->command . '"';
    }

}

?>
