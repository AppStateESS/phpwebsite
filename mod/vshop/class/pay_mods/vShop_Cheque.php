<?php
/**
    * **************************************************************
    * 
    * NOTE: There are no special requirements for using this payment
    * method and nothing to configure.
    * 
    * **************************************************************
*/

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


  
class vShop_Cheque {

    public $_orderID;

    function __construct($orderID){
      $this->_orderID = $orderID;
    }


    public function form () {
        $form = new \core\Form('vshop_payment');
        $form->addSubmit(dgettext('vshop', 'Confirm & Finish'));
        $form->addHidden('module', 'vshop');
        $form->addHidden('order_id', $this->_orderID);
        $form->addHidden('uop', 'complete_order');

        $tpl = $form->getTemplate();
        $tpl['MESSAGE'] = dgettext('vshop', 'Pressing Confirm will e-mail your order to us. We will process your order and reply to you as soon as possible, to arrange payment details.');
        
        return $tpl;
    
    }


    public function complete ($order) {
        $test = false;
        if ($test) {
            $errors[] = dgettext('vshop', 'Oops');
            $errors[] = dgettext('vshop', 'one of us did something wrong');
            return $errors;
        } else {
            return true;
        }
    }


    public function successMessage () {
        $message = null;
        $message[] = dgettext('vshop', 'Your order has been completed successfully.');
        $message[] = dgettext('vshop', 'We will contact you to arrange payment as soon as possible. When payment has been arranged, we will be able to fulfill your order.');
        return implode('<br />', $message);
    }


}

?>