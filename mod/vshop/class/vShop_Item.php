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

        Core\Core::initModClass('filecabinet', 'Cabinet.php');
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
            Core\Core::initModClass('vshop', 'vShop_Dept.php');
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


    public function view()
    {
        if (!$this->id) {
            Core\Core::errorPage(404);
        }

        $key = new Key($this->key_id);

        if (!$key->allowView()) {
            Current_User::requireLogin();            
        }

        Layout::addPageTitle($this->getTitle());
        $tpl['ITEM_LINKS'] = $this->links();
        $tpl['TITLE'] = $this->getTitle(true);
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

        $key->flag();

        return PHPWS_Template::process($tpl, 'vshop', 'view_item.tpl');
    }


    public function links()
    {
        $links = array();
        
        $links[] = $this->addLink(true) . ' ' . $this->addLink();
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
            $label = Icon::show('edit');
            $links[] = PHPWS_Text::secureLink($label, 'vshop', $vars);
        }
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop'] = 'delete_item';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the item %s?'), $this->getTitle());
            $js['LINK'] = Icon::show('delete');
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
            $tpl['ACTION'] = implode(' ', $links);

        return $tpl;
    }


    public function viewTpl()
    {
        $vars['item_id'] = $this->id;
        $vars['dept_id'] = $this->dept_id;
        $links = array();

        $links[] = $this->addLink(true) . ' ' . $this->addLink();

        if (Current_User::allow('vshop', 'edit_items')) {
            $label = Icon::show('edit');
            $vars['aop']  = 'edit_item';
            $links[] = PHPWS_Text::secureLink($label, 'vshop', $vars);
        }
        if (Current_User::allow('vshop', 'edit_items')) {
            $vars['aop'] = 'delete_item';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete the item %s?'), $this->getTitle());
            $js['LINK'] = Icon::show('delete');
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
            $tpl['ITEM_LINKS'] = implode(' ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('vshop_items');

        $result = $db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

        $this->saveKey();

        $search = new Search($this->key_id);
        $search->resetKeywords();
        $search->addKeywords($this->title);
        $search->addKeywords($this->description);
        $result = $search->save();
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

    }


    public function saveKey()
    {
        if (empty($this->key_id)) {
            $key = new Key;
        } else {
            $key = new Key($this->key_id);
            if (PHPWS_Error::isError($key->_error)) {
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
        Core\Core::initCoreClass('Link.php');
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