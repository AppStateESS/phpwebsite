<?php
/**
 * Facilitates versioning, backup, and approvals
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */

define('VERSION_TABLE_SUFFIX', '_version');

/* Error messages */
define('VERSION_MISSING_ID',     -1);
define('VERSION_NO_TABLE',       -2);
define('VERSION_NOT_MODULE',     -3);
define('VERSION_WRONG_SET_VAR',  -4);
define('VERSION_MISSING_SOURCE', -5);

class Version {
  var $id             = 0;
  var $source_id      = 0;
  var $source_table   = NULL;
  var $version_table  = NULL;
  var $source_data    = NULL;
  var $vr_creator     = 0;
  var $vr_editor      = 0;
  var $vr_create_date = 0;
  var $vr_edit_date   = 0;
  var $vr_number      = 0;
  var $vr_current     = 0;
  var $vr_approved    = 0;
  var $vr_locked      = 0;
  
  var $_error         = NULL;

  function Version($source_table, $id=NULL)
  {
    $this->source_table = $source_table;
    $this->id = (int)$id;
    $result = $this->init();
    if (PEAR::isError($result)) {
      $this->_error = $result;
      return;
    }
  }

  function setId($id){
    $this->id = (int)$id;
  }

  function getId(){
    return $this->id;
  }

  function getVersionId(){
    return $this->id;
  }

  function getCreationDate($format=FALSE){
    if ($format = TRUE) {
      return strftime('%c', $this->vr_create_date);
    } else {
      return $this->vr_create_date;
    }
  }

  function getEditedDate($format=FALSE){
    if ($format = TRUE) {
      return strftime('%c', $this->vr_edit_date);
    } else {
      return $this->vr_edit_date;
    }
  }

  function getCreator(){
    return $this->vr_creator;
  }

  function getEditor(){
    return $this->vr_editor;
  }

  function setApproved($approve){
    $this->vr_approved = (int)$approve;
  }

  function isApproved(){
    return (bool)$this->vr_approved;
  }

  function init()
  {
    if (!PHPWS_DB::isTable($this->source_table))
      return PHPWS_Error::get(VERSION_NO_TABLE, 'version', 'init', $this->source_table);

    $result = $this->_initVersionTable();
    if (PEAR::isError($result)) {
      $this->_error = $result;
      return;
    }

    if (!empty($this->id)) {
      $result = $this->_initVersion();
      if (PEAR::isError($result)) {
	return $result;
      }
    }
  }

  function getSource(){
    $data = $this->source_data;
    $data['id'] = $this->source_id;
    return $data;
  }

  function setSource($source_data){
    if (is_object($source_data)) {
      $data_values = get_object_vars($source_data);
    }
    elseif (is_array($source_data)) {
      $data_values = $source_data;
    }
    else {
      return PHPWS_Error::get(VERSION_WRONG_SET_VAR, 'version', 'set', gettype($source_data));
    }

    $this->source_data = $data_values;
    if (isset($this->source_data['id'])) {
      $this->source_id = $this->source_data['id'];
    }

    return TRUE;
  }

  function setSourceId($id){
    $this->source_id = (int)$id;
  }

  function getSourceId(){
    return $this->source_id;
  }

