<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

$use_permissions = TRUE;

$permissions['edit_users']       = dgettext('users', 'Edit Users');
$permissions['delete_users']     = dgettext('users', 'Delete Users');
$permissions['add_edit_groups']  = dgettext('users', 'Add / Edit Groups');
$permissions['delete_groups']    = dgettext('users', 'Delete Groups');
$permissions['edit_permissions'] = dgettext('users', 'Edit Permissions');
$permissions['scripting']        = dgettext('users', 'Allow script tag input');
/**
 * Also controls individual user authorization setting
 */
$permissions['settings']         = dgettext('users', 'Authorization/Settings');
?>