<?php

require_once(PHPWS_SOURCE_DIR . 'mod/phatform/class/Element.php');

/**
 * Textarea item
 *
 * @version $Id$
 * @author  Steven Levin
 * @author  Adam Morton
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 * @package Phat Form
 */
class PHAT_Textarea extends PHAT_Element {

    /**
     * row attribute for textarea element
     *
     * @var     integer
     * @example $this->_rows = 8;
     * @access  private
     */
    var $_rows = NULL;

    /**
     * cols attribute for textarea element
     *
     * @var     integer
     * @example $this->_cols = 40;
     * @access  private
     */
    var $_cols = NULL;

    /**
     * PHAT_Textarea element constructor
     *
     * A PHAT_Textarea element can be constructed in two ways.  You can either
     * send a valid database ID to construct a PHAT_Textarea element that already
     * exists.  Or you can pass nothing and a new PHAT_Textarea will be created,
     * only the item table will be set.
     *
     * @param integer $id database id key for this PHAT_Textarea
     */
    function PHAT_Textarea($id = NULL) {
        $this->setTable('mod_phatform_textarea');
        $this->addExclude(array('_optionText', '_optionValues', '_optionSet'));

        if(isset($id)) {
            $this->setId($id);
            $leftOvers = $this->init();
        }
    } // END FUNC PHAT_Textarea

    function hasOptions() {return FALSE;}

    /**
     * View this PHAT_Textarea
     *
     * @return string The HTML to needed view this PHAT_Textarea
     * @access public
     */
    function view() {
        $label = $this->getLabel();
        if(isset($_REQUEST['PHAT_' . $label])) {
            $this->setValue($_REQUEST['PHAT_' . $label]);
        }

        if($this->isRequired())
        $viewTags['REQUIRED_FLAG'] = '&#42;';

        $viewTags['BLURB'] = Core\Text::parseOutput($this->getBlurb());
        $viewTags['NAME'] = 'PHAT_' . $this->getLabel();
        $viewTags['ROWS'] = $this->_rows;
        $viewTags['COLS'] = $this->_cols;
        $viewTags['VALUE'] = $this->getValue();

        return Core\Template::processTemplate($viewTags, 'phatform', 'textarea/view.tpl');
    } // END FUNC view

    /**
     * Edit this PHAT_Textarea
     *
     * @return string The HTML form needed to edit this PHAT_Textarea
     * @access public
     */
    function edit() {
        $elements[0] = Core\Form::formHidden('module', 'phatform') . Core\Form::formHidden('PHAT_EL_OP', 'SaveElement');

        if(!$this->getLabel()) {
            $num = $_SESSION['PHAT_FormManager']->form->numElements();
            $this->setLabel('Element' . ($num + 1));
        }

        if(PHAT_SHOW_INSTRUCTIONS) {
            $GLOBALS['CNT_phatform']['title'] = dgettext('phatform', 'Textarea Element Instructions');
        }

        $editTags['BLURB_LABEL'] = dgettext('phatform', 'Associated Text');
        $editTags['BLURB_INPUT'] = Core\Form::formTextArea('PHAT_ElementBlurb', $this->getBlurb(), PHAT_DEFAULT_ROWS, PHAT_DEFAULT_COLS);
        $editTags['NAME_LABEL'] = dgettext('phatform', 'Name');
        $editTags['NAME_INPUT'] = Core\Form::formTextField('PHAT_ElementName', $this->getLabel(), PHAT_DEFAULT_SIZE, PHAT_DEFAULT_MAXSIZE);
        $editTags['ROWS_LABEL'] = dgettext('phatform', 'Rows');
        $editTags['ROWS_INPUT'] = Core\Form::formTextField('PHAT_ElementRows', $this->_rows, 5, 3);
        $editTags['COLS_LABEL'] = dgettext('phatform', 'Columns');
        $editTags['COLS_INPUT'] = Core\Form::formTextField('PHAT_ElementCols', $this->_cols, 5, 3);
        $editTags['VALUE_LABEL'] = dgettext('phatform', 'Value');
        $editTags['VALUE_INPUT'] = Core\Form::formTextArea('PHAT_ElementValue', $this->getValue(), PHAT_DEFAULT_ROWS, PHAT_DEFAULT_COLS);
        $editTags['REQUIRE_LABEL'] = dgettext('phatform', 'Require');
        $editTags['REQUIRE_INPUT'] = Core\Form::formCheckBox('PHAT_ElementRequired', 1, $this->isRequired());
        $editTags['BACK_BUTTON'] = Core\Form::formSubmit(dgettext('phatform', 'Back'), 'PHAT_ElementBack');
        $editTags['SAVE_BUTTON'] = Core\Form::formSubmit(dgettext('phatform', 'Save Textarea'));

        $elements[0] .= Core\Template::processTemplate($editTags, 'phatform', 'textarea/edit.tpl');

        return Core\Form::makeForm('PHAT_TextareaEdit', 'index.php', $elements, 'post', NULL, NULL);
    } // END FUNC edit

    /**
     * Save this PHAT_Textarea
     *
     * @return mixed  Message on success and Core\Error on failure
     * @access public
     */
    function save() {
        $error = FALSE;

        $result = $this->setValue($_REQUEST['PHAT_ElementValue']);
        if(Core\Error::isError($result)) {
            $currentError = $result;
            $error = TRUE;
        }

        $label = $this->getLabel();
        if((!$_SESSION['PHAT_FormManager']->form->checkLabel($_REQUEST['PHAT_ElementName']) && (strcasecmp($label, $_REQUEST['PHAT_ElementName']) != 0))
        || Core\Error::isError($this->setLabel(Core\DB::sqlFriendlyName($_REQUEST['PHAT_ElementName'])))) {
            $currentError = Core\Error::get(PHATFORM_INVALID_NAME, 'phatform', 'PHAT_Textarea::save()');
            $error = TRUE;
        }

        $result = $this->setBlurb($_REQUEST['PHAT_ElementBlurb']);
        if(Core\Error::isError($result)) {
            $currentError = $result;
            $error = TRUE;
        }

        if(isset($_REQUEST['PHAT_ElementRequired'])) {
            $this->setRequired(TRUE);
        } else {
            $this->setRequired(FALSE);
        }

        $rows = Core\Text::parseInput($_REQUEST['PHAT_ElementRows']);

        if($rows)
        $this->_rows = $rows;
        else
        $this->_rows = PHAT_DEFAULT_ROWS;

        $cols = Core\Text::parseInput($_REQUEST['PHAT_ElementCols']);

        if($cols)
        $this->_cols = $cols;
        else
        $this->_cols = PHAT_DEFAULT_COLS;

        if($error) {
            return $currentError;
        } else {
            if(Core\Error::isError($this->commit())) {
                return Core\Error::get(PHATFORM_ELEMENT_FAIL, 'phatform', 'PHAT_Textarea::save()',
                array(dgettext('phatform', 'Textarea')));
            } else {
                return sprintf(dgettext('phatform', 'The %s element was saved successfully.'), dgettext('phatform', 'Textarea'));
            }
        }
    } // END FUNC save
} // END CLASS PHAT_Textarea

?>