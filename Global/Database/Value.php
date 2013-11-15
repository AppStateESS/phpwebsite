<?php

namespace Database;

/**
 * A Value is an amount or measure applied to a column in the database. Insert
 * puts new values in a column. Update changes column values.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Value extends Column {

    /**
     * @var string
     */
    private $value = null;
    protected $splat = true;

    /**
     * @param \Database\Table $table
     * @param string $name
     * @param string $value
     */
    public function __construct(Table $table, $name, $value = null, $check_existence=null)
    {
        parent::__construct($table, $name, $check_existence);
        $this->set($value);
    }

    /**
     * Adds a value to insert/update a column
     * @todo create or use string class
     * @param mixed $value
     */
    public function set($value)
    {
        switch (\gettype($value)) {
            case 'string':
                $this->value = new \Variable\String($value, $this->name);
                return true;
                break;

            case 'integer':
                $this->value = new \Variable\Integer($value, $this->name);
                return true;
                break;

            case 'object':
                switch (\get_class($value)) {
                    case 'Expression':
                    case 'Function':
                    case 'Field':
                        $this->value = $value;
                        break;
                }
                /**
                 * This should work for all Variable objects and anything else someone else
                 * someone puts in
                 */
                if (is_string_like($value)) {
                    //if ($value instanceof \Variable) {
                    $this->value = $value;
                    //} elseif (method_exists($value, '__toString')) {
                    //$this->value = new \Variable\String($value->__toString(), $this->name);
                } else {
                    throw new \Exception(t('Unacceptable value type (%s)', gettype($value)));
                }
                return true;
                break;

            case 'NULL':
                $this->value = null;
                return true;
                break;
        }
        throw new \Exception(t('Unacceptable value type (%s)', gettype($value)));
    }

    /**
     * Returns a value string for use in update executions. Insert does
     * not use this function because the label and value are split from
     * one another.
     * @return string
     */
    public function __toString()
    {
        if (is_a($this->value, 'Database\Field')) {
            return $this->getFullName() . '=' . $this->value->getFullName();
        } elseif (is_a($this->value, 'Database\Expression')) {
            return $this->getFullName() . '=' . $this->getValue();
        } else {
            return $this->getFullName() . "='" . $this->getValue() . "'";
        }
    }

    /**
     * Previously quoted but using PDO
     * @return mixed Returns value parameter
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns a PDO constant dependent on the value type
     */
    public function getPDODefine()
    {
        if (is_null($this->value)) {
            return PDO::PARAM_NULL;
        }

        switch (get_class($this->value)) {
            case 'Variable\Bool':
                return \PDO::PARAM_BOOL;

            case 'Variable\Integer':
                return \PDO::PARAM_INT;

            default:
                return \PDO::PARAM_STR;
        }
    }

}

?>