<?php

require_once(PHPWS_SOURCE_DIR . 'core/class/Item.php');

require_once(PHPWS_SOURCE_DIR . 'core/class/Error.php');

//require_once(PHPWS_SOURCE_DIR . 'core/Form.php');

require_once(PHPWS_SOURCE_DIR . 'core/class/Text.php');

/**
 * Element class for phatform
 *
 * @version $Id$
 * @author  Steven Levin <steven@NOSPAM.tux.appstate.edu>
 * @author  Adam Morton <adam@NOSPAM.tux.appstate.edu>
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 * @package Phat Form
 */
class PHAT_Element extends PHPWS_Item {

    /**
     * The blurb for this PHAT_Element
     *
     * @var     string
     * @example $this->_blurb = 'What is your name?';
     * @access  private
     */
    var $_blurb = NULL;

    /**
     * Value to be displayed in this PHAT_Element
     *
     * @var     mixed
     * @example $this->_value = 'Steven Levin';
     * @example $this->_value = 3;
     * @access  private
     */
    var $_value = NULL;

    /**
     * Whether or not this PHAT_Element is a required field
     *
     * @var     boolean
     * @example $this->_required = 1;
     * @access  private
     */
    var $_required = NULL;

    /**
     * Option Text list for this PHAT_Element
     *
     * @var     array
     * @example $this->_optionText = array('Pizza', 'Pretzels', 'PB&H');
     * @access  private
     */
    var $_optionText = array();

    /**
     * Option Value list for this PHAT_Element
     *
     * @var     array
     * @example $this->_optionValues = array(1, 2, 3);
     * @access  private
     */
    var $_optionValues = array();

    /**
     * The option set this PHAT_Element is currently using
     *
     * @var     integer
     * @example $this->_optionSet = 3;
     * @access  private
     */
    var $_optionSet = array();

    /**
     * Sets the blurb for this element
     *
     * @param  string $blurb The text for this blurb
     * @return mixed  TRUE on success PHPWS_Error on failure
     * @access public
     */
    function setBlurb($blurb = NULL) {
        if($blurb) {
            $this->_blurb = PHPWS_Text::parseInput($blurb);
            return TRUE;
        } else if(PHAT_BLURB_REQUIRED) {
            return PHPWS_Error::get(PHATFORM_ASSOC_TEXT, 'phatform', 'PHAT_Element::setBlurb()');
        } else {
            $this->_blurb = NULL;
            return TRUE;
        }
    }

    /**
     * Sets the value for this element
     *
     * @param  mixed  $value Whatever the value of this PHAT_Element is to be
     * @return mixed  TRUE on success PHPWS_Error on failure
     * @access public
     */
    function setValue($value = NULL) {
        if(isset($value) && $this->hasOptions()) {
            $this->_value = $value;
            return TRUE;
        } else if(isset($value)) {
            $this->_value = PHPWS_Text::parseInput($value);
        } else if(PHAT_VALUE_REQUIRED) {
            $message = dgettext('phatform', 'The value for this element was not set.');
            return PHPWS_Error::get(PHATFORM_VALUE_MISSING, 'phatform', 'PHAT_Element::setValue()');
        } else {
            $this->_value = NULL;
            return TRUE;
        }
    }

    /**
     * Sets the required variable for this PHAT_Element
     *
     * @param  boolean $flag Whether or not to set required variable
     * @access public
     */
    function setRequired($flag = TRUE) {
        if($flag)
        $this->_required = 1;
        else
        $this->_required = 0;
    }

    /**
     * Sets the option set for this PHAT_Element
     *
     * @param  interger $set The id of the option set being used
     * @access public
     */
    function setOptionSet($set = NULL) {
        if(is_numeric($set))
        $this->_optionSet = $set;
        else
        $this->_optionSet = 0;
    }

    /**
     * Provides access to the blurb variable
     *
     * @return string The blurb for this PHAT_Element
     * @access public
     */
    function getBlurb() {
        if($this->_blurb)
        return $this->_blurb;
        else
        return NULL;
    }

    /**
     * Provides access to the value variable
     *
     * @return string The value for this PHAT_Element
     * @access public
     */
    function getValue() {
        if($this->_value !== NULL)
        return $this->_value;
        else
        return NULL;
    }

