<?php
/**
 * **************************************************************
 *
 * NOTE: to use this method you must have a valid Skipjack account
 *
 * ENTER THE CARDS YOU ACCEPT IN THE ARRAY AT LINE 45
 *
 * Finally, YOU MUST ENTER YOUR SKIPJACK ACCOUNT'S SERIAL NUMBER
 * BELOW AT LINE 47
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



class vShop_Skipjack {

    /* ************************************************************* */
    /* EDIT YOUR SKIPJACK INFO HERE                                  */
    /* ************************************************************* */
    private $_sj_Cards = array('Visa', 'Master Card', 'American Express');
    /* YOU MUST PROVIDE YOUR SKIPJACK SERIAL NUMBER IN THE NEXT LINE */
    private $_sj_SerialNumber = 'xxxxxxxxxxxx';


    /* ************************************************************* */
    /* DO NOT EDIT ANYTHING BELOW HERE UNLESS YOU ARE A DEVELOPER    */
    /* ************************************************************* */
    private $_sj_host = 'www.skipjackic.com';
    /* if you have a developer account, you may use the sandbox server to test */
//    private $_sj_host = 'developer.skipjackic.com';
    private $_sj_DeveloperSerialNumber = null;
//    private $_sj_DeveloperSerialNumber = 'xxxxxxxxxxxx';

    public $_orderID;

    function __construct($orderID){
        $this->_orderID = $orderID;
    }


    public function form ($order) {

        $form = new PHPWS_Form('vshop_payment');
        $form->addSubmit(dgettext('vshop', 'Click once to complete payment'));

        $form->addHidden('module', 'vshop');
        $form->addHidden('order_id', $this->_orderID);
        $form->addHidden('uop', 'complete_order');

        $form->addTextField('ccnum', '');
        $form->setRequired('ccnum');
        $form->setLabel('ccnum', dgettext('vshop', 'Credit Card Number (no spaces)'));
        $form->setSize('ccnum', 30);

        $form->addTextField('ccem', '');
        $form->setRequired('ccem');
        $form->setLabel('ccem', dgettext('vshop', 'Credit Card Expiry (mm-yy)'));
        $form->setSize('ccem', 2);

        $form->addTextField('ccey', '');
        $form->setRequired('ccey');
        $form->setLabel('ccey', dgettext('vshop', 'Credit Card Expiry (mm-yy)'));
        $form->setSize('ccey', 2);

        $tpl = $form->getTemplate();
        $tpl['CARDS'] = sprintf(dgettext('vshop', 'We accept: %s'), implode(', ', $this->_sj_Cards));
        $tpl['MESSAGE'] = dgettext('vshop', 'Click once and only once to complete your payment. Please be patient as the transaction may take a few secconds.');
//        $tpl['LOGO'] = null;

        return $tpl;

    }


    public function complete ($order) {
        $errors = null;
        $customvars = null;
        $total = number_format($order->order_array['total_grand'], 2, '.', '');
        $orderstring = PHPWS_Settings::get('vshop', 'mod_title') . "~" . dgettext('vshop', 'Aggregated items') . "~" . $total . "~1~N~||";

/*  this isn't really needed but could be used to send custom stuff to skipjack
        $customvars = array(
            "$taxrate Tax=$tax",
            "BeforeTax=$subtotal",
            "Total=$total"
        );
*/

        /* do the transaction */
        $response = $this->SingleCharge($order->first_name.' '.$order->last_name, 
                                        $order->address_1, 
                                        $order->city, 
                                        $order->state, 
                                        $order->postal_code, 
                                        $order->country, 
                                        $order->phone, 
                                        $order->email, 
                                        $this->_orderID . mktime(), 
                                        $_POST['ccnum'], 
                                        $_POST['ccem'], 
                                        $_POST['ccey'], 
                                        $orderstring, 
                                        number_format($order->order_array['total_grand'], 2, '', ''), 
                                        $customvars
                                    );
        if(stristr($response, 'OK - Auth Code')) $approved=true; else $approved=false;

        $transaction = $approved;

        if (!$transaction) {
            $errors[] = dgettext('vshop', 'Oops');
            $errors[] = dgettext('vshop', 'There was a problem with the credit card transaction. The error was: ');
            $errors[] = $response;
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


    function SingleCharge($name,$street,$city,$state,$zip,$country,$phone,$email,$ordernumber,$ccnum,$ccem,$ccey,$orderstring,$amount,$customvars='') {

        $CC_ERRORS = array( -1    =>    'Invalid length (-1)',
                            -35   =>    'Invalid credit card number (-35)',
                            -37   =>    'Failed communication (-37)',
                            -39   =>    'Serial number is too short (-39)',
                            -51   =>    'The zip code is invalid',
                            -52   =>    'The shipto zip code is invalid',
                            -53   =>    'Length of expiration date (-53)',
                            -54   =>    'Length of account number date (-54)',
                            -55   =>    'Length of street address (-55)',
                            -56   =>    'Length of shipto street address (-56)',
                            -57   =>    'Length of transaction amount (-57)',
                            -58   =>    'Length of name (-58)',
                            -59   =>    'Length of location (-59)',
                            -60   =>    'Length of state (-60)',
                            -61   =>    'Length of shipto state (-61)',
                            -62   =>    'Length of order string (-62)',
                            -64   =>    'Invalid phone number (-64)',
                            -65   =>	'Empty name (-65)', 
                            -66   =>    'Empty email (-66)',
                            -67   =>    'Empty street address (-66)',
                            -68   =>    'Empty city (-68)',
                            -69   =>    'Empty state (-69)',
                            -70   =>    'Empty zip code (-70)',
                            -71   =>    'Empty order number (-71)',
                            -72   =>    'Empty account number (-72)',
                            -73   =>    'Empty expiration month (-73)',
                            -74   =>    'Empty expiration year (-74)',
                            -75   =>    'Empty serial number (-75)',
                            -76   =>    'Empty transaction amount (-76)',
                            -79   =>    'Length of customer name (-79)',
                            -80   =>    'Length of shipto customer name (-80)',
                            -81   =>    'Length of customer location (-81)',
                            -82   =>    'Length of customer state (-82)',
                            -83   =>    'Length of shipto phone (-83)',
                            -84   =>    'Pos Error duplicate ordernumber (-84)',
                            -91   =>    'Pos Error CVV2 (-91)',
                            -92   =>    'Pos Error Approval Code (-92)',
                            -93   =>    'Pos Error Blind Credits Not Allowed (-93)',
                            -94   =>    'Pos Error Blind Credits Failed (-94)',
                            -95   =>    'Pos Error Voice Authorizations Not Allowed (-95)'
                        );
        
        $url = 'https://' . $this->_sj_host . '/scripts/evolvcc.dll?Authorize';
        if (!empty($phone)) {
            $phone = $phone;
        } else {
            $phone = '0000000000';
        }

        $postvars = array(  "Sjname=$name",
                            "Streetaddress=$street",
                            "City=$city",
                            "State=$state",
                            "ZipCode=$zip",
                            "Country=$country",
                            "Shiptophone=$phone",
                            "Email=$email",
                            "Ordernumber=$ordernumber",
                            "Accountnumber=$ccnum",
                            "Month=$ccem",
                            "Year=$ccey",
                            "Orderstring=$orderstring",
                            "Transactionamount=$amount",
                            "Serialnumber=$this->_sj_SerialNumber",
                            "DeveloperSerialNumber=$this->_sj_DeveloperSerialNumber"
        );
        $postvars = join('&', $postvars);

        if(is_array($customvars)) {
            $customvars = join('&', $customvars);
            $postvars = "$postvars&$customvars";
        }
//print($postvars); exit;        
        /* initiate the transaction */
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

        $r = curl_exec($ch);
        curl_close($ch);
//print_r($postvars); print_r($r); exit;
        /* parse out the vars from the junky html */
        $ok = 0;
        $lines = split('<', $r);
        foreach($lines as $line) {
            $line = chop($line);
            $line = str_replace('-->', '', $line);
            $vars = split('!--', $line);
            foreach($vars as $v) {
                if(strstr($v, 'AUTHCODE')) {
                    $ok = 1; // start of Vars
                }
                list($name, $value) = split('=', $v);
                if($ok && strlen($name) > 0) {
                    $variables[$name] = $value;
                }
                if(strstr($v, 'szReturnCode')) {
                    $ok = 0; // last of Vars
                }
            }
        }

        // DEBUG OUTPUT VARS
        // foreach($variables as $key => $value)
        // echo "$key => $value<br>";

        if($variables[szIsApproved]==1) {
            return("OK - Auth code $variables[szAuthorizationResponseCode]");
        } else {
            return($CC_ERRORS[(int)$variables[szReturnCode]]." ".$variables[szAuthorizationDeclinedMessage]);
        }
    }


}

?>