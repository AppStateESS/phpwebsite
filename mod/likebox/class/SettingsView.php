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
        $form->addCheck('small_header', 'small_header');
        $form->setLabel('small_header', 'Use Small header');

        // Show border
        $form->addCheck('hide_cover', 'hide_cover');
        $form->setLabel('hide_cover', 'Hide Cover Photo');

        // Show stream
        $form->addCheck('show_posts', 'show_posts');
        $form->setLabel('show_posts', 'Show Page Posts');

        // Show faces
        $form->addCheck('show_faces', 'show_faces');
        $form->setLabel('show_faces', 'Show Friend\'s faces');

        // Submit button
        $form->addSubmit('submit', 'Submit');

        $checkBoxes = array('enabled', 'small_header', 'hide_cover', 'show_posts', 'show_faces');

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
