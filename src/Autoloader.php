<?php

registerAutoloaders();

function registerAutoloaders()
{

    include PHPWS_SOURCE_DIR . 'Autoloaders/Autoloaders.config.php';

    if (!isset($autoloaders)) {
        throw new \Exception('Missing autoloader configuration file.');
    }

    if (!is_array($autoloaders) || empty($autoloaders)) {
        throw new \Exception('Autoloader configuration not formatted correctly.');
    }

    foreach ($autoloaders as $file => $autoload_function) {
        require_once PHPWS_SOURCE_DIR . 'Autoloaders/' . $file . '.php';
        if (!empty($autoload_function)) {
            spl_autoload_register($autoload_function);
        }
    }
}
