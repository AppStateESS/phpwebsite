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

require_once(PHPWS_SOURCE_DIR . 'mod/vlist/class/UNI_Element.php');

class UNI_Div extends UNI_Element {


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

        $tpl['LABEL'] = $this->getTitle();

        return \core\Template::processTemplate($tpl, 'vlist', 'elements/view_div.tpl');
    }


    function edit()
    {
        $elements[0] =  \core\Form::formHidden('module', 'vlist') . 
                        \core\Form::formHidden('aop', 'post_element') . 
                        \core\Form::formHidden('type', 'Div') ;
        if ($this->id) {
            $elements[0] .=  \core\Form::formHidden('id', $this->id) ;
        }

        $tpl['LABEL_LABEL'] = dgettext('vlist', 'Label/name');
        $tpl['LABEL_INPUT'] = \core\Form::formTextField('label', $this->getTitle(), UNI_DEFAULT_SIZE, UNI_DEFAULT_MAXSIZE);

        $tpl['ACTIVE_LABEL'] = dgettext('vlist', 'Active');
        $tpl['ACTIVE_INPUT'] = \core\Form::formCheckBox('active', 1, $this->active);
        $tpl['SORT_LABEL'] = dgettext('vlist', 'Sort order');
        $tpl['SORT_INPUT'] = \core\Form::formTextField('sort', $this->sort, 5, 3);

        $tpl['NEXT_BUTTON'] = \core\Form::formSubmit(dgettext('vlist', 'Save Div Field'));

        $elements[0] .= \core\Template::processTemplate($tpl, 'vlist', 'elements/edit_div.tpl');

        return \core\Form::makeForm('UNI_Div_edit', 'index.php', $elements, 'post', null, null);
    }


}

?>