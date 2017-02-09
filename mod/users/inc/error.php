<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id: error.php 5472 2007-12-11 16:13:40Z jtickle $
 */

$errors = array(
USER_ERROR                => 'Unknown error.',
USER_ERR_DUP_USERNAME     => 'User or display name already in use. Please try another.',
USER_ERR_DUP_EMAIL        => 'Email address already in use.',
USER_ERR_DUP_GROUPNAME    => 'A group with this name already exists.',
USER_ERR_PERM_TABLE       => 'Permission table name already exists.',
USER_ERR_PERM_MISS        => 'Permission table not found.',
USER_ERR_PERM_FILE        => dgettext('users', 'Module\'s permission file is missing.'),
USER_ERR_ITEM_PERM_FILE   => dgettext('users', 'Module\'s item permission file is missing.'),
USER_ERR_BAD_USERNAME     => 'Username is improperly formatted.',
USER_ERR_BAD_DISPLAY_NAME => 'Display name is improperly formatted.',
USER_ERR_PASSWORD_MATCH   => 'Passwords do not match.',
USER_ERR_PASSWORD_LENGTH  => sprintf(dgettext('users', 'Password must be at least %s characters in length.'), PASSWORD_LENGTH),
USER_ERR_PASSWORD_EASY    => 'Password is too easy to guess.',
USER_ERR_NO_MODULE        => 'Module does not exist.',
USER_ERR_BAD_VAR          => 'Invalid variable name.',
USER_ERR_USER_NOT_SAVED   => 'Unable to save user information.',
USER_ERR_BAD_GROUP_NAME   => 'Improperly formatted group name.',
USER_ERR_GROUP_DNE        => 'Group does not exist.',
USER_ERR_MISSING_GROUP    => 'User is missing their permission group',
USER_ERR_FAIL_ON_SUBPERM  => dgettext('users', 'Module tried to detect a non-existent permission.'),
USER_ERR_NO_EMAIL         => dgettext('users', 'User\'s email address is missing.'),
USER_ERR_BAD_EMAIL        => dgettext('users', 'User\'s email address is missing or malformed.'),
USER_ERR_MISSING_AUTH     => 'Missing authorization script.',
USER_ERR_FONT_MISSING     => 'Unable to find font file for graphic confirmation.',
USER_ERR_WRITE_CONFIRM    => 'Unable to write confirmation graphic to server.',
USER_MISSING_MY_PAGE      => 'Missing my_page function.',
USER_BAD_CHARACTERS       => 'Mistyped username.',
USER_AUTH_MISSING         => 'There is a problem with your authentication method. Contact the site administrator.',
USER_NOT_APPROVED         => 'Your user account has not been approved yet. Make sure you have responded to your authentication email.',
USER_NOT_ACTIVE           => 'Your account has been deactivated. You will need to contact a site administrator to have it reactivated.',
USER_BAD_KEY              => 'Unable to load key.',
USER_NOT_FOUND            => 'Could not find user.',
USER_DEACTIVATED          => 'User account deactivated.',
USER_PASSWORD_BLANK       => 'Password was blank'
);
