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

class vShop_Item {

    public $id             = 0;
    public $dept_id        = 0;
    public $key_id         = 0;
    public $title          = null;
    public $description    = null;
    public $file_id        = 0;
    public $price          = 0;
    public $taxable        = 0;
    public $stock          = 0;
    public $weight         = 0;
    public $shipping       = 0;

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
        $db = new PHPWS_DB('vshop_items');
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

    public function setDescription($description)
    {
        $this->description = PHPWS_Text::parseInput($description);
    }

    public function setDept_id($dept_id)
    {
        if (!is_numeric($dept_id)) {
            return false;
        } else {
            $this->dept_id = (int)$dept_id;
            return true;
        }
    }

    public function setFile_id($file_id)
    {
        $this->file_id = $file_id;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function setTaxable($taxable)
    {
        $this->taxable = $taxable;
    }

    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }


    public function getTitle($print=false, $breadcrumb=false)
    {
        if (empty($this->title)) {
            return null;
        }

        if ($print) {
            if ($breadcrumb) {
                if (vShop::countDepts() !== 1) {
                    return PHPWS_Text::moduleLink(PHPWS_Text::parseOutput(PHPWS_Settings::get('vshop', 'mod_title')), 'vshop') . ' &#187; ' . $this->getDept(true) . ' &#187; ' . PHPWS_Text::parseOutput($this->title);
                } else {
                    return $this->getDept(true) . ' &#187; ' . PHPWS_Text::parseOutput($this->title);
                }
            } else {
                return PHPWS_Text::parseOutput($this->title);
            }
        } else {
            return $this->title;
        }
    }