  function save(){
    $source_db = & new PHPWS_DB($this->source_table);
    $version_db = & new PHPWS_DB($this->version_table);
    if (empty($this->source_data))
      return PHPWS_Error::get(VERSION_MISSING_SOURCE, 'version', 'save');

    if (empty($this->id)) {
      $this->vr_creator = Current_User::getId();
      $this->vr_create_date = mktime();
    }
    else {
      $this->vr_editor = Current_User::getId();
      $this->vr_edit_date = mktime();
    }

    if (empty($this->vr_number))
      $this->vr_number = $this->_getVersionNumber();

    if ($this->vr_approved || empty($this->source_id)) {
      $this->vr_current = 1;
    } else {
      $this->vr_current = 0;
    }

    foreach ($this->source_data as $col_name => $col_val) {
      if ($col_name == 'id') {
	$version_db->addValue('source_id', (int)$col_val);
      } else {
	if (!$version_db->isTableColumn($col_name)) {
	  if($source_db->isTableColumn($col_name)) {
	    $result = $this->_copyVersionColumn($col_name);
	    if (PEAR::isError($result)){
	      return $result;
	    }
	  } else {
	    continue;
	  }
	}

	$version_db->addValue($col_name, $col_val);
      }
    }

    $version_db->addValue('vr_creator',     $this->vr_creator);
    $version_db->addValue('vr_editor',      $this->vr_editor);
    $version_db->addValue('vr_create_date', $this->vr_create_date);
    $version_db->addValue('vr_edit_date',   $this->vr_edit_date);
    $version_db->addValue('vr_number',      $this->vr_number);
    $version_db->addValue('vr_current',     $this->vr_current);
    $version_db->addValue('vr_approved',    $this->vr_approved);

    if ($this->vr_current) {
      $this->_clearCurrents();
    }

    if (!empty($this->id)) {
      $version_db->addWhere('id', $this->id);
      return $version_db->update();
    } else {
      $result = $version_db->insert();
      if (PEAR::isError($result)) {
	$this->_error = $result;
	PHPWS_Error::log($result);
	return FALSE;
      }
      $this->id = $result;
    }
    return TRUE;
  }

  function _clearCurrents(){
    $db = & new PHPWS_DB($this->version_table);
    $db->addWhere('source_id', $this->source_id);
    $db->addValue('vr_current', 0);
    $db->update();
  }

  function getUnapproved($restrict=FALSE){
    $version_db = & new PHPWS_DB($this->version_table);

    if ($restrict == TRUE) {
      $version_db->addWhere('vr_creator', Current_User::getId());
    }

    $result = $version_db->addWhere('vr_approved', 0);
    $result = $version_db->select();

    if (PEAR::isError($result) || empty($result))
      return $result;
    
    foreach ($result as $row) {
      $version = & new Version($this->source_table);
      $version->_plugInVersion($row);
      $unapproved_list[$row['id']] = $version;
    }

    return $unapproved_list;
  }

  function _plugInVersion($data){
    if (!is_array($data)){
      return FALSE;
    }
    PHPWS_Core::plugObject($this, $data);
    $diff = array_diff_assoc($data, get_object_vars($this));
    $this->setSource($diff);

    return TRUE;
  }

  function _copyVersionColumn($col_name){
    $source_db = & new PHPWS_DB($this->source_table);
    $version_db = & new PHPWS_DB($this->version_table);

    $col_info = $source_db->getColumnInfo($col_name, TRUE);
    if (isset($col_info['index']))
      $index = TRUE;
    else
      $index = FALSE;

    return $version_db->addTableColumn($col_name, $col_info['parameters'], NULL, $index);
  }

  function _getVersionNumber(){
    if (empty($this->source_id))
      return 1;
    $version_db = & new PHPWS_DB($this->version_table);

    $version_db->addWhere('source_id', $this->source_id);
    $version_db->addColumn('vr_number');
    $current_version = $version_db->select('max');
    if (empty($current_version)) {
      $current_version = 1;
    }
    else {
      $current_version++;
    }

    return $current_version;
  }

  function _initVersion()
  {
    $version_db = & new PHPWS_DB($this->version_table);
    $version_db->addWhere('id', $this->id);
    $row = $version_db->select('row');
    if (PEAR::isError($row))
      return $row;

    $this->_plugInVersion($row);
  }

  function _initVersionTable()
  {
    $this->version_table = $this->source_table . VERSION_TABLE_SUFFIX;
    if (!PHPWS_DB::isTable($this->version_table)){
      $result = Version::_buildVersionTable();

      if (PEAR::isError($result))
	return $result;
    }

    return TRUE;
  }

