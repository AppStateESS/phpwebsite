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
    public $db;

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
     * Associative array of con
     * @var array
     */
    public $group_conjuctions;

    /**
     * Array of groups that are subsets of other groups.
     * @var array
     */
    public $group_in_stack;

    /**
     * Associative array of where objects created when a group name is passed to
     * addWhere.
     * @var array
     */
    public $group_stack;

    /**
     * The current query column that will be used to sort the results of
     * a select or getObjects
     * @var string
     */
    public $indexby;

    /**
     * If true, an indexby result with multiple value per key will return all
     * values in an array instead of allowing single results to occupy an array
     * cell to themselves.
     * @see self::setIndexBy
     * @var boolean
     */
    public $force_array;

    /**
     * If true, an indexby result will overwrite the duplicate values in occupying
     * the same key in the resultant array.
     * @var boolean
     */
    public $ignore_dups;

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
     * Returns the Table object put in the initial_table variable.
     * @return \Database\Table
     */
    public function getInitialTable()
    {
        return $this->initial_table;
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

    public function loadClass($module, $file)
    {
        PHPWS_Core::initModClass($module, $file);
        // not sure needed yet
    }

    /**
     * Receive a column name and determines if it is accessing the first table
     * or a different table. The result is an added Table object.
     *
     * <code>
     * Example:<br>
     * $column_name = 'foo.bar';<br>
     * <br>
     * <pre>
     * $result = $db->deriveTableAndColumn($column_name);<br>
     * </pre>
     * Result is an array<br>
     * <pre>
     * key         | value
     * ------------|------------------------
     * table       | (\Database\Table) 'foo'
     * column_name | (string) 'bar'
     * </pre>
     * </code>
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
            $direction = 'ASC';
        }
        $table->addOrderBy($new_column_name, $direction);
    }

    /**
     * Embeds $sub group into the $main group
     * @param string $sub
     * @param string $main
     */
    public function groupIn($sub, $main)
    {
        // Check if passed group names were previously created.
        if (!isset($this->group_stack[$sub]) && !isset($this->group_stack[$main])) {
            throw new Exception("Unknown group '$sub' or '$main'");
        }

        // Check if main group is already a child of the sub group
        if (isset($this->group_in_stack[$sub]) && in_array($main,
                        $this->group_in_stack[$sub])) {
            throw new Exception("$main group is currently a child group of $sub.");
        }

        if (isset($this->group_in_stack[$sub])) {
            $this->group_in_stack[$main][] = & $this->group_in_stack[$sub];
        } else {
            $this->group_in_stack[$main][] = $sub;
        }
    }

    /**
     * Sets the result array key to the value of the indexby column.
     * If you expect multiple results per index, you may wish to set
     * force_array to true. This will ensure the results per line are always
     * an array of results.
     *
     * For example, if you group by a foreign key you may get 2 results on one
     * index a only one on the other. Here is an example array were that the result:
     *
     * 'cat' => 0 => 'Whiskers'
     *          1 => 'Muffin'
     * 'dog' => 'Rover'
     *
     * If you knew that repeats were possible and set force_array to true, this
     * would be the result instead:
     *
     * 'cat' => 0 => 'Whiskers'
     *          1 => 'Muffin'
     * 'dog' => 0 => 'Rover'
     *
     */
    public function setIndexBy($indexby, $force_array = false, $ignore_dups = false)
    {
        if (strstr($indexby, '.')) {
            $indexby = substr($indexby, strpos($indexby, '.') + 1);
        }
        $this->indexby = $indexby;
        $this->force_array = (bool) $force_array;
        $this->ignore_dups = (bool) $ignore_dups;
    }

    public function setGroupConj($group, $conj)
    {
        $conj = strtoupper($conj);
        if (empty($conj) || ($conj != 'OR' && $conj != 'AND')) {
            return false;
        }

        $this->where[$group]['conj'] = $conj;
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
        $where_obj = $table->addWhere($column_name, $value, $operator, $conj);
        $this->where[] = $where_obj;
        if (!empty($group)) {
            $this->group_stack[$group][] = $where_obj;
        }
        if (!empty($join)) {
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

    /**
     * @param string $column_name
     * @return boolean TRUE is column has been added to the initial table.
     */
    public function columnIsAdded($column_name)
    {
        return $this->initial_table->fieldIsSet($column_name);
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

    public function allowed()
    {

    }

    /**
     * Adds a group by clause to the query string.
     * @param string $group_by
     * @return boolean
     */
    public function addGroupBy($group_by)
    {
        if (PHPWS_DB::allowed($group_by)) {
            // no dot, use initial_table to get field targeted by group_by
            if (!strpos($group_by, '.')) {
                $field = $this->initial_table->getField($group_by);
            } else {
                $data = $this->deriveTableAndColumn($group_by);
                $field = $data['table']->getField();
            }
            $this->db->setGroupBy($field);
        }
        return true;
    }

    /**
     * Takes all the where objects in the group_stack variable and puts them into
     * groups.
     * @todo group in needs to work with this.
     */
    private function groupWhereObjects()
    {
        //no stack, we are done
        if (empty($this->group_stack)) {
            return;
        }

        foreach ($this->group_stack as $key => $grp_array) {
            $group_object_array[$key] = $this->db->groupWhere($grp_array);
        }
    }

    public function select($type = null, $sql = null)
    {
        /**
         * If true, then return the select(col) with the column keyed
         * @var $index_column_name boolean
         */
        $index_column_name = false;

        $this->groupWhereObjects();
        if (empty($sql)) {
            $this->db->loadSelectStatement();
        } else {
            $this->db->loadStatement($sql);
        }

        switch ($type) {
            case 'min':
            case 'max':
            case 'one':
                $result = $this->db->fetchRow();
                return array_pop($result);
                break;

            case 'row':
                return $this->db->fetchRow();

            case 'count':
            case 'count_array':
                exit('count not written');

            case 'col':
                /**
                 * If not indexing, can use fetchColumn and be done with it.
                 */
                if (empty($this->indexby)) {
                    while ($col = $this->db->fetchColumn()) {
                        $result[] = $col;
                    }
                    return $result;
                }
            case 'all':
            case 'assoc':
            default:
                $result = $this->db->select();
        }
        if (empty($result)) {
            return null;
        }
        if (!empty($this->indexby)) {
            /**
             * A sorted version of the select indexby result
             * @var $sorted_result array
             */
            $sorted_result = array();

            /**
             * The other column besides the index by column used in a 'col'
             * select.
             * @var $other_column string
             */
            $other_column = null;

            foreach ($result as $row) {
                /**
                 * In the case of 'col' type select, we need to get the one
                 * other column that is NOT the indexby column.
                 */
                if (empty($other_column) && $type == 'col') {
                    $copy_row = $row;
                    unset($copy_row[$this->indexby]);
                    $row_keys = array_keys($copy_row);
                    $other_column = array_shift($row_keys);
                }
                /* @var $index_by string Key used to index associative result array */
                if ($this->force_array) {
                    $sorted_result[$index_by][] = $row;
                } else {
                    $index_by = $row[$this->indexby];
                    if (isset($sorted_result[$index_by])) {
                        if (is_array($sorted_result[$index_by])) {
                            $sorted_result[$index_by][] = ($type == 'col') ? $row[$other_column] : $row;
                        } else {
                            $hold = $sorted_result[$index_by];
                            unset($sorted_result[$index_by]);
                            $sorted_result[$index_by][] = $hold;
                            $sorted_result[$index_by][] = ($type == 'col') ? $row[$other_column] : $row;
                            unset($hold);
                        }
                    } else {
                        $sorted_result[$index_by] = ($type == 'col') ? $row[$other_column] : $row;
                    }
                }
            }
            return $sorted_result;
        } else {
            return $result;
        }
    }

    public function setDistinct($distinct = true)
    {
        $this->db->setDistinct($distinct);
    }

    public function setLimit($limit, $offset = null)
    {
        $this->db->setLimit($limit, $offset);
    }

    /**
     * Returns an array of instatiated $class_name objects.
     * @param string $class_name
     * @return array|null
     */
    public function getObjects($class_name)
    {
        $stack = null;
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

    /**
     * Deprecated
     */
    public static function disconnect()
    {
        \Database\DB::disconnect();
    }

}

?>
