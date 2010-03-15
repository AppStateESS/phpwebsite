<?php

require_once(PHPWS_SOURCE_DIR . "mod/phatform/class/Element.php");

/**
 * Dropbox item
 *
 * @version $Id$
 * @author  Steven Levin
 * @author  Adam Morton
 * @modified Darren Greene
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 * @package Phat Form
 */
class PHAT_Dropbox extends PHAT_Element {

    /**
     * PHAT_Dropbox element constructor
     *
     * A PHAT_Dropbox element can be constructed in two ways.  You can either
     * send a valid database ID to construct a PHAT_Dropbox element that already
     * exists.  Or you can pass nothing and a new PHAT_Dropbox will be created,
     * only the item table will be set.
     *
     * @param integer $id database id key for this PHAT_Dropbox
     */
    function PHAT_Dropbox($id = NULL) {
        $this->setTable('mod_phatform_dropbox');

        if(isset($id)) {
            $this->setId($id);
            $leftOvers = $this->init();
            // not expecting anything but kill it anyway :)
            unset($leftOvers);
        }
    } // END FUNC PHAT_Dropbox

    function hasOptions() {return TRUE;}

    /**
     * View this PHAT_Dropobox
     *
     * The view function provides the HTML for a user to view the PHAT_Dropbox.
     *
     * @param  mixed  $value whatever needed to match in the dropbox
     * @return string The HTML to be shown
     */
    function view($value = NULL) {
        $label = $this->getLabel();

        if(isset($_REQUEST['PHAT_' . $label])) {
            $this->setValue($_REQUEST['PHAT_' . $label]);
        }

        if($this->isRequired())
        $viewTags['REQUIRED_FLAG'] = '&#42;';

        $optionText = $this->getOptionText();
        $optionValues = $this->getOptionValues();

        for($i = 0; $i < sizeof($optionText); $i++)
        $options[$optionValues[$i]] = $optionText[$i];

        $viewTags['BLURB'] = PHPWS_Text::parseOutput($this->getBlurb());
        $element = new Form_Select('PHAT_' . $label, $options);
        $element->setMatch($this->getValue());

        $viewTags['DROPBOX'] = $element->get();

        return PHPWS_Template::process($viewTags, 'phatform', 'dropbox/view.tpl');
    } // END FUNC view

    /**
     * Edit a new or existing PHAT_Dropbox element
     *
     * The edit function provides the HTML form to edit a new or existing
     * PHAT_Dropbox element.
     *g
     * return string The HTML form to edit a PHAT_Dropbox
     */
    function edit() {
        $numOptions = sizeof($this->getOptionText());
        if(!$numOptions || $this->getOptionSet()) $numOptions='';

        $form = new PHPWS_Form;

        $form->addHidden('module', 'phatform');
        $form->addHidden('PHAT_EL_OP', 'SaveElement');

        if(!$this->getLabel()) {
            $num = $_SESSION['PHAT_FormManager']->form->numElements();
            $this->setLabel('Element' . ($num + 1));
        }

        $form->addTextArea('PHAT_ElementBlurb', $this->getBlurb());
        $form->setRows('PHAT_ElementBlurb', PHAT_DEFAULT_ROWS);
        $form->setCols('PHAT_ElementBlurb', PHAT_DEFAULT_COLS);
        $form->setLabel('PHAT_ElementBlurb', dgettext('phatform', 'Associated Text'));

        $form->addText('PHAT_ElementName', $this->getLabel());
        $form->setSize('PHAT_ElementName', PHAT_DEFAULT_SIZE, PHAT_DEFAULT_MAXSIZE);
        $form->setLabel('PHAT_ElementName', dgettext('phatform', 'Name'));

        $form->addText('PHAT_ElementNumOptions', $numOptions);
        $form->setSize('PHAT_ElementNumOptions', 5, 3);
        $form->setLabel('PHAT_ElementNumOptions', dgettext('phatform', 'Number of Options'));

        $options = $this->getOptionSets();
        if(is_array($options)) {
            $editTags['OPTION_SET_LABEL'] = dgettext('phatform', 'Predefined Option Set');
            $form->addSelect('PHAT_OptionSet', $options);
            $form->setMatch('PHAT_OptionSet', $this->getOptionSet());
        }


        $form->addCheck('PHAT_ElementRequired', 1);
        $form->setMatch('PHAT_ElementRequired', $this->isRequired());
        $form->setLabel('PHAT_ElementRequired', dgettext('phatform', 'Required'));

        $form->addSubmit('PHAT_ElementBack', dgettext('phatform', 'Back'));
        $form->addSubmit('NEXT_BUTTON',dgettext('phatform', 'Next'));

        $template = $form->getTemplate();

        return PHPWS_Template::processTemplate($template, 'phatform', 'dropbox/edit.tpl');
    } // END FUNC edit

    /**
     * Save this PHAT_Dropbox
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
            $currentError = PHPWS_Error::get(PHATFORM_INVALID_NAME, 'phatform', 'PHAT_Dropbox::save()', $_REQUEST['PHAT_ElementName']);
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
} // END CLASS PHAT_Dropbox

?>