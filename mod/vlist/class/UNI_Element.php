<?php
/**
 * vlist - phpwebsite module
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */
PHPWS_Core::requireConfig('vlist');

class UNI_Element {

    public $id             = 0;
    public $title          = null;
    public $type           = null;
    public $value          = null;
    public $active         = 1;
    public $required       = 0;
    public $options        = array();
    public $numoptions     = 0;
    public $size           = 0;
    public $maxsize        = 0;
    public $rows           = 0;
    public $cols           = 0;
    public $sort           = 0;
    public $list           = 0;
    public $search         = 0;
    public $private        = 0;
    public $_error         = null;
    private $db_element    = 'vlist_element';
    private $db_item       = 'vlist_element_items';
    private $db_option     = 'vlist_element_option';
    public $vlist          = null;


    public function init()
    {
        $db = new PHPWS_DB($this->db_element);
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
        if($this->id !== 0 && $this->hasOptions()) {
            $this->loadOptions();
        }
    }


    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }


    function setValue($value = null) {
        if(isset($value) && $this->hasOptions()) {
            $this->value = $value;
            return true;
        } else if(isset($value)) {
            $this->value = PHPWS_Text::parseInput($value);
            return true;
        } else {
            $this->value = null;
            return true;
        }
    }


    public function getTitle($print=false)
    {
        if (empty($this->title)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->title);
        } else {
            return $this->title;
        }
    }


    public function getValue($print=false)
    {
        if (empty($this->value)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->value);
        } else {
            return $this->value;
        }
    }


    public function getSize()
    {
        if ($this->size) {
            $size = $this->size;
        } else {
            $size = UNI_DEFAULT_SIZE;
        }
        return $size;
    }


    public function getMaxsize()
    {
        if ($this->maxsize) {
            $maxsize = $this->maxsize;
        } else {
            $maxsize = UNI_DEFAULT_MAXSIZE;
        }
        return $maxsize;
    }


    public function getRows()
    {
        if ($this->rows) {
            $rows = $this->rows;
        } else {
            $rows = UNI_DEFAULT_ROWS;
        }
        return $rows;
    }


    public function getCols()
    {
        if ($this->cols) {
            $cols = $this->cols;
        } else {
            $cols = UNI_DEFAULT_COLS;
        }
        return $cols;
    }


    public function getQtyItems()
    {
        $db = new PHPWS_DB($this->db_item);
        $db->addWhere('element_id', $this->id);
        $num = $db->count();
        return $num;
    }


    private function loadOptions()
    {
        $db = new PHPWS_DB($this->db_option);
        $db->addWhere('element_id', $this->id);
        $db->addOrder('sort asc');

        $num = $db->count();
        if ($num < $this->numoptions) {
            $diff = $this->numoptions - $num;
            for($i = 0; $i < $diff; $i++) {
                $db->addValue('element_id', $this->id);
                $db->addValue('label', sprintf(dgettext('vlist', 'Choice for elemet %s'), $this->id));
                $result = $db->insert();
            }
        }

        $result2 = $db->select();
        $this->options = $result2;
    }


    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* delete related element_items */
        $db = new PHPWS_DB($this->db_item);
        $db->addWhere('element_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /* delete related element_options */
        $db = new PHPWS_DB($this->db_option);
        $db->addWhere('element_id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        /* delete the element */
        $db = new PHPWS_DB($this->db_element);
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

    }


    public function deleteOption($id)
    {
        if (!$this->id) {
            return;
        }

        /* delete related element_items */
        $db = new PHPWS_DB($this->db_item);
        $db->addWhere('option_id', $id);
        PHPWS_Error::logIfError($db->delete());

        /* delete the option */
        $db = new PHPWS_DB($this->db_option);
        $db->addWhere('id', $id);
        PHPWS_Error::logIfError($db->delete());

        /* reduce numoptions */
        $this->numoptions = $this->numoptions -1;

        /* save the element */
        if (PHPWS_Error::logIfError($this->saveElement(true))) {
            $this->vlist->forwardMessage(dgettext('vlist', 'Error occurred when saving element.'));
            PHPWS_Core::reroute('index.php?module=vlist&aop=edit_element&element=' . $this->id);
        } else {
            $this->vlist->forwardMessage(dgettext('vlist', 'Element saved successfully.'));
            PHPWS_Core::reroute('index.php?module=vlist&aop=edit_options&element=' . $this->id);
        }

    }


    public function deleteLink($icon=false)
    {
        $vars['element']  = $this->id;
        $vars['aop'] = 'delete_element';
        $js['ADDRESS'] = PHPWS_Text::linkAddress('vlist', $vars,true);
        $js['QUESTION'] = sprintf(dgettext('vlist', 'Are you sure you want to delete the element %s?'), $this->getTitle());
        if ($icon) {
            $js['LINK'] = sprintf('<img src="%smod/vlist/img/delete.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP,
            dgettext('vlist', 'Delete'), dgettext('vlist', 'Delete'));
        } else {
            $js['LINK'] = dgettext('vlist', 'Delete');
        }
        return javascript('confirm', $js);
    }


    public function editLink($label=null, $icon=false)
    {

        if ($icon) {
            $label = sprintf('<img src="%smod/vlist/img/edit.png" title="%s" alt="%s" >', PHPWS_SOURCE_HTTP,
            dgettext('vlist', 'Edit element'), dgettext('vlist', 'Edit element'));
        } elseif (empty($label)) {
            $label = dgettext('vlist', 'Edit');
        }

        $vars['element']  = $this->id;
        $vars['aop'] = 'edit_element';
        return PHPWS_Text::secureLink($label, 'vlist', $vars);
    }


    public function activeLink($type='active', $label=null, $icon=false)
    {
        $vars['element']  = $this->id;
        if ($type == 'list') {
            $var_col = $this->list;
            $var_act = 'list_element';
            $var_dis = 'delist_element';
            $var_act_img = '' . PHPWS_SOURCE_HTTP . 'mod/vlist/img/active.png';
            $var_inact_img = '' . PHPWS_SOURCE_HTTP . 'mod/vlist/img/inactive.png';
        } elseif ($type == 'search') {
            $var_col = $this->search;
            $var_act = 'search_element';
            $var_dis = 'desearch_element';
            $var_act_img = '' . PHPWS_SOURCE_HTTP . 'mod/vlist/img/active.png';
            $var_inact_img = '' . PHPWS_SOURCE_HTTP . 'mod/vlist/img/inactive.png';
        } elseif ($type == 'private') {
            $var_col = $this->private;
            $var_act = 'private_element';
            $var_dis = 'deprivate_element';
            $var_act_img = '' . PHPWS_SOURCE_HTTP . 'mod/vlist/img/locked.png';
            $var_inact_img = '' . PHPWS_SOURCE_HTTP . 'mod/vlist/img/unlocked.png';
        } else {
            $var_col = $this->active;
            $var_act = 'activate_element';
            $var_dis = 'deactivate_element';
            $var_act_img = '' . PHPWS_SOURCE_HTTP . 'mod/vlist/img/active.png';
            $var_inact_img = '' . PHPWS_SOURCE_HTTP . 'mod/vlist/img/inactive.png';
        }
        if ($var_col) {
            $vars['aop'] = $var_dis;
            if ($icon) {
                $label = sprintf('<img src="%s" title="%s" alt="%s" >',
                $var_act_img, dgettext('vlist', 'Deactivate'), dgettext('vlist', 'Deactivate'));
            } elseif (empty($label)) {
                $label = dgettext('vlist', 'Deactivate');
            }
        } else {
            $vars['aop'] = $var_act;
            if ($icon) {
                $label = sprintf('<img src="%s" title="%s" alt="%s" >',
                $var_inact_img, dgettext('vlist', 'Activate'), dgettext('vlist', 'Activate'));
            } elseif (empty($label)) {
                $label = dgettext('vlist', 'Activate');
            }
        }
        return PHPWS_Text::secureLink($label, 'vlist', $vars);
    }


    public function rowTag()
    {
        $links = null;

        if (Current_User::allow('vlist', 'settings', null, null, true)){
            $links[] = $this->editLink(null, true);
            $links[] = $this->deleteLink(true);
            $links[] = $this->activeLink('active', null, true);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['TYPE'] = $this->type;
        $tpl['SORT'] = $this->sort;
        if ($this->type !== 'Div') {
            $tpl['LIST'] = $this->activeLink('list', null, true);
            if ($this->type !== 'Textfield' && $this->type !== 'Textarea' && $this->type !== 'Link' && $this->type !== 'Email' && $this->type !== 'GPS' && $this->type !== 'GMap') {
                $tpl['SEARCH'] = $this->activeLink('search', null, true);
            } else {
                $tpl['SEARCH'] = '';
            }
            $tpl['PRIVATE'] = $this->activeLink('private', null, true);
        } else {
            $tpl['LIST'] = '';
            $tpl['SEARCH'] = '';
            $tpl['PRIVATE'] = '';
        }

        if($links)
        $tpl['ACTION'] = implode(' ', $links);

        return $tpl;
    }


    function editOptions()
    {
        $className = get_class($this);
        $properName = ucfirst(str_ireplace('UNI_', '', $className));

        $value = $this->getValue();
        $optionText = array();
        $optionSort = array();
        $optionId = array();
        foreach ($this->options as $option) {
            $optionText[] = $option['label'];
            $optionSort[] = $option['sort'];
            $optionId[] = $option['id'];
        }

        if($this->numoptions > 0) {
            $loops = $this->numoptions;

            /* must reset these arrays for when a new number of options is entered */
            $oldText = $optionText;
            $oldSort = $optionSort;
            $optionText = array();
            $optionSort = array();
            for($i = 0; $i < $loops; $i++) {
                if(isset($oldText[$i])) {
                    $optionText[$i] = $oldText[$i];
                } else {
                    $optionText[$i] = null;
                }
                if(isset($oldSort[$i])) {
                    $optionSort[$i] = $oldSort[$i];
                } else {
                    $optionSort[$i] = null;
                }
                if(isset($optionId[$i])) {
                    $optionId[$i] = $optionId[$i];
                } else {
                    $optionId[$i] = null;
                }
            }

        } else if(sizeof($optionText) > 0) {
            $loops = sizeof($optionText);
        } else {
            /* NOT SURE if I need this */
            return PHPWS_Error::get(PROPERTIES_ZERO_OPTIONS, 'vlist', 'UNI_Element::editOptions()');
        }

        $elements[0] = '<input type="hidden" name="module" value="vlist" /><input type="hidden" name="aop" value="post_options" /><input type="hidden" name="element_id" value="' . $this->id . '" />';

        $tpl['NUMBER_LABEL'] = dgettext('vlist', 'Option');
        $tpl['TEXT_LABEL'] = dgettext('vlist', 'Text');
        $tpl['SORT_LABEL'] = dgettext('vlist', 'Sort');
        $tpl['DEFAULT_LABEL'] = dgettext('vlist', 'Default');

        $tpl['OPTIONS'] = '';
        $rowClass = null;

        for($i = 0; $i < $loops; $i++) {
            $optionRow['OPTION_NUMBER'] = $i + 1;
            $optionRow['OPTION_ID'] = '<input type="hidden" name="UNI_OptionId['.$i.']" value="'.$optionId[$i].'" />';

            $element = new Form_TextField("UNI_OptionText[$i]", $optionText[$i]);
            $element->setSize(UNI_DEFAULT_SIZE, UNI_DEFAULT_MAXSIZE);
            $optionRow['TEXT_INPUT'] = $element->get();

            $element = new Form_TextField("UNI_OptionSort[$i]", $optionSort[$i]);
            $element->setSize(3, 5);
            $optionRow['SORT_INPUT'] = $element->get();

            $vars['option_id']  = $optionId[$i];
            $vars['element_id']  = $this->id;
            $vars['aop'] = 'delete_option';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vlist', $vars,true);
            $js['QUESTION'] = sprintf(dgettext('vlist', 'Are you sure you want to delete the option %s?'), $optionText[$i]);
            $js['LINK'] = sprintf('<img src="%smod/vlist/img/delete.png" title="%s" alt="%s" />', PHPWS_SOURCE_HTTP,
            dgettext('vlist', 'Delete'), dgettext('vlist', 'Delete'));
            $optionRow['DELETE'] =  javascript('confirm', $js);

            $check = null;

            if($className == 'UNI_Checkbox' || $className == 'UNI_Multiselect') {
                if(isset($optionId[$i]) && (isset($value[$i]) && $optionId[$i] == $value[$i])) {
                    $check = $optionId[$i];
                }
                $element = new Form_CheckBox("UNI_OptionDefault[$i]", $optionId[$i]);
                $element->setMatch($check);
                $optionRow['OPTION_DEFAULT'] = $element->get();
            }   else {
                if (isset($optionId[$i]) && $optionId[$i] == $value) {
                    $check = $optionId[$i];
                }
                $element = new Form_CheckBox('UNI_OptionDefault', $optionId[$i]);
                $element->setMatch($check);
                $optionRow['OPTION_DEFAULT'] = $element->get();
            }

            $optionRow['ROW_CLASS'] = $rowClass;
            if ($i%2) {
                $rowClass = ' class="bgcolor1"';
            } else {
                $rowClass = null;
            }

            $tpl['OPTIONS'] .= PHPWS_Template::processTemplate($optionRow, 'vlist', 'elements/edit_option.tpl');
        }

        $tpl['SAVE_BUTTON'] = PHPWS_Form::formSubmit(dgettext('vlist', 'Save ' . $properName));

        $elements[0] .= PHPWS_Template::processTemplate($tpl, 'vlist', 'elements/list_options.tpl');

        return PHPWS_Form::makeForm('UNI_Options', 'index.php', $elements, 'post', NULL, NULL);
    }



    function saveOptions()
    {
        //print_r($_POST); exit;
        $className = get_class($this);
        $properName = ucfirst(str_ireplace('UNI_', '', $className));

        /* for each of the posted options */
        for($i = 0; $i < sizeof($_REQUEST['UNI_OptionId']); $i++) {
            $db = new PHPWS_DB($this->db_option);
            $save_array = array();

            if($_REQUEST['UNI_OptionText'][$i] != null) {
                $save_array['label'] = PHPWS_Text::parseInput($_REQUEST['UNI_OptionText'][$i]);
            } else {
                $save_array['label'] = sprintf(dgettext('vlist', 'Selection %s'), $_REQUEST['UNI_OptionId'][$i]);
            }

            if($_REQUEST['UNI_OptionSort'][$i] != NULL) {
                $save_array['sort'] = (int)$_REQUEST['UNI_OptionSort'][$i];
            } else {
                $save_array['sort']  = 0;
            }

            $db->addWhere("id", $_REQUEST['UNI_OptionId'][$i]);
            $db->addValue($save_array);

            $result = $db->update();
        }

        /* do the defualts */
        if($className == 'UNI_Checkbox' || $className == 'UNI_Multiselect') {
            for($i = 0; $i < sizeof($_REQUEST['UNI_OptionId']); $i++) {
                if(isset($_REQUEST['UNI_OptionDefault']) && isset($_REQUEST['UNI_OptionDefault'][$i])) {
                    $value[$i] = $_REQUEST['UNI_OptionDefault'][$i];
                }
            }
        } else {
            if(isset($_REQUEST['UNI_OptionDefault'])) {
                $value = $_REQUEST['UNI_OptionDefault'];
            } else {
                $value = null;
            }
        }
        $this->setValue($value);

        if (PHPWS_Error::logIfError($this->saveElement(true))) {
            $this->vlist->forwardMessage(dgettext('vlist', 'Error occurred when saving element.'));
            PHPWS_Core::reroute('index.php?module=vlist&aop=edit_element&element=' . $this->id);
        } else {
            $this->vlist->forwardMessage(dgettext('vlist', 'Element saved successfully.'));
            PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
        }

    }


    function save()
    {
        //print_r($_POST); exit;
        if ($this->post()) {
            if (isset($_POST['id'])) {
                $update = true;
            } else {
                $update = false;
            }
            if (PHPWS_Error::logIfError($this->saveElement($update))) {
                $this->vlist->forwardMessage(dgettext('vlist', 'Error occurred when saving element.'));
                PHPWS_Core::reroute('index.php?module=vlist&aop=edit_element&element=' . $this->id);
            } else {
                if ($this->type == 'Checkbox' || $this->type == 'Dropbox' || $this->type == 'Radiobutton' || $this->type == 'Multiselect') {
                    $this->vlist->forwardMessage(dgettext('vlist', 'Element saved successfully, please add your options.'));
                    PHPWS_Core::reroute('index.php?module=vlist&aop=edit_options&element=' . $this->id);
                } else {
                    $this->vlist->forwardMessage(dgettext('vlist', 'Element saved successfully.'));
                    PHPWS_Core::reroute('index.php?module=vlist&aop=menu&tab=elements');
                }
            }
        } else {
            $this->vlist->forwardMessage($this->vlist->message);
            PHPWS_Core::reroute('index.php?module=vlist&aop=edit_element&element=' . $this->id . '&type=' . $this->type);
        }
    }


    public function saveElement($update=false)
    {
        $db = new PHPWS_DB($this->db_element);
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }

        /* not sure I need this anymore, don;t think so at the moment */
        /* begin save options
         $db = new PHPWS_DB($this->db_option);
         foreach($this->options as $option) {
         if($update == false) {
         $db->addValue('element_id', $this->id);
         $result = $db->insert();
         } else {
         $db->addWhere('id', $option['id']);
         $db->addValue('label', $option['label']);
         $db->addValue('element_id', $this->id);
         $db->addValue('sort', $option['sort']);
         $result = $db->update();
         }
         if (PEAR::isError($result)) {
         return $result;
         }
         }
         /* end save options */

    }


    public function post()
    {
        //print_r($_POST); exit;
        if (empty($_POST['label'])) {
            $errors[] = dgettext('vlist', 'You must give this element a label.');
        } else {
            $this->setTitle($_POST['label']);
        }

        $this->type = $_POST['type'];

        if (isset($_POST['value'])) {
            $this->setValue($_POST['value']);
        }

        isset($_POST['active']) ?
        $this->active = 1 :
        $this->active = 0 ;

        isset($_POST['required']) ?
        $this->required = 1 :
        $this->required = 0 ;

        if (isset($_POST['numoptions'])) {
            $this->numoptions = (int)$_POST['numoptions'];
        }

        if (isset($_POST['size'])) {
            $this->size = (int)$_POST['size'];
        }

        if (isset($_POST['maxsize'])) {
            $this->maxsize = (int)$_POST['maxsize'];
        }

        if (isset($_POST['rows'])) {
            $this->rows = (int)$_POST['rows'];
        }

        if (isset($_POST['cols'])) {
            $this->cols = (int)$_POST['cols'];
        }

        if (isset($_POST['sort'])) {
            $this->sort = (int)$_POST['sort'];
        }

        isset($_POST['list']) ?
        $this->list = 1 :
        $this->list = 0 ;

        isset($_POST['search']) ?
        $this->search = 1 :
        $this->search = 0 ;

        isset($_POST['private']) ?
        $this->private = 1 :
        $this->private = 0 ;

        if (isset($errors)) {
            $this->vlist->message = implode('<br />', $errors);
            return false;
        } else {
            return true;
        }
    }


    public function viewLink()
    {
        $vars['uop']  = 'view_element';
        $vars['element'] = $this->id;
        return PHPWS_Text::moduleLink(dgettext('vlist', $this->title), 'vlist', $vars);
    }


}

?>