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
 * Base class with functionality shared by Property and roommate
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
\PHPWS_Core::requireConfig('properties', 'defines.php');

abstract class Room_Base {

    public $id;
    public $active = true;
    public $airconditioning = 0;

    /**
     * Near appalcart
     * @var boolean
     */
    public $appalcart = 0;
    public $clubhouse = 0;
    public $cut_off_date = 0;

    /**
     * distance from campus figured in increments with lower bound
     * determining amount
     * Example : 5 shown at 5-10 miles
     * @var unknown_type
     */
    public $campus_distance = 5;

    /**
     * @var string
     */
    public $contract_length = C_YEARLY;

    /**
     * Free form text area
     * @var string
     */
    public $description;
    public $dishwasher = false;

    public $internet_type;

    /**
     * hookup, washer/dryer included, central on-site facility
     * @var integer
     */
    public $laundry_type = LAUNDRY_NONE;

    /**
     * Cost per month
     * @var integer
     */
    public $monthly_rent;

    /**
     * @var int
     */
    public $move_in_date;
    public $name;
    // zero means no pets allowed
    public $pets_allowed = 0;

    /**
     * @var boolean 0/1
     */
    public $sublease = 0;
    public $timeout = 0;

    /**
     * on site dumpsters, pickup, neither
     * @var integer
     */
    public $trash_type;
    public $tv_type;
    public $workout_room = 0;
    private $errors;

    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    public function getCampusDistance()
    {
        switch ($this->campus_distance) {
            case 0:
                return 'Less than 5 miles';

            case 5:
                return '5 to 10 miles';

            case 10:
                return '10 to 25 miles';

            case 25:
                return '25 miles or more';
        }
    }

    public function setAppalcart($appalcart)
    {
        $this->appalcart = (bool) $appalcart;
    }

    public function setCampusDistance($distance)
    {
        $this->campus_distance = (int) $distance;
    }

    public function getContractLength()
    {
        switch ($this->contract_length) {
            case C_MONTHLY:
                return 'One month';
            case C_FIVE_MONTH:
                return 'Five months';
            case C_SIX_MONTH:
                return 'Six months';
            case C_TEN_MONTH:
                return 'Ten months';
            case C_YEARLY:
                return 'Twelve months';
            case C_SUMMER:
                return 'Summer session';
            case C_SEMESTER:
                return 'One semester';
            case C_TWO_SEMESTER:
                return 'Two semesters';
        }
    }

    public function setSublease($sublease)
    {
        $this->sublease = strip_tags($sublease);
    }

    public function setClubhouse($clubhouse)
    {
        $this->clubhouse = (bool) $clubhouse;
    }

    public function setAirconditioning($air)
    {
        $this->air = (bool) $air;
    }

    public function setContractLength($length)
    {
        $this->contract_length = (int) $length;
    }

    public function setContractType($type)
    {
        $this->contract_type = $type;
    }

    public function setDescription($desc)
    {
        $this->description = \PHPWS_Text::parseInput($desc);
    }

    public function getDescription()
    {
        return nl2br(\PHPWS_Text::parseOutput($this->description));
    }

    public function getInternetType()
    {
        switch ($this->internet_type) {
            case NET_DIALUP:
                return 'Dial-up';
            case NET_DSL:
                return 'DSL';
            case NET_WIRELESS:
                return 'Wireless';
            case NET_SATELLITE:
                return 'Satellite';
            case NET_CABLE:
                return 'Cable';
            case NET_BOTH:
                return 'DSL/Cable';
        }
    }

    public function getLaundryType()
    {
        switch ($this->laundry_type) {
            case LAUNDRY_NONE:
                return 'No option';
            case LAUNDRY_IN_UNIT:
                return 'Washer/Dryer in unit';
            case LAUNDRY_ON_PREMISES:
                return 'Laundry room in building';
            case LAUNDRY_HOOKUP:
                return 'Washer/Dryer hookup in room';
        }
    }

    public function setDishwasher($dishwasher)
    {
        $this->dishwasher = $dishwasher ? 1 : 0;
    }

    public function setInternetType($type)
    {
        $this->internet_type = (int) $type;
    }

