<?php

/**
 * Class forming the foundation of other Javascript classes.
 *
 * Any variable set in an object sets a javascript PARAMETER.
 *
 * $js = Javascript::getScriptObject('message');
 * $js->message = 'Hello World';
 * // Sets a parameter (if exists for script) named message.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Javascript extends Data {

    protected $parameters = array();
    private static $jquery_loaded = false;

    /**
     * Returns a string needing inclusion in the page head
     * @return string
     */
    abstract public function getHeadScript();

    public function __construct()
    {
        Body::addJavascript($this);
    }

    public function __set($name, $value)
    {
        if (!isset($this->parameters[$name])) {
            throw new \Exception(t('Unknown script parameter'));
        }
        if ($this->parameters[$name] instanceof \Variable) {
            $this->parameters[$name]->set($value);
        } else {
            $this->parameters[$name] = $value;
        }
    }

    public function __get($name)
    {
        $this->parameters[$name];
    }

    public function getParameters()
    {
        if (empty($this->parameters)) {
            return null;
        }

        foreach ($this->parameters as $param) {
            if (!$param->isEmpty()) {
                $qu[] = $param->defineAsJavascriptParameter();
            }
        }
        if (!empty($qu)) {
            return implode(",", $qu);
        }
    }

    public function useJquery()
    {
        \Head::loadJquery();
    }

    static public function getScriptObject($script_name)
    {
        $class_name = 'Javascript\\' . $script_name . '\\Script';
        $class_file = 'Javascript/' . $script_name . '/Script.php';

        require_once $class_file;

        $js = new $class_name;
        return $js;
    }

}

?>