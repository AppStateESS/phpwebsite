<?php

namespace Database\Engine\pgsql;

/**
 * Description of Table
 *
 * @author matt
 */
class Table extends \Database\Table
{
    /**
     * Table name is NOT included after "using" in a delete query
     * @var boolean
     */
    protected $included_with_using = false;

    /**
     * Postgresql puts constraint type after the name.
     * @return boolean
     */
    public function constraintTypeAfterName()
    {
        return true;
    }

    public function getDelimiter()
    {
        return '"';
    }

    public function addPrimaryIndexId()
    {
        $id = $this->addDatatype('id', 'serial');
        $pk = new \Database\PrimaryKey($id);
        $this->addPrimaryKey($pk);
        return $id;
    }

    public function getDatatypeList()
    {
        $list = parent::getDatatypeList();
        $list['serial'] = 'serial';
        return $list;
    }

    public function rename($new_name)
    {
        if (!$this->db->allowed($new_name)) {
            throw new \Exception(t('Table name is not allowed'));
        }
        if ($this->db->tableExists($new_name)) {
            throw new \Exception(t('Table name already in use'));
        }

        $query = 'ALTER TABLE ' . $this->getFullName() . ' RENAME TO ' . $new_name;
        return $this->db->exec($query);
    }

    public function renameField(\Database\Field $field, $new_name)
    {
        $update_name = new \Variable\Attribute($new_name, 'update_name');
        $sql[] = 'ALTER TABLE';
        $sql[] = $this->getFullName();
        $sql[] = 'RENAME COLUMN';
        $sql[] = $field->getName();
        $sql[] = 'TO';
        $sql[] = $update_name->get();
        $query = implode(' ', $sql);
        $this->db->query($query);
    }

    /**
     * @see parent::getSchemaQuery()
     * @param string $column_name Name of specific column
     * @return string
     */
    public function getSchemaQuery($column_name = null)
    {
        $sql_query = 'SELECT information_schema.columns.* FROM information_schema.columns
WHERE information_schema.columns.table_name = \'' . $this->getFullName(false) .
                '\' AND information_schema.columns.table_catalog = \'' .
                $this->db->getDatabaseName() . '\'';

        if (isset($column_name)) {
            $column = new \Variable\Attribute($column_name);
            $sql_query .= ' AND information_schema.columns.column_name=\'' .
                    $column->get() . '\'';
        }
        return $sql_query;
    }

    /**
     * Verifies the existence of a specific column in this table.
     * @param string column_name  Column looked for in the table
     * @access public
     */
    public function columnExists($column_name)
    {
        $this->db->loadStatement($this->getSchemaQuery($column_name));
        return (bool) $this->db->fetchOneRow();
    }

    public function getIndexes()
    {
        static $current_table_index = null;

        if (!empty($current_table_index)) {
            return $current_table_index;
        }
        $tbl_name = $this->getFullName(false);
        $query = "
SELECT a.table_name,
       a.constraint_name, a.constraint_type,
       array_to_string(
         array(
           SELECT column_name::varchar
           FROM information_schema.key_column_usage
           WHERE constraint_name = a.constraint_name
           ORDER BY ordinal_position
           ),
         ', '
         ) as column_list
FROM information_schema.table_constraints a
INNER JOIN information_schema.key_column_usage b
ON a.constraint_name = b.constraint_name
LEFT JOIN information_schema.constraint_column_usage c
ON a.constraint_name = c.constraint_name AND
   a.constraint_type = 'FOREIGN KEY'
WHERE    a.table_name='$tbl_name'
GROUP BY a.table_catalog, a.table_schema, a.table_name,
         a.constraint_name, a.constraint_type,
         c.table_name, c.column_name
ORDER BY a.table_catalog, a.table_schema, a.table_name,
         a.constraint_name";
        $this->db->loadStatement($query);
        $rows = $this->db->fetchAll();
        if (empty($rows)) {
            return null;
        }

        foreach ($rows as $idx) {
            $info['column_name'] = $idx['column_list'];
            $info['unique'] = $idx['constraint_type'] == 'UNIQUE' ? 1 : 0;
            $info['primary_key'] = $idx['constraint_type'] == 'PRIMARY KEY' ? 1 : 0;
            $current_table_index[$idx['constraint_name']][] = $info;
        }
        return $current_table_index;
    }

