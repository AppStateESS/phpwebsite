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
 * Allows the creation, viewing, and searching of roommate requests.
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
\PHPWS_Core::initModClass('properties', 'Room_Base.php');

class Roommate extends Room_Base {

    /**
     * Id for roommate is the user's ID
     *
     * @var unknown_type
     */
    public $id;
    public $gender = 0;
    public $share_bedroom = false;
    public $share_bathroom = false;
    public $smoking = 0;

    public function __construct($id=0)
    {
        $db = new \PHPWS_DB('prop_roommate');
        if ($id) {
            $this->id = (int) $id;
        }
        if (!$this->id) {
            return;
        }
        if (empty($this->move_in_date)) {
            $this->move_in_date = time();
        }
        $db->addWhere('id', (int) $id);
        $db->loadObject($this);
    }

    public function delete()
    {
        $db = new \PHPWS_DB('prop_roommate');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if (\PHPWS_Error::isError($result)) {
            \PHPWS_Error::log($result);
            return false;
        } else {
            return true;
        }
    }

    public function setShareBedroom($share)
    {
        $this->share_bedroom = (int) (bool) $share;
    }

    public function setShareBathroom($share)
    {
        $this->share_bathroom = (int) (bool) $share;
    }

    public function getShareBathroom()
    {
        return $this->share_bathroom ? 'Yes' : 'No';
    }

    public function getShareBedroom()
    {
        return $this->share_bedroom ? 'Yes' : 'No';
    }

    public function getSmoking()
    {
        switch ($this->smoking) {
            case 0:
                return 'No preference';
            case \NONSMOKER:
                return 'Non-smoker preferred';
            case \SMOKER:
                return 'Smoker preferred';
        }
    }

    public function setGender($gender)
    {
        $this->gender = (bool) $gender;
    }

    public function setSmoking($smoking)
    {
        $this->smoking = (int) $smoking;
    }

    public function getDescription()
    {
        $desc = parent::getDescription();
        return nl2br(trim(strip_tags($desc)));
    }

    public function getGender()
    {
        switch ($this->gender) {
            case 0:
                return 'No preference';
                break;

            case GENDER_MALE:
                return 'Male';
                break;

            case GENDER_FEMALE:
                return 'Female';
                break;
        }
    }

    public function setDescription($desc)
    {
        $this->description = trim(strip_tags($desc));
    }

    public function form()
    {
        $form = parent::getForm('roommate');

        $form->addHidden('rop', 'post_roommate');
        $form->setLabel('name', 'Title');
        $form->addCheck('share_bedroom', 1);
        $form->setLabel('share_bedroom', 'Bedroom shared');
        $form->setMatch('share_bedroom', $this->share_bedroom);

        $form->addCheck('share_bathroom', 1);
        $form->setLabel('share_bathroom', 'Bathroom shared');
        $form->setMatch('share_bathroom', $this->share_bathroom);

        $form->addRadioAssoc('smoking', array(0 => 'No preference', 1 => 'Non-smokers only', 2 => 'Smokers only'));
        $form->setMatch('smoking', $this->smoking);

        $genders[0] = 'No preference';
        $genders[GENDER_MALE] = 'Male';
        $genders[GENDER_FEMALE] = 'Female';

        $form->addRadioAssoc('gender', $genders);
        $form->setMatch('gender', $this->gender);
        $form->addSubmit('Save');
        $tpl = $form->getTemplate();

        if (!empty($this->errors)) {
            foreach ($this->errors as $key => $message) {
                $new_key = strtoupper($key) . '_ERROR';
                $tpl[$new_key] = $message;
            }
        }
        return \PHPWS_Template::process($tpl, 'properties', 'edit_roommate.tpl');
    }

