<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class PHPWS_DB_Where {

    public $table = null;
    public $column = null;
    public $value = null;
    public $operator = '=';
    public $conj = 'AND';
    public $join = false;

    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * Set operator after checking for compatibility
     * addWhere strtouppers the operator
     */
    public function setOperator($operator)
    {
        if (empty($operator)) {
            return false;
        }

        if (!PHPWS_DB::checkOperator($operator)) {
            return PHPWS_Error::get(PHPWS_DB_BAD_OP, 'core', 'PHPWS_DB::addWhere', _('DB Operator:') . $operator);
        }

        if ($operator == 'LIKE' || $operator == 'ILIKE') {
            $operator = $GLOBALS['PHPWS_DB']['lib']->getLike();
        } elseif ($operator == 'NOT LIKE' || $operator == 'NOT ILIKE') {
            $operator = 'NOT ' . $GLOBALS['PHPWS_DB']['lib']->getLike();
        } elseif ($operator == '~' || $operator == '~*' || $operator == 'REGEXP' || $operator == 'RLIKE') {
            $operator = $GLOBALS['PHPWS_DB']['lib']->getRegexp();
        } elseif ($operator == '!~' || $operator == '!~*' || $operator == 'NOT REGEXP' || $operator == 'NOT RLIKE') {
            $operator = $GLOBALS['PHPWS_DB']['lib']->getNotRegexp();
        }

        $this->operator = $operator;
    }

    public function setJoin($join)
    {
        $this->join = (bool) $join;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function setConj($conj)
    {
        $conj = strtoupper($conj);
        if (empty($conj) || ($conj != 'OR' && $conj != 'AND')) {
            return false;
        }

        $this->conj = $conj;
    }

    public function getValue()
    {
        $value = $this->value;

        if (is_array($value)) {
            switch ($this->operator) {
                case 'IN':
                case 'NOT IN':
                    foreach ($value as $temp_val) {
                        if ($temp_val != 'NULL') {
                            $temp_val_list[] = "'$temp_val'";
                        } else {
                            $temp_val_list[] = $temp_val;
                        }
                    }
                    $value = '(' . implode(', ', $temp_val_list) . ')';

                    break;

                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $value = sprintf("'{%s}' AND '{%s}'", $this->value[0], $this->value[1]);
                    break;
            }
            return $value;
        }

        // If this is not a joined where, return the escaped value
        if (!$this->join && $value != 'NULL') {
            return sprintf('\'%s\'', $value);
        } else {
            // This is a joined value, return table.value
            return $value;
        }
    }

    public function get()
    {
        if (!strstr($this->column, '.')) {
            $column = $this->table . '.' . $this->column;
        } else {
            $column = $this->column;
        }
        $value = $this->getValue();
        $operator = &$this->operator;
        $result = sprintf('%s %s %s', $column, $operator, $value);
        return $result;
    }

}
?>
