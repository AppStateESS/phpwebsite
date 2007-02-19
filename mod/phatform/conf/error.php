<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

translate('phatform');
$errors = array(
                PHATFORM_INVALID_NAME          => _('You may not use that name for this form element.'),
                PHATFORM_ZERO_OPTIONS          => _('The number of options must be a greater than zero.'),
                PHATFORM_VALUE_MISSING         => _('The value for this element was not set.'),
                PHATFORM_ASSOC_TEXT            => _('The associated text for this element was not set.'),
                PHATFORM_OPTION_PBL            => _('There is a problem with options, contact a system administrator.'),
                PHATFORM_OPTION_WONT_SAVE      => _('The option set %s was unable to be saved.'),
                PHATFORM_ELEMENT_FAIL          => _('Unable to save %s.'),
                PHATFORM_VALUES_NOT_SET        => _('All of the values were not set. You must fill out all of them.'),
                PHATFORM_VAL_OPT_NOT_SET       => _('All of the options and values were not set.  Check the box below to use your options as values.'),
                PHATFORM_CANNOT_DELETE_ELEMENT => _('Could not delete the element.'),
                PHATFORM_MISSING_FORM_NAME     => _('Missing form name.'),
                PHATFORM_MULTI_NOT_ALLOWED     => _('Multiple submissions with editable form data are not allowed.'),
                PHATFORM_ANON_NOT_ALLOWED      => _('Anonymous submissions with editable form data are not allowed.'),
                PHATFORM_REQUIRED_MISSING      => _('You must fill out all required fields to continue.'),
                PHATFORM_TEXT_MAXSIZE_PASSED   => _('You have passed the maximum allowed characters in the %s text field.'),
                PHATFORM_SUBMISSION_MISSING    => _('You must provide a submission message.'),
                PHATFORM_NEED_ONE_ELEMENT      => _('You must have at least one element to save this form.'),
                PHATFORM_POSITION_INTEGER      => _('Position must be an integer value.'),
                PHATFORM_INSTRUCTIONS_FORMAT   => _('Instructions are missing or formatted incorrectly.'),
                PHATFORM_MESSAGE_FORMAT        => _('"Thank you" message is not formatted correctly.'),
                PHATFORM_ELEMENT_NOT_OBJ       => _('Form element is not an object.'),
                PHATFORM_ARCHIVE_PATH          => _('The archive path is not web server writable.'),
                PHATFORM_EXPORT_PATH           => _('The export path is not web server writable.')
                );
translate();

?>