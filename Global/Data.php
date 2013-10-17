<?php

/**
 * Foundation abstract class for any data type object. Note: for overload to work
 * all variables in the parent object have to be protected.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Data {

    const SET_MODE = 0;
    const GET_MODE = 1;

    /**
     * If set, then only matching object variables in this array will returned
     * with getVars
     * @var array
     */
    private $allowed_variables;

    /**
     * If set, then matching object variables in this array will NOT be returned
     * with getVars
     * @var array
     */
    private $hidden_variables;

    /**
     * Namespace of class extending this one.
     * @var string
     */
    private $namespace;

    /**
     * The parent object of the current object.
     * @var object
     */
    protected $parent;

    /**
     * Returns true if parameter is private
     * @param string $variable_name
     * @return boolean
     */
    public function isPrivate($variable_name)
    {
        $reflection = new ReflectionClass(get_called_class());
        return $reflection->getProperty($variable_name)->isPrivate();
    }

    /**
     * Returns true if parameter is protected
     * @param string $variable_name
     * @return boolean
     */
    public function isProtected($variable_name)
    {
        $reflection = new ReflectionClass(get_called_class());
        return $reflection->getProperty($variable_name)->isProtected();
    }

    /**
     * Returns true if parameter is public
     * @param string $variable_name
     * @return boolean
     */
    public function isPublic($variable_name)
    {
        $reflection = new ReflectionClass(get_called_class());
        return $reflection->getProperty($variable_name)->isPublic();
    }

    /**
     * Debug helper function. Receives an array and plugs its values into
     * the Debug_Array templates for output.
     * @param string $varname
     * @param array $variables
     * @param boolean $html
     * @return string
     */
    private function debugArray($varname, Array $variables, $html = true)
    {
        ob_start();
        if ($html) {
            include 'Global/Templates/Debug/Debug-Array.html';
        } else {
            include 'Global/Templates/Debug/Debug-Array.txt';
        }
        return ob_get_clean();
    }

    private function debugDefault($varname, $type, $value, $html = true)
    {
        ob_start();
        $value = ($type == 'string') ? "'$value'" : $value;
        if ($html) {
            include 'Global/Templates/Debug/Debug-Default.html';
        } else {
            include 'Global/Templates/Debug/Debug-Default.txt';
        }
        return ob_get_clean();
    }

    private function debugObject($varname, $value, $html = true)
    {
        $class_name = get_class($value);

        if ($value instanceof \Variable\Bool) {
            $object_value = $value->get() ? 'TRUE' : 'FALSE';
        } elseif ($value instanceof \Variable) {
            $object_value = $value->get();
        } else {
            $variables = get_object_vars($value);
        }

        if (is_string_like($value)) {
            $to_string = htmlentities($value->__toString());
        }

        ob_start();
        if ($html) {
            include 'Global/Templates/Debug/Debug-Object.html';
        } else {
            include 'Global/Templates/Debug/Debug-Object.txt';
        }
        return ob_get_clean();
    }

    public static function debugValue($value)
    {
        $type = gettype($value);
        switch ($type) {
            case 'object':
                $info = get_class($value);
                break;

            case 'NULL':
                echo 'NULL';
                return;

            case 'string':
                $info = "'$value'";
                break;

            case 'array':
                $count = count($value);
                $info = $count > 1 ? t('%s rows', $count) : t('1 row');
                break;

            default:
                $info = $value;
        }

        echo "($type) $info";
    }

    /**
     * Simple var_dump of the object
     */
    public function debug($html = true)
    {
        $bt = debug_backtrace();
        $first_call = array_shift($bt);
        extract($first_call);
        $class_name = get_class($this);
        $vars = get_object_vars($this);

        foreach ($vars as $varname => $value) {
            $type = gettype($value);
            switch ($type) {
                case 'object':
                    $row[] = $this->debugObject($varname, $value, $html);
                    break;

                case 'array':
                    $row[] = $this->debugArray($varname, $value, $html);
                    break;

                default:
                    $row[] = $this->debugDefault($varname, $type, $value, $html);
            }
        }
        if ($html) {
            $called = "Class: $class_name<br />Debug called in <a href=\"xdebug://$file@$line\">$file - line $line</a>";
        } else {
            $called = "Class: $class_name\nDebug called in $file - line $line\n";
        }
        $debug_output = implode("\n", $row);
        if ($html) {
            include 'Global/Templates/Debug/Debug.html';
        } else {
            include 'Global/Templates/Debug/Debug.txt';
        }
        exit();
    }

    protected function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns a var_dump of the result of the object's toString method
     */
    public function debugToString()
    {
        if (!method_exists($this, '__toString')) {
            throw new \Exception(t('Class "%s" does not contain a __toString method',
                    get_class($this)));
        }
        if (function_exists('xdebug_var_dump')) {
            // xdebug adds <pre> tags
            var_dump($this->__toString());
        } else {
            echo '<pre>', var_dump($this->__toString()), '</pre>';
        }
    }

    /**
     * Checks the submitted variable name for type, formatting, and existance in object.
     *
     * @param string $variable_name
     */
    private function checkVariableName($variable_name)
    {
        if (!is_string($variable_name)) {
            throw new \Exception(t('Variable name expects a string not a %s',
                    gettype($variable_name)));
        }

        if (preg_match('/\W/', $variable_name)) {
            throw new \Exception(t('Illegally formatted variable name "%s"',
                    $variable_name));
        }

        if (!property_exists($this, $variable_name)) {
            throw new \Exception(t('Variable name "%s" not found in object',
                    $variable_name));
        }
    }

    /**
     * Adds a variable name to the allowed_variables variable.
     *
     * @see Data::$allowed_variables
     * @param string $variable_name
     * @return null
     */
    public function addAllowedVariable($variable_name)
    {
        if (is_array($variable_name)) {
            foreach ($variable_name as $var) {
                $this->addAllowedVariable($var);
            }
            return;
        }
        $this->checkVariableName($variable_name);
        $this->allowed_variables[$variable_name] = $variable_name;
    }

    /**
     * Adds a variable name to the hidden_variables variable
     *
     * @see Data::$hidden_variables
     * @param string $variable_name
     * @return null
     */
    public function addHiddenVariable($variable_name)
    {
        if (is_array($variable_name)) {
            foreach ($variable_name as $var) {
                $this->addHiddenVariable($var);
            }
            return;
        }

        $this->checkVariableName($variable_name);
        $this->hidden_variables[$variable_name] = $variable_name;
    }

    /**
     * Returns the variables set in this object in an associative array.
     * Will not return null variables if $get_null is set to false.
     * Will not return hidden variables.
     * Will ONLY return variables matched in $allowed_variables if set.
     * Will not return child class private variables but WILL return protected
     * variables.
     *
     * @param boolean $return_null If true, return variables with NULL values
     * @return array
     */
    public function getVars($return_null = false)
    {
        $vars = get_object_vars($this);
        unset($vars['allowed_variables']);
        unset($vars['hidden_variables']);
        unset($vars['parent']);
        unset($vars['namespace']);

        if (!$return_null) {
            while ($null_result = array_search(null, $vars, true)) {
                unset($vars[$null_result]);
            }
        }

        if (!empty($this->allowed_variables)) {
            $vars = array_intersect_key($vars, $this->allowed_variables);
        }

        if (!empty($this->hidden_variables)) {
            $vars = array_diff_key($vars, $this->hidden_variables);
        }

        return $vars;
    }

    /**
     * Returns an array of variables that can produce printable strings.
     * @return array
     * @throws \Exception
     */
    public function getStringVars()
    {
        $vars = $this->getVars();

        if (empty($vars)) {
            throw new \Exception(t('No variables returned from Data object'));
        }
        foreach ($vars as $key => $val) {
            if (is_string_like($val)) {
                $new_vars[$key] = (string) $val;
            }
        }

        return $new_vars;
    }

    /**
     * Uses an associate array to set the values in the current object. Will fail
     * if the property does not exist, is not public, or does not have a "set" function.
     * @param array $vars
     * @throws \Exception
     */
    public function setVars(Array $vars)
    {
        foreach ($vars as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \Exception(t('Parameter "%s" does not exist or cannot be set in class %s',
                        $key, get_class($this)));
            }
            if ($this->$key instanceof Variable) {
                if (!is_null($value)) {
                    $this->$key->set($value);
                }
            } elseif (!$this->isPublic($key)) {
                $func = walkingCase($key, 'set');
                if (method_exists($this, $func)) {
                    $this->$func($value);
                } else {
                    throw new \Exception(t('Parameter "%s" does not exist or cannot be set in class %s',
                            $key, get_class($this)));
                }
            } else {
                $this->$key = $value;
            }
        }
    }

    public static function createClassName($name)
    {
        $class = explode('\\', $name);
        foreach ($class as $cl) {
            $new_class[] = ucfirst(strtolower($cl));
        }

        return implode('\\', $new_class);
    }

    /**
     * Parses a file to fill in object values
     * @param string $file_path
     */
    public function read($file_path)
    {
        if (!is_file($file_path)) {
            throw new \Exception(t('Read file not found'));
        }

        include $file_path;
        $vars = get_defined_vars();
        $this->setVars($vars);
    }

    /**
     * Creates a php file containing the current object's values
     * @param string $file_path
     * @param boolean $allow_overwrite
     * @return boolean
     */
    public function write($file_path, $allow_overwrite = false)
    {
        if ((!$allow_overwrite && is_file($file_path))) {
            throw new \Exception(t('Cannot overwrite file at %s', $file_path));
        }

        if (!is_writable(dirname($file_path))) {
            throw new \Exception(t('Cannot write file to directory "%s"',
                    dirname($file_path) . '/'));
        }

        $vars = $this->getVars();
        $content[] = "<?php";
        foreach ($vars as $key => $value) {
            if ($value instanceof Variable) {
                $content[] = $value->getPHP();
            } elseif (is_array($value)) {
                $content[] = sprintf('$%s = \'%s\';', $key, print_r($value));
            } else {
                $content[] = sprintf('$%s = \'%s\';', $key, $value);
            }
        }
        $content[] = "?>";
        if (!file_put_contents($file_path, implode("\n", $content))) {
            throw new \Exception(t('Could not write file to %s', $file_path));
        }
        return true;
    }

    /**
     * Returns the last class from the current object, normally excluding the
     * namespace.
     * @return mixed
     */
    public function popClass($pop_number = 1)
    {
        $carray = explode('\\', get_class($this));

        if ($pop_number < 1 || $pop_number > count($carray)) {
            throw new \Exception('Number of classes to pop outside range');
        }
        $carray_rev = array_reverse($carray);
        return $carray_rev[$pop_number - 1];
    }

    /**
     * Return a form object based on the Variables in the current object.
     * @return \Form
     */
    public function pullForm()
    {
        $vars = $this->getVars();
        $form = new \Form;
        foreach ($vars as $parameter) {
            if ($parameter instanceof \Variable) {
                $form->addVariable($parameter);
            }
        }
        return $form;
    }

    /**
     * Copies namespace of current Module class into the namespace variable
     */
    protected function loadNamespace()
    {
        $class = explode('\\', get_class($this));
        array_pop($class);
        $this->namespace = implode('\\', $class);
    }

    /**
     * Namespace containing just the namespace. If you need the full class name,
     * use get_class()
     * @return string
     */
    public function getNamespace()
    {
        if (empty($this->namespace)) {
            $this->loadNamespace();
        }
        return $this->namespace;
    }

}

?>