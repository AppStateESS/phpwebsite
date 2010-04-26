<?php
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package DB2
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

require_once 'MDB2.php';
require_once 'PEAR/Exception.php';

require_once PHPWS_SOURCE_DIR . 'core/class/Data.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Alias.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Resource.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Expression.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Interfaces.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Object.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_SubSelect.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Table.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Group.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Conditional.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Conditional_Group.php';
require_once PHPWS_SOURCE_DIR . 'core/class/DB2/DB2_Join.php';

/**
 * Types of returns for select
 */
define('DB2_ONE', 1);
define('DB2_ROW', 2);
define('DB2_COLUMN', 3);
define('DB2_ALL', 4);

define('DB2_SELECT', 1);
define('DB2_UPDATE', 2);
define('DB2_DELETE', 3);

class DB2 extends Data {
    /**
     * Array of table objects used for the query
     * @var array
     * @access private
     */
    private $tables = array();

    /**
     * The MDB2 connection object.
     * @var object
     * @access public
     */
    public $mdb2 = null;

    /**
     * Current DSN for database connection
     * @var string
     * @access private
     */
    private $dsn = null;

    /**
     * Current table prefix, if any
     * @var string
     * @access private
     */
    private $tbl_prefix = null;

    /**
     * Contains any PEAR error objects from MDB2
     * @var object
     * @access private
     */
    private $error = null;

    /**
     * The query sent to query() or exec()
     * @var string
     * @access public
     */
    public $query = null;

    /**
     * An array of where groupings within the query
     * @var array
     * @access private
     */
    private $where_group_stack = null;


    /**
     * An array of joined tables
     * @var array
     * @access private
     */
    private $join_tables = null;


    /**
     * Holds global table information
     * @var array
     * @access private
     */
    private $table_info = null;

    /**
     * Holds group by data
     * @var array
     */
    private $group_by = null;

    /**
     * An array of expression objects
     * @var array
     */
    private $expressions = null;

    /**
     * DB2_Field object you want a select result indexed by
     * @see DB2::setIndexBy
     * @var object
     */
    private $index_by = null;

    /**
     * The return type of the selection function.
     * Options are one, row, col, all
     * @var boolean
     */
    private $select_type = DB2_ALL;

    /**
     * Name of the object class the user wishes returned instead of an array
     * @var string
     */
    private $class_name = null;


    /**
     * Number of rows to limit operations
     * @var integer
     */
    private $limit = null;

    /**
     * Number of rows to offset on the limit
     * @var integer
     */
    private $offset = null;

    /**
     * Resource created from last query/exec
     * @var unknown_type
     */
    public $resource = null;

    /**
     * Rows affected by last query or execution
     * @var integer
     */
    private $rows_affected = 0;

    /**
     * Array of objects needing insertion or updating.
     * @var array
     */
    private $object_list = null;

    private $sub_selects = null;

    /**
     * DBMS specific settings called when a table is created.
     * @var array
     */
    private $table_options = array();

    private $database_options = array();


    /**
     * The dsn is expected to in PEAR DB format.
     * @link http://pear.php.net/manual/en/package.database.mdb2.intro-dsn.php
     * @param string $dsn : DSN string to connect to database
     * @param string $tbl_prefix : Table prefix, if any
     * @access public
     */
    public function __construct($dsn=null, $tbl_prefix=null)
    {
        if (!isset($GLOBALS['mdb2_table_info'])) {
            $GLOBALS['mdb2_table_info'] = array();
            $this->table_info = & $GLOBALS['mdb2_table_info'];
        }

        if (empty($dsn)) {
            if (empty($this->dsn)) {
                if (PHPWS_Core::isBranch()) {
                    try {
                        $this->loadBranchDSN();
                    } catch (PEAR_Exception $e) {
                        throw new PEAR_Exception(dgettext('core', 'Could not load branch DSN.'), $e);
                    }
                } else {
                    try {
                        $this->loadDSN();
                    } catch (PEAR_Exception $e) {
                        throw new PEAR_Exception(dgettext('core', 'Could not load hub DSN.'), $e);
                    }
                }
            } else {
                $dsn        = & $this->dsn;
                $tbl_prefix = & $this->tbl_prefix;
            }
        } else {
            $this->dsn        = $dsn;
            $this->tbl_prefix = $tbl_prefix;
        }

        try {
            $this->connect();
        } catch (PEAR_Exception $e) {
            return $e;
        }
        $this->loadOptions();
        $this->logDB(sprintf(dgettext('core', 'Connected to database "%s"'), $this->mdb2->database_name));
    }

    private function loadOptions()
    {
        include PHPWS_SOURCE_DIR . 'core/conf/DB2.php';
        /* looking for a variable named after the current syntax in DB2 config
         * file
         * e.g. MySQL looks for $mysql array
         */

        if (isset($all['table'])) {
            $this->table_options = $all['table'];
        }

        if (isset($all['database'])) {
            $this->database_options = $all['database'];
        }

        if (isset(${$this->mdb2->dbsyntax}['table'])) {
            $this->table_options = array_merge($this->table_options, ${$this->mdb2->dbsyntax}['table']);
        }

        if (isset(${$this->mdb2->dbsyntax}['database'])) {
            $this->database_options = array_merge($this->database_options, ${$this->mdb2->dbsyntax}['database']);
        }
    }