    /**
     * Returns a Datatype object based on the current table column
     * @param string $column_name Name of the column in the current table object
     * @return DB/Datatype
     */
    public function getDataType($column_name)
    {
        $find_default = true;
        $default = null;

        if (!$this->exists()) {
            throw new \Exception(t('Cannot get data type, table does not exist'));
        }
        $schema = $this->getSchema($column_name);

        $column_type = $schema['data_type'];
        $dt = \Database\Datatype::factory($this, $column_name, $column_type);

        $indexes = $this->getIndexes();
        if (!empty($indexes)) {
            foreach ($indexes as $index_name => $indices) {
                foreach ($indices as $idx) {
                    if ($idx['column_name'] == $column_name) {
                        if ($idx['primary_key']) {
                            $find_default = false;
                            $dt->nullDefault();
                        }
                        if ($idx['unique']) {
                            // previously there was an unique flag on the Datatype.
                        }
                    }
                }
            }
        }

        $dt->setIsNull($schema['is_nullable'] != 'NO');

        if ($find_default) {
            $default = preg_replace("/::\w[\w\s]+/", '', $schema['column_default']);

            if (strtolower($default) == 'null') {
                $default = null;
            } elseif (is_numeric($default) && preg_match('/int/', $column_type)) {
                $default = intval($default);
            }
            $dt->setDefault($default);
        }
        return $dt;
    }

    public function hasPearSequenceTable()
    {
        $sequence_table = $this->getFullName(false) . '_seq';

        $db = \Database::newDB();
        $db->loadStatement("SELECT c.relname FROM pg_class c WHERE c.relkind = 'S' AND c.relname = '$sequence_table'");
        $result = $db->fetchOneRow();
        return (bool) $result;
    }

    /**
     * Changes id in the Postgresql to serial coupled to its sequence table.
     */
    public function serializePrimaryKey()
    {
        if (!$this->hasPearSequenceTable()) {
            throw new \Exception('There is not a PEAR::DB sequence for this table');
        }

        $table_name = $this->getFullName();
        $sequence_table = $this->getFullName(false) . '_seq';

        $sql = "ALTER TABLE $table_name ALTER COLUMN id SET DEFAULT NEXTVAL('$sequence_table')";
        $this->db->exec($sql);
        $sql2 = "ALTER SEQUENCE $sequence_table OWNED BY $table_name.id";
        $this->db->exec($sql2);
    }

    public function dropIndex($name)
    {
        $table_name = $this->getFullName();
        $sql = "ALTER TABLE $table_name DROP CONSTRAINT $name";
        $this->db->exec($sql);
    }

    /**
     * Creates a new auto-incrementing, primary key, column named "id"
     */
    public function createPrimaryIndexId()
    {
        $table_name = $this->getFullName();

        $id = $this->addDatatype('id', 'serial');

        $sql = "ALTER TABLE $table_name ADD COLUMN $id";
        $this->db->exec($sql);
        $sql2 = "UPDATE $table_name SET id = DEFAULT";
        $this->db->exec($sql2);
        $sql3 = "ALTER TABLE $table_name ADD PRIMARY KEY (id)";
        $this->db->exec($sql3);
    }

    /**
     * Modifys the parameters of a datatype column.
     * @param \Database\Datatype $old
     * @param \Database\Datatype $new
     */
    public function alter(\Database\Datatype $old, \Database\Datatype $new)
    {
        $this->alterColumnParameters($new);
        $this->alterNullStatus($old, $new);
        $this->alterDefaultStatus($old, $new);
    }

    /**
     * Works with self::alter to change the null status of a column if needed.
     * @param \Database\Datatype $old
     * @param \Database\Datatype $new
     */
    private function alterNullStatus(\Database\Datatype $old, \Database\Datatype $new)
    {
        $table_name = $this->getFullName();
        $column_name = $new->getName();
        if ($old->getIsNull() && !$new->getIsNull()) {
            $setnull = ' SET NOT NULL';
        } elseif (!$old->getIsNull() && $new->getIsNull()) {
            $setnull = ' DROP NOT NULL';
        } else {
            $setnull = null;
        }

        if ($setnull) {
            $query = <<<EOF
ALTER TABLE $table_name ALTER COLUMN $column_name $setnull;
EOF;
            $this->db->exec($query);
        }
    }

    /**
     * Works with self:alter to change teh default setting of a column.
     * @param \Database\Datatype $old
     * @param \Database\Datatype $new
     */
    private function alterDefaultStatus(\Database\Datatype $old, \Database\Datatype $new)
    {
        $old_default = $old->getDefault();
        $new_default = $new->getDefault();

        // if new_default is null, we don't try and update
        // A new default of 'NULL' is handled differently.
        if (!is_null($new_default) && $old_default != $new_default) {
            $default = $new->getDefaultString();
            $table_name = $this->getFullName();
            $column_name = $new->getName();
            $query = <<<EOF
ALTER TABLE $table_name ALTER COLUMN $column_name SET $default;
EOF;
            $this->db->exec($query);
        }
    }

    private function alterColumnParameters(\Database\Datatype $new)
    {
        $table_name = $this->getFullName();
        $column_name = $new->getName();
        $datatype = $new->getDatatype();
        $q[] = $datatype;
        $size = $new->getSize();
        if (!empty($size)) {
            $q[] = '(' . $new->getSize() . ')';
        }
        $q[] = $new->getExtraInfo();

        $new_column_parameters = implode(' ', $q);
        $query = <<<EOF
ALTER TABLE $table_name ALTER COLUMN $column_name TYPE $new_column_parameters
EOF;
        $this->db->exec($query);
    }

}
