<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id: error.php 5472 2007-12-11 16:13:40Z jtickle $
 */

$errors = array(
USER_ERROR                => dgettext('users', 'Unknown error.'),
USER_ERR_DUP_USERNAME     => dgettext('users', 'User or display name off limits. Please try another.'),
USER_ERR_DUP_EMAIL        => dgettext('users', 'Email address already in use.'),
USER_ERR_DUP_GROUPNAME    => dgettext('users', 'A group with this name already exists.'),
USER_ERR_PERM_TABLE       => dgettext('users', 'Permission table name already exists.'),
USER_ERR_PERM_MISS        => dgettext('users', 'Permission table not found.'),
USER_ERR_PERM_FILE        => dgettext('users', 'Module\'s permission file is missing.'),
USER_ERR_ITEM_PERM_FILE   => dgettext('users', 'Module\'s item permission file is missing.'),
USER_ERR_BAD_USERNAME     => dgettext('users', 'Username is improperly formatted.'),
USER_ERR_BAD_DISPLAY_NAME => dgettext('users', 'Display name is improperly formatted.'),
USER_ERR_PASSWORD_MATCH   => dgettext('users', 'Passwords do not match.'),
USER_ERR_PASSWORD_LENGTH  => sprintf(dgettext('users', 'Password must be at least %s characters in length.'), PASSWORD_LENGTH),
USER_ERR_PASSWORD_EASY    => dgettext('users', 'Password is too easy to guess.'),
USER_ERR_NO_MODULE        => dgettext('users', 'Module does not exist.'),
USER_ERR_BAD_VAR          => dgettext('users', 'Invalid variable name.'),
USER_ERR_USER_NOT_SAVED   => dgettext('users', 'Unable to save user information.'),
USER_ERR_BAD_GROUP_NAME   => dgettext('users', 'Improperly formatted group name.'),
USER_ERR_GROUP_DNE        => dgettext('users', 'Group does not exist.'),
USER_ERR_MISSING_GROUP    => dgettext('users', 'User is missing their permission group'),
USER_ERR_FAIL_ON_SUBPERM  => dgettext('users', 'Module tried to detect a non-existent permission.'),
USER_ERR_NO_EMAIL         => dgettext('users', 'User\'s email address is missing.'),
USER_ERR_BAD_EMAIL        => dgettext('users', 'User\'s email address is missing or malformed.'),
USER_ERR_MISSING_AUTH     => dgettext('users', 'Missing authorization script.'),
USER_ERR_FONT_MISSING     => dgettext('users', 'Unable to find font file for graphic confirmation.'),
USER_ERR_WRITE_CONFIRM    => dgettext('users', 'Unable to write confirmation graphic to server.'),
USER_MISSING_MY_PAGE      => dgettext('users', 'Missing my_page function.'),
USER_BAD_CHARACTERS       => dgettext('users', 'Mistyped username.'),
USER_AUTH_MISSING         => dgettext('users', 'There is a problem with your authentication method. Contact the site administrator.'),
USER_NOT_APPROVED         => dgettext('users', 'Your user account has not been approved yet. Make sure you have responded to your authentication email.'),
USER_NOT_ACTIVE           => dgettext('users', 'Your account has been deactivated. You will need to contact a site administrator to have it reactivated.'),
USER_BAD_KEY              => dgettext('users', 'Unable to load key.'),
USER_NOT_FOUND            => dgettext('users', 'Could not find user.'),
USER_DEACTIVATED          => dgettext('users', 'User account deactivated.'),
USER_PASSWORD_BLANK       => dgettext('users', 'Password was blank')
);
?>