    /**
     * Returns the table prefix
     * @return string
     * @access public
     */
    public function getTablePrefix()
    {
        return $this->tbl_prefix;
    }

    /**
     *
     * @param string $dsn
     * @access public
     * @return void
     */
    public function setDSN($dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * Sets the current select type.
     * @param integer $select_type
     * @return void
     */
    public function setSelectType($select_type) {
        $select_type = (int)$select_type;
        if ($select_type == DB2_ONE ||
        $select_type == DB2_ROW ||
        $select_type == DB2_COLUMN ||
        $select_type == DB2_ALL) {
            $this->select_type = $select_type;
        } else {
            throw new PEAR_Exception(dgettext('core', 'Unknown select type'));
        }
    }

    /**
     * Creates new database
     * @param string $database_name
     * @return void
     */
    public function createDatabase($database_name)
    {
        if (!$this->allowedIdentifier($database_name)) {
            throw new PEAR_Exception(dgettext('core', 'Improper database name'));
        }

        $this->mdb2->loadModule('Manager');
        $result = $this->mdb2->createDatabase($database_name);
        if ($this->pearError($result)) {
            throw new PEAR_Exception(dgettext('core', 'Unable to create new database'));
        }
    }

    /**
     * Creates a new table in the current database. Uses MDB2's createTable function.
     * See http://pear.php.net/manual/en/package.database.mdb2.intro-manager-module.php
     * @param string $table_name Name of new table
     * @param array $fields Column parameters for new table
     * @return object DB2_Table object if successful, exception thrown otherwise.
     */
    public function createTable($table_name, $fields)
    {
        if (!$this->allowed($table_name)) {
            throw new PEAR_Exception(dgettext('core', 'Improper table name'));
        }

        $this->mdb2->loadModule('Manager');

        $result = $this->mdb2->createTable($table_name, $fields, $this->table_options);
        if ($this->pearError($result)) {
            throw new PEAR_Exception($result->getMessage());
        }
        return $this->getTable($table_name);
    }


    /**
     * Alters a table's structure. Uses MDB2's alterTable function.
     * See http://pear.php.net/manual/en/package.database.mdb2.intro-manager-module.php
     * From the above page (with some editing):
     * array( 'name' => 'userlist',
     *        'add' => array( 'quota' => array( 'type' => 'integer', 'unsigned' => 1 ) ),
     *        'remove' => array( 'file_limit' => array(), 'time_limit' => array() ),
     *        'change' => array( 'name' => array( 'length' => '20', 'definition' => array( 'type' => 'text', 'length' => 20 ) ) ),
     *        'rename' => array( 'sex' => array( 'name' => 'gender', 'definition' => array( 'type' => 'text', 'length' => 1, 'default' => 'M' ) ) ) );
     *
     * Name, add, remove, change, and rename are the keys of the parent array.
     * They tell the method what action to perform. The array under them uses the key as the column name.
     * The value is another array containing column definitions (or not as in remove).
     *
     *
     * @param string $table_name Name of table to alter
     * @param array $definition Array with commands and parameters
     * @param boolean $test If true, the alteration WILL NOT OCCUR.
     *                      Instead, true or false is returned on the feasibility of the alteration.
     * @return boolean True if is/will be successful. Exception thrown otherwise.
     */
    public function alterTable($table_name, $definition, $test=false)
    {
        $this->mdb2->loadModule('Manager');
        if (!$this->isTable($table_name)) {
            throw new PEAR_Exception(dgettext('core', 'Table does not exist'));
        }

        $result = $this->mdb2->alterTable($table_name, $definition, $test);
        if ($this->pearError($result)) {
            throw new PEAR_Exception($result->getMessage());
        }
        return $result;
    }

    /**
     * Renames a table in the database. This is a shortcut solution to alterTable.
     * @param string $old_name Name of the table to change
     * @param string $new_name Name to change the table to.
     * @return boolean True is successful, thrown exception otherwise.
     */
    public function renameTable($old_name, $new_name)
    {
        if (!$this->isTable($old_name)) {
            throw new PEAR_Exception(dgettext('core', 'Table does not exist'));
        }

        if (!$this->allowed($new_name)) {
            throw new PEAR_Exception(dgettext('core', 'Improper new table name'));
        }
        return $this->alterTable($old_name, array('name'=>$new_name));
    }

    /**
     * Removes a table from the database.
     * @param string $table_name Name of table to remove
     * @return boolean True is successful, thrown exception otherwise.
     */
    public function dropTable($table_name)
    {
        if (!$this->isTable($table_name)) {
            throw new PEAR_Exception(dgettext('core', 'Table does not exist'));
        }

        $this->mdb2->loadModule('Manager');
        $result = $this->mdb2->dropTable($table_name);
        if ($this->pearError($result)) {
            throw new PEAR_Exception($result->getMessage());
        }
        return $result;
    }

    /**
     * Checks a string against a list of restricted database words.
     * Used internally.
     * @param string $name Word to verify.
     * @return boolean True if allowed, false if restricted.
     */
    public function allowedIdentifier($name)
    {
        static $reserved = array('add', 'all', 'alter', 'analyze', 'and', 'any', 'array', 'as', 'asc', 'asensitive',
'asymmetric', 'authorization', 'before', 'between', 'bigint', 'binary', 'blob', 'both',
'by', 'call', 'cascade', 'case', 'change', 'char', 'character', 'check', 'collate',
'column', 'condition', 'connection', 'constraint', 'continue', 'convert', 'create',
'cross', 'current_date', 'current_role', 'current_time', 'current_timestamp',
'current_user', 'cursor', 'database', 'databases', 'day_hour', 'day_microsecond',
'day_minute', 'day_second', 'dec', 'decimal', 'declare', 'default', 'delayed', 'delete',
'desc', 'describe', 'deterministic', 'distinct', 'distinctrow', 'div', 'do', 'double',
'drop', 'dual', 'each', 'else', 'elseif', 'enclosed', 'end', 'escaped', 'except',
'exists', 'exit', 'explain', 'false', 'fetch', 'float', 'float4', 'float8', 'for',
'force', 'foreign', 'freeze', 'from', 'fulltext', 'function', 'goto', 'grant', 'group',
'having', 'high_priority', 'hour_microsecond', 'hour_minute', 'hour_second', 'if',
'ignore', 'ilike', 'in', 'index', 'infile', 'initially', 'inner', 'inout', 'insensitive',
'insert', 'int', 'int1', 'int2', 'int3', 'int4', 'int8', 'integer', 'intersect',
'interval', 'into', 'is', 'isnull', 'iterate', 'join', 'key', 'keys', 'kill', 'label',
'leading', 'leave', 'left', 'like', 'limit', 'lines', 'load', 'localtime',
'localtimestamp', 'lock', 'long', 'longblob', 'longtext', 'loop', 'low_priority', 'match',
'mediumblob', 'mediumint', 'mediumtext', 'middleint', 'minute_microsecond',
'minute_second', 'mod', 'modifies', 'natural', 'new', 'no_write_to_binlog', 'not', 'null',
'numeric', 'off', 'offset', 'old', 'on', 'only', 'optimize', 'option', 'optionally', 'or',
'order', 'out', 'outer', 'outfile', 'overlaps', 'placing', 'precision', 'primary',
'procedure', 'purge', 'read', 'reads', 'real', 'references', 'regexp', 'release',
'rename', 'repeat', 'replace', 'require', 'restrict', 'return', 'returning', 'revoke',
'right', 'rlike', 'schema', 'schemas', 'second_microsecond', 'select', 'sensitive',
'separator', 'session_user', 'set', 'show', 'similiar', 'smallint', 'some', 'soname',
'spatial', 'specific', 'sql', 'sql_big_result', 'sql_calc_found_rows', 'sql_small_result',
'sqlexception', 'sqlstate', 'sqlwarning', 'ssl', 'starting', 'straight_join', 'symmetric',
'table', 'terminated', 'then', 'tinyblob', 'tinyint', 'tinytext', 'to', 'trailing',
'trigger', 'true', 'undo', 'union', 'unique', 'unlock', 'unsigned', 'update', 'upgrade',
'usage', 'use', 'user', 'using', 'utc_date', 'utc_time', 'utc_timestamp', 'values',
'varbinary', 'varchar', 'varcharacter', 'varying', 'verbose', 'when', 'where', 'while',
'with', 'write', 'xor', 'year_month', 'zerofill');
        return !in_array(strtolower($name), $reserved);
    }

    /**
     * Sets the database's table prefix. These prefixes are added to the beginning of every
     * table name.
     * @access public
     * @param string $tbl_prefix Prefix added to table names.
     * @return void
     */
    public function setTablePrefix($tbl_prefix)
    {
        if (preg_match('/\W/', $tbl_prefix)) {
            trigger_error(dgettext('core', 'Table prefix contains illegal characters'), E_ERROR);
        }
        $this->tbl_prefix = $tbl_prefix;
    }

    /**
     * Formally named loadHubDSN but that designation has been removed.
     * This function will load the currently defined DSN in to the object.
     * The define comes from the local config/core/config.php whether from hub
     * or branch. A different DSN and table prefix may be set via the parameters.
     *
     * @param string $dsn : DSN formated string
     * @param string $table_prefix : Prefix to prefix to all table names.
     * @access public
     * @return void
     */
    public function loadDSN($dsn=null, $table_prefix=null)
    {
        static $dsn          = null;
        static $table_prefix = null;

        if (empty($dsn)) {
            $dsn = PHPWS_DSN;
            if (defined('PHPWS_TABLE_PREFIX')) {
                $table_prefix = PHPWS_TABLE_PREFIX;
            }

            if (empty($dsn)) {
                throw new PEAR_Exception(dgettext('core', 'DSN not set database file.'));
            }
        }

        $this->setDSN($dsn);
        $this->setTablePrefix($table_prefix);
    }


    /**
     * Adds an expression to the database.
     * @see DB2_Expression
     * @param string $expression Expression to add
     * @param string $alias Alias of expression (e.g. some_function(column) as foo)
     * @return DB2_Expression
     */
    public function addExpression($expression, $alias=null) {
        $expression = $this->getExpression($expression, $alias);
        $this->expressions[] = $expression;
        return $expression;
    }

    /**
     * Returns a DB2_Expression object
     * @param string $expression
     * @return DB2_Expression;
     */
    public function getExpression($expression, $alias=null)
    {
        return new DB2_Expression($expression, $alias);
    }

    /**
     * Makes a connection to a branch database.
     * Exception thrown on failure
     *
     * @param object $branch : Branch object
     * @return void
     */
    public function loadBranchDSN(Branch $branch)
    {
        $this->setDSN($branch->dsn);
        $this->setTablePrefix($branch->prefix);
    }

    /**
     * Uses the current dsn to make a database connection. Exception thrown on error.
     * Default seqcol_name is 'sequence', id is used for backward compatibility
     * @return void
     */
    private function connect()
    {
        $this->mdb2 = MDB2::singleton($this->dsn, $this->database_options);

        if ($this->pearError($this->mdb2)) {
            $this->error = $this->mdb2;
            if (CLEAR_DSN) {
                $this->mdb2->userinfo = str_replace($this->dsn, '-- DSN removed --', $this->mdb2->userinfo);
            }
            throw new PEAR_Exception(dgettext('core', 'Could not connect to the database.'));
        }

        // there are two other fetch modes - neither really more helpful
        $this->mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);
    }

