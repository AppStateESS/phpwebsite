<?php
/**
 * rolodex - phpwebsite module
 *
 *
 * WARNING: This module has been deprecated. It will no longer be
 * maintained by phpwebsite and no further bug/security patches will
 * be released. It will be removed from the phpWebsite distribution
 * at some point in the future.
 * 
 * @deprecated
 *
 *
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id$
 * @author Verdon Vaillancourt <verdonv at gmail dot com>
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    exit();
}

PHPWS_Core::initModClass('rolodex', 'Rolodex.php');
$rolodex = new Rolodex;

if (isset($_REQUEST['aop'])) {
    $rolodex->adminMenu();
} elseif (isset($_REQUEST['uop'])) {
    $rolodex->userMenu();
} elseif (isset($_REQUEST['id'])) {
    $rolodex->userMenu('view_member');
} else {
    $rolodex->userMenu('list');
}

?>