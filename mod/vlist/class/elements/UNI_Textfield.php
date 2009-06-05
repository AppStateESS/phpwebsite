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
    * @version $Id: $
    * @author Verdon Vaillancourt <verdonv at users dot sourceforge dot net>
*/

require_once(PHPWS_SOURCE_DIR . 'mod/vlist/class/UNI_Element.php');

class UNI_Textfield extends UNI_Element {


    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }


    function hasOptions() 
    {
        return false;
    }


    function view($match=null) 
    {
        if(isset($_REQUEST['UNI_' . $this->id])) {
            $this->setValue($_REQUEST['UNI_' . $this->id]);
        }

        if($this->required) {
            $tpl['REQUIRED_FLAG'] = '&#42;';
        }

        $tpl['LABEL'] = $this->getTitle();
        $tpl['SIZE'] = $this->getSize();
        $tpl['MAXSIZE'] = $this->getMaxsize();
        if (isset($match)) {
            $tpl['VALUE'] = $match;
        } else {
            $tpl['VALUE'] = $this->getValue();
        }
        $tpl['NAME'] = 'UNI_' . $this->id;

        return PHPWS_Template::processTemplate($tpl, 'vlist', 'elements/view_textfield.tpl');
    } 


    function edit() 
    {
        $numoptions = $this->numoptions;
        if ($this->type) {
            $type = $this->type;
        } else {
            $type = $_REQUEST['type'];
        }
        $elements[0] =  PHPWS_Form::formHidden('module', 'vlist') . 
                        PHPWS_Form::formHidden('aop', 'post_element') . 
                        PHPWS_Form::formHidden('type', $type) ;
        if ($this->id) {
            $elements[0] .=  PHPWS_Form::formHidden('id', $this->id) ; 
        }

        $tpl['LABEL_LABEL'] = dgettext('vlist', 'Label/name');
        $tpl['LABEL_INPUT'] = PHPWS_Form::formTextField('label', $this->getTitle(), UNI_DEFAULT_SIZE, UNI_DEFAULT_MAXSIZE);

        $tpl['SIZE_LABEL'] = dgettext('vlist', 'Size');
        $tpl['SIZE_INPUT'] = PHPWS_Form::formTextField('size', $this->getSize(), 5, 3);
        $tpl['MAXSIZE_LABEL'] = dgettext('vlist', 'Maxsize');
        $tpl['MAXSIZE_INPUT'] = PHPWS_Form::formTextField('maxsize', $this->getMaxsize(), 5, 3);
        $tpl['VALUE_LABEL'] = dgettext('vlist', 'Default value');
        $tpl['VALUE_INPUT'] = PHPWS_Form::formTextField('value', $this->getValue(), UNI_DEFAULT_SIZE, UNI_DEFAULT_MAXSIZE);

        $tpl['REQUIRE_LABEL'] = dgettext('vlist', 'Required');
        $tpl['REQUIRE_INPUT'] = PHPWS_Form::formCheckBox('required', 1, $this->required);
        $tpl['ACTIVE_LABEL'] = dgettext('vlist', 'Active');
        $tpl['ACTIVE_INPUT'] = PHPWS_Form::formCheckBox('active', 1, $this->active);
        $tpl['SORT_LABEL'] = dgettext('vlist', 'Sort order');
        $tpl['SORT_INPUT'] = PHPWS_Form::formTextField('sort', $this->sort, 5, 3);
        $tpl['LIST_LABEL'] = dgettext('vlist', 'Use in list');
        $tpl['LIST_INPUT'] = PHPWS_Form::formCheckBox('list', 1, $this->list);
//        $tpl['SEARCH_LABEL'] = dgettext('vlist', 'Use in advanced search');
//        $tpl['SEARCH_INPUT'] = PHPWS_Form::formCheckBox('search', 1, $this->search);

        $tpl['NEXT_BUTTON'] = PHPWS_Form::formSubmit(sprintf(dgettext('vlist', 'Save %s field'), $type));

        $elements[0] .= PHPWS_Template::processTemplate($tpl, 'vlist', 'elements/edit_textfield.tpl');

        return PHPWS_Form::makeForm('UNI_Textfield_edit', 'index.php', $elements, 'post', null, null);
    } 


}

?>