    /**
     * Appends the db.log file with database queries
     * @param string $sql SQL query string.
     * @return void
     */
    private function logDB($sql)
    {
        if (!defined('LOG_DB') || LOG_DB != true) {
            return;
        }

        PHPWS_Core::log($sql, 'db.log');
    }

    /**
     * Adds a table object to the table stack
     * @param string table : Table name
     * @param string as : Table designation/nickname
     * @param boolean show_all_fields : If true, use table.* in a select query.
     *                                  False, ignore table in result.
     * @return object : reference to the object in the tables stack
     */
    public function addTable($table, $alias=null, $show_all_fields=true)
    {
        $index = !empty($alias) ? $alias : $table;

        if (isset($this->tables[$index])) {
            throw new PEAR_Exception(dgettext('core', 'Duplicate table added'));
        }
        $table = $this->getTable($table, $alias);

        $table->showAllFields($show_all_fields);
        $this->tables[$index] = $table;
        return $table;
    }

    /**
     * Indicates if the current table name is already in the table stack.
     * The stack is indexed by aliases, so the same table may be in the stack
     * multiple times. To see if the table exists in the current database, use
     * tableExists.
     * @param $table
     * @return boolean
     */
    public function isTable($table)
    {
        return isset($this->tables[$table]);
    }

