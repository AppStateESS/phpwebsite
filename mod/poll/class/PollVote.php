<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

public class PollVote
{
    var $id;
    var $poll_id;
    var $ip;
    var $user_id;
    var $value;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getPollId()
    {
        return $this->poll_id;
    }

    public function setPollId($poll)
    {
        if($poll instanceof Poll) {
            $this->poll_id = $poll->id;
        } else {
            $this->poll_id = $poll;
        }
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        // TODO: Store an IP string as an integer
        $this->ip = $ip;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}

?>