    public function getDescription($print=false)
    {
        if (empty($this->description)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->description);
        } else {
            return $this->description;
        }
    }

    public function getListDescription($length=60){
        return substr(ltrim(strip_tags(str_replace('<br />', ' ', $this->getDescription(true)))), 0, $length) . ' ...';
    }

    public function getFile()
    {
        if (!$this->file_id) {
            return null;
        }
        return Cabinet::getTag($this->file_id);
    }

    public function getThumbnail($link=false)
    {
        if (empty($this->file_id)) {
            return null;
        }

        PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
        $file = Cabinet::getFile($this->file_id);

        if ($file->isImage(true)) {
            $file->allowImageLink(false);
            if ($link) {
                return sprintf('<a href="%s">%s</a>', $this->viewLink(true), $file->getThumbnail());
            } else {
                return $file->getThumbnail();
            }
        } elseif ($file->isMedia() && $file->_source->isVideo()) {
            if ($link) {
                return sprintf('<a href="%s">%s</a>', $this->viewLink(true), $file->getThumbnail());
            } else {
                return $file->getThumbnail();
            }
        } else {
            return $file->getTag();
        }
    }

    public function getDept($print=false)
    {
        if (empty($this->dept_id)) {
            return null;
        }

        if ($print) {
            PHPWS_Core::initModClass('vshop', 'vShop_Dept.php');
            $dept = new vShop_Dept($this->dept_id);
            return $dept->viewLink();
        } else {
            return $this->dept_id;
        }
    }

    public function getPrice($print=false)
    {
        if (empty($this->price)) {
            return null;
        }

        if ($print) {
            if (PHPWS_Settings::get('vshop', 'curr_symbol_pos') == 1) {
                $price = PHPWS_Settings::get('vshop', 'currency_symbol') . number_format($this->price, 2, '.', ',');
                if (PHPWS_Settings::get('vshop', 'display_currency')) {
                    $price .= ' ' . PHPWS_Settings::get('vshop', 'currency');
                }
            } else {
                $price = number_format($this->price, 2, '.', ',') . PHPWS_Settings::get('vshop', 'currency_symbol');
                if (PHPWS_Settings::get('vshop', 'display_currency')) {
                    $price .= ' ' . PHPWS_Settings::get('vshop', 'currency');
                }
            }
            return $price;
        } else {
            return $this->price;
        }
    }

    public function getStock($print=false)
    {
        if ($print) {
            if ($this->stock > 0) {
                return sprintf(dgettext('vshop', '%s in stock'), PHPWS_Text::parseOutput($this->stock));
            } else {
                return dgettext('vshop', 'Out of stock');
            }
        } else {
            return $this->stock;
        }
    }

    public function getWeight($print=false)
    {
        if ($print) {
            if ($this->weight > 0) {
                return $this->weight . PHPWS_Settings::get('vshop', 'weight_unit');
            } else {
                return dgettext('vshop', 'No weight provided');
            }
        } else {
            return $this->weight;
        }
    }

    public function getShipping($print=false)
    {
        if ($print) {
            if ($this->shipping > 0) {
                if (PHPWS_Settings::get('vshop', 'curr_symbol_pos') == 1) {
                    return PHPWS_Settings::get('vshop', 'currency_symbol') . number_format($this->shipping, 2, '.', ',');
                } else {
                    return number_format($this->shipping, 2, '.', ',') . PHPWS_Settings::get('vshop', 'currency_symbol');
                }
            } else {
                return dgettext('vshop', 'No shipping rate provided');
            }
        } else {
            return $this->shipping;
        }
    }


    public function getAllAttributes($limit=false)
    {
        PHPWS_Core::initModClass('vshop', 'vShop_Attribute.php');
        $db = new PHPWS_DB('vshop_attributes');
        $db->addOrder('set_id asc');
        $db->addWhere('item_id', $this->id);
        if ($limit) {
            $db->setLimit((int)$limit);
        }
        $result = $db->getObjects('vShop_Attribute');
        return $result;
    }


    public function getOptionSets($print=false,$form=true)
    {
        if ($this->getQtyAttributes() == 0) {
            return null;
        }

        $all_sets = null;
        $choices = null;
        $buttons = null;
        $boxes = null;
        $match = null;

        /* get the sets */
        $db = new PHPWS_DB('vshop_attributes');
        $db->addWhere('item_id', $this->id);
        $db->addColumn('set_id', null, null, false, true);
        $set_ids = $db->select();

        foreach ($set_ids as $set) {
            $db = new PHPWS_DB('vshop_option_sets');
            $db->addWhere('id', $set['set_id']);
            $o_set = $db->select('row');

            /* get the values */
            $db = new PHPWS_DB('vshop_attributes');
            $db->addWhere('item_id', $this->id);
            $db->addWhere('set_id', $set['set_id']);
            $value_ids = $db->select();

            foreach ($value_ids as $value) {
                $db = new PHPWS_DB('vshop_option_values');
                $db->addWhere('id', $value['value_id']);
                $db->addOrder('sort asc');
                $s_values = $db->select('row');

                if ($print) { // print
                    // not sure about print yet
                } elseif ($form) { // form
                    $mods = null;
                    if ($value['price_mod'] > 0) {
                        if (PHPWS_Settings::get('vshop', 'curr_symbol_pos') == 1) {
                            $mods[] = $value['price_prefix'].PHPWS_Settings::get('vshop', 'currency_symbol').$value['price_mod'];
                        } else {
                            $mods[] = $value['price_prefix'].$value['price_mod'].PHPWS_Settings::get('vshop', 'currency_symbol');
                        }
                    }
                    if ($value['weight_mod'] > 0) {
                        $mods[] = $value['weight_prefix'].$value['weight_mod'].PHPWS_Settings::get('vshop', 'weight_unit');
                    }
                    if ($mods) {
                        $mods = '(' . implode(', ', $mods) . ')';
                    }
                    if ($o_set['type'] == 1) { // list
                        $choices[$set['set_id']][$value['value_id']] = $s_values['title'].' '.$mods;
                    } elseif ($o_set['type'] == 2) { // buttons
                        $buttons .= PHPWS_Form::formRadio('options['.$set['set_id'].']', $value['value_id'], $match, null, $s_values['title'].' '.$mods) . "<br />\n";
                    } elseif ($o_set['type'] == 3) { // checkboxes
                        $boxes .= PHPWS_Form::formCheckBox('options['.$set['set_id'].'][]', $value['value_id'], $match, null, $s_values['title'].' '.$mods) . "<br />\n";
                    }
                } else { // array
                    $all_sets[$set['set_id']]['values']['value_'.$value['value_id']]['id'] = $s_values['id'];
                    $all_sets[$set['set_id']]['values']['value_'.$value['value_id']]['title'] = $s_values['title'];
                    $all_sets[$set['set_id']]['values']['value_'.$value['value_id']]['price_prefix'] = $value['price_prefix'];
                    $all_sets[$set['set_id']]['values']['value_'.$value['value_id']]['price_mod'] = $value['price_mod'];
                    $all_sets[$set['set_id']]['values']['value_'.$value['value_id']]['weight_prefix'] = $value['weight_prefix'];
                    $all_sets[$set['set_id']]['values']['value_'.$value['value_id']]['weight_mod'] = $value['weight_mod'];
                }
            }

            if ($print) { // print
                // not sure about print yet
            } elseif ($form) { // form
                if (isset($_REQUEST[$set['set_id']])) {
                    $match = 'options['.$set['set_id'].']';
                } else {
                    $match =null;
                }
                if ($o_set['type'] == 1) { // list
                    $all_sets .= '<strong>' . $o_set['title'] . '</strong><br />';
                    $all_sets .= PHPWS_Form::formSelect('options['.$set['set_id'].']', $choices[$set['set_id']]) . '<br /><br />';
                } elseif ($o_set['type'] == 2) { // buttons
                    $all_sets .= '<strong>' . $o_set['title'] . '</strong><br />';
                    $all_sets .= $buttons . '<br />';
                } elseif ($o_set['type'] == 3) { // checkboxes
                    $all_sets .= '<strong>' . $o_set['title'] . '</strong><br />';
                    $all_sets .= $boxes . '<br />';
                }
            } else { // array
                $all_sets[$set['set_id']]['id'] = $o_set['id'];
                $all_sets[$set['set_id']]['type'] = $o_set['type'];
                $all_sets[$set['set_id']]['title'] = $o_set['title'];
            }

        }

//        print_r($all_sets); exit;
        return $all_sets;
    }


    public function getQtyAttributes()
    {
        $db = new PHPWS_DB('vshop_attributes');
        $db->addWhere('item_id', $this->id);
        $qty = $db->count();
        return $qty;
    }


    public function view()
    {
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

// PHPWS_Core::initModClass('vshop', 'vShop_Cart.php');
// $cart = vShop_Cart::CreateInstance();
// $cart_data = $cart->GetCart();
// print_r($cart_data);

        $key = new Key($this->key_id);
        $options = $this->getQtyAttributes();

        if (!$key->allowView()) {
            Current_User::requireLogin();
        }

        if ($options > 0) {
            $form = new PHPWS_Form('vshop_addItem');
            $form->addHidden('module', 'vshop');
            $form->addHidden('uop', 'addto_cart');
            $form->addHidden('item', $this->id);
            $form->addHidden('dept', $this->dept_id);
            $form->addSubmit(dgettext('vshop', 'Add to cart'));
            $tpl = $form->getTemplate();
        }

        Layout::addPageTitle($this->getTitle());
        $tpl['ITEM_LINKS'] = $this->links();
        $tpl['TITLE'] = $this->getTitle(true);
        if ($options > 0) {
            $tpl['PRICE_NOTE'] = dgettext('vshop', 'From');
        }
        $tpl['PRICE'] = $this->getPrice(true);
        if (PHPWS_Settings::get('vshop', 'use_inventory')) {
            $tpl['STOCK'] = $this->getStock(true);
        }
        $tpl['WEIGHT'] = $this->getWeight(true);
        if (PHPWS_Settings::get('vshop', 'shipping_calculation') == 2) {
            $tpl['SHIPPING_LABEL'] = dgettext('vshop', 'Shipping');
            $tpl['SHIPPING'] = $this->getShipping(true);
        }
        $tpl['DESCRIPTION'] = PHPWS_Text::parseTag($this->getDescription(true));
        $tpl['FILE'] = $this->getFile();

        if ($options > 0) {
            $tpl['OPTIONS'] = $this->getOptionSets();
        }

        $key->flag();
        return PHPWS_Template::process($tpl, 'vshop', 'view_item.tpl');
    }


    public function links()
    {
        $links = array();

        if ($this->getQtyAttributes() < 1) {
            $links[] = $this->addLink(true) . ' ' . $this->addLink();
        } //else {
//            $links[] = sprintf('<a href="%s">', $this->viewLink(true)) . dgettext('vshop', 'Choose Options') . '</a>';
//        }
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['dept_id'] = $this->dept_id;
            $vars['item_id'] = $this->id;
            $vars['aop']  = 'edit_item';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit item'), 'vshop', $vars);
        }
        if (!PHPWS_Settings::get('vshop', 'use_breadcrumb')) {
            if (vShop::countDepts() !== 1) {
                $links[] = sprintf(dgettext('vshop', 'Belongs to: %s'), $this->getDept(true));
            }
        }

        if (is_array(vShop::navLinks())) {
            $links = array_merge($links, vShop::navLinks());
        }

        if($links)
            return implode(' | ', $links);
    }


    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* delete the item */
        $db = new PHPWS_DB('vshop_items');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

        Key::drop($this->key_id);
    }


    public function rowTag()
    {
        $vars['item_id'] = $this->id;
        $vars['dept_id'] = $this->dept_id;
        $links = array();

        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop']  = 'edit_item';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit'), 'vshop', $vars);
        }
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop'] = 'delete_item';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the item %s?'), $this->getTitle());
            $js['LINK'] = dgettext('vshop', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['TITLE'] = $this->viewLink();
        $tpl['DESCRIPTION'] = $this->getListDescription(120);
        $tpl['DEPT'] = $this->getDept(true);
        $tpl['ITEM_PRICE'] = $this->getPrice(true);
//        if (PHPWS_Settings::get('vshop', 'use_inventory')) {
            $tpl['ITEM_STOCK'] = $this->getStock();
//        }

        if($links)
            $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }


    public function viewTpl()
    {
        $vars['item_id'] = $this->id;
        $vars['dept_id'] = $this->dept_id;
        $links = array();

        if ($this->getQtyAttributes() < 1) {
            $links[] = $this->addLink(true) . ' ' . $this->addLink();
        } else {
            $links[] = sprintf('<a href="%s">', $this->viewLink(true)) . dgettext('vshop', 'Choose Options') . '</a>';
        }

        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop']  = 'edit_item';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit'), 'vshop', $vars);
        }
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop'] = 'delete_item';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the item %s?'), $this->getTitle());
            $js['LINK'] = dgettext('vshop', 'Delete');
            $links[] = javascript('confirm', $js);
        }

        $tpl['ITEM_TITLE'] = $this->viewLink();
        $tpl['ITEM_DESCRIPTION'] = $this->getDescription(true);
        $tpl['ITEM_PRICE'] = $this->getPrice(true);
        if (PHPWS_Settings::get('vshop', 'use_inventory')) {
            $tpl['ITEM_STOCK'] = $this->getStock(true);
        }

        if ($this->file_id) {
            $tpl['ITEM_THUMBNAIL'] = $this->getThumbnail(true);
        } else {
            $tpl['ITEM_THUMBNAIL'] = null;
        }

        if($links)
            $tpl['ITEM_LINKS'] = implode(' | ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('vshop_items');

        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            return $result;
        }

        $this->saveKey();

        $search = & new Search($this->key_id);
        $search->resetKeywords();
        $search->addKeywords($this->title);
        $search->addKeywords($this->description);
        $result = $search->save();
        if (PEAR::isError($result)) {
            return $result;
        }

    }


    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PEAR::isError($key->_error)) {
                $key = new Key;
            }
        }

        $key->setModule('vshop');
        $key->setItemName('item');
        $key->setItemId($this->id);
        $key->setUrl($this->viewLink(true));
        $key->active = 1;
        $key->setTitle($this->title);
        $key->setSummary($this->description);
        $result = $key->save();
        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        if (!$this->key_id) {
            $this->key_id = $key->id;
            $db = new PHPWS_DB('vshop_items');
            $db->addWhere('id', $this->id);
            $db->addValue('key_id', $this->key_id);
            PHPWS_Error::logIfError($db->update());
        }
        return true;
    }


    public function viewLink($bare=false)
    {
        PHPWS_Core::initCoreClass('Link.php');
        $link = new PHPWS_Link($this->title, 'vshop', array('dept'=>$this->dept_id, 'item'=>$this->id));
        $link->rewrite = MOD_REWRITE_ENABLED;

        if ($bare) {
            return $link->getAddress();
        } else {
            return $link->get();
        }
    }


    public function addLink($icon=false, $enabled=true)
    {
        if (PHPWS_Settings::get('vshop', 'use_inventory')) {
            if ($this->stock < 1 || !$enabled) {
                if ($icon) {
                    $link = '<img src="' . PHPWS_SOURCE_HTTP . 'mod/vshop/img/soldout.gif" width="12" height="12" alt="' . dgettext('vshop', 'Sold out') . '" title="' . dgettext('vshop', 'Sold out') . '" border="0" />';
                } else {
                    $link = dgettext('vshop', 'Sold out');
                }
                return $link;
            }
        }
        if ($icon) {
            $link = '<a href="index.php?module=vshop&amp;dept=' . $this->dept_id . '&amp;item=' . $this->id . '&amp;uop=addto_cart"><img src="' . PHPWS_SOURCE_HTTP . 'mod/vshop/img/plus.gif" width="12" height="12" alt="' . dgettext('vshop', 'Add') . '" title="' . dgettext('vshop', 'Add') . '" border="0" /></a>';
        } else {
            $link = PHPWS_Text::moduleLink(dgettext('vshop', 'Add to cart'), "vshop",  array('dept'=>$this->dept_id, 'item'=>$this->id, 'uop'=>'addto_cart'));
        }
        return $link;
    }

    public function subtractLink($icon=false)
    {
        if ($icon) {
            $link = '<a href="index.php?module=vshop&amp;dept=' . $this->dept_id . '&amp;item=' . $this->id . '&amp;uop=subtractfrom_cart"><img src="' . PHPWS_SOURCE_HTTP . 'mod/vshop/img/minus.gif" width="12" height="12" alt="' . dgettext('vshop', 'Subtract') . '" title="' . dgettext('vshop', 'Subtract') . '" border="0" /></a>';
        } else {
            $link = PHPWS_Text::moduleLink(dgettext('vshop', 'Subtract from cart'), "vshop",  array('dept'=>$this->dept_id, 'item'=>$this->id, 'uop'=>'subtractfrom_cart'));
        }
        return $link;
    }

}

?>