    /**
     * Pulls the currently named table from the table stack
     * @param string $table_name
     * @return DB2_Table
     */
    public function pullTable($table_name)
    {
        if ($this->isTable($table_name)) {
            return $this->tables[$table_name];
        }
    }

    /**
     * Calls the factory method to create a new table object based on the
     * current database OS.
     * @param string $table  Name of table
     * @param string $alias  Alias representation for table in queries
     * @return object A table object with this class as its parent
     */
    public function getTable($table, $alias=null)
    {
        return DB2_Table::factory($table, $alias, $this);
    }

    /**
     * Returns the first table object on the DB2 table stack.
     * @return DB2_Table
     */
    public function getFirstTable()
    {
        return current($this->tables);
    }

    /**
     * Sets the group by query for a select query
     *
     * @param mixed $fields : A single or array of DB2_Field or DB2_Function objects
     * @param integer $group_type : A defined group by type
     * @return unknown_type
     */
    public function setGroupBy($fields, $group_type=null)
    {
        /**
         * For information on the fields parameter
         * @see DB2_Group::__construct
         */

        /**
         * Defines for group type are at the top of DB2_Group
         * @see DB2_Group
         */
        $this->group_by = DB2_Group::factory($this->mdb2->dbsyntax, $fields, $group_type);
        return $this->group_by;
    }

    /**
     * Returns the table stack from the DB2 object
     * @return array
     */
    public function getAllTables()
    {
        return $this->tables;
    }


    /**
     * Limits a query's results. This function uses MDB2's setLimit function. As a result of this,
     * printing the query WILL NOT SHOW THE LIMIT INFORMATION.
     * @param integer $limit Number of rows to return to act upon
     * @param integer $offset Number of rows to skip before starting the limit count.
     * @return void
     */
    public function setLimit($limit, $offset=null)
    {
        $limit = (int)$limit;
        $offset = (int)$offset;
        if ($limit) {
            if ($offset) {
                $this->mdb2->setLimit($limit, $offset);
            } else {
                $this->mdb2->setLimit($limit);
            }
        }
    }


    /**
     * Returns the group by object. Expected use is for string output.
     * @return DB2_Group object
     */
    private function getGroupBy()
    {
        if (empty($this->group_by)) {
            return null;
        }
        return $this->group_by;
    }


    /**
     * Joins two resources together.
     * @param mixed $left Will be a table, subselect, or field object
     * @param mixed $right Same as left
     * @param string $type The type of join to be performed.
     * @param string $operator The comparison operator
     * @return unknown_type
     */
    public function join($left, $right, $type=null, $operator=null)
    {
        $type = $type ? $type : 'inner';
        $operator = $operator ? $operator : '=';
        $jt = new DB2_Join($left, $right, $type, $operator);
        $this->join_tables[] = $jt;
        return $jt;
    }

    /**
     * Checks a value against a number of reserved words, a regular expression, and a variable type.
     * Returns false if the string is not allowed, true otherwise.
     * @param string $value - String of table, field, etc. we are checking.
     * @access public
     */
    public function allowed($value)
    {
        if (!is_string($value)) {
            return false;
        }

        if (preg_match('/[^\w\*\.]/', $value)) {
            return false;
        }

        return DB2::allowedIdentifier($value);
    }

