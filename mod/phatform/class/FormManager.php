<?php

/**
 * Form Manager Class
 *
 * @version $Id$
 * @author Adam Morton
 * @author Steven Levin
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 * @package Phat Form
 */

require_once(PHPWS_SOURCE_DIR . "mod/phatform/class/Manager.php");

//require_once(PHPWS_SOURCE_DIR . "core/Form.php");

require_once(PHPWS_SOURCE_DIR . "mod/phatform/class/advViews.php");

class PHAT_FormManager extends PHPWS_Manager {

    /**
     * The PHAT_Form object of the current form the user is insterested in.
     *
     * @var     object
     * @example $this->form = new PHAT_Form(6);
     * @access  public
     */
    var $form = NULL;

    /**
     * The constructor for the PHAT_FormManager
     *
     * @access public
     */
    function PHAT_FormManager() {
        $this->setModule('phatform');
        $this->setRequest('PHAT_MAN_OP');
        $this->setTable('mod_phatform_forms');
    }// END FUNC PHAT_FormManager

    /**
     * Returns the templated menu for this form manager.
     *
     * @return string The templated menu ready for display.
     * @access private
     */
    function menu() {
        $links = array();

        $links[] = '<a href="./index.php?module=phatform&amp;PHAT_MAN_OP=NewForm">'.dgettext('phatform', 'New Form').'</a>';
        $links[] = '<a href="./index.php?module=phatform&amp;PHAT_MAN_OP=List">'.dgettext('phatform', 'List Forms').'</a>';

        if(!isset($this->form) || $this->form->isArchiveView()) {
            if(Current_User::allow('phatform', 'archive_form'))
            $links[] = '<a href="./index.php?module=phatform&amp;PHAT_MAN_OP=viewArchives&amp;PDA_start=0">'.dgettext('phatform', 'List Archives').'</a>';

            if(Current_User::allow('phatform', 'report_export'))
            $links[] = '<a href="./index.php?module=phatform&amp;PHAT_MAN_OP=viewExports&amp;PDA_start=0">'.dgettext('phatform', 'List Exports').'</a>';
        }

        if(isset($this->form) && !$this->form->isArchiveView()) {
            $id = $this->form->getId();
            if(isset($id)) {
                if(Current_User::allow('phatform', 'edit_forms')) {
                    $links[] = '<a href="./index.php?module=phatform&amp;PHAT_FORM_OP=editSettings">'.dgettext('phatform', 'Settings').'</a>';
                }

                if(!$this->form->isSaved() && Current_User::allow('phatform', 'edit_forms')) {
                    $links[] = '<a href="./index.php?module=phatform&amp;PHAT_FORM_OP=editElements">'.dgettext('phatform', 'Elements').'</a>';
                }

                if(Current_User::allow('phatform', 'edit_options')) {
                    $links[] = '<a href="./index.php?module=phatform&amp;PHAT_MAN_OP=Options">'.dgettext('phatform', 'Option Sets').'</a>';
                }
            }

            if($this->form->isSaved() && Current_User::allow('phatform', 'report_view')) {
                $links[] = '<a href="./index.php?module=phatform&amp;PHAT_FORM_OP=report">'.dgettext('phatform', 'Report').'</a>';
            }

            if($this->form->isSaved() && Current_User::allow('phatform', 'archive_form')) {
                $links[] = '<a href="./index.php?module=phatform&amp;PHAT_FORM_OP=archive">'.dgettext('phatform', 'Archive').'</a>';
            }
        }

        $tags = array();
        $tags['LINKS'] = implode('&#160;|&#160;', $links);

        return PHPWS_Template::processTemplate($tags, 'phatform', 'manager/menu.tpl');
    }// END FUNC _menu

    /**
     * Required function for PHPWS_Manager
     *
     * This function is called via the PHPWS_Manager list.  It loads the form
     * $ids[0] from the database and enters edit mode for that form.
     *
     * @param  array $ids The ids of the items that were selected from the PHPWS_Manager list.
     * @access private
     */
    function _edit($ids) {
        /* Make sure this user has access to edit the current form */
        if(Current_User::isDeity() || Current_User::allow('phatform', 'edit_forms')) {
            $this->form = new PHAT_Form($ids[0]);

            /* Make sure this user owns and can edit the selected form */
            if(!Current_User::isDeity() &&
            (Current_User::allow('phatform', 'user_forms_only') &&
            ($this->form->getOwner() != Current_User::getUsername()))) {
                $this->_accessDenied();
            }

            $content = array();
            $content[] = $this->menu();
            if($this->form->isSaved())
            $content[] = $this->form->editSettings();
            else
            $content[] = $this->form->view(TRUE);
        } else {
            $this->_accessDenied();
        }
        $GLOBALS['CNT_phatform'] = implode(chr(10), $content);
    }// END FUNC _edit

