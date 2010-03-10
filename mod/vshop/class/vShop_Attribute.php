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

class vShop_Attribute {

    public $id             = 0;
    public $set_id         = null;
    public $value_id       = null;
    public $item_id        = null;
    public $price_mod      = 0;
    public $price_prefix   = null;
    public $weight_mod     = 0;
    public $weight_prefix  = null;

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
        $db = new PHPWS_DB('vshop_attributes');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }


    public function setSet_id($set_id)
    {
        $this->set_id = (int)$set_id;
    }

    public function setValue_id($value_id)
    {
        $this->value_id = (int)$value_id;
    }

    public function setItem_id($item_id)
    {
        $this->item_id = (int)$item_id;
    }

    public function setPrice_mod($price_mod)
    {
        $this->price_mod = $price_mod;
    }

    public function setPrice_prefix($price_prefix)
    {
        $this->price_prefix = $price_prefix;
    }

    public function setWeight_mod($weight_mod)
    {
        $this->weight_mod = $weight_mod;
    }

    public function setWeight_prefix($weight_prefix)
    {
        $this->weight_prefix = $weight_prefix;
    }



    public function getTitle($print=false)
    {
        if (empty($this->set_id) || empty($this->value_id)) {
            return null;
        }

        if ($print) {
            PHPWS_Core::initModClass('vshop', 'vShop_Option_set.php');
            $set = new vShop_Option_set($this->set_id);
            PHPWS_Core::initModClass('vshop', 'vShop_Option_value.php');
            $value = new vShop_Option_value($this->value_id);
            return $set->getTitle(true) . ' : ' . $value->getTitle(true);
        } else {
            return $this->set_id . ':' . $this->value_id;
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
            return $set->getTitle(true);
        } else {
            return $this->set_id;
        }
    }


    public function getValue($print=false)
    {
        if (empty($this->value_id)) {
            return null;
        }

        if ($print) {
            PHPWS_Core::initModClass('vshop', 'vShop_Option_value.php');
            $value = new vShop_Option_value($this->value_id);
            return $value->getTitle(true);
        } else {
            return $this->value_id;
        }
    }


    public function getPrice_mod($print=false)
    {
        if (empty($this->price_mod)) {
            return null;
        }

        if ($print) {
            if (PHPWS_Settings::get('vshop', 'curr_symbol_pos') == 1) {
                $price_mod = PHPWS_Settings::get('vshop', 'currency_symbol') . number_format($this->price_mod, 2, '.', ',');
                if (PHPWS_Settings::get('vshop', 'display_currency')) {
                    $price_mod .= ' ' . PHPWS_Settings::get('vshop', 'currency');
                }
            } else {
                $price_mod = number_format($this->price_mod, 2, '.', ',') . PHPWS_Settings::get('vshop', 'currency_symbol');
                if (PHPWS_Settings::get('vshop', 'display_currency')) {
                    $price_mod .= ' ' . PHPWS_Settings::get('vshop', 'currency');
                }
            }
            return $price_mod;
        } else {
            return $this->price_mod;
        }
    }


    public function getWeight_mod($print=false)
    {
        if (empty($this->weight_mod)) {
            return null;
        }

        if ($print) {
            return $this->weight_mod . PHPWS_Settings::get('vshop', 'weight_unit');
        } else {
            return $this->weight_mod;
        }
    }


    public function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        $tpl['ATTRIBUTE_LINKS'] = $this->links();
        $tpl['TITLE'] = $this->getTitle(true);

        return PHPWS_Template::process($tpl, 'vshop', 'view_attribute.tpl');
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['attribute_id'] = $this->id;
            $vars['aop']  = 'edit_attribute';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit attribute'), 'vshop', $vars);
        }

        if($links)
        return implode(' | ', $links);
    }

    public function delete()
    {
        if (!$this->id) {
            return;
        }

        $db = new PHPWS_DB('vshop_attributes');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());
    }


    public function rowTag()
    {
        $vars['attribute_id'] = $this->id;
        $links = array();

        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop']  = 'edit_attribute';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit'), 'vshop', $vars);

            $vars['aop'] = 'delete_attribute';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the attribute %s?'), $this->getTitle());
            $js['LINK'] = dgettext('vshop', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();

        if($links)
        $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }


    public function viewTpl()
    {
        $vars['attribute_id'] = $this->id;
        $links = array();

        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop']  = 'edit_attribute';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit'), 'vshop', $vars);
        }
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop'] = 'delete_attribute';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the attribute %s?'), $this->getTitle(true));
            $js['LINK'] = dgettext('vshop', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        //        $tpl['ATTRIBUTE_TITLE'] = $this->getTitle(true);
        $tpl['ATTRIBUTE_SET'] = $this->getSet(true);
        $tpl['ATTRIBUTE_VALUE'] = $this->getValue(true);
        $tpl['ATTRIBUTE_PRICE_PREFIX'] = $this->price_prefix;
        $tpl['ATTRIBUTE_PRICE_MOD'] = $this->getPrice_mod(true);
        $tpl['ATTRIBUTE_WEIGHT_PREFIX'] = $this->weight_prefix;
        $tpl['ATTRIBUTE_WEIGHT_MOD'] = $this->getWeight_mod(true);

        if($links)
        $tpl['ATTRIBUTE_LINKS'] = implode(' | ', $links);

        return $tpl;
    }

    public function save()
    {
        $db = new PHPWS_DB('vshop_attributes');
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }
    }


    public function viewLink()
    {
        $vars['aop']  = 'view_attribute';
        $vars['attribute'] = $this->id;
        return PHPWS_Text::moduleLink(dgettext('vshop', $this->title), 'vshop', $vars);
    }



}

?>