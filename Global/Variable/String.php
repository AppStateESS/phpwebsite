<?php

namespace Variable;

/**
 * Variable object for strings
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class String extends \Variable {

    /**
     * Contains the form types that can be created from this object
     * @var array
     */
    protected $allowed_inputs = array('text', 'textarea', 'checkbox', 'date', 'datetime', 'email',
        'file', 'month', 'number', 'password', 'search', 'select', 'tel', 'textfield',
        'time', 'url');

    /**
     * @todo assuming this determines whether to slashquote the form value
     * @var boolean
     */
    private $slashquote = false;

    /**
     * A regular expression matched against in the verifyValue function. Must be
     * bookended by delimiters (//, @@)
     *
     * @var string
     * @see String::verifyValue()
     */
    protected $regexp_match;

    /**
     * An array of tags allowed when the value is requested.
     * @var array
     */
    protected $allowed_tags = null;

    /**
     * Number of characters limited to this variable. A zero value is ignored
     * (i.e. "unlimited" characters)
     * @var integer
     */
    protected $limit = 255;

    protected $column_type = 'Mediumtext';

    /**
     * Checks the string to see it is a string, is under limit, and is formatted
     * correctly (dependent on the regexp_match).
     * @param string $value
     * @return boolean|\Error
     */
    protected function verifyValue($value)
    {
        if (!is_string($value)) {
            throw new \Exception(t('Value "%s" is a %s, not a string', gettype($value), $this->varname));
        }

        if ($this->limit && strlen($value) > $this->limit) {
            throw new \Exception(t('%s is over the %s character limit', $this->getLabel(), $this->getLimit()));
        }

        if (isset($this->regexp_match) && !preg_match($this->regexp_match, $value)) {
            throw new \Exception(t('String variable "%s" is not formatted correctly', $this->getVarName()));
        }

        return true;
    }

    /**
     * Sets a character limit for the string.
     * @param integer $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * Sets a regular expression tested against the current value of the object
     * or when the value is set. See verifyValue.
     * @param string $match
     */
    public function setRegexpMatch($match)
    {
        $test = '';
        if (!empty($this->value)) {
            $test = $this->value;
        }

        if (@preg_match($match, $test) === false) {
            throw new \Exception(t('Regular expression error: %s', preg_error_msg(preg_last_error())));
        }
        $this->regexp_match = $match;
    }

    /**
     * @return string Current regexp string
     */
    public function getRegexpMatch()
    {
        return $this->regexp_match;
    }

    /**
     * @return integer Number of characters allowed, 0 == no limit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @todo Write
     * @return string Returns the value ready for Javascript insertion
     */
    public function getJavascript()
    {

    }

    /**
     * Returns value stripped of all tags; unlike __toString which uses the
     * allowed_tags property.
     * @return string
     */
    public function getStripped()
    {
        return \strip_tags($this->value);
    }

    /**
     * Receives an array or individual parameters and adds them to the allowed
     * tags stack.
     */
    public function addAllowedTags()
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            $args = $args[0];
        }
        if (empty($args)) {
            $this->allowed_tags = true;
        } else {
            $this->allowed_tags = '<' . implode('><', $args) . '>';
        }
    }

    /**
     * Return the current value. Strips tags if set.
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->value)) {
            return '';
        }

        if (!$this->allowed_tags) {
            return $this->value;
        } else {
            if (is_string($this->allowed_tags)) {
                $tags = & $this->allowed_tags;
            } else {
                $tags = null;
            }
            return strip_tags($this->value, $tags);
        }
    }

    /**
     * A quick short cut to only allow word characters (A - Z, 0-9, underline)
     * as the value.
     */
    public function wordCharactersOnly()
    {
        $this->regexp_match = '/^[a-zA-Z]+[\w]+$/';
    }

    /**
     * Sets the value as a random string of characters.
     * The length is based on the characters parameter.
     * @param string $characters
     */
    public function randomString($characters = 10)
    {
        if ($characters <= 0) {
            throw new \Exception(t('Too few characters requested'));
        }

        $word = null;
        for ($i = 0; $i < $characters; $i++) {
            switch (rand(0, 2)) {
                case 0:
                    $char = chr(rand(65, 90));
                    break;
                case 1:
                    $char = chr(rand(97, 122));
                    break;

                case 2:
                    $char = (string) rand(0, 9);
                    break;
            }
            $word .= $char;
        }
        $this->value = & $word;
    }

    /**
     * Changes the column name based on the size of the string.
     * NOTE: If the text is extremely long, "Text" may not be enough
     * for MySQL.
     *
     * @param \Database\Table $table
     * @return \Database\Datatype
     */
    public function loadDataType(\Database\Table $table)
    {
        if ($this->limit <= 256) {
            $this->column_type = 'Varchar';
        } else {
            $this->column_type = 'Text';
        }
        $dt = parent::loadDataType($table);
        return $dt;
    }

    public function urlEncode()
    {
        return urlencode($this->__toString());
    }

    public function noLimit()
    {
        $this->limit = 0;
    }
}
?>