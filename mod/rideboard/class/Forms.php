<?php

class RB_Forms {
    var $rideboard = null;

    function rideForm(&$form)
    {
        $form->addHidden('module', 'rideboard');
        if (PHPWS_Settings::get('rideboard', 'show_country')) {
            $countries = $this->rideboard->getCountries();
            $form->addSelect('s_country', $countries);
            $form->addSelect('d_country', $countries);
        }

        $locations = $this->rideboard->getLocations();

        $form->addSelect('s_location', $locations);
        $form->setLabel('s_location', dgettext('alert', 'Starting location'));

        $form->addSelect('d_location', $locations);
        $form->setLabel('d_location', dgettext('alert', 'Destination location'));

        $form->addCheck('allow_smoking', 1);
        $form->setLabel('allow_smoking', dgettext('rideboard', 'Can ride with smokers?'));

        $gender[0] = dgettext('rideboard', 'Does not matter');
        $gender[1] = dgettext('rideboard', 'Male only');
        $gender[2] = dgettext('rideboard', 'Female only');

        $form->addRadio('gender_pref', array(0,1,2));
        $form->setLabel('gender_pref', $gender);

        $form->addTextArea('comments');

        $form->addText('contact_email', Current_User::getEmail());

        $form->addTplTag('START_LEGEND', dgettext('rideboard', 'Starting information'));
        $form->addTplTag('DESTINATION_LEGEND', dgettext('rideboard', 'Destination information'));

    }

    /**
     * Users looking for passenger are creating a driver entry
     */
    function searchForPassenger()
    {
        Layout::addStyle('rideboard', 'forms.css');
        $form = new PHPWS_Form('passenger-search');
        $this->rideForm($form);
        $form->addHidden('uop', 'passenger');

        $form->dateSelect('departure_time', null, 0,1);
        $form->dateSelect('return_time',null, 0,1);

        $tpl = $form->getTemplate();
        $tpl['DEPARTURE_TIME_LABEL'] = dgettext('rideboard', 'Departure time and date');

        return PHPWS_Template::process($tpl, 'rideboard', 'search_passenger.tpl');
    }

    function needDriver()
    {

    }
}

?>