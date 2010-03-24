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

class vShop_Option_value {

    public $id             = 0;
    public $set_id         = null;
    public $sort           = 0;
    public $title          = null;

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
        $db = new PHPWS_DB('vshop_option_values');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
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

    public function setSet_id($set_id)
    {
        $this->set_id = (int)$set_id;
    }

    public function setSort($sort)
    {
        //print_r($sort); exit;
        $this->sort = (int)$sort;
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

    public function getSet($print=false)
    {
        if (empty($this->set_id)) {
            return null;
        }

        if ($print) {
            PHPWS_Core::initModClass('vshop', 'vShop_Option_set.php');
            $set = new vShop_Option_set($this->set_id);
            return $set->viewLink();
        } else {
            return $this->set_id;
        }
    }

    public function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        $tpl['OPTION_VALUE_LINKS'] = $this->links();
        $tpl['TITLE'] = $this->getTitle(true);
        $tpl['SET'] = $this->getSet(true);

        return PHPWS_Template::process($tpl, 'vshop', 'view_option_value.tpl');
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('vshop', 'settings')) {
            $vars['option_value_id'] = $this->id;
            $vars['aop']  = 'edit_option_value';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit option value'), 'vshop', $vars);
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

        $db = new PHPWS_DB('vshop_option_values');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());
    }


    public function rowTag()
    {
        //        $vars['id'] = $this->id;
        $vars['option_value_id'] = $this->id;
        $links = array();

        if (Current_User::allow('vshop', 'settings')) {
            $vars['aop']  = 'edit_option_value';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit'), 'vshop', $vars);

            $vars['aop'] = 'delete_option_value';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the option value %s?'), $this->getTitle());
            $js['LINK'] = dgettext('vshop', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['SET'] = $this->getSet(true);
        $tpl['SORT'] = $this->sort;

        if($links)
        $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }


    public function viewTpl()
    {
        $vars['option_value_id'] = $this->id;
        $vars['option_set_id'] = $this->set_id;
        $links = array();

        //        $links[] = $this->addLink(true) . ' ' . $this->addLink();

        if (Current_User::allow('vshop', 'settings')) {
            $vars['aop']  = 'edit_option_value';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit'), 'vshop', $vars);
        }
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop'] = 'delete_option_value';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the value %s?'), $this->getTitle());
            $js['LINK'] = dgettext('vshop', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['VALUE_TITLE'] = $this->viewLink();

        if($links)
        $tpl['VALUE_LINKS'] = implode(' | ', $links);

        return $tpl;
    }

    public function save()
    {
        $db = new PHPWS_DB('vshop_option_values');
        //print_r($db); exit;
        //        $db->setTestMode();
        //print_r($this); exit;
        $result = $db->saveObject($this);
        //print_r($result); exit;
        if (PHPWS_Error::isError($result)) {
            return $result;
        }
    }


    public function viewLink()
    {
        $vars['aop']  = 'view_option_value';
        $vars['option_value'] = $this->id;
        return PHPWS_Text::moduleLink(dgettext('vshop', $this->title), 'vshop', $vars);
    }



}

?>