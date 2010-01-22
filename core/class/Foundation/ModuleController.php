<?php

/**
 * Abstract Controller Class for Modules.  Contains some useful
 * method implementations, although all of them can be overridden safely.
 *
 * Each module should implement its own singleton pattern, though.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class ModuleController
{
    protected $context;
    protected $config;

    protected function __construct(ModuleConfig $cfg)
    {
        $this->config = $cfg;
        $this->context = new CommandContext();
    }

    public function getContext()
    {
        return $this->context;
    }

    public function loadClass($name, $subdir='', $type='') {
        if(preg_match('/\W/', $name)) {
            PHPWS_Core::initCoreClass('Foundation/Exception/IllegalClassException.php');
            throw new IllegalClassException("Illegal characters in class {$name}");
        }

        $class = $name . $type;

        try {
            PHPWS_Core::initModClass($this->config->getName(), "{$subdir}/{$class}.php");
        } catch(Pear_Exception $e) {
            PHPWS_Core::initCoreClass('Foundation/Exception/IllegalClassException.php');
            throw new IllegalClassException("Could not initialize class {$name}");
        }

        return $class;
    }

    public function loadCommand($action) {
        $cmd = $this->loadClass($action, 'Command', 'Command');
        return new $cmd();
    }

    public function throwException($exception, $message) {
        $exception = $this->loadClass($exception, 'Exception', 'Exception');
        throw new $exception($message);
    }
}

?>
