<?php

/**
 * Settings class - Singleton class that Wraps phpws settings for Likebox.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */
class LikeboxSettings {
    
    // Static var to hold instance of this class
    private static $instance;

    // Settings
    private $settings;

    // Private constructor for singleton
    private function __construct()
    {
        // load all the settings into a local array
        $this->settings = PHPWS_Settings::get('likebox');
    }

    // Set a given setting by name
    public function set($settingName, $value)
    {
        $this->settings[$settingName] = $value;
        PHPWS_Settings::set('likebox', $settingName, $value);
        PHPWS_Settings::save('likebox');
    }

    // Returns the value of the given setting name
    public function get($settingName)
    {
       return $this->settings[$settingName]; 
    }

    public function getAll()
    {
        return $this->settings;
    }

    /**
     * Returns an instance of this class. If one doesn't exist,
     * it instanciates a new one and returns it.
     */
    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new LikeboxSettings();
        }

        return self::$instance;
    }
}

?>
