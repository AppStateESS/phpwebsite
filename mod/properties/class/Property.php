<?php

namespace Properties;

/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
\PHPWS_Core::initModClass('properties', 'Room_Base.php');
\PHPWS_Core::initModClass('properties', 'Photo.php');
\PHPWS_Core::initModClass('properties', 'User.php');

class Property extends Room_Base {

    public $address;
    public $admin_fee_amt;
    public $admin_fee_refund = false;

    /**
     * Indicates if property is approved for display
     * @var unknown_type
     */
    public $approved = 1;

    /**
     * @var float
     */
    public $bathroom_no = 1;

    /**
     * @var float
     */
    public $bedroom_no = 1;
    public $clean_fee_amt;
    public $clean_fee_refund = false;

    /**
     * Id of contact
     * @var integer
     */
    public $contact_id;

    /**
     * Rent by unit (1) or by resident (2)
     * This setting is currently hard coded and not used
     * @var unknown_type
     */
    public $contract_type = 1;
    public $furnished = 0;
    public $heat_type = null;
    public $lease_type = JOINT_LEASE;

    /**
     *
     * @var string
     */
    public $other_fees;

    /**
     * Zero if parking is not charged, amount if otherwise.
     * @var integer
     */
    public $parking_fee;
    public $parking_per_unit;
    public $pet_deposit;
    public $pet_dep_refund = 1;
    public $pet_fee = 0;
    public $pet_type;

    /**
     * Amount of security deposit
     * @var integer
     */
    public $security_amt;
    public $security_refund = 0;
    public $student_type = UNDERGRAD;
    public $util_water;
    public $util_trash;
    public $util_power;
    public $util_fuel;
    public $util_cable;
    public $util_internet;
    public $util_phone;
    public $utilities_inc = 0;

    /**
     * This used to be a count, now a boolean 0,1
     * @var integer
     */
    public $window_number = 1;
    public $company_name;
    public $thumbnail;
    public $efficiency = 0;

    public function __construct($id = null)
    {
        if (!$id) {
            $this->move_in_date = time();
            return;
        }
        $db = new \PHPWS_DB('properties');
        $db->addWhere('id', (int) $id);
        $db->loadObject($this);
    }

