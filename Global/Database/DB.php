<?php

namespace Database;

if (!defined('DB_PERSISTENT_CONNECTION')) {
    define('DB_PERSISTENT_CONNECTION', false);
}

/**
 * The DB class object helps construct a database query. It is abstract and meant
 * for extension by different database engines in the Engine directory.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class DB extends \Data {
    /**
     * Type of module data to pull.
     * @see DB::pullResourceData()
     */

    const SELECT = 1;
    /**
     * Type of module data to pull.
     * @see DB::pullResourceData()
     */
    const UPDATE = 2;
    /**
     * Type of module data to pull.
     * @see DB::pullResourceData()
     */
    const DELETE = 3;

    static $transaction_count = 0;

    /**
     * Array of table objects used for the query
     * @var array
     * @access private
     */
    private $tables = array();

    /**
     * Current table prefix, if any
     * @var string
     * @access private
     */
    private $tbl_prefix = null;
    private $conditional;

    /**
     * An array of joined tables
     * @var array
     * @access private
     */
    private $join_tables = null;

    /**
     * Array of last inserted ids. Keyed by table name.
     * @var array
     */
    private $last_id = null;

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
     * Field object you want a select result indexed by
     * @see DB::setIndexBy
     * @var object
     */
    private $index_by = null;

    /**
     * Last queried PSOStatement. Nulls out when complete
     * @var PDOStatement
     */
    private $pdo_statement = null;

    /**
     * Fetch mode from pdo. This can be altered but the default is an
     * associative result.
     * @var integer
     */
    private $pdo_fetch_mode = \PDO::FETCH_ASSOC;

    /**
     * Tracks the previously requested query. Allows parameter free fetches.
     * @var string
     */
    private $current_query = null;

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
     * Stack of modules from last query/exec
     * @var array
     */
    public $module = null;

    /**
     * Rows affected by last query or execution
     * @var integer
     */
    private $rows_affected = 0;

    /**
     * Stack of sub selects that will be embedded in the end result query
     * @var array
     */
    private $sub_selects = null;

    /**
     * DBMS specific settings called when a table is created.
     * @var array
     */
    private $table_options = array();

    /**
     * If true, the DISTINCT keyword will be added to this object's next
     * SELECT query.
     * @var boolean
     */
    private $distinct = false;

    /**
     * Holds the dsn object
     * @var object
     */
    protected $dsn;

    /**
     * The current PDO object. Kept static to prevent constant construction.
     * @var \PDO
     */
    static public $PDO;

    /**
     * An array stack of PDO constructions. Each PDO construction needs to be
     * initialized only once per DSN connection. The key of the array is a hash
     * based on the PDO object contents.
     *
     * @see $this->loadPDO()
     * @var array
     */
    static private $pdo_stack;

    /**
     * Extended OS classes should return a true if the $database_name is an
     * existing database.
     * @param string
     * @return boolean
     */
    abstract public function databaseExists($database_name);

    /**
     * Should return true if the passed in table name exists.
     * @param string
     * @return boolean
     */
    abstract public function tableExists($table_name);

    /**
     * Extended class should return a flat array of table names from the
     * current database.
     * @return array
     */
    abstract public function listTables();

    /**
     * Extended class should return the value delimiter used by its OS (e.g.
     * ~ : MySql, ' : PostgreSQL
     * @return string
     */
    abstract public function getDelimiter();

    /**
     * Should return the proper format for a random order
     * @return string
     */
    abstract public function getRandomCall();

    /**
     * Should return an array of database names in alphabetical order.
     */
    abstract public function listDatabases();

    /**
     * Accepts a DSN object to create a new
     * @param \Database\DSN $dsn
     */
    public function __construct(\Database\DSN $dsn)
    {
        $this->setDSN($dsn);
    }

    /**
     * Returns the table prefix
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->dsn->getTablePrefix();
    }

    /**
     * Resets the tables variable
     */
    public function clearTables()
    {
        $this->tables = null;
    }

    /**
     * Starts a database transaction.
     */
    public function begin()
    {
        if (self::$transaction_count == 0) {
            if (empty(self::$PDO)) {
                throw new \Exception(t('PDO connection is missing'));
            }
            self::$transaction_count++;
            return self::$PDO->beginTransaction();
        }
        self::$transaction_count++;
    }

    /**
     * Commits changes made to the database since a transaction start.
     * @throws Exception
     */
    public function commit()
    {
        self::$transaction_count--;
        if (self::$transaction_count == 0) {
            if (empty(self::$PDO)) {
                throw new \Exception(t('PDO connection is missing'));
            }

            return self::$PDO->commit();
        } elseif (self::$transaction_count < 0) {
            throw new \Exception(t('Transaction not started'));
        }
    }

    /**
     * Rolls back changes made to the database since a transaction start.
     * @throws Exception
     */
    public function rollback()
    {
        self::$transaction_count--;
        if (self::$transaction_count == 0) {
            if (empty(self::$PDO)) {
                throw new \Exception(t('PDO connection is missing'));
            }

            return self::$PDO->rollBack();
        } elseif (self::$transaction_count < 0) {
            throw new \Exception(t('Transaction not started'));
        }
    }

    /**
     * Sets the conditional for use in select or update queries.
     *
     * @param \Database\Conditional $conditional
     */
    public function setConditional(\Database\Conditional $conditional)
    {
        $this->conditional = $conditional;
    }


    /**
     * Allows the developer to string together several conditionals at once
     * instead of setting them individually. The Conditionals WILL ALWAYS
     * be compared using AND. If you don't want to stack them with ANDS then combine
     * your OR conditionals into a new conditional and THEN include it in the stack.
     *
     * Example:
     * <code>
     * $db = Database::newDB();
     * $t1 = $db->addTable('alpha');
     * $c1 = $t1->getFieldConditional('id', 5);
     * $c2 = $t1->getFieldConditional('name', 'Tom');
     * $db->stackConditionals($c1, $c2);
     * </code>
     *
     * @return void
     * @throws Exception If arguments are not Conditionals or empty
     */
    public function stackConditionals()
    {
        $args = func_get_args();
        $current_conditional = null;
        if (empty($args)) {
            throw new Exception(t('No arguments sent to stackConditionals'));
        }
        foreach ($args as $conditional) {
            if (!($conditional instanceof \Database\Conditional)) {
                throw new Exception(t('Argument sent to stackConditionals was not a Database\Conditional object'));
            }
            if (empty($current_conditional)) {
                $current_conditional = $conditional;
            } else {
                $current_conditional = new \Database\Conditional($current_conditional,
                        $conditional, 'AND');
            }
        }
        $this->setConditional($current_conditional);
    }

    /**
     * Sets the conditional for the current DB object
     */
    public function clearConditional()
    {
        $this->conditional = null;
    }

    /**
     * Constructs and returns a Conditional object.
     * @param mixed $left
     * @param mixed $right
     * @param string $operator
     * @return \Database\Conditional
     */
    public function getConditional($left, $right, $operator = null)
    {
        if (is_null($operator)) {
            if ($left instanceof \Database\Conditional && $right instanceof \Database\Conditional) {
                $operator = 'AND';
            } else {
                $operator = '=';
            }
        }
        return new Conditional($left, $right, $operator);
    }

    /**
     * Sets the DSN and loads the PDO object for future queries.
     * @param \Database\DSN $dsn
     */
    public function setDSN(\Database\DSN $dsn)
    {
        $this->dsn = $dsn;
        $this->loadPDO();
    }

    /**
     * Sets the distinct variable which decides if select queries will contain
     * the DISTINCT condition.
     * @param boolean $distinct
     */
    public function setDistinct($distinct)
    {
        $this->distinct = (bool) $distinct;
    }

    /**
     * Receives a configuration file path and loads the found file. The variables
     * contained in the file are then set to
     * @param string $filename Path to configuration file
     * @throws \Exception
     */
    public function loadDatabaseConfig($filename)
    {
        $this->read($filename);
        if ($this->username->isEmpty()) {
            throw new \Exception(t('Database configuration data missing from DSN file.'));
        }
    }

    /**
     * Initializes a new PDO object into the static pdo_stack variable. Repeated
     * PDO constuctions prohibited by key check on stack.
     */
    public function loadPDO()
    {
        $hash = md5($this->dsn->getPDOString());
        if (!isset(self::$pdo_stack[$hash])) {
            self::$pdo_stack[$hash] = new \PDO($this->dsn->getPDOString(),
                    $this->dsn->getUsername(), $this->dsn->getPassword(),
                    array(\PDO::ATTR_PERSISTENT => DB_PERSISTENT_CONNECTION, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        }
        self::$PDO = self::$pdo_stack[$hash];
    }

    /**
     * If you would prefer to interact directly with PDO, this will return the
     * PDO object used for the current connection.
     */
    public function getPDO()
    {
        return self::$PDO;
    }

    /**
     * Creates new database
     * @param string $database_name
     * @return boolean True on success
     */
    public function createDatabase($database_name)
    {
        if (!$this->allowedIdentifier($database_name)) {
            throw new \Exception(t('Improper database name'));
        }

        return $this->query("CREATE DATABASE $database_name");
    }

    /**
     * Drops the passed database name from the system.
     * @param string $database_name
     * @return boolean | Error
     */
    public function dropDatabase($database_name)
    {
        if (!$this->allowedIdentifier($database_name)) {
            throw new \Exception(t('Improper database name'));
        }
        if ($database_name == (string) $this->dsn->database_name) {
            throw new \Exception(t('May not drop currently connected database'));
        }

        return $this->query('DROP DATABASE ' . $database_name);
    }

    /**
     * Sends a query string to the PDO. Returns a PDOStatement.
     * If not a SELECT query, DB::exec should be used instead.
     * @param string $sql
     * @return PDOStatement
     *
     */
    public function query($sql)
    {
        if (empty($sql)) {
            throw new \Exception('SQL query was empty');
        }

        \Database::logQuery($sql);
        return self::$PDO->query($sql);
    }

    /**
     * Executes a query string using the PDO. Returns the number of rows affected
     * by the statement. If using SELECT, use DB::query() instead.
     * @param string $sql
     * @return integer
     *
     */
    public function exec($sql)
    {
        if (empty($sql)) {
            throw new \Exception('SQL query was empty');
        }

        \Database::logQuery($sql);
        return self::$PDO->exec($sql);
    }

    /**
     * Sets the default fetch mode for the PDO
     * @param integer $mode
     * @link http://www.php.net/manual/en/pdo.constants.php
     */
    public function setFetchMode($mode)
    {
        self::$PDO->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, $mode);
    }

    /**
     * Sets the default fetch mode to an associative array
     */
    public function setFetchModeAssoc()
    {
        $this->setFetchMode(\PDO::FETCH_ASSOC);
    }

    /**
     * Renames a table in the database. This is a shortcut solution to alterTable.
     * @param string $old_name Name of the table to change
     * @param string $new_name Name to change the table to.
     * @return boolean True is successful, thrown exception otherwise.
     */
    public function renameTable($old_name, $new_name)
    {
        if (!$this->inTableStack($old_name)) {
            throw new \Exception(t('Table does not exist'));
        }

        if (!$this->allowed($new_name)) {
            throw new \Exception(t('Improper new table name'));
        }
        return $this->alterTable($old_name, array('name' => $new_name));
    }

    /**
     * Checks a string against a list of restricted database words.
     * Used internally but allowed public for developer use.
     * @param string $name Word to verify.
     * @return boolean True if allowed, false if restricted.
     */
    public static function allowedIdentifier($name)
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
     * Sets the database's table prefix. These prefixes are added to the beginning
     * of every table name.
     * @access private
     * @param string $tbl_prefix Prefix added to table names.
     */
    private function setTablePrefix($tbl_prefix)
    {
        if (preg_match('/\W/', $tbl_prefix)) {
            throw new \Exception(t('Table prefix contains illegal characters'));
        }
        $this->tbl_prefix = $tbl_prefix;
    }

    /**
     * Adds an expression to the database. The expression will be include as a queried
     * column. If you want to use an expression in a conditional, create it separately
     * as a new object and do not use this function.
     * @see Expression
     * @param string $expression Expression to add
     * @param string $alias Alias of expression (e.g. some_function(column) as foo)
     * @return Expression
     */
    public function addExpression($expression, $alias = null)
    {
        $expression = $this->getExpression($expression, $alias);
        $this->expressions[] = $expression;
        return $expression;
    }

    /**
     * Returns a Expression object
     * @param string $expression
     * @param string $alias Alias for the expression.
     * @return Expression
     * @see \Database\Expression
     */
    public function getExpression($expression, $alias = null)
    {
        return new \Database\Expression($expression, $alias);
    }

    /**
     * Returns reference to the connect object
     * @return Database\Connect
     */
    public function getConnect()
    {
        $this->loadConnect();
        return $this->connect;
    }

    /**
     * Returns the currently used DSN connection information
     * @return \Database\DSN
     */
    public function getDSN()
    {
        return $this->dsn;
    }

    /**
     * Adds a table object to the table stack. If you need a table object associated
     * with the current DB, use buildTable.
     *
     * use_in_query determines whether the table is used in a delete or select
     * query.
     *
     * Example:
     * <code>
     * $db->addTable('foo', null, true);
     * $db->addTable('bar', null, true);
     *
     * echo $db->selectQuery();
     * // select foo.* from foo, bar;
     *
     * echo $db->deleteQuery();
     * // delete foo.* from foo, bar;
     * </code>
     *
     * @param string table_name
     * @param string alias Table designation/nickname
     * @param boolean use_in_query If true, use table in select or delete query.
     * @return \Database\Table : reference to the object in the tables stack
     */
    public function addTable($table_name, $alias = null, $use_in_query = true)
    {
        $index = !empty($alias) ? $alias : $table_name;

        if (isset($this->tables[$index])) {
            throw new \Exception(t('Duplicate table added'));
        }
        if (DATABASE_CHECK_TABLE && !$this->tableExists($table_name)) {
            throw new \Exception(t('Table "%s" does not exist', $table_name));
        }
        $table = $this->buildTable($table_name, $alias);

        // @see \Database\Resource::$show_all_fields
        $table->useInQuery($use_in_query);
        $this->tables[$index] = $table;
        return $table;
    }

    /**
     * Creates a table object without requiring its existence in the database.
     * @param string $table_name
     * @param string $alias
     * @return \Database\Table
     */
    public function buildTable($table_name, $alias = null)
    {
        $engine = $this->getDatabaseType();
        $table_class = "Database\Engine\\$engine\Table";
        $table = new $table_class($this, $table_name, $alias);
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
    public function inTableStack($table)
    {
        return isset($this->tables[$table]);
    }

    /**
     * Pulls the currently named table from the table stack
     * @param string $table_name
     * @return Table
     */
    public function pullTable($table_name)
    {
        if ($this->inTableStack($table_name)) {
            return $this->tables[$table_name];
        } else {
            throw new \Exception(t('Table "%s" does not exist', $table_name));
        }
    }

    /**
     * Returns the database OS type (e.g. mysql, pgsql, etc.)
     * @return string
     */
    public function getDatabaseType()
    {
        return $this->dsn->getDatabaseType();
    }

    /**
     * Returns the first table object on the DB table stack.
     * @return \Database\Table
     */
    public function getFirstTable()
    {
        return current($this->tables);
    }

    /**
     * Sets the group by query for a select query
     *
     * @param mixed $fields : A single or array of Field or Function objects
     * @param integer $group_type : A defined group by type
     * @return unknown_type
     */
    public function setGroupBy($fields, $group_type = null)
    {
        /**
         * For information on the fields parameter
         * @see Group::__construct
         */
        /**
         * Defines for group type are at the top of Group
         * @see Group
         */
        $engine = $this->getDatabaseType();
        $group_class = "Database\Engine\\$engine\Group";
        $this->group_by = new $group_class($fields, $group_type);
        return $this->group_by;
    }

    /**
     * Returns the table stack from the DB object. Only tables added by the dev
     * will be returned. For a list of all tables, use $db->listTables().
     * If you need to know if a table is in the database use
     * $db->tableExists($table_name)
     *
     * @return array
     */
    public function getAllTables()
    {
        return $this->tables;
    }

    /**
     * Limits a query's results.
     * @param integer $limit Number of rows to return to act upon
     * @param integer $offset Number of rows to skip before starting the limit count.     */
    public function setLimit($limit, $offset = null)
    {
        $this->limit = (int) $limit;
        if ($offset) {
            $this->offset = (int) $offset;
        }
    }

    /**
     * Returns the group_by object. Expected use is for string output.
     * @return Group object
     */
    private function getGroupBy()
    {
        if (empty($this->group_by)) {
            return null;
        }
        return $this->group_by;
    }

    /**
     * Returns the last id used on the previous insert.
     * @param string $table_name
     * @return integer
     */
    public function getLastId($table_name)
    {
        return $this->last_id[$table_name];
    }

    /**
     * Joins two modules together.
     * @param mixed $left Will be a table, subselect, or field object
     * @param mixed $right Same as left
     * @param string $type The type of join to be performed.
     * @param string $operator The comparison operator
     * @return Database\Join
     */
    public function join($left, $right, $type = null, $operator = null)
    {
        $type = $type ? $type : 'inner';
        $operator = $operator ? $operator : '=';
        $jt = new Join($left, $right, $type, $operator);
        $this->join_tables[] = $jt;
        return $jt;
    }

    /**
     * Checks a value against a number of reserved words, a regular expression, and a variable type.
     * Returns false if the string is not allowed, true otherwise.
     * @param string $value - String of table, field, etc. we are checking.
     * @access public
     */
    public static function allowed($value)
    {
        if (!is_string($value)) {
            return false;
        }

        if (preg_match('/[^\w\*\.]/', $value)) {
            return false;
        }

        return DB::allowedIdentifier($value);
    }

    /**
     * Accepts a table field for indexing a select result.
     * For example:
     * $id_field = $table->getField('id');
     * $DB->setIndexBy($id_field);
     * $result = $DB->select();
     *
     * Normally you would receive array(0=>array('id'=>4, 'name'=>'Ted'))
     * with an index set you would instead get array(4=>array('id'=>4, 'name'=>'Ted'))
     *
     * @param Field $field
     */
    public function setIndexBy(Field $field)
    {
        $this->index_by = $field;
    }

    /**
     * Runs insert on all tables in the database object
     * @return integer Number of rows inserted
     */
    public function insert()
    {
        if (empty($this->tables)) {
            throw new \Exception(t('No tables found'));
        }
        foreach ($this->tables as $tbl) {
            $rows_affected = $tbl->insert();
            $this->rows_affected += $rows_affected;
        }
        return $this->rows_affected;
    }

    /**
     * Updates values in one or more tables. The DB class insert method queries
     * multiple tables and runs multiple queries. This update function only gets
     * multiple table results BUT only performs one query. This allows table to table
     * column copying.
     * @return integer Number of rows affected by update
     */
    public function update()
    {
        $query = $this->updateQuery();
        $this->rows_affected = $this->exec($query);
        return $this->rows_affected;
    }

    /**
     * Deletes one or more rows based on the properties in the DB object.
     * @return integer Number of rows deleted
     */
    public function delete()
    {
        $query = $this->deleteQuery();
        $this->rows_affected = $this->exec($query);
        return $this->rows_affected;
    }

    /**
     * Unlike other execution queries, deleteQuery accepts an array parameter.
     * The array should contain modules the dev wants to delete.
     * For example:
     *
     * $DB = \Database::newDB()();
     * $t1 = $DB->addTable('t1', 'a1');
     * $t2 = $DB->addTable('t2', 'a2');
     *
     * $DB->join($t1, $t2, 'cross');
     * $t1->addWhere('id', $t2->getField('id'));
     * echo $DB->deleteQuery(array($t1, $t2));
     * // echoes DELETE a1, a2 FROM t1 AS a1 CROSS JOIN t2 AS a2 WHERE a1.id = (a2.id)
     *
     * @param array $include_on_join This parameter is optional and is only used with joins.
     * @return string The complete delete query.
     */
    public function deleteQuery($include_on_join = null)
    {
        $delete_modules = array();
        $query = array();
        $modules = array();
        $columns = array();
        /**
         * Next two variables are extracted from pullResourceData
         */
        $where = null;
        $order = null;

        $query[] = 'DELETE';
        $data = $this->pullResourceData(self::DELETE);
        extract($data);

        if (!empty($include_on_join)) {
            foreach ($include_on_join as $module) {
                if (is_subclass_of($module, 'Resource')) {
                    $delete_modules[] = $module->hasAlias() ? $module->getAlias() : $module->getResourceQuery();
                }
            }
            $query[] = implode(', ', $delete_modules);
        }

        $query[] = 'FROM';
        // from tables, joins, and subselects
        $query[] = $modules;

        if (!empty($where)) {
            $query[] = $where;
        }

        // sorting
        if (!empty($order)) {
            $query[] = $order;
        }
        return implode(' ', $query);
    }

    /**
     * Creates a update query string. Updated values are quoted in the
     * Value method __toString.
     * @return string Completed update query
     */
    public function updateQuery()
    {
        // returns an associate array of Values
        // @see pullTableData

        /**
         * Next four variables are extracted from pullResourceData
         */
        $columns = $where = $order = $modules = null;

        $data = $this->pullResourceData(self::UPDATE);
        extract($data);

        if (!isset($columns)) {
            throw new \Exception(t('Update query missing columns'));
        }

        $query[] = 'UPDATE';

        // tables, joins, and subselects
        $query[] = $modules;

        $query[] = 'SET';

        foreach ($columns as $col) {
            // Unlike insert, we only take the first row of values from a table
            // regardless of how many added.
            $foo = array_shift($col);
            foreach ($foo as $bar) {
                $update_values[] = $bar;
            }
        }

        $query[] = implode(', ', $update_values);

        if (!empty($where)) {
            $query[] = $where;
        }

        if (!empty($order)) {
            $query[] = $order;
        }

        return implode(' ', $query);
    }

    /**
     * Allows insertion of a DB object as the source of another query.
     *
     * Example:
     * <code>
     * query1 = \Database::newDB()();
     * $query1->addTable('foo');
     * $query1->setAs('foo_result');
     *
     * $query2 = \Database::newDB()();
     * $subquery1 = $query2->addSubSelect($query1);
     * $query2->addExpression('foo_result.*');
     * echo $query2->selectQuery();
     * // Echoes
     * // SELECT (foo_result.*) FROM (SELECT foo.* FROM foo) as foo_result;
     * </code>
     * @param \DB $DB A DB object queried to produce the subscript
     * @param string $alias Alias for the subselect query.
     */
    public function addSubSelect(DB $DB, $alias)
    {
        $sub = $this->getSubSelect($DB, $alias);
        $this->sub_selects[] = $sub;
        return $sub;
    }

    /**
     * Returns a SubSelect object based on the current db object
     * @param \DB $DB
     * @param \Database\Alias $alias
     * @return SubSelect
     */
    public function getSubSelect(DB $DB, $alias = null)
    {
        return new SubSelect($DB, $alias);
    }

    /**
     * Sets the PDO statement for use with fetch methods
     * @param string $sql
     * @return void
     */
    public function loadStatement($sql)
    {
        $this->pdo_statement = $this->query($sql);
    }

    /**
     * Takes the current select query and creates a PDO statement from it.
     */
    public function loadSelectStatement()
    {
        $this->loadStatement($this->selectQuery());
    }

    /**
     * Checks to see if the pdo_statement has been set. Throws an exception if not.
     * @throws \Exception
     */
    private function checkStatement()
    {
        if (empty($this->pdo_statement)) {
            throw new \Exception(t('Query statement must be set before fetch'));
        }
    }

    /**
     * Loads the select statement and fetches one row.
     * @return array
     */
    public function selectOneRow()
    {
        if (empty($this->pdo_statement)) {
            $this->loadSelectStatement();
        }
        return $this->fetchOneRow();
    }

    /**
     * Fetches a single row and then clears the PDO statement.
     * @return array
     */
    public function fetchOneRow()
    {
        $this->checkStatement();
        $result = $this->pdo_statement->fetch(\PDO::FETCH_ASSOC);
        $this->clearStatement();
        return $result;
    }

    /**
     * Fetches a row from the database based upon the pdo_statement. fetch()
     * will continue to return rows until no more can be returned. Fetch should
     * not be used for single row fetch without clearing the statement (use
     * fetchOneRow() instead). If the statement is not cleared after an incomplete
     * fetch cycle, the next fetch will assume the current statement should be
     * used unless set otherwise.
     * @return array
     */
    public function fetch()
    {
        $this->checkStatement();
        $result = $this->pdo_statement->fetch(\PDO::FETCH_ASSOC);
        if (empty($result)) {
            $this->clearStatement();
        }
        return $result;
    }

    public function selectInto($object)
    {
        if (empty($this->pdo_statement)) {
            $this->loadSelectStatement();
        }
        $this->fetchInto($object);
    }

    /**
     * Returns a passed $object parameter with values set from the current query.
     *
     * @param object $object
     * @return object
     */
    public function fetchInto($object)
    {
        $this->checkStatement();
        $this->pdo_statement->setFetchMode(\PDO::FETCH_INTO, $object);
        $result = $this->pdo_statement->fetch();
        if (empty($result)) {
            $this->clearStatement();
        }
        return $result;
    }

    /**
     * Fetches all rows based on the current pdo_statement and returns them.
     * @return array
     */
    public function fetchAll()
    {
        $this->checkStatement();
        $result = $this->pdo_statement->fetchAll(\PDO::FETCH_ASSOC);
        $this->clearStatement();
        return $result;
    }

    /**
     * Loads the select query and calls self::fetchColumn.
     * Like fetchColumn, only a single row is returned per iteration.
     *
     * @param integer $column
     * @return string
     */
    public function selectColumn($column = 0)
    {
        if (empty($this->pdo_statement)) {
            $this->loadSelectStatement();
        }
        return $this->fetchColumn($column);
    }

    /**
     * Returns a single column from a row in the current pdo select
     * statement. The pointer is then advanced to the next row. Returns null
     * when reaching the end of the result stack.
     *
     * The default column returned is the first. The column number may be changed
     * to return a different column result.
     *
     * @param integer $column
     * @return string
     */
    public function fetchColumn($column = 0)
    {
        $this->checkStatement();
        $result = $this->pdo_statement->fetchColumn($column);
        if (empty($result)) {
            $this->clearStatement();
        }
        return $result;
    }

    /**
     * Fetches one row and returns it as an object of class $class_name. Some notes:
     * 1) The class constuctor WILL BE CALLED.
     * 2) If the variable is set in the constructor, it will overwrite whatever
     *    is pulled from the database.
     *
     * @param string $class_name Name of class to instantiate.
     * @param array $args Array of elements passed to constructor.
     * @return object
     */
    public function fetchObject($class_name, array $args = null)
    {
        $this->checkStatement();
        // PDO's fetchObject second parameter is type hinted so you can't
        // pass null to it without receiving a warning. This is why we have
        // to check and have a separate call.
        if (isset($args)) {
            $result = $this->pdo_statement->fetchObject($class_name, $args);
        } else {
            $result = $this->pdo_statement->fetchObject($class_name);
        }
        if (empty($result)) {
            $this->clearStatement();
        }
        return $result;
    }

    /**
     * Returns the result of the query created by the object's selectQuery method
     * @access public
     * @param integer $select_type Type of results expected: one, row, column, or all. See defines.
     */
    public function select()
    {
        $this->loadSelectStatement();
        $result = $this->fetchAll();
        $this->clearStatement();
        return $result;
    }

    /**
     * Clears the current pdo statement. Should be run if a fetch ends prematurely.
     */
    public function clearStatement()
    {
        unset($this->pdo_statement);
    }

    /**
     * Pulls various control information from the DB object and the tables within.
     * @access private
     * @param integer $mode Type of data fields to pull: SELECT or UPDATE
     * @return array
     */
    private function pullResourceData($mode = DB::SELECT)
    {
        $modules = null; // tables and subselects
        $sources = null; // joins, tables, and subselects
        // if where_groups is NOT empty then allow where to start with its conjunction
        $allow_first_conjunction = false;

        if (!empty($this->join_tables)) {
            $show_left = true;
            $joined = array();
            foreach ($this->join_tables as $join) {
                $joined[] = $join->getResourceQuery($show_left);
                $show_left = false;
            }
            $sources[] = implode(' ', $joined);
        }

        if (!empty($this->tables) && !empty($this->sub_selects)) {
            $modules = array_merge($this->tables, $this->sub_selects);
        } elseif ($this->tables) {
            $modules = $this->tables;
        } elseif ($this->sub_selects) {
            $modules = $this->sub_selects;
        }

        if (empty($sources) && empty($modules)) {
            throw new \Exception(t('No tables or subselects created'));
        }

        if ($modules) {
            foreach ($modules as $module) {
                if (($mode == DB::SELECT) && $field_list = $module->getFields()) {
                    $data['columns'][] = $field_list;
                } elseif ($mode == DB::UPDATE && $value_list = $module->getValues()) {
                    $data['columns'][] = $value_list;
                }

                if (!$module->isJoined()) {
                    $sources[] = $module->getResourceQuery();
                }

                if ($mode == DB::SELECT) {
                    $thaving = $module->getHavingStack($allow_first_conjunction);
                    if ($thaving) {
                        $having[] = $thaving;
                        $allow_first_conjunction = true;
                    }
                }

                if ($module->isRandomOrder()) {
                    $order[] = $module->getRandomOrder();
                } else {
                    $order_list = $module->getOrderBy();
                    if ($order_list) {
                        $order[] = $order_list;
                    }
                }
            }
        }

        $data['modules'] = implode(', ', $sources);
        if (!empty($this->conditional)) {
            $data['where'] = 'WHERE ' . $this->conditional->__toString();
        }

        // Groups used only on selects
        if ($mode == DB::SELECT && $group_by = $this->getGroupBy()) {
            $glist[] = $group_by;
            if (!empty($having)) {
                $glist[] = 'HAVING ' . implode(', ', $having);
            }
            $data['groupby'] = implode(' ', $glist);
        }

        if (!empty($order)) {
            $data['order'] = 'ORDER BY ' . implode(', ', $order);
        }

        if (isset($this->limit)) {
            $data['limit'] = 'LIMIT ' . $this->limit;
            if (isset($this->offset)) {
                $data['limit'] .= ' OFFSET ' . $this->offset;
            }
        }

        if (empty($data)) {
            throw new \Exception('DB object does not contain enough information to create a query.');
        } else {
            return $data;
        }
    }

    /**
     * Creates a SELECT query using the values set in the the DB object.
     * @access public
     * @returns string : The returned query. Also saved in the object's query variable.
     */
    public function selectQuery()
    {
        // Assures fields or functions are present before trying to query database
        $fields_present = false;
        /**
         * Initialized in pullResourceData and extracted below.
         */
        $columns = $modules = $where = $groupby = $order = $limit = null;

        // returns an associate array of values
        // @see pullTableData
        $data = $this->pullResourceData();
        if (!empty($data)) {
            extract($data);
        }

        $query[] = 'SELECT';
        if ($this->distinct) {
            $query[] = 'DISTINCT';
        }
        if (isset($columns)) {
            $query[] = implode(', ', $columns);
            $fields_present = true;
        }

        if (!empty($this->expressions)) {
            if ($fields_present) {
                $query[] = ', ';
            }
            $query[] = implode(', ', $this->expressions);
            //$query[] = implode(', ', $this->expressions);
            $fields_present = true;
        }

        if (!$fields_present) {
            throw new \Exception(t('Select did not contain any fields to return'));
        }

        $query[] = 'FROM';

        // tables, joins, and subselects
        $query[] = $modules;

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

        if (!empty($limit)) {
            $query[] = $limit;
        }
        return implode(' ', $query);
    }

    /**
     * Returns a WHERE conditional string based on the current where_group_stack.
     *
     * @return string
     * @access private
     */
    private function whereGroupQuery($conjunction = false)
    {
        $where = array();
        if (empty($this->where_group_stack)) {
            return null;
        }
        foreach ($this->where_group_stack as $where_group) {
            $where[] = $where_group->query($conjunction);
            $conjunction = true;
        }

        return implode(' ', $where);
    }

    /**
     * Looks in all the current tables in the database and returns a field object
     * or an array of fields if more than one is found. If none is found, an exception
     * is thrown instead
     * @param string $column_name
     * @param boolean $add_to_table If true, adds the submitted field to all tables.
     * @access public
     * @return Field|array
     */
    public function getFieldFromTables($column_name, $add_to_table = false)
    {
        $fields = array();
        foreach ($this->tables as $tbl) {
            if (DATABASE_CHECK_COLUMNS && !$tbl->columnExists($column_name)) {
                throw new \Exception(t('Column "%s" not found', $column_name));
            }
            if ($add_to_table) {
                $fields[] = $tbl->addField($column_name);
            } else {
                $fields[] = $tbl->getField($column_name);
            }
        }

        $tfields = count($fields);

        if ($tfields == 1) {
            return $fields[0];
        } elseif ($fields > 1) {
            return $fields;
        } else {
            throw new \Exception(t('No table fields were found'));
        }
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

    public function hasPrefix()
    {
        return $this->dsn->hasPrefix();
    }

    /**
     * Expecting any DB to string will be used for embedding a select query
     * into another query.
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->selectQuery();
        } catch (Error $e) {
            $this->logError($e);
            trigger_error($e->getMessage());
        }
    }

    /**
     * Returns the rows affected from a previous query or execution
     * @return integer
     */
    public function getRowsAffected()
    {
        return $this->rows_affected;
    }

    /**
     * Returns the number of rows affected by the last query
     * @return integer
     */
    public function numRows()
    {
        if (empty($this->module)) {
            throw new \Exception(t('Missing query module.'));
        }
        return $this->module->numRows();
    }

    /**
     * Returns the number of columns affected by the last query
     * @return integer
     */
    public function numCols()
    {
        if (empty($this->module)) {
            throw new \Exception(t('Missing query module.'));
        }
        return $this->module->numCols();
    }

    /**
     * Safely quotes a value for entry in the database.
     * Uses the mysql quote function but makes assumptions based on the value type
     * @param mixed $value
     * @return mixed
     */
    public function quote($value)
    {
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                trigger_error(t('Database quoting failed on %s object, missing __toString',
                                get_class($value)), E_USER_ERROR);
            }
        }

        if (is_string($value)) {
            $result = self::$PDO->quote($value);
            if ($result === false) {
                throw new \Exception(t('Database connection failed when calling "%s"',
                        'mysql_real_escape_string'));
            } else {
                return $result;
            }
        } elseif (is_array($value)) {
            return array_map(array($this, 'quote'), $value);
        } else {
            return $value;
        }
    }

    /**
     * Stacks the queries made during the current DB incarnation.
     * @param string $query
     */
    public function recordQuery($query)
    {
        \Database::logQuery($query);
    }

    /**
     * Name of the current used database.
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->dsn->getDatabaseName();
    }

    /**
     * Returns the string with the current database engine's delimiter around it.
     * @param string $str
     * @return string
     */
    public function wrap($str)
    {
        return wrap($str, $this->getDelimiter());
    }

    /**
     * Checks to see if there are any transactions remaining. Triggers an error
     * if so. Do not change to an exception as it will seg. fault Apache.
     */
    public function __destruct()
    {
        self::disconnect();
        if (self::$transaction_count > 0) {
            trigger_error(t('%s uncommitted database transactions',
                            self::$transaction_count), E_USER_ERROR);
        } elseif (self::$transaction_count < 0) {
            trigger_error(t('Database transaction commits and/or rollbacks are not in sync'),
                    E_USER_ERROR);
        }
    }

    /**
     * PDO does not appear to have a disconnect function.
     */
    public static function disconnect()
    {
        self::$PDO = null;
    }

    /**
     * Fetches rows via the current select query and loads them into the class
     * name of choice. The class must be an extension of Resource.
     *
     * @param string $class_name
     * @return array Array of Resource objects
     * @throws \Exception
     */
    public function selectAsResources($class_name)
    {
        $this->isResourceClass($class_name);
        $this->loadSelectStatement();
        while ($row = $this->fetch()) {
            $obj = new $class_name;
            $obj->setVars($row);
            $object_stack[] = $obj;
        }
        $this->clearStatement();
        return $object_stack;
    }

    /**
     * Returns a single Resource object based on current select query
     * @param string $class_name
     * @return object
     */
    public function fetchResource($class_name)
    {
        $this->isResourceClass($class_name);
        $this->loadSelectStatement();
        $vars = $this->fetchOneRow();
        $object = new $class_name;
        $object->setVars($vars);
        return $object;
    }

    /**
     * Throws Exception if class is not known or is not a Resource class.
     * @param string $class_name
     * @throws \Exception
     */
    private function isResourceClass($class_name)
    {
        if (!class_exists($class_name)) {
            throw new \Exception(t('Unknown class'));
        }
        if (!is_subclass_of($class_name, '\Resource')) {
            throw new \Exception(t('Class must be of type Resource'));
        }
    }

}

?>
