<?php

namespace Database;

/*
 * @author Matthew McNaney <mcnaney at gmail dot com>
 *
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

abstract class Constraint {

    protected $name;
    protected $columns;
    protected $source_table;

    /**
     *
     * @param mixed $columns A single or array of \Database\Datatype objects
     * @param string $name
     */
    public function __construct($columns, $name = null)
    {
        $this->setColumns($columns);
        if ($name) {
            $this->setName($name);
        }
    }

    public function setName($name)
    {
        $this->name = new \Variable\Attribute($name);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param mixed $columns A single or array of \Database\Datatype objects
     */
    public function setColumns($columns)
    {
        $this->check($columns);
        if (is_array($columns)) {
            $this->source_table = $columns[0]->getTable();
            $this->columns = $columns;
        } else {
            $this->source_table = $columns->getTable();
            $this->columns[] = $columns;
        }
    }

    protected function check($datatype)
    {
        if (is_array($datatype)) {
            $this->checkDatatypeArray($datatype);
        } else {
            $this->checkDatatype($datatype);
        }
    }

    protected function checkDatatype(\Database\Datatype $datatype)
    {
        $type = $datatype->getDatatype();
        if ($type == 'BLOB' || $type == 'TEXT') {
            throw new \Exception(t('Constraint column may not be a blob'));
        }

        if (!is_a($datatype, '\Database\Datatype')) {
            throw new \Exception(t('Constraint column is not a data type'));
        }
    }

    public function getSourceTable()
    {
        if (empty($this->source_table)) {
            throw new \Exception(t('Source table not set'));
        }
        return $this->source_table;
    }

    protected function getColumnKeysString()
    {
        foreach ($this->columns as $sk) {
            $column_keys[] = $sk->getName();
        }
        return '(' . implode(', ', $column_keys) . ')';
    }

    /**
     * Goes through the submitted array and insures all objects are of the Datatype
     * class. Also makes sure that all the datatypes are using the same table.
     * @param array $datatypes
     * @throws \Exception
     */
    protected function checkDatatypeArray(Array $datatypes)
    {
        foreach ($datatypes as $dt) {
            $this->checkDatatype($dt);

            $compare_table = null;
            if (!$compare_table) {
                $compare_table = $dt->getTable();
            } elseif ($compare_table !== $dt->getTable()) {
                throw new \Exception(t('Parameter data types do not have matching tables.'));
            }
        }
    }

    public function getConstraintString()
    {
        if (!is_a($this, '\Database\TableCreateConstraint')) {
            throw new \Exception('This constraint is not allowed during table creation');
        }

        $sql[] = 'CONSTRAINT';
        if ($this->source_table->constraintTypeAfterName()) {
            if ($this->name) {
                $sql[] = $this->name;
            }
            $sql[] = $this->getConstraintType();
        } else {
            $sql[] = $this->getConstraintType();
            if ($this->name) {
                $sql[] = $this->name;
            }
        }

        $sql[] = $this->getColumnKeysString();
        return implode(' ', $sql);
    }

    /**
     * Creates the contraint on the current table
     */
    public function add()
    {
        $sql[] = 'ALTER TABLE';
        $sql[] = $this->source_table->getFullName();
        $sql[] = 'ADD';
        $sql[] = $this->getConstraintString();

        $query = implode(' ', $sql);
        $this->source_table->db->exec($query);
    }

}

?>
