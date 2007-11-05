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
        $form->setLabel('allow_smoking', dgettext('rideboard', 'Willing to ride with smokers?'));

        $gender[0] = dgettext('rideboard', 'Does not matter');
        $gender[1] = dgettext('rideboard', 'Male only');
        $gender[2] = dgettext('rideboard', 'Female only');

        $form->addRadio('gender_pref', array(0,1,2));
        $form->setLabel('gender_pref', $gender);
        $form->addTplTag('GENDER_PREF_LABEL', dgettext('rideboard', 'Gender preference'));

        $form->addTextArea('comments');
        $form->setLabel('comments', dgettext('rideboard', 'Comments/Other information'));
    }

    /**
     * Users looking for passenger are creating a driver entry
     */
    function searchForPassenger()
    {
    }

    function requestRide()
    {
        Layout::addStyle('rideboard', 'forms.css');
        $form = new PHPWS_Form('rideboard-form');
        $form->addHidden('uop', 'need_ride');
        $form->addHidden('nop', 'post_request');

        $this->rideForm($form);

        $form->dateSelect('departure_time', null, 0,1);
        $form->dateSelect('return_time',null, 0,1);
        $form->addSubmit(dgettext('rideboard', 'Save'));

        $tpl = $form->getTemplate();
        $tpl['DEPARTURE_TIME_LABEL'] = dgettext('rideboard', 'Departure date');
        $tpl['JS_CAL'] = javascript('js_calendar', array('form_name'=>'rideboard-form',
                                                         'date_name'=>'departure_time',
                                                         'type'     =>'select'));

        return PHPWS_Template::process($tpl, 'rideboard', 'need_driver.tpl');
    }
}

?>