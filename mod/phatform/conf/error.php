<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */


$errors = array(
PHATFORM_INVALID_NAME          => 'You may not use that name for this form element.',
PHATFORM_ZERO_OPTIONS          => 'The number of options must be a greater than zero.',
PHATFORM_VALUE_MISSING         => 'The value for this element was not set.',
PHATFORM_ASSOC_TEXT            => 'The associated text for this element was not set.',
PHATFORM_OPTION_PBL            => dgettext('phatform', 'There is a problem with options, contact a system administrator.'),
PHATFORM_OPTION_WONT_SAVE      => dgettext('phatform', 'The option set %s was unable to be saved.'),
PHATFORM_ELEMENT_FAIL          => dgettext('phatform', 'Unable to save %s.'),
PHATFORM_VALUES_NOT_SET        => 'All of the values were not set. You must fill out all of them.',
PHATFORM_VAL_OPT_NOT_SET       => 'All of the options and values were not set.  Check the box below to use your options as values.',
PHATFORM_CANNOT_DELETE_ELEMENT => 'Could not delete the element.',
PHATFORM_MISSING_FORM_NAME     => 'Missing form name.',
PHATFORM_MULTI_NOT_ALLOWED     => 'Multiple submissions with editable form data are not allowed.',
PHATFORM_ANON_NOT_ALLOWED      => 'Anonymous submissions with editable form data are not allowed.',
PHATFORM_REQUIRED_MISSING      => 'You must fill out all required fields to continue.',
PHATFORM_TEXT_MAXSIZE_PASSED   => dgettext('phatform', 'You have passed the maximum allowed characters in the %s text field.'),
PHATFORM_SUBMISSION_MISSING    => 'You must provide a submission message.',
PHATFORM_NEED_ONE_ELEMENT      => 'You must have at least one element to save this form.',
PHATFORM_POSITION_INTEGER      => 'Position must be an integer value.',
PHATFORM_INSTRUCTIONS_FORMAT   => 'Instructions are missing or formatted incorrectly.',
PHATFORM_MESSAGE_FORMAT        => dgettext('phatform', '"Thank you" message is not formatted correctly.'),
PHATFORM_ELEMENT_NOT_OBJ       => 'Form element is not an object.',
PHATFORM_ARCHIVE_PATH          => 'The archive path is not web server writable.',
PHATFORM_EXPORT_PATH           => 'The export path is not web server writable.'
);
