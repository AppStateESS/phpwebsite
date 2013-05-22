<?php

/**
 * This is a faux module used for purposes of extracting site settings.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class GlobalModule extends ModuleAbstract implements SettingDefaults {

    /**
     * Eventually to be handled by UI
     */
    public function getSettingDefaults()
    {
        $settings['language'] = DEFAULT_LANGUAGE;
        return $settings;
    }

    public function run()
    {

    }

    public function init()
    {
        
    }

}

?>
