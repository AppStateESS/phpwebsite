<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class Poll {
    var $id;
    var $key_id;
    var $question;
    var $response1;
    var $response2;
    var $creator;
    var $created;
    var $count1 = 0;
    var $count2 = 0;
    var $last_vote_time = null;

    public function __construct($id = NULL)
    {
        if(!is_null($id)) {
            $this->id = $id;
            $this->init();
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('poll');
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)) {
            test($result,1);
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('poll');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)) {
            test($result,1);
        }

        return $this->id;
    }

    public function castVote($value)
    {
        if($value != 1 && $value != 2) {
            test('Illegal Value', 1);
            // TODO: Throw Exception
        }

        // TODO: Make sure this IP has not voted before
        // TODO: Create new poll_vote record
        
        if($value == 1) {
            $this->count1++;
        } else if($value == 2) {
            $this->count2++;
        }

        $this->setLastVoteTime(mktime());
        $this->save();
    }

    public function getPercentage()
    {
        if($this->count1 + $this->count2 == 0) {
            return .5;
        }
        return $this->count2 / ($this->count1 + $this->count2);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getKeyId()
    {
        return $this->key_id;
    }

    public function setKeyId($key_id)
    {
        $this->key_id = $key_id;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion($question)
    {
        $this->question = $question;
    }

    public function getResponse1()
    {
        return $this->response1;
    }

    public function setResponse1($value)
    {
        $this->response1 = $value;
    }

    public function getResponse1Link($text = NULL)
    {
        $command = PollCommandFactory::getCommand('CastVote');
        $command->setPollId($this->getId());
        $command->setValue(1);
        if(is_null($text)) { $text = $this->getResponse1(); }
        return $command->getLink($text);
    }

    public function getResponse2()
    {
        return $this->response2;
    }

    public function setResponse2($value)
    {
        $this->response2 = $value;
    }

    public function getResponse2Link($text = NULL)
    {
        $command = PollCommandFactory::getCommand('CastVote');
        $command->setPollId($this->getId());
        $command->setValue(2);
        if(is_null($text)) { $text = $this->getResponse2(); }
        return $command->getLink($text);
    }

    public function getRange()
    {
        return $this->range;
    }

    public function setRange($range)
    {
        $this->range = $range;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }

    public function getCount1()
    {
        return $this->count1;
    }

    public function setCount1($value)
    {
        $this->count1 = value;
    }

    public function getCount2()
    {
        return $this->count2;
    }

    public function setCount2($value)
    {
        $this->count2 = value;
    }

    public function getLastVoteTime()
    {
        return $this->last_vote_time;
    }

    public function setLastVoteTime($last_vote_time)
    {
        $this->last_vote_time = $last_vote_time;
    }

    public function getFormattedLastVoteTime()
    {
        if(is_null($this->getLastVoteTime())) {
            return dgettext('poll', 'No votes cast.');
        }

        return date('d M Y', $this->getLastVoteTime());
    }
}

?>
