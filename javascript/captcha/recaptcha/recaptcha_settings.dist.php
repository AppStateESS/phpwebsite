<?php

/**
 * recaptcha_settings.dist.php - Default Settings for the ReCaptcha captcha library
 *
 * This file is provided as an example of the configuration for the phpWebsite
 * Recaptcha module. It (generally) contains default values, which you can
 * (and sometimes must) customize.
 *
 * In order to use the ReCaptcha module, you need to rename this file to
 * 'recaptcha_settings.php' and supply your own API keys, acquired from
 * recapthca.net.
 *
 * This file ('recaptcha_settings.dist.php') will be overwritten by future
 * version upgrages. However, the 'recaptcha_settings.php' file will NOT
 * be overwitten, so your customizations will not be lost.
 *
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */


/**
 * API Keys
 *
 * Get public and private API keys by going to http://recaptcha.net
 * and signing up for an account.
 */
define('RECAPTCHA_PUBLIC_KEY',  '');
define('RECAPTCHA_PRIVATE_KEY', '');

/**
 * The ReCapthca theme
 *
 * Valid values are: 'red' | 'white' | 'blackglass' | 'clean' | 'custom'
 *
 * See: http://recaptcha.net/apidocs/captcha/client.html for information
 * custom theming.
 */
define('RECAPTCHA_THEME',		'clean');


?>
