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

    /**
     * First table object created
     * @var \Database\Table
     */
    private $initial_table;
    public $tables;
    public $order;
    public $columns;
    public $group_by;
    public $distinct;

    /**
     * An array of where conditional objects
     * @var array
     */
    private $where;

    public function __construct($table = null)
    {
        $this->db = \Database::newDB();
        if (!empty($table)) {
            $this->addTable($table);
        }
    }

    /**
     * If the table hasn't been added yet, this method:
     * 1) Creates a table object, adding it to the DB object
     * 2) If not yet set, copy the table name to the initial_table variable.
     * 3) Add the table name to the tables variable.
     *
     * If it has already been added, pullTable returns it.
     *
     * @param string $table_name
     * @return \Database\Table
     */
    public function addTable($table_name)
    {
        if ($this->db->inTableStack($table_name)) {
            return $this->db->pullTable($table_name);
        } else {
            $tbl_obj = $this->db->addTable($table_name);
            if (empty($this->initial_table)) {
                $this->initial_table = $tbl_obj;
            }
            $this->tables[] = $table_name;
            return $tbl_obj;
        }
    }

    /**
     * Joins two tables.
     * @param string $join_type The type of join to use
     * @param string $join_from The left table name
     * @param string $join_to The right table name
     * @param string $join_on_1 What column to join on from the left table
     * @param string $join_on_2 What column to join on from the right table
     * @param string $ignore_tables
     */
    public function addJoin($join_type, $join_from, $join_to, $join_on_1 = null, $join_on_2 = null, $ignore_tables = false)
    {
        unset($ignore_tables);
        $left_table = $this->addTable($join_from);
        $right_table = $this->addTable($join_to);

        if (empty($join_on_1)) {
            if (!empty($join_on_2)) {
                throw new \Exception('Right join on added without left join on');
            }
            $this->db->join($left_table, $right_table, $join_type);
        } else {
            $left_field = $left_table->addField($join_on_1);
            $right_field = $right_table->addField($join_on_2);
            $this->db->join($left_field, $right_field, $join_type);
        }
    }

    /**
     * Receive a column name and determines if it is accessing the first table
     * or a different table. The result is an added Table object.
     *
     * Example:
     * $column_name = 'foo.bar';
     *
     * list($table, $column) = $db->deriveTable($column_name);
     * // returns the 'foo' table object and the 'bar' field
     * @param string $column
     * @return array An array with a \Database\Table object and a column name string
     */
    public function deriveTableAndColumn($column)
    {
        // The column was entered as a table/column pair.
        if (preg_match('/\w+\./', $column)) {
            list($table_name, $column_name) = explode('.', $column);
            if ($this->db->inTableStack($table_name)) {
                $data['table'] = $this->db->pullTable($table_name);
            } else {
                $data['table'] = $this->addTable($table_name);
            }
            $data['column_name'] = $column_name;
        } else {
            $data['table'] = $this->initial_table;
            $data['column_name'] = $column;
        }
        return $data;
    }

    /**
     * Order query should be operated on or returned.
     * @param string $order
     */
    public function addOrder($order)
    {
        /* @var $table \Database\Table */
        $table = null;

        /* @var $column_name string */
        $column_name = null;
        extract($this->deriveTableAndColumn($order));

        // if a space is seen, the direction is included
        if (preg_match('/\w+\s\w/', $column_name)) {
            list($new_column_name, $direction) = explode(' ', $column_name);
        } else {
            $new_column_name = &$column_name;
            $direction = 'ASD';
        }
        $table->addOrderBy($new_column_name, $direction);
    }

    /**
     * Adds a where conditional to the first table.
     *
     * @param string $column
     * @param string $value
     * @param string $operator
     * @param string $conj
     * @param string $group
     * @param string $join
     */
    public function addWhere($column, $value = null, $operator = null, $conj = null, $group = null, $join = false)
    {
        /* @var $table \Database\Table */
        $table = null;

        /* @var $column_name string */
        $column_name = null;
        extract($this->deriveTableAndColumn($column));
        $this->where[] = $table->addWhere($column_name, $value, $operator, $conj);
        if (!empty($group) || !empty($join)) {
            trigger_error('Backward\PHPWS_DB::addWhere needs finishing - missing parameters group and join',
                    E_USER_ERROR);
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
            if (!in_array($table, $this->tables)) {
                $column_table = $this->db->addTable($table);
            } else {
                $column_table = $this->db->pullTable($table);
            }
        } else {
            $column_table = $this->initial_table;
        }
        $field = $column_table->getField($column);

        if (!empty($max_min)) {
            $max_min = strtoupper($max_min);
            if ($max_min == 'MAX' || $max_min == 'MIN') {
                $field = new \Database\Expression("$max_min($field)");
            }
        } elseif ($distinct) {
            $field = new \Database\Expression("distinct($field)");
        } elseif ($count) {
            $field = new \Database\Expression("count($field)");
        } elseif ($coalesce) {
            $field = new \Database\Expression("coalesce($field)");
        }

        if ($as) {
            $field->setAlias($as);
        }
        $column_table->addField($field);
    }

    public function select($type = null, $sql = null)
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
     * @param  boolean $require_where If true, require a where parameter or have the id set
     * @return mixed                 Returns true if object properly populated and false otherwise
     *                               Returns error object if something goes wrong
     * @access public
     * @throw \Exception
     */
    public function loadObject($object, $require_where = true)
    {
        if (!is_object($object)) {
            throw new Exception('Non object passed to loadObject');
        }

        // If a where conditional is required and the object doesn't have an id
        // and the database doesn't have a where conditional, we throw an exception.
        if ($require_where && empty($object->id) && empty($this->where)) {
            throw new \Exception(t('loadObject expected the object to have an id or where clause'),
            PHPWS_DB_NO_ID);
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
