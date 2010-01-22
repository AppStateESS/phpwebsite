<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CastVoteCommand extends PollCommand
{
    private $value;

    public function getRequestVars()
    {
        $vars = array('action' => 'CastVote');

        if(isset($this->pollId)) {
            $vars['pollId'] = $this->pollId;
        }

        if(isset($this->value)) {
            $vars['value'] = $this->value;
        }

        return $vars;
    }

    public function setPollId($pollId)
    {
        $this->pollId = $pollId;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function execute(PollContext $context)
    {
        if(!isset($this->pollId)) {
            $this->pollId = $context->get('pollId');
        }
        if(!isset($this->value)) {
            $this->value = $context->get('value');
        }

        $pollId = $this->pollId;
        $value = $this->value;

        $poll = new Poll($pollId);
        $poll->castVote($value);

        header('HTTP/1.1 303 See Other');
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit();
    }

}

?>