    /**
     * Accepts a table field for indexing a select result.
     * For example:
     * $id_field = $db2_table->getField('id');
     * $db2->setIndexBy($id_field);
     * $result = $db2->select();
     *
     * Normally you would receive array(0=>array('id'=>4, 'name'=>'Ted'))
     * with an index set you would instead get array(4=>array('id'=>4, 'name'=>'Ted'))
     *
     * @param DB2_Field $field
     * @return void
     */
    public function setIndexBy(DB2_Field $field)
    {
        $this->index_by = $field;
    }



    /**
     * Runs insert on all tables in the database object
     * @return void
     */
    public function insert()
    {
        if (empty($this->tables)) {
            throw new PEAR_Exception(dgettext('core', 'No tables found'));
        }

        foreach ($this->tables as $tbl) {
            $result = $tbl->insert();
        }
        $this->rows_affected += $result;
        return $this->rows_affected;
    }


    /**
     * Updates values in one or more tables. The DB2 class insert method queries
     * multiple tables and runs multiple queries. This update function only gets
     * multiple table results BUT only performs one query. This allows table to table
     * column copying.
     *
     * @return void
     */
    public function update()
    {
        $this->updateQuery();
        $this->execute($this->query);
        return $this->rows_affected;
    }

    public function delete()
    {
        $this->deleteQuery();
        $this->execute($this->query);
        return $this->rows_affected;
    }

    /**
     * Unlike other execution queries, deleteQuery accepts an array parameter.
     * The array should contain resources the dev wants to delete.
     * For example:
     *
     * $db2 = new DB2;
     * $t1 = $db2->addTable('t1', 'a1');
     * $t2 = $db2->addTable('t2', 'a2');
     *
     * $db2->join($t1, $t2, 'cross');
     * $t1->addWhere('id', $t2->getField('id'));
     * echo $db2->deleteQuery(array($t1, $t2));
     * // echoes DELETE a1, a2 FROM t1 AS a1 CROSS JOIN t2 AS a2 WHERE a1.id = (a2.id)
     *
     * This parameter is optional and is only used with joins.
     *
     * @return unknown_type
     */
    public function deleteQuery($include_on_join=null)
    {
        $query[] = 'DELETE';
        $data = $this->pullResourceData(DB2_DELETE);
        extract($data);

        if (!empty($include_on_join)) {
            foreach ($include_on_join as $resource) {
                if (is_subclass_of($resource, 'DB2_Resource')) {
                    $delete_resources[] = $resource->hasAlias() ? $resource->getAlias() : $resource->getQuery();
                }
            }
            $query[] = implode(', ', $delete_resources);
        }

        $query[] = 'FROM';
        // from tables, joins, and subselects
        $query[] = $resources;

        if (!empty($where)) {
            $query[] = $where;
        }

        // sorting
        if (!empty($order)) {
            $query[] = $order;
        }
        $this->query = implode(' ', $query);
        return $this->query;
    }

    public function updateQuery()
    {
        // returns an associate array of values
        // @see pullTableData
        $data = $this->pullResourceData(DB2_UPDATE);
        extract($data);

        if (!isset($columns)) {
            throw new PEAR_Exception(dgettext('core', 'Update query missing columns'));
        }

        $query[] = 'UPDATE';

        // tables, joins, and subselects
        $query[] = $resources;

        $query[] = 'SET';

        foreach ($columns as $col) {
            // Unlike insert, we only take the first row of values from a table
            // regardless of how many added.
            $foo = array_shift($col);
            foreach ($foo as $bar) {
                $update_values[] = $bar;
            }
        }

        $query[] = DB2::toStringImplode(', ', $update_values);

        if (!empty($where)) {
            $query[] = $where;
        }

        // sorting
        if (!empty($order)) {
            $query[] = $order;
        }

        $this->query = implode(' ', $query);
        return $this->query;
    }

