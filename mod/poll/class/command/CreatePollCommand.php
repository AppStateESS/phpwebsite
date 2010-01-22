<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CreatePollCommand extends PollCommand
{
    private $key_id;

    public function getRequestVars()
    {
        $vars = array('action' => 'CreatePoll');

        if(isset($this->key_id)) {
            $vars['key_id'] = $this->key_id;
        }

        return $vars;
    }

    public function setKeyId($key_id)
    {
        $this->key_id = $key_id;
    }

    public function execute(PollContext $context)
    {
        if(!isset($this->key_id)) {
            $this->key_id = $context->get('key_id');
        }

        $key_id = $this->key_id;
        $question = $context->get('question');
        $response1 = $context->get('response_1');
        $response2 = $context->get('response_2');

        $errors = array();

        if(empty($question)) {
            $errors[] = dgettext('poll', 'Please ask a question.');
        }
        if(empty($response1) || empty($response2)) {
            $errors[] = dgettext('poll', 'Please label both responses.');
        }

        if(empty($errors)) {
            $poll = new Poll();
            $poll->setKeyId($key_id);
            $poll->setQuestion($question);
            $poll->setResponse1($response1);
            $poll->setResponse2($response2);
            $poll->setCreator(Current_User::getUsername());
            $poll->setCreated(mktime());
            $poll->save();
        }

        header('HTTP/1.1 303 See Other');
        header('Location: '.$_SERVER['HTTP_REFERER']);
        exit();
    }
}

?>
