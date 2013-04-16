<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
if (!defined('CURRENT_JQUERY')) {
    /**
     *  The current JQuery file.
     */
    define('CURRENT_JQUERY', 'Javascript/jquery/jquery-current.js');
}

/**
 * Handles information, meta tags, page title, scripts, styles, etc. that
 * occur between the <head> tags in a page.
 */
class Head {

    /**
     * Array of scripts to appear
     * @var array
     */
    private $script_stack;

    /**
     * Array of script files
     * @var array
     */
    private $script_files = array();

    /**
     * Array of css files
     * @var array
     */
    private $css_files = array();

    /**
     * Array of style sheet includes
     * @var array
     */
    private $css_stack;

    /**
     * The page title
     * @var string
     */
    private $page_title = 'Untitled';

    /**
     * Array of variables to establish before scripts
     * @var array
     */
    private $javascript_vars;

    private $jquery_loaded = false;
    /**
     * Singleton object of this class
     * @var Display\Head
     */
    static $head;

    public static function singleton()
    {
        if (empty(self::$head)) {
            self::$head = new self;
        }
        return self::$head;
    }

    public static function loadJquery()
    {
        if (!self::$head->jquery_loaded) {
            self::$head->includeJavascript('Javascript/jquery/jquery-current.js');
            self::$head->jquery_loaded = true;
        }
    }

    /**
     * Adds a Javascript object to the script_stack variable
     * @param \Javascript $js
     */
    public function addJavascript(\Javascript $js)
    {
        $this->script_stack[] = $js;
    }

    /**
     * Adds a file to the css_files stack
     * @param string $file Path to CSS file.
     */
    public function includeCSS($file)
    {
        $this->css_files[md5($file)] = $file;
    }

    /**
     * Includes file path to script_files stack
     * @param string $file
     */
    public function includeJavascript($file)
    {
        $this->script_files[md5($file)] = $file;
    }

    /**
     * Adds key = value pairs which are included before the scripts.
     * If the key is an array, it is run through and passed back
     * @param array|string $key
     * @param string $value
     */
    public function addJavascriptVar($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $subkey => $subvalue) {
                $this->addJavascriptVar($subkey, $subvalue);
            }
            return;
        } elseif (is_null($value)) {
            throw new \Exception(t('Missing second parameter'));
        }

        if (!is_string($key) || !is_string($value)) {
            throw new \Exception(t('Key and value pair must be strings'));
        }

        $this->javascript_vars[$key] = $value;
    }

    /**
     * Sets the title tag in the head.
     * @param string $page_title
     */
    public function setPageTitle($page_title)
    {
        $this->page_title = strip_tags(trim($page_title));
    }

    /**
     *
     * @return string
     */
    public function getKeywords()
    {
        //@todo finish
        return null;
    }

    /**
     * Returns all the collected Javascript saved in the script_stack variable.
     * @return string
     */
    public function getAllScripts()
    {
        $scripts = null;

        if (!empty($this->script_stack)) {
            $scripts[] = "<script>";
            foreach ($this->script_stack as $js) {
                $scripts[] = $js->getHeadScript();
            }
            $scripts[] = "</script>\n";
        }
        if (empty($scripts)) {
            return null;
        }
        return implode("\n", $scripts);
    }

    /**
     * Returns all variables stored in the javascript_vars stack.
     * @return string
     */
    public function getAllJavascriptVars()
    {
        $vars = array();
        if (!empty($this->javascript_vars)) {
            $vars[] = "<script>";
            foreach ($this->javascript_vars as $key => $val) {
                $vars[] = "var $key = '$val';";
            }
            $vars[] = "</script>\n";
            return implode("\n", $vars);
        }
    }

    /**
     * Returns all scripts and includes stacked in the head object for
     * print in the header.
     * @return string
     */
    public function getAllIncludes()
    {
        if (empty($this->script_files) && empty($this->css_files)) {
            return null;
        }

        $includes = array();
        foreach ($this->script_files as $file) {
            $includes[] = "<script src=$file></script>";
        }

        foreach ($this->css_files as $file) {
            $includes[] = "<link rel=stylesheet href=$file>";
        }
        return implode("\n", $includes) . "\n";
    }

    /**
     * @return string The current browser page title
     */
    public function getPageTitle()
    {
        return '<title>' . $this->page_title . "</title>\n";
    }

    /**
     * Retrieves each category of headers used in the head tag.
     * @see Display::show()
     * @return array
     */
    public function getHeadArray()
    {
        $headers = array();
        $headers['page_title'] = $this->getPageTitle();
        $headers['keywords'] = $this->getKeywords();
        //$headers['base'] = '<base href="' . \Server::getHttp() . $_SERVER['HTTP_HOST'] . preg_replace("/index.*/", "", $_SERVER['PHP_SELF']) . '" />';
        $headers['base'] = '<base href="' . SHARED_ASSETS . '" />';


        // these are called in this order in case the scripts have includes
        $scripts = $this->getAllScripts();
        $includes = $this->getAllIncludes();
        $vars = $this->getAllJavascriptVars();
        $headers['scripts'] = $vars . $includes . $scripts;
        return $headers;
    }

}

?>