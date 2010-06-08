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


class vShop_Runtime
{

    public function showCart() {
        \core\Core::initModClass('vshop', 'vShop_Cart.php');
        $cart = vShop_Cart::CreateInstance();
        $cart_data = $cart->GetCart();
//        print_r($cart_data);
        if (!empty($cart_data)) {
            $tpl['TITLE'] = sprintf(dgettext('vshop', '%s Cart'), \core\Text::parseOutput(core\Settings::get('vshop', 'mod_title')));
            $tpl['LABEL'] = dgettext('vshop', 'Cart contents');
            $tpl['NAME_LABEL'] = dgettext('vshop', 'Name');
            $tpl['QTY_LABEL'] = dgettext('vshop', 'Qty');
            $total_items = 0.00;
            foreach ($cart_data as $id=>$val) {
                $qty = $cart_data[$id]['count'];
                \core\Core::initModClass('vshop', 'vShop_Item.php');
                $item = new vShop_Item($id);
                $subtotal = $item->price * $qty;
                $total_items = $total_items + $subtotal;
                $addLink = $item->addLink(true);
                if (core\Settings::get('vshop', 'use_inventory')) { 
                    if ($qty >= $item->stock) {
                        $addLink = $item->addLink(true, false);
                    }
                }

                $tpl['items'][] = array(
                                    'ID'        => $id, 
                                    'QTY'       => $qty, 
                                    'NAME'      => $item->viewLink(), 
                                    'ADD'       => $addLink, 
                                    'SUBTRACT'  => $item->subtractLink(true), 
                                    'SUBTOTAL'  => number_format($subtotal, 2, '.', ',')
                                 );
            }
            $tpl['TOTAL_LABEL'] = dgettext('vshop', 'Total');
            $tpl['TOTAL'] = number_format($total_items, 2, '.', ',');
            if (!core\Settings::get('vshop', 'secure_checkout')) {
                $tpl['CHECKOUT_LINK'] = '<a href="index.php?module=vshop&amp;uop=checkout"><img src="' . PHPWS_SOURCE_HTTP . 'mod/vshop/img/checkout.gif" width="12" height="12" alt="' . dgettext('vshop', 'Checkout') . '" title="' . dgettext('vshop', 'Checkout') . '" border="0" /> ' . dgettext('vshop', 'Checkout') . '</a>';
            } else {
                $tpl['CHECKOUT_LINK'] = '<a href="' . \core\Settings::get('vshop', 'secure_url') . 'index.php?module=vshop&amp;uop=checkout"><img src="' . PHPWS_SOURCE_HTTP . 'mod/vshop/img/checkout.gif" width="12" height="12" alt="' . dgettext('vshop', 'Checkout') . '" title="' . dgettext('vshop', 'Checkout') . '" border="0" /> ' . dgettext('vshop', 'Checkout') . '</a>';
            }
            $tpl['BROWSE_LINK'] = \core\Text::moduleLink(dgettext('vshop', 'Browse  all items'), 'vshop', array('uop'=>'list_depts'));

            $js['ADDRESS'] = \core\Text::linkAddress('vshop', array('uop'=>'clear_cart'), true);
            $js['QUESTION'] = dgettext('vshop', 'Are you sure you want to completely clear the contents of your cart?');
            $js['LINK'] = dgettext('vshop', 'Clear Cart');
            $tpl['CLEAR_LINK'] = javascript('confirm', $js);

            \core\Core::initModClass('layout', 'Layout.php');
            Layout::add(core\Template::process($tpl, 'vshop', 'cart.tpl'), 'vshop', 'vshop_cart');
        }
    }

    public static function showBlock() {
        if (core\Settings::get('vshop', 'enable_sidebox')) {
            if (core\Settings::get('vshop', 'sidebox_homeonly')) {
                $key = \core\Key::getCurrent();
                if (!empty($key) && $key->isHomeKey()) {
                    vShop_Runtime::showvShopBlock();
                }
            } else {
                vShop_Runtime::showvShopBlock();
            }
        }
    }

    public function showvShopBlock() {

        $db = new \core\DB('vshop_items');
        $db->addColumn('id');
        $db->addOrder('rand');
        $db->setLimit(1);
        $result = $db->select();
        if (!core\Error::logIfError($result) && !empty($result)) {
            $tpl['TITLE'] = \core\Text::parseOutput(core\Settings::get('vshop', 'mod_title'));
            $tpl['LABEL'] = dgettext('vshop', 'Random Item');
            $tpl['TEXT'] = \core\Text::parseOutput(core\Settings::get('vshop', 'sidebox_text'));
            \core\Core::initModClass('vshop', 'vShop_Item.php');
            $item = new vShop_Item($result[0]['id']);
            $tpl['NAME'] = $item->viewLink();
            $tpl['ADD'] = $item->addLink(true) . ' ' . $item->addLink();
            if ($item->file_id) {
                $tpl['THUMBNAIL'] = $item->getThumbnail(true);
            } else {
                $tpl['THUMBNAIL'] = null;
            }
            $tpl['LINK'] = \core\Text::moduleLink(dgettext('vshop', 'Browse all items'), 'vshop');

            \core\Core::initModClass('layout', 'Layout.php');
            Layout::add(core\Template::process($tpl, 'vshop', 'block.tpl'), 'vshop', 'vshop_sidebox');
        }

    }

}

?>