    public function form($contact_id = null)
    {
        $form = parent::getForm('property');

        if ($contact_id) {
            $form->addHidden('contact_id', $contact_id);
            $form->addHidden('cop', 'save_property');
            $form->addHidden('k', $_SESSION['Contact_User']->getKey());
        } else {
            $contacts = User::getContacts();
            if (empty($contacts)) {
                return 'Please <a href="index.php?module=properties&aop=edit_contact">create a new contact</a>.';
            }
            $form->addSelect('contact_id', $contacts);
            $form->setMatch('contact_id', $this->contact_id);
            $form->setLabel('contact_id', 'Property contact');
            $form->addHidden('aop', 'save_property');
        }

        if ($this->id) {
            $form->addHidden('pid', $this->id);
            $form->addSubmit('Update property');
        } else {
            $form->addSubmit('Add property');
        }

        $form->addText('address', $this->address);
        $form->setLabel('address', 'Address');
        $form->setSize('address', 50);
        $form->setRequired('address');

        $form->addText('admin_fee_amt', $this->getAdminFeeAmt());
        $form->setLabel('admin_fee_amt', 'Administrative fee');
        $form->setSize('admin_fee_amt', 8, 8);

        $form->addCheck('admin_fee_refund', 1);
        $form->setLabel('admin_fee_refund', 'Fee refunded');
        $form->setMatch('admin_fee_refund', $this->admin_fee_refund);

        $bath['1'] = '1';
        $bath['1.5'] = '1 1/2';
        $bath['2'] = '2';
        $bath['2.5'] = '2 1/2';
        $bath['3'] = '3';
        $bath['3.5'] = '3 1/2';
        $bath['4'] = '4';
        $bath['4.5'] = '4 1/2';
        $bath['5'] = '5';
        $bath['5.5'] = '5 1/2';
        $form->addSelect('bathroom_no', $bath);
        $form->setMatch('bathroom_no', $this->bathroom_no);
        $form->setLabel('bathroom_no', 'Bathrooms');

        $count = range(0, 8);
        unset($count[0]);

        $form->addSelect('bedroom_no', $count);
        $form->setMatch('bedroom_no', $this->bedroom_no);
        $form->setLabel('bedroom_no', 'Bedrooms');

        $form->addText('clean_fee_amt', $this->getCleanFeeAmt());
        $form->setLabel('clean_fee_amt', 'Clean fee amount');
        $form->setSize('clean_fee_amt', 8, 8);

        $form->addCheck('clean_fee_refund', 1);
        $form->setLabel('clean_fee_refund', 'Fee refunded');
        $form->setMatch('clean_fee_refund', $this->clean_fee_refund);

        $form->addCheck('efficiency', 1);
        $form->setLabel('efficiency', 'Efficiency');
        $form->setMatch('efficiency', $this->efficiency);

        $form->addCheck('furnished', 1);
        $form->setLabel('furnished', 'Furnished');
        $form->setMatch('furnished', $this->furnished);

        $form->addRadioAssoc('lease_type',
                array(JOINT_LEASE => 'Per unit', INDIVIDUAL_LEASE => 'Per person'));
        $form->setMatch('lease_type', $this->lease_type);

        $form->addTextarea('other_fees', $this->other_fees);
        $form->setLabel('other_fees', 'Other fees');

        $form->addText('parking_fee', $this->getParkingFee());
        $form->setLabel('parking_fee', 'Parking fee');
        $form->setSize('parking_fee', 8, 8);

        $parking = range(0, 10);
        unset($parking[0]);
        $parking[NO_LIMIT_PARKING] = 'No limit';

        $form->addSelect('parking_per_unit', $parking);
        $form->setLabel('parking_per_unit', 'Parking spaces available per unit');
        $form->setMatch('parking_per_unit', $this->parking_per_unit);

        // Pet deposits are always refundable
        /*
          $form->addCheck('pet_dep_refund', 1);
          $form->setLabel('pet_dep_refund', 'Fee refunded');
          $form->setMatch('pet_dep_refund', $this->pet_dep_refund);
         */

        $form->addText('pet_deposit', $this->getPetDeposit());
        $form->setLabel('pet_deposit', 'Pet deposit');
        $form->setSize('pet_deposit', 8, 8);

        $form->addText('pet_fee', $this->getPetFee());
        $form->setLabel('pet_fee', 'Pet fee');
        $form->setSize('pet_fee', 8, 8);

        $form->addTextArea('pet_type', $this->pet_type);
        $form->setRows('pet_type', '5');
        $form->setCols('pet_type', 20);
        $form->setLabel('pet_type', 'Allowed pet types');

        $form->addText('security_amt', $this->getSecurityAmt());
        $form->setLabel('security_amt', 'Security deposit amount');
        $form->setSize('security_amt', 8, 8);

        $form->addCheck('security_refund', 1);
        $form->setMatch('security_refund', $this->security_refund);
        $form->setLabel('security_refund', 'Fee refunded');

        $form->addRadioAssoc('student_type',
                array(NO_STUDENT_PREFERENCE => 'No preference',
            UNDERGRAD => 'Undergraduate', GRAD_STUDENT => 'Graduate'));
        $form->setMatch('student_type', $this->student_type);

        $form->addCheck('utilities_inc', 1);
        $form->setMatch('utilities_inc', $this->utilities_inc);
        $form->setLabel('utilities_inc', 'Utilities included in rent');

        $form->addText('util_cable', $this->getUtilCable());
        $form->setLabel('util_cable', 'Cable');
        $form->setSize('util_cable', 8, 8);

        $form->addText('util_fuel', $this->getUtilFuel());
        $form->setLabel('util_fuel', 'Fuel');
        $form->setSize('util_fuel', 8, 8);

        $form->addText('util_internet', $this->getUtilInternet());
        $form->setLabel('util_internet', 'Internet');
        $form->setSize('util_internet', 8, 8);

        $form->addText('util_phone', $this->getUtilPhone());
        $form->setLabel('util_phone', 'Phone');
        $form->setSize('util_phone', 8, 8);

        $form->addText('util_power', $this->getUtilPower());
        $form->setLabel('util_power', 'Power');
        $form->setSize('util_power', 8, 8);

        $form->addText('util_trash', $this->getUtilTrash());
        $form->setLabel('util_trash', 'Trash');
        $form->setSize('util_trash', 8, 8);

        $form->addText('util_water', $this->getUtilWater());
        $form->setLabel('util_water', 'Water');
        $form->setSize('util_water', 8, 8);

        $hl[HT_HVAC] = 'HVAC (heat pump)';
        $hl[HT_OIL] = 'Oil';
        $hl[HT_PROPANE] = 'Propane';
        $hl[HT_ELEC_BASE] = 'Electric baseboard';
        $hl[HT_KEROSENE] = 'Kerosene';
        $hl[HT_WOODSTOVE] = 'Woodstove/Fireplace';
        $hl[HT_GAS] = 'Natural gas';
        $form->addCheck('heat_type', array_keys($hl));
        $form->setLabel('heat_type', $hl);
        $form->setMatch('heat_type', $this->heat_type);

        // necessary since there used to be a count instead of a bool
        $window_number = (int) (bool) $this->window_number;
        $form->addCheck('window_number', 1);
        $form->setMatch('window_number', $window_number);
        $form->setLabel('window_number', 'Windows in unit');

        $form->setLabel('sublease', 'Subleasing permitted');

        $tpl = $form->getTemplate();
        if (!empty($this->errors)) {
            foreach ($this->errors as $key => $message) {
                $new_key = strtoupper($key) . '_ERROR';
                $tpl[$new_key] = $message;
            }
        }
        return \PHPWS_Template::process($tpl, 'properties', 'edit_property.tpl');
    }

