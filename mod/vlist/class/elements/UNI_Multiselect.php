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

class UNI_Multiselect extends UNI_Element {


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
        return true;
    }


    function view($match=null) 
    {
        if(isset($_REQUEST['UNI_' . $this->id]) && is_array($_REQUEST['UNI_' . $this->id])) {
            $this->setValue($_REQUEST['UNI_' . $this->id]);
        }

        if($this->required) {
            $tpl['REQUIRED_FLAG'] = '&#42;';
        }

        if (isset($match)) {
            $match = $match;
        } else {
            $match = $this->getValue();
        }
        $optionText = array();
        $optionSort = array();
        $optionId = array();
        foreach ($this->options as $option) {
            $optionText[] = $option['label'];
            $optionSort[] = $option['sort'];
            $optionId[] = $option['id'];
        }

        $tpl['LABEL'] = PHPWS_Text::parseOutput($this->getTitle());

        for($i = 0; $i < sizeof($optionId); $i++) {
            $options[$optionId[$i]] = $optionText[$i]; 
        }
        $size = null;
        $tpl['MULTISELECT'] = PHPWS_Form::formMultipleSelect('UNI_' . $this->id, $options, $match, false, true, $size);

        return PHPWS_Template::processTemplate($tpl, 'vlist', 'elements/view_multiselect.tpl');
    } 


    function edit() 
    {
        $numoptions = $this->numoptions;
        $elements[0] =  PHPWS_Form::formHidden('module', 'vlist') . 
                        PHPWS_Form::formHidden('aop', 'post_element') . 
                        PHPWS_Form::formHidden('type', 'Multiselect') ;
        if ($this->id) {
            $elements[0] .=  PHPWS_Form::formHidden('id', $this->id) ; 
        }

        $tpl['LABEL_LABEL'] = dgettext('vlist', 'Label/name');
        $tpl['LABEL_INPUT'] = PHPWS_Form::formTextField('label', $this->getTitle(), UNI_DEFAULT_SIZE, UNI_DEFAULT_MAXSIZE);
        $tpl['OPTIONS_LABEL'] = dgettext('vlist', 'Number of options');
        $tpl['OPTIONS_INPUT'] = PHPWS_Form::formTextField('numoptions', $numoptions, 5, 3);
        $tpl['REQUIRE_LABEL'] = dgettext('vlist', 'Required');
        $tpl['REQUIRE_INPUT'] = PHPWS_Form::formCheckBox('required', 1, $this->required);
        $tpl['ACTIVE_LABEL'] = dgettext('vlist', 'Active');
        $tpl['ACTIVE_INPUT'] = PHPWS_Form::formCheckBox('active', 1, $this->active);
        $tpl['SORT_LABEL'] = dgettext('vlist', 'Sort order');
        $tpl['SORT_INPUT'] = PHPWS_Form::formTextField('sort', $this->sort, 5, 3);
        $tpl['LIST_LABEL'] = dgettext('vlist', 'Use in list');
        $tpl['LIST_INPUT'] = PHPWS_Form::formCheckBox('list', 1, $this->list);
        $tpl['SEARCH_LABEL'] = dgettext('vlist', 'Use in advanced search');
        $tpl['SEARCH_INPUT'] = PHPWS_Form::formCheckBox('search', 1, $this->search);

        $tpl['NEXT_BUTTON'] = PHPWS_Form::formSubmit(dgettext('vlist', 'Next'));

        $elements[0] .= PHPWS_Template::processTemplate($tpl, 'vlist', 'elements/edit_multiselect.tpl');

        return PHPWS_Form::makeForm('UNI_Multiselect_edit', 'index.php', $elements, 'post', null, null);
    } 


}

?>