    /**
     * Required function for PHPWS_Manager
     *
     * This function is called via the PHPWS_Manager list.  It loads the form
     * $ids[0] from the database and enters view mode for that form.
     *
     * @param  array $ids The ids of the items that were selected from the PHPWS_Manager list.
     * @access private
     */
    function _view($ids) {
        $content = array();
        $this->form = new PHAT_Form($ids[0]);
        if(Current_User::allow('phatform')) {
            $content[] = $_SESSION['PHAT_FormManager']->menu();
            $content[] = $this->form->view();
        } else {
            $content[] = $this->form->view();
        }
        $GLOBALS['CNT_phatform']['content'] = implode(chr(10), $content);
    }// END FUNC _view

    /**
     * Required function for PHPWS_Manager
     *
     * This function is called via the PHPWS_Manager. It is used to allow the Manager
     * to send itself to it's list.
     *
     * @access private
     */
    function _list() {
        $this->init();
        $this->setTable('mod_phatform_forms');
        /* Check if user has admin access to phatform */
        if(Current_User::allow('phatform')) {
            unset($this->form);
            $content = array();

            /* Show menu to admin user */
            $content[] = $this->menu();

            /* Always show the saved/approved forms to the admin user */
            /* Make sure user isn't a deity and then see if they can only edit their forms */
            $content[] = $this->getList('saved', dgettext('phatform', 'Saved Forms'));

            /* If a user can edit forms, show the unsaved ones */
            if(Current_User::allow('phatform', 'edit_forms')) {
                $content[] = '<hr />' . $this->getList('unsaved', dgettext('phatform', 'Unsaved Forms'));
            }

            /* Grab unapproved forms */
            if(Current_User::allow('phatform', 'approve_forms')) {
                $content[] = '<hr />' . $this->getList('unapproved', dgettext('phatform', 'Unapproved Forms'));
            }
        } else {
            /* Not an admin user so only show them a list of available forms */
            $content[] = $this->getList('user', dgettext('phatform', 'Forms'));
        }

        $GLOBALS['CNT_phatform']['title'] = PHAT_TITLE;
        $GLOBALS['CNT_phatform']['content'] = implode(chr(10), $content);
    }// END FUNC _list

    /**
     * Displays a confirmation for deletion of forms and carries out the delete.
     *
     * @param  array $ids An array of form ids of forms to be deleted.
     * @access private
     */
    function _delete($ids) {
        if(isset($_REQUEST['yes'])) {
            foreach($ids as $id) {
                $this->form = new PHAT_Form($id);
                $this->form->delete();
                unset($this->form);
            }
        } elseif(isset($_REQUEST['no'])) {
            $this->_list();
            return;
        } else {
            $title = dgettext('phatform', 'Delete form confirmation');
            $content = $this->_confirmDelete($ids);
            $GLOBALS['CNT_phatform']['title'] = $title;
            $GLOBALS['CNT_phatform']['content'] = $content;
        }
    }

    /**
     * Returns the templated confirmation for deleting multiple items from a list.
     *
     * @param array   $ids     An array of item ids to be deleted.
     * @return string The templated confirmation message.
     * @access private
     */
    function _confirmDelete($ids) {
        /* Make sure an array of ids was recived before asking to confirm */
        if(!is_array($ids)) {
            $this->_list();
            return;
        }

        $elements = array();
        $elements[0] = '';
        $confirmTags = array();
        $confirmTags['ITEMS'] = '';

        $confirmTags['MESSAGE'] = dgettext('phatform', 'Are you sure you wish to delete the following forms?  All data associated with these forms will be lost!');
        $confirmTags['YES_BUTTON'] = PHPWS_Form::formSubmit('Yes', 'yes');
        $confirmTags['NO_BUTTON'] = PHPWS_Form::formSubmit('No', 'no');

        /* Step through ids and grab the names of each form */
        foreach($ids as $key=>$id) {
            $temp = new PHAT_Form($id);
            $confirmTags['ITEMS'] .= $key+1 . '. ' . $temp->getLabel() . '<br />';
            $elements[0] .= PHPWS_Form::formHidden('PHPWS_MAN_ITEMS[]', $id);
        }

        /* Finish creating elements array for form */
        $elements[0] .= PHPWS_Form::formHidden('module', $this->_module);
        $elements[0] .= PHPWS_Form::formHidden('PHAT_MAN_OP', 'delete');
        $elements[0] .= PHPWS_Template::processTemplate($confirmTags, 'phatform', 'manager/confirm.tpl');

        return PHPWS_Form::makeForm('PHPWS_MAN_Deletion', 'index.php', $elements);
    } // END FUNC _confirmDelete()

