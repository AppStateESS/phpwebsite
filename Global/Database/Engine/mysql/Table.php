<?php

namespace Database\Engine\mysql;

/**
 * Description of Table
 *
 * @author matt
 */
class Table extends \Database\Table {

    private $storage_engine = 'InnoDB';

    //DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

    public function addPrimaryIndexId()
    {
        $id = $this->addDatatype('id', 'int');
        $id->setAutoIncrement();
        $pk = new \Database\PrimaryKey($id);
        $this->addPrimaryKey($pk);
        return $id;
    }

    /**
     * MySQL puts constraint type BEFORE the name.
     * @return boolean
     */
    public function constraintTypeAfterName()
    {
        return false;
    }

    public function getDatatypeList()
    {
        $datatypes = parent::getDatatypeList();
        $datatypes['boolean'] = 'boolean';
        $datatypes['datetime'] = 'datetime';
        $datatypes['mediumtext'] = 'mediumtext';
        $datatypes['tinyint'] = 'tinyint';
        $datatypes['tinytext'] = 'tinytext';

        return $datatypes;
    }

    public function rename($new_name)
    {
        if (!$this->db->allowed($new_name)) {
            throw new \Exception(t('Table name is not allowed'));
        }
        if ($this->db->tableExists($new_name)) {
            throw new \Exception(t('Table name already in use'));
        }

        $query = 'RENAME TABLE ' . $this->getFullName() . ' TO ' . $new_name;
        return $this->db->exec($query);
    }

    public function renameField(\Database\Field $field, $new_name)
    {
        $update_name = new \Variable\Attribute($new_name, 'update_name');
        $sql[] = 'ALTER TABLE';
        $sql[] = $this->getFullName();
        $sql[] = 'CHANGE';
        $sql[] = $field->getName();
        $sql[] = $update_name->get();
        $sql[] = $field->getMetaInfo();
        $query = implode(' ', $sql);

        $this->db->query($query);
    }

    /**
     * Returns extra table options in table creation for MySQL. The default
     * condition always returned is that we only create InnoDB tables.
     * @return string
     */
    protected function getTableOptionString()
    {
        $options[] = 'ENGINE=' . $this->storage_engine;
        $options[] = 'CHARACTER SET=' . MYSQL_CHARACTER_SET;
        $options[] = 'COLLATE=' . MYSQL_COLLATE;
        return implode(' ', $options);
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
                '\' AND information_schema.columns.table_schema = \'' .
                $this->db->getDatabaseName() . '\'';

        if (isset($column_name)) {
            $column = new \Variable\Attribute($column_name);
            $sql_query.= ' AND information_schema.columns.column_name=\'' .
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
        return (bool) $this->db->fetchRow();
    }

    public function getIndexes()
    {
        $sql = 'show index in ' . $this->getFullName();
        $this->db->loadStatement($sql);
        $rows = $this->db->fetchAll();

        if (empty($rows)) {
            return null;
        }


        foreach ($rows as $idx) {
            $info['primary_key'] = $idx['Key_name'] == 'PRIMARY' ? 1 : 0;
            $info['column_name'] = $idx['Column_name'];
            $info['unique'] = (int) !$idx['Non_unique'];
            $index[$idx['Key_name']][] = $info;
        }
        return $index;
    }

    /**
     * Returns a Datatype object based on the current table column
     * @param string $column_name Name of the column in the current table object
     * @return DB/Datatype
     */
    public function getDataType($column_name)
    {
        if (!$this->exists()) {
            throw new \Exception(t('Cannot get data type, table does not exist'));
        }
        $schema = $this->getSchema($column_name);
        $column_type = $schema['DATA_TYPE'];
        $dt = \Database\Datatype::factory($this, $column_name, $column_type);

        $indexes = $this->getIndexes();

        foreach ($indexes as $index_name=> $indices) {
            foreach ($indices as $idx) {
                if ($idx['column_name'] == $column_name) {
                    if ($idx['primary_key']) {
                        $dt->setIsPrimaryKey(1);
                    }
                    if ($idx['unique']) {
                        $dt->setIsUnique(1);
                    }
                }
            }
        }

        $dt->setIsNull($schema['IS_NULLABLE']);

        $default = $schema['COLUMN_DEFAULT'];

        switch ($column_type) {
            case 'int':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
                if (is_numeric($default)) {
                    $default = intval($default);
                }
                break;

            case 'float':
            case 'double':
            case 'decimal':
            case 'bool':
            case 'text':
            case 'blob':
            case 'longtext':
            case 'date':
            case 'datetime':
            case 'timestamp':
            case 'time':
            case 'varchar':
            case 'char':
        }
        $dt->setDefault($default);
        return $dt;
    }

    /**
     * Returns the last id from the PEAR sequence table. Always assumes it will
     * be in the "id" column per previous PhpWebSite workings.
     * Returns false if the sequence table does not exist or if it is empty.
     *
     * @return integer|false
     */
    private function getLastPearSequenceTableId()
    {
        $seq_table_name = $this->getFullName(false) . '_seq';
        $db = \Database::newDB();
        $tbl = $db->addTable($seq_table_name);
        $tbl->addField('id');
        $db->loadSelectStatement();
        $result = $db->fetchRow();
        return empty($result) ? false : $result['id'];
    }

    public function hasPearSequenceTable()
    {
        $seq_table_name = $this->getFullName(false) . '_seq';
        $db = \Database::newDB();
        return $db->tableExists($seq_table_name);
    }

    /**
     * Switches from PHPWS_DB's PEAR sequence table dependence to one using
     * auto_increment.
     */
    public function serializePrimaryKey()
    {
        if (!$this->hasPearSequenceTable()) {
            throw new \Exception('There is not a PEAR::DB sequence for this table');
        }

        $id = $this->getLastPearSequenceTableId();
        $table_name = $this->getFullName();
        $sql = "ALTER TABLE $table_name MODIFY id INTEGER NOT NULL AUTO_INCREMENT";
        $this->db->exec($sql);
        $sql2 = "ALTER TABLE $table_name AUTO_INCREMENT = $id";
        $this->db->exec($sql2);
    }

}

?>
