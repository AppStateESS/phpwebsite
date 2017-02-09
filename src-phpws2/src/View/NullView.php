<?php

namespace phpws2\View;

/**
 * For Compatibility Only
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class NullView implements \phpws2\View
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
