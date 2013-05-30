<?php

/**
 * Settings Interface - Generates HTML for the settings form.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */
class SettingsView {

    private $settings;

    public function __construct(LikeboxSettings $settings)
    {
        $this->settings = $settings;
    }

    public function show()
    {
        // Setup form
        $form = new PHPWS_Form('likebox_settings');
        $form->setMethod('POST');

        // Hidden fields for directing this request after submission
        $form->addHidden('module', 'likebox');
        $form->addHidden('action', 'SaveSettings');

        // Enabled Checkbox
        $form->addCheck('enabled', 'enabled');
        $form->setLabel('enabled', 'Enabled');

        // URL
        $form->addText('fb_url', $this->settings->get('fb_url'));
        $form->setLabel('fb_url', 'Facebook Page URL:');

        // Width
        $form->addText('width', $this->settings->get('width'));
        $form->setLabel('width', 'Width');

        // Height
        $form->addText('height', $this->settings->get('height'));
        $form->setLabel('height', 'Height');

        // Show header bar i.e. "Find us on Facebook"
        $form->addCheck('show_header', 'show_header');
        $form->setLabel('show_header', 'Show header');

        // Show border
        $form->addCheck('show_border', 'show_border');
        $form->setLabel('show_border', 'Show border');

        // Show stream
        $form->addCheck('show_stream', 'show_stream');
        $form->setLabel('show_stream', 'Show stream');

        // Show faces
        $form->addCheck('show_faces', 'show_faces');
        $form->setLabel('show_faces', 'Show faces');

        // Submit button
        $form->addSubmit('submit', 'Submit');

        $checkBoxes = array('enabled', 'show_header', 'show_border', 'show_stream', 'show_faces');
        foreach($checkBoxes as $key){
            $value = $this->settings->get($key);
            if(isset($value) && $value == 1){
                $form->setMatch($key, $key);
            }
        }

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'likebox', 'settings.tpl');
    }
}

?>
