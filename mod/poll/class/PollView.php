<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PollView // extends View
{
    var $poll;

    public function __construct(Poll $poll)
    {
        $this->poll = $poll;
    }

    public function selectImage($poll)
    {
        $script = 'mod/poll/class/PieChart.php?';
        $script .= 'b=' . $poll->getCount1();
        $script .= '&';
        $script .= 'r=' . $poll->getCount2();
        return $script;

        $path = 'images/mod/poll/scale_';
        // TODO: Make this smarter
        if($percentage < .16) {
            return $path . '0-15.png';
        } else if($percentage < .31) {
            return $path . '16-30.png';
        } else if($percentage < .45) {
            return $path . '31-44.png';
        } else if($percentage < .56) {
            return $path . '45-55.png';
        } else if($percentage < .71) {
            return $path . '56-70.png';
        } else if($percentage < .85) {
            return $path . '71-84.png';
        }
        return $path . '85-100.png';
    }

    public function show($context)
    {
        Layout::addStyle('poll', 'style.css');
        $poll = $this->poll;

        $tpl = array();
        $tpl['QUESTION'] = $poll->getQuestion();
        $tpl['GRAPHIC'] = '<img src="' . self::selectImage($poll) . '" />';

        $response = array();
        $response['RESPONSE'] = $poll->getResponse1();
        $response['RESPONSE_VOTE'] = $poll->getResponse1Link('<img src="images/mod/poll/vote_blue.jpg" alt="Vote" />');
        //$response['RESPONSE_VALUE'] = $poll->getPercentage1();
        $tpl['RESPONSES'][] = $response;

        $response = array();
        $response['RESPONSE'] = $poll->getResponse2();
        $response['RESPONSE_VOTE'] = $poll->getResponse2Link('<img src="images/mod/poll/vote_red.jpg" alt="Vote" />');
        //$response['RESPONSE_VALUE'] = $poll->getPercentage2();
        $tpl['RESPONSES'][] = $response;

        return PHPWS_Template::process($tpl, 'poll', 'PollView.tpl');
    }

}

?>
