<?php

require_once(PHPWS_SOURCE_DIR . 'mod/phatform/class/Element.php');

/**
 * Radiobutton item
 *
 * @version $Id$
 * @author  Steven Levin
 * @author  Adam Morton
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 * @package Phat Form
 */
class PHAT_Radiobutton extends PHAT_Element {

    /**
     * PHAT_Radiobutton class constructor
     *
     * @param  integer $id The id of the radiobutton to be created
     * @access public
     */
    function PHAT_Radiobutton($id = NULL)
    {
        $this->setTable('mod_phatform_radiobutton');

        if (isset($id)) {
            $this->setId($id);
            $leftOvers = $this->init();
            unset($leftOvers);
        }
    }

// END FUNC PHAT_Radiobutton

    function hasOptions()
    {
        return TRUE;
    }

    /**
     * View this PHAT_Radiobutton
     *
     * The view function provides the HTML for a user to view the PHAT_Radiobutton.
     *
     * @return string The HTML to be shown
     * @access public
     */
    function view($value = NULL)
    {
        $label = $this->getLabel();
        if (isset($_REQUEST['PHAT_' . $label])) {
            $this->setValue($_REQUEST['PHAT_' . $label]);
        }

        if ($this->isRequired()) {
            $viewTags['REQUIRED_FLAG'] = '&#42;';
        }

        $viewTags['BLURB'] = PHPWS_Text::parseOutput($this->getBlurb(),
                        ENCODE_PARSED_TEXT, false, true);
        $viewTags['RADIO_BUTTONS'] = '';

        $optionText = $this->getOptionText();
        $optionValues = $this->getOptionValues();

        for ($i = 0; $i < sizeof($optionText); $i++) {
            $option_value = $optionValues[$i];
            $element = new Form_RadioButton('PHAT_' . $label, $option_value);
            $element->setMatch($this->getValue());
            $id = preg_replace('/\W/', '', $option_value) . $i;
            $viewTags['RADIO_BUTTONS'] .=  '<div class="radio"><label>' . $element->get() . ' ' . $optionText[$i] . "</label></div>\n";
        }

        return PHPWS_Template::processTemplate($viewTags, 'phatform',
                        'radiobutton/view.tpl');
    }

// END FUNC view

    /**
     * Edit this PHAT_Radiobutton
     *
     * This function provides the HTML form to edit or create a new PHAT_Radiobutton
     *
     * @return string The HTML form for editing
     * @access public
     */
    function edit()
    {
        $numOptions = sizeof($this->getOptionText());
        if (!$numOptions)
            $numOptions = '';

        $elements[0] = PHPWS_Form::formHidden('module', 'phatform') . PHPWS_Form::formHidden('PHAT_EL_OP',
                        'SaveElement');

        if (!$this->getLabel()) {
            $num = $_SESSION['PHAT_FormManager']->form->numElements();
            $this->setLabel('Element' . ($num + 1));
        }

        if (PHAT_SHOW_INSTRUCTIONS) {
            $GLOBALS['CNT_phatform']['title'] = dgettext('phatform',
                    'Radiobutton Element Instructions');
        }

        $editTags['BLURB_LABEL'] = dgettext('phatform', 'Associated Text');
        $editTags['BLURB_INPUT'] = PHPWS_Form::formTextArea('PHAT_ElementBlurb',
                        $this->getBlurb(), PHAT_DEFAULT_ROWS, PHAT_DEFAULT_COLS);
        $editTags['NAME_LABEL'] = dgettext('phatform', 'Name');
        $editTags['NAME_INPUT'] = PHPWS_Form::formTextField('PHAT_ElementName',
                        $this->getLabel(), PHAT_DEFAULT_SIZE,
                        PHAT_DEFAULT_MAXSIZE);
        $editTags['OPTIONS_LABEL'] = dgettext('phatform', 'Number of Options');
        $editTags['OPTIONS_INPUT'] = PHPWS_Form::formTextField('PHAT_ElementNumOptions',
                        $numOptions, 5, 3);

        $options = $this->getOptionSets();
        if (is_array($options)) {
            $editTags['OPTION_SET_LABEL'] = dgettext('phatform',
                    'Predefined Option Set');
            $editTags['OPTION_SET_INPUT'] = PHPWS_Form::formSelect('PHAT_OptionSet',
                            $options, $this->getOptionSet(), FALSE, TRUE);
        }

        $editTags['REQUIRE_LABEL'] = dgettext('phatform', 'Required');
        $editTags['REQUIRE_INPUT'] = PHPWS_Form::formCheckBox('PHAT_ElementRequired',
                        1, $this->isRequired());
        $editTags['BACK_BUTTON'] = PHPWS_Form::formSubmit(dgettext('phatform',
                                'Back'), 'PHAT_ElementBack');
        $editTags['NEXT_BUTTON'] = PHPWS_Form::formSubmit(dgettext('phatform',
                                'Next'));

        $elements[0] .= PHPWS_Template::processTemplate($editTags, 'phatform',
                        'radiobutton/edit.tpl');

        return PHPWS_Form::makeForm('PHAT_RadiobuttonEdit', 'index.php',
                        $elements, 'post', NULL, NULL);
    }

// END FUNC edit

    /**
     * Save this PHAT_Radiobutton
     *
     * @return mixed  Content if going to getOptions stage, content for edit if first form not filled in properly,
     *                or PHPWS_Error on failure.
     * @access public
     */
    function save()
    {
        $error = FALSE;
        $label = $this->getLabel();
        if ((!$_SESSION['PHAT_FormManager']->form->checkLabel($_REQUEST['PHAT_ElementName']) && (strcasecmp($label,
                        $_REQUEST['PHAT_ElementName']) != 0)) || PHPWS_Error::isError($this->setLabel(PHPWS_DB::sqlFriendlyName($_REQUEST['PHAT_ElementName'])))) {
            $currentError = PHPWS_Error::get(PHATFORM_INVALID_NAME, 'phatform',
                            'PHAT_Radiobutton::save()');
            $error = TRUE;
        }

        $result = $this->setBlurb($_REQUEST['PHAT_ElementBlurb']);
        if (PHPWS_Error::isError($result)) {
            $currentError = $result;
            $error = TRUE;
        }

        if (isset($_REQUEST['PHAT_ElementRequired'])) {
            $this->setRequired(TRUE);
        } else {
            $this->setRequired(FALSE);
        }

        if ($error) {
            return $currentError;
        } else {
            if ((is_numeric($_REQUEST['PHAT_ElementNumOptions']) && ($_REQUEST['PHAT_ElementNumOptions'] > 0)) || isset($_REQUEST['PHAT_OptionSet'])) {
                return $this->getOptions();
            } else {
                return PHPWS_Error::get(PHATFORM_ZERO_OPTIONS, 'phatform',
                                'PHAT_Radiobutton::save()');
            }
        }
    }

// END FUNC save
}

// END CLASS PHAT_Radiobutton
?>