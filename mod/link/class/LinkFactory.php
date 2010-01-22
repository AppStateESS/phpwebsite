<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('link', 'Link.php');

class LinkFactory
{
    public static function getByKey($key)
    {
        $db = new PHPWS_DB('link');
        $db->addWhere('key_id', $key->id);
        $db->addOrder('placement');
        $result = $db->getObjects('Link');

        if(PHPWS_Error::logIfError($result)) {
            test($result,1);
        }

        return $result;
    }
}

?>
