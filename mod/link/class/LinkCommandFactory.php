<?php

/**
 * Link Command Factory
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class LinkCommandFactory extends LinkClassLoader
{
    static function getCommand($action) {
        $class = self::staticInit($action, 'command/', 'Command');

        $cmd = new $class();
        return $cmd;
    }
}

?>