    /**
     * Checks to the required variable for this PHAT_Element
     *
     * @return integer The value in the required variable
     * @access public
     */
    function isRequired() {
        return $this->_required;
    }

    /**
     * Provides access to the optionText for this PHAT_Element
     *
     * @return array  List of the options text
     * @access public
     */
    function getOptionText() {
        if($this->_optionText)
        return $this->_optionText;
        else
        return NULL;
    }

    /**
     * Provides access to the optionValues for this PHAT_Element
     *
     * @return array  List of the values for the options
     * @access public
     */
    function getOptionValues() {
        if($this->_optionValues)
        return $this->_optionValues;
        else
        return NULL;
    }

    /**
     * Provides access to the optionSet for this PHAT_Element
     *
     * @return integer The id of the current option set
     * @access public
     */
    function getOptionSet() {
        if($this->_optionSet)
        return $this->_optionSet;
        else
        return NULL;
    }

    /**
     * Provides a list of option sets currently stored in the database
     *
     * @return array  The listing of option sets
     * @access public
     */
    function getOptionSets() {
        $sql = 'SELECT id, label FROM mod_phatform_options';
        $optionResult = PHPWS_DB::query($sql);
        $options[0] = '';
        while($row = $optionResult->fetchrow(DB_FETCHMODE_ASSOC)) {
            $options[$row['id']] = $row['label'];
        }

        if(sizeof($options) > 1) {
            return $options;
        } else {
            return NULL;
        }
    }

