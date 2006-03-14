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
   */
  // error_reporting (E_ALL);

define('stats_on',       FALSE); // Must be true for anything to echo
define('stats_classes',  FALSE); // Show the classes currently included
define('stats_time',     FALSE);  // Show the amount of time it took to compute
define('stats_memory',   FALSE);  // Show amount of memory used
define('stats_sessions', FALSE);  // Show the sessions currently used
define('show_request',   FALSE);  // Show the _REQUEST info

if (stats_on && stats_time) {
    list($usec, $sec) = explode(' ', microtime());
    $GLOBALS['site_start_time'] = ((float)$usec + (float)$sec);
 }

function show_stats()
{
    if (!stats_on) {
        return TRUE;
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
    
    if (isset($content)) {
        echo implode('<hr />', $content);
    }

    if(show_request) {
        echo _('Request') . ' ';
        test($_REQUEST);
    }
}

?>