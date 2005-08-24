<?php
/**
 * Local authorization script
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
function authorize($username, $password){
    $db = & new PHPWS_DB("user_authorization");
    $db->addWhere("username", strtolower(preg_replace("/\W/", "", $username)));
    $db->addWhere("password", md5($username . $password));
    $result = $db->select("one");
  
    if (PEAR::isError($result)) {
        return $result;
    } else {
        return isset($result);
    }
}

?>
