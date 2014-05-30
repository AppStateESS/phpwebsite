<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class JS {

    private $scripts;
    private $hashes;
    private static $singleton;

    /**
     * Loads one of the globally available javascripts included with phpwebsite
     *
     * @param string $name
     */
    public static function loadGlobal($name)
    {
        if (!preg_match('/[\w\-]/', $name)) {
            throw new \Exception(t('Bad script name'));
        }
        $file_path = PHPWS_SOURCE_DIR . 'lib/Javascript/' . $name . '/config.php';
        if (!is_file($file_path)) {
            throw new \Exception(t('Script not found'));
        }

        self::loadSingleton();
        include $file_path;
        $script = new \JS\Script;
        if (isset($dependencies)) {
            $script->setDependencies($dependencies);
        }

        if (isset($path)) {
            $script->setPath($path);
        }

        if (isset($compressed)) {
            $script->setCompressed($compressed);
        }

        if (isset($include_css)) {
            $script->setIncludeCSS($include_css);
        }
        self::$singleton->addScript($script);
    }

    private static function loadSingleton()
    {
        if (empty(self::$singleton)) {
            self::$singleton = new JS;
        }
    }

    public function isScriptIncluded(\JS\Script $script)
    {
        if (empty($this->hashes)) {
            return false;
        }
        return in_array($script->getHash(), $this->hashes);
    }

    /**
     * Adds a script object to the stack.
     *
     * @param \Javascript\Script $script
     * @return void
     */
    public function addScript(\JS\Script $script)
    {
        if ($this->isScriptIncluded($script)) {
            return;
        }
        $this->hashes[] = $script->getHash();

        $dependencies = $script->getDependencies();

        if (!empty($dependencies)) {
            foreach ($dependencies as $dep) {
                self::loadGlobal($dep);
            }
        }
        $this->scripts[] = $script;
    }

    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Returns listing of included scripts for insertion in page.
     * @return string
     */
    public static function printTags()
    {
        $scripts = self::$singleton->getScripts();
        if (empty($scripts)) {
            return null;
        }
        foreach ($scripts as $s) {
            $stack[] = $s->getAddressAsScriptTag();
            $includes = $s->getIncludeCSSTags();
            if (!empty($includes)) {
                $stack[] = $includes;
            }
        }
        return implode("\n", $stack);
    }

}
?>