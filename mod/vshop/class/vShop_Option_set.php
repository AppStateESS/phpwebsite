<?php
/**
 * vshop - phpwebsite module
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

class vShop_Option_set {

    public $id             = 0;
    public $title          = null;
    public $type           = 0;

    public $_error         = null;


    public function __construct($id=0)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }


    public function init()
    {
        $db = new PHPWS_DB('vshop_option_sets');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }


    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function setType($type)
    {
        $this->type = (int)$type;
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

    public function getType($print=false)
    {
        if (empty($this->type)) {
            return null;
        }

        if ($print) {
            require PHPWS_SOURCE_DIR . 'mod/vshop/inc/option_types.php';
            return $types[$this->type];
        } else {
            return $this->type;
        }
    }

    public function getAllValues($limit=false)
    {
        PHPWS_Core::initModClass('vshop', 'vShop_Option_value.php');
        $db = new PHPWS_DB('vshop_option_values');
        $db->addOrder('sort asc');
        $db->addOrder('title asc');
        $db->addWhere('set_id', $this->id);
        if ($limit) {
            $db->setLimit((int)$limit);
        }
        $result = $db->getObjects('vShop_Option_value');
        return $result;
    }


    public function getQtyValues()
    {
        $db = new PHPWS_DB('vshop_option_values');
        $db->addWhere('set_id', $this->id);
        $qty = $db->count();
        return $qty;
    }

    public function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        $tpl['OPTION_SET_LINKS'] = $this->links();
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['TYPE'] = $this->getType(true);
        $tpl['SET_LABEL'] = dgettext('vshop', 'Set details');
        $tpl['VALUES_LABEL'] = dgettext('vshop', 'Possible values');
        $tpl['TITLE_LABEL'] = dgettext('vshop', 'Name: ');
        $tpl['TYPE_LABEL'] = dgettext('vshop', 'Element type: ');

        $values = $this->getAllValues();

        if (PHPWS_Error::logIfError($values)) {
            $this->vshop->content = dgettext('vshop', 'An error occurred when accessing this set\'s values.');
            return;
        }

        if ($values) {
            foreach ($values as $value) {
                $tpl['values'][] = $value->viewTpl();
            }
        } else {
            if (Current_User::allow('vshop', 'settings'))
            $tpl['EMPTY'] = dgettext('vshop', 'Click on "New value" to start.');
        }

        return PHPWS_Template::process($tpl, 'vshop', 'view_option_set.tpl');
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('vshop', 'settings')) {
            $vars['option_set_id'] = $this->id;
            $vars['aop']  = 'edit_option_set';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit option set'), 'vshop', $vars);
        }

        if (Current_User::allow('vshop', 'settings')) {
            $vars['aop']  = 'new_option_value';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Add Value'), 'vshop', $vars);
        }

        //        $links = array_merge($links, vShop::navLinks());

        if($links)
        return implode(' | ', $links);
    }

    public function delete()
    {
        if (!$this->id) {
            return;
        }

        $db = new PHPWS_DB('vshop_option_sets');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());
    }


    public function rowTag()
    {
        //        $vars['id'] = $this->id;
        $vars['option_set_id'] = $this->id;
        $links = array();

        if (Current_User::allow('vshop', 'settings')) {
            $vars['aop']  = 'new_option_value';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Add Value'), 'vshop', $vars);
        }

        if (Current_User::allow('vshop', 'settings')) {
            $vars['aop']  = 'edit_option_set';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit'), 'vshop', $vars);

            $vars['aop'] = 'delete_option_set';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the option set %s?'), $this->getTitle());
            $js['LINK'] = dgettext('vshop', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['TYPE'] = $this->getType(true);
        $tpl['VALUES'] = $this->getQtyValues();

        if($links)
        $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('vshop_option_sets');

        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }
    }


    public function viewLink()
    {
        $vars['aop']  = 'view_option_set';
        $vars['option_set'] = $this->id;
        return PHPWS_Text::moduleLink(dgettext('vshop', $this->title), 'vshop', $vars);
    }



}

?>