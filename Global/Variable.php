<?php

/**
 * Abstract class extended by various variables types (Arr, Bool, Date, etc.).
 * Assists with type restrictions, form generation, file configuration creation,
 * and database entry.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Variable extends Data {

    /**
     * The variable's value
     * @var mixed
     */
    protected $value;

    /**
     * Indicates whether variable may be null
     * @var boolean
     */
    protected $allow_null = false;

    /**
     * The name of variable created, helps with forms
     * @var string
     */
    protected $varname;

    /**
     * If developer set the variable name, this is true.
     * @see defineAsTableColumn
     * @var boolean
     */
    private $varname_set = false;

    /**
     * The default input type used for this variable in a form.
     * @var unknown_type
     */
    protected $input_type = 'text';

    /**
     * Type of column in a database
     * @var string
     */
    protected $column_type;

    /**
     * Array of choices used by form and post verification. If choices is not empty, it
     * will be checked against by set
     *
     * @var array
     */
    protected $choices;

    /**
     * Label describes the Variable. A proper name or description of the variable.
     * @var string
     */
    protected $label;

    /**
     * Types of variables allowed by the factory. Defaults to null but may be
     * filled by an extending class
     * @var array
     */
    protected $types_allowed = null;

    protected $allowed_inputs = array();

    protected $datatype;

    /**
     * Verifies the value property. Should throw Error exception on failure. This
     * is run by Variable in set.
     * @see Variable::set()
     * @param mixed $value Each extended class should test if the value getting
     * @param boolean $strict If true, the value MUST be the exact variable type
     *      false here would allow loose typing (for example, an integer could
     *      be a string). It is up to the child class to enforce or even ignore
     *      this parameter
     * set is of the correct format.
     */
    abstract protected function verifyValue($value);

    /**
     *
     * @param string $varname The name of the variable you are creating
     * @param mixed $value
     */
    public function __construct($value = null, $varname = null)
    {
        if (empty($varname)) {
            $this->loadVarname();
        } else {
            $this->setVarname($varname);
        }
        if (!is_null($value)) {
            $this->set($value);
        }
    }

    public function setDatatype(\DB\Datatype $dt)
    {
        $this->datatype = $dt;
    }

    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * Builds an variable object based on the classes in the Variable directory.
     * If $type is not found in the directory, an exception is thrown.
     *
     * If types_allowed contains an array, the passed type must be in this array.
     * This array MUST contain the root variable type along with variables that
     * may extend it. For example, String has extensions for Color, Date, etc.
     * The extensions are in the allowed_types array ALONG WITH the 'String' type.
     *
     * @param string $type The variable type
     * @param string $varname Name given to the new variable object
     * @param mixed $value Default value given to the variable object
     * @return object
     */
    public static function factory($type, $value = null, $varname = null)
    {
        $type = parent::createClassName($type);

        // Cannot have a class named "Array"
        if ($type == 'Array') {
            $type = 'Arr';
        }

        if ($type == 'Boolean') {
            $type = 'Bool';
        }

        $class_name = 'Variable\\' . $type;
        if (class_exists($class_name)) {
            $var = new $class_name($value, $varname);
            // See this method's comment for information on the below
            if (!empty($var->types_allowed) && !in_array($type, $var->types_allowed)) {
                throw new \Exception(t('Unknown type "%s" passed to the Factory method in %s object', $class_name, get_class($var)));
            }
            return $var;
        }
        if (empty($type)) {
            $type = 'NULL';
        }
        throw new \Exception(t('Unknown Variable type: %s', $type));
    }

    /**
     * Sets the name of variable object. Assures variable name is of a certain format.
     * Throws exception error otherwise.
     * @param string $varname
     */
    public function setVarname($varname)
    {
        if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/x', $varname)) {
            $this->varname = $varname;
            $this->varname_set = true;
        } else {
            throw new \Exception(t('Improper variable name "%s"', $varname));
        }
    }

    private function loadVarname()
    {
        static $varname_count = 0;
        $class_name = get_class($this);
        $pos = strrpos($class_name, '\\');
        if ($pos) {
            $varname = substr($class_name, $pos + 1);
        } else {
            $varname = &$class_name;
        }
        $varname_count++;
        $this->varname = $varname . $varname_count;
    }

    /**
     * Returns variable name if set, exception error otherwise.
     * @return string
     */
    public function getVarname()
    {
        if (empty($this->varname)) {
            throw new \Exception(t('Variable name not set'));
        } else {
            return $this->varname;
        }
    }

    /**
     * Sets the value
     * @param mixed $value
     * @return boolean/object Exception error if fails
     */
    public function set($value)
    {
        if ($value !== 0 && is_null($value)) {
            if ($this->allow_null) {
                $this->value = null;
                return true;
            } else {
                throw new \Exception(t('%s may not be null', $this->varname));
            }
        }
        try {
            if (is_object($value)) {
                throw new \Exception(t('Variable values may not be objects'));
            }
            $this->verifyValue($value);
        } catch (Error $e) {
            throw $e;
        }
        $this->value = $value;
        return true;
    }

    /**
     * Returns value of variable
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Returns the variable type (Arr, Bool, etc.)
     * @return string
     */
    public function getType()
    {
        if (!$this->type) {
            throw Error(t('Variable type is not set'));
        } else {
            return $this->type;
        }
    }

    /**
     * @see Variable::$label
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = filter_var($label, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    }

    /**
     * Returns the variable label. If label is not set, it returns a string based
     * on the name of the variable.
     * @return string
     */
    public function getLabel()
    {
        if (empty($this->label)) {
            return ucwords(str_replace('_', ' ', $this->varname));
        }
        return $this->label;
    }

    /**
     * Returns true if value is allowed to be null, false otherwise. Also accepts
     * a parameter to set whether variable may be null or not.
     * @param boolean $allow
     * @return boolean
     */
    public function allowNull($allow = null)
    {
        if (!isset($allow)) {
            return $this->allow_null;
        } else {
            return $this->allow_null = (bool) $allow;
        }
    }

    /**
     * Returns a Input or Choice object based on the Variable type.
     * @staticvar array $types
     * @return object
     */
    public function getInput()
    {
        static $types = array('textarea', 'checkbox', 'color', 'date', 'datetime',
    'email', 'file', 'hidden', 'month', 'number', 'password', 'radio', 'range',
    'search', 'select', 'tel', 'text', 'textfield', 'time', 'url', 'week');


        if (!in_array($this->input_type, $types)) {
            throw new \Exception(t('Unrecognized form input type'));
        }

        switch ($this->input_type) {
            case 'select':
                $input = new Form\Choice\Select($this->varname, $this->choices, $this->value);
                break;

            case 'textarea':
                $input = new Form\Input\Textarea($this->varname, $this->value);
                break;

            case 'radio':
                $input = new Form\Choice\Radio($this->varname, $this->choices, $this->value);
                break;

            default:
                $class_name = 'Form\Input\\' . ucfirst($this->input_type);
                $input = new $class_name($this->varname, $this->value, $this->getLabel());
                $input->setId($this->varname);
        }

        return $input;
    }

    /**
     * @see Variable::get
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

    /**
     * Sets the current variable based on the matching value in the _POST superglobal.
     */
    public function post()
    {
        $this->set($_POST[$this->varname]);
    }

    /**
     * Returns a variable as a PHP string to allow the variable to be defined in
     * a .php file.
     * @return string
     */
    public function defineAsPHP()
    {
        if (is_null($this->value)) {
            return "\${$this->varname} = NULL;";
        } else {
            return "\${$this->varname} = '{$this->value}';";
        }
    }

    /**
     * Returns the current object as a parameter string in a Javascript script.
     * @return string
     */
    public function defineAsJavascriptParameter()
    {
        if (is_null($this->value)) {
            return "'{$this->varname}' : ''";
        } else {
            return "'{$this->varname}' : '{$this->value}'";
        }
    }

    public function defineAsJavascriptVar()
    {
        if (is_null($this->value)) {
            return "var {$this->varname} = '';";
        } else {
            return "var {$this->varname} = '{$this->value}'";
        }
    }

    /**
     * Receives a form object and implants the current variable as an input
     * @param Form $form
     */
    public function loadInput(Form $form)
    {
        $input = $this->getInput();
        $form->plugInput($input);
    }

    /**
     * Loads an array of choices
     * @see Variable::$choices
     * @param array $choices
     */
    public function setChoices(array $choices)
    {
        if (!is_assoc($choices)) {
            $choices = array_combine($choices, $choices);
        }
        $this->choices = $choices;
    }

    /**
     * Sets the database column type
     * @param string $col_type
     */
    public function setColumnType($col_type)
    {
        $col = new \Variable\Attribute($col_type, 'column_type');
        $this->column_type = $col->__toString();
    }

    /**
     * Returns current choices array
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @see Variable::input_type
     * @param string $type
     */
    public function setInputType($type)
    {
        $type = strtolower($type);
        if (!empty($this->allowed_inputs) && !in_array($type, $this->allowed_inputs)) {
            throw new \Exception(t('Input type "%s" is not allowed for this variable', $type));
        }
        $this->input_type = $type;
    }

    /**
     * Returns true if the current value is empty:
     * 1) is zero
     * 2) is a zero length string
     * 3) is a zero count array
     * 4) is null
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->value);
    }

    /**
     * @return boolean True is the current value variable is set to NULL
     */
    public function isNull()
    {
        return is_null($this->value);
    }

    /**
     * Output of value (without quotes) for saving in database.
     * Should be overwritten if the child class returns a formatted string
     * that is different from a database value (e.g. Date).
     */
    public function toDatabase()
    {
        if ($this->allow_null && $this->isNull()) {
            return null;
        } else {
            return $this->__toString();
        }
    }

    /**
     * Loads a datatype into the passed Table object based on settings in the
     * current Variable object.
     *
     * @param \Database\Table $table
     * @return \Database\Datatype
     * @throws \Exception Error is varname is not set
     */
    public function loadDataType(\Database\Table $table)
    {
        $column_type = empty($this->column_type) ? 'Varchar' : $this->column_type;

        if (empty($this->varname)) {
            throw \Exception('Variable name is not set');
        }
        $dt = $table->addDataType($this->varname, $this->column_type);

        if (isset($this->value)) {
            $dt->setDefault($this->toDatabase());
        } else {
            $dt->setDefault(null);
        }

        if ($this->allowNull()) {
            $dt->setIsNull(true);
        }
        return $dt;
    }

}

?>