<?php
/**
 * Facilitates versioning, backup, and approvals
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */

PHPWS_Core::initModClass('version', 'Approval.php');

define('VERSION_TABLE_SUFFIX', '_version');

/* Error messages */
define('VERSION_MISSING_ID',      -1);
define('VERSION_NO_TABLE',        -2);
define('VERSION_NOT_MODULE',      -3);
define('VERSION_WRONG_SET_VAR',   -4);
define('VERSION_MISSING_SOURCE',  -5);
define('VERSION_DEFAULT_MISSING', -6);

class Version {
    public $id             = 0;
    public $source_id      = 0;
    public $source_table   = NULL;
    public $version_table  = NULL;
    public $source_data    = NULL;
    public $vr_creator     = 0;
    public $vr_editor      = 0;
    public $vr_create_date = 0;
    public $vr_edit_date   = 0;

    // number in queue of versions
    public $vr_number      = 0;

    public $vr_current     = 0;
    public $vr_approved    = 0;
    public $vr_locked      = 0;

    public $_error         = NULL;

    public function __construct($source_table, $id=NULL)
    {
        $this->source_table = $source_table;
        $this->id = (int)$id;
        $result = $this->init();
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return;
        }
    }

    public function setId($id)
    {
        $this->id = (int)$id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getVersionId()
    {
        return $this->id;
    }

    public function getCreationDate($format=false)
    {
        if ($format = true) {
            return strftime('%c', $this->vr_create_date);
        } else {
            return $this->vr_create_date;
        }
    }

    public function getEditedDate($format=false)
    {
        if ($format = true) {
            return strftime('%c', $this->vr_edit_date);
        } else {
            return $this->vr_edit_date;
        }
    }

    public function getCreator()
    {
        return $this->vr_creator;
    }

    public function getEditor()
    {
        return $this->vr_editor;
    }

    public function setApproved($approve)
    {
        $this->vr_approved = (int)$approve;
    }

    public function isApproved()
    {
        return (bool)$this->vr_approved;
    }

    public function init()
    {
        if (!PHPWS_DB::isTable($this->source_table)) {
            return PHPWS_Error::get(VERSION_NO_TABLE, 'version', 'init', $this->source_table);
        }

        $result = $this->_initVersionTable();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $this->id = 0;
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

    public function getSource($include_id=true)
    {
        if (empty($this->source_data)) {
            return NULL;
        }
        $data = $this->source_data;
        foreach ($data as $key => $value) {
            if (substr($key, 0, 1) == '_') {
                continue;
            }
            $source[$key] = $value;
        }
        if ($include_id) {
            $source['id'] = $this->source_id;
        }

        return $source;
    }

    public function setSource($source_data)
    {
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

        return true;
    }

    public function setSourceId($id)
    {
        $this->source_id = (int)$id;
    }

    public function getSourceId()
    {
        return $this->source_id;
    }


    public function save()
    {
        $source_db = new PHPWS_DB($this->source_table);
        $version_db = new PHPWS_DB($this->version_table);

        if (empty($this->source_data)) {
            return PHPWS_Error::get(VERSION_MISSING_SOURCE, 'version', 'save');
        }

        if (empty($this->id)) {
            $this->vr_creator = Current_User::getId();
            $this->vr_create_date = mktime();
        }

        $this->vr_editor = Current_User::getId();
        $this->vr_edit_date = mktime();

        if (empty($this->vr_number)) {
            $this->vr_number = $this->_getVersionNumber();
        }

        if ($this->vr_approved || empty($this->source_id)) {
            $this->vr_current = 1;
        } else {
            $this->vr_current = 0;
        }

        foreach ($this->source_data as $col_name => $col_val) {
            if ($col_name == 'id') {
                continue;
            } else {
                if (!$version_db->isTableColumn($col_name)) {
                    if($source_db->isTableColumn($col_name)) {
                        $result = $this->_copyVersionColumn($col_name);
                        if (PEAR::isError($result)) {
                            return $result;
                        }
                    } else {
                        continue;
                    }
                }
                $version_db->addValue($col_name, $col_val);
            }
        }

        $version_db->addValue('source_id',      $this->source_id);
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

        // if there is already an unapproved version, we just
        // want to update, not insert
        if (!$this->vr_approved) {
            $id = $this->isWaitingApproval();
            if ($id) {
                if (PEAR::isError($id)) {
                    return $id;
                } else {
                    $this->id = $id;
                }
            }
            $version_db->resetWhere();
        }


        if (!empty($this->id)) {
            $version_db->addWhere('id', $this->id);
            $result = $version_db->update();
            if (PEAR::isError($result)) {
                $this->_error = $result;
                return $result;
            }
            $this->cleanupVersions();
        } else {
            $result = $version_db->insert();

            if (PEAR::isError($result)) {
                $this->_error = $result;
                return $result;
            }
            $this->id = $result;
            $this->cleanupVersions();
        }
        return true;
    }


    public function cleanupVersions()
    {
        if (!$this->vr_approved) {
            return;
        }
        $saved_version =  PHPWS_Settings::get('version', 'saved_versions');

        if ($saved_version <= 0) {
            return;
        }

        $db = new PHPWS_DB($this->version_table);
        $db->addWhere('source_id', $this->source_id);
        $db->addColumn('vr_number', 'max');
        $last_number = $db->select('one');

        if (PEAR::isError($last_number)) {
            PHPWS_Error::log($last_number);
            return;
        }

        $last_number = $last_number - (int)$saved_version - 1;

        $db->resetColumns();
        $db->addWhere('vr_number', $last_number, '<=');
        return $db->delete();
    }

    public function _clearCurrents()
    {
        $db = new PHPWS_DB($this->version_table);
        $db->addWhere('source_id', $this->source_id);
        $db->addValue('vr_current', 0);
        $db->update();
    }

    /**
     * Returns the number of unapproved items
     */
    public function countUnapproved()
    {
        $version_db = new PHPWS_DB($this->version_table);
        $version_db->addWhere('vr_approved', 0);
        return $version_db->count();
    }

    public function getUnapproved($restrict=false)
    {
        $version_db = new PHPWS_DB($this->version_table);

        if ($restrict == true) {
            $version_db->addWhere('vr_creator', Current_User::getId());
        }

        $result = $version_db->addWhere('vr_approved', 0);
        $result = $version_db->select();

        if (PEAR::isError($result) || empty($result)) {
            return $result;
        }

        foreach ($result as $row) {
            $version = new Version($this->source_table);
            $version->_plugInVersion($row);
            $unapproved_list[$row['id']] = $version;
        }

        return $unapproved_list;
    }

    public function _plugInVersion($data)
    {
        if (!is_array($data)) {
            return false;
        }
        PHPWS_Core::plugObject($this, $data);
        $diff = array_diff_assoc($data, get_object_vars($this));
        $this->setSource($diff);

        return true;
    }

    public function _copyVersionColumn($col_name)
    {
        $source_db = new PHPWS_DB($this->source_table);
        $version_db = new PHPWS_DB($this->version_table);

        $col_info = $source_db->getColumnInfo($col_name, true);
        if (isset($col_info['index'])) {
            $index = true;
        } else {
            $index = false;
        }

        return $version_db->addTableColumn($col_name, $col_info['parameters'], NULL, $index);
    }

    public function _getVersionNumber()
    {
        if (empty($this->source_id)) {
            return 1;
        }
        $version_db = new PHPWS_DB($this->version_table);

        $version_db->addWhere('source_id', $this->source_id);
        $version_db->addColumn('vr_number', 'max');

        $current_version = $version_db->select('one');
        if (empty($current_version)) {
            $current_version = 1;
        }
        else {
            $current_version++;
        }

        return $current_version;
    }

    public function _initVersion()
    {
        $version_db = new PHPWS_DB($this->version_table);
        $version_db->addWhere('id', $this->id);
        $row = $version_db->select('row');
        if (PEAR::isError($row)) {
            return $row;
        }

        $this->_plugInVersion($row);
    }

    public function _initVersionTable()
    {
        $this->version_table = $this->source_table . VERSION_TABLE_SUFFIX;
        if (!PHPWS_DB::isTable($this->version_table)) {
            $result = $this->_buildVersionTable();

            if (PEAR::isError($result)) {
                return $result;
            }
        }

        return true;
    }

    public function _buildVersionTable()
    {

        $source_db = new PHPWS_DB($this->source_table);
        $allColumns = $source_db->getTableColumns(true);

        foreach ($allColumns as $editCol){
            $newColumns[] = $editCol;
            if ($editCol['name'] == 'id') {
                $newColumns[] = array('table' => $this->version_table,
                                      'name'  => 'source_id',
                                      'type'  => 'int',
                                      'flags' => 'NOT NULL'
                                      );
            }
        }

        $parsed_columns = $source_db->parseColumns($newColumns);
        $columns = $parsed_columns['parameters'];

        $filename = PHPWS_SOURCE_DIR . 'mod/version/inc/columns.php';

        if (!is_file($filename)) {
            return PHPWS_Error::get(VERSION_DEFAULT_MISSING, 'version', 'Version::_buildVersionTable');
        }

        include $filename;
        foreach ($version_columns as $verCol) {
            $columns[] = $verCol['name'] . ' ' . $verCol['sql'];
        }

        $sql = 'CREATE TABLE ' . $this->version_table . ' (' . implode(', ', $columns) . ')';
        $result = PHPWS_DB::query($sql);
        if (PEAR::isError($result)) {
            return $result;
        }

        $db = new PHPWS_DB($this->version_table);
        $db->createTableIndex('source_id');

        return true;
    }

    /**
     * Plugs the source variables into the object reference.
     * If the source data is empty
     * @returns true upon success, false is source is empty
     */
    public function loadObject($object)
    {
        $data = $this->getSource();
        if (!$data) {
            return false;
        }
        PHPWS_Core::plugObject($object, $data);
        return true;
    }

    public function isWaitingApproval()
    {
        $db = new PHPWS_DB($this->version_table);
        $db->addWhere('source_id', $this->source_id);
        $db->addWhere('vr_approved', 0);
        $db->addColumn('id');
        return $db->select('one');
    }

    public function authorizeCreator(PHPWS_Key $key)
    {
        return Users_Permission::giveItemPermission($this->getCreator(), $key);
    }

    public function flush($table, $item_id)
    {
        $version = new Version($table);
        $db = new PHPWS_DB($version->version_table);
        $db->addWhere('source_id', (int)$item_id);
        return $db->delete();
    }

    /**
     * Removes a version from the version table. It will also remove
     * the source item if this is the last version
     */
    public function delete($clean_up=true)
    {
        if (empty($this->id)) {
            return false;
        }

        $db = new PHPWS_DB($this->version_table);
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if (PEAR::isError($result)) {
            return $result;
        }

        // If this is the last version, kill the source
        if ($clean_up) {
            $db->resetWhere();
            $db->addWhere('source_id', $this->source_id);
            $db->addColumn('id', null, null, true);
            $version_count = $db->select('one');
            if (!PHPWS_Error::logIfError($version_count)) {
                if (!$version_count) {
                    $source = new PHPWS_DB($this->source_table);
                    $source->addWhere('id', $this->source_id);
                    $result = $source->delete();
                    if (PEAR::isError($result)) {
                        return $result;
                    } else {
                        return true;
                    }
                }
            }
        }

        $db->resetWhere();
        $db->addWhere('source_id', $this->source_id);
        $db->addWhere('vr_number', $this->vr_number, '>');
        return $db->reduceColumn('vr_number');
    }


    /**
     * Replaces a current version with an older version
     */
    public function restore()
    {
        $db = new PHPWS_DB($this->source_table);
        $db->addWhere('id', $this->source_id);
        $data = $this->getSource();
        $db->addValue($data);

        $result = $db->update();
        if (PEAR::isError($result)) {
            return $result;
        }

        unset($this->id);
        $this->setSource($data);
        $this->setApproved(true);
        $this->vr_number = $this->_getVersionNumber();
        return $this->save();
    }
}

?>
