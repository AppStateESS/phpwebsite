<?php

/**
 * Basis for Command Architecture.  All phpWebSite Framework Commands should
 * extend from this abstract class.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class Command
{
    protected $moduleConfig;
    protected $reflection;

    public function __construct()
    {
        $this->reflection = new ReflectionObject($this);
    }

    public abstract function getRequestVars();
    public abstract function execute(PollContext $context);

    protected function getParams()
    {
        $props = array();
        foreach($this->reflection->getProperties(
            ReflectionProperty::IS_PROTECTED) as $prop) {
                $props[] = $prop->getName();
        }

        return $props;
    }

    /*
    public function getRequestVars()
    {
        $params = self::getParams();
        $vars = array();

        // Set the action to the name of the object
        $name = $this->reflection->getName();
        $vars['action'] = preg_replace('Command', '', $name);

        // Fill out the rest
        foreach($params as $param) {
            if(isset($this->$param)) {
                $vars[$param] = $this->$param;
            }
        }

        return $vars;
    }*/

    public function setModuleConfig(ModuleConfig $cfg)
    {
        $this->moduleConfig = $cfg;
    }

    public function initForm(PHPWS_Form &$form)
    {
        $form->addHidden('module', $this->moduleConfig->getName());
        foreach($this->getRequestVars() as $key=>$val) {
            $form->addHidden($key, $val);
        }
    }

    public function getURI()
    {
        $uri = $_SERVER['SCRIPT_NAME'] . "?module={$this->moduleConfig->getName()}";
        foreach($this->getRequestVars() as $key=>$val) {
            $uri .= "&$key=$val";
        }

        return $uri;
    }

    public function getLink($text)
    {
        return PHPWS_Text::moduleLink($text,
            $this->moduleConfig->getName(), $this->getRequestVars());
    }

    public function redirect()
    {
        $path = $this->getURI();

        header('HTTP/1.1 303 See Other');
        header("Location: $path");
        exit(); // TODO: This needs to call the official Core Exit function to close things up.
    }
}

?>
