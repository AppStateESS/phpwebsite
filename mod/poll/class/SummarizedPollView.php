<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SummarizedPollView // extends View
{
    var $poll;

    public function __construct(Poll $poll)
    {
        $this->poll = $poll;
    }

    public function show($context)
    {
        // TODO: Figure out what to do here.
        
        return '';
    }
}

?>
