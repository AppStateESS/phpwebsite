<?php

namespace Database;

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @see Resource
 */
/**
 * @todo Verify
 * If true, columns submitted to the table will be verified. Disabling (false)
 * prevents this. Used when columns are used in table creation.
 */
define('VERIFY_COLUMNS', true);

/**
 * A class that handles database table access and manipulation.
 * Many functions used in Table are contained in Resource. Please see
 * the functions and comments there for more information.
 */
abstract class Table extends Resource
{
    const default_foreign_key_name = 'default_foreign_key';

    /**
     * A listing of all the columns in the table. Not created unless columnExists
     * is called.
     * @var array
     */
    private $all_columns = null;

    /**
     * An array of Datatype objects. Used to Alter/Create tables.
     * Datatypes include Blob, Char, Int(eger), SmallInt(eger), Text,
     * Tinyint(eger), varchar and others.
     * @var array
     */
    protected $datatypes = null;

    /**
     * Name of table after prefixed
     * @see DB::$tbl_prefix
     * @var string
     * @access protected
     */
    protected $full_name = null;

    /**
     * An array of having objects.
     * @var array
     */
    protected $having_stack = null;

    /**
     * Contains an array of ids incremented from a previous insert
     * @var array
     */
    protected $incremented_ids = null;

    /**
     * May contain a DB object used for insertion.
     * @var DB
     */
    protected $insert_select = null;

    /**
     * An array of column names used with an insert select. They must
     * match the order of the select result.
     * @var array
     */
    protected $insert_select_columns = null;

    /**
     * Name of the table
     * @var string
     * @access protected
     */
    protected $name = null;

    /**
     * Array of order by clauses for this table.
     * @var unknown_type
     */
    protected $orders = array();

    /**
     * Stores the primary index, if exists.
     * @var array
     */
    protected $primary_key = null;

    /**
     * Number rows affected from last query.
     * @var integer
     */
    protected $row_count = 0;

    /**
     * Table options included during creation
     * @var string
     */
    protected $table_option = null;

    /**
     * Array of Column objects used in updates. This array
     * is a multiple array structure.
     * @var array
     * @access protected
     */
    protected $values = array();
    protected $constraints = array();

    /**
     * If set to true, then this table will be flagged as one of the tables
     * to delete from in a multiple table query.
     * @var boolean
     */
    protected $include_in_delete;

    /**
     * If true, the table name is included after "using" in a delete query
     * @var boolean
     */
    protected $included_with_using = true;

    /**
     * Extended class should add a primary index to the current table.
     */
    abstract public function addPrimaryIndexId();

    /**
     * @param string $column_name Name of column to check for existence in the
     * current table.
     * @return boolean Returns true
     */
    abstract public function columnExists($column_name);

    abstract public function constraintTypeAfterName();

    abstract public function alter(\Database\Datatype $old, \Database\Datatype $new);

    /**
     * Serializes the primary key in the current table. This is a one time method
     * that brings PHPWS_DB tables up to date with Beanie tables. In a nutshell,
     * the sequence table from the old version will be read for the top id and then
     * the column of the current table will be altered to auto increment.
     */
    abstract public function serializePrimaryKey();

    /**
     * Renames the table
     * @param string $new_name
     */
    abstract public function rename($new_name);

    /**
     * Engine specific function to rename a field.
     * @param \Database\Field $field Field to change
     * @param string $new_name Name to change field to.
     */
    abstract public function renameField(\Database\Field $field, $new_name);

    /**
     * Return the type of database column the current column is.
     * @return DB/Datatype
     */
    abstract public function getDataType($column_name);

    /**
     * Returns a generic schema query used to get table column information from
     * the database. Each database OS is expected to supply their own version.
     *
     * If column_name is set, the query will target a single column for information.
     * Other table columns will be ignored.
     *
     * @param string $column_name Name of specific column
     * @return string
     */
    abstract public function getSchemaQuery($column_name = null);

    /**
     * Return an associative array with index information. Format should be
     *
     * // the $array key is name of the index
     * $index_array['PRIMARY'][0] = array('column_name'=>'id', 'unique'=>1);
     * $index_array['index_name'][0] = array('column_name'=>'foo', 'unique'=>1);
     * $index_array['index_name'][1] = array('column_name'=>'bar', 'unique'=>1);
     *
     */
    abstract public function getIndexes();

