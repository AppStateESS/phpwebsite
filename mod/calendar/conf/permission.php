<?php

$use_permissions = TRUE;
$item_permissions = TRUE;

$permissions['settings']             = _('Change module settings');

// Can create other calendars
// personal calendars handled by settings
$permissions['create_schedule']      = _('Create calendars');
$permissions['edit_schedule']        = _('Edit calendars');

$permissions['edit_event']           = _('Edit event');
$permissions['delete_event']         = _('Delete event');

?>