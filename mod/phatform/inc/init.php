<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

if(isset($_REQUEST['module']) && $_REQUEST['module'] == 'phatform') {
    PHPWS_Core::initModClass('phatform', 'Form.php');
    PHPWS_Core::initModClass('phatform', 'FormManager.php');
    PHPWS_Core::initModClass('phatform', 'Report.php');
    PHPWS_Core::initModClass('phatform', 'Element.php');
    PHPWS_Core::initModClass('phatform', 'Checkbox.php');
    PHPWS_Core::initModClass('phatform', 'Dropbox.php');
    PHPWS_Core::initModClass('phatform', 'Multiselect.php');
    PHPWS_Core::initModClass('phatform', 'Radiobutton.php');
    PHPWS_Core::initModClass('phatform', 'Textarea.php');
    PHPWS_Core::initModClass('phatform', 'Textfield.php');

    /**
     * error definitions 
     */

    define('PHATFORM_INVALID_NAME',          101);
    define('PHATFORM_ZERO_OPTIONS',          102);
    define('PHATFORM_VALUE_MISSING',         103);
    define('PHATFORM_ASSOC_TEXT',            104);
    define('PHATFORM_OPTION_PBL',            105);
    define('PHATFORM_OPTION_WONT_SAVE',      106);
    define('PHATFORM_ELEMENT_FAIL',          107);
    define('PHATFORM_VALUES_NOT_SET',        108);
    define('PHATFORM_VAL_OPT_NOT_SET',       109);
    define('PHATFORM_CANNOT_DELETE_ELEMENT', 110);
    define('PHATFORM_MISSING_FORM_NAME',     111);
    define('PHATFORM_MULTI_NOT_ALLOWED',     112);
    define('PHATFORM_ANON_NOT_ALLOWED',      113);
    define('PHATFORM_REQUIRED_MISSING',      114);
    define('PHATFORM_TEXT_MAXSIZE_PASSED',   115);
    define('PHATFORM_SUBMISSION_MISSING',    116);
    define('PHATFORM_NEED_ONE_ELEMENT',      117);
    define('PHATFORM_POSITION_INTEGER',      118);
    define('PHATFORM_INSTRUCTIONS_FORMAT',   119);
    define('PHATFORM_MESSAGE_FORMAT',        120);
    define('PHATFORM_ELEMENT_NOT_OBJ',       121);
    define('PHATFORM_ARCHIVE_PATH',          122);
    define('PHATFORM_EXPORT_PATH',           123);

}

?>