    public function post()
    {
        $vars = array_keys(get_object_vars($this));

        foreach ($_POST as $key => $value) {
            if (in_array($key, $vars)) {
                $func = 'set' . str_replace(' ', '',
                                ucwords(str_replace('_', ' ', $key)));
                try {
                    $this->$func($value);
                } catch (\Exception $e) {
                    $this->errors[$key] = $e->getMessage();
                }
            }
        }
// checkboxes
        if (!isset($_POST['heat_type'])) {
            $this->heat_type = null;
        }
        $this->efficiency = isset($_POST['efficiency']);
        $this->sublease = isset($_POST['sublease']);
        $this->furnished = isset($_POST['furnished']);
        $this->utilities_inc = isset($_POST['utilities_inc']);
        $this->purchased = isset($_POST['purchased']);
        $this->dishwasher = isset($_POST['dishwasher']);
        $this->airconditioning = isset($_POST['airconditioning']);
        $this->appalcart = isset($_POST['appalcart']);
        $this->clubhouse = isset($_POST['clubhouse']);
        $this->window_number = isset($_POST['window_number']);
        $this->workout_room = isset($_POST['workout_room']);
        $this->pets_allowed = isset($_POST['pets_allowed']);
        // No longer counted
        //$this->pet_dep_refund = isset($_POST['pet_dep_refund']);
        $this->security_refund = isset($_POST['security_refund']);
        $this->admin_fee_refund = isset($_POST['admin_fee_refund']);
        $this->clean_fee_refund = isset($_POST['clean_fee_refund']);
        return !isset($this->errors);
    }

    public function getAddress()
    {
        return nl2br($this->address);
    }

    public function setAddress($address)
    {
        if (empty($address)) {
            throw new \Exception('Property address may not be empty');
        }
        $this->address = strip_tags($address);
    }

    public function getAdminFeeAmt()
    {
        return $this->dollarize($this->admin_fee_amt);
    }

    public function getBathroomNo()
    {
        return str_replace('.5', ' 1/2', (string) $this->bathroom_no);
    }

    public function setAdminFeeAmt($amount)
    {
        $this->admin_fee_amt = $this->undollarize($amount);
    }