  function _buildVersionTable(){
    $source_db = & new PHPWS_DB($this->source_table);
    $allColumns = $source_db->getTableColumns(TRUE);

    foreach ($allColumns as $editCol){
      $newColumns[] = $editCol;
      if ($editCol['name'] == 'id')
	$newColumns[] = array('table' => $this->version_table,
			      'name'  => 'source_id',
			      'type'  => 'int',
			      'flags' => 'NOT NULL'
			      );
    }

    $parsed_columns = $source_db->parseColumns($newColumns);
    $columns = $parsed_columns['parameters'];

    $result = PHPWS_Core::getConfigFile('version', 'config.php');
    if (PEAR::isError($result))
      return $result;

    include $result;
    foreach ($version_columns as $verCol) {
      $columns[] = $verCol['name'] . ' ' . $verCol['sql'];
    }

    $sql = 'CREATE TABLE ' . $this->version_table . ' (' . implode(', ', $columns) . ')';

    $result = PHPWS_DB::query($sql);
    if (PEAR::isError($result))
      return $result;

    if (isset($parsed_columns['index']))
      return PHPWS_DB::query($parsed_columns['index']);
  }

  function loadObject(&$object){
    $data = $this->getSource();
    PHPWS_Core::plugObject($object, $data);
  }

  function isWaitingApproval(){
    $db = & new PHPWS_DB($this->version_table);
    $db->addWhere('source_id', $this->source_id);
    $db->addWhere('vr_approved', 0);
    $db->addColumn('id');
    return $db->select('one');
  }

  function authorizeCreator($module, $itemname=NULL){
    if (empty($this->source_id)) {
      return FALSE;
    }
    return Users_Permission::giveItemPermission($this->getCreator(), $module, $this->source_id, $itemname);
  }

  function flush($table, $item_id)
  {
    $version = & new Version($table);
    $db = & new PHPWS_DB($version->version_table);
    $db->addWhere('source_id', (int)$item_id);
    return $db->delete();
  }

  function kill(){
    if (empty($this->id))
      return FALSE;

    $db = & new PHPWS_DB($this->version_table);
    $db->addWhere('id', $this->id);
    return $db->delete();
  }

  function getBackupList(){
    if (empty($this->source_id))
      return FALSE;

    $db = & new PHPWS_DB($this->version_table);
    $db->addWhere('source_id', $this->source_id);
    $db->addWhere('vr_approved', 1);
    $db->addWhere('vr_current', 0);
    $db->addOrder('vr_number desc');
    $result = $db->select();

    if (empty($result))
      return NULL;

    foreach ($result as $row){
      $version = & new Version($this->source_table);
      $version->_plugInVersion($row);
      $backup_list[$row['id']] = $version;
    }
    
    return $backup_list;
  }

  function restore(){
    $db = & new PHPWS_DB($this->source_table);
    $db->addWhere('id', $this->source_id);
    $data = $this->getSource();
    $db->addValue($data);
    $result = $db->update();
    if (PEAR::isError($result))
      return $result;

    unset($this->id);
    $this->setSource($data);
    $this->setApproved(TRUE);
    $this->vr_number = $this->_getVersionNumber();
    return $this->save();
  }

  function &createApproval()
  {
    PHPWS_Core::initModClass('version', 'Approval.php');
    $approval = & new Version_Approval;
    $approval->setVersionId($this->id);
    $approval->setTable($this->source_table);
    return $approval;
  }

  /**
   *
   */
  function checkApproval()
  {
    PHPWS_Core::initModClass('notes', 'Notes.php');

    if (isset($_SESSION['Approval_Checked']) ||
	!Current_User::isLogged()) {
      return;
    }

    PHPWS_Core::initModClass('version', 'Approval.php');
    $unapproved_list = Version::getUnapprovedNotices();

    if (!empty($unapproved_list)) {
      foreach ($unapproved_list as $unapproved) {
	$result = Notes::add(_('Approval') . ': ' . $unapproved->getInfo(), _('This item needs approval.'));
      }
    }

    $_SESSION['Approval_Checked'] = 1;    
  }

  
  /**
   * Pulls a list of unapproved notices
   */
  function getUnapprovedNotices()
  {
    // Check the user's access to each module
    $module_list = PHPWS_Core::getModules(TRUE, TRUE);
    foreach ($module_list as $mod) {
      if (Current_User::isUnrestricted($mod)) {
	$final_list[] = $mod;
      }
    }

    $db = & new PHPWS_DB('version_approval');
    foreach ($final_list as $module_title) {
      $db->addWhere('module', $module_title, NULL, 'OR');
    }

    return $db->getObjects('Version_Approval');
  }

  
}

?>
