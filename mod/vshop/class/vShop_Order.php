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
 * @version $Id:  $
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

class vShop_Order {

    public $id              = 0;
    public $first_name      = null;
    public $last_name       = null;
    public $email           = null;
    public $phone           = null;
    public $address_1       = null;
    public $address_2       = null;
    public $city            = null;
    public $state           = null;
    public $country         = null;
    public $postal_code     = null;
    public $comments        = null;
    public $pay_method      = null;
    public $order_array     = null;
    public $order_date      = null;
    public $update_date     = null;
    public $completed       = null;
    public $cancelled       = null;
    public $status          = null;
    public $ip_address      = null;

    public $_error          = null;


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
        $db = new PHPWS_DB('vshop_orders');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            $this->_error = & $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
    }


    public function setFirst_name($first_name)
    {
        $this->first_name = strip_tags($first_name);
    }

    public function setLast_name($last_name)
    {
        $this->last_name = strip_tags($last_name);
    }

    public function setEmail($email)
    {
        if (PHPWS_Text::isValidInput($email, 'email')) {
            $this->email = $email;
            return true;
        } else {
            return false;
        }
    }

    public function setPhone($phone)
    {
        $this->phone = strip_tags($phone);
    }

    public function setAddress_1($address_1)
    {
        $this->address_1 = strip_tags($address_1);
    }

    public function setAddress_2($address_2)
    {
        $this->address_2 = strip_tags($address_2);
    }

    public function setCity($city)
    {
        $this->city = strip_tags($city);
    }

    public function setState($state)
    {
        $this->state = strip_tags($state);
    }

    public function setCountry($country)
    {
        $this->country = strip_tags($country);
    }

    public function setPostal_code($postal_code)
    {
        $this->postal_code = strip_tags($postal_code);
    }

    public function setComments($comments)
    {
        $this->comments = strip_tags($comments);
    }

    public function setPay_method($pay_method)
    {
        $this->pay_method = $pay_method;
    }

    public function setIp_address($ip_address)
    {
        $this->ip_address = $ip_address;
    }




    function getOrder_date($print=false, $format=null)
    {
        if (empty($this->order_date)) {
            return null;
        }

        if ($print) {
            if ($format) {
                $format = $format;
            } else {
                $format = VSHOP_DATE_FORMAT;
            }
            return strftime($format, $this->order_date);
        } else {
            return $this->order_date;
        }
    }

    function getUpdate_date($print=false, $format=null)
    {
        if (empty($this->update_date)) {
            return null;
        }

        if ($print) {
            if ($format) {
                $format = $format;
            } else {
                $format = VSHOP_DATE_FORMAT;
            }
            return strftime($format, $this->update_date);
        } else {
            return $this->update_date;
        }
    }

    public function getTitle($print=false)
    {
        if (empty($this->first_name) && empty($this->last_name) && empty($this->id)) {
            return null;
        }

        if ($print) {
            return dgettext('vshop', 'Order #') . PHPWS_Text::parseOutput($this->id) . ' - ' . PHPWS_Text::parseOutput($this->first_name) . ' ' . PHPWS_Text::parseOutput($this->last_name);
        } else {
            return $this->id . ' ' . $this->first_name . ' ' . $this->last_name;
        }
    }


    public function getCustomer($print=false)
    {
        if (empty($this->first_name) && empty($this->last_name)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->first_name) . ' ' . PHPWS_Text::parseOutput($this->last_name);
        } else {
            return $this->first_name . ' ' . $this->last_name;
        }
    }

    public function getAddress($print=false)
    {
        if (empty($this->address_1) && empty($this->address_2) && empty($this->city) && empty($this->state) && empty($this->country) && empty($this->postal_code)) {
            return null;
        }

        if ($print) {
            $address = null;
            $address .= PHPWS_Text::parseOutput($this->address_1);
            if ($this->address_2) {
                $address .= '<br />' . PHPWS_Text::parseOutput($this->address_2);
            }
            if ($this->city) {
                $address .= '<br />' . PHPWS_Text::parseOutput($this->city);
            }
            if ($this->state) {
                $address .= ', ' . PHPWS_Text::parseOutput($this->state);
            }
            if ($this->country) {
                $address .= '<br />' . PHPWS_Text::parseOutput($this->country);
            }
            if ($this->postal_code) {
                $address .= '<br />' . PHPWS_Text::parseOutput($this->postal_code);
            }
            return $address;
        } else {
            return $this->address_1 . "\n" . $this->address_2 . "\n" . $this->city . ', ' . $this->state . "\n" . $this->country . "\n" . $this->postal_code;
        }
    }

    public function getPhone($print=false)
    {
        if (empty($this->phone)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->phone);
        } else {
            return $this->phone;
        }
    }

    public function getEmail($print=false)
    {
        if (empty($this->email)) {
            return null;
        }

        if ($print) {
            return '<a href="mailto:' . $this->email . '">' . PHPWS_Text::parseOutput($this->email) . '</a>';
        } else {
            return $this->email;
        }
    }

    public function getComments($print=false)
    {
        if (empty($this->comments)) {
            return null;
        }

        if ($print) {
            return PHPWS_Text::parseOutput($this->comments);
        } else {
            return $this->comments;
        }
    }

    public function getStatus($print=false)
    {
        if (empty($this->status)) {
            return null;
        }

        if ($print) {
            require PHPWS_SOURCE_DIR . 'mod/vshop/inc/statuses.php';
            return $statuses[$this->status];
        } else {
            return $this->status;
        }
    }

    public function getPay_method($print=false)
    {
        if (empty($this->pay_method)) {
            return null;
        }

        if ($print) {
            require PHPWS_SOURCE_DIR . 'mod/vshop/inc/payment_methods.php';
            return $pay_methods[$this->pay_method];
        } else {
            return $this->pay_method;
        }
    }


    public function view()
    {
//print_r($this->order_array);
        if (!$this->id) {
            PHPWS_Core::errorPage(404);
        }

        $tpl['ORDER_LINKS'] = $this->links();

        $tpl['STATUS_LABEL'] = dgettext('vshop', 'Order status:');
        $tpl['STATUS'] = $this->getStatus(true);

        $tpl['IP_ADDRESS_LABEL'] = dgettext('vshop', 'IP Address:');
        $tpl['IP_ADDRESS'] = $this->ip_address;

        $tpl['CUSTOMER_LABEL'] = dgettext('vshop', 'Customer details');
        $tpl['CUSTOMER'] = $this->getCustomer(true);

        $tpl['ADDRESS'] = $this->getAddress(true);

        $tpl['PHONE_LABEL'] = dgettext('vshop', 'Phone:');
        $tpl['PHONE'] = $this->getPhone(true);

        $tpl['EMAIL_LABEL'] = dgettext('vshop', 'E-mail:');
        $tpl['EMAIL'] = $this->getEmail(true);

        $tpl['COMMENTS_LABEL'] = dgettext('vshop', 'Customer remarks:');
        $tpl['COMMENTS'] = $this->getComments(true);

        $tpl['PAY_METHOD_LABEL'] = dgettext('vshop', 'Payment method:');
        $tpl['PAY_METHOD'] = $this->getPay_method(true);

        return PHPWS_Template::process($tpl, 'vshop', 'view_order.tpl');
    }


    public function links()
    {
        $links = array();

        if (Current_User::allow('vshop', 'edit_orders')) {
            $vars['id'] = $this->id;
            $vars['aop']  = 'edit_order';
            $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit order'), 'vshop', $vars);
        }

        $links = array_merge($links, vShop::navLinks());

        if($links)
            return implode(' | ', $links);
    }

    public function delete()
    {
        if (!$this->id) {
            return;
        }

        /* delete the order */
        $db = new PHPWS_DB('vshop_orders');
        $db->addWhere('id', $this->id);
        PHPWS_Error::logIfError($db->delete());

    }


    public function rowTag()
    {
        $vars['id'] = $this->id;
        $links = array();

        if (Current_User::allow('vshop', 'edit_orders')) {

            if ($_GET['tab'] == 'orders') {
                $vars['aop']  = 'edit_order';
                $links[] = PHPWS_Text::secureLink(dgettext('vshop', 'Edit'), 'vshop', $vars);
            }

            $vars['aop'] = 'delete_order';
            $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to delete order %s from %s?'), $this->id, $this->getCustomer());
            $js['LINK'] = dgettext('vshop', 'Delete');
            $links[] = javascript('confirm', $js);

            if ($_GET['tab'] == 'orders') {
                $vars['aop'] = 'cancel_order';
                $js['ADDRESS'] = PHPWS_Text::linkAddress('vshop', $vars, true);
                $js['QUESTION'] = sprintf(dgettext('vshop', 'Are you sure you want to cancel order %s from %s?'), $this->id, $this->getCustomer());
                $js['LINK'] = dgettext('vshop', 'Cancel');
                $links[] = javascript('confirm', $js);
            }

        }

        $tpl['ORDER'] = $this->viewLink();
        $tpl['TOTAL'] = number_format($this->order_array['total_grand'], 2, '.', ',');
        $tpl['ORDERED'] = $this->getOrder_date(true);
        $tpl['UPDATED'] = $this->getUpdate_date(true);

        if (javascriptEnabled()) {
            $js_vars['label'] = $this->getStatus(true);
            $js_vars['width'] = 400;
            $js_vars['height'] = 250;

            $vars['aop'] = 'set_status';

            $js_vars['address'] = PHPWS_Text::linkAddress('vshop', $vars, true);
            $link = javascript('open_window', $js_vars);
            
            $tpl['STATUS'] = $link;
        } else {
            $tpl['STATUS'] = $this->getStatus(true);
        }

        if($links)
            $tpl['ACTION'] = implode(' | ', $links);

        return $tpl;
    }


    public function save()
    {
        $db = new PHPWS_DB('vshop_orders');

        $result = $db->saveObject($this);
        if (PHPWS_Error::isError($result)) {
            return $result;
        }

    }


    public function updateInventory($add=false)
    {
        PHPWS_Core::initModClass('vshop', 'vShop_Item.php');
        foreach ($this->order_array['items'] as $id=>$var) {
            $item = new vShop_Item($this->order_array['items'][$id]['id']);
            $old_qty = $item->stock;
            if ($add) {
                $new_qty = $old_qty + $var['qty'];
            } else {
                $new_qty = $old_qty - $var['qty'];
            }
            $item->setStock($new_qty);
            $item->save();
        }
    }


    public function viewLink()
    {
        $vars['aop']  = 'view_order';
        $vars['order'] = $this->id;
        return PHPWS_Text::moduleLink(dgettext('vshop', $this->getTitle(true)), 'vshop', $vars);
    }



}

?>