<?php
/**
    * **************************************************************
    * 
    * NOTE: to use this method you must have a valid Paypal account, 
    * either premier or business. You must also go to your account's
    * Profile / Selling Preferences / Website Payment Preferences
    * and turn Auto Return 'On'. You must also set the return url to
    * http://www.yourdomain.com/index.php?module=vshop
    * 
    * Finally, YOU MUST ENTER YOUR PAYPAL ACCOUNT'S EMAIL ADDRESS 
    * BELOW AT LINE 46
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


  
class vShop_PaypalBasic {

    /* ************************************************************* */
    /* YOU MUST PROVIDE YOUR PAYPAL ADDRESS/ACCOUNT IN THE NEXT LINE */
    private $_pp_business = 'youremail@yourdomain.com';
    
    /* ************************************************************* */
    /* DO NOT EDIT ANYTHING BELOW HERE UNLESS YOU ARE A DEVELOPER    */
    /* ************************************************************* */

    private $_pp_url = 'https://www.paypal.com/cgi-bin/webscr';
    /* if you have a developer account, you may use the sandbox server to test */
//    private $_pp_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    private $_pp_return_url;
    public $_orderID;

    function __construct($orderID){
      $this->_orderID = $orderID;
      $this->_pp_return_url = PHPWS_Core::getHomeHttp() . 'index.php?module=vshop&uop=complete_order&order_id=' . $this->_orderID;
    }


    public function form ($order) {
        $total = number_format($order->order_array['total_grand'], 2, '.', '');

        $form = new PHPWS_Form('vshop_payment');
        $form->addSubmit(dgettext('vshop', 'Continue Checkout at Paypal'));


        $form->addHidden('cmd', '_cart');
        $form->addHidden('upload', '1');
        $form->addHidden('business', $this->_pp_business);
        $form->addHidden('item_name_1', dgettext('vshop', 'Aggregated items'));
        $form->addHidden('amount_1', $total);
        $form->addHidden('currency_code', PHPWS_Settings::get('vshop', 'currency'));
//        $form->addHidden('notify_url', $this->_pp_notify_url);
        $form->addHidden('return', $this->_pp_return_url);

        $form->addHidden('address_1', $order->address_1);
        $form->addHidden('address_2', $order->address_2);
        $form->addHidden('city', $order->city);
        $form->addHidden('country', $order->country);
        $form->addHidden('state', $order->state);
        $form->addHidden('zip', $order->postal_code);
        $form->addHidden('first_name', $order->first_name);
        $form->addHidden('last_name', $order->last_name);
        
        $form->noAuthKey();
        $form->setAction($this->_pp_url);


        $tpl = $form->getTemplate();
        $tpl['MESSAGE'] = dgettext('vshop', 'Pressing Continue will re-direct you to Paypal\'s secure servers to complete your payment and checkout. You will be able to pay via Credit Card or with your Paypal account once there. When payment is complete, you will be returned here. It is important to let Paypal return you here to complete your order.');
        $tpl['LOGO'] = '<a href="#" onclick="javascript:window.open(\'https://www.paypal.com/us/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside\',\'olcwhatispaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350\'); return false;"><img  src="https://www.paypal.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif" border="0" alt="Solution Graphics"></a>';
        
        return $tpl;
    
    }


    public function complete ($order) {
        $total = number_format($order->order_array['total_grand'], 2, '.', '');
        $errors = null;
        
        if ($_REQUEST['amt'] !== $total) {
            $errors[] = dgettext('vshop', 'Oops');
            $errors[] = dgettext('vshop', 'There was a mismatch between the Paypal ammount and the order total.');
            $errors[] = dgettext('vshop', 'We will have to verify things before we can ship the order.');
        }
        
        if ($errors) {
            return $errors;
        } else {
            return true;
        }

    }


    public function successMessage () {
        $message = null;
        $message[] = dgettext('vshop', 'Your order and payment has been completed successfully.');
        $message[] = dgettext('vshop', 'We will contact you when your order has been processed and shipped.');
        return implode('<br />', $message);
    }


}

?>