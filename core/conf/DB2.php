<?php
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * This file holds configuration options for MDB2. Please see:
 * http://pear.php.net/manual/en/package.database.mdb2.intro-manager-module.php
 * http://www.installationwiki.org/MDB2 (HUGE thanks for putting this together by the way)
 *
 * Generally, this file should not be touched. Do so at your own risk.
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package DB2
 * @license http://opensource.org/licenses/lgpl-3.0.html
 *
 */


// This setting applies to all databases. STRONGLY SUGGEST it not be changed (especially seqcol name)
// See here for more info: http://pear.php.net/manual/en/package.database.mdb2.intro-connect.php
$all   = array('database' => array ('result_buffering' => true,
                                    'seqcol_name'      => 'id'));

/**
 * mysql type is NOT set to allow default engine; which is usually MyISAM.
 * For foreign keys and transactions, you will need InnoDB.
 */
$mysql = array('table' => array('charset' => 'utf8',
                                'collate' => 'utf8_unicode_ci'));


?>