    public function setAdminFeeRefund($refund)
    {
        $this->admin_fee_refund = (bool) $refund;
    }

    public function setBathroomNo($bathrooms)
    {
        if (empty($bathrooms)) {
            throw new \Exception('Property must contain at least one bathroom');
        }
        $this->bathroom_no = (float) $bathrooms;
    }

    public function setBedroomNo($bedrooms)
    {
        if (empty($bedrooms)) {
            throw new \Exception('Property must contain at least one bedroom');
        }
        $this->bedroom_no = (int) $bedrooms;
        if ($this->efficiency && $this->bedroom_no > 1) {
            $this->bedroom_no = 1;
            throw new \Exception('Efficiency property. Forced to one bedroom.');
        }
    }

    public function getCleanFeeAmt()
    {
        return $this->dollarize($this->clean_fee_amt);
    }

    public function getPetFee()
    {
        return $this->dollarize($this->pet_fee);
    }

    public function setCleanFeeAmt($amount)
    {
        $this->clean_fee_amt = $this->undollarize($amount);
    }

    public function setPetFee($fee)
    {
        $this->pet_fee = $this->undollarize($fee);
    }

    public function setCleanFeeRefund($refund)
    {
        $this->clean_fee_refund = (bool) $refund;
    }

    public function setContactId($id)
    {
        if (empty($id)) {
            throw new \Exception('Property must be linked to a contact');
        }
        $this->contact_id = $id;
    }

    public function setEfficiency($eff)
    {
        $this->efficiency = $eff ? 1 : 0;
    }

    public function setFurnished($furnished)
    {
        $this->furnished = $furnished ? 1 : 0;
    }

    public function setHeatType($heat)
    {
        if (empty($heat)) {
            $this->heat_type = null;
        } elseif (is_string($heat)) {
            $this->heat_type = explode(',', $heat);
        } elseif (is_array($heat)) {
            $this->heat_type = $heat;
        }
    }

    public function setLeaseType($lease)
    {
        $this->lease_type = (int) $lease;
    }

    /**
     * See defines
     * @param integer $length
     */
    public static function googleMapUrl($url)
    {
        $url = preg_replace('/[\W]/', '+', $url);
        return preg_replace('/\+{2,}/', '+', $url);
    }

    public function delete()
    {
        if ($this->id) {
            $db = new \PHPWS_DB('properties');
            $db->addWhere('id', $this->id);
            if (\PEAR::isError($db->delete())) {
                throw new \Exception('Error occurred during deletion.');
            }
        }
    }

    public function getFurnished()
    {
        return $this->furnished ? 'Yes' : 'No';
    }

    public function getHeatType()
    {
        $hl = null;
        if (empty($this->heat_type)) {
            return null;
        }
        foreach ($this->heat_type as $ht) {
            switch ($ht) {
                case HT_HVAC:
                    $hl[HT_HVAC] = 'HVAC (heat pump)';
                    break;

                case HT_OIL:
                    $hl[HT_OIL] = 'Oil';
                    break;

                case HT_PROPANE:
                    $hl[HT_PROPANE] = 'Propane';
                    break;

                case HT_ELEC_BASE:
                    $hl[HT_ELEC_BASE] = 'Electric baseboard';
                    break;

                case HT_KEROSENE:
                    $hl[HT_KEROSENE] = 'Kerosene';
                    break;

                case HT_WOODSTOVE:
                    $hl[HT_WOODSTOVE] = 'Woodstove/Fireplace';
            }
        }
        return $hl;
    }

    public function getLeaseType()
    {
        switch ($this->lease_type) {
            case JOINT_LEASE:
                return 'per unit';

            case INDIVIDUAL_LEASE:
                return 'per person';
        }
    }

    public function getOtherFees()
    {
        return nl2br($this->other_fees);
    }

    public function setOtherFees($fees)
    {
        $this->other_fees = strip_tags($fees);
    }

    public function getParkingFee()
    {
        return $this->dollarize($this->parking_fee);
    }