    /**
     * Prints an access denied message and exits the script.
     *
     * @access private
     */
    function _accessDenied() {
        PHPWS_Core::errorPage('400');
    }


    /**
     * Allows you to edit and delete saved option sets
     *
     * @access private
     */

    function _listOptions() {
        $this->init();
        $GLOBALS['CNT_phatform']['title'] = PHAT_TITLE;

        $content = array();
        $content[] = $this->menu();
        $this->setTable('mod_phatform_options');
        $content[] = $this->getList('options', dgettext('phatform', 'Option Sets'));
        $this->setTable('mod_phatform_forms');

        return implode(chr(10), $content);
    }


    function _editOptions() {
        if(Current_User::allow('phatform', 'edit_options')) {
            if((isset($_REQUEST['PHAT_OptionSetId']) && !is_numeric($_REQUEST['PHAT_OptionSetId'])) || isset($_REQUEST['PHAT_OptionBack'])) {
                $_REQUEST['PHAT_MAN_OP'] = 'Options';
                $this->action();
                return;
            } else {
                $optionSetId = $_REQUEST['PHAT_OptionSetId'];
            }

            if(isset($_REQUEST['PHAT_SaveOptionSet'])) {
                if(is_array($_REQUEST['PHAT_ElementOptions']) && is_array($_REQUEST['PHAT_ElementValues'])) {
                    for($i = 0; $i < sizeof($_REQUEST['PHAT_ElementOptions']); $i++) {
                        $_REQUEST['PHAT_ElementOptions'][$i] = PHPWS_Text::parseInput($_REQUEST['PHAT_ElementOptions'][$i]);
                        $_REQUEST['PHAT_ElementValues'][$i] = PHPWS_Text::parseInput($_REQUEST['PHAT_ElementValues'][$i]);
                    }

                    $options = addslashes(serialize($_REQUEST['PHAT_ElementOptions']));
                    $values = addslashes(serialize($_REQUEST['PHAT_ElementValues']));
                    $saveArray = array('optionSet'=>$options,
                                       'valueSet'=>$values
                    );
                    $db = new PHPWS_DB('mod_phatform_options');
                    $db->addWhere('id', $optionSetId);
                    $db->addValue($saveArray);
                    $db->update();
                }
            } else if(isset($_REQUEST['PHAT_delete'])) {
                $db = new PHPWS_DB('mod_phatform_options');
                $db->addWhere('id', $optionSetId);
                $db->delete();
                $_REQUEST['PHAT_MAN_OP'] = 'Options';
                $this->action();
                return;
            }

            $GLOBALS['CNT_phatform']['title'] = PHAT_TITLE;

            $sql = "SELECT * FROM mod_phatform_options WHERE id='$optionSetId'";
            $result = PHPWS_DB::getRow($sql);

            if($result) {
                $elements = array();
                $elements[] = PHPWS_Form::formHidden('module', $this->_module);
                $elements[] = PHPWS_Form::formHidden('PHAT_MAN_OP', 'editOptions');
                $elements[] = PHPWS_Form::formHidden('PHAT_OptionSetId', $optionSetId);

                $options = unserialize(stripslashes($result['optionSet']));
                $values = unserialize(stripslashes($result['valueSet']));

                $editTags = array();
                $editTags['TITLE'] = dgettext('phatform', 'Edit option set')."&#160;{$result['label']}";
                $editTags['NUMBER_LABEL'] = dgettext('phatform', 'Option');
                $editTags['INPUT_LABEL'] = dgettext('phatform', 'Text');
                $editTags['VALUE_LABEL'] = dgettext('phatform', 'Value');

                $editTags['OPTIONS'] = '';
                $rowClass = NULL;


                for($i = 0; $i < sizeof($options); $i++) {
                    $optionRow['OPTION_NUMBER'] = $i + 1;
                    $optionRow['OPTION_INPUT'] = PHPWS_Form::formTextField("PHAT_ElementOptions[$i]", $options[$i], PHAT_DEFAULT_SIZE, PHAT_DEFAULT_MAXSIZE);
                    $optionRow['VALUE_INPUT'] = PHPWS_Form::formTextField("PHAT_ElementValues[$i]", $values[$i], PHAT_DEFAULT_SIZE, PHAT_DEFAULT_MAXSIZE);
                    $optionRow['ROW_CLASS'] = $rowClass;
                    if ($i%2) {
                        $rowClass = ' class="bgcolor1"';
                    } else {
                        $rowClass = null;
                    }

                    $editTags['OPTIONS'] .= PHPWS_Template::processTemplate($optionRow, 'phatform', 'options/option.tpl');
                }
            }
            $editTags['BACK_BUTTON'] = PHPWS_Form::formSubmit(dgettext('phatform', 'Back'), 'PHAT_OptionBack');
            $editTags['SAVE_BUTTON'] = PHPWS_Form::formSubmit(dgettext('phatform', 'Save'), 'PHAT_SaveOptionSet');

            $elements[] = PHPWS_Template::processTemplate($editTags, 'phatform', 'options/optionList.tpl');

            return PHPWS_Form::makeForm('PHAT_Options_edit', 'index.php', $elements);
        } else {
            $this->_list();
        }
    }