    public function setLaundryType($type)
    {
        $type = intval($type);
        if (!in_array($type, array(LAUNDRY_NONE, LAUNDRY_IN_UNIT, LAUNDRY_ON_PREMISES, LAUNDRY_HOOKUP))) {
            throw new \Exception('Laundry type unknown');
        }
        $this->laundry_type = & $type;
    }

    public function getMonthlyRent()
    {
        return $this->dollarize($this->monthly_rent);
    }

    public function setMonthlyRent($rent)
    {
        $rent = $this->undollarize($rent);
        if (empty($rent)) {
            throw new \Exception('Monthly rent may not be zero');
        }
        $this->monthly_rent = $rent;
    }

    public function setMoveInDate($date)
    {
        $this->move_in_date = strtotime($date);
    }

    public function setName($name)
    {
        $name = preg_replace('/[^\w\s\-\.:]/', '', strip_tags($name));
        if (empty($name)) {
            throw new \Exception('Name may not be empty');
        }
        $this->name = $name;
    }

    public function getMoveInDate($format = '%m/%d/%Y')
    {
        return strftime($format, $this->move_in_date);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTimeout()
    {
        $time_left = $this->timeout - time();
        $purge_days = floor($time_left / 86400);
        $purge_hours = floor(($time_left % 86400) / 3600);
        $purge = null;
        if ($purge_days > 0) {
            $purge .= "{$purge_days}d";
        }

        if ($purge_hours > 0) {
            $purge .= " {$purge_hours}h";
        }

        if (!empty($purge)) {
            $purge = "$purge left";
        } else {
            $purge = 'Purged soon!';
        }

        return $purge;
    }

    public function getTvType()
    {
        switch ($this->tv_type) {
            case TV_NONE:
                return 'Antenna only';

            case TV_CABLE:
                return 'Cable';

            case TV_SATELLITE:
                return 'Satellite';
        }
    }

    public function setPetsAllowed($pets_allowed)
    {
        $this->pets_allowed = (bool) $pets_allowed;
    }

    public function setTvType($type)
    {
        $this->tv_type = (int) $type;
    }

    public function setTrashType($type)
    {
        $this->trash_type = (int) $type;
    }

    public function setWorkoutRoom($room)
    {
        $this->workout_room = $room ? 1 : 0;
    }

    /**
     * Prepares amount for storage in database as an integer
     * @param mixed $amt
     * @return integer
     */
    public function undollarize($amt)
    {
        if (!is_numeric($amt)) {
            return 0;
        }
        return intval($amt * 100);
    }

    /**
     * Formats an integer for display. Does not add dollar sign.
     * @param integer $amt
     * @return string
     */
    public function dollarize($amt)
    {
        $dollar = ($amt / 100);
        $cents = $amt % 100;
        if ($cents < 10) {
            $cents = "0" . $cents;
        }
        return "$dollar.$cents";
    }

    public function getForm($name = null)
    {

        javascript('datepicker');
        \Layout::addStyle('properties', 'forms.css');

        $form = new \PHPWS_Form($name);
        $form->addHidden('module', 'properties');

        $form->addCheck('appalcart', 1);
        $form->setLabel('appalcart', 'On Appalcart route');
        $form->setMatch('appalcart', $this->appalcart);

        $form->addSelect('campus_distance', array(0 => '0 to 5', 5 => '5 to 10', 10 => '10 to 25', 25 => 'More than 25'));
        $form->setLabel('campus_distance', 'Miles from campus');
        $form->setMatch('campus_distance', $this->campus_distance);

        $form->addCheck('clubhouse', 1);
        $form->setLabel('clubhouse', 'Clubhouse');
        $form->setMatch('clubhouse', $this->clubhouse);

        \PHPWS_Core::initModClass('properties', 'User.php');
        $contracts = User::getContracts();
        $form->addSelect('contract_length', $contracts);
        $form->setLabel('contract_length', 'Contract length');
        $form->setMatch('contract_length', $this->contract_length);

        $form->addTextarea('description', $this->description);
        $form->setLabel('description', 'Other property information');

        $form->addCheck('airconditioning', 1);
        $form->setLabel('airconditioning', 'Air Conditioning');
        $form->setMatch('airconditioning', $this->airconditioning);

        $form->addCheck('dishwasher', 1);
        $form->setLabel('dishwasher', 'Dishwasher');
        $form->setMatch('dishwasher', $this->dishwasher);

        $itypes[NET_DIALUP] = 'Dial Up';
        $itypes[NET_CABLE] = 'Cable';
        $itypes[NET_DSL] = 'DSL';
        $itypes[NET_WIRELESS] = 'Wireless';
        $itypes[NET_SATELLITE] = 'Satellite';
        $itypes[NET_FIBER] = 'Fiber';
        $itypes[NET_BOTH] = 'DSL/Cable';

        $form->addSelect('internet_type', $itypes);
        $form->setLabel('internet_type', 'Internet');
        $form->setMatch('internet_type', $this->internet_type);

        $form->addSelect('laundry_type', array(LAUNDRY_NONE => 'No laundry',
            LAUNDRY_ON_PREMISES => 'Laundry room on premises',
            LAUNDRY_HOOKUP => 'Washer/Dryer hook ups in unit',
            LAUNDRY_IN_UNIT => 'Washer/Dryer in unit'));
        $form->setLabel('laundry_type', 'Laundry');
        $form->setMatch('laundry_type', $this->laundry_type);

        $form->addText('monthly_rent', $this->getMonthlyRent());
        $form->setLabel('monthly_rent', 'Monthly rent');
        $form->setSize('monthly_rent', 8, 8);
        $form->setRequired('monthly_rent');

        $form->addText('move_in_date', $this->getMoveInDate());
        $form->setLabel('move_in_date', 'Move-in date');
        $form->setExtra('move_in_date', 'class="datepicker"');

        $form->addText('name', $this->name);
        $form->setLabel('name', 'Name of property');
        $form->setRequired('name');
        $form->setSize('name', 50);

        $form->addCheck('pets_allowed', 1);
        $form->setLabel('pets_allowed', 'Pets allowed');
        $form->setMatch('pets_allowed', $this->pets_allowed);

        $form->addCheck('sublease', 1);
        $form->setMatch('sublease', $this->sublease);
        $form->setLabel('sublease', 'Sublease');

        $toptions[TV_NONE] = 'Antenna';
        $toptions[TV_CABLE] = 'Cable';
        $toptions[TV_SATELLITE] = 'Satellite';
        $toptions[TV_FIBER] = 'Fiber';

        $form->addSelect('tv_type', $toptions);
        $form->setLabel('tv_type', 'Television');
        $form->setMatch('tv_type', $this->tv_type);

        $form->addSelect('trash_type', array(TRASH_ON_YOUR_OWN => 'Trash and recycling receptacles not provided',
            TRASH_ON_PREMISES_NO_RECYCLE => 'Trash receptacles on site.  Recycling not provided',
            TRASH_ON_PREMISES_WITH_RECYCLE => 'Trash and recycling receptacles provided on site',
            TRASH_PICKUP => 'Curbside pickup for trash and recycling'));
        $form->setMatch('trash_type', $this->trash_type);
        $form->setLabel('trash_type', 'Trash removal');

        $form->addCheck('workout_room', 1);
        $form->setMatch('workout_room', $this->workout_room);
        $form->setLabel('workout_room', 'Workout room');

        return $form;
    }


    public function getBaseTpl()
    {
        $tpl['DESCRIPTION'] = $this->getDescription();
        $tpl['MOVE_IN_DATE'] = $this->getMoveInDate();
        $tpl['CAMPUS_DISTANCE'] = $this->getCampusDistance();
        $tpl['MONTHLY_RENT'] = $this->getMonthlyRent();
        $tpl['CONTRACT_LENGTH'] = $this->getContractLength();
        $tpl['SUBLEASE'] = $this->sublease ? '<span class="sublease">sublease</span>' : null;
        $tpl['APPALCART'] = $this->appalcart ? 'Yes' : 'No';
        $tpl['CLUBHOUSE'] = $this->clubhouse ? 'Yes' : 'No';
        $tpl['DISHWASHER'] = $this->dishwasher ? 'Yes' : 'No';
        $tpl['AIRCONDITIONING'] = $this->airconditioning ? 'Yes' : 'No';
        $tpl['INTERNET'] = $this->getInternetType();
        $tpl['LAUNDRY'] = $this->getLaundryType();
        $tpl['TV_TYPE'] = $this->getTvType();
        $tpl['NAME'] = $this->name;

        return $tpl;
    }

}

?>