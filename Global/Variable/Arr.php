<?php

namespace Variable;

/**
 * Variable extender for arrays. Cannot be named "Array" because that is
 * reserved, even with namespaces.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @todo Added a shift and unshift function with associative properties
 */
class Arr extends \Variable {

    /**
     * Arr can use checkbox and select on form export
     * @var array
     */
    protected $allowed_inputs = array('checkbox', 'select');

    /**
     * Makes sure value is an array, throws Error exception otherwise.
     * @param array $value
     * @return boolean | \Error
     */
    protected function verifyValue($value)
    {
        if (!is_array($value)) {
            throw new \Exception(t('Value is not an array'));
        }
        return true;
    }

    /**
     * Pushes a new value on to the array.
     * @param string $value Value added to the array
     * @param string $key The index to the value in the array
     */
    public function push($value, $key = null)
    {
        if (is_null($key)) {
            $this->value[] = $value;
        } else {
            $this->value[$key] = $value;
        }
    }

    /**
     *
     * @param boolean $with_key
     * @return mixed
     */
    public function pop($with_key = false)
    {
        if ($with_key) {
            end($this->value);
            $pair = each($this->value);
            unset($this->value[$pair['key']]);

            return $pair;
        } else {
            return array_pop($this->value);
        }
    }

    /**
     * The value is imploded with each element surrounded by the $tag parameter.
     *
     * @param string $tag
     * @return string
     */
    public function implodeTag($tag)
    {
        $tag_begin = & $tag;
        $tag_end = preg_replace('/<(\w)+[^>]*>/i', '</\\1>', $tag);
        return $tag_begin . implode("$tag_end\n$tag_begin", $this->value) . $tag_end;
    }

    /**
     * Implodes the value parameter, splitting the content with $break
     * @param string $break Characters to insert between array rows.s
     * @return string
     */
    public function implode($break)
    {
        return implode($break, $this->value);
    }

    /**
     * Copied from the php.net website.
     * @author Anonymous
     */
    public function isAssoc()
    {
        return is_assoc($this->value);
    }

    /**
     * Copies the values from the value parameter into the keys of the value.
     */
    public function combine()
    {
        $this->value = array_combine($this->value, $this->value);
    }

    /**
     * Returns true if the $value is found as a key in $this->value
     * @param string $value
     * @return boolean
     */
    public function valueIsSet($value)
    {
        return isset($this->value[$value]);
    }

    /**
     * Runs the php each function on the current object's value.
     * @return mixed
     */
    public function each()
    {
        $val = each($this->value);
        if (empty($val)) {
            $this->reset();
        }
        return $val;
    }

    /**
     * Resets the array pointer in the object's value.
     */
    public function reset()
    {
        reset($this->value);
    }

    /**
     * Overload function returns value of the object's value based on the passed
     * name.
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->value[$name];
    }

    /**
     * Overload function to set value in array based on the name.
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->push($name, $value);
    }

    /**
     * Reindexes a multidimensional array by a specific column in that array.
     * By default, duplicates are not allowed. If you choose to allow them, then
     * expect stacked results:
     *
     * Example:
     * <code>
     * $animals[] = array('id'=>4, 'title'=>'dog');
     * $animals[] = array('id'=>7, 'title'=>'frog');
     * $animals[] = array('id'=>11, 'title'=>'bird');
     * </code>
     *
     * @param string $column_name Name of column
     * @param boolean $duplicate_allowed If false, an exception will be throw on
     * repeated indices. If false, the reindexing will stack rows in an array for
     * each index.
     * @throws \Exception
     */
    public function indexByColumn($column_name, $duplicate_allowed = false)
    {
        $duplicate_allowed = (bool) $duplicate_allowed;

        foreach ($this->value as $val) {
            if (!is_array($val)) {
                throw new \Exception(t('Value of Arr object is not a multidimensional array'));
            }
            if (!isset($val[$column_name])) {
                throw new \Exception(t('Could not index array by column name "%s"',
                        $column_name));
            }

            if (!$duplicate_allowed && isset($new_value[$val[$column_name]])) {
                throw new \Exception(t('Duplicate index value encountered'));
            }

            if ($duplicate_allowed) {
                $new_value[$val[$column_name]][] = $val;
            } else {
                $new_value[$val[$column_name]] = $val;
            }
        }
        $this->value = $new_value;
    }

}

?>