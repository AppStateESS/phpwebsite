<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

$use_permissions = TRUE;

translate('comments');
$permissions['edit_comments']   = _('Edit comments');
$permissions['delete_comments'] = _('Delete comments');
$permissions['settings']        = _('Change settings');
translate();

$item_permissions = FALSE;
?>