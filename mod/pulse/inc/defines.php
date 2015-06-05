<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

/**
 * The process is asleep. It will not be run again until set to awake status
 */
define('PULSE_STATUS_ASLEEP', 0);

/**
 * The process will run every interim. Normal functioning.
 */
define('PULSE_STATUS_AWAKE', 1);

/**
 * The schedule is still processing. The schedule will not run.
 */
define('PULSE_STATUS_PROCESSING', 2);

/**
 * A condition occurred that put the schedule into holding. Like asleep,
 * but indicates something bad happened and it will need to be adminstratively
 * reset.
 */
define('PULSE_STATUS_HOLDING', 3);

define('EXCEPTION_SCHEDULE_NOT_FOUND', 1);