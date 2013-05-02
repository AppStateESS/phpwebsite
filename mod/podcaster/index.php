<?php
/**
 * WARNING: This module has been deprecated. It will no longer be
 * maintained by phpwebsite and no further bug/security patches will
 * be released. It will be removed from the phpWebsite distribution
 * at some point in the future.
 *
 * @deprecated since phpwebsite 1.8.0
 * podcaster - phpwebsite module
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
Deprecate::moduleWarning('podcaster');
PHPWS_Core::initModClass('podcaster', 'Podcaster.php');
$podcaster = new Podcaster;

if (isset($_REQUEST['aop'])) {
    $podcaster->adminMenu();
} elseif (isset($_REQUEST['uop'])) {
    $podcaster->userMenu();
} elseif (isset($_REQUEST['id']) && isset($_REQUEST['episode_id'])) {
    $podcaster->userMenu('view_episode');
} elseif (isset($_REQUEST['id'])) {
    $podcaster->userMenu('view_channel');
} elseif (isset($_REQUEST['channel']) && isset($_REQUEST['episode'])) {
    $podcaster->userMenu('view_episode');
} elseif (isset($_REQUEST['channel'])) {
    $podcaster->userMenu('view_channel');
} else {
    PHPWS_Core::home();
}

?>