    /**
     * Returns true if the current table has a sequence table associated with it.
     */
    abstract public function hasPearSequenceTable();

    /**
     * Drops a table's index
     */
    abstract public function dropIndex($name);

    /**
     * Creates a new primary key index with column name id
     */
    abstract public function createPrimaryIndexId();

    /**
     * @param string $name Name of the table
     * @param string $alias Alias used in place of table name
     * @param DB $db Current object housing this table object
     */
    public function __construct(DB $db, $name, $alias = null)
    {
        parent::__construct($db, $alias);
        $this->setName($name);
    }

    /**
     * The name of the table must conform with MySQL naming conventions and
     * currently exist in the database. Both the name parameter and the full_name
     * parameter are set here.
     * @param string $name Name of the table
     */
    public function setName($name)
    {
        if (!$this->db->allowed($name)) {
            throw new \Exception(t('Improper table name') . ': ' . $name);
        }

        $this->name = $name;
        $this->full_name = $this->db->getTablePrefix() . $this->name;
    }

    /**
     * Returns true if the current table exists. If the table was pulled using
     * DB::addTable, most likely this is not needed as that function will
     * Exception if the table doe not exist.
     * @return boolean
     */
    public function exists()
    {
        return $this->db->tableExists($this->name);
    }

    /**
     * Returns the values set for the current table
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    private function checkConstraintTable(Constraint $constraint)
    {
        $source_table_name = $constraint->getSourceTable()->getFullName();
        if ($source_table_name != $this->getFullName()) {
            throw new \Exception(t('Source column table %s does not match current table %s', $source_table_name, $this->getFullName()));
        }
    }

    /**
     * Adds an associative array of values to the table for an update or
     * insert execution. If this is a multi-tier array, multiple value
     * rows will be added. Note that multiple row is only useful in insertions.
     * Updates will execute the first row ONLY.
     *
     * @param array $values
     */
    public function addValueArray(array $values)
    {
        static $value_key = 0;
        foreach ($values as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $skey => $sval) {
                    $this->addValue($skey, $sval, $value_key);
                }
                $value_key++;
            } else {
                $this->addValue($key, $val);
            }
        }
    }

    public function addForeignKey(ForeignKey $foreign_key)
    {
        $this->checkConstraintTable($foreign_key);
        $this->constraints[] = $foreign_key;
    }

    public function addUnique(Unique $unique)
    {
        $this->checkConstraintTable($unique);
        $this->constraints[] = $unique;
    }

    public function addPrimaryKey(PrimaryKey $primary_key)
    {
        $this->checkConstraintTable($primary_key);
        $this->constraints[] = $primary_key;
    }

    /**
     * Adds insert or update values to the table. If the column is a value object
     * we add it to the array. The value key allows a multiple value query to be
     * sent to the table. The developer's code must keep track of this number.
     * If you want to send an array of values to the table, use addValueArray.
     * Returns the added value object.
     *
     * @param string|Value $column
     * @param mixed $value
     * @param integer $value_key
     * @return object
     */
    public function addValue($column, $value = null, $value_key = 0)
    {
        $value_key = (int) $value_key;
        if (is_string($column)) {
            $value = $this->getValue($column, $value);
        } elseif ($column instanceof Value) {
            if (!$column->inTableStack($this)) {
                throw new \Exception(t('Value object referenced different table object'));
                return false;
            }
            $value = $column;
        } else {
            throw new \Exception(t('Improper parameter'));
        }
        $this->values[$value_key][$value->getName()] = $value;
        return $value;
    }

    /**
     * Returns a Value object. If the column is NOT in the table, a
     * Error is thrown by the Column constructor
     * @param string $column_name
     * @param mixed $value
     * @return Value
     */
    public function getValue($column_name, $value = null)
    {
        if (!$this->db->allowed($column_name)) {
            throw new \Exception(t('Improper column name: "%s"', $column_name));
        }
        if ($value instanceof \Variable) {
            $value = $value->toDatabase();
        }
        $db_value = new Value($this, $column_name, $value);

        return $db_value;
    }

    /**
     * Nulls out the object's values parameter.
     */
    public function resetValues()
    {
        $this->values = null;
    }

    /**
     * Resets the following parameters to null: values, orders, insert_select,
     * insert_select_column, having_stack, incremented_ids
     */
    public function reset()
    {
        parent::reset();
        $this->values = null;
        $this->orders = null;
        $this->insert_select = null;
        $this->insert_select_columns = null;
        $this->having_stack = null;
        $this->incremented_ids = null;
    }

    /**
     * Constructs an insertQuery.
     * @param boolean $use_bind_vars If TRUE, bind variable format will be followed
     * in the query's construction
     * @return string
     */
    public function insertQuery($use_bind_vars = true)
    {
        $column_values = array();
        /**
         * If insert select is present, we run with it and stop. The columns are ignored below.
         */
        if ($this->insert_select) {
            if (empty($this->insert_select_columns)) {
                return sprintf('insert into %s %s;', $this->getFullName(), $this->insert_select);
            } else {
                return sprintf('insert into %s (%s) %s;', $this->getFullName(), implode(', ', $this->insert_select_columns), $this->insert_select);
            }
        }

        if (empty($this->values)) {
            throw new \Exception(sprintf(t('No columns to insert in table: %s'), $this->getFullName()));
        }

        foreach ($this->values as $val_listing) {
            $columns = array();

            if (!isset($set_names)) {
                $set_names = array_keys($val_listing);
                if ($this->primary_key) {
                    $set_names[] = $this->primary_key;
                }
            }
            $primary_key_found = false;
            # If we are using bind vars, they will be supplied in the DB::insert method
            if ($use_bind_vars) {
                $column_values = $set_names;
                array_walk($column_values, function(&$value) {
                    $value = ':' . $value;
                });
            } else {
                foreach ($val_listing as $value) {
                    if ($value->getName() == $this->primary_key) {
                        $primary_key_found = true;
                    }
                    $columns[] = $value->getValue();
                }
                $column_values[] = "'" . implode("', '", $columns) . "'";
            }
        }
        reset($this->values);

        return sprintf('insert into %s (%s) values (%s);', $this->getFullName(), implode(', ', $set_names), implode(', ', $column_values));
    }

    /**
     * Returns incremented primary key ids created from previous inserts
     * @param boolean $first_only
     * @todo needs checking
     * @return array
     */
    public function getIncrementedIds($first_only = false)
    {
        if (empty($this->incremented_ids)) {
            return null;
        }

        if ($first_only) {
            return current($this->incremented_ids);
        } else {
            return $this->incremented_ids;
        }
    }

    /**
     * Receives n parameters to create the primary index array: important for
     * create or alter statements. Each parameter must be a
     * Field or Datatype object, or a string. If it is a string, a Field will
     * attempt to be created. If the Field fails (which WILL happen with a table
     * not yet formed), an exception will be thrown.
     * @param mixed
     */
    public function setPrimaryKey()
    {
        static $recursion = false;

        if (!$recursion) {
            $this->primary_key = array();
        }

        $columns = \func_get_args();

        if (empty($columns)) {
            throw new \Exception(\t('No values were passed'));
        }
        foreach ($columns as $col) {
            if (is_object($col) && ($col instanceof \Database\Field || $col instanceof \Database\Datatype)) {
                $this->primary_key[] = $col;
            } elseif (is_array($col) && $recursion == false) {
                // set to true just long enough to finish the recursion
                $recursion = true;
                call_user_func_array(array('self', 'setPrimaryKey'), $col);
                $recursion = false;
            } elseif (is_string($col)) {
                $this->primary_key[] = new \Database\Field($this, $col, null, false);
            } else {
                throw new \Exception(\t('Could not use supplied parameters'));
            }
        }
    }

    /**
     * Truncates (removes) all rows in the current table. Truncates cannot be rolled
     * back.
     */
    public function truncate()
    {
        $this->db->exec('TRUNCATE TABLE ' . $this->getFullName());
    }

    /**
     * Adds datatypes to the table for creation or alteration.
     * @param string $name The name given to that datatype (id, last_name)
     * @param string $type The type (Blob, Char, Int) of the datatype
     * @return \Database\Datatype
     */
    public function addDataType($name, $type)
    {
        $datatype = Datatype::factory($this, $name, $type);
        $this->datatypes[$name] = $datatype;
        return $datatype;
    }

    /**
     * Returns table name, not prefixed
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns prefixed table name
     * @return string
     */
    public function getFullName($with_delimiter = true)
    {
        if ($with_delimiter) {
            return $this->db->wrap($this->full_name);
        } else {
            return $this->full_name;
        }
    }

    /**
     * Prefixes the table name with the database name
     * @param boolean $with_delimiter
     * @return string
     */
    public function getCompleteName($with_delimiter = true)
    {
        $name = $this->db->getDatabaseName() . '.' . $this->getFullName(false);

        if ($with_delimiter) {
            return wrap($name, $this::field_delimiter);
        } else {
            return $name;
        }
    }

    public function hasAlias()
    {
        return !empty($this->alias);
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getAliasOrName()
    {
        if ($this->alias) {
            return wrap($this->alias, $this->db->getDelimiter());
        } else {
            return wrap($this->full_name, $this->db->getDelimiter());
        }
    }

    /**
     * Returns table alias if set, full_name otherwise.
     * @return string
     */
    public function __toString()
    {
        if ($this->alias) {
            return wrap($this->alias, $this->db->getDelimiter());
        } else {
            return wrap($this->full_name, $this->db->getDelimiter());
        }
    }

    /**
     * Returns the table identifier for a sql query.
     * @return string
     */
    public function getResourceQuery()
    {
        if ($this->alias) {
            return $this->db->wrap($this->full_name) . ' AS ' . $this->alias;
        } else {
            return $this->db->wrap($this->full_name);
        }
    }

    /**
     * Inserts a DB object's select result into this table.
     * If no columns are set you would get this result:
     *
     * Example:
     * <code>
     * $select = \Database::newDB()();
     * $bar = $select->addTable('bar');
     *
     * $db = \Database::newDB()();
     * $foo = $db->addTable('foo');
     * $foo->insertSelect($select);
     * </code>
     * Query:
     * insert into foo SELECT bar.* FROM bar;
     *
     * Using columns names limits the columns inserted.
     *
     * Example using above objects:
     * <code>
     * $bar->addField('id'); // remember: $bar is with $select
     *
     * $foo->insertSelect($select, array('foo_id'));
     * </code>
     * Query:
     * insert into foo (foo_id) SELECT bar.id FROM bar;
     *
     * @param DB $db The database object from which we grab a select result
     * @param array $column_names Names of columns to insert into
     */
    public function insertSelect(DB $db, $column_names = null)
    {
        if ($db === $this->DB) {
            throw new \Exception(t('The insert select DB object must not be this object\'s current parent'));
        }

        $this->insert_select = $db;

        if (is_array($column_names)) {
            $this->insert_select_columns = $column_names;
        }
    }

    /**
     * Sets a SQL table option prior to creation.
     * @link http://dev.mysql.com/doc/refman/5.0/en/create-table.html
     * @param string $option Name of option to set
     * @param string $value Value of the option
     */
    public function setOption($option, $value)
    {
        $this->table_option[$option] = $value;
    }

    /**
     * Drops the current table from the database.
     * @return mixed True on success, exception on error
     */
    public function drop($if_exists = false)
    {

        $exists = $if_exists ? 'IF EXISTS ' : null;
        return $this->db->exec("DROP TABLE $exists" . $this->full_name);
    }

    /**
     * Creates a new table in the currently connected database.
     * The table must contain datatypes. Add datatypes with addDataType method.
     * @param boolean $if_none_exists If true, will add "if not exists" to create
     *                                query. This will avoid an exception
     * @return type
     */
    public function create($if_not_exists = false)
    {
        if (empty($this->datatypes)) {
            throw new \Exception('No data types found. Could not create table');
        }
        $query = $this->createQuery($if_not_exists);
        return $this->db->exec($query);
    }

    public function getDelimiter()
    {
        return $this->db->getDelimiter();
    }

    protected function getConstraintString($create_query = false)
    {
        foreach ($this->constraints as $c) {
            if ($create_query && !is_a($c, '\Database\TableCreateConstraint')) {
                throw new \Exception('This constraint is not allowed during table creation');
            }
            $sql[] = $c->getConstraintString();
        }
        return implode(', ', $sql);
    }

    public function createQuery($if_not_exists = false)
    {
        $str[] = 'CREATE TABLE';
        if ($if_not_exists) {
            $str = 'IF NOT EXISTS';
        }
        $str[] = $this->getFullName();
        $str[] = '(';

        foreach ($this->datatypes as $dt) {
            $sub[] = $dt;
        }

        if (!empty($this->constraints)) {
            $sub[] = $this->getConstraintString(true);
        }

        $table_options = $this->getTableOptionString();
        $str[] = implode(', ', $sub);
        $str[] = ") $table_options ;";
        $query = implode(' ', $str);
        return $query;
    }

    /**
     * A class extending this function can return table options that will be
     * added to the end of the create table query. For example, mysql adds
     * "engine=innodb".
     * @return null
     */
    protected function getTableOptionString()
    {
        return null;
    }

    public function insert()
    {
        $this->row_count = 0;
        $query = $this->insertQuery();
        $prep = DB::$PDO->prepare($query);

        foreach ($this->values as $line) {
            foreach ($line as $key => $val) {
                $data[$key] = $val->getValue();
            }
            $prep->execute($data);
            $this->incremented_ids[] = DB::$PDO->lastInsertId();
            $this->db->recordQuery($query);
            $this->row_count += $prep->rowCount();
        }
        return $this->row_count;
    }

    public function getLastId()
    {
        if (empty($this->incremented_ids)) {
            return null;
        } else {
            return end($this->incremented_ids);
        }
    }

    /**
     * Returns an array of standard SQL data types. Different engine table
     * classes can use this as a foundation for their data type list.
     *
     * When data types created for a table alter or creation, the string
     * is checked against the KEY of this array. The VALUE of the KEY is
     * the class that is created.
     *
     * A extending Table class can alter this function to fit its variants.
     * Unsupported data types can be unset, new datatypes can be added.
     *
     * @see \Database\Datatype::factory()
     * @return array
     */
    public function getDatatypeList()
    {
        $datatype['bit'] = 'bit';
        $datatype['blob'] = 'blob';
        $datatype['char'] = 'character';
        $datatype['character'] = 'character';
        $datatype['character varying'] = 'varchar';
        $datatype['date'] = 'date';
        $datatype['decimal'] = 'decimal';
        $datatype['double precision'] = 'double';
        $datatype['float'] = 'float';
        $datatype['integer'] = 'integer';
        $datatype['int'] = 'integer';
        $datatype['national character'] = 'nchar';
        $datatype['national character varying'] = 'nvarchar';
        $datatype['numeric'] = 'numeric';
        $datatype['real'] = 'real';
        $datatype['smallint'] = 'smallint';
        $datatype['timestamp'] = 'timestamp';
        $datatype['time'] = 'time';
        $datatype['varchar'] = 'varchar';
        $datatype['text'] = 'text';

        return $datatype;
    }

    /**
     * Returns an array containing the table's column information.
     * @param string $column_name
     * @return array
     */
    public function getSchema($column_name = null)
    {
        $sql_query = $this->getSchemaQuery($column_name);
        $table_stmt = $this->db->query($sql_query);

        if (isset($column_name)) {
            $result = $table_stmt->fetch();
        } else {
            $result = $table_stmt->fetchAll();
        }

        return $result;
    }

    /**
     * If passed a parameter, it sets the use_in_query variable.
     * Returns variable condition.
     * @param boolean $use
     * @return boolean
     */
    public function useInQuery($use = null)
    {
        if (isset($use)) {
            $this->use_in_query = (bool) $use;
        }
        return $this->use_in_query;
    }

    public function setIncludeInDelete($delete)
    {
        $this->include_in_delete = (bool) $delete;
    }

    public function getIncludeInDelete()
    {
        return $this->include_in_delete;
    }

    public function isIncludedWithUsing()
    {
        return $this->included_with_using;
    }

}

?>