<?php

namespace Database;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Column extends Alias {

    /**
     * Reference to the parent table object
     * @var \Database\Resource
     */
    public $resource = null;

    /**
     * Name of column
     * @var Variable\Attribute
     */
    protected $name = null;

    /**
     * Constructs a new column object
     * @param string $name Name of the column
     * @param \Database\Resource $resource
     * @param boolean $check_existence If true, check to see if column exists
     *        before creating
     */
    public function __construct(\Database\Resource $resource, $name, $check_existence = null)
    {
        $check_existance = empty($check_existance) ? DATABASE_CHECK_COLUMNS : $check_existance;
        if (!\Database\DB::allowed($name)) {
            throw new \Exception(t('Bad column name'));
        }
        $this->name = new \Variable\Attribute($name, 'name');
        $this->resource = $resource;
        if ($check_existence && !$this->resource->columnExists($name)) {
            throw new \Exception(t('Column "%s" does not exist in %s "%s"',
                    $name, get_class($resource),
                    $this->resource->getFullName(false)));
        }
    }

    public function setName($name)
    {
        $this->name->set($name);
    }

    /**
     * @return string Name of column
     */
    public function getName($with_delimiter = false)
    {
        return $with_delimiter ? wrap($this->name->get(),
                        $this->resource->db->getDelimiter()) : $this->name->get();
    }

    /**
     * Returns the column name prefixed with the name of the resource. For example
     * Table foo, column bar returns "foo.bar"
     * @return string
     */
    public function getFullName()
    {
        return $this->resource->getAliasOrName() . '.' . $this->getName(true);
    }

    /**
     * Returns the name of the table resource the current object is in
     * @return string
     */
    public function getTableName()
    {
        return $this->resource->getName();
    }

    /**
     * Returns true if the current object is the same table that is set
     * to this field
     * @param $table
     * @return unknown_type
     */
    public function inTableStack(Table $table)
    {
        return $this->resource === $table;
    }

    /**
     *
     * @return \Database\Resource
     */
    public function getTable()
    {
        return $this->getResource();
    }

    /**
     *
     * @return \Database\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

}

?>