    /**
     * Get the options for this PHAT_Element
     *
     * @return string The HTML form for retrieving the options
     * @access public
     */
    function getOptions() {
        $className = get_class($this);
        $properName = ucfirst(str_ireplace('phat_', '', $className));

        if(isset($_REQUEST['PHAT_OptionSet']) && ($_REQUEST['PHAT_OptionSet'] != 0) && ($_REQUEST['PHAT_OptionSet'] != $this->getOptionSet())) {
            $this->setOptionSet($_REQUEST['PHAT_OptionSet']);
            $db = new PHPWS_DB('mod_phatform_options');

            $db->addWhere('id', $this->getOptionSet());
            $optionResult = $db->select();

            $this->_optionText = array();
            $this->_optionValues = array();

            $this->_optionText = unserialize(stripslashes($optionResult[0]['optionSet']));
            $this->_optionValues = unserialize(stripslashes($optionResult[0]['valueSet']));
        }

        if(!empty($_REQUEST['PHAT_ElementNumOptions']) && is_numeric($_REQUEST['PHAT_ElementNumOptions'])) {
            $loops = $_REQUEST['PHAT_ElementNumOptions'];

            /* must reset these arrays for when a new number of options is entered */
            $oldText = $this->_optionText;
            $oldValues = $this->_optionValues;
            $this->_optionText = array();
            $this->_optionValues = array();
            for($i = 0; $i < $loops; $i++) {
                if(isset($oldText[$i])) {
                    $this->_optionText[$i] = $oldText[$i];
                } else {
                    $this->_optionText[$i] = NULL;
                }
                if(isset($oldValues[$i])) {
                    $this->_optionValues[$i] = $oldValues[$i];
                } else {
                    $this->_optionValues[$i] = NULL;
                }
            }

        } else if(sizeof($this->_optionText) > 0) {
            $loops = sizeof($this->_optionText);
        } else {
            return PHPWS_Error::get(PHATFORM_ZERO_OPTIONS, 'phatform', 'PHAT_Element::getOptions()');
        }

        $elements[0] = '<input type="hidden" name="module" value="phatform" /><input type="hidden" name="PHAT_EL_OP" value="SaveElementOptions" />';

        if(PHAT_SHOW_INSTRUCTIONS) {
            $GLOBALS['CNT_phatform']['title'] = dgettext('phatform', 'Option Instructions');
        }

        $editTags['NUMBER_LABEL'] = dgettext('phatform', 'Option');
        $editTags['INPUT_LABEL'] = dgettext('phatform', 'Text');
        $editTags['VALUE_LABEL'] = dgettext('phatform', 'Value');
        $editTags['DEFAULT_LABEL'] = dgettext('phatform', 'Default');

        $editTags['OPTIONS'] = '';
        $rowClass = NULL;

        for($i = 0; $i < $loops; $i++) {
            $optionRow['OPTION_NUMBER'] = $i + 1;
            $element = new Form_TextField("PHAT_ElementOptions[$i]", $this->_optionText[$i]);
            $element->setSize(PHAT_DEFAULT_SIZE, PHAT_DEFAULT_MAXSIZE);
            $optionRow['OPTION_INPUT'] = $element->get();

            $element = new Form_TextField("PHAT_ElementValues[$i]", $this->_optionValues[$i]);
            $element->setSize(PHAT_DEFAULT_SIZE, PHAT_DEFAULT_MAXSIZE);
            $optionRow['VALUE_INPUT'] = $element->get();

            $check = NULL;

            if($className == 'PHAT_checkbox' || $className == 'PHAT_Multiselect') {
                if(isset($this->_optionValues[$i]) && (isset($this->_value[$i]) && $this->_optionValues[$i] == $this->_value[$i])) {
                    $check = $i;
                }
                $element = new Form_CheckBox("PHAT_ElementDefault[$i]", $i);
                $element->setMatch($check);
                $optionRow['OPTION_DEFAULT'] = $element->get();
            }   else {

                if (isset($this->_optionValues[$i]) && $this->_optionValues[$i] == $this->_value) {
                    $check = $i;
                }

                $element = new Form_CheckBox('PHAT_ElementDefault', $i);
                $element->setMatch($check);
                $optionRow['OPTION_DEFAULT'] = $element->get();
            }

            $optionRow['ROW_CLASS'] = $rowClass;
            if ($i%2) {
                $rowClass = ' class="bgcolor1"';
            } else {
                $rowClass = null;
            }

            $editTags['OPTIONS'] .= PHPWS_Template::processTemplate($optionRow, 'phatform', 'element/option.tpl');
        }


        $check = NULL;
        if($this->getId()) {
            if(($this->_optionText == $this->_optionValues) && (sizeof($this->_optionText) > 0)) {
                $check = 1;
            }
        }

        if(isset($_REQUEST['PHAT_SaveOptionSet'])) {
            $setName = $_REQUEST['PHAT_SaveOptionSet'];
        } else {
            $setName = NULL;
        }

        $element = new Form_Checkbox('PHAT_ElementUseText', 1);
        $element->setMatch($check);

        $editTags['USE_TEXT_CHECK'] = dgettext('phatform', 'Use option text as values') . ': ' . $element->get();
        $editTags['SAVE_OPTION_SET'] = dgettext('phatform', 'Save option set as') . ': ' . PHPWS_Form::formTextField('PHAT_SaveOptionSet', $setName, PHAT_DEFAULT_SIZE, PHAT_DEFAULT_MAXSIZE);
        $editTags['BACK_BUTTON'] = PHPWS_Form::formSubmit(dgettext('phatform', 'Back'), 'PHAT_OptionBack');
        $editTags['SAVE_BUTTON'] = PHPWS_Form::formSubmit(dgettext('phatform', 'Save ' . $properName));

        $elements[0] .= PHPWS_Template::processTemplate($editTags, 'phatform', 'element/optionList.tpl');

        return PHPWS_Form::makeForm('PHAT_Options', 'index.php', $elements, 'post', NULL, NULL);
    } // END FUNC getOptions

