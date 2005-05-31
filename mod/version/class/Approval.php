<?php

/**
 * Class assists with managing items needing approval
 *
 * @author Matt McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Version_Approval {
  var $module         = NULL;
  var $source_table   = NULL;
  var $version_table  = NULL;
  var $view_url       = NULL;
  var $edit_url       = NULL;
  var $approve_url    = NULL;
  var $disapprove_url = NULL;
  var $columns        = array();
  var $standard       = array('id', 'source_id', 'vr_creator', 'vr_editor',
			      'vr_create_date', 'vr_edit_date', 'vr_number',
			      'vr_current', 'vr_approved', 'vr_locked');
  

  function Version_Approval($module, $table)
  {
    $this->setModule($module);
    $this->setSourceTable($table);
  }

  function setModule($module)
  {
    $this->module = $module;
  }

  function setSourceTable($table)
  {
    $this->source_table = $table;
    $this->version_table = $this->source_table . VERSION_TABLE_SUFFIX;
  }

  function setApproveUrl($approve_url)
  {
    $this->approve_url = $approve_url;
  }

  function setDisapproveUrl($disapprove_url)
  {
    $this->disapprove_url = $disapprove_url;
  }

  function setViewUrl($view_url)
  {
    $this->view_url = $view_url;
  }

  function setEditUrl($edit_url)
  {
    $this->edit_url = $edit_url;
  }

  function setColumns()
  {
    $this->columns = func_get_args();
  }
  function getList()
  {
    if (empty($this->approve_url) || empty($this->disapprove_url)) {
      return FALSE;
    }
    
    $sql = '
SELECT users.username, ' . $this->version_table. '.* 
FROM ' . $this->version_table . ', users
WHERE
' . $this->version_table . '.vr_approved=0
AND
users.id=' . $this->version_table . '.vr_creator';

    $result = PHPWS_DB::getAll($sql);

    if (empty($result)) {
      return _('No items for approval.');
    }
    $temp_count = 0;
    foreach ($result as $app_item) {
      $links = array();

      /*
       // come back to this
      if (!empty($app_item['source_id'])) {
	$source_db = & new PHPWS_DB($this->source_table);
	$source_db->addWhere('id', $this->source_id);
	$result = $source_db->select('one');
	test($result);
      }
      */
      $row_tpl['CREATE_DATE_LABEL'] = _('Created');
      $row_tpl['CREATE_DATE']       = strftime('%c', $app_item['vr_create_date']);

      $row_tpl['AUTHOR_LABEL']      = _('Author');
      $row_tpl['AUTHOR']            = $app_item['username'];
      // prevent the repeat
      unset($app_item['username']);

      $keys = array_keys($app_item);
      if (!empty($this->columns)) {
	$show_cols = array_intersect($this->columns, $keys);
      } else {
	$show_cols = array_diff($keys, $this->standard);
      }

      $count = 0;

      foreach ($show_cols as $show_tag) {
	$count++;
	$row_tpl['COLUMN_LABEL_' . $count] = $show_tag;
	$row_tpl['COLUMN_' . $count] = $app_item[$show_tag];
      }

      if (!Current_User::isRestricted($this->module)) {
	$links[] = sprintf('<a href="%s">%s</a>',
			   $this->approve_url . '&amp;version_id=' . $app_item['id'] .
			   '&amp;authkey=' . Current_User::getAuthKey(),
			   _('Approve'));
	
	$links[] = sprintf('<a href="%s">%s</a>',
			   $this->disapprove_url . '&amp;version_id=' . $app_item['id'] .
			   '&amp;authkey=' . Current_User::getAuthKey(),
			   _('Disapprove'));
      }


      if (isset($this->view_url)) {
	$links[] = sprintf('<a href="%s">%s</a>',
			   $this->view_url . '&amp;version_id=' . $app_item['id'], _('View'));
      }

      if (isset($this->edit_url)) {
	$links[] = sprintf('<a href="%s">%s</a>',
			   $this->edit_url . '&amp;version_id=' . $app_item['id'], _('Edit'));
      }

      $row_tpl['LINKS'] = implode(' | ', $links);

      $template['approval-rows'][$temp_count] = $row_tpl;
      $temp_count++;
    }

    return PHPWS_Template::process($template, 'version', 'approval_list.tpl');
  }

}

?>
