<?php

$use_permissions = TRUE;
$item_permissions = TRUE;

$permissions['settings']             = _('Change module settings');

// Can create other calendars
// personal calendars handled by settings
$permissions['create_schedule']      = _('Create calendars');
// can edit schedules assigned to them as well as create
// and edit events on that calendar
$permissions['edit_schedule']        = _('Edit calendars');

$permissions['delete_event']         = _('Delete event');

?>