    /**
     * Save the options for this PHAT_Element
     *
     * @return mixed  Content if the editing is to continue, PHPWS_Error on failure, or message on success
     * @access public
     */
    function saveOptions() {
        $className = get_class($this);
        $properName = ucfirst(str_ireplace('phat_', '', $className));

        if(is_array($_REQUEST['PHAT_ElementOptions']) && is_array($_REQUEST['PHAT_ElementValues'])) {
            $saveText = TRUE;
            $returnText = NULL;
            $saveValues = TRUE;
            $atLeastOne = FALSE;

            if(isset($_REQUEST['PHAT_ElementUseText'])) {
                $_REQUEST['PHAT_ElementValues'] = $_REQUEST['PHAT_ElementOptions'];
                $this->setOptionSet();
            }

            for($i = 0; $i < sizeof($_REQUEST['PHAT_ElementOptions']); $i++) {
                if($_REQUEST['PHAT_ElementOptions'][$i] != NULL) {
                    $this->_optionText[$i] = PHPWS_Text::parseInput($_REQUEST['PHAT_ElementOptions'][$i]);
                } else {
                    $this->_optionText[$i] = NULL;
                    $saveText = FALSE;
                }

                if($_REQUEST['PHAT_ElementValues'][$i] != NULL) {
                    $this->_optionValues[$i] = PHPWS_Text::parseInput($_REQUEST['PHAT_ElementValues'][$i]);
                    $atLeastOne = TRUE;
                } else {
                    $this->_optionValues[$i] = NULL;
                    $saveValues = FALSE;
                }
            }

            if($className == 'PHAT_Checkbox' || $className == 'PHAT_Multiselect') {
                for($i = 0; $i < sizeof($_REQUEST['PHAT_ElementOptions']); $i++) {
                    if(isset($_REQUEST['PHAT_ElementDefault']) && isset($_REQUEST['PHAT_ElementDefault'][$i])) {
                        $this->_value[$i] = $_REQUEST['PHAT_ElementValues'][$_REQUEST['PHAT_ElementDefault'][$i]];
                    }
                }
            } else {
                if(isset($_REQUEST['PHAT_ElementDefault'])) {
                    $this->_value = $_REQUEST['PHAT_ElementValues'][$_REQUEST['PHAT_ElementDefault']];
                } else {
                    $this->_value = NULL;
                }
            }

            if($saveText && $saveValues) {
                if($_REQUEST['PHAT_SaveOptionSet']) {
                    $label = PHPWS_Text::parseInput($_REQUEST['PHAT_SaveOptionSet']);
                    $options = addslashes(serialize($this->_optionText));
                    $values = addslashes(serialize($this->_optionValues));

                    $saveArray = array('label'=>$label,
                                       'optionSet'=>$options,
                                       'valueSet'=>$values
                    );
                    $db = new PHPWS_DB('mod_phatform_options');
                    $db->addValue($saveArray);
                    $id = $db->insert();
                    if($id) {
                        $this->setOptionSet($id);
                        $returnText = sprintf(dgettext('phatform', 'The option set %s was successfully saved.'), '<b><i>' . $label . '</i></b>') . '<br />';
                    } else {
                        return PHPWS_Error::get(PHATFORM_OPTION_WONT_SAVE, 'phatform', 'PHAT_Element::saveOptions()', array($label));
                    }
                }

                if(PHPWS_Error::isError($this->commit())) {
                    return PHPWS_Error::get(PHATFORM_ELEMENT_FAIL, 'phatform', 'PHAT_Element::saveOptions()',  array($properName));
                } else {
                    $returnText .= sprintf(dgettext('phatform', 'The %s was saved successfully.'), '<b><i>' . $properName . '</i></b>');
                    return $returnText;
                }

            } else {
                if($atLeastOne) {
                    return PHPWS_Error::get(PHATFORM_VALUES_NOT_SET, 'phatform', 'PHAT_Element::saveOptions()');
                } else {
                    return PHPWS_Error::get(PHATFORM_VAL_OPT_NOT_SET, 'phatform', 'PHAT_Element::saveOptions()');
                }
            }
        } else {
            return PHPWS_Error::get(PHATFORM_ELEMENT_FAIL, 'phatform', 'PHAT_Element::saveOptions()', array($properName));
        }
    } // END FUNC saveOptions

