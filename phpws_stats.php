<?php

/**
 * Shows debugging information. stats_on must be TRUE. Other options
 * can be toggled according to what you need.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

/**
 * Line below determines which errors should be shown.
 * The default is E_ALL. If you are having problems, it is suggested
 * you uncomment this line.
 * While running smoothly, this line should be commented out. You
 * do NOT want users seeing system error messages.
 * There is also an ini_set for display_errors. Again, this should normally be
 * kept commented.
 */

//ini_set('display_errors', 'On');
//error_reporting (-1);

/**
 * If the define below is uncommented, the ip address set within will
 * limit views to a specific ip address. So, if you need to see information
 * about your installation, but don't wish others coming to the site to
 * see this information, uncomment the below and put your ip address within.
 * The default setting is uncommented for 127.0.0.1 viewable only.
 */
define('IP_VIEW', '127.0.0.1');

define('stats_on',        false); // Must be true for anything to echo
define('stats_classes',   false); // Show the classes currently included
define('stats_time',      false); // Show the amount of time it took to compute
define('stats_memory',    false); // Show amount of memory used
define('stats_sessions',  false); // Show the sessions currently used
define('show_request',    false); // Show the _REQUEST info
define('display_status',  false); // Shows configuration settings that may confuse developer
define('browser_details', false); // Shows information collected about your browser
define('language_details',false); // Shows the current and default language options.

if (stats_on && stats_time) {
    list($usec, $sec) = explode(' ', microtime());
    $GLOBALS['site_start_time'] = ((float)$usec + (float)$sec);
}

function show_stats()
{
    if (defined('IP_VIEW') && IP_VIEW != $_SERVER['REMOTE_ADDR']) {
        return;
    }

    if (!stats_on) {
        return;
    }

    if (stats_time) {
        list($usec, $sec) = explode(' ', microtime());
        $site_end_time = ((float)$usec + (float)$sec);
        $execute_time = round( ($site_end_time - $GLOBALS['site_start_time']), 3);
        $content[] = sprintf(_('Execution time: %s seconds.'), $execute_time);
    }

    if (stats_memory) {
        $memory_used = round( (memory_get_usage() / 1024) / 1024, 3);
        $content[] = sprintf('Memory used: %sMB', $memory_used);
    }

    if (stats_classes) {
        $classes = get_declared_classes();
        $content[] = _('Declared classes:') . '<ul>' . implode('</li><li>', $classes) . '</ul>';
    }

    if (stats_sessions) {
        $sessions = array_keys($_SESSION);
        $content[] = _('Current sessions:') . '<ul>' . implode('</li><li>', $sessions) . '</ul>';
    }

    if (display_status) {
        if (ALLOW_CACHE_LITE) {
            $subcontent[] = _('Cache Lite is enabled.');
        } else {
            $subcontent[] = _('Cache Lite is disabled.');
        }

        if (FORCE_MOD_CONFIG) {
            $subcontent[] = _('Using configuration files directly from module\'s directory.');
        } else {
            $subcontent[] = _('Using local configuration files.');
        }

        $content[] = implode('<br />', $subcontent);
    }

    if (language_details) {
        $subcontent[] = sprintf(_('The current language is %s.'), CURRENT_LANGUAGE);
        $subcontent[] = sprintf(_('The default language is %s.'), DEFAULT_LANGUAGE);
        if (FORCE_DEFAULT_LANGUAGE) {
            $subcontent[] = _('The default language is being forced.');
        }
        $content[] = implode('<br />', $subcontent);
    }

    if (isset($content)) {
        echo implode('<hr />', $content);
    }

    if(show_request) {
        echo '<hr />' . _('Request') . ' ';
        test($_REQUEST);
    }

    if (browser_details) {
        test($GLOBALS['browser_info']);
    }
}

?>