<?php
translate("users");
$errors = array(
		USER_ERROR                => _("Unknown error."),
		USER_ERR_DUP_USERNAME     => _("An user with this name already exists."),
		USER_ERR_DUP_GROUPNAME    => _("A group with this name already exists."),
		USER_ERR_PERM_TABLE       => _("Permission table name already exists."),
		USER_ERR_PERM_MISS        => _("Permission table not found."),
		USER_ERR_PERM_FILE        => _("Module's permission file is missing."),
		USER_ERR_BAD_USERNAME     => _("Username is improperly formatted."),
		USER_ERR_PASSWORD_MATCH   => _("Passwords do not match."),
		USER_ERR_PASSWORD_LENGTH  => _print(_("Password must be at least [var1] characters in length."), array(PASSWORD_LENGTH)),
		USER_ERR_PASSWORD_EASY    => _("Password is too easy to guess."),
		USER_ERR_LABEL_NOT_FOUND  => _("Demographic label not found."),
		USER_ERR_UNKNOWN_INPUT    => _("Unknown input type."),
		USER_ERR_NO_MODULE        => _("Module does not exist."),
		USER_ERR_BAD_VAR          => _("Invalid variable name."),
		USER_ERR_USER_NOT_SAVED   => _("Unable to save user information.")
		);

?>