    /**
     * The action function for PHAT_FormManager
     *
     * This function checks the PHAT_MAN_OP...hehe I said phat man :)  Based on that
     * variable, action calls the required functions to complete the operation.
     *
     * @access private
     */
    function action() {

        switch($_REQUEST['PHAT_MAN_OP']) {
            case 'View':
                if(isset($_REQUEST['PHAT_FORM_ID'])) {
                    $this->form = new PHAT_Form($_REQUEST['PHAT_FORM_ID']);
                    if($this->form->isSaved() && !$this->form->isHidden() && $this->form->isApproved())
                    $content = $this->form->view();
                    else {
                        $GLOBALS['CNT_phatform']['title'] = $this->form->getLabel();
                        $content = dgettext('phatform', 'This form is not available for viewing at this time.');
                    }
                }
                break;

            case 'List':
                $this->_list();
                return;
                break;

            case 'viewExports':
                if(!isset($_SESSION['PHAT_advViews']))
                $_SESSION['PHAT_advViews'] = new advViews();

                if(!isset($_REQUEST['EXPORT_OP'])) {
                    $_SESSION['PHAT_advViews']->intAdvViews();

                    if(isset($this->form))
                    unset($this->form);
                }

                $content  = $this->menu();
                $content .= $_SESSION['PHAT_advViews']->viewExports();
                break;

            case 'viewArchives':
                if(!isset($_SESSION['PHAT_advViews']))
                $_SESSION['PHAT_advViews'] = new advViews();

                if(!isset($_REQUEST['ARCHIVE_OP'])) {
                    $_SESSION['PHAT_advViews']->intAdvViews();

                    if(isset($this->form))
                    unset($this->form);
                }

                $content  = $this->menu();
                $content .= $_SESSION['PHAT_advViews']->viewArchives();
                break;

            case 'Options':
                if(Current_User::allow('phatform', 'edit_options')) {
                    $content = $this->_listOptions();
                } else {
                    $this->_accessDenied();
                }
                break;

            case 'editOptions':
                if(Current_User::allow('phatform', 'edit_options')) {
                    $content = $this->_editOptions();
                } else {
                    $this->_accessDenied();
                }
                break;

            case 'NewForm':
                if(Current_User::allow('phatform', 'edit_forms')) {
                    $this->form = new PHAT_Form;
                    $content = $this->menu();
                    $content .= $this->form->editSettings();
                } else {
                    $this->_accessDenied();
                }
                break;

        }// END PHAT_MAN_OP SWITCH

        if(isset($content)) {
            $GLOBALS['CNT_phatform']['content'] .= $content;
        }
    }// END FUNC action

}// END CLASS PHAT_FormManager

?>