    /**
     * Action function for this PHAT_Element
     *
     * @access public
     */
    function action() {
        $content = NULL;

        if($this->getId()) {
            $new = FALSE;
        } else {
            $new = TRUE;
        }

        if(isset($_REQUEST['PHAT_ElementBack'])) {
            $content = $_SESSION['PHAT_FormManager']->menu();
            $content .= $_SESSION['PHAT_FormManager']->form->view(TRUE);
        } else {
            switch($_REQUEST['PHAT_EL_OP']) {
                case 'SaveElement':
                    if(Current_User::allow('phatform', 'edit_forms')) {
                        $result = $this->save();

                        if(PHPWS_Error::isError($result)) {
                            $GLOBALS['CNT_phatform']['message'] =  $result->getMessage();
                            $content .= $this->edit();
                        } elseif($this->hasOptions()) {

                            $content = $result;
                        } else {
                            $content = $_SESSION['PHAT_FormManager']->menu();
                            $content .= $result . '<br />';

                            if($new) {
                                $result = $_SESSION['PHAT_FormManager']->form->pushElement();
                                if(PHPWS_Error::isError($result)) {
                                    $content .= $result->getMessage() .'<br />';
                                    $content .= $this->edit();
                                    return;
                                } else {
                                    $content .= $result;
                                }
                            }

                            $content .= $_SESSION['PHAT_FormManager']->form->view(TRUE);
                        }
                    } else {
                        $this->accessDenied();
                    }
                    break;

                case 'SaveElementOptions':
                    if(Current_User::allow('phatform', 'edit_forms')) {
                        if(isset($_REQUEST['PHAT_OptionBack'])) {
                            $content = $this->edit();
                        } else {
                            $result = $this->saveOptions();
                            if(PHPWS_Error::isError($result)) {
                                $content .= $result->getMessage() .'<br />';
                                $content .= $this->getOptions();
                            } else {
                                $content = $_SESSION['PHAT_FormManager']->menu();
                                $content .= $result . '<br />';

                                if($new) {
                                    $result = $_SESSION['PHAT_FormManager']->form->pushElement();
                                    if(PHPWS_Error::isError($result)) {
                                        $content .= $result->getMessage() .'<br />';
                                        $content .= $this->edit();
                                        return;
                                    } else {
                                        $content .= $result;
                                    }
                                }

                                $content .= $_SESSION['PHAT_FormManager']->form->view(TRUE);
                            }

                        }
                    } else {
                        $this->accessDenied();
                    }
                    break;

                case 'RemoveElement':
                    if(Current_User::allow('phatform', 'edit_forms')) {
                        $result = $this->remove();
                        if(PHPWS_Error::isError($result)) {
                            $content .= $result->getMessage() .'<br />';
                        } else {
                            $content = $_SESSION['PHAT_FormManager']->menu();
                            $content .= $result . '<br />';
                        }

                        $content .= $_SESSION['PHAT_FormManager']->form->view(TRUE);
                    } else {
                        $this->accessDenied();
                    }
                    break;
            } // END PHAT_EL_OP SWITCH
        }

        if(isset($content)) {
            $GLOBALS['CNT_phatform']['content'] .= $content;
        }
    } // END FUNC action

    /**
     * Access denied
     *
     * Exits the script because user should not be wherever they were
     *
     * @access public
     */
    function accessDenied() {
        Core\Core::errorPage('404');
    } // END FUNC accessDenied()

    /**
     * Remove this PHAT_Element
     *
     * @accesss public
     */
    function remove() {
        if(isset($_REQUEST['PHAT_Yes'])) {
            $result = $this->kill();
            if(PHPWS_Error::isError($result)) {
                return PHPWS_Error::get(PHATFORM_CANNOT_DELETE_ELEMENT, 'phatform', 'PHAT_Element::remove()');
            } else {
                $result = $_SESSION['PHAT_FormManager']->form->popElement();
                if(PHPWS_Error::isError($result)) {
                    return $result;
                } else {
                    return dgettext('phatform', 'The element was successfully removed.');
                }
            }
        } else if(isset($_REQUEST['PHAT_No'])) {
            return dgettext('phatform', 'No element was removed.');;
        } else {
            $className = get_class($this);
            $properName = ucfirst(str_ireplace('phat_', '', $className));

            $tags['MESSAGE'] = sprintf(dgettext('phatform', 'Are you sure you want to remove this %s element?'), '<b><i>' . $properName . '</i></b>');
            $tags['YES_BUTTON'] = PHPWS_Form::formSubmit('Yes', 'PHAT_Yes');
            $tags['NO_BUTTON'] = PHPWS_Form::formSubmit('No', 'PHAT_No');
            $tags['ELEMENT_PREVIEW'] = $this->view();

            $elements[0] = PHPWS_Form::formHidden('module', 'phatform');
            $elements[0] .= PHPWS_Form::formHidden('PHAT_EL_OP', 'RemoveElement');
            $elements[0] .= PHPWS_Template::processTemplate($tags, 'phatform', 'form/deleteConfirm.tpl');

            $content = PHPWS_Form::makeForm('PHAT_Confirm', 'index.php', $elements);
        }

        return $content;
    } // END FUNC remove
} // END CLASS PHAT_Element
?>