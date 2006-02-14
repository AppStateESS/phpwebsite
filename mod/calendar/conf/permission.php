<?php
  /**
   * Permissions file for users
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$use_permissions = TRUE;
$item_permissions = TRUE;

$permissions['settings'] = _('Change module settings');

/**
 * Can create other calendars
 * personal calendars handled by settings
 * Users with basic calendar rights can make a
 * private calendar (if settings allow)
 */
$permissions['edit_public'] = _('Create/Edit public schedules');


// can edit schedules assigned to them as well as create
// and edit events on that calendar
$permissions['edit_other']        = _('Edit other schedules');

?>