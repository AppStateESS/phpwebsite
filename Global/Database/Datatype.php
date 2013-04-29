<?php

namespace Database;

/**
 * A database data type
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Datatype extends \Data {

    /**
     * The name of the datatype/column name
     * @var string
     */
    protected $name = null;

    /**
     * Default value of data type
     * @var \Variable
     */
    protected $default = null;

    /**
     * @var \Variable Value of data type.
     */
    protected $value = null;

    /**
     * If true, the column will report itself as null
     * @var boolean
     */
    protected $is_null = false;
    protected $table = null;
    protected $check = null;

    /**
     * Size of datatype. Used with character types, floats, etc.
     * @var string
     */
    protected $size = null;

    /**
     * Creates a variable object for the default.
     */
    abstract protected function loadDefault();

    public function __construct(Table $table, $name)
    {
        $this->setName($name);
        $this->table = $table;
        $this->loadDefault();
    }

    /**
     * Creates a data type for insertion into the passed table. The table's
     * database engine type is checked first. This allows database specific data
     * type instructions to be used.
     *
     * @param \Database\Table $table
     * @param string $name Name of column/data type
     * @param string $type Data type
     * @param string $value Default value for column
     * @return \Database\Datatype Returns an extension of Datatype
     * @throws \Exception
     */
    public static function factory(Table $table, $name, $type, $value = null)
    {
        $engine = $table->db->getDatabaseType();
        $alltypes = $table->getDatatypeList();
        $type = strtolower($type);
        if (empty($type)) {
            throw new \Exception(\t('Data type was empty'));
        }
        if (!isset($alltypes[$type])) {
            throw new \Exception(\t('Unknown data type "%s"', $type));
        }
        $class_name = ucwords($alltypes[$type]);
        $class_file = "Global/Database/Datatype/$class_name.php";
        $engine_file = "Global/Database/Engine/$engine/Datatype/$class_name.php";

        if (is_file($engine_file)) {
            $datatype_name = "\Database\Engine\\$engine\Datatype\\$class_name";
        } elseif (is_file($class_file)) {
            $datatype_name = "\Database\Datatype\\$class_name";
        } else {
            throw new \Exception(\t('Unknown class name "%s"', $class_name));
        }
        $object = new $datatype_name($table, $name);
        if ($object->default instanceof \Variable) {
            $object->setDefault($value);
        }
        return $object;
    }

    /**
     * Returns NULL or NOT NULL based on is_null parameter
     * @return string
     */
    public function getIsNull()
    {
        if ($this->is_null) {
            return 'null';
        } else {
            return 'not null';
        }
    }

    public function setIsNull($null)
    {
        $this->is_null = (bool) $null;
    }

    public function setName($name)
    {
        $this->name = \Variable::factory('alphanumeric', $name);
    }

    public function getName()
    {
        return wrap((string) $this->name, $this->table->getDelimiter());
    }

    /**
     * Returns the data type for an alter or create query. Note that
     * getIsNull must directly follow getDefault.
     * @return string
     */
    public function __toString()
    {
        $q[] = (string) $this->getName();
        $q[] = $this->getDatatype();
        if (!is_null($this->size)) {
            $q[] = '(' . $this->getSize() . ')';
        }
        $q[] = $this->getExtraInfo();
        $q[] = $this->getDefault();
        // this MUST be next after getDefault
        $q[] = $this->getIsNull();

        return implode(' ', $q);
    }

    public function getExtraInfo()
    {
        return null;
    }

    /**
     * Extended in varchar and char.
     * @return string The current data type.
     */
    public function getDatatype()
    {
        return strtoupper($this->popClass());
    }

    /**
     * Returns default value as a string for the query. Note that
     * if the default is null and null is allowed, just "default" will be
     * returned. This works because getIsNull is called afterwards.
     *
     * @return string
     */
    public function getDefault()
    {
        /**
         * Text cannot have a default value. loadDefault should prevent data
         * types slipping through without setting this.
         */
        if (is_null($this->default)) {
            return null;
        }

        if ($this->default->isNull() && $this->is_null) {
            return 'default';
        }
        return "default '" . mysql_escape_string($this->default) . "'";
    }

    /**
     *
     * The default may be set to NULL (Text datatype does this) in case default
     * should not be listed
     * @param type $value
     */
    public function setDefault($value)
    {
        if (is_null($value)) {
            if ($this->default instanceof \Variable) {
                $this->default->set(null);
            } else {
                $this->default = null;
            }
        } elseif ($this->default instanceof \Variable) {
            $this->default->set($value);
        } else {
            $this->default = new \Variable\String((string) $value);
        }
    }

    /**
     * Adds the current datatype to the associated table.
     * @param string $after Name of column to place new column after. Null
     * puts new column at the end of the table. 'FIRST' makes it the first column.
     */
    public function add($after = null)
    {
        if (!empty($after)) {
            if ($after !== 'FIRST') {
                $field = new Field($this->table, $after);
                $after = 'AFTER ' . $field->getName();
            }
        }
        $query = 'ALTER TABLE ' . $this->table->getFullName() . ' ADD COLUMN ' .
                $this->__toString() . ' ' . $after;
        return $this->table->db->exec($query);
    }

    /**
     * Calls a CHANGE alteration for renaming the column.
     * @param string $new_name
     */
    public function change($new_name)
    {
        $old_name = $this->getName();
        $this->setName($new_name);
        $query = 'ALTER TABLE ' . $this->table->getFullName() . ' CHANGE ' .
                $old_name . ' ' . $this->__toString();
        $this->table->db->exec($query);
    }

    /**
     * Calls a MODIFY alteration based on the current datatype settings.
     * @param string $after Name of column to place new column after. Null
     * puts new column at the end of the table. 'FIRST' makes it the first column.
     */
    public function modify($after = null)
    {
        $query = 'ALTER TABLE ' . $this->table->getFullName() . ' MODIFY ' .
                $this->__toString() . ' ' . $after;
        return $this->table->db->exec($query);
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * Receives an integer between 0 - 255 for the varchar length
     * @param integer $length
     */
    public function setSize($length)
    {
        $this->size->set($length);
    }

    public function getSize()
    {
        return $this->size;
    }

}

?>