    public function getParkingPerUnit()
    {
        if ($this->parking_per_unit == NO_LIMIT_PARKING) {
            return 'No limit';
        } elseif ($this->parking_per_unit == 1) {
            return 'one space';
        } else {
            return $this->parking_per_unit . ' spaces';
        }
    }

    public function setParkingFee($fee)
    {
        $this->parking_fee = $this->undollarize($fee);
    }

    public function setParkingPerUnit($parking)
    {
        $parking = intval($parking);
        if (!$parking || $parking > NO_LIMIT_PARKING) {
            throw new \Exception('Parking per unit number is incorrect');
        }
        $this->parking_per_unit = & $parking;
    }

    public function setPetAllowed($pet)
    {
        $this->pet_allowed = $pet ? 1 : 0;
    }

    /*
      public function setPetDepRefund($refund)
      {
      $this->pet_dep_refund = $refund ? 1 : 0;
      }
     */

    public function getPetDeposit()
    {
        return $this->dollarize($this->pet_deposit);
    }

    public function getPetType()
    {
        return nl2br($this->pet_type);
    }

    public function setPetDeposit($deposit)
    {
        $this->pet_deposit = $this->undollarize($deposit);
    }

    public function setPetType($pet_type)
    {
        $this->pet_type = strip_tags(trim($pet_type));
    }

    public function getSecurityAmt()
    {
        return $this->dollarize($this->security_amt);
    }

    public function getStudentType()
    {
        switch ($this->student_type) {
            case UNDERGRAD:
                return 'Undergraduate';

            case GRAD_STUDENT:
                return 'Graduate student';

            default:
                return 'No preference';
        }
    }

    public function setSecurityAmt($amt)
    {
        $this->security_amt = $this->undollarize($amt);
    }

    public function setSecurityRefund($refund)
    {
        $this->security_refund = $refund ? 1 : 0;
    }

    public function setStudentType($type)
    {
        $this->student_type = (int) $type;
    }

    public function setUtilitiesInc($inc)
    {
        $this->utilities_inc = $inc ? 1 : 0;
    }

    public function setUtilCable($util)
    {
        $this->util_cable = $this->undollarize($util);
    }

    public function setUtilFuel($util)
    {
        $this->util_fuel = $this->undollarize($util);
    }

    public function setUtilInternet($util)
    {
        $this->util_internet = $this->undollarize($util);
    }

    public function setUtilPhone($util)
    {
        $this->util_phone = $this->undollarize($util);
    }

    public function setUtilPower($util)
    {
        $this->util_power = $this->undollarize($util);
    }

    public function setUtilTrash($util)
    {
        $this->util_trash = $this->undollarize($util);
    }

    public function setUtilWater($util)
    {
        $this->util_water = $this->undollarize($util);
    }

    public function getUtilCable()
    {
        return $this->dollarize($this->util_cable);
    }

    public function getUtilFuel()
    {
        return $this->dollarize($this->util_fuel);
    }

    public function getUtilInternet()
    {
        return $this->dollarize($this->util_internet);
    }

    public function getUtilPhone()
    {
        return $this->dollarize($this->util_phone);
    }

    public function getUtilPower()
    {
        return $this->dollarize($this->util_power);
    }

    public function getUtilTrash()
    {
        return $this->dollarize($this->util_trash);
    }

    public function getUtilWater()
    {
        return $this->dollarize($this->util_water);
    }

    public function setWindowNumber($number)
    {
        $this->window_number = (int) (bool) $number;
    }

    public function save()
    {
        $this->updated = time();
        if (!$this->id) {
            $this->created = time();
        }
        $this->timeout = $this->updated + (86400 * 30);
        $db = new \PHPWS_DB('properties');
        $result = $db->saveObject($this);
        if (\PEAR::isError($result)) {
            \PHPWS_Error::log($result);
            throw new \Exception('Could not save property to the database.');
        }
        return true;
    }

    public function update()
    {
        $this->updated = time();
        $this->timeout = $this->updated + (86400 * 30);
        $db = new \PHPWS_DB('properties');
        $db->addValue('updated', $this->updated);
        $db->addValue('timeout', $this->timeout);
        $db->addWhere('id', $this->id);
        $db->update();
    }

