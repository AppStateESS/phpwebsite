<?php

/* 
 * Copyright (C) 2016 Matthew McNaney <mcnaneym@appstate.edu>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

/**
 * Logs a message to the specified $filename in side the defined LOG_DIRECTORY
 *
 * @param string $message
 * @param string $filename
 * @return boolean
 */
function logMessage($message, $filename)
{
    if (preg_match('|[/\\\]|', $filename)) {
        trigger_error('Slashes not allowed in log file names.', E_USER_ERROR);
    }
    loadTimeZone();
    $log_path = LOG_DIRECTORY . $filename;
    $message = strftime('[' . LOG_TIME_FORMAT . ']', time()) . trim($message) . "\n";
    if (error_log($message, 3, $log_path)) {
        chmod($log_path, LOG_PERMISSION);
        return true;
    } else {
        trigger_error("Could not write $filename file. Check error directory setting and file permissions.",
                E_USER_ERROR);
    }
}

function loadTimeZone()
{
    if (defined('DATE_SET_SERVER_TIME_ZONE')) {
        date_default_timezone_set(DATE_SET_SERVER_TIME_ZONE);
        return;
    }


    if (defined('PHPWS_SOURCE_DIR')) {
        $dir = PHPWS_SOURCE_DIR . 'core/conf/';
    } else {
        $dir = 'core/conf/';
    }

    $tz = ini_get('date.timezone');

    if (is_file($dir . 'defines.php')) {
        $defines = $dir . 'defines.php';
    } elseif (is_file($dir . 'defines.dist.php')) {
        $defines = $dir . 'defines.dist.php';
    } else {
        $defines = null;
    }

    require_once $defines;

    if (defined('DATE_SET_SERVER_TIME_ZONE')) {
        date_default_timezone_set(DATE_SET_SERVER_TIME_ZONE);
    } elseif (!empty($tz)) {
        date_default_timezone_set($tz);
    } else {
        date_default_timezone_set('America/New_York');
    }
}