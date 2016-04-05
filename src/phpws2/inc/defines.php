<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

/**
 * The maximum amount of characters allowed for a varchar. Recent MySQL is 65,535 and Postgresql
 * is 1 GB. Default is 500 but it can be raised. Over this value, text will be used over varchar.
 */
define('DB_VARCHAR_LIMIT', 500);