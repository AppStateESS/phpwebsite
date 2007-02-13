<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

translate('users');
$use_permissions = TRUE;

$permissions['edit_users']       = _('Edit Users');
$permissions['delete_users']     = _('Delete Users');
$permissions['add_edit_groups']  = _('Add / Edit Groups');
$permissions['delete_groups']    = _('Delete Groups');
$permissions['edit_permissions'] = _('Edit Permissions');
/**
 * Also controls individual user authorization setting
 */
$permissions['settings']         = _('Authorization/Settings');

translate();
?>