    public function row_tags($contact_command = false)
    {
        $tpl['NAME'] = $this->viewLink($this->name);
        if ($contact_command) {
            $cmd = 'cop';
            $cmd_array['k'] = $_SESSION['Contact_User']->getKey();
        } else {
            $cmd = 'aop';
            $cmd_array['authkey'] = \Current_User::getAuthKey();
        }

        $cmd_array['pid'] = $this->id;

        if ($this->active) {
            $cmd_array[$cmd] = 'deactivate_property';
            $admin[] = \PHPWS_Text::moduleLink(\Icon::show('active',
                                    'Click to deactivate'), 'properties',
                            $cmd_array);
        } else {
            $cmd_array[$cmd] = 'activate_property';
            $admin[] = \PHPWS_Text::moduleLink(\Icon::show('inactive',
                                    'Click to activate'), 'properties',
                            $cmd_array);
        }
        $cmd_array[$cmd] = 'edit_property';
        $admin[] = \PHPWS_Text::secureLink(\Icon::show('edit'), 'properties',
                        $cmd_array);

        $cmd_array[$cmd] = 'update';
        if ($this->active) {
            $tpl['TIMEOUT'] = \PHPWS_Text::moduleLink($this->getTimeout(),
                            'properties', $cmd_array);
        } else {
            $tpl['TIMEOUT'] = 'N/A';
        }

        $photo = new Photo;
        $photo->setPropertyId($this->id);
        $admin[] = $photo->uploadNew();


        $js['LINK'] = \Icon::show('delete');
        $js['QUESTION'] = 'Are you sure you want to delete this property?';
        if ($contact_command) {
            $js['ADDRESS'] = 'index.php?module=properties&cop=delete_property&pid=' . $this->id . '&k=' . $_SESSION['Contact_User']->getKey();
        } else {
            $js['ADDRESS'] = 'index.php?module=properties&aop=delete_property&pid=' . $this->id . '&authkey=' . \Current_User::getAuthKey();
        }

        $admin[] = javascript('confirm', $js);
        $tpl['ACTION'] = implode('', $admin);
        return $tpl;
    }

    public function viewLink($label = null)
    {
        $url = sprintf('./properties/%s/%s', $this->id, $this->getUrlName());
        if (!empty($label)) {
            return sprintf('<a href="%s" title="%s">%s</a>', $url, $this->name,
                    $label);
        } else {
            return $url;
        }
    }

    public function getUrlName()
    {
        $name = preg_replace('/[\W]/', '-', $this->name);
        return preg_replace('/-{2,}/', '-', $name);
    }

    public function listRows()
    {
        $tpl['NAME'] = $this->viewLink($this->name);
        if ($this->thumbnail) {
            $thumbnail = sprintf('<img class="property-thumb" src="%s" />',
                    Photo::thumbnailPath($this->thumbnail));
        } else {
            $thumbnail = sprintf('<img class="property-thumb" src="%smod/properties/img/no_photo.gif" />',
                    PHPWS_SOURCE_HTTP);
        }
        $tpl['THUMBNAIL'] = $this->viewLink($thumbnail);
        $tpl['MONTHLY_RENT'] = $this->getMonthlyRent();
        $tpl['CAMPUS_DISTANCE'] = $this->getCampusDistance();
        if ($this->move_in_date <= time()) {
            $tpl['MOVE_IN_DATE'] = 'Now!';
        } else {
            $tpl['MOVE_IN_DATE'] = $this->getMoveInDate('%B %e, %Y');
        }
        return $tpl;
    }

