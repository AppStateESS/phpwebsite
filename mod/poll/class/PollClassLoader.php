<?php

/**
 * Poll Closs Loader
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PollClassLoader
{
    static function staticInit($name, $dir='', $type='') {
        if(preg_match('/\W/', $name)) {
            PHPWS_Core::initModClass('poll', 'exception/IllegalClassException.php');
            throw new IllegalClassException("Illegal characters in class {$name}");
        }

        $class = $name . $type;

        $found = PHPWS_Core::initModClass('poll', "{$dir}{$class}.php");
        if(!$found) {
            PHPWS_Core::initModClass('poll', 'exception/IllegalClassException.php');
            throw new IllegalClassException("Could not initialize class {$name}");
        }

        return $class;
    }
}

?>
