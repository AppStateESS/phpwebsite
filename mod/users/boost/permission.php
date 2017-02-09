<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

$use_permissions = TRUE;

$permissions['edit_users']       = 'Edit Users';
$permissions['delete_users']     = 'Delete Users';
$permissions['add_edit_groups']  = dgettext('users', 'Add / Edit Groups');
$permissions['delete_groups']    = 'Delete Groups';
$permissions['edit_permissions'] = 'Edit Permissions';
$permissions['scripting']        = '<del>' . 'Allow script tag input' . '</del>';
/**
 * Also controls individual user authorization setting
 */
$permissions['settings']         = dgettext('users', 'Authorization/Settings');
