<?php

namespace Database;

/**
 * A Field is a column name used in select query. In regards to this system:
 *
 * SELECT Field from Table;
 *
 * Not to be confused with Value which is used in INSERT and UPDATE.
 *
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Field extends Column {

    /**
     * Determines if the field should be shown in a select query. For example,
     * if you are using the fields in a function, you may not wish to have
     * them repeated in the query.
     * @var boolean
     */
    private $show_in_select = true;

    /**
     * If true, allows the use of splat (*) as a column name
     * @var boolean
     */
    protected $splat = false;

    /**
     *
     * @param \Database\Resource $resource
     * @param string $name
     * @param string $alias
     * @param boolean $check_existance Sends boolean argument to Column constructor
     */
    public function __construct(\Database\Resource $resource, $name, $alias = null, $check_existance = null)
    {
        $check_existance = empty($check_existance) ? DATABASE_CHECK_COLUMNS : $check_existance;
        parent::__construct($resource, $name, $check_existance);

        if ($alias) {
            $this->setAlias($alias);
        }
    }

    /**
     * @see \Database\Field::$show_in_select
     * @param bool $show
     * @return boolean
     */
    public function showInSelect($show = null)
    {
        if (isset($show)) {
            $this->show_in_select = (bool) $show;
        }
        return $this->show_in_select;
    }

    /**
     * Adds this field to the parent table's field stack
     */
    public function addToResource()
    {
        $this->resource->addField($this);
    }

    /**
     * String representation of this field.
     * @return string
     */
    public function __toString()
    {
        if ($this->alias) {
            return $this->getFullName() . " AS $this->alias";
        } else {
            return $this->getFullName();
        }
    }

    public function rename($new_name)
    {
        $this->resource->renameField($this, $new_name);
    }

    public function allowSplat()
    {
        return $this->splat;
    }

    public function getSchema()
    {
        return $this->resource->getSchema($this->getName());
    }

    public function getMetaInfo()
    {
        $result = $this->getSchema();
        $sql[] = $result['COLUMN_TYPE'];
        if (!empty($result['EXTRA'])) {
            $sql[] = $result['EXTRA'];
        }

        if ($result['IS_NULLABLE'] == 'NO') {
            $sql[] = 'NOT NULL';
        }

        if ($result['COLUMN_DEFAULT'] !== null) {
            $sql[] = 'default';
            $sql[] = $result['COLUMN_DEFAULT'];
        }

        return implode(' ', $sql);
    }

}

?>