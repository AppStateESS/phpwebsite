<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class PHPWS_DB {

    /**
     *
     * @var Database\DB object
     */
    private $db;
    private $initial_table;
    public $tables;
    public $order;
    public $columns;
    public $group_by;
    public $distinct;

    public function __construct($table = null)
    {
        $this->db = \Database::newDB();
        if ($table) {
            $this->initial_table = $this->db->addTable($table);
            $this->tables[$table] = $this->initial_table;
        }
    }

    /**
     * Drops a table from the database
     * @param string $table
     * @param boolean $check_existence
     * @param boolean $sequence_table Ignored because we aren't using PEAR sequence tables
     * @return boolean
     */
    public static function dropTable($table, $check_existence = true, $sequence_table = true)
    {
        if ($check_existence && !$this->db->tableExists($table)) {
            return true;
        }
        $drop_table = $this->db->addTable($table);
        $drop_table->drop();
        return true;
    }

    public static function isTable($table_name)
    {
        return $this->db->tableExists($table_name);
    }

    /**
     * Returns an array of column information from the current (first) table.
     * If fullInfo is false, only the column names are returned.
     * @param boolean $fullInfo
     * @return array
     * @throws \Exception
     */
    public function getTableColumns($fullInfo = false)
    {
        $result = $this->initial_table->getSchema();

        if (empty($result)) {
            throw new \Exception(t('Table did not return any column information'));
        }
        if ($fullInfo) {
            return $result;
        } else {
            foreach ($result as $table_info) {
                $tbls[] = $table_info['COLUMN_NAME'];
            }
            return $tbls;
        }
    }

    /**
     * Returns 'id' if id is indeed the primary key. Yes, in retrospect, not a
     * very useful function.
     * @return string|null
     */
    public function getIndex()
    {
        $index = $this->initial_table->getIndexes();

        foreach ($index as $cols) {
            foreach ($cols as $idx) {
                if ($idx['primary_key'] && $idx['column_name'] == 'id') {
                    return 'id';
                }
            }
        }
        return null;
    }

    public function getTable()
    {
        return $this->initial_table->getFullName(false);
    }

    public function addColumn($column, $max_min = null, $as = null, $count = false, $distinct = false, $coalesce = null)
    {
        if (strpos($column, '.')) {
            list($table, $column) = explode('.', $column);
            if (!isset($this->tables[$table])) {
                $column_table = $this->db->addTable($table);
            } else {
                $column_table = $this->tables[$table];
            }
        } else {
            $column_table = $this->initial_table;
        }
        $field = $column_table->getField($column);

        if ($distinct) {
            $field = new \Database\Expression("distinct($field)");
        }

        if ($count) {
            $field = new \Database\Expression("count($field)");
        } elseif ($coalesce) {
            $field = new \Database\Expression("coalesce($field)");
        }

        if ($as) {
            $field->setAlias($as);
        }
        $column_table->addField($field);
    }

    public function select($type, $sql = null)
    {
        if (empty($sql)) {
            $this->db->loadSelectStatement();
        } else {
            $this->db->loadStatement($sql);
        }

        switch ($type) {
            case 'col':
                exit('col not written');

            case 'min':
            case 'max':
            case 'one':
                $result = $this->db->fetch();
                return array_pop($result);
                break;

            case 'row':
                return $this->db->fetch();

            case 'count':
            case 'count_array':
                exit('count not written');

            case 'all':
            case 'assoc':
            default:
                return $this->db->fetchAll();
        }
        return $this->db->select();
    }

    public function setDistinct($distinct = true)
    {
        $this->db->setDistinct($distinct);
    }

    public function setLimit($limit, $offset = null)
    {
        $this->db->setLimit($limit, $offset);
    }

    public function getObjects($class_name)
    {
        $this->db->loadSelectStatement();
        while ($obj = $this->db->fetchObject($class_name)) {
            $stack[] = $obj;
        }
        return $stack;
    }

    public function lastQuery()
    {
        return $this->db->getLastQuery();
    }

    /**
     * @author Matt McNaney <mcnaney at gmail dot com>
     * @param  object $object        Object variable filled with result.
     * @param  boolean $require_where If true, require a where parameter or
     *                               have the id set
     * @return mixed                 Returns true if object properly populated and false otherwise
     *                               Returns error object if something goes wrong
     * @access public
     */
    public function loadObject($object, $require_where = true)
    {
        if (!is_object($object)) {
            throw new Exception('Non object passed to loadObject');
        }
        echo 'loadobject is not complete<br>';
        echo '<a href="xdebug:///' . __FILE__ . '@' . __LINE__ . '">' . __CLASS__ . '::' . __FUNCTION__ . '</a>';
        exit();
        if ($require_where && empty($object->id) && empty($this->where)) {
            return PHPWS_Error::get(PHPWS_DB_NO_ID, 'core', 'PHPWS_DB::loadObject', get_class($object));
        }

        if ($require_where && empty($this->where)) {
            $this->addWhere('id', $object->id);
        }

        $variables = $this->select('row');

        if (PHPWS_Error::isError($variables)) {
            return $variables;
        } elseif (empty($variables)) {
            return false;
        }

        return PHPWS_Core::plugObject($object, $variables);
    }

}

?>
