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

define('DB2_GROUP_BASE',   1);
define('DB2_GROUP_ROLLUP', 2);
define('DB2_GROUP_CUBE',   3);
define('DB2_GROUP_SET',    4);


abstract class DB2_Group {
    /**
     * An array db2_fields
     * @var array
     */
    protected $fields = null;

    /**
     * The grouping type. Will be a standard group by list, rollup, or cube.
     * If a db2_group object is passed, the type will be a grouping set.
     * @var integer
     */
    protected $type = DB2_GROUP_BASE;

    /**
     * This value should be checked before returning the output string.
     * When a group object is used in the construction of a grouping set, its
     * trigger will automatically be changed to false.
     * @var boolean
     */
    protected $show_in_query = true;

    /**
     * Each db has its own method for outputting a group query.
     * @return string
     */
    abstract protected function __toString();

    /**
     * Verifies a group type may be used with the factory db. For example,
     * mysql does not support cube. Should return true or false
     * @param integer Group type define
     * @return boolean
     */
    abstract protected function allowedType($type);


    /**
     * The constructor takes an array of table fields to create a group b
     * y object.
     * The fields may be multidimensional but ONLY if the database supports grouping sets.
     * If it does not, then an exception will be thrown when processed for the query.
     *
     * @param array $fields : An array of DB2_Fields. May be multidimensional (see above)
     * @param integer $type : One of the defined grouping types
     * @return void
     */
    public function __construct($fields, $group_type=null)
    {
        if (empty($group_type)) {
            $group_type = DB2_GROUP_BASE;
        }

        if ($group_type != DB2_GROUP_BASE && !$this->allowedType($group_type)) {
            throw new PEAR_Exception(dgettext('core', 'This database does not support this type of group by'));
        }

        $this->type = $group_type;

        if (is_array($fields)) {
            array_walk_recursive($fields, array($this, 'fieldOrFunction'));
            $this->fields = $fields;
        } elseif ($this->fieldOrFunction($fields)) {
            $this->fields = array($fields);
        } else {
            throw new PEAR_Exception(dgettext('core', 'Parameter contained a value that was not a DB2_Field or DB2_Function object'));
        }
    }


    public function showInQuery($show=null)
    {
        if (isset($show)) {
            $this->show_in_query = (bool)$show;
        }
        return $this->show_in_query;
    }

    static public function factory($dbtype, $fields, $group_type=null)
    {
        $file = PHPWS_SOURCE_DIR . "core/class/DB2/factory/{$dbtype}_Group.php";
        if (!is_file($file)) {
            throw new PEAR_Exception(dgettext('core', 'Group functionality is not available for this database type'));
        }
        require_once $file;
        $class_name = $dbtype . '_Group';
        return new $class_name($fields, $group_type);
    }

    /**
     * Verifies a parameter is a db2_field or db2_function object
     * @param object $f_or_f
     * @return boolean
     */
    public function fieldOrFunction($f_or_f)
    {
        if (!is_a($f_or_f, 'DB2_Field') && !is_a($f_or_f, 'DB2_Function')) {
            return false;
        } else {
            return true;
        }
    }

    public function setGroupType($type)
    {
        if (!in_array($type, array(DB2_GROUP_BASE, DB2_GROUP_CUBE, DB2_GROUP_ROLLUP, DB2_GROUP_SET))) {
            throw new PEAR_Exception(dgettext('core', 'Unknown group by type'));
        }
        $this->type = $type;
    }
}

?>