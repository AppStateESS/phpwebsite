<?php

namespace View;

/**
 * For Compatibility Only
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class NullView implements \View
{
    public function __construct()
    {
    }

    public function render()
    {
        return "";
    }

    public function getContentType()
    {
        return 'text/html';
    }
}

?>
