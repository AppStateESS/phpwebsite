<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('poll', 'Poll.php');

class PollFactory
{
    public static function getByKey($key)
    {
        $db = new PHPWS_DB('poll');
        $db->addWhere('key_id', $key->id);
        $db->addOrder('question');
        $result = $db->getObjects('Poll');

        if(PHPWS_Error::logIfError($result)) {
            test($result,1);
        }

        return $result;
    }
}

?>
