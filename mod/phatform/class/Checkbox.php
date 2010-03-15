<?php

require_once(PHPWS_SOURCE_DIR . 'mod/phatform/class/Element.php');

/**
 * Checkbox item
 *
 * @version $Id$
 * @author  Steven Levin
 * @author  Adam Morton
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 * @package Phat Form
 */
class PHAT_Checkbox extends PHAT_Element {

    /**
     * PHAT_Checkbox class constructor
     *
     * @param  integer $id The id of the checkbox to be created
     * @access public
     */
    function PHAT_Checkbox($id = NULL) {
        $this->setTable('mod_phatform_checkbox');

        if(isset($id)) {
            $this->setId($id);
            $leftOvers = $this->init();
        }
    } // END FUNC PHAT_Checkbox

    function hasOptions() {return TRUE;}

    /**
     * View this PHAT_Checkbox
     *
     * @return string The HTML content to view the PHAT_Checkbox
     * @access public
     */
    function view() {
        $label = $this->getLabel();
        if(isset($_REQUEST['PHAT_' . $label]) && is_array($_REQUEST['PHAT_' . $label])) {
            $this->setValue($_REQUEST['PHAT_' . $label]);
        }

        if($this->isRequired())
        $viewTags['REQUIRED_FLAG'] = '&#42;';

        $viewTags['BLURB'] = $this->getBlurb();
        $viewTags['CHECK_BOXES'] = '';

        $optionText = $this->getOptionText();
        $optionValues = $this->getOptionValues();
        $value = $this->getValue();

        for($i = 0; $i < sizeof($optionText); $i++)
        $viewTags['CHECK_BOXES'] .= PHPWS_Form::formCheckBox('PHAT_' . $label . '[' . $i . ']', $optionValues[$i], $value[$i], NULL, $optionText[$i]) . "<br />\n";

        return PHPWS_Template::processTemplate($viewTags, 'phatform', 'checkbox/view.tpl');
    } // END FUNC view

    /**
     * Edit this PHAT_Checkbox
     *
     * This function provides the HTML form to edit or create a new PHAT_Checkbox
     *
     * @return string The HTML form for editing
     * @access public
     */
    function edit() {
        $numOptions = sizeof($this->getOptionText());
        if(!$numOptions || $this->getOptionSet()) $numOptions='';

        $elements[0] = PHPWS_Form::formHidden('module', 'phatform') . PHPWS_Form::formHidden('PHAT_EL_OP', 'SaveElement');

        if(!$this->getLabel()) {
            $num = $_SESSION['PHAT_FormManager']->form->numElements();
            $this->setLabel('Element' . ($num + 1));
        }

        if(PHAT_SHOW_INSTRUCTIONS) {
            $GLOBALS['CNT_phatform']['title'] = dgettext('phatform', 'Checkbox Element Instructions');
        }

        $editTags['BLURB_LABEL'] = dgettext('phatform', 'Associated Text');
        $editTags['BLURB_INPUT'] = PHPWS_Form::formTextArea('PHAT_ElementBlurb', $this->getBlurb(), PHAT_DEFAULT_ROWS, PHAT_DEFAULT_COLS);
        $editTags['NAME_LABEL'] = dgettext('phatform', 'Name');
        $editTags['NAME_INPUT'] = PHPWS_Form::formTextField('PHAT_ElementName', $this->getLabel(), PHAT_DEFAULT_SIZE, PHAT_DEFAULT_MAXSIZE);
        $editTags['OPTIONS_LABEL'] = dgettext('phatform', 'Number of Options');
        $editTags['OPTIONS_INPUT'] = PHPWS_Form::formTextField('PHAT_ElementNumOptions', $numOptions, 5, 3);

        $options = $this->getOptionSets();
        if(is_array($options)) {
            $editTags['OPTION_SET_LABEL'] = dgettext('phatform', 'Predefined Option Set');
            $editTags['OPTION_SET_INPUT'] = PHPWS_Form::formSelect('PHAT_OptionSet', $options, $this->getOptionSet(), FALSE, TRUE);
        }

        $editTags['REQUIRE_LABEL'] = dgettext('phatform', 'Required');
        $editTags['REQUIRE_INPUT'] = PHPWS_Form::formCheckBox('PHAT_ElementRequired', 1, $this->isRequired());
        $editTags['BACK_BUTTON'] = PHPWS_Form::formSubmit(dgettext('phatform', 'Back'), 'PHAT_ElementBack');
        $editTags['NEXT_BUTTON'] = PHPWS_Form::formSubmit(dgettext('phatform', 'Next'));

        $elements[0] .= PHPWS_Template::processTemplate($editTags, 'phatform', 'checkbox/edit.tpl');

        return PHPWS_Form::makeForm('PHAT_CheckboxEdit', 'index.php', $elements, 'post', NULL, NULL);
    } // END FUNC edit

    /**
     * Save this PHAT_Checkbox
     *
     * @return mixed  Content if going to getOptions stage, content for edit if first form not filled in properly,
     *                or PHPWS_Error on failure.
     * @access public
     */
    function save() {
        $error = FALSE;
        $label = $this->getLabel();
        if((!$_SESSION['PHAT_FormManager']->form->checkLabel($_REQUEST['PHAT_ElementName']) && (strcasecmp($label, $_REQUEST['PHAT_ElementName']) != 0))
        || PEAR::isError($this->setLabel(PHPWS_DB::sqlFriendlyName($_REQUEST['PHAT_ElementName'])))) {

            $message = dgettext('phatform', 'The name you entered for the Checkbox is not valid or is already in use with this form.');
            $currentError = PHPWS_Error::get(PHATFORM_INVALID_NAME, 'phatform', 'PHAT_Checkbox::save()', $_REQUEST['PHAT_ElementName']);
            $error = TRUE;
        }

        $result = $this->setBlurb($_REQUEST['PHAT_ElementBlurb']);
        if(PEAR::isError($result)) {
            $currentError = $result;
            $error = TRUE;
        }

        if(isset($_REQUEST['PHAT_ElementRequired'])) {
            $this->setRequired(TRUE);
        } else {
            $this->setRequired(FALSE);
        }

        if($error) {
            return $currentError;
        } else {
            if((is_numeric($_REQUEST['PHAT_ElementNumOptions']) && ($_REQUEST['PHAT_ElementNumOptions'] > 0)) || isset($_REQUEST['PHAT_OptionSet'])) {
                return $this->getOptions();
            } else {
                return PHPWS_Error::get(PHATFORM_ZERO_OPTIONS, 'phatform', 'PHAT_Checkbox::save()');
            }
        }
    } // END FUNC save
} // END CLASS PHAT_Checkbox

?>