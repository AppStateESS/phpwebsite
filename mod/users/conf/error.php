<?php

translate('users');
$errors = array(
		USER_ERROR                => _('Unknown error.'),
		USER_ERR_DUP_USERNAME     => _('User with this name already exists.'),
		USER_ERR_DUP_EMAIL        => _('User with this email already exists.'),
		USER_ERR_DUP_GROUPNAME    => _('A group with this name already exists.'),
		USER_ERR_PERM_TABLE       => _('Permission table name already exists.'),
		USER_ERR_PERM_MISS        => _('Permission table not found.'),
		USER_ERR_PERM_FILE        => _('Module\'s permission file is missing.'),
		USER_ERR_ITEM_PERM_FILE   => _('Module\'s item permission file is missing.'),
		USER_ERR_BAD_USERNAME     => _('Username is improperly formatted.'),
		USER_ERR_BAD_DISPLAY_NAME => _('Display name is improperly formatted.'),
		USER_ERR_PASSWORD_MATCH   => _('Passwords do not match.'),
		USER_ERR_PASSWORD_LENGTH  => sprintf(_('Password must be at least %s characters in length.'), PASSWORD_LENGTH),
		USER_ERR_PASSWORD_EASY    => _('Password is too easy to guess.'),
		USER_ERR_NO_MODULE        => _('Module does not exist.'),
		USER_ERR_BAD_VAR          => _('Invalid variable name.'),
		USER_ERR_USER_NOT_SAVED   => _('Unable to save user information.'),
		USER_ERR_BAD_GROUP_NAME   => _('Improperly formatted group name.'),
		USER_ERR_GROUP_DNE        => _('Group does not exist.'),
		USER_ERR_MISSING_GROUP    => _('User is missing their permission group'),
		USER_ERR_FAIL_ON_SUBPERM  => _('Module tried to detect a non-existant permission.'),
		USER_ERR_NO_EMAIL         => _('User\'s email address is missing.'),
		USER_ERR_BAD_EMAIL        => _('User\'s email address is missing or malformed.'),
		USER_ERR_MISSING_AUTH     => _('Missing authorization script.'),
		USER_ERR_FONT_MISSING     => _('Unable to find font file for graphic confirmation.'),
		USER_ERR_WRITE_CONFIRM    => _('Unable to write confirmation graphic to server.')
		);

?>