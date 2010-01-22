<?php

/**
 * Poll Command Factory
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PollCommandFactory extends PollClassLoader
{
    static function getCommand($action) {
        $class = self::staticInit($action, 'command/', 'Command');

        $cmd = new $class();
        return $cmd;
    }
}

?>
