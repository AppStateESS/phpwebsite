<?php

namespace Database;

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
/**
 * Basic group by clause
 */
define('GROUP_BASE', 1);

/**
 * Adds sum results for grouping
 * @link http://dev.mysql.com/doc/refman/5.0/en/group-by-modifiers.html
 */
define('GROUP_ROLLUP', 2);

/**
 * Like rollup, but sums all columns. Can be heavy.
 * Currently not supported by MySQL.
 */
define('GROUP_CUBE', 3);
/**
 * Allows defining of groups results
 * Currently not supported by MySQL.
 */
define('GROUP_SET', 4);

/**
 * Facilitates the grouping of conditions in a database query.
 */
abstract class Group {

    /**
     * An array fields
     * @var array
     */
    protected $fields = null;

    /**
     * The grouping type. Will be a standard group by list, rollup, or cube.
     * If a group object is passed, the type will be a grouping set.
     * @var integer
     */
    protected $type = GROUP_BASE;

    /**
     * This value should be checked before returning the output string.
     * When a group object is used in the construction of a grouping set, its
     * trigger will automatically be changed to false.
     * @var boolean
     * @todo Delete if not used
     */
    //protected $show_in_query = true;

    /**
     * Each db has its own method for outputting a group query.
     * @return string
     */
    abstract public function __toString();

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
     * @param array $fields : An array of Fields. May be multidimensional (see above)
     * @param integer $group_type Contains one of the group type defines: GROUP_BASE or
     * GROUP_ROLLUP
     * @param integer $type : One of the defined grouping types
     */
    public function __construct($fields, $group_type = null)
    {
        if (empty($group_type)) {
            $group_type = GROUP_BASE;
        }

        if ($group_type != GROUP_BASE && !$this->allowedType($group_type)) {
            throw new \Exception(t('This database does not support this type of group by'));
        }

        $this->type = $group_type;

        if (is_array($fields)) {
            array_walk_recursive($fields, array($this, 'fieldOrExpression'));
            $this->fields = $fields;
        } elseif ($this->fieldOrExpression($fields)) {
            $this->fields = array($fields);
        } else {
            throw new \Exception(t('Parameter contained a value that was not a Field or Expression object'));
        }
    }

    /**
     * Returns true if the current group has been shown in the query.
     * Returns false is it hasn't been shown yet
     * @todo Searching doesn't reveal a match for this variable
     * @param boolean $show
     * @return boolean
     */
    /**
      public function showInQuery($show=null)
      {
      if (isset($show)) {
      $this->show_in_query = (bool) $show;
      }
      return $this->show_in_query;
      }
     */

    /**
     * Verifies a parameter is a field or expression object
     * @param object $f_or_f
     * @return boolean
     */
    public function fieldOrExpression($f_or_f)
    {
        if (!$f_or_f instanceof Field && !( $f_or_f instanceof Expression && $f_or_f->hasAlias())) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Receives and sets one of the group types
     * @param integer $type
     */
    public function setGroupType($type)
    {
        if (!$this->allowedType($type)) {
            throw new \Exception(t('Unknown or unsupported group type'));
        }
        $this->type = $type;
    }

}

?>
