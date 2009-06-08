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
    * @version $Id: $
    * @author Verdon Vaillancourt <verdonv at gmail dot com>
*/

$settings['enable_sidebox']         = 0;
$settings['sidebox_homeonly']       = 0;
$settings['mod_title']              = dgettext('vshop', 'vShop');
$settings['sidebox_text']           = null;
$settings['enable_files']           = 1;
$settings['max_width']              = 300;
$settings['max_height']             = 300;
$settings['use_inventory']          = 1;
$settings['use_breadcrumb']         = 0;
$settings['checkout_inst']          = null;
$settings['admin_email']            = null;
$settings['order_message']          = null;
$settings['weight_unit']            = 'Kgs';
$settings['currency']               = 'USD';
$settings['currency_symbol']        = '$';
$settings['curr_symbol_pos']        = 1;
$settings['display_currency']       = 1;
$settings['shipping_calculation']   = 2;
$settings['shipping_flat']          = 10;
$settings['shipping_percent']       = 2;
$settings['shipping_minimum']       = 0;
$settings['shipping_maximum']       = 0;
$settings['payment_methods']        = array('vShop_Cheque');
$settings['secure_checkout']        = 0;
$settings['secure_url']             = null;

?>