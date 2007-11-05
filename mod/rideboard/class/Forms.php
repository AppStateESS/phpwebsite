<?php

class RB_Forms {
    var $rideboard = null;

    function rideForm(&$form)
    {
        $form->addHidden('module', 'rideboard');
        $locations = $this->rideboard->getLocations();

        $form->addSelect('s_location', $locations);
        $form->setLabel('s_location', dgettext('alert', 'Starting location'));

        $form->addSelect('d_location', $locations);
        $form->setLabel('d_location', dgettext('alert', 'Destination location'));

        $form->addCheck('allow_smoking', 1);
        $form->setLabel('allow_smoking', dgettext('rideboard', 'Will ride with smokers?'));

        $gender[0] = dgettext('rideboard', 'Does not matter');
        $gender[1] = dgettext('rideboard', 'Male only');
        $gender[2] = dgettext('rideboard', 'Female only');

        $form->addRadio('gender_pref', array(0,1,2));
        $form->setLabel('gender_pref', $gender);
        $form->addTplTag('GENDER_PREF_LABEL', dgettext('rideboard', 'Gender preference'));

        $form->addTextArea('comments');
        $form->setLabel('comments', dgettext('rideboard', 'Comments/Other information'));


        $form->dateSelect('departure_time', null, 0,1);
        $form->dateSelect('return_time',null, 0,1);
        $form->addSubmit(dgettext('rideboard', 'Save'));

        $form->addTplTag('DEPARTURE_TIME_LABEL',  dgettext('rideboard', 'Departure date'));
        $form->addTplTag('RETURN_TIME_LABEL', dgettext('rideboard', 'Return date'));

        $form->addTplTag('JS_DEP', javascript('js_calendar', array('form_name'=>'rideboard-form',
                                                                   'date_name'=>'departure_time',
                                                                   'type'     =>'select')));

        $form->addTplTag('JS_RET', javascript('js_calendar', array('form_name'=>'rideboard-form',
                                                                   'date_name'=>'return_time',
                                                                   'type'     =>'select')));
    }

    /**
     * Users looking for passenger are creating a driver entry
     */
    function searchForPassenger()
    {
    }


    function offerRide()
    {
        Layout::addStyle('rideboard', 'forms.css');
        $form = new PHPWS_Form('rideboard-form');
        $this->rideForm($form);

        $form->addHidden('uop', 'offer_ride');
        $form->addHidden('oop', 'post_offer');

        $distance[0] = dgettext('rideboard', 'No detours');


        for ($i = 5; $i < 105; $i += 5) {
            if (PHPWS_Settings::get('rideboard', 'miles_or_kilometers')) {
                $distance[$i] = sprintf(dgettext('rideboard', '%s kilometers'), $i);
            } else {
                $distance[$i] = sprintf(dgettext('rideboard', '%s miles'), $i);
            }
        }

        $form->addSelect('detour_distance', $distance);
        if (PHPWS_Settings::get('rideboard', 'miles_or_kilometers')) {
            $form->setLabel('detour_distance', dgettext('rideboard', 'Total kilometers you would detour for rider(s)'));
        } else {
            $form->setLabel('detour_distance', dgettext('rideboard', 'Total miles you would detour for rider(s)'));
        }

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'rideboard', 'offer_ride.tpl');

    }

    function requestRide()
    {
        Layout::addStyle('rideboard', 'forms.css');
        $form = new PHPWS_Form('rideboard-form');
        $this->rideForm($form);

        $form->addHidden('uop', 'need_ride');
        $form->addHidden('nop', 'post_request');

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'rideboard', 'request_ride.tpl');
    }
}

?>