    public function post()
    {
        $vars = array_keys(get_object_vars($this));

        foreach ($_POST as $key => $value) {
            if (in_array($key, $vars)) {
                $func = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                try {
                    $this->$func($value);
                } catch (\Exception $e) {
                    $this->errors[$key] = $e->getMessage();
                }
            }
        }
        // checkboxes
        $this->sublease = isset($_POST['sublease']);
        $this->dishwasher = isset($_POST['dishwasher']);
        $this->appalcart = isset($_POST['appalcart']);
        $this->clubhouse = isset($_POST['clubhouse']);
        $this->workout_room = isset($_POST['workout_room']);
        $this->pets_allowed = isset($_POST['pets_allowed']);
        $this->gender = (int) $_POST['gender'];
        return!isset($this->errors);
    }

    public function update()
    {
        $this->updated = time();
        $this->timeout = $this->updated + (86400 * 30);
        $db = new \PHPWS_DB('prop_roommate');
        $db->addValue('updated', $this->updated);
        $db->addValue('timeout', $this->timeout);
        $db->addWhere('id', $this->id);
        $db->update();
    }

    public function save()
    {
        $this->updated = time();
        if (!$this->id) {
            $this->created = time();
        }
        $this->timeout = $this->updated + (86400 * 30);
        $db = new \PHPWS_DB('prop_roommate');

        if (!$this->id) {
            $this->id = \Current_User::getId();
        }
        //don't judge me. coded into a corner
        $db->addWhere('id', $this->id);
        $db->delete();
        $db->reset();
        $result = $db->saveObject($this, false, false);
        if (\PEAR::isError($result)) {
            \PHPWS_Error::log($result);
            throw new \Exception('Could not save roommate to the database.');
        }
        return true;
    }

    public function viewLink($label=null)
    {
        $url = sprintf('./properties/rop/view/id/%s', $this->id);
        if (!empty($label)) {
            return sprintf('<a href="%s" title="Roommate">%s</a>', $url, $label);
        } else {
            return $url;
        }
    }

    public function view()
    {
        \Layout::addStyle('properties', 'view.css');
        $tpl = $this->getBaseTpl();

        $tpl['BEDROOMS'] = $this->share_bedroom ? 'Yes' : 'No';
        $tpl['BATHROOMS'] = $this->share_bathroom ? 'Yes' : 'No';

        $tpl['SMOKING'] = $this->getSmoking();

        if ($this->pets_allowed) {
            $tpl['PETS_ALLOWED'] = 'Yes';
        } else {
            $tpl['PETS_ALLOWED'] = 'No';
        }

        $tpl['DESCRIPTION'] = $this->getDescription();
        $tpl['TV_TYPE'] = $this->getTvType();
        $tpl['GENDER'] = $this->getGender();

        javascriptMod('properties', 'contact');
        if (\Current_User::isLogged()) {
            if (\Current_User::getId() == $this->id) {
                $purge = $this->getTimeout();

                $tpl['EMAIL'] = '<a href="index.php?module=properties&rop=edit">Update my request</a> |
                    <a href="index.php?module=properties&rop=timeout">Update my cut-off date (' . $purge . ')</a>';
            } else {
                $tpl['EMAIL'] = sprintf('<a style="cursor : pointer" class="message" id="%s">Contact this renter</a>', $this->id);
            }
        } else {
            $tpl['EMAIL'] = sprintf('<a href="%s">Login to contact this renter</a>', Base::loginLink());
        }
        return \PHPWS_Template::process($tpl, 'properties', 'roommate_view.tpl');
    }

    public function rowtags()
    {
        $tpl['NAME'] = $this->viewLink($this->name);
        $tpl['CAMPUS_DISTANCE'] = $this->getCampusDistance();
        $tpl['MONTHLY_RENT'] = $this->getMonthlyRent();
        $tpl['SHARE_BATHROOM'] = $this->getShareBathroom();
        $tpl['SHARE_BEDROOM'] = $this->getShareBedroom();
        $tpl['MOVE_IN_DATE'] = $this->getMoveInDate();
        return $tpl;
    }

}

?>