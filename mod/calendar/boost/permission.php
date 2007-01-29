<?php
  /**
   * Permissions file for users
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$use_permissions  = TRUE;
$item_permissions = TRUE;

$permissions['settings']            = _('Change module settings (unrestricted only)');
$permissions['edit_public']         = _('Create/Edit public schedule');

// Creation of private schedules handled by calendar settings
$permissions['edit_private']        = _('Edit private schedule');
$permissions['delete_schedule']     = _('Delete schedules (unrestricted only)');

?>