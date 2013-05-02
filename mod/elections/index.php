<?php
/**
 * WARNING: This module has been deprecated. It will no longer be
 * maintained by phpwebsite and no further bug/security patches will
 * be released. It will be removed from the phpWebsite distribution
 * at some point in the future. We recommend migrating to one of the
 * many other freely available web forums packages.
 *
 * @deprecated since phpwebsite 1.8.0
 *
 * elections - phpwebsite module
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
    include '../../core/conf/404.html';
    exit();
}
Deprecate::moduleWarning('elections');
PHPWS_Core::initModClass('elections', 'Election.php');
$election = new Election;

if (isset($_REQUEST['aop'])) {
    $election->adminMenu();
} elseif (isset($_REQUEST['uop'])) {
    $election->userMenu();
} elseif (isset($_REQUEST['id']) && isset($_REQUEST['candidate_id'])) {
    $election->userMenu('view_candidate');
} elseif (isset($_REQUEST['id'])) {
    $election->userMenu('view_ballot');
} elseif (isset($_REQUEST['ballot']) && isset($_REQUEST['candidate'])) {
    $election->userMenu('view_candidate');
} elseif (isset($_REQUEST['ballot'])) {
    $election->userMenu('view_ballot');
} elseif (isset($_REQUEST['candidate'])) {
    $election->userMenu('view_candidate');
} else {
//    PHPWS_Core::home();
    $election->userMenu('list_ballots');
}


?>