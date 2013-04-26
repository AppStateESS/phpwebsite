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
    public function push($value, $key=null)
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
    public function pop($with_key=false)
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

}

?>