    public function view()
    {
        $tpl = $this->getBaseTpl();
        $refund = '<span style="font-size : 90%">(Refundable)</span>';
        \PHPWS_Core::initModClass('properties', 'Contact.php');
        $max_width = PANEL_WIDTH;
        $max_height = PANEL_HEIGHT;

        \Layout::addStyle('properties', 'view.css');
        $tpl['NAME'] = $this->viewLink($this->name);
        $photos = $this->getPhotos();

        if ($photos) {
            javascriptMod('properties', 'galleryview',
                    array('panel_width' => $max_width, 'panel_height' => $max_height));
            foreach ($photos as $p) {
                $dim = getimagesize($p['path']);
                $width = & $dim[0];
                $height = & $dim[1];

                $diff = \PHPWS_File::getDiff($width, $max_width, $height,
                                $max_height);

                $new_width = round($width * $diff);
                $new_height = round($height * $diff);

                if ($new_width > $max_width || $new_height > $max_height) {
                    $diff = \PHPWS_File::getDiff($new_width, $max_width,
                                    $new_height, $max_height);
                    $new_width = round($width * $diff);
                    $new_height = round($height * $diff);
                }


                $all[] = sprintf('<li><img src="%s" title="%s" />
            <div class="panel-content lightbox">
            <a class="lightbox" href="%s"><img src="%s" width="%s" height="%s" /></a>
            </div></li>', Photo::thumbnailPath($p['path']), $p['title'],
                        $p['path'], $p['path'], $new_width, $new_height);
            }
            $tpl['PHOTOS'] = implode("\n", $all);
        } else {
            $tpl['NO_PHOTO'] = '<img src="' . PHPWS_SOURCE_HTTP . 'mod/properties/img/no_photo.gif" alt="No photo" title="No photos available" />';
        }

        $contact = new Contact($this->contact_id);
        if (!empty($contact->company_address)) {
            $tpl['COMPANY_ADDRESS'] = $contact->getCompanyAddress();
            $tpl['GOOGLE_COMPANY'] = sprintf('<a target="_blank" href="http://maps.google.com/maps?q=%s">
        <img class="google-map" src="%smod/properties/img/google-pin-red.gif" title="Google maps" target="_blank" /></a>',
                    Property::googleMapUrl($contact->company_address),
                    PHPWS_SOURCE_HTTP);
        }

        $tpl['COMPANY_NAME'] = $contact->getCompanyUrl();
        $tpl['EMAIL'] = $contact->getEmailAddress(true);
        $tpl['PHONE'] = $contact->getPhone();
        $tpl['TIMES_AVAILABLE'] = $contact->getTimesAvailable();

        $tpl['ADDRESS'] = $this->getAddress();

        $tpl['GOOGLE_MAP'] = sprintf('<a target="_blank" href="http://maps.google.com/maps?q=%s">
        <img src="%smod/properties/img/google-pin-red.gif" title="Google maps" target="_blank" /></a>',
                Property::googleMapUrl($this->address), PHPWS_SOURCE_HTTP);

        $tpl['LEASE_TYPE'] = $this->getLeaseType();
        if ($this->efficiency) {
            $tpl['BEDROOMS'] = 'One room efficiency';
        } else {
            $tpl['BEDROOMS'] = $this->bedroom_no;
        }
        $tpl['BATHROOMS'] = $this->getBathroomNo();

        if ($this->window_number) {
            $tpl['WINDOWS'] = 'Yes';
        } else {
            $tpl['WINDOWS'] = 'No';
        }

        if (!empty($this->admin_fee_amt)) {
            $tpl['ADMIN_FEE'] = '$' . $this->getAdminFeeAmt();
            $tpl['ADMIN_FEE_REFUND'] = $this->admin_fee_refund ? $refund : null;
        }

        if (!empty($this->parking_fee)) {
            $tpl['PARKING_FEE'] = '$' . $this->getParkingFee();
        }

        if (!empty($this->security_amt)) {
            $tpl['SECURITY_AMT'] = '$' . $this->getSecurityAmt();
            $tpl['SECURITY_REFUND'] = $this->security_refund ? $refund : null;
        }

        if (!empty($this->other_fees)) {
            $tpl['OTHER_FEES'] = $this->getOtherFees();
        }

        if (!empty($this->clean_fee_amt)) {
            $tpl['CLEAN_FEE_AMT'] = '$' . $this->getCleanFeeAmt();
            $tpl['CLEAN_FEE_REFUND'] = $this->clean_fee_refund ? $refund : null;
        }

        $tpl['PARKING_PER_UNIT'] = $this->getParkingPerUnit();


        if ($this->pets_allowed) {
            $tpl['PETS_ALLOWED'] = 'Yes';
            $tpl['PET_TYPES'] = $this->getPetType();
            if ($this->pet_deposit) {
                $tpl['PET_DEPOSIT'] = '$' . $this->getPetDeposit() . ' <span style="font-size : 90%">(refundable)</span>';
            } else {
                $tpl['PET_DEPOSIT'] = 'None';
            }
            if ($this->pet_fee) {
                $tpl['PET_FEE'] = '$' . $this->getPetFee() . ' <span style="font-size : 90%">(nonrefundable)</span>';
            } else {
                $tpl['PET_FEE'] = 'None';
            }
        } else {
            $tpl['PETS_ALLOWED'] = 'No';
        }

        $utility_allowance = false;

        $tpl['STUDENT_TYPE'] = $this->getStudentType();

        if ($this->util_water) {
            $utility_allowance = true;
            $tpl['UTIL_WATER'] = $this->getUtilWater();
        }

        if ($this->util_trash) {
            $utility_allowance = true;
            $tpl['UTIL_TRASH'] = $this->getUtilTrash();
        }

        if ($this->util_power) {
            $utility_allowance = true;
            $tpl['UTIL_POWER'] = $this->getUtilPower();
        }

        if ($this->util_fuel) {
            $utility_allowance = true;
            $tpl['UTIL_FUEL'] = $this->getUtilFuel();
        }

        if ($this->util_cable) {
            $utility_allowance = true;
            $tpl['UTIL_CABLE'] = $this->getUtilCable();
        }

        if ($this->util_internet) {
            $utility_allowance = true;
            $tpl['UTIL_INTERNET'] = $this->getUtilInternet();
        }

        if ($this->util_phone) {
            $utility_allowance = true;
            $tpl['UTIL_PHONE'] = $this->getUtilPhone();
        }

        $photo = new Photo;
        $photo->setPropertyId($this->id);

        if (isset($_GET['photo'])) {
            $data['pid'] = $this->id;
        }
        $data['view'] = 1;
        if (\Current_User::allow('properties')) {
            javascriptMod('properties', 'photo_upload', $data);
            $tpl['ADD_PHOTO'] = $photo->uploadNew(false);
            $tpl['EDIT'] = \PHPWS_Text::secureLink('Edit', 'properties',
                            array('aop' => 'edit_property', 'pid' => $this->id));
            if (!$this->active) {
                $tpl['ACTIVE'] = '<div id="not-active">This property is currently NOT ACTIVE</div>';
            }
        } elseif (@$_SESSION['Contact_User']->id == $this->contact_id) {
            if (!$this->active) {
                $tpl['ACTIVE'] = '<div id="not-active">This property is currently NOT ACTIVE</div>';
            }
            $data['is_contact'] = 1;
            javascriptMod('properties', 'photo_upload', $data);
            $tpl['ADD_PHOTO'] = $photo->uploadNew(false);
            $tpl['EDIT'] = \PHPWS_Text::moduleLink('Edit property',
                            'properties',
                            array('cop' => 'edit_property', 'pid' => $this->id, 'k' => $_SESSION['Contact_User']->getKey()));
        } elseif (!$this->active) {
            \Layout::add('This property is currently not available');
            return;
        }
        $heat_type = $this->getHeatType();
        if ($heat_type) {
            $tpl['HEAT_TYPE'] = implode(', ', $this->getHeatType());
        }

        $content = \PHPWS_Template::process($tpl, 'properties', 'view.tpl');
        \Layout::add($content);
    }

    public function getPhotos()
    {
        $db = new \PHPWS_DB('prop_photo');
        $db->addColumn('path');
        $db->addColumn('title');
        $db->addWhere('pid', $this->id);
        $db->addOrder('main_pic desc');
        $photos = $db->select();
        if (empty($photos)) {
            return null;
        }
        return $photos;
    }

}

?>