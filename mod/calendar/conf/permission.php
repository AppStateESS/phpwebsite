<?php
  /**
   * Permissions file for users
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$use_permissions = TRUE;
$item_permissions = TRUE;

$permissions['settings']        = _('Change module settings');

$permissions['edit_public']     = _('Create/Edit public schedules');
$permissions['edit_private']    = _('Edit private schedules');
$permissions['delete_schedule'] = ('Delete schedules');

?>