<?php

/**
 * Helps log information about deprecated modules and classes.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * True to log deprecations, false to not
 */
define('LOG_DEPRECATIONS', 1);

/**
 * Number of days between logged reminders. Setting zero will log EVERY TIME
 * a warning is accessed.
 */
define('DEPRECATE_DAY_SPACING', 1);

class Deprecate {

    public static function moduleWarning($module)
    {
        // disabling as it is broken
        return;
        if (!LOG_DEPRECATIONS) {
            return;
        }

        $dep_name = $module . '_deprecated';
        $last_warned = PHPWS_Settings::get('users', $dep_name);
        $spacing = time() - (86400 * DEPRECATE_DAY_SPACING);
        // It hasn't been long enough to log the warning.
        if ($last_warned && $last_warned > $spacing) {
            return;
        }
        $warning = "The $module module is deprecated and support is discontinued. Please consider uninstalling it.";
        PHPWS_Core::log($warning, 'deprecated.log');
        PHPWS_Settings::set('users', $dep_name, time());
        PHPWS_Settings::save('users');
    }

}
