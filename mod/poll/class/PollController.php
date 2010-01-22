<?php

/**
 * Main Controller Class for Poll module
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('poll', 'PollContext.php');
PHPWS_Core::initModClass('poll', 'PollClassLoader.php');
PHPWS_Core::initModClass('poll', 'PollCommandFactory.php');
PHPWS_Core::initModClass('poll', 'PollExceptionFactory.php');
PHPWS_Core::initModClass('poll', 'PollCommand.php');
PHPWS_Core::initModClass('poll', 'PollFactory.php');

class PollController
{
    private static $INSTANCE;

    var $context;

    private function __construct()
    {
        $this->context = new PollContext();
    }

    public function getContext()
    {
        return $this->context;
    }

    public function process()
    {
        $cmd = PollCommandFactory::getCommand($this->context->get('action'));

        $cmd->execute($this->context);
    }

    public function unregisterModule($module)
    {
        // TODO: Implement
    }

    public function miniAdmin(Key $key)
    {
        PHPWS_Core::initModClass('poll', 'PollFactory.php');
        $polls = PollFactory::getPollsByKeyId($key->id);
        foreach($polls as $poll) {
            $cmd = PollCommandFactory::getCommand('ManagePoll');
            $cmd->setPollId($poll->id);
            MiniAdmin::add('poll', $cmd->getLink("Manage {$poll->name}"));
        }

        $cmd = PollCommandFactory::getCommand('NewPoll');
        $cmd->setKeyId($key->id);
        MiniAdmin::add('poll', $cmd->getLink('New Poll'));
    }

    public static function getInstance()
    {
        if(is_null(self::$INSTANCE)) {
            self::$INSTANCE = new PollController();
        }

        return self::$INSTANCE;
    }

    public static function showPollForKey($key, $summarized = FALSE)
    {
        $polls = PollFactory::getByKey($key);
        if(empty($polls)) {
            if(Current_User::allow('poll') && !$summarized) {
                $class = PollClassLoader::staticInit('CreatePollView');
                $view = new $class($key);
                return $view->show(new PollContext());
            } else return null;
        }

        $content = '';
        foreach($polls as $poll) {
            if($summarized) {
                PHPWS_Core::initModClass('poll', 'SummarizedPollView.php');
                $pv = new SummarizedPollView($poll);
            } else {
                PHPWS_Core::initModClass('poll', 'PollView.php');
                $pv = new PollView($poll);
            }
            $content .= $pv->show(new PollContext());
        }

        return $content;
    }
}

?>
