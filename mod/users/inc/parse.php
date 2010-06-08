<?php

/**
 * @author Matthew McNaney
 * @version $Id$
 */

function new_account($item)
{
    if (!PHPWS_User::getUserSetting('new_user_method') > 0) {

        $msg = dgettext('users', 'New user signup is currently disabled.');
        return $msg;
    }
    $signup_vars = array('action'  => 'user',
                         'command' => 'signup_user');
    if (!empty($item[1])) {
        $link = strip_tags($item[1]);
    } else {
        $link = USER_SIGNUP_QUESTION;
    }

    return \core\Text::moduleLink($link, 'users', $signup_vars);
}

?>