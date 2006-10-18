<?php
  /**
   * Permissions file for users
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$use_permissions  = TRUE;
$item_permissions = TRUE;

$permissions['settings']            = _('Change module settings');

$permissions['edit_public']         = _('Create/Edit public schedule');
$permissions['edit_public_events']  = _('Create/Edit public events');
$permissions['edit_private']        = _('Edit private schedule');
$permissions['edit_private_events'] = _('Edit private events');
$permissions['delete_schedule']     = _('Delete schedules');

?>