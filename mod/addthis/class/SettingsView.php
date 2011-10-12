<?php

/**
 * Settings Interface - Generates HTML for the settings form.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */
class SettingsView {
    
    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function show()
    {
        // Setup form
        $form = new PHPWS_Form('addthis_settings');
        $form->setMethod('POST');

        // Hidden fields for directing this request after submission
        $form->addHidden('module', 'addthis');
        $form->addHidden('action', 'SaveSettings');

        // List of checkboxes and their labels for settings
        $settingList = array('enabled', 'fb_like_enabled', 'google_plus_enabled','share_bar_enabled');
        $settingLabels = array('Enabled', 'Facebook Like Enabled', 'Google+ Enabled', 'Share Bar Enabled');

        // Add checkboxes, set labels
        $form->addCheck('enabled_check', $settingList);
        $form->setLabel('enabled_check', $settingLabels);

        // If a setting is enabled, then check its box
        $toCheck = array();
        foreach($this->settings->getAll() as $key=>$value){
            if($value == 1){
                $toCheck[] = $key;
            }
        }

        // NB: Have to set the checked elements all at once
        $form->setMatch('enabled_check', $toCheck);

        $form->addSubmit('submit', 'Submit');

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'addthis', 'settings.tpl');
    }
}

?>
