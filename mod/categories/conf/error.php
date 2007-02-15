<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

translate('categories');
$errors[CATEGORY_NOT_FOUND]    = _('Category ID not found');
$errors[CAT_DB_PROBLEM]        = _('There was a problem accessing the database.');
$errors[CAT_NO_MOD_TABLE]      = _('Module does not have a category table.');
$errors[CAT_ITEM_MISSING_VAL]  = _('Category item is missing variables.');
$errors[CAT_NO_IDS_IN_VERSION] = _('Missing either a version or item id.');
translate();
?>