    /**
     * For compatibility reasons, MDB2 creates PEAR errors and not exceptions.
     * This function tests for a Pear error and records it. We don't throw the
     * exception from here however as it would make debugging difficult.
     * @param $result
     * @return boolean
     */
    public function pearError($result)
    {
        if (PHPWS_Error::isError($result)) {
            $this->error = & $result;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Allows insertion of a DB2 object as the source of another query.
     *
     * Example:
     * query1 = new DB2;
     * $query1->addTable('foo');
     * $query1->setAs('foo_result');
     *
     * $query2 = new DB2;
     * $subquery1 = $query2->addSubSelect($query1);
     * $query2->addExpression('foo_result.*');
     * echo $query2->selectQuery();
     * // Echoes
     * // SELECT (foo_result.*) FROM (SELECT foo.* FROM foo) as foo_result;
     * @param DB2 $query
     * @return void
     */
    public function addSubSelect(DB2 $db2, $alias)
    {
        $sub = $this->getSubSelect($db2, $alias);
        $this->sub_selects[] = $sub;
        return $sub;
    }

    public function getSubSelect(DB2 $db2, $alias)
    {
        return new DB2_SubSelect($db2, $alias);
    }

    /**
     * Executes a string or an array of queries sent from insert, update, and delete
     * @param array|string $queries
     * @return void
     */
    public function execute($queries)
    {
        if (is_string($queries)) {
            $queries = array($queries);
        }

        $rows_affected = 0;
        foreach ($queries as $query) {
            $result = $this->mdb2->exec($query);
            if ($this->pearError($result)) {
                throw new PEAR_Exception($result->getMessage());
            }
            $rows_affected += $result;
        }
        $this->query = implode("\n", $queries);
        $this->rows_affected = $rows_affected;
    }

    /**
     * Returns the result of the query created by the object's selectQuery method
     * @access public
     */
    public function select($select_type=0)
    {
        if ($select_type) {
            $this->setSelectType($select_type);
        }

        $this->selectQuery();
        /**
         * Preventing method_exists running per row
         */
        if (isset($this->class_name)) {
            $obj = new $this->class_name;
            $object_select = true;
            $object_import = method_exists($obj, 'load');
        } else {
            $object_select = false;
        }

        switch ($this->select_type) {
            case DB2_ALL:
            case DB2_ROW:
            case DB2_COLUMN:
                $this->resource = $this->mdb2->query($this->query);
                if ($this->pearError($this->resource)) {
                    throw new PEAR_Exception($this->resource->getMessage());
                }

                if (isset($this->index_by)) {
                    $index_name = $this->index_by->getName();
                } else {
                    $index_name = null;
                }

                while ($row = $this->resource->fetchRow()) {
                    if ($this->pearError($row)) {
                        throw new PEAR_Exception($row->getMessage());
                    }

                    if ($object_select) {
                        $obj_row = new $this->class_name;
                        /*
                         * True below means the class contains a db2_import function. The data is passed
                         * to it instead of setting values
                         */
                        if ($object_import) {
                            $obj_row->load($row);
                        } else {
                            foreach ($row as $key=>$value) {
                                $obj_row->$key = $value;
                            }
                        }
                        // If this is a single row, object pull, we just return the object.
                        if ($this->select_type == DB2_ROW) {
                            return $obj_row;
                        } else {
                            $list_row = $obj_row;
                        }
                    } elseif ($this->select_type == DB2_ROW) {
                        // returning the single row called for by the type
                        return $row;
                    } else {
                        // This is not an object or a single row. Rename to add to list.
                        $list_row = & $row;
                    }

                    // Index the row if set
                    if (isset($index_name)) {
                        $row_index = @$row[$index_name];
                        if (!$row_index) {
                            throw new PEAR_Exception(dgettext('core', 'The requested indexing column is missing from select row result'));
                        } else {
                            if ($this->select_type == DB2_COLUMN) {
                                unset($row[$index_name]);
                                if (count($row) != 1) {
                                    throw new PEAR_Exception(dgettext('core', 'An indexed select column must only contain two fields'));
                                }
                                $result[$row_index] = array_pop($row);
                            } else {
                                $result[$row_index] = $list_row;
                            }
                        }
                    } elseif ($this->select_type == DB2_COLUMN) {
                        $result = $this->mdb2->queryCol($this->query);
                    } else{
                        $result[] = $list_row;
                    }
                }
                if (!isset($result)) {
                    $result = null;
                }
                break;

            case DB2_ONE:
                $result = $this->mdb2->queryOne($this->query);
                break;
        }

        if ($this->pearError($result)) {
            throw new PEAR_Exception($result->getMessage());
        } else {
            $this->rows_affected = $this->resource->numRows();
            return $result;
        }
    }


    /**
     * MDB2 does not use exception so we capture its PEAR_Error into the error variable.
     * This function clones the layout of the exception error print out so a developer may
     * backtrace the error.
     * @access public
     * @returns string
     */
    public function printError()
    {
        if (empty($this->error)) {
            return dgettext('core', 'No error found.');
        } else {
            $tpl['message'] = & $this->error->message;
            $tpl['exception'] = dgettext('core', 'Exception trace');
            $tpl['function_label'] = dgettext('core', 'Function');
            $tpl['location_label'] = dgettext('core', 'Location');
            foreach ($this->error->backtrace as $key => $err) {
                $subtpl['key'] = $key;
                $subtpl['function'] = "{$err['class']}->{$err['function']}()";
                $subtpl['location'] = "{$err['file']}:{$err['line']}";
                $tpl['errors'][] = $subtpl;
            }
            return PHPWS_Template::process($tpl, 'core', 'error.tpl');
        }
    }



    /**
     * Pulls various control information from the DB2 object and the tables within. Originally
     * used only in select, it was pulled out for use in update and delete.
     * @access private
     * @return array
     */
    private function pullResourceData($mode=DB2_SELECT)
    {
        $resources = null; // tables and subselects
        $sources = null; // joins, tables, and subselects

        // if where_groups is NOT empty then allow where to start with its conjunction
        $allow_first_conjunction = false;


        if (!empty($this->join_tables)) {
            $show_left = true;
            foreach ($this->join_tables as $join) {
                $joined[] = $join->getQuery($show_left);
                $show_left = false;
            }
            $sources[] = implode(' ', $joined);
        }

        if (!empty($this->tables) && !empty($this->sub_selects)) {
            $resources = array_merge($this->tables, $this->sub_selects);
        } elseif ($this->tables) {
            $resources = $this->tables;
        } elseif ($this->sub_selects) {
            $resources = $this->sub_selects;
        }

        if (empty($sources) && empty($resources)) {
            throw new PEAR_Exception(dgettext('core', 'No resources created'));
        }

        if ($resources) {
            foreach ($resources as $resource) {
                if ($mode == DB2_SELECT && $field_list = $resource->getFields()) {
                    $data['columns'][] = $field_list;
                } elseif ($mode == DB2_UPDATE && $value_list = $resource->getValues()) {
                    $data['columns'][] = $value_list;
                }

                if (!$resource->isJoined()) {
                    $sources[] = $resource->getQuery();
                }

                if ($twhere = $resource->getWhereStack($allow_first_conjunction)) {
                    $where[] = $twhere;
                    $allow_first_conjunction = true;
                }

                if ($mode == DB2_SELECT) {
                    if ($thaving = $resource->getHavingStack($allow_first_conjunction)) {
                        $having[] = $thaving;
                        $allow_first_conjunction = true;
                    }
                }

                if ($resource->isRandomOrder()) {
                    $order[] = $resource->getRandomOrder();
                } elseif ($order_list = $resource->getOrderBy()) {
                    $order[] = $order_list;
                }
            }
        }

        $data['resources'] = implode(', ', $sources);

        $where_groups = $this->whereGroupQuery();

        if (!empty($where_groups) || !empty($where)) {
            $slist[] = 'WHERE';
            if (!empty($where)) {
                $slist[] = implode(' ', $where);
            }

            if (!empty($where_groups)) {
                $slist[] = $where_groups;
            }
            $data['where'] = implode(' ', $slist);
        }

        // Groups used only on selects
        if ($mode == DB2_SELECT && $group_by = $this->getGroupBy()) {
            $glist[] = $group_by;
            if (!empty($having)) {
                $glist[] = 'HAVING ' . implode(', ', $having);
            }
            $data['groupby'] = DB2::toStringImplode(' ', $glist);
            //$data['groupby'] = implode(' ', $glist);
        }

        if (!empty($order)) {
            $data['order'] = 'ORDER BY ' . implode(', ', $order);
        }

        if (empty($data)) {
            throw new PEAR_Exception('core', 'DB2 object does not contain enough information to create a query.');
        } else {
            return $data;
        }
    }

    /**
     * Creates a SELECT query using the values set in the the DB2 object.
     * @access public
     * @returns string : The returned query. Also saved in the object's query variable.
     */
    public function selectQuery()
    {
        // Assures fields or functions are present before trying to query database
        $fields_present = false;

        // returns an associate array of values
        // @see pullTableData

        $data = $this->pullResourceData();

        if (!empty($data)) {
            extract($data);
        }

        $query[] = 'SELECT';
        if (isset($columns)) {
            $query[] = implode(', ', $columns);
            $fields_present = true;
        }

        if (!empty($this->expressions)) {
            if ($fields_present) {
                $query[] = ', ';
            }
            $query[] = DB2::toStringImplode(', ', $this->expressions);
            //$query[] = implode(', ', $this->expressions);
            $fields_present = true;
        }

        if (!$fields_present) {
            throw new PEAR_Exception(dgettext('core', 'Select did not contain any fields to return'));
        }

        $query[] = 'FROM';

        // tables, joins, and subselects
        $query[] = $resources;

        // where and where groups
        if (!empty($where)) {
            $query[] = $where;
        }

        // groups and having clauses
        if (!empty($groupby)) {
            $query[] = $groupby;
        }

        // sorting
        if (!empty($order)) {
            $query[] = $order;
        }

        $this->query = implode(' ', $query);
        return $this->query;
    }

    /**
     * @access private
     */
    private function whereGroupQuery()
    {
        if (empty($this->where_group_stack)) {
            return null;
        }

        $first = true;
        foreach ($this->where_group_stack as $where_group) {
            $where[] = $where_group->query($first);
            $first = false;
        }

        return implode(' ', $where);
    }

    /**
     *
     * @param string class_name
     * @return void
     */
    public function setClass($class_name) {
        $this->class_name = $class_name;
    }

    /**
     * Looks in all the current tables in the database and returns a field object
     * or an array of fields if more than one is found. If none is found, an exception
     * is thrown instead
     * @param string $column_name
     * @access public
     * @return DB2_Field|array
     */
    public function getFieldFromTables($column_name, $add_to_table=false)
    {
        $tfields = 0;
        foreach ($this->tables as $tbl) {
            if ($tbl->verifyColumn($column_name)) {
                if ($add_to_table) {
                    $fields[] = $tbl->addField($column_name);
                } else {
                    $fields[] = $tbl->getField($column_name);
                }
            }
        }

        $tfields = count($fields);

        if ($tfields == 1) {
            return $fields[0];
        } elseif ($fields > 1) {
            return $fields;
        } else {
            throw new PEAR_Exception(dgettext('core', 'No table fields were found'));
        }
    }

    /**
     * @access public
     */
    public function groupWhere()
    {
        static $position = 0;

        $args = func_get_args();

        if (empty($args)) {
            throw PEAR_Exception(dgettext('core', 'Invalid parameters.'));
        } else {
            $position++;
            try {
                $this->where_group_stack[$position] = new DB2_Conditional_Group($this, $position, $args);
            } catch (PEAR_Exception $e) {
                throw new PEAR_Exception('groupWhere parameters must be DB2 Conditional/Conditional_Group objects', $e);
            }
            return $this->where_group_stack[$position];
        }
    }

    /**
     * Removes a where group from the stack. This is used internally when a
     * new group is added to prevent repeats.
     * @param boolean $position The position of the where group in the stack
     * @return void
     */
    public function dropWhereGroup($position)
    {
        unset($this->where_group_stack[$position]);
    }

    /**
     * Tests the operator parameter to see if it is valid
     * @param string $operator
     * @return boolean  True if valid, false if not.
     */
    public static function isOperator($operator)
    {
        static $operator_types = array('=', '<', '>', '>=', '<=', '<>', '!=', '!<', '!>');
        return in_array($operator, $operator_types);
    }

    /**
     * Returns true if the table name exists in the entire database. To see if
     * the table is currently in the DB2 object stack, use DB2::isTable($table_name)
     * @param string $table_name Name of table to check
     * @return boolean
     */
    public function tableExists($table_name)
    {
        static $table_list = null;
        if (empty($table_list)) {
            $this->mdb2->loadModule('Manager');
            $table_list = $this->mdb2->listTables();
        }
        
        if ($this->tbl_prefix) {
            $table_name = $this->tbl_prefix . $table_name;
        }

        return in_array($table_name, $table_list);
    }

    /**
     * Expecting any DB2 to string will be used for embedding a select query
     * into another query.
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->selectQuery();
        } catch (PEAR_Exception $e) {
            $this->logError($e);
            trigger_error($e->getMessage());
        }
    }

    public function logError(PEAR_Exception $e)
    {
        require_once 'Log.php';
        $trace = $e->getTrace();
        $count = 1;
        foreach ($trace as $err) {
            $message[] = sprintf("(%s) File:%s | Line:%s | Function:%s%s%s(%s)", $count, $err['file'], $err['line'], $err['class'], $err['type'], $err['function'], $err['args']);
            $count++;
        }
        PHPWS_Core::log($e->getMessage() . ' -- ' . implode(', ', $message), 'db2.log', dgettext('core', 'DB2 Error'), PEAR_LOG_CRIT);
    }

    /**
     * Returns the rows affected from a previous query or execution
     * @return integer
     */
    public function getRowsAffected()
    {
        return $this->rows_affected;
    }

    public function numRows()
    {
        if (empty($this->resource)) {
            throw new PEAR_Exception(dgettext('core', 'Missing query resource.'));
        }
        return $this->resource->numCols();
    }

    public function numCols()
    {
        if (empty($this->resource)) {
            throw new PEAR_Exception(dgettext('core', 'Missing query resource.'));
        }
        return $this->resource->numCols();
    }

    /**
     * Takes an object, parses its variables, and saves them into the current table or tables.
     * Function checks to see if the object is an extension of DB2_Object. If so, it calls the
     * object's save function. Otherwise, it depends on get_object_vars (which may skip
     * private variables)
     * @param object $object
     * @return void
     */
    public function saveObject($object)
    {
        $insert_object = false;

        if (!is_object($object)) {
            throw new PEAR_Exception(dgettext('core', 'Variable is not an object'));
        }

        if (is_subclass_of($object, 'DB2_Object')) {
            return $object->save();
        } else {
            $values = get_object_vars($object);
        }

        if (empty($values)) {
            throw new PEAR_Exception(dgettext('core', 'No values in object to save'));
        }

        foreach ($this->tables as $tbl) {
            $primary_key = $tbl->getPrimaryIndex();
            foreach ($values as $column=>$value) {
                if ($tbl->verifyColumn($column)) {
                    // if the column is a primary key and empty, we want to insert
                    if ($column == $primary_key) {
                        if (empty($value)) {
                            $insert_object = true;
                        } else {
                            if (!$tbl->isJoined()) {
                                $tbl->addWhere($primary_key, $value);
                            }
                            $insert_object = false;
                        }
                        continue;
                    }
                    $tbl->addValue($column, $value);
                }
            }
        }

        if ($insert_object) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    /**
     * Saves an array of objects
     * @param array $objects
     * @return void
     */
    public function saveAllObjects(array $objects)
    {
        foreach ($objects as $obj)
        {
            if (!is_object($obj)) {
                throw new PEAR_Exception(dgettext('core', 'Array must contain object variables only'));
            }

            if (is_subclass_of($obj, 'DB2_Object')) {
                $obj->save();
            } else {
                $this->saveObject($obj);
            }
        }
    }

    /**
     * Safely quotes a value for entry in the database.
     * Uses the MDB2 quote function but makes assumptions based on the value type
     * @param mixed $value
     * @return mixed
     */
    public function quote($value)
    {
        if (is_object($value) && method_exists($value, '__isString')) {
            $value = (string)$value;
        }

        if (is_string($value)) {
            return $this->mdb2->quote($value);
        } elseif (is_array($value)) {
            return array_map(array($this, 'quote'), $value);
        } else {
            return $value;
        }
    }

    /**
     * Because PHP 5.1 doesn't do __toString like 5.2 (booo!), this function
     * is a work-around
     * @param array $objects
     * @return unknown_type
     */
    public static function toStringImplode($glue, array $pieces)
    {
        if (version_compare(phpversion(), '5.2.0', '<')) {
            $callback = create_function('$e', 'return call_user_func(array($e, "__toString"));');
            $result = array_map($callback, $pieces);
            return implode($glue, $result);
        } else {
            return implode($glue, $pieces);
        }
    }
}
?>