<?php

/**
 * For Compatibility Only
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class NullView implements View
{
    public function __construct()
    {
    }

    public function render($data)
    {
        return "";
    }
}

?>
