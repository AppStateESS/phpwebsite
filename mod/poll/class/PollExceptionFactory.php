<?php

/**
 * Poll Exception Factory
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PollExceptionFactory extends PollClassLoader
{
    static function throwException($type, $message) {
        $class = self::staticInit($type, 'exception/', 'Exception');

        throw new $class($message);
    }
}

?>
